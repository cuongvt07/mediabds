<div class="h-full flex flex-col bg-slate-50 relative">
    <!-- Header -->
    <div class="bg-white border-b border-gray-100 px-6 py-5 flex flex-col md:flex-row md:items-center justify-between shrink-0 gap-4">
        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight uppercase">Quản Lý Tài Khoản</h1>
        <button wire:click="openCreatePopup"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20 active:scale-95 transition-all">
            <i class="fa-solid fa-plus font-bold"></i> Thêm Tài Khoản
        </button>
    </div>


    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-100 px-6 py-4 flex items-center gap-4 shrink-0">
        <div class="relative w-full md:max-w-md">
            <input type="text" placeholder="Tìm kiếm theo tên hoặc SĐT..." wire:model.live.debounce.300ms="search"
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 placeholder-slate-400">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>
    </div>


    <!-- Content -->
<<<<<<< HEAD
    <div class="flex-1 overflow-auto p-4 sm:p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Họ và Tên</th>
                        <th class="px-6 py-4">Số điện thoại</th>
                        <th class="px-6 py-4">Mã code</th>
                        <th class="px-6 py-4">Được mời bởi</th>
                        <th class="px-6 py-4">Lượt dùng mã</th>
                        <th class="px-6 py-4">Ngày tạo</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-blue-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600">{{ $user->phone }}</td>
                            <td class="px-6 py-4 font-mono text-slate-700">{{ $user->invite_code ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if ($user->inviter)
                                    <div>{{ $user->inviter->name }}</div>
                                    <div class="font-mono text-xs text-gray-400">{{ $user->inviter->invite_code }}</div>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->sent_invite_logs_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editUser({{ $user->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-100 transition-colors"
                                        title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $user->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-red-600 hover:bg-red-100 transition-colors"
                                        title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
=======
    <div class="flex-1 overflow-auto p-4 md:p-6 custom-scrollbar">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        <tr>
                            <th class="px-6 py-5">Thành viên</th>
                            <th class="px-6 py-5">Số điện thoại</th>
                            <th class="px-6 py-5">Mã code</th>
                            <th class="px-6 py-5">Người mời</th>
                            <th class="px-6 py-5 text-center">Lượt mời</th>
                            <th class="px-6 py-5">Ngày tham gia</th>
                            <th class="px-6 py-5 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-[12px] font-bold uppercase tracking-tight">
                        @forelse ($users as $user)
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-inner ring-4 ring-slate-50/50">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-slate-800">{{ $user->name }}</p>
                                            <p class="text-[9px] text-slate-400 font-mono tracking-widest">{{ $user->invite_code ?? 'NO-CODE' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-500 lowercase">{{ $user->phone }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black font-mono">
                                        {{ $user->invite_code ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->inviter)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-[8px]">
                                                <i class="fa-solid fa-link"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-slate-700 truncate max-w-[120px]">{{ $user->inviter->name }}</p>
                                                <p class="text-[8px] text-slate-400 font-mono">{{ $user->inviter->invite_code }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic font-normal text-[10px]">Tài khoản gốc</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black">
                                        {{ $user->sent_invite_logs_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-400 font-mono lowercase text-[10px]">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 px-4 invisible group-hover:visible translate-x-4 group-hover:translate-x-0 transition-all duration-300">
                                        <button wire:click="editUser({{ $user->id }})"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50/50 transition-all group/btn"
                                            title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square group-hover/btn:scale-110"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $user->id }})"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50/50 transition-all group/btn"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash-can group-hover/btn:scale-110"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center text-slate-300 italic">
                                    <i class="fa-solid fa-users-viewfinder text-5xl mb-4 block opacity-10"></i>
                                    <span class="uppercase tracking-widest text-[10px] font-black">Không có dữ liệu</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden divide-y divide-gray-50 uppercase font-black tracking-tight">
                @forelse ($users as $user)
                    <div class="p-5 flex flex-col gap-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-blue-500/20">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-slate-800 text-base leading-tight">{{ $user->name }}</p>
                                    <p class="text-[10px] font-mono text-slate-400 mt-1 lowercase">{{ $user->phone }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="editUser({{ $user->id }})"
                                    class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-600 rounded-xl active:bg-blue-600 active:text-white transition-all">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-600 rounded-xl active:bg-red-600 active:text-white transition-all">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-blue-50/50 p-3 rounded-2xl">
                                <span class="text-[8px] text-slate-400 block mb-1">Mã định danh</span>
                                <span class="text-blue-600 font-mono text-[10px]">{{ $user->invite_code ?? '-' }}</span>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-2xl">
                                <span class="text-[8px] text-slate-400 block mb-1">Lượt mời</span>
                                <span class="text-slate-700 text-[10px]">{{ $user->sent_invite_logs_count }} thành viên</span>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-2xl col-span-2 flex items-center justify-between">
                                <div class="min-w-0">
                                    <span class="text-[8px] text-slate-400 block mb-1">Người giới thiệu</span>
                                    <span class="text-[10px] text-slate-700 truncate block">
                                        {{ $user->inviter->name ?? 'Tài khoản gốc' }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[8px] text-slate-400 block mb-1">Tham gia</span>
                                    <span class="text-[10px] font-mono text-slate-500 lowercase">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center italic text-slate-300">
                        <i class="fa-solid fa-user-slash text-5xl mb-4 block opacity-10"></i>
                        <span class="uppercase tracking-widest text-[9px] font-black">Chưa có thành viên</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4 px-2">
            {{ $users->links() }}
        </div>
    </div>


    <!-- Create/Edit Modal -->
    @if ($showCreatePopup)
<<<<<<< HEAD
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            x-transition.opacity>
            <div
                class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-3rem)] animate-[scaleIn_0.2s_ease-out]">
                <div
                    class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                    <h2 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i
                                class="fa-solid fa-user-gear"></i></span>
                        {{ $selectedUserId ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản Mới' }}
=======
        <div class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeCreatePopup"></div>

            <div class="relative bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[90vh] md:max-h-[min(800px,90vh)] overflow-hidden animate-[slideUp_0.3s_ease-out]">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-white md:bg-gray-50 rounded-t-3xl shrink-0">
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <span>{{ $selectedUserId ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản' }}</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                    </h2>
                    <button wire:click="closeCreatePopup"
                        class="text-slate-300 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-xl md:bg-transparent">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 md:p-8 space-y-8 overflow-y-auto custom-scrollbar flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Họ và Tên -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Họ và Tên <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text"
                                class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 placeholder-slate-400"
                                placeholder="Nhập tên đầy đủ">
                            @error('name')
                                <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Số điện thoại -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Số điện thoại <span class="text-red-500">*</span></label>
                            <input wire:model="phone" type="text"
                                class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-bold text-slate-700 placeholder-slate-400"
                                placeholder="Ví dụ: 0912345678">
                            @error('phone')
                                <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

<<<<<<< HEAD
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Số điện thoại <span
                                class="text-red-500">*</span></label>
                        <input wire:model="phone" type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Nhập số điện thoại">
                        @error('phone')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    @php
                        $selectedInviter = collect($inviters ?? [])->firstWhere('id', (int) $inviterUserId);
                    @endphp
                    <div x-data x-init="$nextTick(() => {
                        const boot = () => {
                            if (window.initInviterSelect2) {
                                window.initInviterSelect2($refs.inviterSelect, @js($inviterUserId));
                                return;
                            }
                            setTimeout(boot, 50);
                        };
                        boot();
                    })">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Người mời (nếu có)</label>
                        <div wire:ignore>
=======
                    <!-- Người mời -->
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Người mời giới thiệu</label>
                        <div wire:ignore x-data x-init="window.initInviterSelect2($refs.inviterSelect, @js($inviterUserId))">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            <select x-ref="inviterSelect" data-livewire-id="{{ $this->getId() }}"
                                class="inviter-select2 w-full">
                                <option value="">Tài khoản gốc (không có người mời)</option>
                                @foreach ($inviters as $inviter)
                                    <option value="{{ $inviter->id }}" @selected((string) $inviterUserId === (string) $inviter->id)>
                                        {{ $inviter->name }} - {{ $inviter->phone }} ({{ $inviter->invite_code ?? 'Chưa mã' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('inviterUserId')
                            <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span>
                        @enderror
<<<<<<< HEAD

                        @if ($selectedInviter)
                            <p class="text-xs text-gray-500 mt-1">Mã người mời: <span
                                    class="font-mono font-bold">{{ $selectedInviter->invite_code ?: 'Chưa có mã' }}</span>
                            </p>
                        @endif
=======
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                    </div>

                    <!-- Mã định danh -->
                    <div class="space-y-2 p-5 bg-slate-50 rounded-3xl border border-gray-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mã định danh (Invite Code)</label>
                        
                        @if ($selectedUserId && !blank($existingInviteCode))
                             <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-mono font-black text-slate-800 text-lg tracking-widest uppercase">
                                        {{ $existingInviteCode }}
                                    </p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Mã cố định không thể thay đổi</p>
                                </div>
                            </div>
                        @elseif ($inviterUserId && $selectedInviter)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-mono font-black text-blue-600 text-lg tracking-widest uppercase italic bg-white px-3 py-1 rounded-lg border border-blue-100 inline-block shadow-sm">
                                        {{ $selectedInviter->invite_code }}{{ $selectedUserId ? $selectedUserId : 'X' }}
                                    </p>
                                    <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest mt-1">Tự động tạo từ mã người mời</p>
                                </div>
                            </div>
                        @else
                            <input wire:model="rootInviteCode" type="text"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-mono font-black tracking-widest text-slate-700 uppercase"
                                placeholder="Ví dụ: NDT">
                            @error('rootInviteCode')
                                <span class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight block">{{ $message }}</span>
                            @enderror
                             <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-info-circle"></i> Chỉ dùng cho tài khoản gốc không có người mời
                            </p>
                        @endif
                    </div>

<<<<<<< HEAD

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-house-chimney text-blue-500 mr-1"></i>
                            Loại BĐS được phân công
                        </label>
                        <div
                            class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                            @foreach ($propertyTypeOptions as $id => $name)
                                <label
                                    class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white p-2 rounded">
                                    <input type="checkbox" wire:model="property_types" value="{{ $id }}"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-gray-700">{{ $name }}</span>
=======
                    <!-- Phân công loại BĐS -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-building-circle-check text-blue-500"></i>
                                Loại BĐS được phân công
                            </label>
                            <span class="text-[8px] bg-slate-100 text-slate-400 px-2 py-0.5 rounded-lg uppercase font-black">{{ count($property_types) }} đã chọn</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto custom-scrollbar p-1">
                            @foreach ($propertyTypeOptions as $id => $name)
                                <label class="flex items-center gap-4 p-4 rounded-2xl border cursor-pointer transition-all duration-200 {{ in_array($id, $property_types) ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-white border-gray-100 text-slate-600 hover:border-blue-200' }}">
                                    <input type="checkbox" wire:model="property_types" value="{{ $id }}" class="hidden">
                                    <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-colors {{ in_array($id, $property_types) ? 'border-white/40 bg-white/20' : 'border-slate-200 bg-slate-50' }}">
                                        @if(in_array($id, $property_types))
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-black uppercase tracking-tight">{{ $name }}</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-gray-100 flex flex-col md:flex-row justify-end gap-3 bg-white md:bg-gray-50 shrink-0">
                    <button wire:click="closeCreatePopup"
                        class="order-2 md:order-1 px-8 py-3 text-slate-400 hover:text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-colors mb-2 md:mb-0">
                        Hủy bỏ
                    </button>
                    <button wire:click="saveUser"
                        class="order-1 md:order-2 px-10 py-3 rounded-2xl bg-blue-600 text-white hover:bg-blue-700 font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-500/20 active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-save"></i> Cập nhật ngay
                    </button>
                </div>
            </div>
        </div>
    @endif

    @endif

    <!-- Delete Confirmation Modal -->
    @if ($confirmingUserDeletion)
        <div class="fixed inset-0 z-[300] flex items-end md:items-center justify-center p-0 md:p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="cancelDelete"></div>
            <div class="relative bg-white w-full max-w-sm rounded-t-3xl md:rounded-3xl shadow-2xl p-8 text-center animate-[slideUp_0.3s_ease-out]">
                <div class="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-red-500/10">
                    <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-3 uppercase tracking-tight">Xác nhận xóa?</h3>
                <p class="text-sm text-slate-400 font-bold mb-8 uppercase tracking-widest text-[10px] leading-relaxed">Hành động này không thể hoàn tác. Dữ liệu tài khoản sẽ bị xóa vĩnh viễn.</p>
                <div class="flex flex-col gap-3">
                    <button wire:click="deleteUser"
                        class="w-full py-4 rounded-2xl bg-red-600 text-white font-black text-[10px] uppercase tracking-widest shadow-lg shadow-red-500/20 active:scale-95 transition-all">
                        Xóa tài khoản ngay
                    </button>
                    <button wire:click="cancelDelete"
                        class="w-full py-4 rounded-2xl text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">
                        Hủy bỏ
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
            window.initInviterSelect2 = function(selectEl, selectedValue) {
                if (!window.jQuery || !window.jQuery.fn.select2 || !selectEl) {
                    return;
                }

                const $select = window.jQuery(selectEl);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                const $modal = $select.closest('.fixed');

                $select.select2({
                    width: '100%',
                    placeholder: 'Tìm kiếm tài khoản mời...',
                    allowClear: true,
                    dropdownParent: $modal.length ? $modal : window.jQuery(document.body),
                    language: {
                        noResults: function() {
                            return 'Không tìm thấy tài khoản';
                        },
                        searching: function() {
                            return 'Đang tìm...';
                        },
                        inputTooShort: function() {
                            return 'Nhập thêm ký tự để tìm kiếm';
                        }
                    }
                });

                $select.val(selectedValue ? String(selectedValue) : '').trigger('change.select2');

                $select.off('change.accountInviter').on('change.accountInviter', function() {
                    const livewireId = this.getAttribute('data-livewire-id');
                    if (!livewireId || !window.Livewire) {
                        return;
                    }

                    const selected = this.value === '' ? null : Number(this.value);
                    window.Livewire.find(livewireId).set('inviterUserId', selected);
                });
            };
        </script>
    @endonce
</div>
