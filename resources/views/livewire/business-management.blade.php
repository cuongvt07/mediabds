<div class="h-full flex flex-col bg-slate-50 relative">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between shrink-0">
        <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3 lowercase">
            <span class="p-2 bg-blue-100 text-blue-600 rounded-xl">
                <i class="fa-solid fa-briefcase"></i>
            </span>
            Quản Lý <span class="hidden sm:inline">Kinh Doanh</span>
        </h1>
    </div>

    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex flex-col sm:flex-row items-center gap-3 sm:gap-4 shrink-0">
        <div class="relative w-full max-w-md">
            <input type="text" placeholder="Tìm kiếm nhân viên..." wire:model.live.debounce.300ms="search"
                class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-[11px] md:text-sm shadow-sm transition-shadow focus:shadow-md uppercase font-bold tracking-tight">
            <i class="fa-solid fa-search w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
        </div>
        <a href="{{ route('business.statistics') }}" class="w-full sm:w-auto sm:ml-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-[10px] md:text-sm font-black shadow-lg transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
            <i class="fa-solid fa-chart-line"></i> Thống kê <span class="sm:hidden lg:inline">tổng hợp</span>
        </a>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto p-4 sm:p-6">
        <!-- Pagination (Moved to top) -->
        <div class="mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="hidden md:block text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Hiển thị bản ghi {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} / {{ $users->total() }}
            </div>
            <div class="w-full md:w-auto flex justify-center">
                {{ $users->links() }}
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
            <div class="min-w-[800px]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-6 py-5 text-center w-16">#</th>
                            <th class="px-6 py-5">Nhân Viên</th>
                            <th class="px-6 py-5 text-center">Giao Dịch</th>
                            <th class="px-6 py-5 text-right">Doanh Thu</th>
                            <th class="px-6 py-5">Hoạt Động</th>
                            <th class="px-6 py-5 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 uppercase text-[12px] font-bold">
                        @forelse ($users as $index => $user)
                            <tr class="hover:bg-blue-50/50 transition-colors group">
                                <td class="px-6 py-4 text-center text-gray-300 font-mono">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center ring-4 ring-white shadow-lg text-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-slate-800 leading-tight">{{ $user->name }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5 tracking-tight">{{ $user->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black">
                                        {{ $user->sales_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-blue-600 font-mono">
                                    {{ number_format($user->total_revenue ?? 0, 0, ',', '.') }} <span class="text-[10px] text-gray-400">đ</span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 normal-case font-medium text-[11px]">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-end gap-1.5">
                                        <button wire:click="showDetail({{ $user->id }})"
                                            class="bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-black transition-all flex items-center gap-2 uppercase tracking-tighter">
                                            <i class="fa-solid fa-eye"></i> Xem nhanh
                                        </button>
                                        <a href="{{ route('business.detail', $user->id) }}"
                                            class="text-blue-600 hover:underline text-[9px] font-black uppercase tracking-tighter">
                                            Chi tiết <i class="fa-solid fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="fa-solid fa-briefcase text-5xl opacity-10"></i>
                                        <p class="font-black uppercase tracking-widest text-[10px]">Không tìm thấy dữ liệu kinh doanh.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailPopup && $selectedUser)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-transition.opacity>
            <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-3rem)] animate-[scaleIn_0.2s_ease-out]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold shadow-lg">
                            {{ substr($selectedUser->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800 uppercase leading-none">{{ $selectedUser->name }}</h2>
                            <p class="text-sm text-gray-500 mt-1 font-mono uppercase font-bold">{{ $selectedUser->phone }} - ID: {{ $selectedUser->id }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetail"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-times fa-lg"></i>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 px-4 sm:px-6 bg-white shrink-0 overflow-x-auto no-scrollbar">
                    <button wire:click="setTab('info')"
                        class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'info' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        <i class="fa-solid fa-user-circle mr-2"></i> Hồ sơ
                    </button>
                    <button wire:click="setTab('work')"
                        class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'work' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        <i class="fa-solid fa-clipboard-list mr-2"></i> NB
                    </button>
                    <button wire:click="setTab('invites')"
                        class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'invites' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        <i class="fa-solid fa-user-plus mr-2"></i> CTV
                    </button>
                    <button wire:click="setTab('sales')"
                        class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'sales' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        <i class="fa-solid fa-chart-line mr-2"></i> KQ
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-4 sm:p-6 overflow-y-auto bg-slate-50 flex-1">
                    @if ($activeTab === 'info')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 uppercase font-black text-[11px] sm:text-[12px]">
                            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                                <h3 class="text-blue-600 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-id-card"></i> Cơ bản
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between py-2 border-b border-gray-50">
                                        <span class="text-gray-400">Tên</span>
                                        <span class="text-slate-800 text-right truncate ml-4">{{ $selectedUser->name }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-50">
                                        <span class="text-gray-400">SĐT</span>
                                        <span class="text-slate-800 font-mono">{{ $selectedUser->phone }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-50">
                                        <span class="text-gray-400">Mã giới thiệu</span>
                                        <span class="text-blue-600 font-mono">{{ $selectedUser->invite_code ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-400">Gia nhập</span>
                                        <span class="text-slate-800">{{ $selectedUser->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                                <h3 class="text-blue-600 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-shield-halved"></i> Phân quyền & Giới tính
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-2">
                                        <span class="text-gray-400">Loại BĐS phụ trách:</span>
                                        <div class="flex flex-wrap gap-2">
                                            @php
                                                $types = \App\Livewire\RealEstateListing::PROPERTY_TYPES;
                                            @endphp
                                            @forelse ($selectedUser->property_types ?? [] as $typeId)
                                                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px]">
                                                    {{ $types[$typeId] ?? 'Unknown' }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 font-normal italic">Chưa được phân công</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($activeTab === 'work')
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden overflow-x-auto">
                            <div class="min-w-[600px]">
                                <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 text-[11px] uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Ngày</th>
                                        <th class="px-6 py-4">Khách hàng</th>
                                        <th class="px-6 py-4">Nội dung</th>
                                        <th class="px-6 py-4">Tiến độ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-[13px] font-bold uppercase">
                                    @forelse ($workLogs as $work)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-mono text-gray-500">{{ $work->work_date->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4">
                                                <div class="text-slate-800">{{ $work->customer->name ?? '-' }}</div>
                                                <div class="text-[10px] text-gray-400 font-mono">{{ $work->customer->phone ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 normal-case font-medium">{{ $work->content }}</td>
                                            <td class="px-6 py-4">
                                                <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded-md text-[10px]">
                                                    {{ $work->progress }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 normal-case font-normal">
                                                Chưa có lịch sử làm việc.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @elseif ($activeTab === 'invites')
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden overflow-x-auto">
                            <div class="min-w-[600px]">
                                <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 text-[11px] uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Người được mời</th>
                                        <th class="px-6 py-4">Số điện thoại</th>
                                        <th class="px-6 py-4">Mã đã dùng</th>
                                        <th class="px-6 py-4">Ngày tham gia</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-[13px] font-bold uppercase">
                                    @forelse ($inviteLogs as $log)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-slate-800">
                                                {{ $log->invitedUser->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 font-mono text-gray-500">
                                                {{ $log->invitedUser->phone ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 font-mono text-blue-600">
                                                {{ $log->inviter_code }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 normal-case font-normal">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 normal-case font-normal">
                                                Chưa mời được CTV nào.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @elseif ($activeTab === 'sales')
                        <!-- Filters -->
                        <div class="mb-4 sm:mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <h3 class="text-blue-600 font-black uppercase text-[10px] sm:text-xs flex items-center gap-2 w-full sm:w-auto">
                                <i class="fa-solid fa-filter"></i> Lọc Kết Quả
                            </h3>
                            <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto">
                                <select wire:model.live="filterYear" wire:change="loadTabData" class="flex-1 sm:flex-none border border-gray-200 rounded-xl px-3 py-1.5 text-[10px] sm:text-xs font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 uppercase">
                                    @for ($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                <select wire:model.live="filterQuarter" wire:change="loadTabData" class="flex-1 sm:flex-none border border-gray-200 rounded-xl px-3 py-1.5 text-[10px] sm:text-xs font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 uppercase">
                                    <option value="all">Tất cả</option>
                                    <option value="1">Q1</option>
                                    <option value="2">Q2</option>
                                    <option value="3">Q3</option>
                                    <option value="4">Q4</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 sm:mb-6 grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 uppercase font-black">
                            <div class="bg-blue-600 text-white p-4 sm:p-5 rounded-2xl shadow-lg relative overflow-hidden">
                                <div class="text-[9px] opacity-70 mb-1 uppercase tracking-tighter">Giá trị GD</div>
                                <div class="text-sm sm:text-lg font-mono truncate">{{ number_format($salesTotal / 1000000, 1) }}M</div>
                            </div>
                            <div class="bg-indigo-600 text-white p-4 sm:p-5 rounded-2xl shadow-lg relative overflow-hidden">
                                <div class="text-[9px] opacity-70 mb-1 uppercase tracking-tighter">Doanh thu</div>
                                <div class="text-sm sm:text-lg font-mono truncate">{{ number_format($revenueTotal / 1000000, 1) }}M</div>
                            </div>
                            <div class="bg-emerald-600 text-white p-4 sm:p-5 rounded-2xl shadow-lg relative overflow-hidden">
                                <div class="text-[9px] opacity-70 mb-1 uppercase tracking-tighter">Thưởng</div>
                                <div class="text-sm sm:text-lg font-mono truncate">{{ number_format($bonusTotal / 1000, 0) }}K</div>
                            </div>
                            <div class="bg-slate-800 text-white p-4 sm:p-5 rounded-2xl shadow-lg relative overflow-hidden">
                                <div class="text-[9px] opacity-70 mb-1 uppercase tracking-tighter">Hợp đồng</div>
                                <div class="text-sm sm:text-lg font-mono">{{ count($saleLogs) }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden overflow-x-auto">
                            <div class="min-w-[700px]">
                                <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 text-[11px] uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Sản phẩm/Dự án</th>
                                        <th class="px-6 py-4 text-right">Giá bán</th>
                                        <th class="px-6 py-4 text-right">Hoa hồng</th>
                                        <th class="px-6 py-4 text-right">Thưởng</th>
                                        <th class="px-6 py-4 text-right">Tổng nhận</th>
                                        <th class="px-6 py-4">Ngày bán</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-[12px] font-bold uppercase">
                                    @forelse ($saleLogs as $sale)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-slate-800">{{ $sale->project_name }}</div>
                                                <div class="text-[10px] text-gray-400 font-mono">{{ $sale->listing->code ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-slate-700">
                                                {{ number_format($sale->actual_price, 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-green-600">
                                                {{ number_format($sale->revenue_amount, 0, ',', '.') }} đ
                                                <div class="text-[10px] text-gray-400 font-normal">({{ $sale->revenue_percent }}%)</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-orange-500">
                                                {{ number_format($sale->bonus_amount ?? 0, 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-blue-600 bg-slate-50/50">
                                                {{ number_format(($sale->revenue_amount ?? 0) + ($sale->bonus_amount ?? 0), 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 normal-case font-normal">
                                                {{ $sale->sold_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 normal-case font-normal">
                                                Chưa có dữ liệu bán hàng.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
