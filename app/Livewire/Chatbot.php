<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OpenAIService;
use App\Models\RealEstateListing;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use Livewire\WithFileUploads;

class Chatbot extends Component
{
    use WithFileUploads;

    public string $userInput = '';
    public array $messages = [];
    public bool $isTyping = false;
    public $chatFiles = []; // Temporary uploads
    public bool $isPopup = false;
    
    // Custom Rules Management
    public string $customRules = '';
    public bool $showRulesEditor = false;

    // HITL (Human-In-The-Loop) State
    public ?string $pendingRunStateId = null;

    // Quick View Listing Detail
    public $selectedListing = null;
    public bool $showDetailPopup = false;


    public function mount()
    {
        $this->loadRules();
        $this->loadHistory();
    }

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
        } else {
            $this->messages = [
                [
                    'role' => 'assistant',
                    'content' => 'Xin chào! Tôi là Antigravity Admin AI. Tôi có thể giúp bạn quản lý hệ thống, thống kê dữ liệu hoặc giải đáp các thắc mắc về quy trình tin đăng. Hôm nay tôi có thể giúp gì cho bạn?'
                ]
            ];
        }
    }

    public function loadRules()
    {
        $path = storage_path('app/chatbot_rules.md');
        $this->customRules = file_exists($path) ? file_get_contents($path) : '';
    }

    public function saveRules()
    {
        if (!auth()->user()?->isAdmin()) {
            return;
        }

        $path = storage_path('app/chatbot_rules.md');
        file_put_contents($path, $this->customRules);
        
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

    public string $streamingResponse = ''; // Holds the current chunked response

    public function updatedChatFiles()
    {
        try {
            $this->validate([
                'chatFiles.*' => 'image|max:2048', // 2MB Max, only images
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->chatFiles = []; // Clear failed uploads
            $this->dispatch('toast', ['message' => 'Lỗi: Tệp phải là ảnh và dung lượng tối đa 2MB.', 'type' => 'error']);
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->userInput)) && empty($this->chatFiles)) return;

        $userMessage = $this->userInput;
        
        // Phase 1: Rapid Intent Check (Bypass AI for keywords)
        $intentHandled = $this->handleKeywordIntents($userMessage);
        if ($intentHandled) {
            $this->messages[] = ['role' => 'user', 'content' => $userMessage];
            $this->messages[] = ['role' => 'assistant', 'content' => $intentHandled];
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => $userMessage]);
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'assistant', 'content' => $intentHandled]);
            $this->userInput = '';
            return;
        }

        $fileInfo = "";
        // Process File Uploads (Synchronous for now, but fast)
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

        $fullUserContent = $userMessage . $fileInfo;
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => $fullUserContent]);

        // INSTANT UI UPDATE
        $this->messages[] = [
            'role' => 'user', 
            'content' => $userMessage ?: "Đã gửi tệp tin.",
            'has_files' => count($this->chatFiles)
        ];

        $this->userInput = '';
        $this->chatFiles = [];
        $this->isTyping = true;
        $this->streamingResponse = '';

        // DISPATCH AI PROCESSING (Separate request)
        $this->dispatch('trigger-ai-response', userMessage: $userMessage, fullUserContent: $fullUserContent);
    }

    #[\Livewire\Attributes\On('trigger-ai-response')]
    public function generateAIResponse($userMessage, $fullUserContent)
    {
        $openAIService = app(OpenAIService::class);
        
        // Rate Limiting
        $rateKey = 'chatbot_limit_' . auth()->id();
        $hits = \Illuminate\Support\Facades\Cache::get($rateKey, 0);
        if ($hits > 50) {
            $this->messages[] = ['role' => 'assistant', 'content' => '⚠️ Quá giới hạn (50 tin/giờ). Thử lại sau.'];
            $this->isTyping = false; return;
        }
        \Illuminate\Support\Facades\Cache::put($rateKey, $hits + 1, 3600);

        // Summarize if history is long
        if (count($this->messages) > 25) $this->summarizeHistory();

        // Phase 3: Auto-Switch Mode (FAST vs SMART)
        $mode = $this->detectMode($userMessage);
        $this->dispatch('mode-detected', mode: $mode);

        $context = $this->getCachedSystemContext($mode);
        $tools = ($mode === 'FAST') ? $this->getAvailableTools('GENERAL') : $this->getAvailableTools('ALL');

        // Prepare API Messages (Last 6 to save tokens)
        $apiMessages = array_map(fn($m) => ['role' => $m['role'], 'content' => $m['content']], array_slice($this->messages, -6));
        if (!empty($apiMessages) && $apiMessages[count($apiMessages) - 1]['role'] === 'user') {
            $apiMessages[count($apiMessages) - 1]['content'] = $fullUserContent;
        }
        array_unshift($apiMessages, ['role' => 'system', 'content' => $context]);

        // Phase 4: Unified Streaming Execution (1-Roundtrip focused)
        $pendingCalls = [];
        $toolCalls = $openAIService->streamChat($apiMessages, function($chunk) {
            $this->streamingResponse .= $chunk;
            $this->stream(to: 'assistant-reply', content: $chunk);
        }, $tools);

        // Handle Tool Calls if detected during stream
        if (!empty($toolCalls)) {
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
                    $toolFeedback .= ($result['message'] ?? 'Đã xử lý.') . "\n";
                    $toolMessages[] = ['role' => 'tool', 'tool_call_id' => $toolCall['id'], 'name' => $toolName, 'content' => json_encode($result, JSON_UNESCAPED_UNICODE)];
                }
            }

            if ($requiresConfirmation) {
                // Ensure the assistant message with tool_calls is in the context
                $apiMessages[] = ['role' => 'assistant', 'content' => $this->streamingResponse, 'tool_calls' => $toolCalls];
                
                $runStateId = uniqid('run_', true);
                \Illuminate\Support\Facades\Cache::put("hitl_state_{$runStateId}", [
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

            // Phase 5: Single Roundtrip Optimization
            $hasComplexData = false;
            foreach ($toolCalls as $tc) {
                if (str_contains($tc['function']['name'], 'search') || str_contains($tc['function']['name'], 'get_details')) {
                    $hasComplexData = true; break;
                }
            }
            
            if ($mode === 'SMART' || $hasComplexData) {
                $apiMessages[] = ['role' => 'assistant', 'content' => $this->streamingResponse, 'tool_calls' => $toolCalls];
                foreach ($toolMessages as $tm) $apiMessages[] = $tm;
                
                $this->streamingResponse = ''; // Reset for summary
                $openAIService->streamChat($apiMessages, function($chunk) {
                    $this->streamingResponse .= $chunk;
                    $this->stream(to: 'assistant-reply', content: $chunk);
                });
            } else {
                // For simple actions in FAST mode, just show the tool feedback directly
                $feedback = "\n✅ " . trim($toolFeedback);
                $this->streamingResponse .= $feedback;
                $this->stream(to: 'assistant-reply', content: $feedback);
            }
        }

        if (!empty($this->streamingResponse)) {
            $this->messages[] = ['role' => 'assistant', 'content' => $this->streamingResponse];
            \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'assistant', 'content' => $this->streamingResponse]);
        }
        $this->isTyping = false;
        $this->streamingResponse = '';
    }

    protected function detectMode(string $input): string
    {
        $input = mb_strtolower($input);
        
        // 🧠 SMART KEYWORDS (phân tích, so sánh sâu, xu hướng...)
        $smartPatterns = ['phân tích', 'so sánh', 'đánh giá', 'xu hướng', 'thị trường', 'tại sao', 'nên mua', 'đầu tư', 'tối ưu', 'hiệu suất'];
        foreach ($smartPatterns as $kw) { if (str_contains($input, $kw)) return 'SMART'; }

        // ⚡ FAST KEYWORDS (CRUD / thao tác nhanh / hỏi đáp số liệu sẵn có)
        $fastPatterns = ['tạo', 'thêm', 'update', 'cập nhật', 'chốt', 'đã bán', 'mở lại', 'xem tin', 'chi tiết', 'id', 'tìm tin', 'search', 'bao nhiêu', 'tổng', 'thống kê'];
        foreach ($fastPatterns as $kw) { if (str_contains($input, $kw)) return 'FAST'; }

        return 'FAST';
    }

    protected function getCachedSystemContext(string $mode = 'FAST'): string
    {
        $userId = auth()->id();
        return \Illuminate\Support\Facades\Cache::remember("chatbot_context_{$mode}_{$userId}", 300, function() use ($mode) {
            $stats = $this->getSystemStats();
            $prompt = ($mode === 'FAST') ? $this->getFastPrompt() : $this->getSmartPrompt();
            return "{$prompt}\n\n[DỮ LIỆU HỆ THỐNG - TRẢ LỜI NGAY DỰA TRÊN ĐÂY]\n{$stats}";
        });
    }

    protected function getFastPrompt(): string
    {
        $user = auth()->user();
        $rules = $this->customRules
            ? "\n\n---\n[QUY TẮC BỔ SUNG CỦA ADMIN]\n" . $this->customRules
            : '';

        return <<<PROMPT
Bạn là Trợ lý Vận hành Antigravity, đang hỗ trợ nhân sự {$user->name} (ID #{$user->id}).

Bạn CHỈ hỗ trợ các nghiệp vụ:
- Tin đăng bất động sản (tạo, tìm, cập nhật trạng thái)
- Hồ sơ khách hàng (tạo, tìm, xem lịch sử)
- Nhân sự & CTV (hiệu suất, doanh thu, rank)
- Thống kê tổng quan hệ thống

Nếu câu hỏi hoàn toàn nằm ngoài phạm vi trên, từ chối ngắn gọn:
"Tôi chỉ hỗ trợ nghiệp vụ trong hệ thống Antigravity. Bạn cần giúp gì về tin đăng hay khách hàng không?"

## Phong cách — Fast mode
- Trả lời TRỰC TIẾP. Không mở đầu bằng "Xin chào", "Tất nhiên", "Để tôi...".
- Không giải thích quy trình suy nghĩ. Không "Chain-of-thought".
- Đủ thông tin → gọi tool ngay, không hỏi lại.
- Thiếu 1 trường bắt buộc → hỏi đúng 1 câu, cụ thể.
  Ví dụ: "Cần thêm SĐT liên hệ để tạo tin. Số nào?"

## Định dạng đầu ra
- Thành công:    ✅ [kết quả 1 dòng + Mã tin nếu có]
- Lỗi/không tìm: ❌ [lý do ngắn] + gợi ý bước tiếp nếu có
- Danh sách ≥3:  dùng markdown table với cột Mã tin | Tiêu đề | Giá | Trạng thái (CHỈ dùng cho Khách hàng/Nhân sự, KHÔNG dùng cho Tin đăng BĐS).
- TIN ĐĂNG BĐS:  LUÔN dùng định dạng [LISTING:ID] để hiển thị thẻ tóm tắt. KHI NGƯỜI DÙNG CẦN XEM CHI TIẾT (xem kỹ, xem hết, thông tin đầy đủ), hãy gọi tool `get_listing_details` và LIỆT KÊ TẤT CẢ thông tin (Mô tả, Link MXH, Tọa độ, Thông số kỹ thuật...) dưới dạng danh sách hoặc bảng để người dùng nắm rõ.
- Số tiền:       "2.5 Tỷ" / "850 Triệu" / "15 Triệu/tháng"
- Không in đậm toàn câu — chỉ in đậm số liệu quan trọng

## Xử lý lỗi tool
Tool trả về status "error":
→ Báo lỗi bằng tiếng Việt.
→ Đề xuất hướng xử lý nếu có.
Ví dụ: "❌ Không tìm thấy tin #99. Bạn muốn tìm theo địa chỉ hoặc SĐT không?"

## Giới hạn quyền
- Không tự xóa dữ liệu nếu không có tool delete tường minh.
- Cập nhật is_sold = true là không thể hoàn tác dễ dàng — confirm lại Mã tin/ID trước khi thực hiện nếu user chưa nêu rõ.{$rules}
PROMPT;
    }

    protected function getSmartPrompt(): string
    {
        $user = auth()->user();
        $rules = $this->customRules
            ? "\n\n---\n[QUY TẮC BỔ SUNG CỦA ADMIN]\n" . $this->customRules
            : '';

        return <<<PROMPT
Bạn là Chuyên gia Phân tích Antigravity, đang hỗ trợ cấp cao {$user->name} (ID #{$user->id}).

Bạn CHỈ phân tích dữ liệu trong hệ thống: tin đăng, khách hàng, doanh thu, hiệu suất nhân sự. Không so sánh với thị trường bên ngoài trừ khi user cung cấp số liệu cụ thể.

## Phong cách — Smart mode
Cấu trúc bắt buộc cho mọi phân tích:
  1. Quan sát  — dữ liệu nói gì?
  2. Nhận xét  — tại sao lại như vậy?
  3. Khuyến nghị — nên làm gì tiếp theo?

Giữ mỗi phần ≤3 câu. Kết thúc bằng mục **Khuyến nghị:** in đậm.
Không vòng vo, không liệt kê hiển nhiên, không giả định số liệu ngoài những gì tool trả về.

## Định dạng đầu ra
- Báo cáo: markdown với ## heading rõ ràng
- Bảng so sánh: markdown table, căn chỉnh số phải (Dùng cột Mã tin thay vì ID cho BĐS nếu cần liệt kê).
- Chỉ số nổi bật: in đậm con số (ví dụ: doanh thu **2.3 Tỷ**)
- Không in đậm toàn câu
- TIN ĐĂNG BĐS: LUÔN dùng định dạng [LISTING:ID] để hiển thị thẻ tóm tắt. KHI NGƯỜI DÙNG CẦN XEM CHI TIẾT (xem kỹ, xem hết, thông tin đầy đủ), hãy gọi tool `get_listing_details` và LIỆT KÊ TẤT CẢ thông tin dưới dạng báo cáo chuyên sâu.
- Số tiền: "2.5 Tỷ" / "850 Triệu"

## Xử lý khi dữ liệu thiếu
Nếu tool trả về rỗng hoặc lỗi:
→ Thừa nhận giới hạn dữ liệu.
→ Đề xuất góc phân tích thay thế khả thi.
Ví dụ: "Chưa có giao dịch tháng này. Tôi có thể phân tích theo quý hoặc so sánh doanh thu theo loại BĐS — bạn muốn góc nào?"

## Giới hạn
- Không dự báo thị trường ngoài dữ liệu hệ thống.
- Không đề xuất giá bán/mua cụ thể trừ khi có đủ data tham chiếu.{$rules}
PROMPT;
    }

    public function confirmToolCall(OpenAIService $openAIService)
    {
        if (!$this->pendingRunStateId) return;

        $state = \Illuminate\Support\Facades\Cache::get("hitl_state_{$this->pendingRunStateId}");
        if (!$state) {
            $this->dispatch('toast', ['message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'type' => 'error']);
            $this->pendingRunStateId = null;
            return;
        }

        $apiMessages = $state['apiMessages'];
        $pendingCalls = $state['pendingCalls'];
        $iteration = $state['iteration'];
        $tools = $state['tools'];

        $this->messages[] = ['role' => 'user', 'content' => 'Tôi xác nhận thực hiện hành động trên.'];
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => 'Tôi xác nhận thực hiện hành động trên.']);

        $this->isTyping = true;
        
        foreach ($pendingCalls as $toolCall) {
            $toolResult = $this->executeTool($toolCall['function']['name'], json_decode($toolCall['function']['arguments'], true));
            $apiMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'name' => $toolCall['function']['name'],
                'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE)
            ];
        }

        \Illuminate\Support\Facades\Cache::forget("hitl_state_{$this->pendingRunStateId}");
        $this->pendingRunStateId = null;
        
        $this->resumeRun($openAIService, $apiMessages, $iteration + 1, $tools);
    }

    public function cancelToolCall(OpenAIService $openAIService)
    {
        if (!$this->pendingRunStateId) return;
        
        $state = \Illuminate\Support\Facades\Cache::get("hitl_state_{$this->pendingRunStateId}");
        if (!$state) return;

        $apiMessages = $state['apiMessages'];
        $pendingCalls = $state['pendingCalls'];
        $iteration = $state['iteration'];
        $tools = $state['tools'];

        $this->messages[] = ['role' => 'user', 'content' => 'Tôi hủy bỏ hành động này.'];
        \App\Models\ChatMessage::create(['user_id' => auth()->id(), 'role' => 'user', 'content' => 'Tôi hủy bỏ hành động này.']);

        $this->isTyping = true;
        
        foreach ($pendingCalls as $toolCall) {
            $apiMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'name' => $toolCall['function']['name'],
                'content' => json_encode(['status' => 'error', 'message' => 'Người dùng đã từ chối thực hiện hành động này.'], JSON_UNESCAPED_UNICODE)
            ];
        }

        \Illuminate\Support\Facades\Cache::forget("hitl_state_{$this->pendingRunStateId}");
        $this->pendingRunStateId = null;

        $this->resumeRun($openAIService, $apiMessages, $iteration + 1, $tools);
    }

    protected function resumeRun(OpenAIService $openAIService, array $apiMessages, int $iteration, array $tools)
    {
        $this->streamingResponse = '';
        $this->isTyping = true;

        // Note: $apiMessages already contains: [..., User, Assistant (tool_calls), Tool (result)]
        // We just need one more streamChat call to get the final answer.
        $toolCalls = $openAIService->streamChat($apiMessages, function($chunk) {
            $this->streamingResponse .= $chunk;
            // Use replace: true to update the stream target correctly
            $this->stream(to: 'assistant-reply', content: $this->streamingResponse, replace: true);
        }, $tools);

        // If it somehow calls MORE tools (recursive), we execute them automatically for now to avoid loop
        if (!empty($toolCalls)) {
            foreach ($toolCalls as $toolCall) {
                $toolResult = $this->executeTool($toolCall['function']['name'], json_decode($toolCall['function']['arguments'], true));
                $apiMessages[] = ['role' => 'assistant', 'content' => '', 'tool_calls' => [$toolCall]];
                $apiMessages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $toolCall['function']['name'],
                    'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE)
                ];
            }
            // One last call for the final summary after recursive tools
            $this->resumeRun($openAIService, $apiMessages, $iteration + 1, $tools);
            return;
        }

        if (!empty($this->streamingResponse)) {
            \App\Models\ChatMessage::create([
                'user_id' => auth()->id(),
                'role' => 'assistant',
                'content' => $this->streamingResponse
            ]);
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
                            'query' => ['type' => 'string', 'description' => 'Từ khóa tìm kiếm (địa chỉ, tiêu đề)'],
                            'type' => ['type' => 'string', 'enum' => ['Cần bán', 'Cho thuê', 'Cần mua'], 'description' => 'Loại hình'],
                            'min_price' => ['type' => 'number', 'description' => 'Giá tối thiểu'],
                            'max_price' => ['type' => 'number', 'description' => 'Giá tối đa'],
                            'min_area' => ['type' => 'number', 'description' => 'Diện tích tối thiểu'],
                            'max_area' => ['type' => 'number', 'description' => 'Diện tích tối đa'],
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
                    'description' => 'Xem báo cáo hiệu suất của một nhân sự hoặc CTV (Doanh thu, Rank, số lượng mời).',
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
                    'description' => 'Xem thống kê tổng quan của hệ thống (Số lượng tin đăng, khách hàng, nhân sự, giá trị cao nhất).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[]
                    ]
                ]
            ]
        ];

        // Phase 3: Tool Filtering by Intent (Optional, but good for performance)
        if ($intent === 'LISTING') {
            return array_values(array_filter($allTools, fn($t) => in_array($t['function']['name'], ['create_listing', 'search_listings', 'get_listing_details', 'update_listing_status', 'delete_listing'])));
        }
        if ($intent === 'CUSTOMER') {
            return array_values(array_filter($allTools, fn($t) => in_array($t['function']['name'], ['create_customer', 'search_customers', 'get_customer_details'])));
        }
        if ($intent === 'ANALYTICS') {
            return array_values(array_filter($allTools, fn($t) => in_array($t['function']['name'], ['get_user_performance', 'get_system_stats'])));
        }

        return $allTools;
    }


    protected function handleKeywordIntents(string $input): ?string
    {
        $input = mb_strtolower($input);
        

        // 2. Mark as Sold (Pattern: đã bán [id] / chốt tin [id])
        if (preg_match('/(?:đã bán|chốt tin|đã chốt)\s*(?:số|id)?\s*(\d+)/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                $listing->update(['is_sold' => true]);
                return "✅ Đã chuyển trạng thái tin đăng ID #{$id} sang 'ĐÃ BÁN'. Chúc mừng giao dịch thành công! 🎉";
            }
            return "❌ Không tìm thấy tin đăng ID #{$id} để cập nhật.";
        }

        // 3. Mark as Available (Pattern: mở lại [id])
        if (preg_match('/mở lại\s*(?:số|id)?\s*(\d+)/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                $listing->update(['is_sold' => false]);
                return "✅ Đã mở lại tin đăng ID #{$id}. Tin hiện đang ở trạng thái 'Chưa bán'.";
            }
            return "❌ Không tìm thấy tin đăng ID #{$id} để mở lại.";
        }

        // 5. Search Listing (Pattern: tìm tin [query])
        if (preg_match('/(?:tìm tin|tìm kiếm tin|search)\s+(.+)/i', $input, $matches)) {
            $query = trim($matches[1]);
            $results = RealEstateListing::where('title', 'like', "%{$query}%")
                ->orWhere('address', 'like', "%{$query}%")
                ->orWhere('contact_phone', 'like', "%{$query}%")
                ->limit(3)
                ->get();
            
            if ($results->count() > 0) {
                $text = "🔎 Kết quả tìm kiếm cho '{$query}':\n";
                foreach($results as $r) {
                    $text .= "[LISTING:{$r->id}]\n";
                }
                return $text;
            }
            return "❌ Không tìm thấy tin đăng nào khớp với từ khóa '{$query}'.";
        }

        // 6. Quick Stats (Pattern: thống kê)
        if ($input === 'thống kê' || $input === 'thống kê hệ thống') {
            return null; // Let AI handle the full stats analysis as it's better at formatting it
        }

        return null;
    }


    protected function executeTool(string $name, array $args)
    {
        try {
            switch ($name) {
                case 'create_listing':
                    // Normalize 'type'
                    if (isset($args['type'])) {
                        $typeMap = [
                            'bán' => 'Cần bán', 'cần bán' => 'Cần bán', 'bán nhà' => 'Cần bán',
                            'thuê' => 'Cho thuê', 'cho thuê' => 'Cho thuê',
                            'mua' => 'Cần mua', 'cần mua' => 'Cần mua'
                        ];
                        $args['type'] = $typeMap[mb_strtolower($args['type'])] ?? $args['type'];
                    }

                    // Normalize 'price_unit'
                    if (isset($args['price_unit'])) {
                        $unitMap = ['tỷ' => 'Tỷ', 'tỉ' => 'Tỷ', 'triệu' => 'Triệu', 'tr' => 'Triệu', 'vnđ' => 'VNĐ/tháng'];
                        $args['price_unit'] = $unitMap[mb_strtolower($args['price_unit'])] ?? $args['price_unit'];
                    }

                    $data = array_merge([
                        'type' => 'Cần bán',
                        'property_type' => 'Nhà phố',
                        'price_unit' => 'Tỷ',
                        'user_id' => auth()->id(),
                        'code' => 'AI-' . strtoupper(str()->random(6))
                    ], $args);
                    $listing = RealEstateListing::create($data);
                    return ['status' => 'success', 'message' => "Đã tạo tin đăng thành công. Mã tin: {$listing->code} (ID: #{$listing->id})"];


                case 'create_customer':
                    $data = array_merge([
                        'code' => 'KH' . rand(1000, 9999),
                        'status' => 'khach_mua_o',
                        'assigned_user_id' => auth()->id()
                    ], $args);
                    $customer = Customer::create($data);
                    return ['status' => 'success', 'message' => "Đã tạo hồ sơ khách hàng thành công. Mã KH: {$customer->code} (ID: #{$customer->id})"];

                case 'update_listing_status':
                    $listing = RealEstateListing::find($args['listing_id']);
                    if (!$listing) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng ID: ' . $args['listing_id']];
                    if ($listing->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
                        return ['status' => 'error', 'message' => 'Bạn không có quyền cập nhật tin đăng này.'];
                    }
                    $listing->update(['is_sold' => $args['is_sold']]);
                    return ['status' => 'success', 'message' => 'Đã cập nhật trạng thái tin đăng thành công.'];


                case 'search_listings':
                    $query = RealEstateListing::query();
                    
                    if (!empty($args['query'])) {
                        $query->where(function($q) use ($args) {
                            $q->where('title', 'like', '%' . $args['query'] . '%')
                              ->orWhere('address', 'like', '%' . $args['query'] . '%')
                              ->orWhere('contact_phone', 'like', '%' . $args['query'] . '%');
                        });
                    }

                    if (!empty($args['type'])) $query->where('type', $args['type']);
                    if (isset($args['min_price'])) $query->where('price', '>=', $args['min_price']);
                    if (isset($args['max_price'])) $query->where('price', '<=', $args['max_price']);
                    if (isset($args['min_area'])) $query->where('area', '>=', $args['min_area']);
                    if (isset($args['max_area'])) $query->where('area', '<=', $args['max_area']);
                    
                    $sortBy = $args['sort_by'] ?? 'created_at';
                    $sortOrder = $args['sort_order'] ?? 'desc';
                    $query->orderBy($sortBy, $sortOrder);

                    $limit = min($args['limit'] ?? 5, 20);
                    $results = $query->limit($limit)->get([
                        'id', 'title', 'price', 'price_unit', 'area', 'address', 'type', 'is_sold', 
                        'code', 'contact_type', 'property_type', 'avatar', 'images'
                    ]);
                    
                    return [
                        'status' => 'success', 
                        'count' => $results->count(),
                        'data' => $results->map(fn($r) => [
                            'id' => $r->id,
                            'code' => $r->code,
                            'title' => $r->title,
                            'price_display' => number_format($r->price, 0, ',', '.') . ' ' . $r->price_unit,
                            'area' => $r->area . ' m2',
                            'address' => $r->address,
                            'status' => $r->is_sold ? 'Đã bán' : 'Còn trống'
                        ])
                    ];

                case 'get_listing_details':
                    $listing = RealEstateListing::find($args['listing_id']);
                    if (!$listing) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng ID: ' . $args['listing_id']];
                    return [
                        'status' => 'success',
                        'data' => $listing->toArray()
                    ];

                case 'search_customers':
                    $q = Customer::query();
                    if (!empty($args['query'])) {
                        $q->where(function($sub) use ($args) {
                            $sub->where('name', 'like', '%' . $args['query'] . '%')
                                ->orWhere('phone', 'like', '%' . $args['query'] . '%');
                        });
                    }
                    if (!empty($args['status'])) {
                        $q->where('status', $args['status']);
                    }
                    $results = $q->limit(5)->get(['id', 'name', 'phone', 'status', 'budget_from', 'budget_to']);
                    return ['status' => 'success', 'data' => $results];

                case 'get_customer_details':
                    $customer = Customer::with(['works.user'])->find($args['customer_id']);
                    if (!$customer) return ['status' => 'error', 'message' => 'Không tìm thấy khách hàng ID: ' . $args['customer_id']];
                    return [
                        'status' => 'success',
                        'profile' => $customer->toArray(),
                        'history' => $customer->works->map(fn($w) => [
                            'date' => $w->formatted_date,
                            'content' => $w->content,
                            'progress' => $w->progress,
                            'user' => $w->user?->name
                        ])
                    ];

                case 'get_user_performance':
                    $user = User::find($args['user_id']);
                    if (!$user) return ['status' => 'error', 'message' => 'Không tìm thấy người dùng ID: ' . $args['user_id']];
                    return [
                        'status' => 'success',
                        'user' => [
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'total_revenue' => number_format($user->total_revenue, 0, ',', '.') . ' VNĐ',
                            'rank' => $user->rank?->name ?? 'N/A',
                            'invitees_count' => $user->invitees()->count()
                        ]
                    ];

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
        $listing = \App\Models\RealEstateListing::with(['reporter', 'user'])->find($id);
        return $listing ? $listing->toArray() : null;
    }

    public function viewListingQuickly($id)
    {
        $listing = \App\Models\RealEstateListing::with(['reporter', 'user', 'sale.soldBy', 'sale.members.user'])->find($id);
        if (!$listing) {
            $this->dispatch('toast', ['message' => 'Không tìm thấy tin đăng!', 'type' => 'error']);
            return;
        }

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

        // Fetch customer name by contact_phone
        if (!empty($data['contact_phone'])) {
            $customer = \App\Models\Customer::where('phone', $data['contact_phone'])
                ->orWhere('phone2', $data['contact_phone'])
                ->first(['name']);
            if ($customer) {
                $data['contact_customer_name'] = $customer->name;
            }
        }

        // Prepare slider images: Avatar first, then others
        $allImages = [];
        if (!empty($data['avatar'])) {
            $allImages[] = $data['avatar'];
        }
        
        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                if ($img !== $data['avatar']) {
                    $allImages[] = $img;
                }
            }
        }
        
        $data['display_images'] = count($allImages) > 0 ? $allImages : ['https://placehold.co/800x600?text=No+Image'];
        
        return $data;
    }






    protected function getSystemStats(): string
    {
        return \Illuminate\Support\Facades\Cache::remember('system_ctx_stats', 60, function () {
            $listingStats = [
                'total' => RealEstateListing::count(),
                'sold' => RealEstateListing::where('is_sold', true)->count(),
                'available' => RealEstateListing::where('is_sold', false)->count(),
                'max_price' => RealEstateListing::max('price'),
                'by_type' => RealEstateListing::selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(),
            ];
            
            $customerStats = [
                'total' => Customer::count(),
                'by_status' => Customer::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
                'pending_tasks' => \App\Models\CustomerWork::where('progress', '!=', 'Hoàn thành')->count(),
            ];
            
            $userStats = [
                'total' => User::count(),
                'admins' => User::where('phone', User::ADMIN_PHONE)->count(),
                'ctvs' => User::where('phone', '!=', User::ADMIN_PHONE)->count(),
            ];
            
            $storageStats = [
                'files' => \App\Models\File::count(),
                'folders' => \App\Models\Folder::count(),
            ];

            return "THỐNG KÊ HỆ THỐNG REAL-TIME:\n" .
                "- Tin đăng: {$listingStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($listingStats['by_type']), $listingStats['by_type'])) . "). Đã bán: {$listingStats['sold']}.\n" .
                "- Giá trị tin đăng cao nhất: " . number_format($listingStats['max_price'], 0, ',', '.') . " Tỷ (hoặc đơn vị tương đương).\n" .
                "- Khách hàng: {$customerStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($customerStats['by_status']), $customerStats['by_status'])) . "). Công việc tồn đọng: {$customerStats['pending_tasks']}.\n" .
                "- Nhân sự: {$userStats['total']} thành viên ({$userStats['admins']} Admin, {$userStats['ctvs']} CTV).\n" .
                "- Lưu trữ: {$storageStats['files']} tệp tin, {$storageStats['folders']} thư mục.";
        });
    }

    protected function summarizeHistory()
    {
        $openAIService = app(OpenAIService::class);
        $toSummarize = array_slice($this->messages, 0, -5);
        $prompt = [
            ['role' => 'system', 'content' => 'Hãy tóm tắt cuộc hội thoại sau thành một đoạn văn ngắn (dưới 100 chữ) để làm ngữ cảnh cho AI. Giữ lại các thông tin quan trọng như ID tin đăng đang thảo luận hoặc tên khách hàng.'],
            ['role' => 'user', 'content' => json_encode($toSummarize, JSON_UNESCAPED_UNICODE)]
        ];

        $response = $openAIService->chat($prompt);
        $summary = $response['choices'][0]['message']['content'] ?? 'Cuộc hội thoại trước đó.';

        // Replace old messages with summary
        $this->messages = array_merge(
            [['role' => 'system', 'content' => 'Tóm tắt hội thoại trước đó: ' . $summary]],
            array_slice($this->messages, -5)
        );
    }







    public function clearChat()
    {
        \App\Models\ChatMessage::where('user_id', auth()->id())->delete();
        $this->loadHistory();
    }


    public function render()
    {
        return view('livewire.chatbot')->layout('components.layouts.app');
    }
}
