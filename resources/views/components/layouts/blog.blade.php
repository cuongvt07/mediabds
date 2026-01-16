<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Real Estate Listings' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .header-gradient {
            background: linear-gradient(to right, #2563eb, #1d4ed8);
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">

    <!-- Header / Navigation -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <img src="https://s3-hcm5-r1.longvan.net/phongland/2025/12/logo.png" alt="Logo"
                        class="h-10 w-auto object-contain">
                    <div>
                        <h1 class="text-lg font-bold text-slate-800 tracking-tight leading-none">PHONGPHATLAND</h1>
                        <p class="text-[11px] text-gray-500 font-medium">Quản lý tin đăng & dữ liệu</p>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <!-- Link to Media Manager -->
                    <a href="{{ route('media') }}"
                        class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                        <i class="fa-solid fa-photo-film"></i>
                        <span>Media Manager</span>
                    </a>

                    <!-- User Profile Dropdown (Simplified) -->
                    <div class="flex items-center gap-3 pl-4 border-l border-gray-100">
                        <div class="text-right hidden md:block">
                            <div class="text-sm font-bold text-slate-700">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-500">Administrator</div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-[95%] w-full mx-auto px-4 sm:px-6 lg:px-8 py-2">
        {{ $slot }}
    </main>

    <footer class="bg-orange-500 border-t border-orange-600 mt-auto py-2">
        <div class="max-w-[95%] mx-auto px-4 text-center text-white text-xs font-medium">
            Phần mềm quản lý dữ liệu openfiles đã active thành công. Cảm ơn quý khách đã sử dụng dịch vụ chúng tôi!
        </div>
    </footer>

    @livewireScripts
</body>

</html>
