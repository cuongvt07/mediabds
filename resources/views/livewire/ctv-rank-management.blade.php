<div class="h-full flex flex-col p-6 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-[1600px] w-full mx-auto relative group flex-1 pb-24">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 flex-shrink-0 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <span>Hạng CTV</span>
            </h1>
            <p class="text-slate-400 mt-2 font-bold text-xs uppercase tracking-widest hidden md:block">Quản lý cấp độ & mức hiển thị giá</p>
        </div>

        <button wire:click="create()"
            class="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-500/20 active:scale-95">
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
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="relative flex-1 md:max-w-md">
            <input wire:model.live="search" type="text" placeholder="Tìm kiếm tên hạng..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 placeholder-slate-400 shadow-sm">
            <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex-1 flex flex-col">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-50 bg-slate-50/50">
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tên hạng</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">CTV mời tối thiểu</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Khoảng giá hiển thị</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 uppercase text-[12px] font-bold">
                    @forelse($ranks as $rank)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="py-5 px-6">
                                <span class="text-slate-800 tracking-tight">{{ $rank->name }}</span>
                            </td>
                            <td class="py-5 px-6 text-center">
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-blue-50 text-blue-600 text-[10px] font-black">
                                    <i class="fa-solid fa-users"></i>
                                    {{ $rank->min_invites }}
                                </span>
                            </td>
                            <td class="py-5 px-6 text-slate-600 font-mono tracking-tighter">
                                {{ number_format($rank->min_price, 0, ',', '.') }}
                                {{ $rank->max_price ? ' - ' . number_format($rank->max_price, 0, ',', '.') : ' trở lên' }} <span class="text-[9px] text-slate-400">tỷ</span>
                            </td>
                            <td class="py-5 px-6 text-right">
                                <div class="flex justify-end gap-2 px-4 invisible group-hover:visible translate-x-4 group-hover:translate-x-0 transition-all duration-300">
                                    <button wire:click="edit({{ $rank->id }})" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button wire:click="delete({{ $rank->id }})" wire:confirm="Xóa hạng này?" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-20 text-center italic text-slate-300">
                                <i class="fa-solid fa-inbox text-5xl mb-4 block opacity-10"></i>
                                <span class="uppercase tracking-widest text-[10px] font-black">Trống dữ liệu</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($ranks as $rank)
                <div class="p-5 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-black text-slate-800 uppercase tracking-tight text-lg">{{ $rank->name }}</h3>
                        <div class="flex gap-2">
                            <button wire:click="edit({{ $rank->id }})" class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-600 rounded-xl active:bg-blue-600 active:text-white transition-all">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button wire:click="delete({{ $rank->id }})" wire:confirm="Xóa hạng này?" class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-600 rounded-xl active:bg-red-600 active:text-white transition-all">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-[10px] font-black uppercase tracking-widest">
                        <div class="bg-blue-50 text-blue-600 p-3 rounded-2xl">
                            <p class="text-[8px] opacity-70 mb-1">Mời tối thiểu</p>
                            <p class="text-sm">{{ $rank->min_invites }} CTV</p>
                        </div>
                        <div class="bg-slate-50 text-slate-600 p-3 rounded-2xl">
                            <p class="text-[8px] opacity-70 mb-1">Giá hiển thị</p>
                            <p class="text-xs font-mono tracking-tighter lowercase">{{ number_format($rank->min_price, 0, ',', '.') }}{{ $rank->max_price ? '-' . number_format($rank->max_price, 0, ',', '.') : '+' }}<span class="text-[8px] ml-0.5 uppercase tracking-normal">tỷ</span></p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-16 text-center italic text-slate-300">
                    <i class="fa-solid fa-ranking-star text-4xl mb-4 block opacity-10"></i>
                    <p class="uppercase tracking-widest text-[9px] font-black">Chưa có cấu hình</p>
                </div>
            @endforelse
        </div>

        <div class="mt-auto border-t border-gray-100 bg-slate-50/50 px-6 py-4">
            {{ $ranks->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isModalOpen)
    <div class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

        <div class="relative bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-[slideUp_0.3s_ease-out]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-white md:bg-gray-50 rounded-t-3xl">
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid {{ $rankId ? 'fa-pen-to-square' : 'fa-plus' }}"></i>
                    </div>
                    <span>{{ $rankId ? 'Sửa hạng' : 'Hạng mới' }}</span>
                </h3>
                <button wire:click="closeModal" class="text-slate-300 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-xl md:bg-transparent">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-8">
                <form wire:submit.prevent="store" class="space-y-6">
                    <!-- Tên hạng -->
                    <div>
                        <label for="name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tên hạng <span class="text-red-500">*</span></label>
                        <input type="text" id="name" wire:model="name"
                            class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 placeholder-slate-400"
                            placeholder="Ví dụ: Vàng, Bạc, Đồng">
                        @error('name') <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Số lượng mời -->
                    <div>
                        <label for="min_invites" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mời tối thiểu (CTV) <span class="text-red-500">*</span></label>
                        <input type="number" id="min_invites" wire:model="min_invites" min="0"
                            class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700"
                            placeholder="0">
                        @error('min_invites') <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Mức giá hiển thị -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="min_price" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Giá tối thiểu (Tỷ) <span class="text-red-500">*</span></label>
                            <input type="text" id="min_price" wire:model="min_price"
                                x-data="{
                                    formatInput(el) {
                                        let val = el.value.replace(/[^0-9]/g, '');
                                        if(val) {
                                            val = parseInt(val, 10).toLocaleString('vi-VN').replace(/,/g, '.');
                                        }
                                        el.value = val;
                                        $wire.set('min_price', val);
                                    }
                                }"
                                x-on:input="formatInput($el)"
                                class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 font-mono"
                                placeholder="0">
                            @error('min_price') <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="max_price" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Giá tối đa (Tỷ)</label>
                            <input type="text" id="max_price" wire:model="max_price"
                                x-data="{
                                    formatInput(el) {
                                        let val = el.value.replace(/[^0-9]/g, '');
                                        if(val) {
                                            val = parseInt(val, 10).toLocaleString('vi-VN').replace(/,/g, '.');
                                        }
                                        el.value = val;
                                        $wire.set('max_price', val);
                                    }
                                }"
                                x-on:input="formatInput($el)"
                                class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 font-mono"
                                placeholder="+∞">
                            @error('max_price') <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="order-2 md:order-1 px-8 py-3 text-slate-400 hover:text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-colors">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="order-1 md:order-2 px-8 py-3 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-500/20 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            Lưu cấu hình
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
