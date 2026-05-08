<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OpenAIService;
use App\Models\RealEstateListing;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use Livewire\WithFileUploads;

class Chatbot extends Component
{
    use WithFileUploads;

    public string $userInput = '';
    public array $messages = [];
    public bool $isTyping = false;
    public $chatFiles = [];
    public bool $isPopup = false;

    public string $customRules = '';
    public bool $showRulesEditor = false;

    public ?string $pendingRunStateId = null;

    public $selectedListing = null;
    public bool $showDetailPopup = false;

    // ═══════════════════════════════════════════════════════
    // [MỚI] Conversation Memory — lưu entities được nhắc đến
    // ═══════════════════════════════════════════════════════
    public array $conversationContext = [
        'last_listing_ids' => [],   // IDs tin đăng vừa nhắc đến
        'last_customer_ids' => [],  // IDs khách hàng vừa nhắc đến
        'last_user_ids' => [],      // IDs nhân sự vừa nhắc đến
        'last_intent' => null,      // Intent vừa thực hiện
        'pending_clarification' => null, // Đang chờ làm rõ gì?
    ];

    public string $streamingResponse = '';

    public function mount()
    {
        $this->loadRules();
        $this->loadHistory();
    }

    // ═══════════════════════════════════════════════════════════════
    // [CẢI THIỆN] loadHistory — khôi phục cả conversationContext
    // ═══════════════════════════════════════════════════════════════
    public function loadHistory()
    {
        $dbMessages = \App\Models\ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        if ($dbMessages->count() > 0) {
            $this->messages = $dbMessages->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content
            ])->toArray();

            // Khôi phục entity context từ lịch sử gần nhất
            $this->rebuildConversationContext();
        } else {
            $this->messages = [[
                'role' => 'assistant',
                'content' => 'Xin chào! Tôi là Antigravity Admin AI. Tôi có thể giúp bạn quản lý hệ thống, thống kê dữ liệu hoặc giải đáp các thắc mắc về quy trình tin đăng. Hôm nay tôi có thể giúp gì cho bạn?'
            ]];
        }
    }

    /**
     * [MỚI] Quét lại 10 tin nhắn gần nhất để trích xuất entities còn liên quan.
     */
    protected function rebuildConversationContext(): void
    {
        $recent = array_slice($this->messages, -10);
        $ids = [];
        foreach ($recent as $m) {
            preg_match_all('/\[LISTING:(\d+)\]|tin\s*(?:đăng|#|ID)?\s*#?(\d+)/i', $m['content'], $matches);
            $found = array_filter(array_merge($matches[1], $matches[2]));
            foreach ($found as $id) $ids[] = (int)$id;
        }
        if (!empty($ids)) {
            $this->conversationContext['last_listing_ids'] = array_values(array_unique(array_slice($ids, -3)));
        }
    }

    public function loadRules()
    {
        $path = storage_path('app/chatbot_rules.md');
        $this->customRules = file_exists($path) ? file_get_contents($path) : '';
    }

    public function saveRules()
    {
        if (!auth()->user()?->isAdmin()) return;
        file_put_contents(storage_path('app/chatbot_rules.md'), $this->customRules);
        $this->showRulesEditor = false;
        $this->dispatch('toast', ['message' => 'Đã cập nhật quy tắc hệ thống!', 'type' => 'success']);
    }

    public function toggleRulesEditor()
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->showRulesEditor = !$this->showRulesEditor;
    }

    public function removeFile($index)
    {
        array_splice($this->chatFiles, $index, 1);
    }

    public function updatedChatFiles()
    {
        try {
            $this->validate(['chatFiles.*' => 'image|max:2048']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->chatFiles = [];
            $this->dispatch('toast', ['message' => 'Lỗi: Tệp phải là ảnh và dung lượng tối đa 2MB.', 'type' => 'error']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // [CẢI THIỆN] sendMessage — thêm context injection trước khi gửi AI
    // ═══════════════════════════════════════════════════════════════════════
    public function sendMessage()
    {
        if (empty(trim($this->userInput)) && empty($this->chatFiles)) return;

        $userMessage = $this->userInput;

        // [CẢI THIỆN] Làm giàu input với context trước khi kiểm tra keyword
        $enrichedMessage = $this->enrichWithContext($userMessage);

        // Keyword intent chỉ dùng cho lệnh CỰC KỲ rõ ràng (đã bán #123, mở lại #123)
        // Các câu mơ hồ đều đẩy lên AI để hiểu ngữ nghĩa
        $intentHandled = $this->handleKeywordIntents($enrichedMessage);
        if ($intentHandled) {
            $this->messages[] = ['role' => 'user', 'content' => $userMessage];
            $this->messages[] = ['role' => 'assistant', 'content' => $intentHandled];
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => $userMessage]);
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'assistant', 'content' => $intentHandled]);
            $this->userInput = '';
            return;
        }

        $fileInfo = "";
        if (!empty($this->chatFiles)) {
            $uploadedUrls = [];
            foreach ($this->chatFiles as $file) {
                $disk = !empty(config('filesystems.disks.s3.bucket')) ? 's3' : 'public';
                $path = $file->store('chatbot_uploads', $disk);
                $url = \Storage::disk($disk)->url($path);
                $uploadedUrls[] = ['name' => $file->getClientOriginalName(), 'url' => $url];
            }
            $fileNames = implode(', ', array_column($uploadedUrls, 'name'));
            $fileInfo = "\n\n[Hệ thống: Đã đính kèm " . count($uploadedUrls) . " tệp: {$fileNames}]";
        }

        // fullUserContent dùng enrichedMessage để AI có đủ ngữ cảnh
        $fullUserContent = $enrichedMessage . $fileInfo;
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => $userMessage]);

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'has_files' => count($this->chatFiles)
        ];

        $this->userInput = '';
        $this->chatFiles = [];
        $this->isTyping = true;
        $this->streamingResponse = '';

        $this->dispatch('trigger-ai-response', userMessage: $userMessage, fullUserContent: $fullUserContent);
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // [MỚI] enrichWithContext — tự động giải mã đại từ/tham chiếu mơ hồ
    // Ví dụ: "xem chi tiết cái đó" → "xem chi tiết tin đăng ID #42"
    //        "cập nhật nó thành đã bán" → "cập nhật tin đăng ID #42 thành đã bán"
    // ═══════════════════════════════════════════════════════════════════════════════
    protected function enrichWithContext(string $input): string
    {
        $lower = mb_strtolower($input);
        $ctx = $this->conversationContext;

        // Giải mã đại từ "cái đó", "nó", "tin đó", "tin này" → ID cụ thể
        $pronounPatterns = ['cái đó', 'cái này', 'nó', 'tin đó', 'tin này', 'cái trên', 'tin vừa rồi'];
        $hasPronoun = false;
        foreach ($pronounPatterns as $p) {
            if (str_contains($lower, $p)) { $hasPronoun = true; break; }
        }

        if ($hasPronoun && !empty($ctx['last_listing_ids'])) {
            $lastId = end($ctx['last_listing_ids']);
            $input .= "\n[System context: Người dùng đang đề cập đến tin đăng ID #{$lastId} từ cuộc hội thoại trước đó]";
        }

        // Nếu input CÓ số nhưng không có "ID/tin/đăng" — gợi ý đó là listing ID
        if (preg_match('/\b(\d{1,6})\b/', $input, $m) && !empty($ctx['last_listing_ids'])) {
            $num = (int)$m[1];
            if (in_array($num, $ctx['last_listing_ids'])) {
                // Đây là ID tin đăng đã nhắc — không cần bổ sung
            } elseif ($num > 0 && $num < 100000 && !str_contains($lower, 'giá') && !str_contains($lower, 'triệu') && !str_contains($lower, 'tỷ')) {
                $input .= "\n[System context: Số {$num} có thể là ID tin đăng hoặc khách hàng]";
            }
        }

        // Inject pending clarification context nếu đang chờ
        if ($ctx['pending_clarification']) {
            $input .= "\n[System context: Đang chờ làm rõ: {$ctx['pending_clarification']}]";
        }

        return $input;
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // [CẢI THIỆN LỚN] generateAIResponse — thêm Pre-Reasoning + Smart Context Window
    // ═══════════════════════════════════════════════════════════════════════════════
    #[\Livewire\Attributes\On('trigger-ai-response')]
    public function generateAIResponse($userMessage, $fullUserContent)
    {
        $openAIService = app(OpenAIService::class);

        // Rate Limiting
        $rateKey = 'chatbot_limit_' . auth()->id();
        $hits = Cache::get($rateKey, 0);
        if ($hits > 50) {
            $this->messages[] = ['role' => 'assistant', 'content' => '⚠️ Quá giới hạn (50 tin/giờ). Thử lại sau.'];
            $this->isTyping = false;
            return;
        }
        Cache::put($rateKey, $hits + 1, 3600);

        // [CẢI THIỆN] Smart summarize: chỉ summarize khi thực sự cần
        if (count($this->messages) > 30) $this->summarizeHistory();

        // [CẢI THIỆN] detectMode dùng AI-assisted analysis thay vì keyword thuần
        $mode = $this->detectModeAdvanced($userMessage);
        $this->dispatch('mode-detected', mode: $mode);

        $context = $this->buildSmartSystemContext($mode);
        $tools = $this->getAvailableTools('ALL');

        // [CẢI THIỆN] Smart Context Window: giữ lại messages quan trọng, không chỉ 6 gần nhất
        $apiMessages = $this->buildSmartContextWindow($fullUserContent);
        array_unshift($apiMessages, ['role' => 'system', 'content' => $context]);

        // [MỚI] Pre-Reasoning Step cho câu phức tạp
        if ($mode === 'SMART' || $this->requiresReasoning($userMessage)) {
            $apiMessages = $this->injectReasoningInstruction($apiMessages, $userMessage);
        }

        // Streaming + Tool calls
        $pendingCalls = [];
        $toolCalls = $openAIService->streamChat($apiMessages, function($chunk) {
            $this->streamingResponse .= $chunk;
            $this->stream(to: 'assistant-reply', content: $chunk);
        }, $tools);

        if (!empty($toolCalls)) {
            // [MỚI] Cập nhật conversation context sau khi tool được gọi
            $this->updateConversationContextFromTools($toolCalls);

            $requiresConfirmation = false;
            $toolFeedback = "";
            $toolMessages = [];

            foreach ($toolCalls as $toolCall) {
                $toolName = $toolCall['function']['name'];
                $args = json_decode($toolCall['function']['arguments'], true);

                if ($toolName === 'update_listing_status') {
                    $requiresConfirmation = true;
                    $pendingCalls[] = $toolCall;
                } else {
                    $result = $this->executeTool($toolName, $args);

                    // [MỚI] Extract entities từ tool result để cập nhật context
                    $this->extractEntitiesFromResult($toolName, $result);

                    $toolFeedback .= ($result['message'] ?? 'Đã xử lý.') . "\n";
                    $toolMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name' => $toolName,
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE)
                    ];
                }
            }

            if ($requiresConfirmation) {
                $apiMessages[] = ['role' => 'assistant', 'content' => $this->streamingResponse, 'tool_calls' => $toolCalls];
                $runStateId = uniqid('run_', true);
                Cache::put("hitl_state_{$runStateId}", [
                    'apiMessages' => $apiMessages,
                    'pendingCalls' => $pendingCalls,
                    'iteration' => 0,
                    'tools' => $tools
                ], 3600);
                $this->pendingRunStateId = $runStateId;
                $this->messages[] = ['role' => 'assistant', 'content' => "⚠️ Cần xác nhận: \n" . $toolFeedback, 'is_hitl' => true];
                $this->isTyping = false;
                return;
            }

            $hasComplexData = collect($toolCalls)->contains(fn($tc) =>
                str_contains($tc['function']['name'], 'search') ||
                str_contains($tc['function']['name'], 'get_details') ||
                str_contains($tc['function']['name'], 'get_listing')
            );

            if ($mode === 'SMART' || $hasComplexData) {
                $apiMessages[] = ['role' => 'assistant', 'content' => $this->streamingResponse, 'tool_calls' => $toolCalls];
                foreach ($toolMessages as $tm) $apiMessages[] = $tm;
                $this->streamingResponse = '';
                $openAIService->streamChat($apiMessages, function($chunk) {
                    $this->streamingResponse .= $chunk;
                    $this->stream(to: 'assistant-reply', content: $chunk);
                });
            } else {
                $feedback = "\n✅ " . trim($toolFeedback);
                $this->streamingResponse .= $feedback;
                $this->stream(to: 'assistant-reply', content: $feedback);
            }
        }

        if (!empty($this->streamingResponse)) {
            // [MỚI] Cập nhật context từ response trước khi lưu
            $this->extractEntitiesFromResponse($this->streamingResponse);

            $this->messages[] = ['role' => 'assistant', 'content' => $this->streamingResponse];
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'assistant', 'content' => $this->streamingResponse]);
        }

        $this->isTyping = false;
        $this->streamingResponse = '';
    }

    // ═══════════════════════════════════════════════════════════════════
    // [MỚI] detectModeAdvanced — kết hợp pattern + semantic signals
    // Chính xác hơn keyword matching đơn thuần
    // ═══════════════════════════════════════════════════════════════════
    protected function detectModeAdvanced(string $input): string
    {
        $input = mb_strtolower($input);
        $wordCount = str_word_count($input);

        // SMART signals — cần suy luận sâu
        $smartSignals = [
            // Phân tích & so sánh
            'phân tích', 'so sánh', 'đánh giá', 'xu hướng', 'thị trường',
            'tại sao', 'lý do', 'nguyên nhân', 'vì sao',
            // Tư vấn & chiến lược
            'nên', 'có nên', 'có đáng', 'tốt hơn', 'ưu điểm', 'nhược điểm',
            'hiệu quả', 'tối ưu', 'cải thiện', 'đề xuất', 'khuyến nghị',
            // Dự báo & xu hướng
            'dự báo', 'tiềm năng', 'triển vọng', 'tương lai',
            // Câu dài có nhiều điều kiện
        ];

        $smartScore = 0;
        foreach ($smartSignals as $kw) {
            if (str_contains($input, $kw)) $smartScore++;
        }

        // Câu dài (>10 từ) có nhiều điều kiện → khả năng cần SMART
        if ($wordCount > 10) $smartScore++;
        // Có dấu "?" và câu dài → câu hỏi mở
        if (str_contains($input, '?') && $wordCount > 6) $smartScore++;

        if ($smartScore >= 2) return 'SMART';

        // FAST signals — thao tác cụ thể
        $fastSignals = [
            'tạo', 'thêm', 'cập nhật', 'chốt', 'đã bán', 'mở lại',
            'xem', 'chi tiết', 'tìm', 'search', 'danh sách', 'list',
            'bao nhiêu', 'tổng', 'thống kê', 'id', 'số',
            'update', 'sửa', 'xóa', 'lưu',
        ];

        foreach ($fastSignals as $kw) {
            if (str_contains($input, $kw)) return 'FAST';
        }

        // Default: FAST cho câu ngắn, SMART cho câu dài không rõ
        return $wordCount <= 5 ? 'FAST' : 'SMART';
    }

    // ═════════════════════════════════════════════════════════════════════
    // [MỚI] requiresReasoning — phát hiện câu cần suy nghĩ trước khi trả lời
    // ═════════════════════════════════════════════════════════════════════
    protected function requiresReasoning(string $input): bool
    {
        $lower = mb_strtolower($input);
        $reasoningTriggers = [
            'tại sao', 'vì sao', 'lý do', 'giải thích', 'nguyên nhân',
            'so sánh', 'khác nhau', 'giống nhau', 'ưu', 'nhược',
            'nên chọn', 'tốt hơn', 'phù hợp', 'đánh giá',
            'phân tích', 'báo cáo', 'tổng hợp',
        ];
        foreach ($reasoningTriggers as $t) {
            if (str_contains($lower, $t)) return true;
        }
        return false;
    }

    // ════════════════════════════════════════════════════════════════════════
    // [MỚI] injectReasoningInstruction — thêm bước "think before answer"
    // Dùng kỹ thuật scratchpad nhẹ, không làm chậm quá nhiều
    // ════════════════════════════════════════════════════════════════════════
    protected function injectReasoningInstruction(array $apiMessages, string $userMessage): array
    {
        // Inject vào system message thay vì gọi thêm API round-trip
        $reasoningAddition = "\n\n## Yêu cầu đặc biệt cho câu hỏi này\n" .
            "Trước khi trả lời, hãy NGẦM xác định (không cần in ra):\n" .
            "1. User thực sự muốn biết điều gì? (core intent)\n" .
            "2. Cần tool nào? Nếu không chắc → gọi tool trước.\n" .
            "3. Dữ liệu đủ chưa? Nếu thiếu → hỏi đúng 1 câu.\n" .
            "Sau đó mới trả lời theo cấu trúc: Quan sát → Nhận xét → Khuyến nghị.";

        // Thêm vào cuối system message
        if (!empty($apiMessages) && $apiMessages[0]['role'] === 'system') {
            $apiMessages[0]['content'] .= $reasoningAddition;
        }

        return $apiMessages;
    }

    // ════════════════════════════════════════════════════════════════════════════
    // [MỚI] buildSmartContextWindow — giữ lại messages QUAN TRỌNG, không chỉ 6 cuối
    // Logic: Luôn giữ 2 đầu + 8 cuối + bất kỳ message nào có listing ID được nhắc lại
    // ════════════════════════════════════════════════════════════════════════════
    protected function buildSmartContextWindow(string $fullUserContent): array
    {
        $all = $this->messages;
        $total = count($all);

        if ($total <= 10) {
            // Ít tin nhắn → giữ hết
            $window = $all;
        } else {
            // Luôn giữ: 2 message đầu (chào hỏi/setup) + 8 message cuối
            $head = array_slice($all, 0, 2);
            $tail = array_slice($all, -8);

            // Tìm thêm messages "anchor" — chứa entities đang được nhắc đến
            $anchors = [];
            $importantIds = $this->conversationContext['last_listing_ids'];
            if (!empty($importantIds)) {
                $idPattern = implode('|', $importantIds);
                foreach (array_slice($all, 2, -8) as $idx => $msg) {
                    if (preg_match("/({$idPattern})/", $msg['content'])) {
                        $anchors[] = $msg;
                        if (count($anchors) >= 3) break; // Tối đa 3 anchor messages
                    }
                }
            }

            $window = array_merge($head, $anchors, $tail);
        }

        // Map sang API format + inject fullUserContent vào message cuối
        $apiMessages = array_map(fn($m) => [
            'role' => $m['role'],
            'content' => $m['content']
        ], $window);

        // Thay thế content của user message cuối cùng bằng enriched version
        for ($i = count($apiMessages) - 1; $i >= 0; $i--) {
            if ($apiMessages[$i]['role'] === 'user') {
                $apiMessages[$i]['content'] = $fullUserContent;
                break;
            }
        }

        return $apiMessages;
    }

    // ═══════════════════════════════════════════════════════════════════
    // [MỚI] buildSmartSystemContext — thay getCachedSystemContext
    // Cache theo mode + user + conversation context hash
    // ═══════════════════════════════════════════════════════════════════
    protected function buildSmartSystemContext(string $mode): string
    {
        $userId = auth()->id();
        // Hash context để invalidate cache khi context thay đổi
        $ctxHash = md5(json_encode($this->conversationContext));

        return Cache::remember("chatbot_ctx_{$mode}_{$userId}_{$ctxHash}", 120, function() use ($mode) {
            $stats = $this->getSystemStats();
            $prompt = ($mode === 'FAST') ? $this->getFastPrompt() : $this->getSmartPrompt();

            // [MỚI] Inject conversation memory vào system context
            $memoryBlock = $this->buildMemoryBlock();

            return "{$prompt}\n\n{$memoryBlock}\n\n[DỮ LIỆU HỆ THỐNG]\n{$stats}";
        });
    }

    // ════════════════════════════════════════════════════════════════════
    // [MỚI] buildMemoryBlock — tóm tắt "bộ nhớ ngắn hạn" cho AI
    // ════════════════════════════════════════════════════════════════════
    protected function buildMemoryBlock(): string
    {
        $ctx = $this->conversationContext;
        $parts = [];

        if (!empty($ctx['last_listing_ids'])) {
            $ids = implode(', #', $ctx['last_listing_ids']);
            $parts[] = "- Tin đăng đang thảo luận: #{$ids}";
        }
        if (!empty($ctx['last_customer_ids'])) {
            $ids = implode(', #', $ctx['last_customer_ids']);
            $parts[] = "- Khách hàng đang thảo luận: #{$ids}";
        }
        if (!empty($ctx['last_intent'])) {
            $parts[] = "- Thao tác vừa thực hiện: {$ctx['last_intent']}";
        }
        if (!empty($ctx['pending_clarification'])) {
            $parts[] = "- ⚠️ Đang chờ user làm rõ: {$ctx['pending_clarification']}";
        }

        if (empty($parts)) return '[BỘ NHỚ NGẮN HẠN: Cuộc hội thoại mới]';

        return "[BỘ NHỚ NGẮN HẠN — Dùng để hiểu đại từ/tham chiếu]\n" . implode("\n", $parts);
    }

    // ════════════════════════════════════════════════════════════════════════
    // [MỚI] updateConversationContextFromTools — cập nhật context sau tool call
    // ════════════════════════════════════════════════════════════════════════
    protected function updateConversationContextFromTools(array $toolCalls): void
    {
        foreach ($toolCalls as $tc) {
            $name = $tc['function']['name'];
            $args = json_decode($tc['function']['arguments'], true) ?? [];

            // Cập nhật last_intent
            $intentMap = [
                'search_listings' => 'Tìm kiếm tin đăng',
                'get_listing_details' => 'Xem chi tiết tin đăng',
                'create_listing' => 'Tạo tin đăng mới',
                'update_listing_status' => 'Cập nhật trạng thái tin đăng',
                'search_customers' => 'Tìm kiếm khách hàng',
                'get_customer_details' => 'Xem chi tiết khách hàng',
                'create_customer' => 'Tạo khách hàng mới',
                'get_user_performance' => 'Xem hiệu suất nhân sự',
                'get_system_stats' => 'Xem thống kê hệ thống',
            ];
            $this->conversationContext['last_intent'] = $intentMap[$name] ?? $name;

            // Cập nhật entity IDs
            if (isset($args['listing_id'])) {
                $this->addToContext('last_listing_ids', (int)$args['listing_id']);
            }
            if (isset($args['customer_id'])) {
                $this->addToContext('last_customer_ids', (int)$args['customer_id']);
            }
            if (isset($args['user_id'])) {
                $this->addToContext('last_user_ids', (int)$args['user_id']);
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // [MỚI] extractEntitiesFromResult — trích xuất ID từ kết quả tool
    // ════════════════════════════════════════════════════════════════════════
    protected function extractEntitiesFromResult(string $toolName, array $result): void
    {
        if ($result['status'] !== 'success') return;

        if (isset($result['data']) && is_array($result['data'])) {
            // search_listings trả về array các listings
            if (str_contains($toolName, 'listing')) {
                foreach (($result['data'] ?? []) as $item) {
                    if (isset($item['id'])) $this->addToContext('last_listing_ids', (int)$item['id']);
                }
            }
            if (str_contains($toolName, 'customer')) {
                foreach (($result['data'] ?? []) as $item) {
                    if (isset($item['id'])) $this->addToContext('last_customer_ids', (int)$item['id']);
                }
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // [MỚI] extractEntitiesFromResponse — trích xuất [LISTING:ID] từ response AI
    // ════════════════════════════════════════════════════════════════════════
    protected function extractEntitiesFromResponse(string $response): void
    {
        preg_match_all('/\[LISTING:(\d+)\]/', $response, $matches);
        foreach ($matches[1] as $id) {
            $this->addToContext('last_listing_ids', (int)$id);
        }

        // Reset pending_clarification nếu AI đã trả lời đầy đủ
        $this->conversationContext['pending_clarification'] = null;
    }

    // Helper: thêm ID vào context, giữ tối đa 5 phần tử
    protected function addToContext(string $key, int $id): void
    {
        $arr = $this->conversationContext[$key] ?? [];
        $arr[] = $id;
        $arr = array_values(array_unique($arr));
        if (count($arr) > 5) $arr = array_slice($arr, -5);
        $this->conversationContext[$key] = $arr;
    }

    protected function getFastPrompt(): string
    {
        $user = auth()->user();
        $rules = $this->customRules
            ? "\n\n---\n[QUY TẮC BỔ SUNG CỦA ADMIN]\n" . $this->customRules
            : '';

        return <<<PROMPT
Bạn là Trợ lý Vận hành Antigravity, đang hỗ trợ {$user->name} (ID #{$user->id}).

## Phạm vi hỗ trợ
- Tin đăng bất động sản (tạo, tìm, cập nhật trạng thái)
- Hồ sơ khách hàng (tạo, tìm, xem lịch sử)
- Nhân sự & CTV (hiệu suất, doanh thu, rank)
- Thống kê tổng quan hệ thống

Nếu ngoài phạm vi → từ chối ngắn gọn bằng 1 câu.

## Hiểu câu hỏi — QUAN TRỌNG NHẤT
- Đọc [BỘ NHỚ NGẮN HẠN] để hiểu "nó", "cái đó", "tin đó" đang chỉ đến entity nào.
- Đọc [System context] trong message user nếu có.
- Nếu câu hỏi có đại từ mà KHÔNG có context → hỏi 1 câu cụ thể.
- Nếu câu THIẾU 1 thông số bắt buộc → hỏi ĐÚNG 1 câu, không hỏi nhiều.
  VD: "Cần thêm SĐT để tạo tin. Số nào?"
- Nếu ĐỦ thông tin → gọi tool NGAY, không hỏi lại.

## Phong cách — Fast mode
- Trả lời TRỰC TIẾP. Không mở đầu bằng "Xin chào", "Tất nhiên", "Để tôi...".
- Không giải thích quy trình suy nghĩ.

## Định dạng đầu ra
- Thành công:      ✅ [kết quả 1 dòng]
- Lỗi/không tìm:   ❌ [lý do ngắn + hướng xử lý]
- TIN ĐĂNG BĐS:    LUÔN dùng [LISTING:ID]. KHÔNG dùng bảng, KHÔNG dùng danh sách text.
- Số tiền:         "2.5 Tỷ" / "850 Triệu" / "15 Triệu/tháng"
- Không in đậm toàn câu — chỉ in đậm số liệu quan trọng{$rules}
PROMPT;
    }

    protected function getSmartPrompt(): string
    {
        $user = auth()->user();
        $rules = $this->customRules
            ? "\n\n---\n[QUY TẮC BỔ SUNG CỦA ADMIN]\n" . $this->customRules
            : '';

        return <<<PROMPT
Bạn là Chuyên gia Phân tích Antigravity, đang hỗ trợ {$user->name} (ID #{$user->id}).

## Phạm vi phân tích
Chỉ phân tích dữ liệu trong hệ thống: tin đăng, khách hàng, doanh thu, hiệu suất nhân sự.

## Hiểu câu hỏi — QUAN TRỌNG NHẤT
- Đọc [BỘ NHỚ NGẮN HẠN] để hiểu tham chiếu từ cuộc hội thoại trước.
- Xác định: User muốn BIẾT gì (thông tin) hay muốn LÀM gì (hành động)?
- Nếu câu hỏi mơ hồ → gọi tool để lấy dữ liệu trước, sau đó phân tích.
- Nếu thiếu dữ liệu → thừa nhận và đề xuất góc phân tích thay thế.

## Phong cách — Smart mode
Cấu trúc cho phân tích:
  1. **Quan sát** — dữ liệu nói gì? (≤3 câu)
  2. **Nhận xét** — tại sao lại như vậy? (≤3 câu)
  3. **Khuyến nghị** — nên làm gì tiếp? (≤3 câu)

## Định dạng
- TIN ĐĂNG BĐS: LUÔN dùng [LISTING:ID]. KHÔNG dùng bảng hay danh sách text.
- Số tiền: "2.5 Tỷ" / "850 Triệu"
- Không dự báo ngoài dữ liệu hệ thống.{$rules}
PROMPT;
    }

    // ════════════════════════════════════════════════════════════════════════
    // [CẢI THIỆN] handleKeywordIntents — CHỈ xử lý lệnh CỰC KỲ rõ ràng
    // Loại bỏ các pattern mơ hồ, để AI xử lý thay
    // ════════════════════════════════════════════════════════════════════════
    protected function handleKeywordIntents(string $input): ?string
    {
        $input = mb_strtolower(trim($input));

        // ✅ Pattern 1: Đánh dấu đã bán — CHỈ khi có ID rõ ràng
        // "đã bán 42", "chốt tin 42", "đã chốt #42"
        if (preg_match('/^(?:đã bán|chốt tin|đã chốt)\s*(?:số|id|tin|#)?\s*(\d+)$/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                $listing->update(['is_sold' => true]);
                $this->addToContext('last_listing_ids', (int)$id);
                $this->conversationContext['last_intent'] = 'Đánh dấu đã bán';
                return "✅ Đã chuyển tin #{$id} sang **ĐÃ BÁN**. Chúc mừng giao dịch! 🎉";
            }
            return "❌ Không tìm thấy tin đăng #{$id}.";
        }

        // ✅ Pattern 2: Mở lại — CHỈ khi có ID rõ ràng
        if (preg_match('/^mở lại\s*(?:số|id|tin|#)?\s*(\d+)$/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                $listing->update(['is_sold' => false]);
                $this->addToContext('last_listing_ids', (int)$id);
                return "✅ Đã mở lại tin #{$id}. Trạng thái: **Còn trống**.";
            }
            return "❌ Không tìm thấy tin đăng #{$id}.";
        }

        // ✅ Pattern 3: Tìm kiếm nhanh (Listing Search)
        if (preg_match('/(?:tìm|kiếm|search|danh sách|list|xem)\s+(?:tin|nhà|đất|bđs|căn|hộ|phòng|biệt thự|kho|xưởng)?\s*(.*)/i', $input, $matches)) {
            $query = trim($matches[1]);
            if (!empty($query)) {
                $results = RealEstateListing::where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")->orWhere('address', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%")->orWhere('price', 'like', "%{$query}%");
                })->where('is_sold', false)->limit(5)->get();
                if ($results->count() > 0) {
                    $text = "🔎 Kết quả cho '{$query}':\n\n";
                    foreach($results as $r) { $text .= "[LISTING:{$r->id}]\n"; $this->addToContext('last_listing_ids', $r->id); }
                    return $text;
                }
            }
        }

        // ✅ Pattern 4: Tin giá trị nhất
        if (preg_match('/(?:tin|nhà|đất|bđs)\s+(?:giá trị nhất|đắt nhất|cao nhất|khủng nhất)/i', $input)) {
            $maxListing = RealEstateListing::where('is_sold', false)->orderBy('price', 'desc')->first();
            if ($maxListing) {
                $this->addToContext('last_listing_ids', $maxListing->id);
                return "🏆 Tin đăng có giá trị cao nhất hệ thống hiện tại:\n\n[LISTING:{$maxListing->id}]";
            }
        }

        // ✅ Pattern 5: Thống kê
        if ($input === 'thống kê' || $input === 'thống kê hệ thống') return null;

        return null;
    }

    // ════════════════════════════════════════════
    // Các methods còn lại giữ nguyên từ bản gốc
    // ════════════════════════════════════════════

    public function confirmToolCall(OpenAIService $openAIService)
    {
        if (!$this->pendingRunStateId) return;
        $state = Cache::get("hitl_state_{$this->pendingRunStateId}");
        if (!$state) {
            $this->dispatch('toast', ['message' => 'Phiên làm việc đã hết hạn.', 'type' => 'error']);
            $this->pendingRunStateId = null;
            return;
        }
        $this->messages[] = ['role' => 'user', 'content' => 'Tôi xác nhận thực hiện hành động trên.'];
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => 'Tôi xác nhận thực hiện hành động trên.']);
        $this->isTyping = true;
        $apiMessages = $state['apiMessages'];
        foreach ($state['pendingCalls'] as $toolCall) {
            $toolResult = $this->executeTool($toolCall['function']['name'], json_decode($toolCall['function']['arguments'], true));
            $apiMessages[] = ['role' => 'tool', 'tool_call_id' => $toolCall['id'], 'name' => $toolCall['function']['name'], 'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE)];
        }
        Cache::forget("hitl_state_{$this->pendingRunStateId}");
        $this->pendingRunStateId = null;
        $this->resumeRun($openAIService, $apiMessages, $state['iteration'] + 1, $state['tools']);
    }

    public function cancelToolCall(OpenAIService $openAIService)
    {
        if (!$this->pendingRunStateId) return;
        $state = Cache::get("hitl_state_{$this->pendingRunStateId}");
        if (!$state) return;
        $this->messages[] = ['role' => 'user', 'content' => 'Tôi hủy bỏ hành động này.'];
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => 'Tôi hủy bỏ hành động này.']);
        $this->isTyping = true;
        $apiMessages = $state['apiMessages'];
        foreach ($state['pendingCalls'] as $toolCall) {
            $apiMessages[] = ['role' => 'tool', 'tool_call_id' => $toolCall['id'], 'name' => $toolCall['function']['name'], 'content' => json_encode(['status' => 'error', 'message' => 'Người dùng đã từ chối.'], JSON_UNESCAPED_UNICODE)];
        }
        Cache::forget("hitl_state_{$this->pendingRunStateId}");
        $this->pendingRunStateId = null;
        $this->resumeRun($openAIService, $apiMessages, $state['iteration'] + 1, $state['tools']);
    }

    protected function resumeRun(OpenAIService $openAIService, array $apiMessages, int $iteration, array $tools)
    {
        $this->streamingResponse = '';
        $this->isTyping = true;
        $toolCalls = $openAIService->streamChat($apiMessages, function($chunk) {
            $this->streamingResponse .= $chunk;
            $this->stream(to: 'assistant-reply', content: $this->streamingResponse, replace: true);
        }, $tools);
        if (!empty($toolCalls)) {
            foreach ($toolCalls as $toolCall) {
                $toolResult = $this->executeTool($toolCall['function']['name'], json_decode($toolCall['function']['arguments'], true));
                $apiMessages[] = ['role' => 'assistant', 'content' => '', 'tool_calls' => [$toolCall]];
                $apiMessages[] = ['role' => 'tool', 'tool_call_id' => $toolCall['id'], 'name' => $toolCall['function']['name'], 'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE)];
            }
            $this->resumeRun($openAIService, $apiMessages, $iteration + 1, $tools);
            return;
        }
        if (!empty($this->streamingResponse)) {
            $this->extractEntitiesFromResponse($this->streamingResponse);
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'assistant', 'content' => $this->streamingResponse]);
            $this->messages[] = ['role' => 'assistant', 'content' => $this->streamingResponse];
        }
        $this->streamingResponse = '';
        $this->isTyping = false;
    }

    protected function getAvailableTools(string $intent = 'ALL'): array
    {
        $allTools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_listing',
                    'description' => 'Tạo tin đăng bất động sản mới vào hệ thống.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Tiêu đề tin đăng (Bắt buộc)'],
                            'price' => ['type' => 'number', 'description' => 'Giá trị (Bắt buộc)'],
                            'price_unit' => ['type' => 'string', 'enum' => ['Tỷ', 'Triệu', 'VNĐ/tháng', 'Thỏa thuận'], 'description' => 'Đơn vị giá'],
                            'area' => ['type' => 'number', 'description' => 'Diện tích m2 (Bắt buộc)'],
                            'address' => ['type' => 'string', 'description' => 'Địa chỉ chi tiết'],
                            'contact_phone' => ['type' => 'string', 'description' => 'SĐT liên hệ (Bắt buộc)'],
                            'type' => ['type' => 'string', 'enum' => ['Cần bán', 'Cho thuê', 'Cần mua'], 'description' => 'Loại tin đăng'],
                            'property_type' => ['type' => 'string', 'description' => 'Loại BĐS (Nhà phố, Đất nền, Căn hộ...)'],
                            'description' => ['type' => 'string', 'description' => 'Mô tả chi tiết nội dung'],
                            'floors' => ['type' => 'integer', 'description' => 'Số tầng'],
                            'bedrooms' => ['type' => 'integer', 'description' => 'Số phòng ngủ'],
                            'toilets' => ['type' => 'integer', 'description' => 'Số WC'],
                            'direction' => ['type' => 'string', 'description' => 'Hướng nhà'],
                        ],
                        'required' => ['title', 'price', 'area', 'contact_phone']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_customer',
                    'description' => 'Tạo hồ sơ khách hàng mới.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Họ tên khách hàng (Bắt buộc)'],
                            'phone' => ['type' => 'string', 'description' => 'SĐT khách hàng (Bắt buộc)'],
                            'status' => ['type' => 'string', 'enum' => ['khach_mua_o', 'dau_tu', 'mua', 'ban', 'dich_vu'], 'description' => 'Phân loại khách'],
                            'budget_from' => ['type' => 'number', 'description' => 'Ngân sách từ (VNĐ)'],
                            'budget_to' => ['type' => 'number', 'description' => 'Ngân sách đến (VNĐ)'],
                            'description' => ['type' => 'string', 'description' => 'Ghi chú về khách hàng'],
                        ],
                        'required' => ['name', 'phone']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_listing_status',
                    'description' => 'Cập nhật trạng thái tin đăng (đã bán/chưa bán).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'listing_id' => ['type' => 'integer', 'description' => 'ID của tin đăng'],
                            'is_sold' => ['type' => 'boolean', 'description' => 'true nếu đã bán, false nếu chưa'],
                        ],
                        'required' => ['listing_id', 'is_sold']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_listings',
                    'description' => 'Tìm kiếm nâng cao và phân tích danh sách tin đăng theo nhiều tiêu chí (giá, diện tích, sắp xếp).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Từ khóa tìm kiếm (địa chỉ, tiêu đề, mã tin)'],
                            'type' => ['type' => 'string', 'enum' => ['Cần bán', 'Cho thuê', 'Cần mua'], 'description' => 'Loại hình'],
                            'property_type' => ['type' => 'string', 'description' => 'Loại BĐS (Biệt thự, Căn hộ, Đất, Nhà phố, Khách sạn...)'],
                            'min_price' => ['type' => 'number', 'description' => 'Giá tối thiểu'],
                            'max_price' => ['type' => 'number', 'description' => 'Giá tối đa'],
                            'min_area' => ['type' => 'number', 'description' => 'Diện tích tối thiểu'],
                            'max_area' => ['type' => 'number', 'description' => 'Diện tích tối đa'],
                            'province' => ['type' => 'string', 'description' => 'Tỉnh/Thành phố'],
                            'district' => ['type' => 'string', 'description' => 'Quận/Huyện'],
                            'ward' => ['type' => 'string', 'description' => 'Phường/Xã'],
                            'direction' => ['type' => 'string', 'description' => 'Hướng nhà (Đông, Tây, Nam, Bắc, Đông Bắc...)'],
                            'is_sold' => ['type' => 'boolean', 'description' => 'Trạng thái đã bán (true/false)'],
                            'contact_phone' => ['type' => 'string', 'description' => 'Số điện thoại liên hệ'],
                            'sort_by' => ['type' => 'string', 'enum' => ['price', 'area', 'created_at'], 'description' => 'Trường sắp xếp'],
                            'sort_order' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'description' => 'Thứ tự (asc/desc)'],
                            'limit' => ['type' => 'integer', 'description' => 'Số lượng kết quả (mặc định 5, tối đa 20)'],
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_listing_details',
                    'description' => 'Xem chi tiết đầy đủ của một tin đăng bằng ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'listing_id' => ['type' => 'integer', 'description' => 'ID của tin đăng'],
                        ],
                        'required' => ['listing_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_customers',
                    'description' => 'Tìm kiếm khách hàng theo tên, SĐT hoặc trạng thái.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Từ khóa tìm kiếm (tên hoặc SĐT)'],
                            'status' => ['type' => 'string', 'enum' => ['khach_mua_o', 'dau_tu', 'mua', 'ban', 'dich_vu'], 'description' => 'Trạng thái khách hàng'],
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_customer_details',
                    'description' => 'Xem hồ sơ chi tiết và lịch sử làm việc của một khách hàng.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_id' => ['type' => 'integer', 'description' => 'ID của khách hàng'],
                        ],
                        'required' => ['customer_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_user_performance',
                    'description' => 'Xem báo cáo hiệu suất của một nhân sự hoặc CTV.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'ID của người dùng'],
                        ],
                        'required' => ['user_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_system_stats',
                    'description' => 'Xem thống kê tổng quan của hệ thống.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[]
                    ]
                ]
            ]
        ];
        return $allTools;
    }

    protected function executeTool(string $name, array $args)
    {
        try {
            switch ($name) {
                case 'create_listing':
                    if (isset($args['type'])) { $typeMap = ['bán' => 'Cần bán', 'cần bán' => 'Cần bán', 'thuê' => 'Cho thuê', 'cho thuê' => 'Cho thuê', 'mua' => 'Cần mua', 'cần mua' => 'Cần mua']; $args['type'] = $typeMap[mb_strtolower($args['type'])] ?? $args['type']; }
                    if (isset($args['price_unit'])) { $unitMap = ['tỷ' => 'Tỷ', 'tỉ' => 'Tỷ', 'triệu' => 'Triệu', 'tr' => 'Triệu', 'vnđ' => 'VNĐ/tháng']; $args['price_unit'] = $unitMap[mb_strtolower($args['price_unit'])] ?? $args['price_unit']; }
                    $data = array_merge(['type' => 'Cần bán', 'property_type' => 'Nhà phố', 'price_unit' => 'Tỷ', 'user_id' => auth()->id(), 'code' => 'AI-' . strtoupper(str()->random(6))], $args);
                    $listing = RealEstateListing::create($data);
                    $this->addToContext('last_listing_ids', $listing->id);
                    return ['status' => 'success', 'message' => "Đã tạo tin đăng. Mã: {$listing->code} (ID: #{$listing->id})"];

                case 'create_customer':
                    $data = array_merge(['code' => 'KH' . rand(1000, 9999), 'status' => 'khach_mua_o', 'assigned_user_id' => auth()->id()], $args);
                    $customer = Customer::create($data);
                    $this->addToContext('last_customer_ids', $customer->id);
                    return ['status' => 'success', 'message' => "Đã tạo khách hàng. Mã: {$customer->code} (ID: #{$customer->id})"];

                case 'update_listing_status':
                    $listing = RealEstateListing::find($args['listing_id']);
                    if (!$listing) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng ID: ' . $args['listing_id']];
                    if ($listing->user_id !== auth()->id() && !auth()->user()?->isAdmin()) return ['status' => 'error', 'message' => 'Bạn không có quyền cập nhật tin đăng này.'];
                    $listing->update(['is_sold' => $args['is_sold']]);
                    return ['status' => 'success', 'message' => 'Đã cập nhật trạng thái tin đăng.'];

                case 'search_listings':
                    $query = RealEstateListing::query();
                    if (!empty($args['query'])) {
                        $term = trim($args['query']);
                        $query->where(function($q) use ($term) {
                            if (preg_match('/^[A-ZĐ]{1,3}\d+$/i', $term)) {
                                $q->where('code', $term)->orWhere('title', 'like', '%' . $term . '%');
                            } else {
                                $q->where('title', 'like', '%' . $term . '%')
                                  ->orWhere('address', 'like', '%' . $term . '%')
                                  ->orWhere('code', 'like', '%' . $term . '%')
                                  ->orWhere('contact_phone', 'like', '%' . $term . '%');
                            }
                        });
                    }
                    if (!empty($args['type'])) $query->where('type', $args['type']);
                    if (isset($args['min_price'])) $query->where('price', '>=', $args['min_price']);
                    if (isset($args['max_price'])) $query->where('price', '<=', $args['max_price']);
                    if (isset($args['min_area'])) $query->where('area', '>=', $args['min_area']);
                    if (isset($args['max_area'])) $query->where('area', '<=', $args['max_area']);
                    
                    // Advanced Filters
                    if (!empty($args['province'])) $query->where('province_name', 'like', '%' . $args['province'] . '%');
                    if (!empty($args['district'])) $query->where('district_name', 'like', '%' . $args['district'] . '%');
                    if (!empty($args['ward'])) $query->where('ward_name', 'like', '%' . $args['ward'] . '%');
                    if (!empty($args['direction'])) $query->where('direction', 'like', '%' . $args['direction'] . '%');
                    if (isset($args['is_sold'])) $query->where('is_sold', $args['is_sold']);
                    if (!empty($args['contact_phone'])) $query->where('contact_phone', 'like', '%' . $args['contact_phone'] . '%');
                    
                    if (!empty($args['property_type'])) {
                        $pTypes = [110 => 'Bất động sản khác', 102 => 'Biệt thự', 103 => 'Căn hộ', 104 => 'Đất', 105 => 'Đất nền', 106 => 'Mặt tiền', 107 => 'Nhà mặt phố', 111 => 'Nhà mặt phố', 108 => 'Nhà riêng', 109 => 'Trang trại', 112 => 'Khách sạn', 113 => 'Nhà nghỉ', 114 => 'Homestay', 115 => 'Nhà trọ'];
                        $foundType = null;
                        foreach($pTypes as $id => $name) {
                            if (mb_stripos($name, $args['property_type']) !== false) { $foundType = $id; break; }
                        }
                        if ($foundType) $query->where('property_type', $foundType);
                    }

                    $query->orderBy($args['sort_by'] ?? 'created_at', $args['sort_order'] ?? 'desc');
                    $results = $query->limit(min($args['limit'] ?? 5, 20))->get(['id', 'title', 'price', 'price_unit', 'area', 'address', 'type', 'is_sold', 'code', 'property_type', 'avatar', 'images']);
                    return ['status' => 'success', 'count' => $results->count(), 'format_hint' => 'BẮT BUỘC dùng [LISTING:ID] cho mỗi tin đăng, KHÔNG dùng bảng.', 'data' => $results->map(fn($r) => ['id' => $r->id, 'code' => $r->code, 'title' => $r->title, 'price_display' => number_format($r->price, 0, ',', '.') . ' ' . $r->price_unit, 'area' => $r->area . ' m2', 'address' => $r->address, 'status' => $r->is_sold ? 'Đã bán' : 'Còn trống'])];

                case 'get_listing_details':
                    $listing = RealEstateListing::find($args['listing_id']);
                    if (!$listing) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng ID: ' . $args['listing_id']];
                    return ['status' => 'success', 'data' => $listing->toArray()];

                case 'search_customers':
                    $q = Customer::query();
                    if (!empty($args['query'])) { $q->where(function($sub) use ($args) { $sub->where('name', 'like', '%' . $args['query'] . '%')->orWhere('phone', 'like', '%' . $args['query'] . '%'); }); }
                    if (!empty($args['status'])) $q->where('status', $args['status']);
                    return ['status' => 'success', 'data' => $q->limit(5)->get(['id', 'name', 'phone', 'status', 'budget_from', 'budget_to'])];

                case 'get_customer_details':
                    $customer = Customer::with(['works.user'])->find($args['customer_id']);
                    if (!$customer) return ['status' => 'error', 'message' => 'Không tìm thấy khách hàng ID: ' . $args['customer_id']];
                    return ['status' => 'success', 'profile' => $customer->toArray(), 'history' => $customer->works->map(fn($w) => ['date' => $w->formatted_date, 'content' => $w->content, 'progress' => $w->progress, 'user' => $w->user?->name])];

                case 'get_user_performance':
                    $user = User::find($args['user_id']);
                    if (!$user) return ['status' => 'error', 'message' => 'Không tìm thấy người dùng ID: ' . $args['user_id']];
                    return ['status' => 'success', 'user' => ['name' => $user->name, 'phone' => $user->phone, 'total_revenue' => number_format($user->total_revenue, 0, ',', '.') . ' VNĐ', 'rank' => $user->rank?->name ?? 'N/A', 'invitees_count' => $user->invitees()->count()]];

                case 'get_system_stats':
                    return ['status' => 'success', 'data' => $this->getSystemStats()];

                default:
                    return ['status' => 'error', 'message' => 'Tool không tồn tại.'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getListingData($id)
    {
        // Try finding by ID first, then by Code
        $listing = \App\Models\RealEstateListing::with(['reporter', 'user'])
            ->where('id', $id)
            ->orWhere('code', $id)
            ->orWhere('code', 'NR' . $id) // Handle common prefix if user just types the number
            ->first();
        return $listing ? $listing->toArray() : null;
    }

    public function viewListingQuickly($id)
    {
        $listing = \App\Models\RealEstateListing::with(['reporter', 'user', 'sale.soldBy', 'sale.members.user'])->find($id);
        if (!$listing) { $this->dispatch('toast', ['message' => 'Không tìm thấy tin đăng!', 'type' => 'error']); return; }
        $this->selectedListing = $this->prepareListingForQuickView($listing);
        $this->showDetailPopup = true;
    }

    public function closeDetailQuickly()
    {
        $this->showDetailPopup = false;
        $this->selectedListing = null;
    }

    protected function prepareListingForQuickView($listing)
    {
        $data = $listing->toArray();
        if (!empty($data['contact_phone'])) {
            $customer = \App\Models\Customer::where('phone', $data['contact_phone'])->orWhere('phone2', $data['contact_phone'])->first(['name']);
            if ($customer) $data['contact_customer_name'] = $customer->name;
        }
        $allImages = [];
        if (!empty($data['avatar'])) $allImages[] = $data['avatar'];
        if (!empty($data['images']) && is_array($data['images'])) { foreach ($data['images'] as $img) { if ($img !== $data['avatar']) $allImages[] = $img; } }
        $data['display_images'] = count($allImages) > 0 ? $allImages : ['https://placehold.co/800x600?text=No+Image'];
        return $data;
    }

    protected function getSystemStats(): string
    {
        return Cache::remember('system_ctx_stats', 60, function () {
            $maxListing = RealEstateListing::where('is_sold', false)->orderBy('price', 'desc')->first(['id', 'code', 'price', 'price_unit', 'title']);
            $listingStats = ['total' => RealEstateListing::count(), 'sold' => RealEstateListing::where('is_sold', true)->count(), 'available' => RealEstateListing::where('is_sold', false)->count(), 'max_listing' => $maxListing, 'by_type' => RealEstateListing::selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type')->toArray()];
            $customerStats = ['total' => Customer::count(), 'by_status' => Customer::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(), 'pending_tasks' => \App\Models\CustomerWork::where('progress', '!=', 'Hoàn thành')->count()];
            $userStats = ['total' => User::count(), 'admins' => User::where('phone', User::ADMIN_PHONE)->count(), 'ctvs' => User::where('phone', '!=', User::ADMIN_PHONE)->count()];
            $storageStats = ['files' => \App\Models\File::count(), 'folders' => \App\Models\Folder::count()];
            $maxInfo = $listingStats['max_listing'] ? "[LISTING:{$listingStats['max_listing']->id}] (Mã: {$listingStats['max_listing']->code}) - " . number_format($listingStats['max_listing']->price, 0, ',', '.') . " {$listingStats['max_listing']->price_unit}" : "N/A";
            return "THỐNG KÊ HỆ THỐNG:\n- Tin đăng: {$listingStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($listingStats['by_type']), $listingStats['by_type'])) . "). Đã bán: {$listingStats['sold']}.\n- Tin giá trị nhất: {$maxInfo}\n- Khách hàng: {$customerStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($customerStats['by_status']), $customerStats['by_status'])) . "). Tồn đọng: {$customerStats['pending_tasks']}.\n- Nhân sự: {$userStats['total']} ({$userStats['admins']} Admin, {$userStats['ctvs']} CTV).\n- Lưu trữ: {$storageStats['files']} tệp, {$storageStats['folders']} thư mục.";
        });
    }

    protected function summarizeHistory()
    {
        $openAIService = app(OpenAIService::class);
        $toSummarize = array_slice($this->messages, 0, -8); // Giữ lại 8 thay vì 5
        $prompt = [
            ['role' => 'system', 'content' => 'Tóm tắt cuộc hội thoại dưới 120 chữ. Giữ lại: ID tin đăng/khách hàng được nhắc đến, các quyết định quan trọng, thông tin đang chờ xác nhận.'],
            ['role' => 'user', 'content' => json_encode($toSummarize, JSON_UNESCAPED_UNICODE)]
        ];
        $response = $openAIService->chat($prompt);
        $summary = $response['choices'][0]['message']['content'] ?? 'Cuộc hội thoại trước.';
        $this->messages = array_merge(
            [['role' => 'system', 'content' => '[TÓM TẮT HỘI THOẠI TRƯỚC]: ' . $summary]],
            array_slice($this->messages, -8)
        );
    }

    public function clearChat()
    {
        \App\Models\ChatMessage::where('user_id', auth()->id())->delete();
        $this->conversationContext = ['last_listing_ids' => [], 'last_customer_ids' => [], 'last_user_ids' => [], 'last_intent' => null, 'pending_clarification' => null];
        $this->loadHistory();
    }

    public function render()
    {
        return view('livewire.chatbot')->layout('components.layouts.app');
    }
}