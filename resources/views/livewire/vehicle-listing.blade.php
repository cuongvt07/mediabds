<div class="h-full flex flex-col bg-slate-50 relative">

    <!-- Header/Topbar -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-4 flex flex-wrap md:flex-nowrap items-center justify-between gap-4 shrink-0">
        <!-- Centered Search -->
        <div class="order-3 md:order-2 w-full md:flex-1 md:max-w-xl">
            <div class="relative w-full">
                <input type="text" placeholder="Tìm kiếm tin xe..." wire:model.live.debounce.300ms="search"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition-shadow focus:shadow-md">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 shrink-0 order-2 md:order-3">
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('listings') }}" wire:navigate
                    class="bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-house"></i> <span>Tin BĐS</span>
                </a>
                <a href="{{ route('media') }}"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-photo-film"></i> <span>Media</span>
                </a>
            </div>

            <button wire:click="openCreatePopup"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-bold text-xs md:text-sm flex items-center gap-1.5 md:gap-2 shadow-lg hover:shadow-xl transition-all whitespace-nowrap">
                <i class="fa-solid fa-car"></i> <span>Đăng Tin</span><span class="hidden xs:inline"> Xe</span>
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
                $filter_type ||
                    $filter_vehicle_type ||
                    $filter_brand ||
                    $filter_price_min ||
                    $filter_price_max ||
                    $filter_province ||
                    $filter_district ||
                    $filter_month ||
                    $filter_year ||
                    $filter_date_from ||
                    $filter_date_to ||
                    $filter_is_sold !== null)
                <button wire:click="clearFilters"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-times-circle"></i> Xóa bộ lọc
                </button>
            @endif
        </div>

        <div x-show="showFilters" x-collapse class="grid grid-cols-1 md:grid-cols-6 gap-3">
            {{-- Nhu cầu --}}
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

            {{-- Loại xe --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Loại xe</label>
                <select wire:model.live="filter_vehicle_type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @foreach ($vehicleTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Hãng --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Hãng xe</label>
                <select wire:model.live="filter_brand"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @foreach ($filterBrandOptions as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Giá từ --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Giá từ</label>
                <input wire:model.live.debounce.500ms="filter_price_min" type="text" placeholder="VD: 100"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Giá đến --}}
            <div x-data>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Giá đến</label>
                <input wire:model.live.debounce.500ms="filter_price_max" type="text" placeholder="VD: 800"
                    x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Trạng thái --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Trạng thái</label>
                <select wire:model.live="filter_is_sold"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    <option value="0">Chưa bán</option>
                    <option value="1">Đã bán</option>
                </select>
            </div>

            {{-- Tháng --}}
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

            {{-- Năm --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Năm đăng</label>
                <select wire:model.live="filter_year"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @php $currentYear = date('Y'); @endphp
                    @for ($year = $currentYear; $year >= 2020; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            {{-- Từ ngày --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Từ ngày</label>
                <input type="date" wire:model.live="filter_date_from"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Đến ngày --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Đến ngày</label>
                <input type="date" wire:model.live="filter_date_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Tỉnh/Thành --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Tỉnh/Thành</label>
                <select wire:model.live="filter_province"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Tất cả</option>
                    @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Quận/Huyện --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase mb-1 block">Quận/Huyện</label>
                <select wire:model.live="filter_district"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    {{ empty($filter_province) ? 'disabled' : '' }}>
                    <option value="">Tất cả</option>
                    @foreach ($filter_districts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-3">
        <!-- Pagination (top) -->
        <div class="w-full flex justify-center">
            {{ $vehicles->links('livewire.custom-pagination') }}
        </div>

        @if ($vehicles->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-gray-400">
                <i class="fa-solid fa-car-side text-6xl mb-4 opacity-20"></i>
                <p class="font-bold">Chưa có tin xe nào.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-20">
                @foreach ($vehicles as $v)
                    <x-vehicle-card :vehicle="$v" :isAdmin="$isAdmin" mode="grid" />
                @endforeach
            </div>
        @endif

        {{-- Create / Edit Popup --}}
        @if ($showCreatePopup)
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all duration-300 overflow-hidden">
                <div
                    x-data="{
                        isUploadingAvatar: false,
                        avatarStatus: '',
                        isUploadingSlider: false,
                        sliderProgress: 0,
                        sliderStatus: '',
                        localAvatarPreview: null,
                        localSliderPreviews: [],

                        async handleAvatarUpload(e) {
                            const file = e.target.files[0];
                            if (!file) return;
                            this.localAvatarPreview = URL.createObjectURL(file);
                            this.isUploadingAvatar = true;
                            this.avatarStatus = 'Đang xử lý...';
                            setTimeout(async () => {
                                try {
                                    this.avatarStatus = 'Đang nén ảnh...';
                                    const compressed = await window.compressImage(file, 1200, 1200, 0.6);
                                    this.avatarStatus = 'Đang tải lên cloud...';
                                    @this.upload('tempAvatar', compressed,
                                        (url) => {
                                            this.isUploadingAvatar = false;
                                            this.avatarStatus = 'Hoàn tất ✓';
                                            setTimeout(() => { this.avatarStatus = ''; this.localAvatarPreview = null; }, 2000);
                                        },
                                        () => {
                                            this.isUploadingAvatar = false;
                                            this.avatarStatus = '';
                                            this.localAvatarPreview = null;
                                            alert('Lỗi tải ảnh đại diện!');
                                        }
                                    );
                                } catch (err) {
                                    this.isUploadingAvatar = false;
                                    this.avatarStatus = '';
                                    this.localAvatarPreview = null;
                                }
                            }, 100);
                        },

                        async handleSliderUpload(e) {
                            const files = Array.from(e.target.files);
                            if (files.length === 0) return;
                            const newPreviews = files.map(f => URL.createObjectURL(f));
                            this.localSliderPreviews = [...this.localSliderPreviews, ...newPreviews];
                            this.isUploadingSlider = true;
                            this.sliderProgress = 0;
                            this.sliderStatus = 'Đang xử lý...';
                            setTimeout(async () => {
                                try {
                                    this.sliderStatus = 'Đang nén ' + files.length + ' ảnh...';
                                    const compressedFiles = [];
                                    for (let i = 0; i < files.length; i++) {
                                        const compressed = await window.compressImage(files[i], 1600, 1600, 0.7);
                                        compressedFiles.push(compressed);
                                        this.sliderProgress = Math.round(((i + 1) / files.length) * 30);
                                    }
                                    this.sliderStatus = 'Đang tải lên cloud...';
                                    @this.uploadMultiple('tempImages', compressedFiles,
                                        (uploadedNames) => {
                                            this.isUploadingSlider = false;
                                            this.sliderProgress = 100;
                                            this.sliderStatus = 'Hoàn tất ✓';
                                            setTimeout(() => {
                                                this.sliderStatus = '';
                                                this.sliderProgress = 0;
                                                this.localSliderPreviews = [];
                                            }, 500);
                                        },
                                        () => {
                                            this.isUploadingSlider = false;
                                            this.sliderStatus = '';
                                            this.sliderProgress = 0;
                                            this.localSliderPreviews = [];
                                            alert('Lỗi tải ảnh slider!');
                                        },
                                        (event) => {
                                            this.sliderProgress = 30 + Math.round((event.detail.progress / 100) * 70);
                                        }
                                    );
                                } catch (e) {
                                    this.isUploadingSlider = false;
                                    this.sliderStatus = '';
                                    this.sliderProgress = 0;
                                    this.localSliderPreviews = [];
                                }
                            }, 100);
                        }
                    }"
                    class="bg-white rounded-t-[2.5rem] md:rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col max-h-[85dvh] md:max-h-[90dvh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden">

                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                        <h2 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i class="fa-solid fa-car"></i></span>
                            {{ $selectedListingId ? 'Cập Nhật Tin Xe' : 'Đăng Tin Xe' }}
                        </h2>
                        <button wire:click="closeCreatePopup"
                            class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                            <i class="fa-solid fa-times fa-lg"></i>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                        <form class="grid grid-cols-1 md:grid-cols-12 gap-6">

                            {{-- Title --}}
                            <div class="md:col-span-9">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tiêu đề tin đăng <span class="text-red-500">*</span></label>
                                <input wire:model="title" type="text" placeholder="VD: Bán Toyota Vios 2020 số tự động..."
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nhu cầu <span class="text-red-500">*</span></label>
                                <select wire:model.live="type"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                    <option>Cần bán</option>
                                    <option>Cho thuê</option>
                                    <option>Cần mua</option>
                                </select>
                            </div>

                            {{-- Vehicle type --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Loại xe <span class="text-red-500">*</span></label>
                                <select wire:model.live="vehicle_type"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                    @foreach ($vehicleTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Contact type --}}
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

                            {{-- Phone --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Số điện thoại liên hệ</label>
                                <input wire:model="contact_phone" type="text" placeholder="090..."
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm font-bold text-green-700">
                            </div>

                            {{-- Code --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    Mã tin <span class="text-xs text-gray-500 font-normal">(tự sinh nếu trống)</span>
                                </label>
                                <input wire:model="code" type="text" placeholder="VD: OT1234"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm">
                            </div>

                            {{-- VIP tier --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Hạng VIP</label>
                                <select wire:model="vip_tier"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                    <option value="normal">Thường</option>
                                    <option value="vip1">VIP 1</option>
                                    <option value="vip2">VIP 2</option>
                                    <option value="vip3">VIP 3</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Trạng thái tin</label>
                                <select wire:model="status"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                    <option value="active">Đang hiển thị</option>
                                    <option value="pending">Chờ duyệt</option>
                                    <option value="expired">Hết hạn</option>
                                    <option value="sold">Đã bán</option>
                                </select>
                            </div>

                            {{-- Sold toggle --}}
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Trạng thái giao dịch</label>
                                <div class="flex items-center gap-4 h-11">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_sold" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-bold text-gray-700">Đã bán</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Location section --}}
                            @if ($type !== 'Cần mua')
                                <div class="md:col-span-12 mt-2">
                                    <p class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                        <i class="fa-solid fa-map-location-dot"></i> Thông tin vị trí
                                    </p>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Tỉnh/Thành</label>
                                    <select wire:model.live="province_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">Chọn tỉnh/thành</option>
                                        @foreach (\App\Livewire\RealEstateListing::PROVINCES as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Quận/Huyện</label>
                                    <select wire:model.live="district_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">-- Chọn --</option>
                                        @foreach ($districts as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Phường/Xã</label>
                                    <select wire:model.live="ward_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">-- Chọn --</option>
                                        @foreach ($wards as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Địa chỉ chính xác</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input wire:model="address" type="text" placeholder="Số nhà, tên đường..."
                                            class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                    </div>
                                </div>
                            @endif

                            {{-- Vehicle specs section --}}
                            <div class="md:col-span-12 mt-2">
                                <p class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-gears"></i> Thông số xe
                                </p>
                            </div>

                            {{-- Brand (datalist) --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Hãng xe</label>
                                <input wire:model="brand" type="text" list="vehicle-brand-options" placeholder="VD: Toyota"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                <datalist id="vehicle-brand-options">
                                    @foreach ($brandOptions as $b)
                                        <option value="{{ $b }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            {{-- Model name --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Dòng xe</label>
                                <input wire:model="model_name" type="text" placeholder="VD: Vios, SH..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            {{-- Year --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Năm sản xuất</label>
                                <input wire:model="year" type="number" min="1950" max="2100" placeholder="VD: 2020"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            {{-- Mileage --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Số km đã đi</label>
                                <input wire:model="mileage" type="number" min="0" placeholder="VD: 25000"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            {{-- Transmission --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Hộp số</label>
                                <select wire:model="transmission"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($transmissions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Fuel type --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nhiên liệu</label>
                                <select wire:model="fuel_type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($fuelTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Engine capacity --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Dung tích / Phân khối</label>
                                <input wire:model="engine_capacity" type="text" placeholder="VD: 1.5L, 150cc"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            {{-- Color --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Màu sắc</label>
                                <input wire:model="color" type="text" placeholder="VD: Trắng, Đen..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            {{-- Seats (car only) --}}
                            @if ($vehicle_type === 'car')
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Số chỗ ngồi</label>
                                    <input wire:model="seats" type="number" min="1" max="64" placeholder="VD: 5"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            @endif

                            {{-- Condition --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tình trạng</label>
                                <select wire:model="condition"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    @foreach ($conditions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Origin --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Xuất xứ</label>
                                <select wire:model="origin"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn --</option>
                                    @foreach ($origins as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Price + unit --}}
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
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="Triệu">Triệu</option>
                                        <option value="Tỷ">Tỷ</option>
                                        <option value="Thỏa thuận">Thỏa thuận</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-12">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả chi tiết</label>
                                <textarea wire:model="description"
                                    class="w-full border border-gray-300 rounded-lg p-3 h-40 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                    placeholder="Mô tả chi tiết về xe..."></textarea>
                            </div>

                            {{-- Images & video --}}
                            <div class="md:col-span-12 mt-4 space-y-4">
                                <p class="text-sm text-blue-600 font-bold uppercase border-b-2 border-blue-100 pb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-images"></i> Hình ảnh & Video
                                </p>

                                {{-- Avatar --}}
                                <div class="space-y-2 mb-4">
                                    <label class="block text-sm font-bold text-gray-700">Ảnh đại diện (Avatar)</label>
                                    <div class="flex gap-4 items-start">
                                        <div class="w-32 h-32 bg-gray-100 rounded-lg border border-gray-300 flex-shrink-0 relative overflow-hidden group">
                                            <div x-show="isUploadingAvatar" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-20" style="display: none;">
                                                <i class="fa-solid fa-cloud-arrow-up fa-2x text-blue-500 animate-bounce mb-2"></i>
                                                <span class="text-[10px] font-black uppercase tracking-tighter text-blue-600" x-text="avatarStatus"></span>
                                            </div>

                                            <template x-if="localAvatarPreview">
                                                <img :src="localAvatarPreview" class="w-full h-full object-cover">
                                            </template>

                                            <template x-if="!localAvatarPreview">
                                                <div class="w-full h-full">
                                                    @if ($tempAvatar)
                                                        @php
                                                            try {
                                                                $tempUrl = $tempAvatar->temporaryUrl();
                                                                $extension = strtolower($tempAvatar->getClientOriginalExtension());
                                                                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                                            } catch (\Exception $e) {
                                                                $tempUrl = null;
                                                                $isPreviewable = false;
                                                            }
                                                        @endphp
                                                        @if ($isPreviewable && $tempUrl)
                                                            <img src="{{ $tempUrl }}" class="w-full h-full object-cover">
                                                        @else
                                                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-800 text-[#00D1FF] p-2 text-center">
                                                                <i class="fa-solid fa-file-image fa-2x mb-1"></i>
                                                                <span class="text-[10px] font-black uppercase tracking-tighter">{{ $extension ?? 'ERR' }}</span>
                                                            </div>
                                                        @endif
                                                        <button type="button" wire:click="$set('tempAvatar', null)" @click="localAvatarPreview = null"
                                                            class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-100 transition-opacity z-30">
                                                            <i class="fa-solid fa-times"></i>
                                                        </button>
                                                    @elseif ($avatar)
                                                        <img src="{{ $avatar }}" class="w-full h-full object-cover">
                                                        <button type="button" wire:click="removeAvatar" @click="localAvatarPreview = null"
                                                            class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-100 transition-opacity z-30">
                                                            <i class="fa-solid fa-times"></i>
                                                        </button>
                                                    @else
                                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                                            <i class="fa-solid fa-image fa-2x mb-1"></i>
                                                            <span class="text-[10px]">Chưa có ảnh</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </template>
                                        </div>

                                        <div class="flex-1 relative group h-32">
                                            <input type="file" class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                :disabled="isUploadingAvatar" @change="handleAvatarUpload($event)">
                                            <div class="bg-gray-50 hover:bg-gray-100 text-gray-500 px-6 py-4 rounded-xl border border-gray-200 border-dashed flex flex-col items-center justify-center gap-2 font-bold transition-all w-full h-full group-hover:border-blue-300 group-hover:text-blue-500 overflow-hidden relative"
                                                :class="isUploadingAvatar && 'border-blue-400 bg-blue-50'">
                                                <div x-show="!isUploadingAvatar" class="flex flex-col items-center gap-2 relative z-10">
                                                    <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                                                    Tải ảnh đại diện
                                                    <span class="text-xs font-normal text-gray-400">Chọn 1 ảnh làm ảnh bìa</span>
                                                </div>
                                                <div x-show="isUploadingAvatar" class="flex flex-col items-center gap-2 text-blue-600 relative z-10" style="display: none;">
                                                    <i class="fa-solid fa-circle-notch fa-spin fa-lg"></i>
                                                    <span class="text-xs font-black uppercase tracking-tighter" x-text="avatarStatus"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Slider images --}}
                                <div class="space-y-4">
                                    <label class="block text-sm font-bold text-gray-700">Ảnh Slider (Chi tiết)</label>
                                    <div class="flex gap-4">
                                        <button type="button" wire:click="$set('showMediaPopup', true)"
                                            class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 px-6 py-4 rounded-xl border border-blue-200 border-dashed flex items-center justify-center gap-2 font-bold transition-all">
                                            <i class="fa-solid fa-folder-open"></i>
                                            Chọn từ Thư viện (Media)
                                        </button>

                                        <div class="flex-1 relative group">
                                            <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                :disabled="isUploadingSlider" @change="handleSliderUpload($event)">
                                            <div class="bg-gray-50 hover:bg-gray-100 text-gray-500 px-6 py-4 rounded-xl border border-gray-200 border-dashed flex items-center justify-center gap-2 font-bold transition-all w-full h-full group-hover:border-blue-300 group-hover:text-blue-500 overflow-hidden relative min-h-[64px]"
                                                :class="isUploadingSlider && 'border-blue-400 bg-blue-50'">
                                                <div x-show="!isUploadingSlider" class="flex items-center gap-2 relative z-10">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                                    Tải ảnh slider từ máy tính
                                                </div>
                                                <div x-show="isUploadingSlider" class="flex items-center gap-3 text-blue-600 relative z-10" style="display: none;">
                                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                                    <span class="text-xs font-black uppercase tracking-widest"><span x-text="sliderStatus"></span> <span x-text="sliderProgress"></span>%</span>
                                                    <div class="absolute bottom-0 left-0 h-1 bg-blue-500 transition-all duration-300" :style="'width: ' + sliderProgress + '%'"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Previews --}}
                                    @if (count($images) + count($tempImages) > 0)
                                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                            @foreach ($images as $index => $img)
                                                <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 group">
                                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-black/40 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                                        <button type="button" wire:click="setAvatarFromImage({{ $index }})"
                                                            class="bg-white text-blue-600 text-[10px] font-bold px-2 py-1 rounded shadow-sm hover:bg-blue-50" title="Đặt làm ảnh đại diện">
                                                            <i class="fa-solid fa-star"></i> Avatar
                                                        </button>
                                                        <button type="button" wire:click="removeImage({{ $index }})"
                                                            class="bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center hover:bg-red-600">
                                                            <i class="fa-solid fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <template x-for="(localUrl, idx) in localSliderPreviews" :key="'local-'+idx">
                                                <div class="relative aspect-square rounded-lg overflow-hidden border border-blue-400 ring-2 ring-blue-500/20 group">
                                                    <img :src="localUrl" class="absolute inset-0 w-full h-full object-cover opacity-50">
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <i class="fa-solid fa-circle-notch fa-spin text-blue-500"></i>
                                                    </div>
                                                </div>
                                            </template>

                                            @foreach ($tempImages as $index => $file)
                                                @php
                                                    try {
                                                        $tempUrl = $file->temporaryUrl();
                                                        $extension = strtolower($file->getClientOriginalExtension());
                                                        $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                                    } catch (\Exception $e) {
                                                        $tempUrl = null;
                                                        $isPreviewable = false;
                                                    }
                                                @endphp
                                                <div class="relative aspect-square rounded-lg overflow-hidden border border-blue-200 ring-2 ring-blue-500 group">
                                                    @if ($isPreviewable && $tempUrl)
                                                        <img src="{{ $tempUrl }}" class="absolute inset-0 w-full h-full object-cover">
                                                    @else
                                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-800 text-[#00D1FF] p-2 text-center">
                                                            <i class="fa-solid fa-file-image fa-xl mb-1"></i>
                                                            <span class="text-[10px] font-black uppercase tracking-tighter">{{ $extension ?? 'ERR' }}</span>
                                                        </div>
                                                    @endif
                                                    <button type="button" wire:click="removeTempImage({{ $index }})"
                                                        class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        <i class="fa-solid fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Youtube --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1"><i class="fa-brands fa-youtube text-red-500 mr-1"></i>Link Youtube</label>
                                    <input wire:model="youtube_link" type="url" placeholder="https://youtube.com/watch?v=..."
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                    @error('youtube_link') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="p-6 pb-12 md:pb-6 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-2xl">
                        <button wire:click="closeCreatePopup"
                            class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-200 font-bold transition-colors">Hủy bỏ</button>
                        <button type="button" wire:click="saveListing" wire:loading.attr="disabled" wire:target="saveListing"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg hover:shadow-blue-500/30 transform active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-paper-plane" wire:loading.remove wire:target="saveListing"></i>
                            <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="saveListing"></i>
                            {{ $selectedListingId ? 'Lưu Thay Đổi' : 'Đăng Tin Xe' }}
                        </button>
                    </div>

                </div>
            </div>
        @endif

        {{-- Media Library Popup --}}
        @if ($showMediaPopup)
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] flex items-end md:items-center justify-center p-0 md:p-4 transition-all duration-300 overflow-hidden">
                <div class="bg-white w-full max-w-6xl rounded-t-[2.5rem] md:rounded-2xl shadow-2xl overflow-hidden flex flex-col relative animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] max-h-[85dvh] md:max-h-[90dvh]">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                        <h3 class="text-xl font-black text-gray-800 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i class="fa-solid fa-images"></i></span>
                            Chọn ảnh từ Thư viện
                        </h3>
                        <button wire:click="$set('showMediaPopup', false)"
                            class="text-gray-400 hover:text-red-500 w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition-colors">
                            <i class="fa-solid fa-times fa-lg"></i>
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        @livewire('file-manager', ['isModeSelect' => true])
                    </div>
                </div>
            </div>
        @endif
    </div>

    @once
        <script>
            // Image compression utility (shared mechanism with Real Estate module).
            window.compressImage = window.compressImage || async function(file, maxWidth = 1600, maxHeight = 1600, quality = 0.7) {
                return new Promise((resolve) => {
                    if (!file.type.startsWith('image/')) {
                        return resolve(file);
                    }
                    const img = new Image();
                    const objectUrl = URL.createObjectURL(file);
                    img.onerror = () => { URL.revokeObjectURL(objectUrl); resolve(file); };
                    img.onload = () => {
                        URL.revokeObjectURL(objectUrl);
                        try {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            if (width > height) {
                                if (width > maxWidth) { height *= maxWidth / width; width = maxWidth; }
                            } else {
                                if (height > maxHeight) { width *= maxHeight / height; height = maxHeight; }
                            }
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.imageSmoothingEnabled = true;
                            ctx.imageSmoothingQuality = 'high';
                            ctx.drawImage(img, 0, 0, width, height);
                            canvas.toBlob((blob) => {
                                if (!blob) return resolve(file);
                                resolve(new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                }));
                            }, 'image/jpeg', quality);
                        } catch (e) {
                            resolve(file);
                        }
                    };
                    img.src = objectUrl;
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
