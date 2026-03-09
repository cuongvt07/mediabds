<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'File Manager' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-item-active {
            background: linear-gradient(to right, #f8fafc, #eff6ff);
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .bg-main-gradient {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
    </style>
</head>

<body class="antialiased text-gray-800 bg-white" x-data="{
    currentModule: new URLSearchParams(window.location.search).get('module') || 'listings',
    sidebarOpen: true
}">

    <div class="flex h-screen overflow-hidden">
        <!-- Global Sidebar (Level 1) -->
        <aside class="bg-slate-900 w-20 flex flex-col items-center py-6 shrink-0 z-50">
            <!-- Brand -->
            <!-- Brand -->
            <div
                class="w-10 h-10 mb-8 cursor-pointer hover:bg-slate-800 rounded-xl flex items-center justify-center transition-colors">
                <img src="https://s3-hcm5-r1.longvan.net/phongland/2026/01/d13bf59afd35726b2b24.jpg" alt="Logo"
                    class="w-full h-full object-contain">
            </div>

            <!-- Navigation Modules -->
            <div class="flex flex-col gap-4 w-full px-2">
                <a href="{{ route('listings') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('listings') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Tin đăng BĐS">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('listings') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-newspaper text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Tin Đăng</span>
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('media') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('media') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Media Manager">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('media') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-photo-film text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Media</span>
                </a>

                <a href="{{ route('accounts') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('accounts') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Quản lý tài khoản">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('accounts') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-users text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Account</span>
                </a>

                <a href="{{ route('business') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('business') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Quản lý kinh doanh">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('business') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-briefcase text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Kinh Doanh</span>
                </a>

                @endif

                <a href="{{ route('customers') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('customers') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Quản lý khách hàng">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('customers') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-user-group text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Khách Hàng</span>
                </a>
            </div>

            <!-- Bottom Actions -->
            <div class="mt-auto flex flex-col gap-4 relative" x-data="{ showSettings: false, showUserMenu: false }">
                
                <!-- Settings Dropdown -->
                <div x-show="showSettings" @click.away="showSettings = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute bottom-12 left-14 w-60 bg-slate-800 rounded-xl shadow-2xl border border-slate-700 py-2 z-50 mb-2"
                    style="display: none;">
                    <div class="px-4 py-2 border-b border-slate-700/50 mb-2">
                        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Module Cấu Hình</h3>
                    </div>
                    
                    @if(auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('ctv.ranks') }}" wire:navigate
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors {{ request()->routeIs('ctv.ranks') ? 'bg-slate-700/50 text-blue-400 font-bold border-l-2 border-blue-500' : 'border-l-2 border-transparent' }}">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-ranking-star"></i>
                        </div>
                        <div class="flex-1">
                            <div class="leading-tight">Hạng CTV</div>
                            <div class="text-[10px] text-slate-500 font-normal mt-0.5">Xếp hạng & hiển thị giá</div>
                        </div>
                    </a>
                    @endif
                </div>

                <button @click="showSettings = !showSettings"
                    class="w-10 h-10 rounded-full bg-slate-800 hover:text-white flex items-center justify-center transition-all duration-200 {{ request()->routeIs('ctv.ranks') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-slate-400 hover:bg-slate-700' }}"
                    :class="{ 'bg-slate-700 text-white shadow-lg': showSettings }">
                    <i class="fa-solid fa-gear" :class="{ 'animate-[spin_3s_linear_infinite]': showSettings }"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div x-show="showUserMenu" @click.away="showUserMenu = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute bottom-0 left-14 w-52 bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden z-[60]"
                    style="display: none;">
                    <div class="p-2 border-b border-slate-700/50">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('business.detail', auth()->id()) }}" 
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-300 hover:bg-blue-500/10 hover:text-white transition-all font-bold text-xs group">
                            <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-500/10 group-hover:bg-blue-500/20 transition-colors">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                            Chi tiết cá nhân
                        </a>
                        @endif
                        <a href="{{ route('landing.ctv') }}" wire:navigate
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-300 hover:bg-emerald-500/10 hover:text-white transition-all font-bold text-xs group">
                            <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/10 group-hover:bg-emerald-500/20 transition-colors">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            Landing CTV
                        </a>
                    </div>
                    
                    <div class="p-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all font-bold text-xs group">
                                <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-500/10 group-hover:bg-red-500/20 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </div>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>

                <button @click="showUserMenu = !showUserMenu"
                    class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md transition-transform active:scale-90 hover:ring-2 hover:ring-white ring-2 ring-white">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </button>
            </div>
        </aside>

        <!-- Module Content Area -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50 relative">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</body>

</html>
