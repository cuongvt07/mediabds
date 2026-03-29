<div class="h-full flex flex-col bg-slate-50 relative">

    <!-- Header/Topbar for Real Estate Module -->
    <div
        class="bg-white border-b border-gray-200 px-4 md:px-6 py-4 flex flex-wrap md:flex-nowrap items-center justify-between gap-4 shrink-0">
        <!-- Title -->
        <!-- Centered Search -->
        <div class="order-3 md:order-2 w-full md:flex-1 md:max-w-xl">
            <div class="relative w-full">
                <input type="text" placeholder="Tìm kiếm tin đăng..." wire:model.live.debounce.300ms="search"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition-shadow focus:shadow-md">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex items-center gap-2 shrink-0 order-2 md:order-3">
            <!-- Mobile Dropdown for Secondary Actions -->
            <div class="relative md:hidden" x-data="{ open: false }">
                <button @click="open = !open"
                    class="bg-white border border-gray-200 text-slate-600 p-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="absolute left-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-50 py-1 overflow-hidden"
                    style="display: none;">
                    <a href="https://VM PHÚ THỊNH LAND.com/"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-arrow-left w-4"></i> Về Trang Chủ
                    </a>
                    <a href="{{ route('landing.ctv') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-emerald-700 hover:bg-emerald-50 transition-colors">
                        <i class="fa-solid fa-link w-4"></i> Landing CTV
                    </a>
                    <a href="{{ route('media') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-photo-film w-4"></i> Media Manager
                    </a>
                </div>
            </div>

            <!-- Desktop Secondary Actions -->
            <div class="hidden md:flex items-center gap-2">
                <a href="https://VM PHÚ THỊNH LAND.com/"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-arrow-left"></i> <span>Về Trang Chủ</span>
                </a>
                <a href="{{ route('landing.ctv') }}" wire:navigate
                    class="bg-white border border-emerald-200 hover:bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-link"></i> <span>Landing CTV</span>
                </a>
                <a href="{{ route('media') }}"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-photo-film"></i> <span>Media</span>
                </a>
            </div>

            <!-- Primary Actions (Visible both mobile & desktop) -->
            <button wire:click="exportExcel"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-bold text-xs md:text-sm flex items-center gap-1.5 md:gap-2 shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-file-excel"></i> <span class="hidden xs:inline">Xuất Excel</span>
            </button>
            <button wire:click="openCreatePopup"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-bold text-xs md:text-sm flex items-center gap-1.5 md:gap-2 shadow-lg hover:shadow-xl transition-all whitespace-nowrap">
                <i class="fa-solid fa-plus"></i> <span>Đăng Tin</span><span class="hidden xs:inline"> Mới</span>
            </button>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-3 shrink-0" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-2">
            <button @click="showFilters = !showFilters"
                class="text-sm font-bold text-gray-700 flex items-center gap-2 hover:text-blue-600 transition-colors">
                <i class="fa-solid fa-filter"></i>
                Bộ lọc
                <i class="fa-solid fa-chevron-down transition-transform" :class="{ 'rotate-180': showFilters }"></i>
            </button>

            @if (
                $filter_price_min ||
                    $filter_price_max ||
                    $filter_province ||
                    $filter_district ||
                    $filter_ward ||
                    $filter_property_type ||
                    $filter_type ||
                    $filter_month ||
                    $filter_year ||
                    $filter_phone ||
                    $filter_date_from ||
                    $filter_date_to ||
                    $filter_area_min ||
                    $filter_area_max ||
                    $filter_is_sold !== null)
                <button wire:click="clearFilters"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-times-circle"></i> Xóa bộ lọc
                </button>
            @endif
        </div>

        @if ($filter_phone)
            <div
                class="mb-3 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2.5 rounded-lg flex items-center justify-between text-sm shadow-sm animate-[fadeIn_0.3s_ease-out]">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-address-book"></i> Đang lọc tin đăng liên quan đến khách hàng (SĐT: <span
                        class="font-bold">{{ str_replace(',', ' hoặc ', $filter_phone) }}</span>)
                </div>
                <button wire:click="$set('filter_phone', null)"
                    class="text-blue-600 hover:text-red-600 transition-colors font-bold whitespace-nowrap ml-3"
                    title="Bỏ lọc theo SĐT khách">
                    <i class="fa-solid fa-times"></i> Đóng
                </button>
            </div>
        @endif

        <div x-show="showFilters" x-collapse class="grid grid-cols-1 md:grid-cols-6 gap-3">
            {{-- Sale/Rent Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Nhu cầu</label>
                <select wire:model.live="filter_type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    <option>Cần bán</option>
                    <option>Cho thuê</option>
                    <option>Cần mua</option>
                </select>
            </div>
            {{-- Front Width Filter --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Mặt tiền (m)</label>
                <input wire:model.live.debounce.500ms="filter_front_width" type="text" placeholder="VD: 5.5"
                    x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Price Min --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Giá từ</label>
                <input wire:model.live.debounce.500ms="filter_price_min" type="text" placeholder="VD: 1.000.000"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Price Max --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Giá đến</label>
                <input wire:model.live.debounce.500ms="filter_price_max" type="text" placeholder="VD: 5.000.000"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Property Type Filter (Admin only) --}}
            @if ($isAdmin)
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Loại nhà</label>
                    <select wire:model.live="filter_property_type"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Tất cả</option>
                        @foreach (\App\Livewire\RealEstateListing::PROPERTY_TYPES as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Sold Status Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Trạng thái</label>
                <select wire:model.live="filter_is_sold"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    <option value="0">Chưa bán</option>
                    <option value="1">Đã bán</option>
                </select>
            </div>

            {{-- Month Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Tháng đăng</label>
                <select wire:model.live="filter_month"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">Tháng {{ $i }}</option>
                    @endfor
                </select>
            </div>

            {{-- Year Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Năm đăng</label>
                <select wire:model.live="filter_year"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @php
                        $currentYear = date('Y');
                        $oldestYear = 2020; // Assume tracking started no earlier than 2020 realistically
                    @endphp
                    @for ($year = $currentYear; $year >= $oldestYear; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            {{-- Date From Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Từ ngày</label>
                <input type="date" wire:model.live="filter_date_from"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Date To Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Đến ngày</label>
                <input type="date" wire:model.live="filter_date_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Area Min Filter --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Diện tích từ (m²)</label>
                <input wire:model.live.debounce.500ms="filter_area_min" type="text" placeholder="VD: 50.5"
                    x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Area Max Filter --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Diện tích đến (m²)</label>
                <input wire:model.live.debounce.500ms="filter_area_max" type="text" placeholder="VD: 100.5"
                    x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Province Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Tỉnh/Thành</label>
                <select wire:model="filter_province" wire:change="loadFilterDistricts"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- District Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Quận/Huyện</label>
                <select wire:model="filter_district" wire:change="loadFilterWards"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    {{ empty($filter_province) ? 'disabled' : '' }}>
                    <option value="">Tất cả</option>
                    @foreach ($filter_districts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Ward Filter --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Phường/Xã</label>
                <select wire:model.live="filter_ward"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    {{ empty($filter_district) ? 'disabled' : '' }}>
                    <option value="">Tất cả</option>
                    @foreach ($filter_wards as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Main Content: Scrollable Grid -->
    <div class="flex-1 overflow-y-auto p-3">
        <!-- Pagination (Moved to top) -->
            <div class="w-full flex justify-center">
                {{ $listings->links('livewire.custom-pagination') }}
            </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-20">
            @foreach ($listings as $listing)
                <div wire:key="{{ $listing['id'] }}-{{ $listing['updated_at'] }}"
                    wire:click="viewListingDetail({{ $listing['id'] }})"
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col md:flex-row h-auto md:h-52 group cursor-pointer relative animate-[fadeIn_0.3s_ease-out]">
                    
                    <!-- Left: Image (30%) -->
                    <div class="w-full h-48 md:w-[30%] md:h-full bg-gray-200 relative overflow-hidden shrink-0">
                        <img src="{{ !empty($listing['avatar']) ? $listing['avatar'] : (!empty($listing['images']) && count($listing['images']) > 0 ? $listing['images'][0] : 'https://placehold.co/400x300?text=No+Img') }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy" alt="{{ $listing['title'] }}">

                        <!-- Type & Code Badges -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                            <span class="bg-blue-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                                {{ $listing['type'] }}
                            </span>
                            @if (!empty($listing['contact_type']))
                                <span class="bg-emerald-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                                    {{ $listing['contact_type'] }}
                                </span>
                            @endif
                            @if (!empty($listing['code']))
                                <span class="bg-slate-800 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm">
                                    {{ $listing['code'] }}
                                </span>
                            @endif
                            @if ($listing['is_sold'])
                                <span class="bg-red-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-check"></i> ĐÃ BÁN
                                </span>
                            @endif
                        </div>

                        <!-- Video Overlay -->
                        @if(!empty($listing['youtube_link']))
                            <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/20 transition-colors pointer-events-none">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white ring-1 ring-white/50">
                                    <i class="fa-solid fa-play ml-0.5"></i>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Info (70%) -->
                    <div class="w-full md:w-[70%] p-3 md:p-4 flex flex-col justify-between relative overflow-hidden">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-1.5">
                                <h3 class="font-bold text-slate-800 text-sm md:text-base leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors" title="{{ $listing['title'] }}">
                                    {{ $listing['title'] }}
                                </h3>
                                
                                {{-- Quick Actions --}}
                                <div class="flex items-center gap-1.5 shrink-0" x-data="{ copied: false }">
                                    <button @click.stop="
                                                const text = `🏠 {{ $listing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$listing['address'], $listing['ward_name'], $listing['district_name'], $listing['province_name']])) }} \n💰 Giá: {{ number_format($listing['price'], 0, ',', '.') }} {{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($listing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $listing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $listing['bedrooms'] ?? 0 }} \n- Toilet: {{ $listing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($listing['front_width']) }}m \n- Lộ giới: {{ floatval($listing['road_width']) }}m \n------------------ \n📝 Mô tả: \n{{ $listing['description'] }}`;
                                                navigator.clipboard.writeText(text);
                                                copied = true;
                                                setTimeout(() => copied = false, 2000);
                                            "
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors bg-gray-50 md:bg-transparent"
                                            title="Copy QC">
                                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                                        <i class="fa-solid fa-check" x-show="copied" style="display: none;"></i>
                                    </button>
                                    @if ($isAdmin)
                                        <button wire:click.stop="editListing({{ $listing['id'] }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition-colors bg-gray-50 md:bg-transparent">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click.stop="deleteListing({{ $listing['id'] }})"
                                            wire:confirm="Bạn có chắc chắn muốn xóa tin đăng này? Thao tác này có thể hoàn tác bởi Admin."
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-red-600 hover:bg-red-50 transition-colors bg-gray-50 md:bg-transparent">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 font-medium truncate flex items-center gap-1 mb-2">
                                <i class="fa-solid fa-location-dot text-gray-400"></i>
                                {{ implode(', ', array_filter([$listing['ward_name'], $listing['district_name'], $listing['province_name']])) }}
                            </p>

                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-black text-red-600 text-base">
                                    {{ number_format($listing['price'], 0, ',', '.') }}
                                    <span class="text-[10px] font-normal text-gray-400">
                                        {{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}
                                    </span>
                                </span>
                                <span class="w-px h-3 bg-gray-200"></span>
                                <span class="text-sm font-bold text-slate-700">{{ floatval($listing['area']) }} m²</span>
                            </div>

                            {{-- Facts --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-gray-500 font-medium mb-3">
                                <span class="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase transition-colors">
                                    {{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$listing['property_type']] ?? 'Khác' }}
                                </span>
                                @if($listing['floors']) <span><i class="fa-solid fa-layer-group text-gray-400 mr-1"></i>{{ $listing['floors'] }} T</span> @endif
                                @if($listing['bedrooms']) <span><i class="fa-solid fa-bed text-gray-400 mr-1"></i>{{ $listing['bedrooms'] }} PN</span> @endif
                                @if($listing['toilets']) <span><i class="fa-solid fa-restroom text-gray-400 mr-1"></i>{{ $listing['toilets'] }} WC</span> @endif
                                @if($listing['direction']) <span><i class="fa-regular fa-compass text-gray-400 mr-1"></i>{{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? 'N/A' }}</span> @endif
                                @if($listing['front_width']) <span><i class="fa-solid fa-ruler-horizontal text-gray-400 mr-1"></i>MT: {{ floatval($listing['front_width']) }}m</span> @endif
                                @if($listing['road_width']) <span><i class="fa-solid fa-road text-gray-400 mr-1"></i>Lộ: {{ floatval($listing['road_width']) }}m</span> @endif
                            </div>
                        </div>

                        {{-- Footer: Reporter & Timestamp --}}
                        <div class="mt-auto pt-3 border-t border-gray-50 flex justify-between items-center text-[10px] font-medium">
                            @if (!empty($listing['reporter']))
                                <div class="flex items-center gap-1.5">
                                    <div class="relative shrink-0">
                                        @if (!empty($listing['reporter']['avatar']))
                                            <img src="{{ url('storage/' . $listing['reporter']['avatar']) }}" 
                                                 class="w-5 h-5 rounded-md object-cover ring-1 ring-gray-100 shadow-sm">
                                        @else
                                            <div class="w-5 h-5 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center text-[8px] font-black">
                                                {{ mb_substr($listing['reporter']['name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-blue-600 rounded-full border border-white"></div>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-slate-800 font-bold truncate max-w-[80px] leading-tight">{{ $listing['reporter']['name'] }}</span>
                                        <span class="text-[8px] text-blue-500 uppercase tracking-tighter leading-none">Nguồn</span>
                                    </div>
                                </div>
                            @elseif(!empty($listing['user']))
                                <div class="flex items-center gap-1.5">
                                    <div class="shrink-0">
                                        @if (!empty($listing['user']['avatar']))
                                            <img src="{{ url('storage/' . $listing['user']['avatar']) }}" 
                                                 class="w-5 h-5 rounded-md object-cover ring-1 ring-gray-100 shadow-sm">
                                        @else
                                            <div class="w-5 h-5 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center text-[8px] font-black">
                                                {{ mb_substr($listing['user']['name'], 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-slate-500 font-bold truncate max-w-[80px] leading-tight">{{ $listing['user']['name'] }}</span>
                                </div>
                            @endif
                            <span class="text-gray-400 italic">
                                {{ \Carbon\Carbon::parse($listing['created_at'])->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


    <!-- Create Listing Popup (User Provided HTML adapted) -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all duration-300 overflow-hidden">
            <div
                class="bg-white rounded-t-[2.5rem] md:rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col max-h-[85dvh] md:max-h-[90dvh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden">

                <div
                    class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                    <h2 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i
                                class="fa-solid fa-pen-to-square"></i></span>
                        {{ $selectedListingId ? 'Cập Nhật Tin Đăng' : 'Tạo Tin Đăng Bất Động Sản' }}
                    </h2>
                    <button wire:click="closeCreatePopup"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <form class="grid grid-cols-1 md:grid-cols-12 gap-6">

                        <div class="md:col-span-9">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tiêu đề tin đăng <span
                                    class="text-red-500">*</span></label>
                            <input wire:model="title" type="text" placeholder="VD: Bán nhà mặt tiền Quận 1..."
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nhu cầu <span
                                    class="text-red-500">*</span></label>
                            <select wire:model.live="type"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                <option>Cần bán</option>
                                <option>Cho thuê</option>
                                <option>Cần mua</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Liên hệ</label>
                            <select wire:model="contact_type"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                <option value="">Chọn loại liên hệ</option>
                                <option value="Chủ">Chủ</option>
                                <option value="Môi giới">Môi giới</option>
                                <option value="Công ty">Công ty</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                            <input wire:model="contact_phone" type="text" placeholder="090..."
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm font-bold text-green-700">
                            @error('contact_phone')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-6 border-b pb-4 mb-2">
                            <div class="flex items-center justify-between mb-3" wire:key="customer-toggle-header">
                                <label class="text-sm font-bold text-gray-700">Thông tin khách hàng</label>
                                @if($customer_selection_mode === 'existing')
                                    <button type="button" wire:click="toggleCustomerMode('new')" wire:key="btn-new-cust"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors disabled:opacity-50">
                                        <i class="fa-solid fa-user-plus" wire:loading.remove></i>
                                        <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                                        Tạo khách mới
                                    </button>
                                @else
                                    <button type="button" wire:click="toggleCustomerMode('existing')" wire:key="btn-existing-cust"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-bold text-gray-500 hover:text-gray-700 flex items-center gap-1 transition-colors disabled:opacity-50">
                                        <i class="fa-solid fa-search" wire:loading.remove></i>
                                        <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                                        Tìm khách sẵn
                                    </button>
                                @endif
                            </div>

                            @if($customer_selection_mode === 'new')
                                <div class="space-y-3 p-4 bg-blue-50/50 rounded-2xl border border-blue-100" wire:key="new-customer-form">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">Họ và Tên <span class="text-red-500">*</span></label>
                                            <input wire:model="new_customer_name" type="text" placeholder="Nguyễn Văn A" 
                                                class="w-full border border-blue-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                            @error('new_customer_name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                            <input wire:model="new_customer_phone" type="text" placeholder="090..." 
                                                class="w-full border border-blue-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                            @error('new_customer_phone') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">Loại khách hàng</label>
                                        <select wire:model="new_customer_status" class="w-full border border-blue-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                            @foreach(\App\Models\Customer::STATUS_LABELS as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="relative group" wire:ignore wire:key="customer-select-container" x-data x-init="$nextTick(() => {
                                    const boot = () => {
                                        if (window.initSelect2) {
                                            window.initSelect2($refs.customerSelect, 'contact_phone', @js($contact_phone));
                                            return;
                                        }
                                        setTimeout(boot, 50);
                                    };
                                    boot();
                                })">
                                    <select x-ref="customerSelect" data-livewire-id="{{ $this->getId() }}"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm bg-white">
                                        <option value="">-- Tìm kiếm SĐT / Tên khách hàng sẵn --</option>
                                        @foreach($allCustomers as $c)
                                            <option value="{{ $c->phone }}" @selected($contact_phone === $c->phone)>
                                                {{ $c->phone }} - {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                <i class="fa-solid fa-user-tie text-blue-600 mr-1"></i> Người đưa tin
                            </label>
                            <div class="relative group" wire:ignore x-data x-init="$nextTick(() => {
                                const boot = () => {
                                    if (window.initSelect2) {
                                        window.initSelect2($refs.reporterSelect, 'reporter_id', @js($reporter_id));
                                        return;
                                    }
                                    setTimeout(boot, 50);
                                };
                                boot();
                            })">
                                <select x-ref="reporterSelect" data-livewire-id="{{ $this->getId() }}"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm bg-white">
                                    <option value="">-- Chọn người đưa tin --</option>
                                    @foreach($salesUsers as $u)
                                        <option value="{{ $u->id }}" @selected($reporter_id == $u->id)>
                                            {{ $u->name }} ({{ $u->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mật khẩu nhà</label>
                            <input wire:model="house_password" type="text"
                                placeholder="Nhập mật khẩu nhà (số và chữ)"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Mã tin đăng
                                <span class="text-xs text-gray-500 font-normal">(Để trống để tự động sinh)</span>
                            </label>
                            <input wire:model="code" type="text"
                                placeholder="VD: RE-1234567890-ABC123 (hoặc để trống)"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                            @if ($selectedListingId && !empty($code))
                                <p class="text-xs text-blue-600 mt-1">
                                    <i class="fa-solid fa-info-circle"></i> Mã hiện tại: <span
                                        class="font-bold">{{ $code }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Trạng thái giao dịch</label>
                            <div class="flex items-center gap-4 h-11">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_sold" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                    <span class="ml-3 text-sm font-bold text-gray-700">Đã bán/Cho thuê xong</span>
                                </label>
                            </div>
                        </div>

                        @if ($type !== 'Cần mua')
                            <div class="md:col-span-12 mt-2">
                                <p
                                    class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot"></i> Thông tin vị trí
                                </p>
                            </div>

                            <div class="md:col-span-3">
                                <label
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Tỉnh/Thành</label>
                                <select wire:model.live="province_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">Chọn tỉnh/thành</option>
                                    @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Quận/Huyện</label>
                                <select wire:model.live="district_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($districts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Phường/Xã</label>
                                <select wire:model.live="ward_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($wards as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <label
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Loại
                                    BĐS</label>
                                <select wire:model="property_type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="0">Chọn loại nhà đất</option>
                                    @foreach (\App\Livewire\RealEstateListing::PROPERTY_TYPES as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-12">
                                <label
                                    class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Địa
                                    chỉ chính xác</label>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input wire:model="address" type="text" placeholder="Số nhà, tên đường..."
                                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                </div>
                            </div>

                            <div class="md:col-span-12 mt-2">
                                <p
                                    class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-house-chimney"></i> Đặc điểm bất động sản
                                </p>
                            </div>

                            <div class="md:col-span-4" x-data>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Diện tích (m²)</label>
                                <input wire:model="area" type="text"
                                    x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-gray-800">
                            </div>

                            <div class="md:col-span-8 flex space-x-2">
                                <div class="flex-1" x-data>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mức giá</label>
                                    <input wire:model="price" type="text"
                                        x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-gray-800">
                                </div>
                                <div class="w-1/3">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Đơn vị</label>
                                    <select wire:model="price_unit"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="0">Chọn đơn giá</option>
                                        <option value="1">VNĐ</option>
                                        <option value="2">VNĐ/tháng</option>
                                        <option value="3">VNĐ/m2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 md:col-span-12">
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Số tầng</label>
                                    <input wire:model="floors" type="number"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">P.Ngủ</label>
                                    <input wire:model="bedrooms" type="number"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Toilet</label>
                                    <input wire:model="toilets" type="number"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Hướng</label>
                                    <select wire:model="direction"
                                        class="w-full border border-gray-300 rounded-lg px-1 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                        <option value="0">Chọn hướng nhà</option>
                                        <option value="1">Đông</option>
                                        <option value="2">Tây</option>
                                        <option value="3">Nam</option>
                                        <option value="4">Bắc</option>
                                        <option value="5">Đông bắc</option>
                                        <option value="6">Đông nam</option>
                                        <option value="7">Tây bắc</option>
                                        <option value="8">Tây nam</option>
                                    </select>
                                </div>
                                <div x-data>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Mặt tiền (m)</label>
                                    <input wire:model="front_width" type="text"
                                        x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                                <div x-data>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Lộ giới (m)</label>
                                    <input wire:model="road_width" type="text"
                                        x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g, '')"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                            </div>
                        @endif

                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả chi tiết</label>
                            <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-2 flex space-x-2">
                                <button type="button"
                                    class="p-1.5 hover:bg-gray-200 rounded font-bold text-gray-600 w-8">B</button>
                                <button type="button"
                                    class="p-1.5 hover:bg-gray-200 rounded italic text-gray-600 w-8">I</button>
                                <button type="button"
                                    class="p-1.5 hover:bg-gray-200 rounded underline text-gray-600 w-8">U</button>
                                <button type="button" class="p-1.5 hover:bg-gray-200 rounded text-gray-600 w-8"><i
                                        class="fa-solid fa-list-ul"></i></button>
                            </div>
                            <textarea wire:model="description"
                                class="w-full border-x border-b border-gray-300 rounded-b-lg p-3 h-40 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                placeholder="Mô tả chi tiết về bất động sản..."></textarea>
                        </div>

                        <div class="md:col-span-12 mt-4 space-y-4">
                            <p
                                class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                <i class="fa-solid fa-images"></i> Hình ảnh & Video
                            </p>

                            <!-- Representative Image (Avatar) -->
                            <div class="space-y-2 mb-4">
                                <label class="block text-sm font-bold text-gray-700">Ảnh đại diện (Avatar)</label>
                                <div class="flex gap-4 items-start">
                                    <div
                                        class="w-32 h-32 bg-gray-100 rounded-lg border border-gray-300 flex-shrink-0 relative overflow-hidden group">
                                        @if ($tempAvatar)
                                            @php
                                                $extension = strtolower($tempAvatar->getClientOriginalExtension());
                                                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                            @endphp
                                            @if ($isPreviewable)
                                                <img src="{{ $tempAvatar->temporaryUrl() }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex flex-col items-center justify-center bg-slate-800 text-[#00D1FF] p-2 text-center">
                                                    <i class="fa-solid fa-file-image fa-2x mb-1"></i>
                                                    <span class="text-[10px] font-black uppercase tracking-tighter">{{ $extension }}</span>
                                                    <span class="text-[8px] opacity-60 uppercase font-bold">No Preview</span>
                                                </div>
                                            @endif
                                            <button type="button" wire:click="$set('tempAvatar', null)"
                                                class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @elseif ($avatar)
                                            <img src="{{ $avatar }}" class="w-full h-full object-cover">
                                            <button type="button" wire:click="removeAvatar"
                                                class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @else
                                            <div
                                                class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-image fa-2x mb-1"></i>
                                                <span class="text-[10px]">Chưa có ảnh</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 relative group h-32">
                                        <input type="file" wire:model="tempAvatar"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                        <div
                                            class="bg-gray-50 hover:bg-gray-100 text-gray-500 px-6 py-4 rounded-xl border border-gray-200 border-dashed flex flex-col items-center justify-center gap-2 font-bold transition-all w-full h-full group-hover:border-blue-300 group-hover:text-blue-500">
                                            <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                                            Tải ảnh đại diện
                                            <span class="text-xs font-normal text-gray-400">Chọn 1 ảnh làm ảnh bìa
                                                listing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="block text-sm font-bold text-gray-700">Ảnh Slider (Chi tiết)</label>
                                <!-- Actions -->
                                <div class="flex gap-4">
                                    <!-- Select from Media -->
                                    <button type="button" wire:click="$set('showMediaPopup', true)"
                                        class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 px-6 py-4 rounded-xl border border-blue-200 border-dashed flex items-center justify-center gap-2 font-bold transition-all">
                                        <i class="fa-solid fa-folder-open"></i>
                                        Chọn từ Thư viện (Media)
                                    </button>

                                    <!-- Upload Local -->
                                    <div class="flex-1 relative group">
                                        <input type="file" wire:model="tempImages" multiple
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                        <div
                                            class="bg-gray-50 hover:bg-gray-100 text-gray-500 px-6 py-4 rounded-xl border border-gray-200 border-dashed flex items-center justify-center gap-2 font-bold transition-all w-full h-full group-hover:border-blue-300 group-hover:text-blue-500">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                            Tải ảnh slider từ máy tính
                                        </div>
                                    </div>
                                </div>

                                <!-- Previews -->
                                @if (!empty($images) || !empty($tempImages))
                                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                        <!-- Existing Images -->
                                        @foreach ($images as $index => $img)
                                            <div
                                                class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 group">
                                                <img src="{{ $img }}" class="w-full h-full object-cover">

                                                <div
                                                    class="absolute inset-0 bg-black/40 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                                    <button type="button"
                                                        wire:click="setAvatarFromImage({{ $index }})"
                                                        class="bg-white text-blue-600 text-[10px] font-bold px-2 py-1 rounded shadow-sm hover:bg-blue-50"
                                                        title="Đặt làm ảnh đại diện">
                                                        <i class="fa-solid fa-star"></i> Avatar
                                                    </button>
                                                    <button type="button"
                                                        wire:click="removeImage({{ $index }})"
                                                        class="bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center hover:bg-red-600">
                                                        <i class="fa-solid fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Temp Images -->
                                        @foreach ($tempImages as $index => $file)
                                            @php
                                                $extension = strtolower($file->getClientOriginalExtension());
                                                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                            @endphp
                                            <div
                                                class="relative aspect-square rounded-lg overflow-hidden border border-blue-200 ring-2 ring-blue-500 group">
                                                <!-- Just display image without spinner if loaded, livewire handles tempUrl -->
                                                @if ($isPreviewable)
                                                    <img src="{{ $file->temporaryUrl() }}"
                                                        class="absolute inset-0 w-full h-full object-cover">
                                                @else
                                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-800 text-[#00D1FF] p-2 text-center">
                                                        <i class="fa-solid fa-file-image fa-xl mb-1"></i>
                                                        <span class="text-[10px] font-black uppercase tracking-tighter">{{ $extension }}</span>
                                                    </div>
                                                @endif
                                                <button type="button"
                                                    wire:click="removeTempImage({{ $index }})"
                                                    class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-brands fa-youtube text-red-500 mr-1"></i>Link Youtube (Dài)</label>
                            <input wire:model="youtube_link" type="url" placeholder="https://youtube.com/watch?v=..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-brands fa-youtube text-red-500 mr-1"></i>Link Youtube (Ngắn/Short)</label>
                            <input wire:model="youtube_link_short" type="url" placeholder="https://youtube.com/shorts/..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-brands fa-facebook text-blue-600 mr-1"></i>Link Facebook</label>
                            <input wire:model="facebook_link" type="text" placeholder="https://facebook.com/..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('facebook_link')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-brands fa-tiktok text-pink-500 mr-1"></i>Link TikTok</label>
                            <input wire:model="tiktok_link" type="url"
                                placeholder="https://www.tiktok.com/@username..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('tiktok_link')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-solid fa-map-location-dot text-green-600 mr-1"></i>Link Google
                                Map</label>
                            <input wire:model="google_map_link" type="text"
                                placeholder="https://maps.google.com/..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            @error('google_map_link')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </form>
                </div>

                <div class="p-6 pb-12 md:pb-6 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-2xl">
                    <button wire:click="closeCreatePopup"
                        class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-200 font-bold transition-colors">Hủy
                        bỏ</button>
                    <button type="button" wire:click="saveListing" wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg hover:shadow-blue-500/30 transform active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-paper-plane" wire:loading.remove wire:target="saveListing"></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="saveListing"></i>
                        {{ $selectedListingId ? 'Lưu Thay Đổi' : 'Đăng Tin Nhà Đất' }}
                    </button>
                </div>

            </div>
        </div>
    @endif
    @if ($showMediaPopup)
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] flex items-end md:items-center justify-center p-0 md:p-4 transition-all duration-300 overflow-hidden">
            <div
                class="bg-white w-full max-w-6xl rounded-t-[2.5rem] md:rounded-2xl shadow-2xl overflow-hidden flex flex-col relative animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] max-h-[85dvh] md:max-h-[90dvh]">
                <!-- Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-800 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i
                                class="fa-solid fa-images"></i></span>
                        Chọn ảnh từ Thư viện
                    </h3>
                    <button wire:click="$set('showMediaPopup', false)"
                        class="text-gray-400 hover:text-red-500 w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition-colors">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-hidden">
                    @livewire('file-manager', ['isModeSelect' => true])
                </div>
            </div>
        </div>
    @endif

    {{-- Detail View Popup --}}
    @if ($showDetailPopup && $selectedListing)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all duration-300 overflow-hidden">
            <div
                class="bg-white w-full max-w-4xl rounded-t-[2.5rem] md:rounded-2xl shadow-2xl flex flex-col max-h-[85dvh] md:max-h-[90vh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden">

                {{-- Header --}}
                <div
                    class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 shrink-0">
                    <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                        <span class="bg-blue-600 text-white p-2 rounded-lg"><i
                                class="fa-solid fa-house-circle-check"></i></span>
                        Chi Tiết Tin Đăng
                    </h2>
                    <button wire:click="closeDetailPopup"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">
                    {{-- Image Gallery --}}
                    <div class="mb-6" x-data="{
                        mainImage: {{ \Illuminate\Support\Js::from(!empty($selectedListing['images']) && count($selectedListing['images']) > 0 ? $selectedListing['images'][0] : 'https://placehold.co/800x600?text=No+Image') }},
                        images: {{ \Illuminate\Support\Js::from(!empty($selectedListing['images']) ? $selectedListing['images'] : ['https://placehold.co/800x600?text=No+Image']) }},
                        selectedImages: [],
                        isDownloading: false,
                        getAllUniqueImages() {
                            return [...new Set([this.mainImage, ...this.images])];
                        },
                        swapImage(newImage, index) {
                            const oldMain = this.mainImage;
                            this.mainImage = newImage;
                            this.images[index] = oldMain;
                        },
                        toggleSelection(img) {
                            let idx = this.selectedImages.indexOf(img);
                            if (idx > -1) {
                                this.selectedImages.splice(idx, 1);
                            } else {
                                this.selectedImages.push(img);
                            }
                        },
                        isSelected(img) {
                            return this.selectedImages.includes(img);
                        },
                        selectAll() {
                            const allUnique = this.getAllUniqueImages();
                            if (this.selectedImages.length === allUnique.length) {
                                this.selectedImages = [];
                            } else {
                                this.selectedImages = [...allUnique];
                            }
                        },
                        downloadCount: 0,
                        downloadTotal: 0,
                        async downloadImages(urls) {
                            if (!urls || urls.length === 0) {
                                alert('Vui lòng chọn ít nhất 1 ảnh để tải về.');
                                return;
                            }
                            this.isDownloading = true;
                            this.downloadTotal = urls.length;
                            this.downloadCount = 0;
                    
                            for (let i = 0; i < urls.length; i++) {
                                this.downloadCount = i + 1;
                                await this.$wire.downloadSingleImage(urls[i]);
                                // Chờ 2s cho mobile kịp hoàn tất tải file trước khi bắt đầu file kế tiếp
                                if (i < urls.length - 1) {
                                    await new Promise(r => setTimeout(r, 2000));
                                }
                            }
                    
                            this.isDownloading = false;
                            this.downloadCount = 0;
                            this.downloadTotal = 0;
                            this.selectedImages = [];
                        }
                    }">
                        {{-- Controls --}}
                        <div
                            class="flex items-center justify-between mb-3 bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2 cursor-pointer" @click="selectAll()">
                                <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                    :class="selectedImages.length > 0 && selectedImages.length === getAllUniqueImages().length ?
                                        'bg-blue-600 border-blue-600 text-white' : 'border-gray-300'">
                                    <i class="fa-solid fa-check text-xs"
                                        x-show="selectedImages.length > 0 && selectedImages.length === getAllUniqueImages().length"></i>
                                </div>
                                <span
                                    class="text-sm font-bold text-gray-700 hover:text-blue-600 transition-colors">Chọn
                                    tất cả</span>
                                <span class="text-xs text-gray-500 ml-2" x-show="selectedImages.length > 0">
                                    (Đã chọn <span x-text="selectedImages.length"></span> ảnh)
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="downloadImages(selectedImages)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 md:px-4 md:py-2 rounded flex items-center gap-2 text-xs md:text-sm font-bold shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="selectedImages.length === 0 || isDownloading">
                                    <i class="fa-solid"
                                        :class="isDownloading ? 'fa-spinner fa-spin' : 'fa-download'"></i>
                                    <span class="hidden md:inline"
                                        x-text="isDownloading ? 'Đang tải ' + downloadCount + '/' + downloadTotal + '...' : 'Tải đã chọn'"></span>
                                    <span class="md:hidden"
                                        x-text="isDownloading ? downloadCount + '/' + downloadTotal : 'Tải' + (selectedImages.length ? ' (' + selectedImages.length + ')' : '')"></span>
                                </button>
                                <button type="button" @click="downloadImages(getAllUniqueImages())"
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 md:px-4 md:py-2 rounded flex items-center gap-2 text-xs md:text-sm font-bold shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isDownloading">
                                    <i class="fa-solid"
                                        :class="isDownloading ? 'fa-spinner fa-spin' : 'fa-cloud-arrow-down'"></i>
                                    <span class="hidden md:inline">Tải tất cả</span>
                                    <span class="md:hidden">Tải tất cả</span>
                                </button>
                            </div>
                        </div>

                        {{-- Main Image --}}
                        <div
                            class="w-full aspect-video bg-gray-200 rounded-xl overflow-hidden mb-4 shadow-lg relative group">
                            <img :src="mainImage" class="w-full h-full object-cover"
                                alt="{{ $selectedListing['title'] }}">

                            <!-- Selection Checkbox on Main Image -->
                            <div
                                class="absolute top-3 w-full px-3 flex justify-between items-start z-10 pointer-events-none">
                                <button type="button" @click.stop="toggleSelection(mainImage)"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all bg-white shadow-md border-2 pointer-events-auto"
                                    :class="isSelected(mainImage) ? 'border-blue-600 text-blue-600 bg-blue-50' :
                                        'border-gray-300 text-gray-400 hover:border-blue-400 hover:bg-white/90'">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Thumbnails --}}
                        <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                            <template x-for="(img, index) in images" :key="index">
                                <div @click="swapImage(img, index)"
                                    class="relative w-24 h-24 bg-gray-200 rounded-lg overflow-hidden cursor-pointer hover:ring-4 hover:ring-blue-400 transition-all shrink-0 shadow-md hover:shadow-xl group">
                                    <img :src="img" class="w-full h-full object-cover">

                                    <!-- Selection Checkbox -->
                                    <div class="absolute top-1 left-1 z-10">
                                        <button type="button" @click.stop="toggleSelection(img)"
                                            class="w-6 h-6 rounded-full flex items-center justify-center transition-all bg-white shadow-sm border"
                                            :class="isSelected(img) ? 'border-blue-600 text-blue-600 bg-blue-50' :
                                                'border-gray-200 text-gray-300 opacity-70 group-hover:opacity-100 hover:border-blue-400'">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Listing Information --}}
                    <div class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 mb-2">{{ $selectedListing['title'] }}</h3>
                            <span
                                class="inline-block bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ $selectedListing['type'] }}
                            </span>
                            @if ($selectedListing['contact_type'])
                                <span
                                    class="inline-block {{ $selectedListing['contact_type'] == 'Chủ' ? 'bg-green-600' : ($selectedListing['contact_type'] == 'Công ty' ? 'bg-indigo-600' : 'bg-orange-600') }} text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider ml-2">
                                    {{ $selectedListing['contact_type'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Location --}}
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <p class="text-gray-700 font-semibold flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-location-dot text-red-500"></i>
                                {{ $selectedListing['address'] }}
                            </p>
                            <p class="text-gray-500 text-sm pl-6">
                                {{ implode(', ', array_filter([$selectedListing['ward_name'], $selectedListing['district_name'], $selectedListing['province_name']])) }}
                            </p>
                        </div>

                        {{-- Price & Area --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-4 border border-red-200">
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Giá</p>
                                <p class="text-2xl font-black text-red-600">
                                    {{ number_format($selectedListing['price'], 0, ',', '.') }}
                                    {{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}
                                </p>
                            </div>
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Diện tích</p>
                                <p class="text-2xl font-black text-blue-600">{{ floatval($selectedListing['area']) }}
                                    m²</p>
                            </div>
                        </div>

                        {{-- External Links --}}
                        @if (!empty($selectedListing['facebook_link']) || !empty($selectedListing['google_map_link']) || !empty($selectedListing['youtube_link']) || !empty($selectedListing['tiktok_link']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @if (!empty($selectedListing['facebook_link']))
                                    <a href="{{ $selectedListing['facebook_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all group overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                        <i class="fa-brands fa-facebook text-lg"></i>
                                        <span>Bài viết Facebook</span>
                                    </a>
                                @endif
                                @if (!empty($selectedListing['youtube_link']))
                                    <a href="{{ $selectedListing['youtube_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all group overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                        <i class="fa-brands fa-youtube text-lg"></i>
                                        <span>Video Review (Dài)</span>
                                    </a>
                                @endif
                                @if (!empty($selectedListing['youtube_link_short']))
                                    <a href="{{ $selectedListing['youtube_link_short'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all group overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                        <i class="fa-brands fa-youtube text-lg"></i>
                                        <span>Video Shorts</span>
                                    </a>
                                @endif
                                @if (!empty($selectedListing['tiktok_link']))
                                    <a href="{{ $selectedListing['tiktok_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="bg-gradient-to-r from-pink-500 via-fuchsia-600 to-slate-900 hover:from-pink-600 hover:to-slate-900 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all group overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                        <i class="fa-brands fa-tiktok text-lg"></i>
                                        <span>Video TikTok</span>
                                    </a>
                                @endif
                                @if (!empty($selectedListing['google_map_link']))
                                    <a href="{{ $selectedListing['google_map_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all group overflow-hidden relative">
                                        <div class="absolute inset-x-0 bottom-0 h-1 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                                        <span>Google Map</span>
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Property Details --}}
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <h4 class="text-sm font-bold text-gray-700 uppercase mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-house-chimney text-blue-600"></i>
                                Thông tin chi tiết
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold">Loại BĐS:</span>
                                    <span
                                        class="font-bold text-gray-800">{{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$selectedListing['property_type']] ?? 'Khác' }}</span>
                                </div>
                                @if ($selectedListing['floors'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i
                                                class="fa-solid fa-layer-group"></i> Tầng:</span>
                                        <span class="font-bold text-gray-800">{{ $selectedListing['floors'] }}</span>
                                    </div>
                                @endif
                                @if ($selectedListing['bedrooms'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i class="fa-solid fa-bed"></i>
                                            Phòng ngủ:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ $selectedListing['bedrooms'] }}</span>
                                    </div>
                                @endif
                                @if ($selectedListing['toilets'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i class="fa-solid fa-bath"></i>
                                            Toilet:</span>
                                        <span class="font-bold text-gray-800">{{ $selectedListing['toilets'] }}</span>
                                    </div>
                                @endif
                                @if ($selectedListing['direction'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i
                                                class="fa-regular fa-compass"></i>
                                            Hướng:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ \App\Livewire\RealEstateListing::DIRECTIONS[$selectedListing['direction']] ?? $selectedListing['direction'] }}</span>
                                    </div>
                                @endif
                                @if ($selectedListing['front_width'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i
                                                class="fa-solid fa-ruler-horizontal"></i> Mặt tiền:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ floatval($selectedListing['front_width']) }}m</span>
                                    </div>
                                @endif
                                @if ($selectedListing['road_width'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i class="fa-solid fa-road"></i>
                                            Lộ giới:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ floatval($selectedListing['road_width']) }}m</span>
                                    </div>
                                @endif
                                @if ($selectedListing['house_password'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i class="fa-solid fa-key"></i>
                                            Mật khẩu nhà:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ $selectedListing['house_password'] }}</span>
                                    </div>
                                @endif
                                @if ($selectedListing['contact_phone'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 font-semibold"><i class="fa-solid fa-phone"></i>
                                            SĐT Liên hệ:</span>
                                        <span
                                            class="font-bold text-green-600">{{ $selectedListing['contact_phone'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (!empty($selectedListing['sale']))
                            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                                <h4 class="text-sm font-bold text-emerald-700 uppercase mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-handshake-angle"></i>
                                    Thông Tin Giao Dịch Đã Bán
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500 font-semibold">Dự án:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ $selectedListing['sale']['project_name'] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-semibold">Người bán:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ data_get($selectedListing, 'sale.sold_by.name', '-') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-semibold">Giá thực tế:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ number_format((float) data_get($selectedListing, 'sale.actual_price', 0), 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-semibold">Doanh thu (%):</span>
                                        <span
                                            class="font-bold text-gray-800">{{ number_format((float) data_get($selectedListing, 'sale.revenue_percent', 0), 2, ',', '.') }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-semibold">Doanh thu (tiền):</span>
                                        <span
                                            class="font-bold text-gray-800">{{ number_format((float) data_get($selectedListing, 'sale.revenue_amount', 0), 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 font-semibold">Thưởng:</span>
                                        <span
                                            class="font-bold text-gray-800">{{ number_format((float) data_get($selectedListing, 'sale.bonus_amount', 0), 0, ',', '.') }}</span>
                                    </div>
                                    <div class="md:col-span-2">
                                        <span class="text-gray-500 font-semibold">Thực nhận:</span>
                                        <span
                                            class="font-black text-emerald-700 text-base">{{ number_format((float) data_get($selectedListing, 'sale.net_received_amount', 0), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Description --}}
                        @if ($selectedListing['description'])
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 uppercase mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-align-left text-blue-600"></i>
                                    Mô tả
                                </h4>
                                <div class="text-gray-700 text-sm leading-relaxed break-words">
                                    {!! nl2br(e($selectedListing['description'])) !!}
                                </div>
                            </div>
                        @endif

                        {{-- Reporter & Publisher Info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            @if (!empty($selectedListing['reporter']))
                                <div class="flex items-center gap-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm group hover:shadow-md transition-all">
                                    <div class="relative shrink-0">
                                        @if (!empty($selectedListing['reporter']['avatar']))
                                            <img src="{{ str_starts_with($selectedListing['reporter']['avatar'], 'http') ? $selectedListing['reporter']['avatar'] : url('storage/' . $selectedListing['reporter']['avatar']) }}" 
                                                 class="w-14 h-14 rounded-2xl object-cover ring-4 ring-white shadow-md">
                                        @else
                                            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl font-black shadow-lg">
                                                {{ mb_substr($selectedListing['reporter']['name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="absolute -bottom-1 -right-1 bg-blue-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] ring-2 ring-white">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-0.5">Người đưa tin (Nguồn)</p>
                                        <h5 class="text-base font-bold text-slate-800 truncate">{{ $selectedListing['reporter']['name'] }}</h5>
                                        <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                                            <i class="fa-solid fa-phone text-blue-500"></i> {{ $selectedListing['reporter']['phone'] ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($selectedListing['user']))
                                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 shadow-sm group hover:shadow-md transition-all">
                                    <div class="relative shrink-0">
                                        @if (!empty($selectedListing['user']['avatar']))
                                            <img src="{{ str_starts_with($selectedListing['user']['avatar'], 'http') ? $selectedListing['user']['avatar'] : url('storage/' . $selectedListing['user']['avatar']) }}" 
                                                 class="w-14 h-14 rounded-2xl object-cover ring-4 ring-white shadow-md">
                                        @else
                                            <div class="w-14 h-14 rounded-2xl bg-slate-600 text-white flex items-center justify-center text-xl font-black shadow-lg">
                                                {{ mb_substr($selectedListing['user']['name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="absolute -bottom-1 -right-1 bg-slate-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] ring-2 ring-white">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Người đăng tin</p>
                                        <h5 class="text-base font-bold text-slate-800 truncate">{{ $selectedListing['user']['name'] }}</h5>
                                        <div class="flex flex-col gap-0.5 mt-0.5">
                                            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                                <i class="fa-solid fa-phone text-slate-400"></i> {{ $selectedListing['user']['phone'] ?? 'N/A' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                                <i class="fa-solid fa-clock text-slate-300"></i> {{ \Carbon\Carbon::parse($selectedListing['created_at'])->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="p-4 pb-12 md:pb-4 border-t border-gray-200 grid grid-cols-2 sm:flex sm:justify-end gap-3 bg-slate-50/80 backdrop-blur-md shrink-0 sticky bottom-0 z-20"
                    x-data="{ copied: false }">
                    @if ($selectedListing['contact_phone'])
                        <a href="tel:{{ $selectedListing['contact_phone'] }}"
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white text-sm md:text-base font-bold transition-all flex items-center justify-center gap-2 shadow-lg hover:shadow-emerald-500/20 active:scale-95">
                            <i class="fa-solid fa-phone-volume animate-pulse"></i>
                            <span>Gọi Ngay</span>
                        </a>
                    @endif
                    <button wire:click.stop="toggleSold({{ $selectedListing['id'] }})"
                        class="px-4 py-2.5 rounded-xl {{ $selectedListing['is_sold'] ? 'bg-slate-600 hover:bg-slate-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-sm md:text-base font-bold transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95">
                        <i
                            class="fa-solid {{ $selectedListing['is_sold'] ? 'fa-rotate-left' : 'fa-check-circle' }}"></i>
                        <span>{{ $selectedListing['is_sold'] ? 'Chưa bán' : 'Đã bán' }}</span>
                    </button>
                    <button
                        @click="
                            const text = `🏠 {{ $selectedListing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$selectedListing['address'], $selectedListing['ward_name'], $selectedListing['district_name'], $selectedListing['province_name']])) }} \n💰 Giá: {{ number_format($selectedListing['price'], 0, ',', '.') }} {{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($selectedListing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $selectedListing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $selectedListing['bedrooms'] ?? 0 }} \n- Toilet: {{ $selectedListing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$selectedListing['direction']] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($selectedListing['front_width']) }}m \n- Lộ giới: {{ floatval($selectedListing['road_width']) }}m \n------------------ \n📝 Mô tả: \n{{ $selectedListing['description'] }}`;
                            navigator.clipboard.writeText(text);
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm md:text-base font-bold transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95">
                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                        <i class="fa-solid fa-check" x-show="copied" style="display: none;"></i>
                        <span x-text="copied ? 'Đã Copy' : 'Copy QC'"></span>
                    </button>
                    @if ($isAdmin)
                        <button wire:click.stop="deleteListing({{ $selectedListing['id'] }})"
                            wire:confirm="Bạn có chắc chắn muốn xóa tin đăng này? Thao tác này có thể hoàn tác bởi Admin."
                            class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm md:text-base font-bold transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95">
                            <i class="fa-regular fa-trash-can"></i>
                            <span>Xóa Tin</span>
                        </button>
                    @endif
                    <button wire:click="editFromDetail"
                        class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm md:text-base font-bold transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Chỉnh Sửa</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    @if ($showSoldPopup)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end md:items-center justify-center z-[60] p-0 md:p-4">
            <div class="bg-white w-full max-w-4xl rounded-t-[2.5rem] md:rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85dvh] md:max-h-[90dvh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 shrink-0">
                    <h3 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                        <span class="bg-green-100 text-green-700 p-2 rounded-lg"><i
                                class="fa-solid fa-file-signature"></i></span>
                        Xác Nhận Đã Bán & Chia Lợi Nhuận
                    </h3>
                    <button wire:click="closeSoldPopup" class="text-gray-400 hover:text-red-500 transition-colors w-10 h-10 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6">
                    {{-- Project Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tên Dự Án / Tin Đăng</label>
                                <input wire:model="saleProjectName" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none shadow-sm transition-all"
                                    placeholder="Tên dự án...">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Giá Thực Tế</label>
                                    <input wire:model.live.debounce.500ms="saleActualPrice" type="text"
                                        x-data="{ val: @entangle('saleActualPrice') }"
                                        x-init="$watch('val', v => { if(v) val = v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') })"
                                        x-on:input="val = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); $wire.set('saleActualPrice', $el.value.replace(/[^0-9]/g, ''), true)"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none shadow-sm font-bold text-green-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">% Doanh Thu</label>
                                    <input wire:model.live.debounce.500ms="saleRevenuePercent" type="number"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none shadow-sm font-bold">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Thưởng Thêm</label>
                                <input wire:model.live.debounce.500ms="saleBonusAmount" type="text"
                                    x-data="{ val: @entangle('saleBonusAmount') }"
                                    x-init="$watch('val', v => { if(v) val = v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') })"
                                    x-on:input="val = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); $wire.set('saleBonusAmount', $el.value.replace(/[^0-9]/g, ''), true)"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none shadow-sm font-bold text-orange-600">
                            </div>
                        </div>

                        <div class="bg-slate-900 rounded-2xl p-6 text-white flex flex-col justify-center relative shadow-2xl overflow-hidden group">
                            <div class="absolute top-0 right-0 p-8 opacity-10 transform translate-x-4 -translate-y-4">
                                <i class="fa-solid fa-sack-dollar text-8xl"></i>
                            </div>
                            
                            <div class="relative z-10 space-y-4">
                                <div>
                                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Tổng Lợi Nhuận</p>
                                    <p class="text-4xl font-black text-emerald-400">
                                        {{ number_format((float) $saleNetAmount, 0, ',', '.') }} <span class="text-sm font-normal">VNĐ</span>
                                    </p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-700">
                                    <div>
                                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Doanh Thu</p>
                                        <p class="text-lg font-bold text-slate-200">{{ number_format((float) $saleRevenueAmount, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Thưởng</p>
                                        <p class="text-lg font-bold text-slate-200">{{ number_format((float) $saleBonusNumeric, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-white/5 rounded-xl border border-white/10">
                                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Lợi Nhuận Còn Lại</p>
                                    <p class="text-2xl font-black {{ $saleRemainingAmount < 0 ? 'text-red-400' : 'text-blue-400' }}">
                                        {{ number_format((float) $saleRemainingAmount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Members Sharing --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-md font-black text-gray-800 uppercase flex items-center gap-2">
                                <i class="fa-solid fa-users text-blue-500"></i>
                                Thành Viên Nhận Lợi Nhuận
                            </h4>
                            <button type="button" wire:click="addSaleMember"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-xs flex items-center gap-2 shadow-lg transition-all active:scale-95">
                                <i class="fa-solid fa-user-plus"></i> Thêm Người
                            </button>
                        </div>

                        <div class="space-y-2">
                            {{-- Header --}}
                            <div class="hidden md:flex gap-3 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <div class="flex-1">Thành viên</div>
                                <div class="w-64">Số tiền nhận (VNĐ)</div>
                                <div class="w-11"></div>
                            </div>

                            @foreach ($sale_members as $index => $member)
                                <div class="flex flex-col md:flex-row items-center gap-3 p-2 md:p-3 bg-white rounded-2xl border border-gray-100 shadow-sm group animate-[scaleIn_0.1s_ease-out]">
                                    <div class="flex-1 w-full" wire:ignore x-data x-init="$nextTick(() => {
                                        const boot = () => {
                                            if (window.initSelect2) {
                                                window.initSelect2($refs.userSelect, 'sale_members.{{ $index }}.user_id', @js($member['user_id']));
                                                return;
                                            }
                                            setTimeout(boot, 50);
                                        };
                                        boot();
                                    })">
                                        <select x-ref="userSelect" data-livewire-id="{{ $this->getId() }}"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50/50">
                                            <option value="">-- Chọn nhân viên --</option>
                                            @foreach ($salesUsers as $u)
                                                <option value="{{ $u->id }}" @selected($member['user_id'] == $u->id)>{{ $u->name }} ({{ $u->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-full md:w-64">
                                        <div class="relative" x-data="{ val: @entangle('sale_members.'.$index.'.received_amount') }"
                                            x-init="$watch('val', v => { if(v) val = v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') })">
                                            <input type="text" 
                                                x-model="val"
                                                x-on:input="val = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); $wire.set('sale_members.{{ $index }}.received_amount', $el.value.replace(/[^0-9]/g, ''), true)"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50/50 font-bold text-blue-700"
                                                placeholder="0">
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <button type="button" wire:click="removeSaleMember({{ $index }})"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($sale_members) === 0)
                                <div class="py-8 text-center border-2 border-dashed border-gray-100 rounded-2xl">
                                    <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-solid fa-users-slash text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-sm text-gray-500">Chưa có thành viên nào được thêm. Vui lòng bấm "Thêm Người".</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 pb-12 md:pb-4 border-t border-gray-200 bg-white flex justify-end gap-3 shrink-0">
                    <button wire:click="closeSoldPopup"
                        class="px-6 py-3 rounded-xl text-gray-600 hover:bg-gray-100 font-bold transition-colors">Đóng</button>
                    <button wire:click="saveSoldInformation"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-green-600 to-emerald-700 text-white font-black shadow-xl hover:shadow-green-500/20 transform active:scale-95 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i>
                        Xác Nhận Giao Dịch
                    </button>
                </div>
            </div>
        </div>
    @endif
    @once
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            window.initSelect2 = function(selectEl, wireProperty, selectedValue) {
                if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined' || !selectEl) {
                    return;
                }

                const $select = window.jQuery(selectEl);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                const $modal = $select.closest('.fixed');

                $select.select2({
                    width: '100%',
                    placeholder: 'Tìm kiếm...',
                    allowClear: true,
                    dropdownParent: $modal.length ? $modal : window.jQuery(document.body)
                });

                $select.val(selectedValue ? String(selectedValue) : '').trigger('change.select2');

                $select.off('change.select2-integration').on('change.select2-integration', function() {
                    const livewireId = this.getAttribute('data-livewire-id');
                    if (!livewireId || !window.Livewire) {
                        return;
                    }

                    const val = this.value === '' ? null : this.value;
                    window.Livewire.find(livewireId).set(wireProperty, val);
                });
            };
        </script>
    @endonce
    
    {{-- Floating Scroll to Top Button --}}
    <div x-data="{ show: false }" 
         x-on:scroll.window="show = window.pageYOffset > 500"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-24 right-6 z-50">
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-12 h-12 bg-blue-600/80 backdrop-blur-md hover:bg-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center transition-all hover:-translate-y-1 active:scale-95 group border border-white/20">
            <i class="fa-solid fa-arrow-up text-lg group-hover:animate-bounce"></i>
        </button>
    </div>
</div>
