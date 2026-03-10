<div class="h-full flex flex-col bg-slate-50 relative">

    <!-- Header/Topbar -->
    <div class="bg-white/90 backdrop-blur-2xl border-b border-slate-200 px-3 md:px-5 py-2 md:py-3 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0 sticky top-0 z-40">
        <!-- Title & Search Group -->
        <div class="flex items-center gap-6 w-full md:w-auto flex-1">
            <div class="hidden xl:flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-blue-500/20">
                    <i class="fa-solid fa-house-chimney text-xl"></i>
                </div>
                <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Bất Động Sản</h1>
            </div>
            
            <div class="relative w-full md:max-w-md group">
                <input type="text" placeholder="Tìm kiếm tin đăng..." wire:model.live.debounce.300ms="search"
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border-none rounded-[1.25rem] focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-sm shadow-inner transition-all group-hover:bg-slate-100">
                <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 transition-colors group-focus-within:text-blue-500"></i>
            </div>
        </div>

        <!-- Action Button -->
<<<<<<< HEAD
        <div
            class="flex items-center gap-2 sm:gap-3 shrink-0 order-2 md:order-3 overflow-x-auto no-scrollbar max-w-full pb-1 sm:pb-0 w-full sm:w-auto mt-2 sm:mt-0">
=======
        <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-end overflow-x-auto no-scrollbar pb-1 md:pb-0">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
            <a href="https://phongphatland.com/"
                class="h-12 px-5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-sm transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-earth-asia text-base"></i> <span class="">Trang chủ</span>
            </a>
            <button wire:click="openCreatePopup"
                class="h-12 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-plus text-base"></i> <span>Đăng Tin Mới</span>
            </button>
            <div class="h-8 w-px bg-slate-200 mx-1 hidden md:block"></div>
            <button wire:click="exportExcel"
                class="h-12 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-lg shadow-emerald-500/20 transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-file-excel text-base"></i> <span class="">Xuất Excel</span>
            </button>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white border-b border-slate-100 px-4 md:px-8 py-4 shrink-0" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between">
            <button @click="showFilters = !showFilters"
                class="group flex items-center gap-3 px-5 py-2.5 bg-slate-50 hover:bg-blue-50 rounded-2xl transition-all border border-transparent hover:border-blue-100">
                <div class="w-8 h-8 rounded-xl bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-blue-500 transition-colors">
                    <i class="fa-solid fa-filter text-xs"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest text-slate-600 group-hover:text-blue-600">Bộ lọc thông minh</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform duration-300 group-hover:text-blue-400" :class="{ 'rotate-180': showFilters }"></i>
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
                    $filter_is_sold !== null)
                <button wire:click="clearFilters"
                    class="h-10 px-4 text-[10px] font-black uppercase tracking-widest text-red-500 hover:bg-red-50 rounded-xl transition-all flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate"></i> LÀM MỚI
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

        <div x-show="showFilters" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-4 mt-4">
            {{-- Sale/Rent Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Nhu cầu</label>
                <select wire:model.live="filter_type"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                    <option value="">Tất cả</option>
                    <option>Cần bán</option>
                    <option>Cho thuê</option>
                    <option>Cần mua</option>
                </select>
            </div>
            {{-- Price Min --}}
            <div x-data>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Giá từ</label>
                <input wire:model.live.debounce.500ms="filter_price_min" type="text" placeholder="Thấp nhất"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
            </div>

            {{-- Price Max --}}
            <div x-data>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Giá đến</label>
                <input wire:model.live.debounce.500ms="filter_price_max" type="text" placeholder="Cao nhất"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
            </div>

            {{-- Property Type Filter (Admin only) --}}
            @if ($isAdmin)
<<<<<<< HEAD
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
=======
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Loại nhà</label>
                <select wire:model.live="filter_property_type"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                    <option value="">Tất cả</option>
                    @foreach (\App\Livewire\RealEstateListing::PROPERTY_TYPES as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
            @endif

            {{-- Month Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Tháng đăng</label>
                <select wire:model.live="filter_month"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                    <option value="">Tất cả</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">Tháng {{ $i }}</option>
                    @endfor
                </select>
            </div>

<<<<<<< HEAD
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



=======
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
            {{-- Province Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Tỉnh/Thành</label>
                <select wire:model="filter_province" wire:change="loadFilterDistricts"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                    <option value="">Tất cả</option>
                    @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- District Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Quận/Huyện</label>
                <select wire:model="filter_district" wire:change="loadFilterWards"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm"
                    {{ empty($filter_province) ? 'disabled' : '' }}>
                    <option value="">Tất cả</option>
                    @foreach ($filter_districts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Ward Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Phường/Xã</label>
                <select wire:model.live="filter_ward"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm"
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
    <div class="flex-1 overflow-y-auto p-2 md:p-3 custom-scrollbar">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($listings as $listing)
                <div wire:key="{{ $listing['id'] }}-{{ $listing['updated_at'] }}"
                    wire:click="viewListingDetail({{ $listing['id'] }})"
                    class="bg-white rounded-[2rem] shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 border border-slate-100 overflow-hidden flex flex-col lg:flex-row h-auto lg:h-60 group cursor-pointer relative">
                    <!-- Image -->
                    <div class="w-full h-64 lg:w-[40%] lg:h-full bg-slate-100 relative overflow-hidden shrink-0">
                        <img src="{{ !empty($listing['avatar']) ? $listing['avatar'] : (!empty($listing['images']) && count($listing['images']) > 0 ? $listing['images'][0] : 'https://placehold.co/600x400?text=No+Image') }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            loading="lazy" alt="{{ $listing['title'] }}">

                        <!-- Overlays -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <!-- Type Badge -->
<<<<<<< HEAD
                        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                            <div
                                class="bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
                                {{ $listing['type'] }}
                            </div>
                            @if (!empty($listing['code']))
                                <div
                                    class="bg-slate-700 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
=======
                        <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
                            <div class="bg-blue-600/90 backdrop-blur-xl text-white text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-[0.15em] shadow-xl shadow-blue-500/20">
                                {{ $listing['type'] }}
                            </div>
                            @if (!empty($listing['code']))
                                <div class="bg-slate-900/80 backdrop-blur-xl text-white text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-[0.1em] border border-white/10">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                    {{ $listing['code'] }}
                                </div>
                            @endif
                            @if ($listing['is_sold'])
<<<<<<< HEAD
                                <div
                                    class="bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid fa-check-circle"></i> ĐÃ BÁN
=======
                                <div class="bg-red-500 text-white text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-[0.1em] flex items-center gap-2 shadow-lg">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                                    ĐÃ BÁN
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                </div>
                            @endif
                        </div>

                        <!-- Image Count Badge -->
                        @if (!empty($listing['images']) && count($listing['images']) > 1)
                            <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-xl text-slate-800 text-[10px] font-black px-4 py-2 rounded-xl shadow-xl z-10 flex items-center gap-2">
                                <i class="fa-solid fa-images text-blue-500"></i> <span>{{ count($listing['images']) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 p-4 md:p-5 flex flex-col min-w-0">
                        <div class="flex-1">
                            <div class="flex justify-between items-start gap-4 mb-4">
                                <h3 class="font-black text-slate-800 text-lg md:text-xl leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors uppercase tracking-tight"
                                    title="{{ $listing['title'] }}">
                                    {{ $listing['title'] }}
                                </h3>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 shrink-0" x-data="{ copied: false }">
                                    <button
                                        @click.stop="
                                            const text = `🏠 {{ $listing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$listing['address'], $listing['ward_name'], $listing['district_name'], $listing['province_name']])) }} \n💰 Giá: {{ number_format($listing['price'], 0, ',', '.') }} {{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($listing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $listing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $listing['bedrooms'] ?? 0 }} \n- Toilet: {{ $listing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($listing['front_width']) }}m \n- Lộ giới: {{ floatval($listing['road_width']) }}m \n------------------ \n📝 Mô tả: \n{{ $listing['description'] }}`;
                                            navigator.clipboard.writeText(text);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="w-10 h-10 flex items-center justify-center rounded-2xl text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all active:scale-90"
                                        title="Copy thông tin">
                                        <i class="fa-regular fa-copy text-lg" x-show="!copied"></i>
                                        <i class="fa-solid fa-check text-emerald-500" x-show="copied" style="display: none;"></i>
                                    </button>

                                    @if ($isAdmin)
                                        <button wire:click.stop="editListing({{ $listing['id'] }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-90"
                                            title="Sửa tin">
                                            <i class="fa-regular fa-pen-to-square text-base"></i>
                                        </button>
                                        <button wire:click.stop="deleteListing({{ $listing['id'] }})"
                                            wire:confirm="Bạn có chắc chắn muốn xóa tin này không?"
                                            class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all active:scale-90"
                                            title="Xóa tin">
                                            <i class="fa-regular fa-trash-can text-base"></i>
                                        </button>
                                    @endif
                                    <button wire:click.stop="viewListingDetail({{ $listing['id'] }})"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-md transition-all">
                                        Xem chi tiết
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div class="flex items-center gap-3 text-slate-500">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-location-dot text-[10px]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black text-slate-700 truncate capitalize">{{ $listing['address'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold truncate">
                                            {{ implode(', ', array_filter([$listing['ward_name'], $listing['district_name'], $listing['province_name']])) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-6 pt-2">
                                    <div class="flex flex-col">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Mức giá</p>
                                        <p class="text-xl font-black text-red-500 flex items-baseline gap-1">
                                            {{ number_format($listing['price'], 0, ',', '.') }}
                                            <span class="text-[10px] uppercase font-bold text-slate-400">{{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}</span>
                                        </p>
                                    </div>
                                    <div class="w-px h-8 bg-slate-100"></div>
                                    <div class="flex flex-col">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Diện tích</p>
                                        <p class="text-xl font-black text-slate-800">{{ floatval($listing['area']) }} <span class="text-[10px] font-bold text-slate-400 uppercase leading-none">m²</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 mb-6">
                                <span class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-blue-100">
                                   {{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$listing['property_type']] ?? 'Khác' }}
                                </span>

                                @if ($listing['bedrooms'])
                                    <span class="px-3 py-1.5 bg-slate-50 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-tight flex items-center gap-2 border border-slate-100">
                                        <i class="fa-solid fa-bed text-blue-400"></i> {{ $listing['bedrooms'] }} PN
                                    </span>
                                @endif

                                @if ($listing['toilets'])
                                    <span class="px-3 py-1.5 bg-slate-50 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-tight flex items-center gap-2 border border-slate-100">
                                        <i class="fa-solid fa-bath text-blue-400"></i> {{ $listing['toilets'] }} WC
                                    </span>
                                @endif

                                @if ($listing['direction'])
                                    <span class="px-3 py-1.5 bg-slate-50 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-tight flex items-center gap-2 border border-slate-100">
                                        <i class="fa-regular fa-compass text-blue-400"></i> {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? $listing['direction'] }}
                                    </span>
                                @endif
                                
                                @if ($listing['contact_phone'])
                                    <span class="ml-auto px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                                        <i class="fa-solid fa-phone"></i> {{ $listing['contact_phone'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-50 pt-4">
                            <span class="flex items-center gap-2">
                                <i class="fa-regular fa-clock text-slate-300"></i> 
                                {{ \Carbon\Carbon::parse($listing['created_at'])->diffForHumans() }}
                            </span>
                            <div class="flex items-center gap-1 group-hover:text-blue-500 transition-colors">
                                XEM CHI TIẾT <i class="fa-solid fa-arrow-right-long ml-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $listings->links() }}
        </div>
    </div>


    <!-- Create Listing Popup -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4 opacity-0 pointer-events-none transition-all duration-300"
            style="display: none;"
            x-init="setTimeout(() => { $el.style.display = 'flex'; $el.classList.remove('opacity-0', 'pointer-events-none') }, 10)">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xl" wire:click="closeCreatePopup"></div>
            <div class="relative bg-white w-full max-w-5xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col max-h-[95vh] md:max-h-[90vh] translate-y-10 transition-transform duration-300 overflow-hidden">
                <div class="flex justify-between items-center px-8 py-6 border-b border-slate-50 shrink-0">
                    <div>
                        <div class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest mb-2">Quản lý nội dung</div>
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                            {{ $selectedListingId ? 'Cập Nhật Tin Đăng' : 'Đăng Tin BĐS Mới' }}
                        </h2>
                    </div>
                    <button wire:click="closeCreatePopup"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-red-500 transition-all active:scale-95">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        {{-- Basic Info --}}
                        <div class="md:col-span-9">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tiêu đề tin đăng <span class="text-red-500">*</span></label>
                            <input wire:model="title" type="text" placeholder="VD: Bán nhà mặt tiền Quận 1..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nhu cầu <span class="text-red-500">*</span></label>
                            <select wire:model.live="type"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                <option>Cần bán</option>
                                <option>Cho thuê</option>
                                <option>Cần mua</option>
                            </select>
                        </div>

                        {{-- Contact Info --}}
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Liên hệ</label>
                            <select wire:model="contact_type"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                <option value="">Chọn loại liên hệ</option>
                                <option value="Chủ">Chủ</option>
                                <option value="Môi giới">Môi giới</option>
                                <option value="Công ty">Công ty</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">SĐT Liên hệ</label>
                            <input wire:model="contact_phone" type="text" placeholder="0901234567"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                        </div>
                        <div class="md:col-span-6">
<<<<<<< HEAD
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
=======
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mật khẩu nhà</label>
                            <input wire:model="house_password" type="text" placeholder="Nhập mật khẩu nhà"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        </div>

                        @if ($type !== 'Cần mua')
                            <div class="md:col-span-12">
                                <div class="px-6 py-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-3">
                                        <i class="fa-solid fa-map-location-dot"></i> Thông tin vị trí & đặc điểm
                                    </p>
                                </div>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tỉnh/Thành</label>
                                <select wire:model.live="province_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                    <option value="">Chọn tỉnh/thành</option>
                                    @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Quận/Huyện</label>
                                <select wire:model.live="district_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($districts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Phường/Xã</label>
                                <select wire:model.live="ward_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($wards as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Loại BĐS</label>
                                <select wire:model="property_type"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner appearance-none">
                                    <option value="0">Chọn loại nhà đất</option>
                                    @foreach (\App\Livewire\RealEstateListing::PROPERTY_TYPES as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Địa chỉ chính xác</label>
                                <input wire:model="address" type="text" placeholder="Số nhà, tên đường..."
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Diện tích (m²)</label>
                                <input wire:model="area" type="number"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                            </div>
                            <div class="md:col-span-5">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mức giá</label>
                                <input wire:model="price" type="text"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Đơn vị</label>
                                <select wire:model="price_unit"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
                                    <option value="1">VNĐ</option>
                                    <option value="2">VNĐ/tháng</option>
                                    <option value="3">VNĐ/m2</option>
                                </select>
                            </div>
                        @endif

                        <div class="md:col-span-12">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mô tả chi tiết</label>
                            <textarea wire:model="description"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 h-40 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner resize-none"
                                placeholder="Mô tả chi tiết về bất động sản..."></textarea>
                        </div>

<<<<<<< HEAD
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
                                            <img src="{{ $tempAvatar->temporaryUrl() }}"
                                                class="w-full h-full object-cover">
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
                                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
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
                                            <div
                                                class="relative aspect-square rounded-lg overflow-hidden border border-blue-200 ring-2 ring-blue-500 group">
                                                <!-- Just display image without spinner if loaded, livewire handles tempUrl -->
                                                <img src="{{ $file->temporaryUrl() }}"
                                                    class="absolute inset-0 w-full h-full object-cover">
                                                <button type="button"
                                                    wire:click="removeTempImage({{ $index }})"
                                                    class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

=======
                        {{-- Media Section --}}
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        <div class="md:col-span-12">
                            <div class="px-6 py-4 bg-emerald-50/50 rounded-2xl border border-emerald-100/50 mb-6">
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-3">
                                    <i class="fa-solid fa-images"></i> Hình ảnh & Video Review
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <button type="button" wire:click="$set('showMediaPopup', true)"
                                    class="flex flex-col items-center justify-center gap-3 p-10 bg-slate-50 hover:bg-white rounded-3xl border-2 border-dashed border-slate-200 hover:border-blue-500 hover:text-blue-500 transition-all group">
                                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Chọn từ Thư viện</span>
                                </button>
                                
                                <div class="relative group">
                                    <input type="file" wire:model="tempImages" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                    <div class="flex flex-col items-center justify-center gap-3 p-10 bg-slate-50 hover:bg-white rounded-3xl border-2 border-dashed border-slate-200 group-hover:border-emerald-500 group-hover:text-emerald-500 transition-all">
                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300 group-hover:text-emerald-500 transition-colors"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Tải lên từ máy</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-slate-50 bg-slate-50/30 flex flex-col md:flex-row gap-4 shrink-0">
                    <button wire:click="closeCreatePopup"
                        class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-100 rounded-2xl transition-all">Đóng cửa sổ</button>
                    <button wire:click="saveListing" wire:loading.attr="disabled"
                        class="flex-1 px-8 py-5 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all flex items-center justify-center gap-3">
                        <i class="fa-solid fa-paper-plane" wire:loading.remove></i>
                        <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                        {{ $selectedListingId ? 'Lưu thay đổi ngay' : 'Đăng tin BĐS ngay' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if ($showMediaPopup)
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
            <div
                class="bg-white w-full h-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden flex flex-col relative animate-[scaleIn_0.2s_ease-out]">
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
        <div class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 opacity-0 pointer-events-none transition-all duration-300"
            style="display: none;"
            x-init="setTimeout(() => { $el.style.display = 'flex'; $el.classList.remove('opacity-0', 'pointer-events-none') }, 10)">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-xl" wire:click="closeDetailPopup"></div>
            <div class="relative bg-white w-full max-w-6xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col lg:flex-row max-h-[98vh] md:max-h-[95vh] translate-y-10 transition-transform duration-300 overflow-hidden">
                
                {{-- Left: Media Gallery --}}
                <div class="w-full lg:w-[60%] bg-slate-100 relative group shrink-0 min-h-[400px]">
                    <div x-data="{ 
                        mainImage: '{{ !empty($selectedListing['avatar']) ? $selectedListing['avatar'] : (!empty($selectedListing['images']) && count($selectedListing['images']) > 0 ? $selectedListing['images'][0] : 'https://placehold.co/800x600?text=Antigravity') }}'
                    }" class="h-full flex flex-col">
                        <div class="flex-1 relative overflow-hidden">
                            <img :src="mainImage" class="w-full h-full object-cover">
                            
                            <!-- Actions Overlay -->
                            <div class="absolute top-8 left-8 flex gap-4">
                                <button wire:click="closeDetailPopup"
                                    class="w-12 h-12 flex items-center justify-center bg-white/90 backdrop-blur hover:bg-white rounded-2xl shadow-xl text-slate-800 transition-all active:scale-90 lg:hidden">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        @if (!empty($selectedListing['images']) && count($selectedListing['images']) > 1)
                            <div class="p-6 bg-white/50 backdrop-blur-md border-t border-white/20">
                                <div class="flex gap-4 overflow-x-auto no-scrollbar">
                                    @foreach ($selectedListing['images'] as $img)
                                        <div @click="mainImage = '{{ $img }}'"
                                            class="w-20 h-20 rounded-2xl overflow-hidden cursor-pointer ring-2 ring-white shadow-lg shrink-0 transition-transform active:scale-90"
                                            :class="mainImage === '{{ $img }}' ? 'ring-blue-500 scale-105' : 'hover:scale-105'">
                                            <img src="{{ $img }}" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

<<<<<<< HEAD
                {{-- Content --}}
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
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
=======
                {{-- Right: Content --}}
                <div class="flex-1 flex flex-col bg-white overflow-y-auto custom-scrollbar">
                    <div class="p-8 md:p-10 space-y-10">
                        {{-- Title & Header --}}
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full">
                                    {{ $selectedListing['type'] }}
                                </span>
                                @if ($selectedListing['contact_type'])
                                    <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full">
                                        {{ $selectedListing['contact_type'] }}
                                    </span>
                                @endif
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-full">
                                    ID: {{ $selectedListing['id'] }}
                                </span>
                            </div>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-tight uppercase">{{ $selectedListing['title'] }}</h3>
                        </div>

                        {{-- Price Card --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-red-50 p-8 rounded-[2rem] border border-red-100">
                                <p class="text-[10px] font-black text-red-400 uppercase tracking-[0.2em] mb-3">Mức giá giao dịch</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-red-600 tracking-tight">{{ number_format($selectedListing['price'], 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-black text-red-400 uppercase">{{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}</span>
                                </div>
                            </div>
                            <div class="bg-blue-50 p-8 rounded-[2rem] border border-blue-100">
                                <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-3">Diện tích sử dụng</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-blue-600 tracking-tight">{{ floatval($selectedListing['area']) }}</span>
                                    <span class="text-[10px] font-black text-blue-400 uppercase">M²</span>
                                </div>
                            </div>
                        </div>

                        {{-- Information List --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Vị trí tài sản</p>
                                    <p class="text-sm font-bold text-slate-800 tracking-tight">{{ $selectedListing['address'] }}</p>
                                    <p class="text-[11px] text-slate-500 font-bold">
                                        {{ implode(', ', array_filter([$selectedListing['ward_name'], $selectedListing['district_name'], $selectedListing['province_name']])) }}
                                    </p>
                                </div>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            </div>

<<<<<<< HEAD
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

                        {{-- External Links (Facebook & Google Map) --}}
                        @if (!empty($selectedListing['facebook_link']) || !empty($selectedListing['google_map_link']))
                            <div class="flex flex-wrap gap-3">
                                @if (!empty($selectedListing['facebook_link']))
                                    <a href="{{ $selectedListing['facebook_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex-1 min-w-[140px] bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all">
                                        <i class="fa-brands fa-facebook text-lg"></i>
                                        Xem Bài viết
                                    </a>
                                @endif
                                @if (!empty($selectedListing['google_map_link']))
                                    <a href="{{ $selectedListing['google_map_link'] }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex-1 min-w-[140px] bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-4 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all">
                                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                                        Xem Google Map
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
=======
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Thông số chi tiết</p>
                                    <div class="space-y-3">
                                        @if ($selectedListing['floors'])
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-slate-500 font-bold uppercase">Số tầng</span>
                                                <span class="font-black text-slate-800">{{ $selectedListing['floors'] }}</span>
                                            </div>
                                        @endif
                                        @if ($selectedListing['bedrooms'])
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-slate-500 font-bold uppercase">Phòng ngủ</span>
                                                <span class="font-black text-slate-800">{{ $selectedListing['bedrooms'] }}</span>
                                            </div>
                                        @endif
                                        @if ($selectedListing['direction'])
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-slate-500 font-bold uppercase">Hướng</span>
                                                <span class="font-black text-slate-800">{{ \App\Livewire\RealEstateListing::DIRECTIONS[$selectedListing['direction']] ?? $selectedListing['direction'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kích thước</p>
                                    <div class="space-y-3">
                                        @if ($selectedListing['front_width'])
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-slate-500 font-bold uppercase">Mặt tiền</span>
                                                <span class="font-black text-slate-800">{{ floatval($selectedListing['front_width']) }}m</span>
                                            </div>
                                        @endif
                                        @if ($selectedListing['road_width'])
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-slate-500 font-bold uppercase">Lộ giới</span>
                                                <span class="font-black text-slate-800">{{ floatval($selectedListing['road_width']) }}m</span>
                                            </div>
                                        @endif
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                                <i class="fa-solid fa-align-left"></i> Mô tả chi tiết
                            </p>
                            <div class="text-sm text-slate-600 leading-relaxed font-medium bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
                                {!! nl2br(e($selectedListing['description'])) !!}
                            </div>
                        </div>

                        {{-- Footer Actions Desktop --}}
                        <div class="pt-10 border-t border-slate-50 flex flex-wrap gap-4 shrink-0 pb-10">
                            @if ($selectedListing['contact_phone'])
                                <a href="tel:{{ $selectedListing['contact_phone'] }}"
                                    class="h-16 px-8 bg-emerald-500 text-white rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-3 shadow-xl shadow-emerald-500/20 active:scale-95 transition-all flex-1">
                                    <i class="fa-solid fa-phone-volume text-base"></i> GỌI TƯ VẤN NGAY
                                </a>
                            @endif
                            <button wire:click.stop="toggleSold({{ $selectedListing['id'] }})"
                                class="h-16 px-8 bg-slate-100 text-slate-500 rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-3 active:scale-95 transition-all">
                                <i class="fa-solid {{ $selectedListing['is_sold'] ? 'fa-rotate-left' : 'fa-check-circle' }}"></i>
                                {{ $selectedListing['is_sold'] ? 'Đánh dấu chưa bán' : 'Xác nhận đã bán' }}
                            </button>
                            <button wire:click="editFromDetail"
                                class="h-16 w-16 bg-blue-50 text-blue-600 rounded-[1.25rem] flex items-center justify-center active:scale-95 transition-all">
                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Close Button Desktop -->
                <button wire:click="closeDetailPopup"
                    class="absolute top-8 right-8 w-12 h-12 items-center justify-center bg-white/20 hover:bg-white/40 backdrop-blur rounded-2xl text-white transition-all hidden lg:flex active:scale-90">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Sold Popup --}}
    @if ($showSoldPopup)
<<<<<<< HEAD
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60] p-4">
            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-black text-gray-800 uppercase flex items-center gap-2">
                        <span class="bg-green-100 text-green-700 p-2 rounded-lg"><i
                                class="fa-solid fa-file-signature"></i></span>
                        Nhập Thông Tin Đã Bán
=======
        <div class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 opacity-0 pointer-events-none transition-all duration-300"
            style="display: none;"
            x-init="setTimeout(() => { $el.style.display = 'flex'; $el.classList.remove('opacity-0', 'pointer-events-none') }, 10)">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" wire:click="closeSoldPopup"></div>
            <div class="relative bg-white w-full max-w-2xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl overflow-hidden translate-y-10 transition-transform duration-300">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800 uppercase flex items-center gap-3">
                        <span class="bg-emerald-500 text-white p-3 rounded-2xl shadow-lg shadow-emerald-500/20"><i class="fa-solid fa-file-signature"></i></span>
                        XÁC NHẬN GIAO DỊCH
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                    </h3>
                    <button wire:click="closeSoldPopup" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

<<<<<<< HEAD
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Dự án</label>
                        <input wire:model="saleProjectName" type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 outline-none"
                            placeholder="Tên dự án / tên tin đăng">
                        @error('saleProjectName')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Người bán</label>
                        <select wire:model="saleUserId"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">Chọn tài khoản đã bán</option>
                            @foreach ($salesUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}{{ $u->phone ? ' - ' . $u->phone : '' }}</option>
                            @endforeach
                        </select>
                        @error('saleUserId')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Giá thực tế</label>
                            <input wire:model.live="saleActualPrice" type="text"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 outline-none"
                                placeholder="Ví dụ: 4.500.000.000">
                            @error('saleActualPrice')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
=======
                <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Thông tin dự án/tin đăng</label>
                            <input wire:model="saleProjectName" type="text"
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"
                                placeholder="Nhập tên dự án hoặc ghi chú tin đăng...">
                            @error('saleProjectName')
                                <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tài khoản môi giới đã bán</label>
                            <div class="relative group">
                                <select wire:model="saleUserId"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none appearance-none transition-all">
                                    <option value="">Chọn thành viên...</option>
                                    @foreach ($salesUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}{{ $u->phone ? ' - ' . $u->phone : '' }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                            @error('saleUserId')
                                <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Giá trị giao dịch (VNĐ)</label>
                                <input wire:model.live="saleActualPrice" type="text"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-emerald-600 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"
                                    placeholder="4.500.000.000">
                                @error('saleActualPrice')
                                    <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">% Hoa hồng doanh thu</label>
                                <div class="relative">
                                    <input wire:model.live="saleRevenuePercent" type="text"
                                        class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"
                                        placeholder="1.5">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">%</span>
                                </div>
                                @error('saleRevenuePercent')
                                    <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

<<<<<<< HEAD
                    <div
                        class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="text-gray-500">Doanh thu</div>
                            <div class="font-bold text-emerald-700">
                                {{ number_format((float) $saleRevenueAmount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Thưởng</div>
                            <div class="font-bold text-emerald-700">
                                {{ number_format((float) $saleBonusNumeric, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Thực nhận</div>
                            <div class="font-black text-emerald-700 text-base">
                                {{ number_format((float) $saleNetAmount, 0, ',', '.') }}</div>
=======
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tiền thưởng nóng kèm theo (VNĐ)</label>
                            <input wire:model.live="saleBonusAmount" type="text"
                                x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-amber-600 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"
                                placeholder="20.000.000">
                            @error('saleBonusAmount')
                                <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[2rem] p-8 shadow-2xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 relative z-10">
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Doanh thu</p>
                                    <p class="text-sm font-black text-white">{{ number_format((float) $saleRevenueAmount, 0, ',', '.') }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Tiền thưởng</p>
                                    <p class="text-sm font-black text-amber-400">{{ number_format((float) $saleBonusNumeric, 0, ',', '.') }}</p>
                                </div>
                                <div class="col-span-2 md:col-span-1 space-y-1 pt-4 md:pt-0 border-t md:border-t-0 md:border-l border-white/5 md:pl-6">
                                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest">Thực nhận</p>
                                    <p class="text-xl font-black text-emerald-400 tracking-tight">{{ number_format((float) $saleNetAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-slate-50/50 border-t border-slate-50 flex flex-col md:flex-row gap-4">
                    <button wire:click="closeSoldPopup"
                        class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-100 rounded-2xl transition-all flex-1">Hủy bỏ</button>
                    <button wire:click="saveSoldInformation"
                        class="px-10 py-5 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-emerald-500/20 hover:bg-emerald-600 active:scale-95 transition-all flex-[2] flex items-center justify-center gap-3">
                        <i class="fa-solid fa-check-double"></i>
                        XÁC NHẬN & QUAY LẠI
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
