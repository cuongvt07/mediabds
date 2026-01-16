<div class="h-full flex flex-col bg-slate-50 relative">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Quản Lý Tài Khoản</h1>
        <button wire:click="openCreatePopup"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
            <i class="fa-solid fa-plus"></i> Thêm Tài Khoản
        </button>
    </div>

    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center gap-4 shrink-0">
        <div class="relative w-full max-w-md">
            <input type="text" placeholder="Tìm kiếm theo tên hoặc SĐT..." wire:model.live.debounce.300ms="search"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition-shadow focus:shadow-md">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Họ và Tên</th>
                        <th class="px-6 py-4">Số điện thoại</th>
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
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
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
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-users-slash text-4xl mb-2 text-gray-300"></i>
                                    <p>Không tìm thấy tài khoản nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if ($showCreatePopup)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            x-transition.opacity>
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col animate-[scaleIn_0.2s_ease-out]">
                <div
                    class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                    <h2 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i
                                class="fa-solid fa-user-gear"></i></span>
                        {{ $selectedUserId ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản Mới' }}
                    </h2>
                    <button wire:click="closeCreatePopup"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Họ và Tên <span
                                class="text-red-500">*</span></label>
                        <input wire:model="name" type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Nhập họ và tên">
                        @error('name')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

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

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mật khẩu
                            {{ $selectedUserId ? '(Để trống nếu không đổi)' : '<span class="text-red-500">*</span>' }}</label>
                        <input wire:model="password" type="password"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Nhập mật khẩu">
                        @error('password')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-2xl">
                    <button wire:click="closeCreatePopup"
                        class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-200 font-bold transition-colors">Hủy
                        bỏ</button>
                    <button wire:click="saveUser"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Lưu Lại
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($confirmingUserDeletion)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div
                class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 text-center animate-[scaleIn_0.2s_ease-out]">
                <div
                    class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Xác nhận xóa tài khoản?</h3>
                <p class="text-sm text-gray-500 mb-6">Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa tài
                    khoản này không?</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="cancelDelete"
                        class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 font-bold transition-colors">Hủy
                        bỏ</button>
                    <button wire:click="deleteUser"
                        class="px-5 py-2.5 rounded-xl bg-red-600 text-white hover:bg-red-700 font-bold shadow-lg">Xóa
                        Ngay</button>
                </div>
            </div>
        </div>
    @endif
</div>
