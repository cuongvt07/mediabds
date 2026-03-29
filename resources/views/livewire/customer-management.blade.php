<div class="min-h-[100dvh] flex flex-col bg-gray-50">
    <!-- Header -->
    <div
        class="bg-white border-b px-4 py-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shrink-0">
        <h1 class="text-lg font-bold text-gray-800">Quản Lý Khách Hàng</h1>
        <button wire:click="openCreatePopup"
            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Thêm Khách Hàng
        </button>
    </div>

    <!-- Toolbar -->
    <div class="bg-white border-b px-4 py-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
        <!-- Search -->
        <div class="relative flex-1 sm:max-w-sm">
            <input type="text" placeholder="Tìm kiếm SĐT, tên, mã KH..." wire:model.live.debounce.300ms="search"
                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <i class="fa-solid fa-search text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
        </div>

        <!-- Status Filter -->
        <div class="flex items-center gap-2">
            <select wire:model.live="filterStatus"
                class="flex-1 sm:flex-none border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">Tất cả trạng thái</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if ($search || $filterStatus)
                <button wire:click="clearFilters"
                    class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1 whitespace-nowrap">
                    <i class="fa-solid fa-times"></i> Xóa lọc
                </button>
            @endif
        </div>
    </div>

    <!-- Customer List -->
    <div class="flex-1 overflow-auto p-2 sm:p-4">
        <!-- Pagination (Moved to top) -->
        <div class="mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="hidden md:block text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Hiển thị {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} /
                {{ $customers->total() }}
            </div>
            <div class="w-full md:w-auto flex justify-center">
                {{ $customers->links() }}
            </div>
        </div>
        <div class="bg-white rounded-lg border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px] sm:min-w-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Mã KH</th>
                            <th class="px-4 py-3 text-left font-semibold">Khách Hàng</th>
                            <th class="px-4 py-3 text-left font-semibold">SĐT</th>
                            <th class="px-4 py-3 text-left font-semibold">Trạng thái</th>
                            <th class="px-4 py-3 text-left font-semibold">NV Phụ trách</th>
                            <th class="px-4 py-3 text-right font-semibold">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span
                                        class="font-mono text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                        {{ $customer->code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">

                                        @if ($customer->avatar)
                                            <img src="{{ $customer->avatar_url }}" alt=""
                                                class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                        @else
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-medium">
                                                {{ mb_substr($customer->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-800">{{ $customer->name }}</span>
                                        @if ($customer->facebook)
                                            <a href="{{ $customer->facebook }}" target="_blank"
                                                class="text-blue-600 hover:text-blue-800" title="Facebook">
                                                <i class="fa-brands fa-facebook"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $customer->phone }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-medium {{ $statusColors[$customer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$customer->status] ?? $customer->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $customer->assignedUser?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="viewCustomerDetail({{ $customer->id }})"
                                            class="p-2 sm:p-1.5 text-blue-600 hover:bg-blue-50 rounded"
                                            title="Chi tiết">
                                            <i class="fa-solid fa-eye text-sm sm:text-xs"></i>
                                        </button>
                                        @if ($isAdmin || $customer->assigned_user_id === auth()->id())
                                            <button wire:click="editCustomer({{ $customer->id }})"
                                                class="p-2 sm:p-1.5 text-amber-600 hover:bg-amber-50 rounded"
                                                title="Sửa">
                                                <i class="fa-solid fa-pen text-sm sm:text-xs"></i>
                                            </button>
                                        @endif
                                        @if ($isAdmin)
                                            <button wire:click="confirmDelete({{ $customer->id }})"
                                                class="p-2 sm:p-1.5 text-red-600 hover:bg-red-50 rounded"
                                                title="Xóa">
                                                <i class="fa-solid fa-trash text-sm sm:text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Không tìm thấy khách hàng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create/Edit Modal -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 bg-[#050505]/80 backdrop-blur-xl flex items-end md:items-center justify-center z-[100] p-0 md:p-4 transition-all duration-500 overflow-hidden">
            <div class="bg-white w-full max-w-2xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col max-h-[85dvh] md:max-h-[90dvh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-center px-8 py-6 border-b border-gray-100 bg-white shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#00D1FF] text-white flex items-center justify-center shadow-lg shadow-[#00D1FF]/20 transition-transform hover:scale-110 duration-300">
                            <i class="fa-solid fa-user-pen text-lg"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-[#00D1FF] uppercase tracking-[0.2em] mb-1 block">Hồ sơ khách hàng</span>
                            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ $selectedCustomerId ? 'Cập Nhật' : 'Thêm Mới' }}</h2>
                        </div>
                    </div>
                    <button wire:click="closeCreatePopup" class="text-slate-300 hover:text-red-500 w-12 h-12 flex items-center justify-center rounded-2xl hover:bg-red-50 transition-all active:scale-95">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar space-y-8">
                    <!-- Customer Code + Avatar -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-8 bg-slate-50/50 border border-slate-100 rounded-3xl p-6 flex items-center gap-5 relative overflow-hidden group transition-all hover:bg-white hover:shadow-xl hover:shadow-slate-200/50">
                            <div class="absolute -right-8 -top-8 w-32 h-32 bg-[#00D1FF]/5 rounded-full blur-3xl group-hover:bg-[#00D1FF]/10 transition-colors"></div>
                            <div class="w-14 h-14 bg-[#050505] text-[#00D1FF] rounded-2xl flex items-center justify-center shadow-xl shrink-0">
                                <i class="fa-solid fa-fingerprint text-2xl"></i>
                            </div>
                            <div class="relative">
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-1">Mã định danh</p>
                                <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ $code }}</p>
                            </div>
                        </div>
                        
                        <!-- Avatar Upload -->
                        <div class="md:col-span-4 flex items-center gap-5">
                            <div class="w-20 h-20 rounded-[2rem] border-4 border-white shadow-2xl overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center relative group">
                                @if ($avatar)
                                    @php
                                        $extension = strtolower($avatar->getClientOriginalExtension());
                                        $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                    @endphp
                                    @if ($isPreviewable)
                                        <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-[#050505] text-[#00D1FF] p-2 text-center transition-transform duration-500 group-hover:scale-110">
                                            <i class="fa-solid fa-file-image text-2xl mb-1"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest">{{ $extension }}</span>
                                        </div>
                                    @endif
                                @elseif ($existingAvatar)
                                    <img src="{{ $existingAvatar }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                @else
                                    <i class="fa-solid fa-user-astronaut text-slate-300 text-3xl"></i>
                                @endif
                                
                                <label class="absolute inset-0 bg-[#050505]/60 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <i class="fa-solid fa-camera text-white text-xl"></i>
                                    <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                                </label>
                                
                                <div wire:loading wire:target="avatar" class="absolute inset-0 bg-[#050505]/80 backdrop-blur-md flex items-center justify-center">
                                    <i class="fa-solid fa-atom fa-spin text-[#00D1FF] text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Avatar</p>
                                <button type="button" class="text-xs font-black text-[#00D1FF] hover:text-[#00B8E6] transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-plus-circle"></i> Thay đổi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Form Grid -->
                    <div class="space-y-6">
                        <!-- Row 1: Name + Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="name" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm"
                                    placeholder="Vd: Nguyễn Văn A">
                                @error('name') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="phone" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm font-mono"
                                    placeholder="Vd: 0901234567">
                                @error('phone') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Row 1.5: Phone 2 + FB -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Số điện thoại phụ
                                </label>
                                <input wire:model="phone2" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm font-mono"
                                    placeholder="Nhập SĐT phụ (nếu có)">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Link Facebook
                                </label>
                                <input wire:model="facebook" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm"
                                    placeholder="https://facebook.com/...">
                            </div>
                        </div>

                        <!-- Row 2: Status + Assigned -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Trạng thái <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="status"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm">
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Phân công nhân viên
                                </label>
                                <select wire:model="assignedUserId"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Budget -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                Tài chính mong muốn (VNĐ)
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-2xl border border-gray-100">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">Từ</span>
                                        <input wire:model="budgetFromValue" type="number" step="0.1"
                                            class="w-full bg-transparent pl-10 pr-2 py-1.5 text-sm font-bold text-slate-700 focus:outline-none"
                                            placeholder="Vd: 3.5">
                                    </div>
                                    <select wire:model="budgetFromUnit" class="w-20 bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-[10px] font-black focus:ring-2 focus:ring-blue-500 outline-none uppercase shadow-sm">
                                        @foreach (\App\Livewire\CustomerManagement::BUDGET_UNITS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-2xl border border-gray-100">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">Đến</span>
                                        <input wire:model="budgetToValue" type="number" step="0.1"
                                            class="w-full bg-transparent pl-10 pr-2 py-1.5 text-sm font-bold text-slate-700 focus:outline-none"
                                            placeholder="Vd: 10">
                                    </div>
                                    <select wire:model="budgetToUnit" class="w-20 bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-[10px] font-black focus:ring-2 focus:ring-blue-500 outline-none uppercase shadow-sm">
                                        @foreach (\App\Livewire\CustomerManagement::BUDGET_UNITS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Description -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                Mô tả / Ghi chú chiến dịch
                            </label>
                            <textarea wire:model="description" rows="3"
                                class="w-full border border-gray-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm resize-none bg-slate-50/50"
                                placeholder="Nhập ghi chú quan trọng về khách hàng này..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-6 pb-12 md:pb-6 border-t border-slate-50 bg-white flex flex-col md:flex-row justify-between items-center gap-4 shrink-0">
                    <div class="w-full md:w-auto">
                        @if ($selectedCustomerId && $editFromDetailMode)
                            <button wire:click="backToDetail" class="w-full md:w-auto px-6 py-4 text-slate-400 hover:text-[#00D1FF] rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-3 transition-all hover:bg-slate-50">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại chi tiết
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                        <button wire:click="closeCreatePopup" class="px-8 py-4 text-slate-400 hover:text-red-500 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                            Hủy lệnh
                        </button>
                        <button wire:click="saveCustomer" class="px-12 py-4 bg-[#050505] text-white hover:bg-[#00D1FF] hover:text-[#050505] rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 shadow-2xl shadow-[#00D1FF]/10 active:scale-95 transition-all duration-300">
                            <i class="fa-solid fa-bolt-lightning"></i> {{ $selectedCustomerId ? 'Cập Nhật Ngay' : 'Khởi Tạo Ngay' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer Detail Modal -->
    @if ($showDetailPopup && $selectedCustomer)
        <div class="fixed inset-0 bg-[#050505]/80 backdrop-blur-xl flex items-end md:items-center justify-center z-[100] p-0 md:p-4 transition-all duration-500 overflow-hidden">
            <div class="bg-white w-full max-w-4xl rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col max-h-[85dvh] md:max-h-[90dvh] animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-8 py-6 border-b border-gray-100 bg-white shrink-0 gap-6">
                    <div class="flex items-center gap-5">

                        @if ($selectedCustomer->avatar)
                            <img src="{{ $selectedCustomer->avatar_url }}"
                                class="w-20 h-20 rounded-[2rem] object-cover border-4 border-white shadow-2xl shadow-slate-200/50">
                        @else
                            <div
                                class="w-20 h-20 bg-[#050505] text-[#00D1FF] rounded-[2rem] flex items-center justify-center text-3xl font-black shadow-2xl shadow-[#00D1FF]/10">
                                {{ mb_substr($selectedCustomer->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <span class="text-[10px] font-black text-[#00D1FF] uppercase tracking-[0.2em] mb-1 block">Chi tiết đối tác</span>
                            <h2 class="text-2xl font-black text-slate-800 truncate tracking-tighter uppercase leading-none">{{ $selectedCustomer->name }}</h2>
                            <p class="text-xs font-bold text-slate-400 mt-2 flex items-center gap-2">
                                <span class="bg-slate-100 px-2 py-0.5 rounded-lg text-blue-600 font-mono">{{ $selectedCustomer->code }}</span> 
                                <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                {{ $selectedCustomer->phone }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                        <button wire:click="viewCustomerListings"
                            class="flex-1 md:flex-none px-6 py-4 bg-[#050505] text-white hover:bg-[#00D1FF] hover:text-[#050505] rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-3 transition-all">
                            <i class="fa-solid fa-building"></i> Tin đăng
                        </button>
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <button wire:click="editFromDetail"
                                class="w-12 h-12 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-2xl flex items-center justify-center transition-all border border-amber-100">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        @endif
                        <button wire:click="closeDetailPopup" class="w-12 h-12 flex items-center justify-center text-slate-300 hover:text-red-500 rounded-2xl hover:bg-red-50 transition-all">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar">
                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-8">
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-12 h-12 bg-blue-500/5 rounded-full blur-xl group-hover:bg-blue-500/10 transition-colors"></div>
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Trạng thái</p>
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full {{ str_contains($statusColors[$selectedCustomer->status] ?? '', 'blue') ? 'bg-blue-500' : (str_contains($statusColors[$selectedCustomer->status] ?? '', 'green') ? 'bg-green-500' : 'bg-slate-400') }}"></span>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-tighter">{{ $statusLabels[$selectedCustomer->status] ?? $selectedCustomer->status }}</span>
                            </div>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Tài chính</p>
                            <p class="text-xs font-black text-slate-700">{{ $selectedCustomer->formatted_budget }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Phụ trách</p>
                            <p class="text-xs font-black text-slate-700 truncate" title="{{ $selectedCustomer->assignedUser?->name ?? 'Hệ thống' }}">
                                {{ $selectedCustomer->assignedUser?->name ?? 'Hệ thống' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Ngày tạo</p>
                            <p class="text-xs font-black text-slate-700">{{ $selectedCustomer->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-span-2 lg:col-span-1 bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2 text-indigo-500">Mạng xã hội</p>
                            @if ($selectedCustomer->facebook)
                                <a href="{{ $selectedCustomer->facebook }}" target="_blank" class="text-xs font-black text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <i class="fa-brands fa-facebook"></i> Inbox FB
                                </a>
                            @else
                                <p class="text-[10px] text-slate-300 italic font-bold">Chưa cập nhật</p>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    @if ($selectedCustomer->description)
                        <div class="mb-8 p-6 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            <div class="flex items-center gap-3 mb-4">
                                <i class="fa-solid fa-quote-left text-[#00D1FF] text-xl opacity-20"></i>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ghi chú đối tác</span>
                            </div>
                            <p class="text-sm font-medium text-slate-600 leading-relaxed italic">"{{ $selectedCustomer->description }}"</p>
                        </div>
                    @endif

                    <!-- Work Timeline Section -->
                    <div class="mb-4">
                        <h4 class="text-sm font-black text-slate-800 mb-5 flex items-center gap-3 uppercase tracking-tight">
                            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                            <span>Nội dung làm việc ({{ $selectedCustomer->works->count() }})</span>
                        </h4>

                        <!-- Add Work Form -->
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-4 mb-6 transition-all focus-within:border-blue-300">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <input wire:model="workDate" type="date" class="w-full h-10 px-3 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-700 outline-none">
                                    </div>
                                    <div class="md:col-span-6">
                                        <input wire:model="workContent" type="text" class="w-full h-10 px-4 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-700 outline-none" placeholder="Chi tiết công việc...">
                                    </div>
                                    <div class="md:col-span-2">
                                        <input wire:model="workProgress" type="text" class="w-full h-10 px-3 rounded-xl border border-slate-100 bg-slate-50 text-xs font-bold text-[#00D1FF] outline-none" placeholder="Tiến độ">
                                    </div>
                                    <div class="md:col-span-1">
                                        <button wire:click="addWork" class="w-full h-10 bg-[#050505] text-white hover:bg-green-600 rounded-xl transition-all shadow-lg active:scale-95">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('workContent') <p class="text-red-500 text-[9px] font-bold mt-2 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <!-- Work Timeline Table -->
                        <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
                            <!-- Desktop Timeline Table -->
                            <table class="hidden md:table w-full text-sm">
                                <thead class="bg-slate-50 text-[10px] text-slate-400 font-black uppercase tracking-wider">
                                    <tr>
                                        <th class="px-5 py-4 text-left w-32 border-b border-gray-50">Ngày</th>
                                        <th class="px-5 py-4 text-left border-b border-gray-50">Công việc</th>
                                        <th class="px-5 py-4 text-center w-32 border-b border-gray-50">Tiến độ</th>
                                        <th class="px-5 py-4 text-right border-b border-gray-50">Thực hiện</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($selectedCustomer->works as $work)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-5 py-4 font-mono text-xs text-slate-500 tracking-tight">{{ $work->formatted_date }}</td>
                                            <td class="px-5 py-4 text-slate-800 font-bold">{{ $work->content }}</td>
                                            <td class="px-5 py-4 text-center">
                                                @if ($work->progress)
                                                    <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $work->progress }}</span>
                                                @else
                                                    <span class="text-slate-300 italic text-[10px]">Chưa cập nhật</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-right text-slate-400 text-[10px] font-black uppercase">{{ $work->user?->name ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-10 text-center text-slate-300 italic text-xs font-bold">Lịch sử tương tác hiện đang trống.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Mobile Timeline Cards -->
                            <div class="md:hidden divide-y divide-slate-50">
                                @forelse ($selectedCustomer->works as $work)
                                    <div class="p-5 flex flex-col gap-3">
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-mono text-slate-400">{{ $work->formatted_date }}</span>
                                            @if ($work->progress)
                                                <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase">{{ $work->progress }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ $work->content }}</p>
                                        <div class="flex justify-between items-center pt-2 border-t border-slate-50 border-dashed">
                                            <span class="text-[9px] font-black text-slate-300 uppercase">Thực hiện bởi</span>
                                            <span class="text-[10px] font-black text-slate-500 uppercase">{{ $work->user?->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-10 text-center text-slate-300 italic text-xs font-bold">Chưa có lịch sử làm việc.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-6 pb-12 md:pb-6 border-t bg-white flex justify-end shrink-0">
                    <button wire:click="closeDetailPopup"
                        class="w-full md:w-auto px-10 py-4 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                        Đóng cửa sổ
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if ($confirmingDeletion)
        <div class="fixed inset-0 bg-[#050505]/90 backdrop-blur-2xl flex items-end md:items-center justify-center z-[200] p-0 md:p-4 transition-all duration-500">
            <div class="bg-white w-full md:w-[450px] rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl p-10 text-center animate-[slideUp_0.4s_cubic-bezier(0.16,1,0.3,1)] overflow-hidden relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-500/5 rounded-full blur-3xl"></div>
                
                <div class="w-24 h-24 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-red-500/10 rotate-12 transition-transform hover:rotate-0 duration-500">
                    <i class="fa-solid fa-triangle-exclamation text-4xl"></i>
                </div>
                
                <h3 class="font-black text-slate-800 text-2xl mb-3 tracking-tighter uppercase">Xóa khách hàng?</h3>
                <p class="text-[13px] text-slate-400 mb-10 font-bold leading-relaxed px-6 uppercase tracking-widest">Dữ liệu sẽ bị xóa khỏi không gian hệ thống và không thể khôi phục.</p>
                
                <div class="flex flex-col gap-4 pb-12 md:pb-0">
                    <button wire:click="deleteCustomer" class="w-full py-5 bg-red-600 text-white hover:bg-red-700 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-red-500/20 active:scale-95 transition-all">
                        Xác nhận xóa ngay
                    </button>
                    <button wire:click="cancelDelete" class="w-full py-5 text-slate-400 hover:text-slate-800 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                        Không, giữ lại
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
