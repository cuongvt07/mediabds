<div x-data class="flex flex-col h-full bg-slate-50 text-slate-900 font-sans selection:bg-blue-500/10">
    <!-- Header -->
    <header class="flex items-center justify-between px-8 py-6 border-b border-slate-200 bg-white/80 backdrop-blur-md sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-400 flex items-center justify-center shadow-[0_10px_20px_rgba(0,112,243,0.15)]">
                    <i class="fa-solid fa-robot text-white text-xl"></i>
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-4 border-white rounded-full"></div>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Antigravity <span class="text-blue-600">Admin AI</span></h1>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-[0.2em]">Neural Processing System</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            @if(auth()->user()?->isAdmin())
                <button wire:click="toggleRulesEditor" class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all duration-300 border border-blue-200">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="text-xs font-bold">System Rules</span>
                </button>
            @endif
            <button wire:click="clearChat" class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-600 transition-all duration-300 border border-slate-200 hover:border-red-200">
                <i class="fa-solid fa-trash-can text-sm group-hover:rotate-12 transition-transform"></i>
                <span class="text-xs font-bold">Clear Context</span>
            </button>
        </div>
    </header>

    <!-- Rules Editor (Overlay) -->
    @if($showRulesEditor)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh] animate-in zoom-in-95 duration-300">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">System Instructions</h2>
                        <p class="text-xs text-slate-500">Dạy AI cách xử lý dữ liệu và hành xử theo quy tắc riêng của bạn.</p>
                    </div>
                    <button wire:click="toggleRulesEditor" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <div class="p-8 flex-1 overflow-y-auto">
                    <textarea 
                        wire:model="customRules" 
                        class="w-full h-80 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/5 transition-all duration-300 resize-none font-mono"
                        placeholder="Nhập các quy tắc riêng của bạn tại đây..."
                    ></textarea>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-4">
                    <button wire:click="toggleRulesEditor" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors">Hủy</button>
                    <button wire:click="saveRules" class="px-8 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all">Lưu quy tắc</button>
                </div>
            </div>
        </div>
    @endif


    <!-- Chat Area -->
    <div class="flex-1 overflow-y-auto px-8 py-10 space-y-8 scroll-smooth custom-scrollbar" id="chat-container" x-init="
        $watch('$wire.messages', () => { 
            $nextTick(() => { $el.scrollTop = $el.scrollHeight });
        });
        $el.scrollTop = $el.scrollHeight;
    ">

        @foreach($messages as $index => $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="max-w-[80%] flex gap-4 {{ $message['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                    <!-- Avatar -->
                    <div class="shrink-0">
                        @if($message['role'] === 'user')
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-500">
                                <i class="fa-solid fa-user text-sm"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-blue-600/10 flex items-center justify-center border border-blue-500/20 text-blue-600">
                                <i class="fa-solid fa-sparkles text-sm"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Bubble -->
                    <div class="group relative">
                        <div class="px-5 py-3.5 rounded-2xl text-sm leading-relaxed shadow-sm border 
                            {{ $message['role'] === 'user' 
                                ? 'bg-blue-600 text-white border-blue-500 rounded-tr-none shadow-blue-500/10' 
                                : 'bg-white border-slate-200 text-slate-700 rounded-tl-none' }}">
                            {!! nl2br(e($message['content'])) !!}

                            @if(isset($message['has_files']) && $message['has_files'] > 0)
                                <div class="mt-2 pt-2 border-t {{ $message['role'] === 'user' ? 'border-white/20' : 'border-slate-100' }} flex items-center gap-2 text-[10px] opacity-80">
                                    <i class="fa-solid fa-paperclip"></i>
                                    <span>Đã đính kèm {{ $message['has_files'] }} tệp tin</span>
                                </div>
                            @endif
                        </div>
                        <div class="absolute top-1/2 -translate-y-1/2 {{ $message['role'] === 'user' ? '-left-12' : '-right-12' }} opacity-0 group-hover:opacity-100 transition-opacity">
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $message['role'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Streaming Response Target -->
        <div id="streaming-reply-container" class="flex justify-start animate-in fade-in slide-in-from-bottom-2 duration-300" 
             x-show="$wire.streamingResponse.length > 0" style="display: none;">
            <div class="flex gap-4 max-w-[80%]">
                <div class="shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600/10 flex items-center justify-center border border-blue-500/20 text-blue-600">
                        <i class="fa-solid fa-sparkles text-sm"></i>
                    </div>
                </div>
                <div class="px-5 py-3.5 rounded-2xl text-sm leading-relaxed shadow-sm border bg-white border-slate-200 text-slate-700 rounded-tl-none" 
                     wire:stream="assistant-reply">
                </div>
            </div>
        </div>

        @if($isTyping && !($streamingResponse))
            <div class="flex justify-start animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="flex gap-4 items-start">
                    <!-- Assistant Avatar with Loading -->
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center text-white shadow-lg shadow-blue-200 relative shrink-0">
                        <i class="fa-solid fa-sparkles"></i>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-[8px]"></i>
                        </div>
                    </div>
                    
                    <div class="flex gap-4 items-center bg-white border border-slate-200 px-5 py-3 rounded-2xl rounded-tl-none shadow-sm mt-1">
                        <div class="flex gap-1">
                            <div class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                            <div class="w-1.5 h-1.5 bg-blue-300 rounded-full animate-bounce"></div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Antigravity is thinking...</span>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Input Area -->
    <div class="px-8 pb-8 pt-4 bg-white border-t border-slate-200 shadow-[0_-10px_30px_rgba(0,0,0,0.02)]">
        
        <!-- File Preview Area -->
        @if(count($chatFiles) > 0)
            <div class="flex flex-wrap gap-3 mb-4 animate-in slide-in-from-bottom-2 duration-300">
                @foreach($chatFiles as $index => $file)
                    <div class="group relative bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-3 pr-8 shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            @if(str_contains($file->getMimeType(), 'image'))
                                <i class="fa-solid fa-image text-xs"></i>
                            @else
                                <i class="fa-solid fa-file-lines text-xs"></i>
                            @endif
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[120px]">{{ $file->getClientOriginalName() }}</span>
                            <span class="text-[8px] text-slate-400 uppercase">{{ number_format($file->getSize() / 1024, 1) }} KB</span>
                        </div>
                        <button wire:click="removeFile({{ $index }})" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!$isTyping)
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2 mb-6 justify-center">
                <button wire:click="$set('userInput', 'Thống kê hệ thống hiện tại')" class="px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-300 uppercase tracking-widest">
                    <i class="fa-solid fa-chart-mixed mr-2"></i> Thống kê hệ thống
                </button>
                <button wire:click="$set('userInput', 'Quy trình duyệt tin đăng BĐS')" class="px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-300 uppercase tracking-widest">
                    <i class="fa-solid fa-file-check mr-2"></i> Quy trình duyệt tin
                </button>
                <button wire:click="$set('userInput', 'Phím tắt hệ thống Antigravity')" class="px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-300 uppercase tracking-widest">
                    <i class="fa-solid fa-keyboard mr-2"></i> Phím tắt nhanh
                </button>
            </div>
        @endif


        <form wire:submit.prevent="sendMessage" class="relative group flex items-center gap-2">
            
            <!-- File Upload Trigger -->
            <label class="shrink-0 w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition-all duration-300 group/file">
                <input type="file" wire:model="chatFiles" multiple class="hidden">
                <i class="fa-solid fa-paperclip text-lg group-hover/file:rotate-12 transition-transform"></i>
            </label>

            <div class="relative flex-1">
                <input 
                    type="text" 
                    wire:model="userInput"
                    placeholder="Ask Antigravity anything..." 
                    class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 pr-32 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/5 transition-all duration-300"
                    @if($isTyping) disabled @endif
                >
                
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                    <div class="hidden sm:flex flex-col items-end mr-2">
                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">Enter to send</span>
                    </div>
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                        class="h-11 px-6 rounded-xl bg-blue-600 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 disabled:opacity-50 disabled:grayscale relative overflow-hidden group"
                    >
                        <span wire:loading.remove wire:target="sendMessage" class="flex items-center">
                            Send <i class="fa-solid fa-paper-plane-top ml-2"></i>
                        </span>
                        
                        <span wire:loading wire:target="sendMessage" class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            <span class="animate-pulse">Sending...</span>
                        </span>

                        @if($isTyping)
                             <span wire:loading.remove wire:target="sendMessage" class="flex items-center gap-2">
                                <i class="fa-solid fa-sparkles fa-spin text-blue-200"></i>
                                AI...
                            </span>
                        @endif
                    </button>

                </div>
            </div>
        </form>

        
        <div class="mt-4 flex justify-center gap-8">
            <div class="flex items-center gap-2">
                <div class="w-1 h-1 bg-blue-500 rounded-full"></div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">LLaMA 3.1 8B</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-1 h-1 bg-cyan-500 rounded-full"></div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Groq LPUs Optimized</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-1 h-1 bg-purple-500 rounded-full"></div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">System Training Active</span>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 112, 243, 0.2);
        }
    </style>
</div>

