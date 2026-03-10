<div class="h-full flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-3 md:py-4 flex flex-row items-center justify-between shrink-0 gap-4">
        <h1 class="text-lg md:text-xl font-black text-slate-800 uppercase tracking-tight shrink-0">Khách Hàng</h1>
        <button wire:click="openCreatePopup"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-xs md:text-sm flex items-center gap-2 shadow-lg shadow-blue-500/20 transition-all active:scale-95">
            <i class="fa-solid fa-plus font-black"></i> <span class="hidden sm:inline">Thêm Khách Hàng</span> <span class="sm:hidden">Thêm Mới</span>
        </button>
    </div>

    <!-- Toolbar -->
<<<<<<< HEAD
    <div class="bg-white border-b px-4 py-3 flex flex-col sm:flex-row items-center gap-3 shrink-0">
        <!-- Search -->
        <div class="relative w-full sm:flex-1 sm:max-w-sm">
=======
    <div class="bg-white border-b px-4 py-3 flex flex-col md:flex-row items-stretch md:items-center gap-3 shrink-0">
        <!-- Search -->
        <div class="relative flex-1 max-w-none md:max-w-sm">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
            <input type="text" placeholder="Tìm kiếm SĐT, tên, mã KH..." wire:model.live.debounce.300ms="search"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm">
            <i class="fa-solid fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
        </div>

<<<<<<< HEAD
        <!-- Status Filter -->
        <div class="w-full sm:w-auto flex items-center gap-3">
            <select wire:model.live="filterStatus"
                class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
=======
        <!-- Filter Group -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 md:pb-0">
            <!-- Status Filter -->
            <select wire:model.live="filterStatus"
                class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm shrink-0">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                <option value="">Tất cả trạng thái</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if ($search || $filterStatus)
<<<<<<< HEAD
                <button wire:click="clearFilters"
                    class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1 whitespace-nowrap">
                    <i class="fa-solid fa-times"></i> Xóa lọc
=======
                <button wire:click="clearFilters" class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors shrink-0" title="Xóa lọc">
                    <i class="fa-solid fa-circle-xmark fa-lg"></i>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                </button>
            @endif
        </div>
    </div>

    <!-- Customer List -->
<<<<<<< HEAD
    <div class="flex-1 overflow-auto p-4">
        <div class="bg-white rounded-lg border overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
=======
    <div class="flex-1 overflow-auto p-4 custom-scrollbar">
        <!-- Desktop Table View -->
        <div class="hidden md:block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-wider">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                    <tr>
                        <th class="px-6 py-4 text-left">Mã KH</th>
                        <th class="px-6 py-4 text-left">Khách Hàng</th>
                        <th class="px-6 py-4 text-left">SĐT</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-left">NV Phụ trách</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($customers as $customer)
<<<<<<< HEAD
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
=======
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" wire:click="viewCustomerDetail({{ $customer->id }})">
                            <td class="px-6 py-4">
                                <span class="font-black text-[10px] text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">
                                    {{ $customer->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden shadow-sm shrink-0">
                                        @if ($customer->avatar)
                                            <img src="{{ $customer->avatar_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black">
                                                {{ mb_substr($customer->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate">{{ $customer->name }}</div>
                                        @if ($customer->facebook)
                                            <a href="{{ $customer->facebook }}" target="_blank" @click.stop class="text-blue-500 hover:text-blue-700 text-[10px] flex items-center gap-1 mt-0.5">
                                                <i class="fa-brands fa-facebook"></i> Kết nối Facebook
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-600 font-mono text-xs">{{ $customer->phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusColors[$customer->status] ?? 'bg-gray-100 text-gray-600' }}">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                    {{ $statusLabels[$customer->status] ?? $customer->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-slate-500 text-xs">
                                    <i class="fa-solid fa-user-tie text-[10px]"></i>
                                    {{ $customer->assignedUser?->name ?? 'Chưa phân công' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click.stop="viewCustomerDetail({{ $customer->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    @if ($isAdmin || $customer->assigned_user_id === auth()->id())
                                        <button wire:click.stop="editCustomer({{ $customer->id }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endif
                                    @if ($isAdmin)
                                        <button wire:click.stop="confirmDelete({{ $customer->id }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl mb-3 block opacity-20"></i>
                                <p class="font-bold">Không tìm thấy khách hàng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden flex flex-col gap-4">
            @forelse ($customers as $customer)
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm relative active:bg-gray-50 transition-colors"
                     wire:click="viewCustomerDetail({{ $customer->id }})">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden shadow-md shrink-0 ring-2 ring-white">
                            @if ($customer->avatar)
                                <img src="{{ $customer->avatar_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center text-base font-black uppercase">
                                    {{ mb_substr($customer->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-black text-[9px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg uppercase">
                                    {{ $customer->code }}
                                </span>
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $statusColors[$customer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusLabels[$customer->status] ?? $customer->status }}
                                </span>
                            </div>
                            <h3 class="font-black text-slate-800 text-base mt-1 truncate">{{ $customer->name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <a href="tel:{{ $customer->phone }}" @click.stop class="text-slate-500 font-mono text-sm hover:text-blue-600">
                                    <i class="fa-solid fa-phone-flip text-[10px] mr-1"></i> {{ $customer->phone }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                        <div class="text-[10px] text-slate-400 font-bold flex items-center gap-1.5 uppercase">
                            <i class="fa-solid fa-user-circle"></i> {{ $customer->assignedUser?->name ?? 'N/A' }}
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($isAdmin || $customer->assigned_user_id === auth()->id())
                                <button wire:click.stop="editCustomer({{ $customer->id }})" class="text-amber-500 p-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            @endif
                            @if ($isAdmin)
                                <button wire:click.stop="confirmDelete({{ $customer->id }})" class="text-red-500 p-1">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-12 py-16 text-center text-slate-400 border border-gray-100 italic">
                    <i class="fa-solid fa-users-slash text-4xl mb-4 block opacity-10"></i>
                    Không có dữ liệu khách hàng.
                </div>
            @endforelse
        </div>
    </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-gray-500">
                Hiển thị {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} /
                {{ $customers->total() }}
            </span>
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if ($showCreatePopup)
<<<<<<< HEAD
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-full sm:w-[55%] sm:min-w-[500px] rounded-xl shadow-xl flex flex-col max-h-[90vh]">
=======
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all">
            <div class="bg-white w-full max-w-2xl rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[85vh] animate-[slideUp_0.3s_ease-out]">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                <!-- Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-white md:bg-gray-50 rounded-t-3xl">
                    <h2 class="text-base md:text-lg font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <span>{{ $selectedCustomerId ? 'Cập Nhật Khách Hàng' : 'Thêm Khách Hàng Mới' }}</span>
                    </h2>
                    <button wire:click="closeCreatePopup" class="text-gray-400 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-xl hover:bg-red-50 transition-colors">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
<<<<<<< HEAD
                <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                    <!-- Customer Code + Avatar -->
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 mb-6">
                        <div class="flex-1 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-600 text-white rounded-lg flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-hashtag text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-medium uppercase">Mã khách hàng</p>
                                <p class="text-xl sm:text-2xl font-bold text-blue-700">{{ $code }}</p>
=======
                <div class="flex-1 overflow-y-auto p-5 md:p-6 custom-scrollbar">
                    <!-- Customer Code + Avatar -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                        <div class="md:col-span-2 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-600/5 rounded-full blur-2xl group-hover:bg-blue-600/10 transition-colors"></div>
                            <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                                <i class="fa-solid fa-hashtag text-xl font-black"></i>
                            </div>
                            <div class="relative">
                                <p class="text-[10px] text-blue-400 font-black uppercase tracking-widest">Mã khách hàng</p>
                                <p class="text-2xl font-black text-blue-700 tracking-tight">{{ $code }}</p>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            </div>
                        </div>

                        <!-- Avatar Upload -->
<<<<<<< HEAD
                        <div class="w-full sm:w-1/3">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ảnh đại diện</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-16 h-16 rounded-full border border-gray-300 overflow-hidden flex-shrink-0 bg-gray-100 flex items-center justify-center relative">
                                    @if ($avatar)
                                        <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif ($existingAvatar)
                                        <img src="{{ $existingAvatar }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-user text-gray-400 text-2xl"></i>
                                    @endif

                                    <div wire:loading wire:target="avatar"
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <i class="fa-solid fa-spinner fa-spin text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input type="file" wire:model="avatar" accept="image/*"
                                        class="text-xs text-gray-500 file:mr-2 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full">
                                    <p class="text-[10px] text-gray-400 mt-1">PNG, JPG, GIF tối đa 5MB.</p>
                                    @error('avatar')
                                        <span class="text-red-500 text-xs block">{{ $message }}</span>
                                    @enderror
=======
                        <div class="flex items-center gap-4 px-1">
                            <div class="w-16 h-16 rounded-2xl border-2 border-gray-100 overflow-hidden shrink-0 bg-gray-50 flex items-center justify-center relative shadow-sm group">
                                @if ($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif ($existingAvatar)
                                    <img src="{{ $existingAvatar }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-user text-gray-300 text-2xl"></i>
                                @endif
                                
                                <div wire:loading wire:target="avatar" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fa-solid fa-spinner fa-spin text-white"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pointer-events-none">ẢNH ĐẠI DIỆN</label>
                                <div class="relative">
                                    <input type="file" wire:model="avatar" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                    <button class="w-full text-left text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors truncate">
                                        <i class="fa-solid fa-cloud-arrow-up mr-1 text-[10px]"></i> Tải lên ảnh mới
                                    </button>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Grid -->
<<<<<<< HEAD
                    <div class="space-y-4 sm:space-y-5">
                        <!-- Row 1: Name + Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
=======
                    <div class="space-y-6">
                        <!-- Row 1: Name + Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="name" type="text"
<<<<<<< HEAD
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Nhập họ và tên khách hàng">
                                @error('name')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
=======
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm"
                                    placeholder="Vd: Nguyễn Văn A">
                                @error('name') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="phone" type="text"
<<<<<<< HEAD
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Nhập số điện thoại">
                                @error('phone')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Row 1.5: Phone 2 -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fa-solid fa-phone text-gray-400 mr-1"></i>
                                        Số điện thoại 2
                                    </label>
                                    <input wire:model="phone2" type="text"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        placeholder="Nhập SĐT phụ (nếu có)">
                                    @error('phone2')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fa-brands fa-facebook text-blue-600 mr-1"></i>
                                        Link Facebook
                                    </label>
                                    <input wire:model="facebook" type="text"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        placeholder="https://facebook.com/...">
                                    @error('facebook')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Status + Assigned -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
=======
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm font-mono"
                                    placeholder="Vd: 0901234567">
                                @error('phone') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <!-- Row 1.5: Phone 2 + FB -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Số điện thoại 2
                                </label>
                                <input wire:model="phone2" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm font-mono"
                                    placeholder="SĐT dự phòng (nếu có)">
                                @error('phone2') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Link Facebook
                                </label>
                                <input wire:model="facebook" type="text"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm"
                                    placeholder="https://facebook.com/user">
                                @error('facebook') <span class="text-red-500 text-[10px] font-bold mt-1.5 block ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        </div>

                        <!-- Row 2: Status + Assigned -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
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
<<<<<<< HEAD
                                        <option value="{{ $employee->id }}">{{ $employee->name }}
                                            ({{ $employee->phone }})</option>
=======
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Budget -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                Tài chính mong muốn (VNĐ)
                            </label>
<<<<<<< HEAD
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex gap-2 items-center">
                                    <div class="relative flex-1">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Từ</span>
=======
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-2xl border border-gray-100">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">Từ</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                        <input wire:model="budgetFromValue" type="number" step="0.1"
                                            class="w-full bg-transparent pl-10 pr-2 py-1.5 text-sm font-bold text-slate-700 focus:outline-none"
                                            placeholder="Vd: 3.5">
                                    </div>
<<<<<<< HEAD
                                    <select wire:model="budgetFromUnit"
                                        class="w-24 border border-gray-300 rounded-lg px-2 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
=======
                                    <select wire:model="budgetFromUnit" class="w-20 bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-[10px] font-black focus:ring-2 focus:ring-blue-500 outline-none uppercase shadow-sm">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                        @foreach (\App\Livewire\CustomerManagement::BUDGET_UNITS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-2xl border border-gray-100">
                                    <div class="relative flex-1">
<<<<<<< HEAD
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Đến</span>
=======
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">Đến</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                        <input wire:model="budgetToValue" type="number" step="0.1"
                                            class="w-full bg-transparent pl-10 pr-2 py-1.5 text-sm font-bold text-slate-700 focus:outline-none"
                                            placeholder="Vd: 10">
                                    </div>
<<<<<<< HEAD
                                    <select wire:model="budgetToUnit"
                                        class="w-24 border border-gray-300 rounded-lg px-2 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
=======
                                    <select wire:model="budgetToUnit" class="w-20 bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-[10px] font-black focus:ring-2 focus:ring-blue-500 outline-none uppercase shadow-sm">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
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
                                Mô tả / Ghi chú
                            </label>
                            <textarea wire:model="description" rows="3"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm resize-none"
                                placeholder="Nội dung ghi chú..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-100 bg-white md:bg-gray-50 flex justify-between rounded-b-3xl">
                    <div>
                        @if ($selectedCustomerId && $editFromDetailMode)
<<<<<<< HEAD
                            <button wire:click="backToDetail"
                                class="px-4 py-2.5 text-blue-600 hover:bg-blue-50 rounded-lg text-sm font-medium flex items-center gap-2">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại chi tiết
=======
                            <button wire:click="backToDetail" class="px-4 py-2.5 text-blue-600 hover:bg-blue-50 rounded-xl text-xs font-black uppercase flex items-center gap-2 transition-all">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-3">
<<<<<<< HEAD
                        <button wire:click="closeCreatePopup"
                            class="px-5 py-2.5 text-gray-600 hover:bg-gray-200 rounded-lg text-sm font-medium">
                            Hủy bỏ
                        </button>
                        <button wire:click="saveCustomer"
                            class="px-5 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Lưu lại
=======
                        <button wire:click="closeCreatePopup" class="px-6 py-2.5 text-slate-400 hover:text-slate-600 rounded-xl text-xs font-black uppercase">
                            Hủy bỏ
                        </button>
                        <button wire:click="saveCustomer" class="px-8 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-xl text-xs font-black uppercase flex items-center gap-2 shadow-lg shadow-blue-500/20 active:scale-95 transition-all">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Lưu dữ liệu
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer Detail Modal -->
    @if ($showDetailPopup && $selectedCustomer)
<<<<<<< HEAD
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-full sm:w-[60%] sm:min-w-[600px] rounded-xl shadow-xl flex flex-col max-h-[90vh]">
=======
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all">
            <div class="bg-white w-full max-w-3xl rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[85vh] animate-[slideUp_0.3s_ease-out]">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-6 py-4 border-b border-gray-100 bg-white md:bg-gray-50 rounded-t-3xl gap-4">
                    <div class="flex items-center gap-4">
<<<<<<< HEAD

                        @if ($selectedCustomer->avatar)
                            <img src="{{ $selectedCustomer->avatar_url }}"
                                class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md">
                        @else
                            <div
                                class="w-16 h-16 bg-blue-600 text-white rounded-lg flex items-center justify-center text-2xl font-bold shadow-md">
                                {{ mb_substr($selectedCustomer->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h2 class="text-base sm:text-lg font-bold text-gray-800 truncate">
                                {{ $selectedCustomer->name }}</h2>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">
                                <span class="font-mono">{{ $selectedCustomer->code }}</span> •
                                {{ $selectedCustomer->phone }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0 ml-2">
                        <button wire:click="viewCustomerListings"
                            class="px-2 py-1.5 sm:px-4 sm:py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-1 sm:gap-2">
                            <i class="fa-solid fa-building"></i> <span class="hidden sm:inline">Xem tin đăng</span>
                        </button>
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <button wire:click="editFromDetail"
                                class="px-2 py-1.5 sm:px-4 sm:py-2 bg-yellow-500 text-gray-900 hover:bg-yellow-600 rounded-lg text-xs sm:text-sm font-medium flex items-center gap-1 sm:gap-2 border border-yellow-600">
                                <i class="fa-solid fa-pen"></i> <span class="hidden sm:inline">Sửa</span>
=======
                        <div class="w-16 h-16 rounded-2xl overflow-hidden shadow-lg ring-4 ring-white shrink-0">
                            @if ($selectedCustomer->avatar)
                                <img src="{{ $selectedCustomer->avatar_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center text-2xl font-black">
                                    {{ mb_substr($selectedCustomer->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ $selectedCustomer->name }}</h2>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-0.5">
                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg mr-1">{{ $selectedCustomer->code }}</span> • {{ $selectedCustomer->phone }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto no-scrollbar pb-1 md:pb-0">
                        <button wire:click="viewCustomerListings" 
                            class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-xl text-[10px] font-black uppercase flex items-center gap-2 shadow-lg shadow-blue-500/20 shrink-0">
                            <i class="fa-solid fa-building"></i> Tin đăng
                        </button>
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <button wire:click="editFromDetail" class="px-4 py-2 bg-amber-500 text-white hover:bg-amber-600 rounded-xl text-[10px] font-black uppercase flex items-center gap-2 shadow-lg shadow-amber-500/20 shrink-0">
                                <i class="fa-solid fa-pen"></i> Sửa
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                            </button>
                        @endif
                        <button wire:click="closeDetailPopup" class="text-slate-300 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 md:bg-transparent shrink-0">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
<<<<<<< HEAD
                <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-flag text-gray-400 mr-1"></i> Trạng thái
                            </p>
                            <span
                                class="px-2 py-1 rounded text-xs font-medium {{ $statusColors[$selectedCustomer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$selectedCustomer->status] ?? $selectedCustomer->status }}
                            </span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-wallet text-gray-400 mr-1"></i> Tài chính mong muốn
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedCustomer->formatted_budget }}
                            </p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-user-tie text-gray-400 mr-1"></i> NV Phụ trách
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $selectedCustomer->assignedUser?->name ?? 'Chưa phân công' }}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-calendar text-gray-400 mr-1"></i> Ngày tạo
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $selectedCustomer->created_at->format('d/m/Y') }}</p>
=======
                <div class="flex-1 overflow-y-auto p-5 md:p-6 custom-scrollbar">
                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-8">
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 relative overflow-hidden">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Trạng thái</p>
                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $statusColors[$selectedCustomer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$selectedCustomer->status] ?? $selectedCustomer->status }}
                            </span>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2 text-blue-500">Tài chính</p>
                            <p class="text-xs font-black text-slate-700">{{ $selectedCustomer->formatted_budget }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Phụ trách</p>
                            <p class="text-xs font-black text-slate-700 truncate">{{ $selectedCustomer->assignedUser?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2">Ngày tạo</p>
                            <p class="text-xs font-black text-slate-700">{{ $selectedCustomer->created_at->format('d/m/Y') }}</p>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        </div>
                        <div class="col-span-2 lg:col-span-1 bg-slate-50 border border-slate-100 rounded-2xl p-3">
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mb-2 text-indigo-500">Mạng xã hội</p>
                            @if ($selectedCustomer->facebook)
<<<<<<< HEAD
                                <a href="{{ $selectedCustomer->facebook }}" target="_blank"
                                    class="text-sm font-semibold text-blue-600 hover:underline truncate block">
                                    Xem trang cá nhân
=======
                                <a href="{{ $selectedCustomer->facebook }}" target="_blank" class="text-xs font-black text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <i class="fa-brands fa-facebook"></i> Inbox FB
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                </a>
                            @else
                                <p class="text-[10px] text-slate-300 italic font-bold">Chưa cập nhật</p>
                            @endif
                        </div>
                    </div>

                    @if ($selectedCustomer->description)
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-8">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-wider mb-2">
                                <i class="fa-solid fa-comment-dots mr-1"></i> Ghi chú hệ thống
                            </p>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $selectedCustomer->description }}</p>
                        </div>
                    @endif

                    <!-- Work Timeline Section -->
<<<<<<< HEAD
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                            Nội dung làm việc ({{ $selectedCustomer->works->count() }})
=======
                    <div class="mb-4">
                        <h4 class="text-sm font-black text-slate-800 mb-5 flex items-center gap-3 uppercase tracking-tight">
                            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <span>Nội dung làm việc ({{ $selectedCustomer->works->count() }})</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        </h4>

                        <!-- Add Work Form -->
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
<<<<<<< HEAD
                            <div
                                class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-3">
                                <span
                                    class="text-sm font-semibold text-green-800 whitespace-nowrap w-full sm:w-auto mb-1 sm:mb-0">
                                    <i class="fa-solid fa-plus-circle mr-1"></i> Thêm:
                                </span>
                                <input wire:model="workDate" type="date"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-1/2 sm:w-36">
                                <input wire:model="workProgress" type="text"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-[45%] sm:w-28"
                                    placeholder="Tiến độ">
                                <input wire:model="workContent" type="text"
                                    class="w-full sm:flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1 sm:mt-0"
                                    placeholder="Nội dung công việc...">
                                <button wire:click="addWork"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 whitespace-nowrap">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </div>
                            @error('workContent')
                                <div class="text-red-500 text-xs mb-3 -mt-2">{{ $message }}</div>
                            @enderror
                        @endif

                        <!-- Work Timeline Table -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden overflow-x-auto">
                            <table class="w-full text-sm min-w-[500px]">
                                <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
=======
                            <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-4 mb-6 transition-all focus-within:border-blue-300">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Ngày thực hiện</label>
                                        <input wire:model="workDate" type="date"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Nội dung</label>
                                        <input wire:model="workContent" type="text"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none"
                                            placeholder="Giao dịch, tư vấn...">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Tiến độ</label>
                                        <input wire:model="workProgress" type="text"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none"
                                            placeholder="Vd: 50%">
                                    </div>
                                    <div class="md:col-span-2 flex items-end">
                                        <button wire:click="addWork"
                                            class="w-full py-2 bg-green-600 text-white rounded-xl text-xs font-black uppercase hover:bg-green-700 transition-all shadow-lg shadow-green-500/20 active:scale-95">
                                            <i class="fa-solid fa-plus mr-1"></i> Lưu
                                        </button>
                                    </div>
                                </div>
                                @error('workContent') <div class="text-red-500 text-[10px] font-black mt-2 uppercase text-center">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <!-- Work Timeline Table -->
                        <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                            <!-- Desktop Timeline Table -->
                            <table class="hidden md:table w-full text-sm">
                                <thead class="bg-slate-50 text-[10px] text-slate-400 font-black uppercase tracking-wider">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                    <tr>
                                        <th class="px-5 py-4 text-left w-32 border-b border-gray-50">Ngày</th>
                                        <th class="px-5 py-4 text-left border-b border-gray-50">Công việc</th>
                                        <th class="px-5 py-4 text-left w-32 border-b border-gray-50">Tiến độ</th>
                                        <th class="px-5 py-4 text-left w-40 border-b border-gray-50">Thực hiện</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($selectedCustomer->works as $work)
<<<<<<< HEAD
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                                {{ $work->formatted_date }}</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $work->content }}</td>
                                            <td class="px-4 py-3">
                                                @if ($work->progress)
                                                    <span
                                                        class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium">{{ $work->progress }}</span>
=======
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-5 py-4 font-mono text-xs text-slate-500 tracking-tight">{{ $work->formatted_date }}</td>
                                            <td class="px-5 py-4 text-slate-800 font-bold">{{ $work->content }}</td>
                                            <td class="px-5 py-4">
                                                @if ($work->progress)
                                                    <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $work->progress }}</span>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                                @else
                                                    <span class="text-slate-300 italic text-[10px]">Chưa cập nhật</span>
                                                @endif
                                            </td>
<<<<<<< HEAD
                                            <td class="px-4 py-3 text-gray-600 text-xs">
                                                {{ $work->user?->name ?? '-' }}</td>
=======
                                            <td class="px-5 py-4 text-slate-400 text-[10px] font-black uppercase">{{ $work->user?->name ?? 'N/A' }}</td>
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-12 text-center text-slate-300">
                                                <i class="fa-solid fa-inbox text-3xl mb-3 block opacity-20"></i>
                                                <p class="font-bold">Chưa có lịch sử làm việc.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Mobile Timeline View -->
                            <div class="md:hidden divide-y divide-gray-50">
                                @forelse ($selectedCustomer->works as $work)
                                    <div class="p-4 bg-white">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $work->formatted_date }}</span>
                                            @if ($work->progress)
                                                <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase">{{ $work->progress }}</span>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 mb-2 leading-tight">{{ $work->content }}</p>
                                        <div class="text-[9px] text-slate-400 font-black uppercase flex items-center gap-1.5">
                                            <i class="fa-solid fa-user-circle"></i> {{ $work->user?->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-slate-300 font-bold italic text-xs uppercase tracking-widest">
                                        Trống
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
<<<<<<< HEAD
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end rounded-b-xl">
                    <button wire:click="closeDetailPopup"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-200 rounded-lg text-sm font-medium">
=======
                <div class="px-6 py-4 border-t border-gray-100 bg-white md:bg-gray-50 flex justify-end rounded-b-3xl">
                    <button wire:click="closeDetailPopup" class="w-full md:w-auto px-10 py-3 bg-slate-100 md:bg-white border border-gray-200 text-slate-600 hover:bg-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if ($confirmingDeletion)
<<<<<<< HEAD
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-[400px] rounded-xl shadow-xl p-6 text-center">
                <div
                    class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg mb-2">Xóa khách hàng?</h3>
                <p class="text-sm text-gray-500 mb-5">Hành động này không thể hoàn tác. Tất cả dữ liệu sẽ bị xóa vĩnh
                    viễn.</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="cancelDelete"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">
                        Hủy
                    </button>
                    <button wire:click="deleteCustomer"
                        class="px-5 py-2.5 bg-red-600 text-white hover:bg-red-700 rounded-lg text-sm font-medium">
                        Xóa
=======
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-end md:items-center justify-center z-[60] p-0 md:p-4">
            <div class="bg-white w-full md:w-[400px] rounded-t-3xl md:rounded-3xl shadow-2xl p-8 text-center animate-[slideUp_0.3s_ease-out]">
                <div class="w-20 h-20 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-red-500/10 rotate-3">
                    <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-xl mb-2 tracking-tight">XÓA KHÁCH HÀNG?</h3>
                <p class="text-sm text-slate-400 mb-8 font-bold leading-relaxed px-4">Dữ liệu sẽ bị xóa vĩnh viễn khỏi hệ thống và không thể khôi phục.</p>
                <div class="flex flex-col md:flex-row justify-center gap-3">
                    <button wire:click="cancelDelete" class="w-full order-2 md:order-1 px-8 py-3 text-slate-400 hover:text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest">
                        Hủy
                    </button>
                    <button wire:click="deleteCustomer" class="w-full order-1 md:order-2 px-8 py-3 bg-red-600 text-white hover:bg-red-700 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-500/20 active:scale-95 transition-all">
                        Xác nhận xóa
>>>>>>> cdc78b0cf3ea7808bcb9d42230ea16571468e2b8
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
