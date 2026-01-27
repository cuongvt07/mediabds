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
                    <img src="https://s3-hcm5-r1.longvan.net/phongland/2026/01/d13bf59afd35726b2b24.jpg" alt="Logo"
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

    <!-- Global Toast Component -->
    <div x-data="{ 
        messages: [],
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id)
        }
    }" 
    @toast.window="
        const id = Date.now();
        messages.push({ id, message: $event.detail[0].message, type: $event.detail[0].type });
        setTimeout(() => remove(id), 3000);
    "
    class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2">
        <template x-for="m in messages" :key="m.id">
            <div x-show="true" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                :class="{
                    'bg-green-600': m.type === 'success',
                    'bg-red-600': m.type === 'error',
                    'bg-blue-600': m.type === 'info'
                }"
                class="text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px]">
                <i :class="{
                    'fa-solid fa-check-circle': m.type === 'success',
                    'fa-solid fa-circle-exclamation': m.type === 'error',
                    'fa-solid fa-circle-info': m.type === 'info'
                }"></i>
                <span x-text="m.message" class="font-bold text-sm"></span>
            </div>
        </template>
    </div>
</body>

</html>
