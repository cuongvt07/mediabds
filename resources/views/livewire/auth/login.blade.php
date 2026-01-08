<div
    class="min-h-screen flex items-center justify-center bg-slate-900 relative overflow-hidden font-sans selection:bg-blue-500 selection:text-white">

    <!-- Tech Background Effect -->
    <div class="absolute inset-0 z-0">
        <!-- Animated Grid -->
        <div
            class="absolute inset-0 bg-[linear-gradient(rgba(30,58,138,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(30,58,138,0.1)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_80%_80%_at_50%_50%,black,transparent)]">
        </div>

        <!-- Glowing Orbs -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-md w-full relative z-10 p-6">
        <!-- Main Card -->
        <div
            class="bg-slate-800/40 backdrop-blur-xl p-8 sm:p-10 rounded-3xl border border-slate-700/50 shadow-2xl ring-1 ring-white/10 animate-in fade-in slide-in-from-bottom-8 duration-700">

            <div class="text-center mb-10">
                <h2 class="text-4xl font-black text-white tracking-widest uppercase font-mono">
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
                            <input id="phone" name="phone" type="text" wire:model="phone" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all sm:text-sm font-medium hover:bg-slate-900/70"
                                placeholder="Phone Number">
                        </div>
                        @error('phone')
                            <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-blue-500 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" wire:model="password" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all sm:text-sm font-medium hover:bg-slate-900/70"
                                placeholder="Password">
                        </div>
                        @error('password')
                            <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 transition-all shadow-[0_0_20px_rgba(37,99,235,0.3)] hover:shadow-[0_0_30px_rgba(37,99,235,0.5)]">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                        Sign In
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
                <p class="text-xs text-slate-500">
                    Default Access: <span class="font-mono text-slate-400">0999999999</span> / <span
                        class="font-mono text-slate-400">12345678</span>
                </p>
            </div>
        </div>

        <!-- Bottom branding -->
        <div class="text-center mt-8 opacity-60">
            <p class="text-[10px] text-slate-500 uppercase tracking-[0.2em] font-bold">Secure Access Gateway</p>
        </div>
    </div>
</div>
