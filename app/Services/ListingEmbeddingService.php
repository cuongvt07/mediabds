<?php

namespace App\Services;

use App\Models\RealEstateListing;
use App\Models\ListingEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ListingEmbeddingService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint = 'https://api.openai.com/v1/embeddings';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->model = (string) config('services.openai.embedding_model', 'text-embedding-3-small');
    }

    /**
     * Sinh embedding cho 1 đoạn text. Cache theo hash → tiết kiệm API.
     */
    public function embedText(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') return null;
        if (empty($this->apiKey)) {
            Log::warning('embedding.no_api_key');
            return null;
        }

        $hash = hash('sha256', $this->model . '|' . $text);
        return Cache::remember("embed_{$hash}", 86400, function () use ($text) {
            try {
                $resp = Http::withToken($this->apiKey)->timeout(30)->retry(2, 300)->post($this->endpoint, [
                    'model' => $this->model,
                    'input' => $text,
                ]);
                if ($resp->failed()) {
                    Log::error('embedding.failed', ['body' => $resp->body()]);
                    return null;
                }
                return $resp->json('data.0.embedding');
            } catch (\Throwable $e) {
                Log::error('embedding.exception', ['err' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Sinh hoặc cập nhật embedding cho 1 listing.
     */
    public function embedListing(RealEstateListing $l): ?ListingEmbedding
    {
        $text = $this->buildListingText($l);
        $hash = hash('sha256', $this->model . '|' . $text);

        $existing = ListingEmbedding::where('listing_id', $l->id)->first();
        if ($existing && $existing->content_hash === $hash) return $existing;

        $vec = $this->embedText($text);
        if (!$vec) return null;

        return ListingEmbedding::updateOrCreate(
            ['listing_id' => $l->id],
            ['content_hash' => $hash, 'model' => $this->model, 'embedding' => $vec]
        );
    }

    /**
     * Build text mô tả 1 listing để embed — concat các trường có ngữ nghĩa.
     */
    public function buildListingText(RealEstateListing $l): string
    {
        $parts = [
            $l->title,
            "Loại: " . ($l->type ?? '') . ' - ' . ($l->property_type ?? ''),
            "Địa chỉ: " . trim(($l->address ?? '') . ' ' . ($l->ward_name ?? '') . ' ' . ($l->district_name ?? '') . ' ' . ($l->province_name ?? '')),
            "Giá: " . ($l->price ?? '') . ' ' . ($l->price_unit ?? ''),
            "Diện tích: " . ($l->area ?? '') . ' m²',
            $l->bedrooms ? "{$l->bedrooms} PN" : null,
            $l->toilets ? "{$l->toilets} WC" : null,
            $l->floors ? "{$l->floors} tầng" : null,
            $l->direction ? "Hướng {$l->direction}" : null,
            "Mô tả: " . strip_tags((string) $l->description),
        ];
        return implode(' | ', array_filter($parts));
    }

    /**
     * Cosine similarity giữa 2 vector.
     */
    public function cosine(array $a, array $b): float
    {
        $dot = 0.0; $na = 0.0; $nb = 0.0;
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $na  += $a[$i] * $a[$i];
            $nb  += $b[$i] * $b[$i];
        }
        if ($na == 0.0 || $nb == 0.0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }

    /**
     * Tool entry: tìm tin tương tự.
     * Hỗ trợ 2 mode:
     *   - similar_to_listing_id: tìm tin giống tin #X
     *   - query: tìm theo câu mô tả (NL search)
     */
    public function findSimilar(array $args): array
    {
        $limit = min((int) ($args['limit'] ?? 5), 20);
        $excludeSold = (bool) ($args['exclude_sold'] ?? true);

        // Lấy vector cần so sánh
        $queryVec = null;
        $refListingId = null;
        if (!empty($args['similar_to_listing_id'])) {
            $refListingId = (int) $args['similar_to_listing_id'];
            $emb = ListingEmbedding::where('listing_id', $refListingId)->first();
            if (!$emb) {
                $l = RealEstateListing::find($refListingId);
                if (!$l) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng tham chiếu.'];
                $emb = $this->embedListing($l);
                if (!$emb) return ['status' => 'error', 'message' => 'Không tạo được embedding cho tin tham chiếu (kiểm tra OPENAI_API_KEY).'];
            }
            $queryVec = $emb->embedding;
        } elseif (!empty($args['query'])) {
            $queryVec = $this->embedText($args['query']);
            if (!$queryVec) return ['status' => 'error', 'message' => 'Không tạo được embedding cho query.'];
        } else {
            return ['status' => 'error', 'message' => 'Cần "similar_to_listing_id" hoặc "query".'];
        }

        // Lấy tất cả embeddings — pre-filter is_sold để giảm tải
        $q = ListingEmbedding::query()
            ->select('listing_embeddings.listing_id', 'listing_embeddings.embedding')
            ->join('real_estate_listings as l', 'l.id', '=', 'listing_embeddings.listing_id');
        if ($excludeSold) $q->where('l.is_sold', false);
        if ($refListingId) $q->where('listing_embeddings.listing_id', '!=', $refListingId);
        $embs = $q->get();

        if ($embs->isEmpty()) {
            return ['status' => 'success', 'count' => 0, 'message' => 'Chưa có dữ liệu embedding. Chạy: php artisan chatbot:embed-listings'];
        }

        // Tính cosine và lấy top
        $scored = $embs->map(function ($e) use ($queryVec) {
            $vec = is_array($e->embedding) ? $e->embedding : json_decode($e->embedding, true);
            return ['listing_id' => $e->listing_id, 'score' => $this->cosine($queryVec, $vec ?? [])];
        })->sortByDesc('score')->take($limit)->values();

        $ids = $scored->pluck('listing_id')->all();
        $listings = RealEstateListing::whereIn('id', $ids)->get(['id', 'code', 'title', 'price', 'price_unit', 'area', 'address', 'is_sold'])->keyBy('id');

        $data = $scored->map(function ($s) use ($listings) {
            $l = $listings->get($s['listing_id']);
            if (!$l) return null;
            return [
                'id' => $l->id,
                'code' => $l->code,
                'title' => $l->title,
                'price_display' => number_format($l->price, 2, '.', '') . ' ' . $l->price_unit,
                'area' => $l->area . ' m²',
                'address' => $l->address,
                'similarity' => round($s['score'], 4),
                'is_sold' => (bool) $l->is_sold,
            ];
        })->filter()->values();

        return [
            'status' => 'success',
            'format_hint' => 'Hiển thị bằng [LISTING:ID] kèm điểm similarity (làm tròn 2 chữ số).',
            'count' => $data->count(),
            'ref_listing_id' => $refListingId,
            'data' => $data->all(),
        ];
    }
}
