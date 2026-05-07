<div x-data="{ 
        currentMode: 'FAST',
        handleMode(e) { this.currentMode = e.detail.mode }
     }" 
     @mode-detected.window="handleMode"
     class="flex flex-col h-full bg-white text-slate-900 font-sans selection:bg-slate-200 overflow-hidden">
    <!-- Header -->
    @if(!$isPopup)
    <header class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-white sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="w-9 h-9 rounded-lg bg-slate-900 flex items-center justify-center shadow-sm">
                    <template x-if="currentMode === 'FAST'">
                        <i class="fa-solid fa-bolt text-white text-sm"></i>
                    </template>
                    <template x-if="currentMode === 'SMART'">
                        <i class="fa-solid fa-brain text-white text-sm"></i>
                    </template>
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></div>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900 tracking-tight">
                    Antigravity AI
                    <span class="text-[8px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 ml-1 font-bold" x-text="currentMode"></span>
                </h1>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none">Hệ thống xử lý</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            @if(auth()->user()?->isAdmin())
                <button wire:click="toggleRulesEditor" class="group flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 transition-all border border-slate-200">
                    <i class="fa-solid fa-gear text-[10px]"></i>
                    <span class="text-[10px] font-bold">Cài đặt</span>
                </button>
            @endif
            <button wire:click="clearChat" class="group flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-slate-500 transition-all border border-slate-200">
                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                <span class="text-[10px] font-bold">Làm mới</span>
            </button>
        </div>
    </header>
    @endif

    <!-- Rules Editor (Overlay) -->
    @if($showRulesEditor)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="w-full max-w-xl bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[70vh] animate-in zoom-in-95 duration-300">
                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Cấu hình kỹ năng</h2>
                    </div>
                    <button wire:click="toggleRulesEditor" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <div class="p-4 flex-1 overflow-y-auto bg-slate-50/50">
                    <textarea 
                        wire:model="customRules" 
                        class="w-full h-64 bg-white border border-slate-200 rounded-lg p-4 text-sm text-slate-700 focus:outline-none focus:border-slate-900 transition-all resize-none font-mono"
                        placeholder="// Nhập quy tắc tùy chỉnh..."
                    ></textarea>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-white">
                    <button wire:click="toggleRulesEditor" class="px-4 py-1.5 rounded-lg text-[10px] font-bold text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-widest">Hủy</button>
                    <button wire:click="saveRules" class="px-6 py-1.5 rounded-lg bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-black transition-all">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    @endif


    <!-- Chat Area -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 scroll-smooth custom-scrollbar" id="chat-container" 
        x-init="
            $nextTick(() => { $el.scrollTop = $el.scrollHeight });
            $watch('$wire.messages', () => { 
                $nextTick(() => { $el.scrollTop = $el.scrollHeight });
            });
            window.addEventListener('chat-opened', () => {
                $nextTick(() => { $el.scrollTop = $el.scrollHeight });
            });
        "
        @chat-opened.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
    >

        @foreach($messages as $index => $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-1 duration-300">
                <div class="max-w-[90%] flex gap-2 {{ $message['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                    <!-- Avatar -->
                    <div class="shrink-0">
                        @if($message['role'] === 'user')
                            <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-800 text-white shadow-sm overflow-hidden">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[10px] font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-900 text-white shadow-sm">
                                <template x-if="currentMode === 'FAST'">
                                    <i class="fa-solid fa-bolt text-[10px]"></i>
                                </template>
                                <template x-if="currentMode === 'SMART'">
                                    <i class="fa-solid fa-brain text-[10px]"></i>
                                </template>
                            </div>
                        @endif
                    </div>

                    <!-- Bubble -->
                    <div class="group relative flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }}">
                        <div class="px-3.5 py-2 rounded-xl text-[13px] leading-snug border transition-all
                            {{ $message['role'] === 'user' 
                                ? 'bg-slate-900 text-white border-slate-800 rounded-tr-none' 
                                : 'bg-slate-50 border-slate-200 text-slate-800 rounded-tl-none' }}">
                            @php
                                $parts = preg_split('/(\[LISTING:\d+\])/', $message['content'], -1, PREG_SPLIT_DELIM_CAPTURE);
                            @endphp
                            @foreach($parts as $part)
                                @if(preg_match('/\[LISTING:(\d+)\]/', $part, $matches))
                                    @php 
                                        $listingId = $matches[1]; 
                                        $listing = $this->getListingData($listingId); 
                                    @endphp
                                    @if($listing)
                                        <div class="my-3 -mx-2">
                                            <x-listing-card :listing="$listing" mode="chat" />
                                        </div>
                                    @else
                                        <span class="text-red-500 text-[10px]">❌ Không tìm thấy tin #{{ $listingId }}</span>
                                    @endif
                                @else
                                    {!! nl2br(e($part)) !!}
                                @endif
                            @endforeach

                            @if(isset($message['is_hitl']) && $message['is_hitl'])
                                <div class="mt-3 flex gap-2 p-2 bg-white rounded-lg border border-slate-200 border-dashed">
                                    <button wire:click="confirmToolCall" class="flex-1 px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold text-[10px] uppercase tracking-wider hover:bg-black transition-all">
                                        Xác nhận
                                    </button>
                                    <button wire:click="cancelToolCall" class="flex-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 font-bold text-[10px] uppercase tracking-wider hover:bg-red-50 hover:text-red-600 transition-all">
                                        Hủy
                                    </button>
                                </div>
                            @endif

                            @if(isset($message['has_files']) && $message['has_files'] > 0)
                                <div class="mt-1.5 pt-1.5 border-t {{ $message['role'] === 'user' ? 'border-white/10' : 'border-slate-200' }} flex items-center gap-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                    <i class="fa-solid fa-paperclip"></i>
                                    <span>Tệp: {{ $message['has_files'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="mt-1 px-1 opacity-40 group-hover:opacity-100 transition-opacity">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $message['role'] === 'user' ? auth()->user()->name : 'AI' }} • {{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Streaming Response Target -->
        <div id="streaming-reply-container" class="flex justify-start animate-in fade-in duration-300" 
             x-show="$wire.isTyping" style="display: none;">
            <div class="flex gap-2 max-w-[90%]">
                <div class="shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white">
                        <i class="fa-solid fa-bolt text-[10px]"></i>
                    </div>
                </div>
                <div class="px-3.5 py-2 rounded-xl text-[13px] leading-snug border bg-slate-50 border-slate-200 text-slate-800 rounded-tl-none" 
                     wire:stream="assistant-reply">
                    <!-- The content will be streamed here -->
                </div>
            </div>
        </div>

        @if($isTyping && empty($streamingResponse))
            <!-- Thinking Dots -->
            <div class="flex justify-start animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="flex gap-2 items-start">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white shrink-0 shadow-sm">
                        <template x-if="currentMode === 'FAST'">
                            <i class="fa-solid fa-bolt text-[10px] animate-pulse"></i>
                        </template>
                        <template x-if="currentMode === 'SMART'">
                            <i class="fa-solid fa-brain text-[10px] animate-pulse"></i>
                        </template>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl rounded-tl-none px-4 py-3 flex items-center gap-1">
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Input Area -->
    <div class="px-4 pb-4 pt-2 bg-white border-t border-slate-100">
        
        <!-- File Preview Area -->
        @if(count($chatFiles) > 0)
            <div class="flex flex-wrap gap-1.5 mb-2">
                @foreach($chatFiles as $index => $file)
                    <div class="group relative bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 flex items-center gap-2 pr-6">
                        <div class="w-5 h-5 rounded bg-slate-200 flex items-center justify-center text-slate-600">
                            <i class="fa-solid fa-file text-[8px]"></i>
                        </div>
                        <span class="text-[9px] font-bold text-slate-700 truncate max-w-[80px]">{{ $file->getClientOriginalName() }}</span>
                        <button wire:click="removeFile({{ $index }})" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                            <i class="fa-solid fa-circle-xmark text-[10px]"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!$isTyping)
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-1.5 mb-3 justify-center">
                <button wire:click="$set('userInput', 'Phân tích thống kê hệ thống')" class="px-3 py-1 rounded-full bg-slate-50 border border-slate-100 text-[9px] font-bold text-slate-500 hover:bg-slate-900 hover:text-white transition-all uppercase tracking-wider">
                    Thống kê
                </button>
                <button wire:click="$set('userInput', 'Tìm khách hàng đang có ngân sách tốt')" class="px-3 py-1 rounded-full bg-slate-50 border border-slate-100 text-[9px] font-bold text-slate-500 hover:bg-slate-900 hover:text-white transition-all uppercase tracking-wider">
                    Phân tích Lead
                </button>
            </div>
        @endif


        <form wire:submit.prevent="sendMessage" class="relative group flex items-center gap-2">
            <label class="shrink-0 w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 cursor-pointer transition-all" title="Tối đa 2MB (Chỉ ảnh)">
                <input type="file" wire:model="chatFiles" multiple accept="image/*" class="hidden">
                <i class="fa-solid fa-paperclip text-base"></i>
            </label>

            <div class="relative flex-1">
                <input 
                    type="text" 
                    wire:model="userInput"
                    placeholder="Nhập nội dung..." 
                    class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg px-4 pr-20 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-slate-900 transition-all"
                    @if($isTyping) disabled @endif
                >
                
                <div class="absolute right-1 top-1/2 -translate-y-1/2">
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="h-8 px-4 rounded bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2"
                    >
                        <span wire:loading.remove>Gửi</span>
                        <span wire:loading class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            Đang xử lý
                        </span>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-2 flex justify-center gap-4 opacity-30">
            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">GPT-4o Mini</span>
            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">OpenAI Neural</span>
        </div>
    </div>

    <!-- Quick View Detail Drawer (Slide-over) -->
    <div x-cloak x-show="$wire.showDetailPopup" 
         class="fixed inset-0 z-[100] flex justify-end overflow-hidden"
         x-on:keydown.escape.window="$wire.closeDetailQuickly()">
        
        <!-- Backdrop -->
        <div x-show="$wire.showDetailPopup" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$wire.closeDetailQuickly()"
             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

        <!-- Drawer Content -->
        <div x-show="$wire.showDetailPopup" 
             x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="relative w-full max-w-md h-full bg-slate-950/90 backdrop-blur-2xl border-l border-white/10 shadow-2xl flex flex-col">
            
            @if($selectedListing)
            <!-- Header -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-white/5 bg-slate-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <i class="fa-solid fa-house-chimney text-white text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-wider">Chi tiết tin đăng</h3>
                        <p class="text-[10px] text-blue-400 font-bold">#{{ $selectedListing['code'] }}</p>
                    </div>
                </div>
                <button @click="$wire.closeDetailQuickly()" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-white transition-all flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6">
                <!-- Image Slider (Simple) -->
                <div class="relative group" x-data="{ 
                    current: 0, 
                    images: @js($selectedListing['display_images']),
                    next() { this.current = (this.current + 1) % this.images.length },
                    prev() { this.current = (this.current - 1 + this.images.length) % this.images.length }
                }">
                    <div class="aspect-[4/3] rounded-2xl bg-slate-900 overflow-hidden border border-white/10 shadow-xl relative">
                        <template x-for="(img, index) in images" :key="index">
                            <img x-show="current === index" :src="img" 
                                 class="absolute inset-0 w-full h-full object-cover transition-all duration-700"
                                 x-transition:enter="opacity-0 scale-110"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 loading="lazy">
                        </template>

                        <!-- Nav Buttons -->
                        <div class="absolute inset-0 flex items-center justify-between px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="prev()" class="w-8 h-8 rounded-full bg-black/40 backdrop-blur-md text-white hover:bg-black/60 transition-all flex items-center justify-center">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button @click="next()" class="w-8 h-8 rounded-full bg-black/40 backdrop-blur-md text-white hover:bg-black/60 transition-all flex items-center justify-center">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>

                        <!-- Dots -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 px-2 py-1 rounded-full bg-black/20 backdrop-blur-md">
                            <template x-for="(img, index) in images" :key="index">
                                <button @click="current = index" 
                                        :class="current === index ? 'bg-blue-400 w-4' : 'bg-white/40 w-1.5'"
                                        class="h-1.5 rounded-full transition-all duration-300"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Main Info -->
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-white leading-tight">{{ $selectedListing['title'] }}</h2>
                    
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-widest">
                            {{ $selectedListing['type'] }}
                        </span>
                        @if($selectedListing['property_type'])
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] font-black uppercase tracking-widest">
                            {{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$selectedListing['property_type']] ?? 'Khác' }}
                        </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1">Giá bán</p>
                            <p class="text-xl font-black text-red-500">
                                {{ number_format($selectedListing['price'], 0, ',', '.') }}
                                <span class="text-xs font-bold text-slate-400">{{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}</span>
                            </p>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1">Diện tích</p>
                            <p class="text-xl font-black text-blue-400">{{ floatval($selectedListing['area']) }} <span class="text-xs font-bold text-slate-400">m²</span></p>
                        </div>
                    </div>
                </div>

                <!-- Facts -->
                <div class="grid grid-cols-3 gap-3">
                    @if($selectedListing['floors'])
                    <div class="flex flex-col items-center p-3 rounded-xl bg-slate-900 border border-white/5">
                        <i class="fa-solid fa-layer-group text-slate-500 mb-2"></i>
                        <span class="text-xs font-bold text-white">{{ $selectedListing['floors'] }} Tầng</span>
                    </div>
                    @endif
                    @if($selectedListing['bedrooms'])
                    <div class="flex flex-col items-center p-3 rounded-xl bg-slate-900 border border-white/5">
                        <i class="fa-solid fa-bed text-slate-500 mb-2"></i>
                        <span class="text-xs font-bold text-white">{{ $selectedListing['bedrooms'] }} PN</span>
                    </div>
                    @endif
                    @if($selectedListing['toilets'])
                    <div class="flex flex-col items-center p-3 rounded-xl bg-slate-900 border border-white/5">
                        <i class="fa-solid fa-restroom text-slate-500 mb-2"></i>
                        <span class="text-xs font-bold text-white">{{ $selectedListing['toilets'] }} WC</span>
                    </div>
                    @endif
                </div>

                <!-- Location -->
                <div class="p-4 rounded-2xl bg-slate-900 border border-white/5 space-y-2">
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Vị trí</p>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-bold text-white leading-snug">{{ $selectedListing['address'] }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ implode(', ', array_filter([$selectedListing['ward_name'], $selectedListing['district_name'], $selectedListing['province_name']])) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Mô tả</p>
                    <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                        {{ $selectedListing['description'] }}
                    </div>
                </div>

                <!-- Social Links -->
                <div class="space-y-3 pb-8">
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Liên kết hỗ trợ</p>
                    <div class="grid grid-cols-2 gap-2">
                        @if (!empty($selectedListing['facebook_link']))
                            <a href="{{ $selectedListing['facebook_link'] }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600/10 border border-blue-600/20 text-blue-400 hover:bg-blue-600/20 transition-all text-xs font-bold">
                                <i class="fa-brands fa-facebook text-base"></i> Facebook
                            </a>
                        @endif
                        @if (!empty($selectedListing['google_map_link']))
                            <a href="{{ $selectedListing['google_map_link'] }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600/10 border border-emerald-600/20 text-emerald-400 hover:bg-emerald-600/20 transition-all text-xs font-bold">
                                <i class="fa-solid fa-map-location-dot text-base"></i> Google Map
                            </a>
                        @endif
                        @if (!empty($selectedListing['youtube_link']))
                            <a href="{{ $selectedListing['youtube_link'] }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600/10 border border-red-600/20 text-red-400 hover:bg-red-600/20 transition-all text-xs font-bold col-span-2">
                                <i class="fa-brands fa-youtube text-base"></i> Video Review
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="px-6 py-4 border-t border-white/5 bg-slate-900/50 flex gap-2">
                <button @click="
                    const text = `🏠 {{ $selectedListing['title'] }} \n📍 Vị trí: {{ $selectedListing['address'] }} \n💰 Giá: {{ number_format($selectedListing['price'], 0, ',', '.') }} {{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($selectedListing['area']) }} m²`;
                    navigator.clipboard.writeText(text);
                    $dispatch('toast', { message: 'Đã copy thông tin tin đăng!', type: 'success' });
                " class="flex-1 h-11 rounded-xl bg-white/5 border border-white/10 text-white font-bold text-xs uppercase tracking-widest hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                    <i class="fa-regular fa-copy"></i> Copy thông tin
                </button>
            </div>
            @endif
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 2px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        [x-cloak] { display: none !important; }
    </style>
</div>


