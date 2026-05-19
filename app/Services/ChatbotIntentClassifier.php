<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Bước phân loại ý định CHẠY TRƯỚC tool calling.
 * Đảm bảo:
 *   1. Khi câu hỏi mơ hồ → bot HỎI LẠI (không gọi tool bừa)
 *   2. Khi câu hỏi rõ → bot biết tool nào sẽ gọi & param gì
 *   3. Log lại để audit khi sai
 */
class ChatbotIntentClassifier
{
    protected $apiKey;
    protected $endpoint = 'https://api.openai.com/v1/chat/completions';
    protected $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.key', env('OPENAI_API_KEY', ''));
        // Dùng model rẻ — đây là pre-flight, không cần mạnh
        $this->model = (string) config('services.openai.classifier_model', env('OPENAI_CLASSIFIER_MODEL', 'gpt-4o-mini'));
    }

    /**
     * Phân loại 1 câu hỏi.
     *
     * @return array{intent: string, confidence: float, needs_clarification: bool, clarification_question: ?string, target_tool: ?string, extracted_entities: array, reasoning: ?string}
     */
    public function classify(string $userInput, array $context = []): array
    {
        if (empty($this->apiKey)) {
            return $this->fallback('Không có API key — bỏ qua classifier');
        }

        // Cache 30s — user thường gõ lại y hệt khi bot không phản hồi
        $cacheKey = 'intent_' . md5($userInput . json_encode($context));
        $cached = Cache::get($cacheKey);
        if ($cached) return $cached;

        $systemPrompt = $this->buildSystemPrompt($context);

        try {
            // [FIX] timeout 8s thay vì 20s — pre-flight không được block user lâu.
            // Nếu chậm hơn → fallback gracefully, AI chính vẫn chạy bình thường.
            $resp = Http::withToken($this->apiKey)->timeout(8)->retry(1, 200)->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userInput],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1, // low — phân loại cần ổn định
                'max_tokens' => 400,
            ]);

            if ($resp->failed()) {
                Log::warning('intent_classifier.failed', ['body' => $resp->body()]);
                return $this->fallback('API failed');
            }

            $content = $resp->json('choices.0.message.content');
            // [FIX] Guard null content — json_decode(null) gây deprecation PHP 8.1+, error PHP 8.4+
            if (!is_string($content) || $content === '') {
                return $this->fallback('Empty content from API');
            }
            $parsed = json_decode($content, true);
            if (!is_array($parsed)) return $this->fallback('Parse JSON failed');

            $result = [
                'intent' => $parsed['intent'] ?? 'unclear',
                'confidence' => (float) ($parsed['confidence'] ?? 0.0),
                'needs_clarification' => (bool) ($parsed['needs_clarification'] ?? false),
                'clarification_question' => $parsed['clarification_question'] ?? null,
                'target_tool' => $parsed['target_tool'] ?? null,
                'extracted_entities' => $parsed['extracted_entities'] ?? [],
                'reasoning' => $parsed['reasoning'] ?? null,
                'scope_hint' => $parsed['scope_hint'] ?? null, // 'mine'|'all'|'specific'
            ];

            // Log cho audit
            Log::info('intent_classifier.result', [
                'input' => $userInput,
                'intent' => $result['intent'],
                'confidence' => $result['confidence'],
                'needs_clarification' => $result['needs_clarification'],
                'target_tool' => $result['target_tool'],
            ]);

            Cache::put($cacheKey, $result, 30);
            return $result;
        } catch (\Throwable $e) {
            Log::error('intent_classifier.exception', ['err' => $e->getMessage()]);
            return $this->fallback($e->getMessage());
        }
    }

    protected function buildSystemPrompt(array $context): string
    {
        $userRole = $context['user_role'] ?? 'CTV';
        $userId = $context['user_id'] ?? '?';
        $recentEntities = $context['recent_entities'] ?? '';
        $pendingClarification = $context['pending_clarification'] ?? null;

        $pendingNote = $pendingClarification
            ? "\nQUAN TRỌNG: Bot vừa hỏi user làm rõ: \"{$pendingClarification}\". Nếu user trả lời câu đó → confidence cao, không cần hỏi lại."
            : '';

        return <<<PROMPT
Bạn là Intent Classifier cho chatbot quản lý BĐS Antigravity. Người đang hỏi là {$userRole} (ID #{$userId}).
Recent context (entities được nhắc gần đây): {$recentEntities}{$pendingNote}

NHIỆM VỤ: Phân loại câu hỏi → JSON ngắn gọn theo schema:
{
  "intent": string,             // search_listings | analytics | create_listing | create_customer | update_listing_status | get_details | find_similar | top_performers | revenue_report | market_analysis | customer_funnel | manage_files | chitchat | unclear | out_of_scope
  "confidence": float (0-1),    // Độ tự tin classifier
  "needs_clarification": bool,  // TRUE nếu câu mơ hồ — cần hỏi lại
  "clarification_question": string|null,  // Nếu cần hỏi lại, hỏi đúng 1 câu CỤ THỂ
  "target_tool": string|null,   // Tên tool sẽ gọi (nếu confidence ≥ 0.7)
  "extracted_entities": object, // Các giá trị đã rút được: {price_range, location, listing_id, customer_id, period, ...}
  "scope_hint": string|null,    // "mine" (của user đang hỏi) | "all" (toàn hệ thống) | "specific" (ID cụ thể)
  "reasoning": string           // 1 câu giải thích
}

QUY TẮC PHÂN LOẠI:
1. Câu hỏi đếm/tổng/trung bình → intent="analytics" + target_tool="aggregate_listings_stats" hoặc "search_listings" với aggregate_only=true
2. Câu hỏi "top X" → target_tool="top_performers"
3. Câu hỏi giá trung bình/giá thị trường → target_tool="market_analysis"
4. Câu hỏi doanh thu → target_tool="revenue_report"
5. Câu hỏi so sánh tin → target_tool="compare_listings"
6. Câu hỏi tìm tin GIỐNG/TƯƠNG TỰ → target_tool="find_similar_listings"
7. Câu hỏi "khách của tôi", "tin của tôi" → scope_hint="mine"
8. Câu hỏi không thuộc BĐS/khách/CTV/doanh thu/file → intent="out_of_scope"
9. Câu chỉ có đại từ ("nó", "cái đó") mà không có entity gần đây → needs_clarification=true

QUY TẮC CONFIDENCE (rất quan trọng):
- ≥ 0.85: Câu rõ ràng, đủ thông tin (vd: "tìm nhà 2 tỷ ở Q.1")
- 0.7-0.85: Hợp lý nhưng thiếu 1 thông tin nhỏ — vẫn gọi tool, có thể default
- 0.5-0.7: Mơ hồ — needs_clarification=true
- < 0.5: Rất mơ hồ — luôn needs_clarification=true

KHI needs_clarification=true:
- clarification_question phải là MỘT câu hỏi cụ thể, ngắn (< 20 từ)
- VD: "Bạn muốn xem doanh thu tháng nào? (tháng này, tháng trước, hay 6 tháng qua?)"
- KHÔNG hỏi chung chung như "Bạn cần gì?"

CHỈ trả về JSON, không có text khác.
PROMPT;
    }

    protected function fallback(string $reason): array
    {
        return [
            'intent' => 'unclear',
            'confidence' => 0.0,
            'needs_clarification' => false,
            'clarification_question' => null,
            'target_tool' => null,
            'extracted_entities' => [],
            'scope_hint' => null,
            'reasoning' => "Classifier fallback: {$reason}",
        ];
    }
}
