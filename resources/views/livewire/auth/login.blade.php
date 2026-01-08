<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-main-gradient relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.1),transparent)] pointer-events-none"></div>

    <div class="max-w-md w-full space-y-8 bg-white/80 backdrop-blur-xl p-10 rounded-[2.5rem] shadow-2xl border border-white/50 relative z-10">
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-blue-50 rounded-full flex items-center justify-center mb-6 shadow-inner ring-4 ring-white">
                 <span class="text-4xl">📂</span>
            </div>
            <h2 class="mt-2 text-3xl font-black text-gray-900 tracking-tight">
                Media Manager
            </h2>
            <p class="mt-2 text-sm text-gray-500 font-medium">
                Sign in to access your files
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="login">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="phone" class="sr-only">Phone Number</label>
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-400 font-bold group-focus-within:text-blue-500 transition-colors">📱</span>
                        </div>
                        <input id="phone" name="phone" type="text" wire:model="phone" required class="appearance-none rounded-2xl relative block w-full px-4 py-4 pl-12 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-b-none focus:outline-none focus:ring-4 focus:ring-blue-50/50 focus:border-blue-400 focus:z-10 sm:text-sm font-bold transition-all bg-gray-50/50 focus:bg-white" placeholder="Phone Number">
                    </div>
                    @error('phone') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                             <span class="text-gray-400 font-bold group-focus-within:text-blue-500 transition-colors">🔒</span>
                        </div>
                        <input id="password" name="password" type="password" wire:model="password" required class="appearance-none rounded-2xl relative block w-full px-4 py-4 pl-12 border border-gray-200 placeholder-gray-400 text-gray-900 rounded-t-none focus:outline-none focus:ring-4 focus:ring-blue-50/50 focus:border-blue-400 focus:z-10 sm:text-sm font-bold transition-all bg-gray-50/50 focus:bg-white" placeholder="Password">
                    </div>
                     @error('password') <span class="text-red-500 text-xs font-bold ml-2">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-black rounded-2xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all shadow-lg shadow-blue-200 hover:-translate-y-0.5 transform">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
            
            <div class="text-center">
                 <p class="text-xs text-gray-400 font-medium">Default: 0999999999 / 12345678</p>
            </div>
        </form>
    </div>
</div>
