<div class="h-full flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-3 flex items-center justify-between shrink-0">
        <h1 class="text-lg font-bold text-gray-800">Quản Lý Khách Hàng</h1>
        <button wire:click="openCreatePopup"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Thêm Khách Hàng
        </button>
    </div>

    <!-- Toolbar -->
    <div class="bg-white border-b px-4 py-3 flex items-center gap-3 shrink-0">
        <!-- Search -->
        <div class="relative flex-1 max-w-sm">
            <input type="text" placeholder="Tìm kiếm SĐT, tên, mã KH..." wire:model.live.debounce.300ms="search"
                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <i class="fa-solid fa-search text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
        </div>

        <!-- Status Filter -->
        <select wire:model.live="filterStatus"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
            <option value="">Tất cả trạng thái</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        @if ($search || $filterStatus)
            <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1">
                <i class="fa-solid fa-times"></i> Xóa lọc
            </button>
        @endif
    </div>

    <!-- Customer List -->
    <div class="flex-1 overflow-auto p-4">
        <div class="bg-white rounded-lg border overflow-hidden">
            <table class="w-full text-sm">
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
                                <span class="font-mono text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                    {{ $customer->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-medium">
                                        {{ mb_substr($customer->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600">{{ $customer->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $statusColors[$customer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusLabels[$customer->status] ?? $customer->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $customer->assignedUser?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="viewCustomerDetail({{ $customer->id }})"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Chi tiết">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    @if ($isAdmin || $customer->assigned_user_id === auth()->id())
                                        <button wire:click="editCustomer({{ $customer->id }})"
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded" title="Sửa">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                    @endif
                                    @if ($isAdmin)
                                        <button wire:click="confirmDelete({{ $customer->id }})"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Xóa">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                <i class="fa-solid fa-users-slash text-2xl mb-2"></i>
                                <p>Không tìm thấy khách hàng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-gray-500">
                Hiển thị {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} / {{ $customers->total() }}
            </span>
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-[55%] min-w-[500px] rounded-xl shadow-xl flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50 rounded-t-xl">
                    <h2 class="text-lg font-bold text-gray-800">
                        <i class="fa-solid fa-user-pen text-blue-600 mr-2"></i>
                        {{ $selectedCustomerId ? 'Cập Nhật Khách Hàng' : 'Thêm Khách Hàng Mới' }}
                    </h2>
                    <button wire:click="closeCreatePopup" class="text-gray-400 hover:text-gray-600 p-1">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Customer Code -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-hashtag text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-medium uppercase">Mã khách hàng</p>
                            <p class="text-2xl font-bold text-blue-700">{{ $code }}</p>
                        </div>
                    </div>

                    <!-- Form Grid -->
                    <div class="space-y-5">
                        <!-- Row 1: Name + Phone -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fa-solid fa-user text-gray-400 mr-1"></i>
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="name" type="text"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Nhập họ và tên khách hàng">
                                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fa-solid fa-phone text-gray-400 mr-1"></i>
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="phone" type="text"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Nhập số điện thoại">
                                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                        <!-- Row 1.5: Phone 2 -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fa-solid fa-phone text-gray-400 mr-1"></i>
                                    S? di?n tho?i 2
                                </label>
                                <input wire:model="phone2" type="text"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    placeholder="Nh?p S�T ph? (n?u c�)">
                                @error('phone2') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div></div>
                        </div>
                        </div>

                        <!-- Row 2: Status + Assigned -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fa-solid fa-flag text-gray-400 mr-1"></i>
                                    Trạng thái <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="status"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fa-solid fa-user-tie text-gray-400 mr-1"></i>
                                    Phân công nhân viên
                                </label>
                                <select wire:model="assignedUserId"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->phone }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Budget -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fa-solid fa-wallet text-gray-400 mr-1"></i>
                                Tài chính mong muốn (VNĐ)
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Từ</span>
                                    <input wire:model="budgetFrom" type="text"
                                        class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        placeholder="VD: 1000000000">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Đến</span>
                                    <input wire:model="budgetTo" type="text"
                                        class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        placeholder="VD: 5000000000">
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Description -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fa-solid fa-comment-dots text-gray-400 mr-1"></i>
                                Mô tả / Ghi chú
                            </label>
                            <textarea wire:model="description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                placeholder="Nhập mô tả, ghi chú về khách hàng..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-between rounded-b-xl">
                    <div>
                        @if ($selectedCustomerId && $editFromDetailMode)
                            <button wire:click="backToDetail" class="px-4 py-2.5 text-blue-600 hover:bg-blue-50 rounded-lg text-sm font-medium flex items-center gap-2">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại chi tiết
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="closeCreatePopup" class="px-5 py-2.5 text-gray-600 hover:bg-gray-200 rounded-lg text-sm font-medium">
                            Hủy bỏ
                        </button>
                        <button wire:click="saveCustomer" class="px-5 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Lưu lại
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer Detail Modal -->
    @if ($showDetailPopup && $selectedCustomer)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-[60%] min-w-[600px] rounded-xl shadow-xl flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50 rounded-t-xl">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-lg flex items-center justify-center text-xl font-bold">
                            {{ mb_substr($selectedCustomer->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">{{ $selectedCustomer->name }}</h2>
                            <p class="text-sm text-gray-500">
                                <span class="font-mono">{{ $selectedCustomer->code }}</span> • {{ $selectedCustomer->phone }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="viewCustomerListings" 
                            class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium flex items-center gap-2">
                            <i class="fa-solid fa-building"></i> Xem tin đăng khách
                        </button>
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <button wire:click="editFromDetail" class="px-4 py-2 bg-yellow-500 text-gray-900 hover:bg-yellow-600 rounded-lg text-sm font-medium flex items-center gap-2 border border-yellow-600">
                                <i class="fa-solid fa-pen"></i> Sửa
                            </button>
                        @endif
                        <button wire:click="closeDetailPopup" class="text-gray-400 hover:text-gray-600 p-1">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Info Cards -->
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-flag text-gray-400 mr-1"></i> Trạng thái
                            </p>
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $statusColors[$selectedCustomer->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$selectedCustomer->status] ?? $selectedCustomer->status }}
                            </span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-wallet text-gray-400 mr-1"></i> Tài chính mong muốn
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedCustomer->formatted_budget }}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-user-tie text-gray-400 mr-1"></i> NV Phụ trách
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedCustomer->assignedUser?->name ?? 'Chưa phân công' }}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-medium mb-1">
                                <i class="fa-solid fa-calendar text-gray-400 mr-1"></i> Ngày tạo
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedCustomer->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    @if ($selectedCustomer->description)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                            <p class="text-xs text-gray-500 font-medium mb-2">
                                <i class="fa-solid fa-comment-dots text-gray-400 mr-1"></i> Mô tả / Ghi chú
                            </p>
                            <p class="text-sm text-gray-700">{{ $selectedCustomer->description }}</p>
                        </div>
                    @endif

                    <!-- Work Timeline Section -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> 
                            Nội dung làm việc ({{ $selectedCustomer->works->count() }})
                        </h4>

                        <!-- Add Work Form -->
                        @if ($isAdmin || $selectedCustomer->assigned_user_id === auth()->id())
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 flex items-center gap-3">
                                <span class="text-sm font-semibold text-green-800 whitespace-nowrap">
                                    <i class="fa-solid fa-plus-circle mr-1"></i> Thêm:
                                </span>
                                <input wire:model="workDate" type="date"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-36">
                                <input wire:model="workContent" type="text"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                    placeholder="Nội dung công việc...">
                                <input wire:model="workProgress" type="text"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-28"
                                    placeholder="Tiến độ">
                                <button wire:click="addWork"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 whitespace-nowrap">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </div>
                            @error('workContent') <div class="text-red-500 text-xs mb-3 -mt-2">{{ $message }}</div> @enderror
                        @endif

                        <!-- Work Timeline Table -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold w-28">Ngày</th>
                                        <th class="px-4 py-3 text-left font-semibold">Công việc</th>
                                        <th class="px-4 py-3 text-left font-semibold w-28">Tiến độ</th>
                                        <th class="px-4 py-3 text-left font-semibold w-32">Người thực hiện</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($selectedCustomer->works as $work)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $work->formatted_date }}</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $work->content }}</td>
                                            <td class="px-4 py-3">
                                                @if ($work->progress)
                                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium">{{ $work->progress }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $work->user?->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                                <i class="fa-solid fa-inbox text-xl mb-1"></i>
                                                <p>Chưa có công việc nào.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end rounded-b-xl">
                    <button wire:click="closeDetailPopup" class="px-5 py-2.5 text-gray-600 hover:bg-gray-200 rounded-lg text-sm font-medium">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if ($confirmingDeletion)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white w-[400px] rounded-xl shadow-xl p-6 text-center">
                <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg mb-2">Xóa khách hàng?</h3>
                <p class="text-sm text-gray-500 mb-5">Hành động này không thể hoàn tác. Tất cả dữ liệu sẽ bị xóa vĩnh viễn.</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="cancelDelete" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">
                        Hủy
                    </button>
                    <button wire:click="deleteCustomer" class="px-5 py-2.5 bg-red-600 text-white hover:bg-red-700 rounded-lg text-sm font-medium">
                        Xóa
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

