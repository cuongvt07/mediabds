<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GroqService;
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
    
    // Custom Rules Management
    public string $customRules = '';
    public bool $showRulesEditor = false;


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

    public function sendMessage(GroqService $groqService)
    {
        if (empty(trim($this->userInput)) && empty($this->chatFiles)) return;

        $userMessage = $this->userInput;

        // --- HYBRID LOGIC: Pre-process for Keywords (Bypass AI for simple CRUD) ---
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


        // Process File Uploads
        if (!empty($this->chatFiles)) {
            $uploadedUrls = [];
            foreach ($this->chatFiles as $file) {
                $disk = !empty(config('filesystems.disks.s3.bucket')) ? 's3' : 'public';
                $path = $file->store('chatbot_uploads', $disk);
                $url = \Storage::disk($disk)->url($path);
                
                $uploadedUrls[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => $url,
                    'type' => $file->getMimeType()
                ];
            }
            $fileNames = implode(', ', array_column($uploadedUrls, 'name'));
            $fileInfo = "\n\n[Hệ thống: Người dùng đã đính kèm " . count($uploadedUrls) . " tệp tin: {$fileNames}]";
            foreach($uploadedUrls as $file) {
                $fileInfo .= "\n- Tệp: {$file['name']} | Link: {$file['url']}";
            }
        }

        $fullUserContent = $userMessage . $fileInfo;
        
        \App\Models\ChatMessage::create([
            'user_id' => auth()->id(),
            'role' => 'user',
            'content' => $fullUserContent
        ]);

        $this->messages[] = [
            'role' => 'user', 
            'content' => $userMessage ?: "Đã tải lên " . count($this->chatFiles) . " tệp tin.",
            'has_files' => !empty($this->chatFiles) ? count($this->chatFiles) : 0
        ];

        $this->userInput = '';
        $this->chatFiles = [];
        $this->isTyping = true;
        $this->streamingResponse = ''; // Reset streaming

        $context = $this->getSystemContext();
        $tools = $this->getAvailableTools();

        // Limit history to last 8 messages to stay within TPM limits
        $recentMessages = array_slice($this->messages, -8);

        $apiMessages = array_map(fn($m) => [
            'role' => $m['role'],
            'content' => $m['content']
        ], $recentMessages);

        // Update the very last message (the current user input) with rich content (file links, etc)
        if (!empty($apiMessages) && $apiMessages[count($apiMessages) - 1]['role'] === 'user') {
            $apiMessages[count($apiMessages) - 1]['content'] = $fullUserContent;
        }

        // Prepend system context
        array_unshift($apiMessages, ['role' => 'system', 'content' => $context]);


        // Tool calling (non-streaming for now as it's internal)
        $maxIterations = 3;
        $iteration = 0;
        $lastToolResponse = null;

        while ($iteration < $maxIterations) {
            $response = $groqService->chat($apiMessages, 0.7, $tools);

            if (isset($response['error'])) {
                $this->messages[] = ['role' => 'assistant', 'content' => '⚠️ ' . $response['error']];
                break;
            }

            $message = $response['choices'][0]['message'] ?? null;
            if (!$message) break;

            if (isset($message['tool_calls'])) {
                $apiMessages[] = $message;
                foreach ($message['tool_calls'] as $toolCall) {
                    $toolResult = $this->executeTool($toolCall['function']['name'], json_decode($toolCall['function']['arguments'], true));
                    $apiMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name' => $toolCall['function']['name'],
                        'content' => json_encode($toolResult)
                    ];
                }
                $iteration++;
                continue;
            }

            // Final text response - Use Streaming
            $groqService->streamChat($apiMessages, function($chunk) {
                $this->streamingResponse .= $chunk;
                $this->stream(to: 'assistant-reply', content: $this->streamingResponse, replace: true);
            });

            // Save final result to DB and UI
            \App\Models\ChatMessage::create([
                'user_id' => auth()->id(),
                'role' => 'assistant',
                'content' => $this->streamingResponse
            ]);

            $this->messages[] = ['role' => 'assistant', 'content' => $this->streamingResponse];
            $this->streamingResponse = '';
            break;
        }

        $this->isTyping = false;
    }


    protected function getAvailableTools(): array
    {
        return [
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
                    'description' => 'Tìm kiếm tin đăng theo từ khóa, địa chỉ hoặc SĐT.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Từ khóa tìm kiếm'],
                        ],
                        'required' => ['query']
                    ]
                ]
            ]
        ];
    }


    protected function handleKeywordIntents(string $input): ?string
    {
        $input = mb_strtolower($input);
        
        // 1. Delete Listing (Pattern: xóa tin [id])
        if (preg_match('/xóa tin\s*(?:số|id)?\s*(\d+)/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                $listing->delete();
                return "✅ Hệ thống đã thực hiện xóa tin đăng ID #{$id} theo yêu cầu của bạn.";
            }
            return "❌ Không tìm thấy tin đăng ID #{$id} để xóa.";
        }

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

        // 4. View Listing Details (Pattern: chi tiết tin [id] / xem tin [id])
        if (preg_match('/(?:chi tiết tin|xem tin|check tin)\s*(?:số|id)?\s*(\d+)/i', $input, $matches)) {
            $id = $matches[1];
            $listing = RealEstateListing::find($id);
            if ($listing) {
                return "🔍 **Chi tiết tin đăng #{$id}**:\n" .
                       "- Tiêu đề: {$listing->title}\n" .
                       "- Giá: " . number_format($listing->price, 0, ',', '.') . " {$listing->price_unit_label}\n" .
                       "- Địa chỉ: {$listing->address}\n" .
                       "- Trạng thái: " . ($listing->is_sold ? '🔴 Đã bán' : '🟢 Còn trống') . "\n" .
                       "- SĐT: {$listing->contact_phone}";
            }
            return "❌ Không tìm thấy tin đăng ID #{$id}.";
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
                    $text .= "- [#{$r->id}] {$r->title} ({$r->price} {$r->price_unit_label})\n";
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
                    return ['status' => 'success', 'message' => "Đã tạo tin đăng thành công. ID: #{$listing->id}. Mã: {$listing->code}"];


                case 'create_customer':
                    $data = array_merge([
                        'code' => 'KH' . rand(1000, 9999),
                        'status' => 'khach_mua_o',
                        'assigned_user_id' => auth()->id()
                    ], $args);
                    $customer = Customer::create($data);
                    return ['status' => 'success', 'message' => "Đã tạo hồ sơ khách hàng thành công. ID: #{$customer->id}. Mã: {$customer->code}"];

                case 'update_listing_status':
                    $listing = RealEstateListing::find($args['listing_id']);
                    if (!$listing) return ['status' => 'error', 'message' => 'Không tìm thấy tin đăng ID: ' . $args['listing_id']];
                    $listing->update(['is_sold' => $args['is_sold']]);
                    return ['status' => 'success', 'message' => 'Đã cập nhật trạng thái tin đăng thành công.'];

                case 'search_listings':
                    $results = RealEstateListing::where('title', 'like', '%' . $args['query'] . '%')
                        ->orWhere('address', 'like', '%' . $args['query'] . '%')
                        ->orWhere('contact_phone', 'like', '%' . $args['query'] . '%')
                        ->limit(5)
                        ->get(['id', 'title', 'price', 'address', 'contact_phone']);
                    return ['status' => 'success', 'data' => $results];

                default:
                    return ['status' => 'error', 'message' => 'Tool không tồn tại.'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }





    protected function getSystemContext(): string
    {
        // 1. Detailed Database Statistics
        $listingStats = [
            'total' => RealEstateListing::count(),
            'sold' => RealEstateListing::where('is_sold', true)->count(),
            'available' => RealEstateListing::where('is_sold', false)->count(),
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

        // 2. Load Custom Rules (if any)
        $customRulesPath = storage_path('app/chatbot_rules.md');
        $customRules = file_exists($customRulesPath) ? file_get_contents($customRulesPath) : "Chưa có quy tắc bổ sung.";

        // 3. System Specs & Design Philosophy
        $systemPhilosophy = "
TRIẾT LÝ THIẾT KẾ & VẬN HÀNH (ANTIGRAVITY):
- Role: Senior Lead UX/UI Designer & Senior Fullstack Engineer.
- Vibe: Futuristic, Minimalist, 'Zero-Gravity' experience.
- UI: Deep Space Black (#050505), Electric Blue (#00D1FF), Glassmorphism, Micro-animations.
- Performance: Tối ưu tuyệt đối, Real-time reactivity (Livewire + Alpine).
- Shortcuts: 'N' (New), '/' (Search), 'Esc' (Close).
";

        $erpKnowledge = "
KIẾN THỨC NGHIỆP VỤ ERP:
- Sales: Quản lý đơn hàng (Pending -> Processing -> Done). Yêu cầu check kho & MST.
- Production: Kanban Workflow (Chờ xử lý -> Đang thực hiện -> QC -> Hoàn thành). Drag & Drop UI.
- Warehouse: Inventory tracking (Red/Yellow/Green alerts). QR/Barcode support.
- Real Estate: Tin đăng cần ảnh S3, mô tả minh bạch, duyệt trong 24h. CTV hưởng hoa hồng theo Rank.
";

        // 4. Final Prompt Construction
        $statsString = "THỐNG KÊ HỆ THỐNG REAL-TIME:\n" .
            "- Tin đăng: {$listingStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($listingStats['by_type']), $listingStats['by_type'])) . "). Đã bán: {$listingStats['sold']}.\n" .
            "- Khách hàng: {$customerStats['total']} (" . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($customerStats['by_status']), $customerStats['by_status'])) . "). Công việc tồn đọng: {$customerStats['pending_tasks']}.\n" .
            "- Nhân sự: {$userStats['total']} thành viên ({$userStats['admins']} Admin, {$userStats['ctvs']} CTV).\n" .
            "- Lưu trữ: {$storageStats['files']} tệp tin, {$storageStats['folders']} thư mục.";

        $richContext = $this->getRichListingContext();

        return "Bạn là Antigravity Admin AI, bộ não trung tâm của hệ thống Mediabds. 
Bạn hội tụ kỹ năng của một Senior Lead Designer và Senior Engineer với hơn 10 năm kinh nghiệm thực chiến.

PHONG CÁCH PHẢN HỒI:
- Trí tuệ, Futuristic, súc tích nhưng cực kỳ sắc bén.
- Luôn hướng tới giải pháp tối ưu và tự động hóa.
- Ngôn ngữ: Tiếng Việt, chuyên nghiệp, truyền cảm hứng về công nghệ.

{$statsString}

{$richContext}

{$systemPhilosophy}

{$erpKnowledge}

QUY TẮC RIÊNG (CUSTOM RULES):
{$customRules}

NHIỆM VỤ CHIẾN LƯỢC:
1. Phân tích dữ liệu: Dựa trên số liệu thực tế để đưa ra lời khuyên quản trị.
2. Hướng dẫn kỹ thuật: Giải đáp quy trình ERP (Sales, Production, Warehouse) một cách chuyên sâu.
3. Tư vấn thiết kế: Nếu được hỏi về giao diện, hãy phân tích dựa trên triết lý Antigravity.
4. Thay thế quản trị viên: Tự động hóa các câu hỏi lặp lại và hướng dẫn CTV.

QUY TẮC SỬ DỤNG CÔNG CỤ (TOOL USAGE RULES):
- **create_listing**: CHỈ ĐƯỢC GỌI khi có đủ 4 thông tin: Tiêu đề, Giá, Diện tích và SĐT.
- **Dữ liệu chuẩn**: Luôn dùng đúng Enum:
  + `type`: 'Cần bán', 'Cho thuê', 'Cần mua'. (KHÔNG dùng từ khác).
  + `price_unit`: 'Tỷ', 'Triệu', 'VNĐ/tháng', 'Thỏa thuận'.

Hãy trả lời với phong thái của một AI đẳng cấp, mang lại cảm giác nhẹ nhàng nhưng quyền lực của hệ thống Antigravity.";



    }

    protected function getRichListingContext(): string
    {
        // Fetch 3 latest listings for context (Reduced from 5 for token efficiency)
        $latestListings = RealEstateListing::orderBy('created_at', 'desc')->limit(3)->get();
        $listingList = $latestListings->map(fn($l) => "- {$l->title} | {$l->price} {$l->price_unit}")->join("\n");


        // High value listings (> 10 billion VND)
        $highValueCount = RealEstateListing::where('price', '>', 10)->where('price_unit', 'Tỷ')->count();

        // Area stats
        $avgArea = RealEstateListing::avg('area');

        return "PHÂN TÍCH CHUYÊN SÂU TIN ĐĂNG:
- 5 Tin đăng mới nhất:
{$listingList}
- Phân khúc cao cấp (>10 Tỷ): {$highValueCount} tin.
- Diện tích trung bình: " . number_format($avgArea, 1) . " m2.
- Xu hướng: " . ($highValueCount > 5 ? "Thị trường đang tập trung nhiều sản phẩm giá trị cao." : "Thị trường đang ổn định ở phân khúc tầm trung.") . "
";
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
