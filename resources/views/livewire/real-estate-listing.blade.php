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
        <div class="flex items-center gap-3 shrink-0 order-2 md:order-3">
            <a href="{{ route('media') }}"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-slate-600 px-3 py-2 md:px-4 md:py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-photo-film"></i> <span class="hidden md:inline">Media Manager</span>
            </a>
            <button wire:click="openCreatePopup"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-lg hover:shadow-xl transition-all whitespace-nowrap">
                <i class="fa-solid fa-plus"></i> <span class="hidden md:inline">Đăng Tin Mới</span><span
                    class="md:hidden">Đăng Tin</span>
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
                    $filter_type)
                <button wire:click="clearFilters"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-times-circle"></i> Xóa bộ lọc
                </button>
            @endif
        </div>

        <div x-show="showFilters" x-collapse class="grid grid-cols-1 md:grid-cols-5 gap-3">
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

            {{-- Property Type Filter --}}
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
            <!-- Note: User asked for "1 dòng 1 item" but in a grid context.
                 If 3/7 split is item internal, maybe they want a list of items stacked?
                 "12 item 1 trang" suggests pagination.
                 "item nằm ngang chia 3/7" suggests a horizontal card.
                 Let's do 1 column on small screens, maybe 2 on massive screens if space permits, or just 1 column stack list style.
                 Given "grid... 12 items/page", let's assume they want a list of these horizontal cards. -->

            @foreach ($listings as $listing)
                <div wire:key="{{ $listing['id'] }}-{{ $listing['updated_at'] }}"
                    wire:click="viewListingDetail({{ $listing['id'] }})"
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col md:flex-row h-auto md:h-48 group cursor-pointer relative">
                    <!-- Image (First Only) -->
                    <div class="w-full h-48 md:w-[30%] md:h-full bg-gray-200 relative overflow-hidden shrink-0 group">
                        <img src="{{ !empty($listing['images']) && count($listing['images']) > 0 ? $listing['images'][0] : 'https://placehold.co/600x400?text=No+Image' }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy" alt="{{ $listing['title'] }}">

                        <!-- Type Badge -->
                        <div
                            class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider z-10">
                            {{ $listing['type'] }}
                        </div>

                        <!-- Image Count Badge (Bottom Left) -->
                        @if (!empty($listing['images']) && count($listing['images']) > 1)
                            <div
                                class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded backdrop-blur-sm z-10 flex items-center gap-1">
                                <i class="fa-solid fa-camera"></i> <span>{{ count($listing['images']) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="w-full md:w-[70%] p-3 flex flex-col justify-between relative">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-1">
                                <h3 class="font-bold text-slate-800 text-lg leading-tight line-clamp-2 hover:text-blue-600 transition-colors flex-1"
                                    title="{{ $listing['title'] }}">
                                    {{ $listing['title'] }}
                                </h3>

                                <!-- Actions (Copy + Delete) -->
                                <div class="flex items-center gap-1 shrink-0" x-data="{ copied: false }">
                                    <button
                                        @click.stop="
                                            const text = `🏠 {{ $listing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$listing['address'], $listing['ward_name'], $listing['district_name'], $listing['province_name']])) }} \n💰 Giá: {{ number_format($listing['price'], 0, ',', '.') }} {{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($listing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $listing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $listing['bedrooms'] ?? 0 }} \n- Toilet: {{ $listing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($listing['front_width']) }}m \n- Lộ giới: {{ floatval($listing['road_width']) }}m \n------------------ \n📝 Mô tả: \n{{ $listing['description'] }}`;
                                            navigator.clipboard.writeText(text);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors relative"
                                        title="Copy thông tin">
                                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                                        <i class="fa-solid fa-check text-green-600" x-show="copied"
                                            style="display: none;"></i>
                                    </button>

                                    <button wire:click.stop="editListing({{ $listing['id'] }})"
                                        class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Sửa tin">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>

                                    <button wire:click.stop="deleteListing({{ $listing['id'] }})"
                                        wire:confirm="Bạn có chắc chắn muốn xóa tin này không?"
                                        class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Xóa tin">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-2">
                                <p class="text-gray-700 text-xs font-medium truncate flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-gray-500"></i> {{ $listing['address'] }}
                                </p>
                                <p class="text-gray-500 text-[11px]  truncate pl-4">
                                    {{ implode(', ', array_filter([$listing['ward_name'], $listing['district_name'], $listing['province_name']])) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-2">
                                <span
                                    class="font-bold text-red-500 text-lg">{{ number_format($listing['price'], 0, ',', '.') }}
                                    {{ $listing['price_unit'] == 1 ? 'VNĐ' : ($listing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }}</span>
                                <span class="w-px h-4 bg-gray-300"></span>
                                <span class="font-bold">{{ floatval($listing['area']) }} m²</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 mb-3">
                                <span title="Loại BĐS" class="bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold">
                                    {{ \App\Livewire\RealEstateListing::PROPERTY_TYPES[$listing['property_type']] ?? 'Khác' }}
                                </span>

                                @if ($listing['floors'])
                                    <span title="Số tầng"><i class="fa-solid fa-layer-group mr-1"></i>
                                        {{ $listing['floors'] }} Tầng</span>
                                @endif

                                @if ($listing['bedrooms'])
                                    <span title="Phòng ngủ"><i class="fa-solid fa-bed mr-1"></i>
                                        {{ $listing['bedrooms'] }} PN</span>
                                @endif

                                @if ($listing['toilets'])
                                    <span title="Toilet"><i class="fa-solid fa-bath mr-1"></i>
                                        {{ $listing['toilets'] }} WC</span>
                                @endif

                                @if ($listing['direction'])
                                    <span title="Hướng"><i class="fa-regular fa-compass mr-1"></i>
                                        {{ \App\Livewire\RealEstateListing::DIRECTIONS[$listing['direction']] ?? $listing['direction'] }}</span>
                                @endif

                                @if ($listing['front_width'])
                                    <span title="Mặt tiền"><i class="fa-solid fa-ruler-horizontal mr-1"></i> MT:
                                        {{ floatval($listing['front_width']) }}m</span>
                                @endif

                                @if ($listing['road_width'])
                                    <span title="Đường trước nhà"><i class="fa-solid fa-road mr-1"></i> Đường:
                                        {{ floatval($listing['road_width']) }}m</span>
                                @endif

                                @if ($listing['house_password'])
                                    <span title="Mật khẩu nhà" class="text-red-500 font-medium"><i
                                            class="fa-solid fa-key mr-1"></i>
                                        {{ $listing['house_password'] }}</span>
                                @endif

                                @if ($listing['contact_phone'])
                                    <span title="SĐT Liên hệ" class="text-green-600 font-bold"><i
                                            class="fa-solid fa-phone mr-1"></i>
                                        {{ $listing['contact_phone'] }}</span>
                                @endif
                            </div>

                            <!-- Description (New) -->
                            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-4">
                                {{ $listing['description'] }}
                            </p>

                            <!-- Footer -->
                            <div
                                class="mt-auto border-t border-gray-100 pt-3 flex justify-between items-center text-xs text-gray-400 font-medium">
                                {{ \Carbon\Carbon::parse($listing['created_at'])->diffForHumans() }}
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


    <!-- Create Listing Popup (User Provided HTML adapted) -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-2 md:p-4"
            x-transition.opacity>
            <div
                class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl flex flex-col max-h-[95vh] animate-[scaleIn_0.2s_ease-out]">

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
                            <label class="block text-sm font-bold text-gray-700 mb-1">SĐT Liên hệ</label>
                            <input wire:model="contact_phone" type="text" placeholder="0901234567"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mật khẩu nhà</label>
                            <input wire:model="house_password" type="text"
                                placeholder="Nhập mật khẩu nhà (số và chữ)"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
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

                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Diện tích (m²)</label>
                                <input wire:model="area" type="number"
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
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Mặt tiền (m)</label>
                                    <input wire:model="front_width" type="number"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-center focus:ring-2 focus:ring-blue-500 outline-none mt-1">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Lộ giới (m)</label>
                                    <input wire:model="road_width" type="number"
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

                            <div class="space-y-4">
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
                                            Tải ảnh từ máy tính
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
                                                <button type="button" wire:click="removeImage({{ $index }})"
                                                    class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
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

                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-gray-700 mb-1"><i
                                    class="fa-brands fa-youtube text-red-500 mr-1"></i>Link Youtube Review</label>
                            <input wire:model="youtube_link" type="url" placeholder="https://youtube.com/..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                    </form>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-2xl">
                    <button wire:click="closeCreatePopup"
                        class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-200 font-bold transition-colors">Hủy
                        bỏ</button>
                    <button wire:click="saveListing"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg hover:shadow-blue-500/30 transform active:scale-95 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        {{ $selectedListingId ? 'Lưu Thay Đổi' : 'Đăng Tin Nhà Đất' }}
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
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-2 md:p-4"
            x-transition.opacity>
            <div
                class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[95vh] animate-[scaleIn_0.2s_ease-out] overflow-hidden">

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
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                    {{-- Image Gallery --}}
                    <div class="mb-6" x-data="{
                        mainImage: {{ \Illuminate\Support\Js::from(!empty($selectedListing['images']) && count($selectedListing['images']) > 0 ? $selectedListing['images'][0] : 'https://placehold.co/800x600?text=No+Image') }},
                        images: {{ \Illuminate\Support\Js::from(!empty($selectedListing['images']) ? $selectedListing['images'] : ['https://placehold.co/800x600?text=No+Image']) }},
                        swapImage(newImage, index) {
                            const oldMain = this.mainImage;
                            this.mainImage = newImage;
                            this.images[index] = oldMain;
                        }
                    }">
                        {{-- Main Image --}}
                        <div class="w-full aspect-video bg-gray-200 rounded-xl overflow-hidden mb-4 shadow-lg">
                            <img :src="mainImage" class="w-full h-full object-cover"
                                alt="{{ $selectedListing['title'] }}">
                        </div>

                        {{-- Thumbnails --}}
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            <template x-for="(img, index) in images" :key="index">
                                <div @click="swapImage(img, index)"
                                    class="w-24 h-24 bg-gray-200 rounded-lg overflow-hidden cursor-pointer hover:ring-4 hover:ring-blue-400 transition-all shrink-0 shadow-md hover:shadow-xl">
                                    <img :src="img" class="w-full h-full object-cover">
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
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 shrink-0"
                    x-data="{ copied: false }">
                    <button
                        @click="
                            const text = `🏠 {{ $selectedListing['title'] }} \n📍 Vị trí: {{ implode(', ', array_filter([$selectedListing['address'], $selectedListing['ward_name'], $selectedListing['district_name'], $selectedListing['province_name']])) }} \n💰 Giá: {{ number_format($selectedListing['price'], 0, ',', '.') }} {{ $selectedListing['price_unit'] == 1 ? 'VNĐ' : ($selectedListing['price_unit'] == 2 ? 'VNĐ/tháng' : 'VNĐ/m2') }} \n📐 Diện tích: {{ floatval($selectedListing['area']) }} m² \n------------------ \n📋 Thông tin chi tiết: \n- Tầng: {{ $selectedListing['floors'] ?? 0 }} \n- Phòng ngủ: {{ $selectedListing['bedrooms'] ?? 0 }} \n- Toilet: {{ $selectedListing['toilets'] ?? 0 }} \n- Hướng: {{ \App\Livewire\RealEstateListing::DIRECTIONS[$selectedListing['direction']] ?? 'N/A' }} \n- Mặt tiền: {{ floatval($selectedListing['front_width']) }}m \n- Lộ giới: {{ floatval($selectedListing['road_width']) }}m \n------------------ \n📝 Mô tả: \n{{ $selectedListing['description'] }}`;
                            navigator.clipboard.writeText(text);
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold transition-all flex items-center gap-2 shadow-lg">
                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                        <i class="fa-solid fa-check" x-show="copied" style="display: none;"></i>
                        <span x-text="copied ? 'Đã Copy!' : 'Copy Thông Tin'"></span>
                    </button>
                    <button wire:click="editFromDetail"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all flex items-center gap-2 shadow-lg">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Chỉnh Sửa
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
