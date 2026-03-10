<div class="h-full flex flex-col bg-slate-50 relative">

    <!-- Header/Topbar -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200 px-4 md:px-8 py-4 md:py-6 flex flex-col md:flex-row items-center justify-between gap-6 shrink-0 sticky top-0 z-50">
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
        <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-end overflow-x-auto no-scrollbar pb-1 md:pb-0">
            <a href="https://phongphatland.com/"
                class="h-12 px-5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-sm transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-earth-asia text-base"></i> <span class="hidden sm:inline">Trang chủ</span>
            </a>
            <button wire:click="openCreatePopup"
                class="h-12 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-plus text-base"></i> <span>Đăng Tin Mới</span>
            </button>
            <div class="h-8 w-px bg-slate-200 mx-1 hidden md:block"></div>
            <button wire:click="exportExcel"
                class="h-12 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-[10px] uppercase tracking-widest flex items-center gap-3 shadow-lg shadow-emerald-500/20 transition-all whitespace-nowrap active:scale-95">
                <i class="fa-solid fa-file-excel text-base"></i> <span class="hidden sm:inline">Xuất Excel</span>
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

        @if($filter_phone)
        <div class="mb-3 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2.5 rounded-lg flex items-center justify-between text-sm shadow-sm animate-[fadeIn_0.3s_ease-out]">
            <div class="flex items-center gap-2 font-medium">
                <i class="fa-solid fa-address-book"></i> Đang lọc tin đăng liên quan đến khách hàng (SĐT: <span class="font-bold">{{ str_replace(',', ' hoặc ', $filter_phone) }}</span>)
            </div>
            <button wire:click="$set('filter_phone', null)" class="text-blue-600 hover:text-red-600 transition-colors font-bold whitespace-nowrap ml-3" title="Bỏ lọc theo SĐT khách">
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
            @endif

            {{-- Month Filter --}}
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em] mb-1.5 block">Tháng đăng</label>
                <select wire:model.live="filter_month"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                    <option value="">Tất cả</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">Tháng {{ $i }}</option>
                    @endfor
                </select>
            </div>

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
    <div class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach ($listings as $listing)
                <div wire:key="{{ $listing['id'] }}-{{ $listing['updated_at'] }}"
                    wire:click="viewListingDetail({{ $listing['id'] }})"
                    class="bg-white rounded-[2rem] shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 border border-slate-100 overflow-hidden flex flex-col lg:flex-row h-auto lg:h-64 group cursor-pointer relative">
                    <!-- Image -->
                    <div class="w-full h-64 lg:w-[40%] lg:h-full bg-slate-100 relative overflow-hidden shrink-0">
                        <img src="{{ !empty($listing['avatar']) ? $listing['avatar'] : (!empty($listing['images']) && count($listing['images']) > 0 ? $listing['images'][0] : 'https://placehold.co/600x400?text=No+Image') }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            loading="lazy" alt="{{ $listing['title'] }}">

                        <!-- Overlays -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <!-- Type Badge -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
                            <div class="bg-blue-600/90 backdrop-blur-xl text-white text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-[0.15em] shadow-xl shadow-blue-500/20">
                                {{ $listing['type'] }}
                            </div>
                            @if (!empty($listing['code']))
                                <div class="bg-slate-900/80 backdrop-blur-xl text-white text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-[0.1em] border border-white/10">
                                    {{ $listing['code'] }}
                                </div>
                            @endif
                            @if ($listing['is_sold'])
                                <div class="bg-red-500 text-white text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-[0.1em] flex items-center gap-2 shadow-lg">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                                    ĐÃ BÁN
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
                    <div class="flex-1 p-6 md:p-8 flex flex-col min-w-0">
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
                                            class="w-10 h-10 flex items-center justify-center rounded-2xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-90"
                                            title="Sửa tin">
                                            <i class="fa-regular fa-pen-to-square text-lg"></i>
                                        </button>
                                        <button wire:click.stop="deleteListing({{ $listing['id'] }})"
                                            wire:confirm="Bạn có chắc chắn muốn xóa tin này không?"
                                            class="w-10 h-10 flex items-center justify-center rounded-2xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all active:scale-90"
                                            title="Xóa tin">
                                            <i class="fa-regular fa-trash-can text-lg"></i>
                                        </button>
                                    @endif
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
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mật khẩu nhà</label>
                            <input wire:model="house_password" type="text" placeholder="Nhập mật khẩu nhà"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none text-sm font-bold shadow-inner">
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

                        {{-- Media Section --}}
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
                            </div>

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
        <div class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4 opacity-0 pointer-events-none transition-all duration-300"
            style="display: none;"
            x-init="setTimeout(() => { $el.style.display = 'flex'; $el.classList.remove('opacity-0', 'pointer-events-none') }, 10)">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" wire:click="closeSoldPopup"></div>
            <div class="relative bg-white w-full max-w-2xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl overflow-hidden translate-y-10 transition-transform duration-300">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800 uppercase flex items-center gap-3">
                        <span class="bg-emerald-500 text-white p-3 rounded-2xl shadow-lg shadow-emerald-500/20"><i class="fa-solid fa-file-signature"></i></span>
                        XÁC NHẬN GIAO DỊCH
                    </h3>
                    <button wire:click="closeSoldPopup" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Thông tin dự án/tin đăng</label>
                            <input wire:model="saleProjectName" type="text"
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none transition-all"
                                placeholder="Nhập tên dự án hoặc ghi chú tin đăng...">
                            @error('saleProjectName')
                                <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span>
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
