<div
    class="h-full flex flex-col p-6 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-[1600px] w-full mx-auto relative group flex-1 pb-24">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 flex-shrink-0">
        <div class="text-center sm:text-left">
            <h1
                class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight flex items-center justify-center sm:justify-start gap-3">
                <div class="p-2 sm:p-2.5 bg-blue-100 rounded-xl">
                    <i class="fa-solid fa-ranking-star text-blue-600 text-lg sm:text-xl"></i>
                </div>
                Cấu hình Hạng CTV
            </h1>
            <p class="text-slate-500 mt-2 font-medium text-sm sm:text-base">Quản lý các cấp độ Cộng Tác Viên và mức hiển
                thị giá</p>
        </div>

        <button wire:click="create()"
            class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-semibold shadow-sm hover:shadow-md active:scale-95">
            <i class="fa-solid fa-plus"></i>
            Thêm hạng mới
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100/50 flex items-start gap-3 backdrop-blur-sm"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600 shrink-0">
                <i class="fa-solid fa-check text-sm leading-none"></i>
            </div>
            <div>
                <h4 class="text-emerald-800 font-semibold mb-0.5">Thành công</h4>
                <p class="text-emerald-600 text-sm">{{ session('message') }}</p>
            </div>
            <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Controls Row -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-search"></i>
            </div>
            <input wire:model.live="search" type="text" placeholder="Tìm kiếm tên hạng..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium placeholder-slate-400 shadow-sm">
        </div>
    </div>

    <!-- Pagination (Moved to top) -->
    <div class="mb-6 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="hidden md:block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">
            Tổng số {{ $ranks->total() }} hạng mục
        </div>
        <div class="w-full md:w-auto flex justify-center">
            {{ $ranks->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    <!-- Data Table -->
    <div
        class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex-1 flex flex-col relative before:absolute before:inset-x-0 before:top-0 before:h-px before:bg-gradient-to-r before:from-transparent before:via-slate-200 before:to-transparent">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] sm:min-w-0">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tên hạng
                        </th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">
                            Mời tối thiểu</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mức giá hiển
                            thị</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">
                            Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ranks as $rank)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="py-4 px-5">
                                <div class="font-semibold text-slate-900">{{ $rank->name }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-medium border border-blue-100/50">
                                    <i class="fa-solid fa-users text-xs"></i>
                                    {{ $rank->min_invites }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-700">
                                {{ number_format($rank->min_price, 0, ',', '.') }}
                                {{ $rank->max_price ? ' - ' . number_format($rank->max_price, 0, ',', '.') : ' trở lên' }}
                                Tỷ VNĐ
                            </td>
                            <td class="py-4 px-5 text-right space-x-2">
                                <button wire:click="edit({{ $rank->id }})"
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block"
                                    title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="delete({{ $rank->id }})"
                                    wire:confirm="Bạn có chắc chắn muốn xóa hạng này?"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors inline-block"
                                    title="Xóa">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="inline-flex flex-col items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-4xl mb-4 text-slate-300"></i>
                                    <p class="font-medium text-slate-500">Chưa có hạng CTV nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if ($isModalOpen)
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="closeModal">
            </div>

            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg transform border border-slate-200 my-8 sm:my-0 animate-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid {{ $rankId ? 'fa-pen-to-square' : 'fa-plus' }} text-blue-600"></i>
                        {{ $rankId ? 'Sửa Hạng CTV' : 'Thêm Hạng Mới' }}
                    </h3>
                    <button wire:click="closeModal"
                        class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-5">
                    <form wire:submit.prevent="store" class="space-y-4">
                        <!-- Tên hạng -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Tên hạng
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" wire:model="name"
                                class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium placeholder-slate-400 shadow-sm"
                                placeholder="Ví dụ: Vàng, Bạc, Đồng">
                            @error('name')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Số lượng mời -->
                        <div>
                            <label for="min_invites" class="block text-sm font-semibold text-slate-700 mb-1.5">Số lượng
                                CTV mời tối thiểu <span class="text-red-500">*</span></label>
                            <input type="number" id="min_invites" wire:model="min_invites" min="0"
                                class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium placeholder-slate-400 shadow-sm"
                                placeholder="0">
                            @error('min_invites')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-slate-500 mt-1">Hạng CTV sẽ được tự động tính dựa trên số người mà
                                CTV này đã mời.</p>
                        </div>

                        <!-- Mức giá hiển thị -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="min_price" class="block text-sm font-semibold text-slate-700 mb-1.5">Giá tối
                                    thiểu (Tỷ VNĐ) <span class="text-red-500">*</span></label>
                                <input type="text" id="min_price" wire:model="min_price" x-data="{
                                    formatInput(el) {
                                        let val = el.value.replace(/[^0-9]/g, '');
                                        if (val) {
                                            val = parseInt(val, 10).toLocaleString('vi-VN').replace(/,/g, '.');
                                        }
                                        el.value = val;
                                        $wire.set('min_price', val);
                                    }
                                }"
                                    x-on:input="formatInput($el)"
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium shadow-sm"
                                    placeholder="0">
                                @error('min_price')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="max_price" class="block text-sm font-semibold text-slate-700 mb-1.5">Giá
                                    tối đa (Tỷ VNĐ)</label>
                                <input type="text" id="max_price" wire:model="max_price" x-data="{
                                    formatInput(el) {
                                        let val = el.value.replace(/[^0-9]/g, '');
                                        if (val) {
                                            val = parseInt(val, 10).toLocaleString('vi-VN').replace(/,/g, '.');
                                        }
                                        el.value = val;
                                        $wire.set('max_price', val);
                                    }
                                }"
                                    x-on:input="formatInput($el)"
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium placeholder-slate-400 shadow-sm"
                                    placeholder="Để trống nếu không giới hạn">
                                @error('max_price')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="mt-6 pt-5 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" wire:click="closeModal"
                                class="px-5 py-2.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold transition-colors">
                                Hủy
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold shadow-sm flex items-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                Lưu dữ liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
