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
            <div
                class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-900/50 mb-8 cursor-pointer hover:bg-blue-500 transition-colors">
                <span class="transform -rotate-12">🏠</span>
            </div>

            <!-- Navigation Modules -->
            <div class="flex flex-col gap-4 w-full px-2">
                <a href="{{ route('listings') }}"
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('listings') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Tin đăng BĐS">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('listings') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-newspaper text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Tin Đăng</span>
                </a>

                <a href="{{ route('media') }}"
                    class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all group {{ request()->routeIs('media') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                    title="Media Manager">
                    <div
                        class="w-8 h-8 flex items-center justify-center rounded-lg mb-1 {{ request()->routeIs('media') ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700' }}">
                        <i class="fa-solid fa-photo-film text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold text-center leading-none">Media</span>
                </a>
            </div>

            <!-- Bottom Actions -->
            <div class="mt-auto flex flex-col gap-4">
                <button
                    class="w-10 h-10 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-gear"></i>
                </button>
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-pink-500 text-white flex items-center justify-center font-bold text-xs shadow-lg">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
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
