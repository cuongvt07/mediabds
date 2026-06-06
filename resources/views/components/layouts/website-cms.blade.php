<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Website CMS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; }
        .cms-scrollbar::-webkit-scrollbar { width: 10px; height: 10px; }
        .cms-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; border: 3px solid #f8fafc; }
        .cms-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
    </style>
</head>

@php
    $activeTab = request('tab', 'overview');
    $mainItems = [
        'overview' => ['Tong quan', 'fa-chart-pie', 'Suc khoe website public'],
        'home' => ['Trang user', 'fa-house-chimney-window', 'Cau hinh khoi trang chu'],
    ];
    $contentItems = [
        'listings' => ['Tin public', 'fa-newspaper', 'Duyet va hien thi tin'],
        'categories' => ['Danh muc', 'fa-layer-group', 'Danh muc website'],
        'blogs' => ['Blog', 'fa-pen-nib', 'Bai viet va SEO'],
    ];
    $engagementItems = [
        'leads' => ['Lead', 'fa-address-book', 'Yeu cau tu van'],
        'favorites' => ['Yeu thich', 'fa-heart', 'Hanh vi luu tin'],
        'saved-searches' => ['Tim kiem luu', 'fa-bookmark', 'Bo loc nguoi dung'],
        'analytics' => ['Analytics', 'fa-chart-line', 'Luot xem va do luong'],
    ];
@endphp

<body class="antialiased bg-slate-950 text-slate-800" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-screen lg:flex">
        <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden" @click="mobileMenuOpen = false" style="display: none;"></div>

        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-80 flex-col border-r border-slate-800 bg-slate-950 text-white transition-transform duration-300 lg:static lg:translate-x-0">
            <div class="border-b border-slate-800 px-5 py-5">
                <a href="{{ route('website.admin') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-globe text-lg"></i>
                    </span>
                    <span>
                        <span class="block text-[10px] font-black uppercase tracking-[0.28em] text-emerald-300">Website CMS</span>
                        <span class="mt-1 block text-lg font-black tracking-tight text-white">Phong Phat Land</span>
                    </span>
                </a>
            </div>

            <nav class="cms-scrollbar flex-1 overflow-y-auto px-4 py-5">
                <x-website-cms-nav-group title="Van hanh" :items="$mainItems" :active-tab="$activeTab" />
                <x-website-cms-nav-group title="Noi dung" :items="$contentItems" :active-tab="$activeTab" />
                <x-website-cms-nav-group title="Nguoi dung & do luong" :items="$engagementItems" :active-tab="$activeTab" />
            </nav>

            <div class="border-t border-slate-800 p-4">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('listings') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 px-3 py-2 text-xs font-black uppercase tracking-widest text-slate-300 hover:border-blue-400 hover:text-white">
                        <i class="fa-solid fa-arrow-left"></i> Admin
                    </a>
                    <a href="https://b-s-pink.vercel.app" target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-3 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-400">
                        <i class="fa-solid fa-up-right-from-square"></i> Site
                    </a>
                </div>
                <div class="mt-4 flex items-center gap-3 rounded-2xl bg-slate-900 p-3">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-tr from-emerald-500 to-cyan-500 text-sm font-black text-white">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-black text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ auth()->user()->phone ?? auth()->user()->email ?? '' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-red-500/10 hover:text-red-300" title="Dang xuat">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-h-screen flex-1 bg-slate-50 lg:min-w-0">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur lg:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" @click="mobileMenuOpen = true">
                            <i class="fa-solid fa-bars-staggered"></i>
                        </button>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-600">CMS quan tri website public</p>
                            <h1 class="mt-0.5 truncate text-xl font-black tracking-tight text-slate-900">Quan tri website BDS</h1>
                        </div>
                    </div>
                    <div class="hidden items-center gap-2 sm:flex">
                        <a href="{{ route('docs.api') }}" target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-600 hover:border-emerald-500 hover:text-emerald-600">
                            <i class="fa-solid fa-code"></i> API Docs
                        </a>
                    </div>
                </div>
            </header>

            <section class="cms-scrollbar h-[calc(100vh-65px)] overflow-y-auto">
                {{ $slot }}
            </section>
        </main>
    </div>

    @livewireScripts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</body>

</html>
