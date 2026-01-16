<div
    class="bg-slate-800 p-8 sm:p-10 rounded-3xl border border-slate-700 shadow-2xl ring-1 ring-white/10 animate-in fade-in slide-in-from-bottom-8 duration-700 w-full">

    <div class="text-center mb-10">
        <img src="https://s3-hcm5-r1.longvan.net/phongland/2025/12/logo.png" alt="OpenFiles"
            class="h-16 mx-auto mb-4 object-contain">
        <h2 class="text-2xl font-black text-white tracking-widest uppercase font-mono">
            OpenFiles
        </h2>
        <p class="mt-2 text-slate-400 text-sm font-medium tracking-wide">
            SECURE CLOUD STORAGE
        </p>
    </div>

    <form wire:submit="login" class="space-y-6">
        <div class="space-y-4">
            <!-- Phone Input -->
            <div>
                <label for="phone" class="sr-only">Phone Number</label>
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-blue-500 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <input id="phone" name="phone" type="text" wire:model="phone" required=""
                        class="block w-full pl-11 pr-4 py-3.5 bg-slate-900 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all sm:text-sm font-medium hover:bg-slate-900/70"
                        placeholder="Phone Number">
                </div>
                @error('phone')
                    <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 transition-all shadow-[0_0_20px_rgba(37,99,235,0.3)] hover:shadow-[0_0_30px_rgba(37,99,235,0.5)]">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </span>
                Sign In
            </button>
        </div>
    </form>


</div>
