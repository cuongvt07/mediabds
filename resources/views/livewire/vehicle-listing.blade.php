<div class="py-4">
    {{-- Thanh điều hướng giữa các module tin đăng --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('listings') }}" wire:navigate
                class="px-3 py-2 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-house mr-1"></i> Tin bất động sản
            </a>
            <span class="px-3 py-2 rounded-xl text-sm font-bold text-blue-600 bg-blue-50">
                <i class="fa-solid fa-car mr-1"></i> Tin xe cộ
            </span>
        </div>
        <button wire:click="createVehicle"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow hover:bg-blue-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Đăng tin xe
        </button>
    </div>

    {{-- Bộ lọc --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Tìm tiêu đề, hãng, dòng, mã tin..."
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-gray-200 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none">
            </div>
            <select wire:model.live="filterVehicleType"
                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:border-blue-400 outline-none">
                <option value="">Tất cả loại xe</option>
                @foreach ($vehicleTypes as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus"
                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:border-blue-400 outline-none">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Đang hiển thị</option>
                <option value="pending">Chờ duyệt</option>
                <option value="expired">Hết hạn</option>
                <option value="sold">Đã bán</option>
            </select>
        </div>
    </div>

    {{-- Bảng tin --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold">Tin xe</th>
                        <th class="px-4 py-3 text-left font-bold">Loại</th>
                        <th class="px-4 py-3 text-left font-bold">Thông số</th>
                        <th class="px-4 py-3 text-right font-bold">Giá</th>
                        <th class="px-4 py-3 text-center font-bold">Trạng thái</th>
                        <th class="px-4 py-3 text-right font-bold">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($vehicles as $v)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-11 rounded-lg bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if ($v->avatar)
                                            <img src="{{ $v->avatar }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <i class="fa-solid fa-car text-gray-300"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate max-w-[260px]">{{ $v->title }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono">{{ $v->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $vehicleTypes[$v->vehicle_type] ?? $v->vehicle_type }}
                                <div class="text-[11px] text-gray-400">{{ $v->brand }} {{ $v->model_name }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">
                                @if ($v->year) <span class="mr-2">{{ $v->year }}</span> @endif
                                @if ($v->mileage !== null) <span class="mr-2">{{ number_format($v->mileage) }} km</span> @endif
                                @if ($v->transmission) <span>{{ $transmissions[$v->transmission] ?? $v->transmission }}</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">
                                @if ($v->price !== null)
                                    {{ rtrim(rtrim(number_format((float) $v->price, 2), '0'), '.') }} {{ $v->price_unit }}
                                @else
                                    <span class="text-gray-400 font-normal">Thỏa thuận</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleSold({{ $v->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-bold
                                    {{ $v->is_sold ? 'bg-gray-200 text-gray-600' : 'bg-green-100 text-green-700' }}">
                                    {{ $v->is_sold ? 'Đã bán' : 'Đang bán' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button wire:click="editVehicle({{ $v->id }})"
                                    class="px-2 py-1 rounded-lg text-blue-600 hover:bg-blue-50" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click="deleteVehicle({{ $v->id }})"
                                    wire:confirm="Xóa tin xe này?"
                                    class="px-2 py-1 rounded-lg text-red-500 hover:bg-red-50" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-car-on text-3xl mb-2 block"></i>
                                Chưa có tin xe nào. Bấm "Đăng tin xe" để thêm mới.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-50">
            {{ $vehicles->links() }}
        </div>
    </div>

    {{-- Modal form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[200] flex items-start justify-center bg-black/40 p-4 overflow-y-auto"
            wire:key="vehicle-modal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl my-8" @click.outside="$wire.closeModal()">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ $editingId ? 'Sửa tin xe' : 'Đăng tin xe mới' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-700">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit="saveVehicle" class="p-5 space-y-5 max-h-[75vh] overflow-y-auto">
                    {{-- Cơ bản --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tiêu đề tin <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="VD: Toyota Vios 2020 số tự động bản G"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:border-blue-400 outline-none">
                        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Loại xe <span class="text-red-500">*</span></label>
                            <select wire:model.live="vehicleType" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                @foreach ($vehicleTypes as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Hình thức</label>
                            <select wire:model="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="Cần bán">Cần bán</option>
                                <option value="Cho thuê">Cho thuê</option>
                                <option value="Cần mua">Cần mua</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Hãng</label>
                            <input type="text" list="brandOptions" wire:model="brand" placeholder="Toyota..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                            <datalist id="brandOptions">
                                @foreach ($brandOptions as $b)
                                    <option value="{{ $b }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Dòng xe</label>
                            <input type="text" wire:model="modelName" placeholder="Vios, SH..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                    </div>

                    {{-- Thông số xe --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Năm SX</label>
                            <input type="number" wire:model="year" placeholder="2020"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                            @error('year') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Số km đã đi</label>
                            <input type="number" wire:model="mileage" placeholder="35000"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Hộp số</label>
                            <select wire:model="transmission" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="">--</option>
                                @foreach ($transmissions as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Nhiên liệu</label>
                            <select wire:model="fuelType" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="">--</option>
                                @foreach ($fuelTypes as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Dung tích / Phân khối</label>
                            <input type="text" wire:model="engineCapacity" placeholder="1.5L / 150cc"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Màu sắc</label>
                            <input type="text" wire:model="color" placeholder="Trắng..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        @if ($vehicleType === 'car')
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Số chỗ</label>
                                <input type="number" wire:model="seats" placeholder="5"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tình trạng</label>
                            <select wire:model="condition" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                @foreach ($conditions as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Xuất xứ</label>
                            <select wire:model="origin" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="">--</option>
                                @foreach ($origins as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Giá + vị trí --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Giá</label>
                            <input type="number" step="0.01" wire:model="price" placeholder="450"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Đơn vị</label>
                            <select wire:model="priceUnit" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="Triệu">Triệu</option>
                                <option value="Tỷ">Tỷ</option>
                                <option value="Thỏa thuận">Thỏa thuận</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tỉnh/Thành</label>
                            <input type="text" wire:model="provinceName" placeholder="TP. Hồ Chí Minh"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Quận/Huyện</label>
                            <input type="text" wire:model="districtName" placeholder="Quận 1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Địa chỉ</label>
                        <input type="text" wire:model="address" placeholder="Số nhà, đường..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                    </div>

                    {{-- Liên hệ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Người liên hệ</label>
                            <input type="text" wire:model="contactName"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">SĐT</label>
                            <input type="text" wire:model="contactPhone"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Zalo</label>
                            <input type="text" wire:model="contactZalo"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                    </div>

                    {{-- Mô tả --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Mô tả chi tiết</label>
                        <textarea wire:model="description" rows="4" placeholder="Tình trạng xe, lịch sử bảo dưỡng, lý do bán..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none"></textarea>
                    </div>

                    {{-- Ảnh + video --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Ảnh (mỗi dòng 1 URL — ảnh đầu là ảnh đại diện)</label>
                            <textarea wire:model="imagesText" rows="3" placeholder="https://.../anh1.jpg&#10;https://.../anh2.jpg"
                                class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-gray-200 outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Link video (YouTube)</label>
                            <input type="text" wire:model="youtubeLink"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                        </div>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Trạng thái</label>
                            <select wire:model="statusValue" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="active">Đang hiển thị</option>
                                <option value="pending">Chờ duyệt</option>
                                <option value="expired">Hết hạn</option>
                                <option value="sold">Đã bán</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Gói VIP</label>
                            <select wire:model="vipTier" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 outline-none">
                                <option value="normal">Thường</option>
                                <option value="vip1">VIP 1</option>
                                <option value="vip2">VIP 2</option>
                                <option value="vip3">VIP 3</option>
                            </select>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 pb-2">
                            <input type="checkbox" wire:model="isSold" class="rounded"> Đã bán
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100">Hủy</button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
