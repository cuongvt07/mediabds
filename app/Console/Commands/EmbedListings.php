<?php

namespace App\Console\Commands;

use App\Models\RealEstateListing;
use App\Services\ListingEmbeddingService;
use Illuminate\Console\Command;

class EmbedListings extends Command
{
    protected $signature = 'chatbot:embed-listings
                            {--only-missing : Chỉ embed các listing chưa có embedding}
                            {--id=* : Chỉ embed các ID cụ thể}
                            {--chunk=100 : Số listing mỗi lần xử lý}
                            {--sleep=0 : Delay (ms) giữa mỗi API call để tránh rate limit}';

    protected $description = 'Sinh/cập nhật embeddings cho real estate listings (phục vụ semantic search)';

    public function handle(ListingEmbeddingService $svc): int
    {
        $ids = $this->option('id');
        $onlyMissing = (bool) $this->option('only-missing');
        $sleep = (int) $this->option('sleep');

        $q = RealEstateListing::query()->whereNotNull('title');
        if (!empty($ids)) $q->whereIn('id', $ids);
        if ($onlyMissing) {
            $q->whereNotExists(function ($s) {
                $s->select(\DB::raw(1))->from('listing_embeddings')->whereColumn('listing_embeddings.listing_id', 'real_estate_listings.id');
            });
        }

        $total = $q->count();
        if ($total === 0) {
            $this->info('Không có listing nào cần embed.');
            return self::SUCCESS;
        }

        $this->info("Bắt đầu embed {$total} listing(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = 0; $fail = 0;
        $q->chunkById((int) $this->option('chunk'), function ($chunk) use ($svc, $bar, &$ok, &$fail, $sleep) {
            foreach ($chunk as $l) {
                $emb = $svc->embedListing($l);
                if ($emb) $ok++; else $fail++;
                $bar->advance();
                if ($sleep > 0) usleep($sleep * 1000);
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Xong. Thành công: {$ok}. Lỗi: {$fail}.");
        if ($fail > 0) {
            $this->warn('Có lỗi xảy ra. Kiểm tra storage/logs/laravel.log để biết chi tiết.');
        }
        return self::SUCCESS;
    }
}
