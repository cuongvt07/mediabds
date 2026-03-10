<div class="h-full flex flex-col bg-slate-50 relative">
    <!-- Header -->
    <div class="bg-white border-b border-gray-100 px-4 md:px-6 py-4 flex items-center justify-between shrink-0 gap-4">
        <h1 class="text-lg md:text-xl font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <span>Kinh Doanh</span>
        </h1>
        <a href="{{ route('business.statistics') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-[10px] md:text-sm font-black shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2 uppercase tracking-widest active:scale-95">
            <i class="fa-solid fa-chart-line"></i> <span class="hidden sm:inline">Thống kê tổng hợp</span> <span class="sm:hidden">Thống kê</span>
        </a>
    </div>

    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-100 px-4 md:px-6 py-3 flex flex-col md:flex-row items-stretch md:items-center gap-3 shrink-0">
        <div class="relative flex-1 max-w-none md:max-w-sm">
            <input type="text" placeholder="Tìm kiếm nhân viên..." wire:model.live.debounce.300ms="search"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm shadow-sm transition-all font-bold text-slate-700">
            <i class="fa-solid fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs"></i>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-auto p-4 md:p-6 custom-scrollbar">
        <!-- Desktop Table View -->
        <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-6 py-5 text-center w-16">#</th>
                        <th class="px-6 py-5">Nhân Viên</th>
                        <th class="px-6 py-5 text-center">Giao Dịch</th>
                        <th class="px-6 py-5 text-right">Doanh Thu</th>
                        <th class="px-6 py-5">Hoạt Động</th>
                        <th class="px-6 py-5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 uppercase text-[12px] font-bold">
                    @forelse ($users as $index => $user)
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" wire:click="showDetail({{ $user->id }})">
                            <td class="px-6 py-4 text-center text-slate-300 font-mono">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center ring-4 ring-white shadow-lg text-sm shrink-0">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-slate-800 leading-tight truncate">{{ $user->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5 tracking-tight">{{ $user->phone }}</div>
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
                            <td class="px-6 py-4 text-slate-400 normal-case font-medium text-[11px]">
                                {{ $user->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    <button wire:click.stop="showDetail({{ $user->id }})"
                                        class="bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-black transition-all flex items-center gap-2 uppercase tracking-tighter">
                                        <i class="fa-solid fa-bolt"></i> Xem nhanh
                                    </button>
                                    <a href="{{ route('business.detail', $user->id) }}"
                                        class="text-blue-600 hover:underline text-[9px] font-black uppercase tracking-tighter">
                                        Hồ sơ đầy đủ <i class="fa-solid fa-arrow-right-long ml-1"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-300">
                                <div class="flex flex-col items-center gap-3 italic">
                                    <i class="fa-solid fa-briefcase text-4xl opacity-10 mb-2"></i>
                                    <p class="font-bold uppercase tracking-widest text-[10px]">Trống dữ liệu</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden flex flex-col gap-4">
            @forelse ($users as $user)
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm relative active:bg-gray-50 transition-all group"
                     wire:click="showDetail({{ $user->id }})">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg text-lg font-black shrink-0 ring-2 ring-white">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-slate-800 text-base leading-tight truncate uppercase tracking-tight">{{ $user->name }}</h3>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400 font-bold">
                                <span class="font-mono text-blue-500">{{ $user->phone }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[9px] text-slate-400 font-black uppercase mb-0.5">Doanh Thu</div>
                            <div class="text-xs font-black text-blue-600 font-mono">
                                {{ number_format(($user->total_revenue ?? 0) / 1000000, 1) }}M
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-50">
                        <div class="flex items-center gap-2">
                             <div class="bg-blue-50 text-blue-600 px-2 py-1 rounded-lg text-[9px] font-black uppercase">
                                {{ $user->sales_count ?? 0 }} Giao dịch
                            </div>
                        </div>
                        <div class="text-right">
                             <a href="{{ route('business.detail', $user->id) }}" @click.stop
                                class="inline-flex items-center gap-1.5 text-blue-600 text-[10px] font-black uppercase hover:underline">
                                Xem chi tiết <i class="fa-solid fa-chevron-right text-[8px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-12 text-center text-slate-300 italic border border-gray-100">
                    <i class="fa-solid fa-briefcase text-4xl mb-4 block opacity-10"></i>
                    Chưa có đối tác kinh doanh.
                </div>
            @endforelse
        </div>
    </div>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailPopup && $selectedUser)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-end md:items-center justify-center z-50 p-0 md:p-4 transition-all" x-transition.opacity>
            <div class="bg-white w-full max-w-5xl rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[85vh] animate-[slideUp_0.3s_ease-out]">
                <!-- Modal Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-white md:bg-gray-50 rounded-t-3xl">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-700 text-white flex items-center justify-center text-xl font-black shadow-lg ring-4 ring-white shrink-0">
                            {{ substr($selectedUser->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-lg md:text-xl font-black text-slate-800 uppercase tracking-tight leading-none">{{ $selectedUser->name }}</h2>
                            <p class="text-[10px] md:text-xs text-slate-400 mt-1.5 font-bold uppercase tracking-widest">
                                <span class="text-blue-600 font-mono">{{ $selectedUser->phone }}</span> • ID: {{ $selectedUser->id }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeDetail"
                        class="text-slate-300 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-xl hover:bg-red-50 transition-colors">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-100 px-4 md:px-6 bg-white shrink-0 overflow-x-auto no-scrollbar">
                    <button wire:click="setTab('info')"
                        class="px-5 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'info' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-300 hover:text-slate-500' }}">
                        Hồ sơ
                    </button>
                    <button wire:click="setTab('work')"
                        class="px-5 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'work' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-300 hover:text-slate-500' }}">
                        Nhật ký
                    </button>
                    <button wire:click="setTab('invites')"
                        class="px-5 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'invites' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-300 hover:text-slate-500' }}">
                        CTV
                    </button>
                    <button wire:click="setTab('sales')"
                        class="px-5 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'sales' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-300 hover:text-slate-500' }}">
                        Kết quả
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-5 md:p-8 overflow-y-auto bg-slate-50/50 flex-1 custom-scrollbar">
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
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <!-- Desktop Table -->
                            <table class="hidden md:table w-full text-left border-collapse">
                                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                                    <tr>
                                        <th class="px-6 py-5">Ngày</th>
                                        <th class="px-6 py-5">Khách hàng</th>
                                        <th class="px-6 py-5">Nội dung</th>
                                        <th class="px-6 py-5">Tiến độ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-[12px] font-bold uppercase">
                                    @forelse ($workLogs as $work)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-mono text-slate-400 leading-tight">
                                                {{ $work->work_date->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-slate-800 leading-tight">{{ $work->customer->name ?? '-' }}</div>
                                                <div class="text-[9px] text-slate-400 font-mono mt-0.5 tracking-tight uppercase">{{ $work->customer->phone ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 normal-case font-medium leading-relaxed">{{ $work->content }}</td>
                                            <td class="px-6 py-4">
                                                <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                    {{ $work->progress }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-300 italic">
                                                Chưa có lịch sử làm việc.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Mobile View -->
                            <div class="md:hidden divide-y divide-gray-50">
                                @forelse ($workLogs as $work)
                                    <div class="p-4 bg-white">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $work->work_date->format('d/m/Y') }}</span>
                                            <span class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase">{{ $work->progress }}%</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-[9px] text-slate-300 font-black uppercase block mb-0.5">Khách hàng</span>
                                            <p class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ $work->customer->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-[9px] text-slate-300 font-black uppercase block mb-0.5">Nội dung</span>
                                            <p class="text-xs font-medium text-slate-600 leading-relaxed">{{ $work->content }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-slate-300 font-bold uppercase tracking-widest text-[10px]">Trống</div>
                                @endforelse
                            </div>
                        </div>
                    @elseif ($activeTab === 'invites')
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <!-- Desktop Table -->
                            <table class="hidden md:table w-full text-left border-collapse">
                                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                                    <tr>
                                        <th class="px-6 py-5">Người được mời</th>
                                        <th class="px-6 py-5">Số điện thoại</th>
                                        <th class="px-6 py-5">Mã đã dùng</th>
                                        <th class="px-6 py-5">Ngày tham gia</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-[12px] font-bold uppercase">
                                    @forelse ($inviteLogs as $log)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-slate-800">
                                                {{ $log->invitedUser->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 font-mono text-slate-400">
                                                {{ $log->invitedUser->phone ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 font-mono text-blue-600">
                                                {{ $log->inviter_code }}
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 normal-case font-medium">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-300 italic">
                                                Chưa mời được CTV nào.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Mobile View -->
                            <div class="md:hidden divide-y divide-gray-50">
                                @forelse ($inviteLogs as $log)
                                    <div class="p-4 flex items-center justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-[12px] font-black text-slate-800 uppercase truncate mb-1">{{ $log->invitedUser->name ?? 'N/A' }}</p>
                                            <p class="text-[10px] font-mono text-slate-400">{{ $log->invitedUser->phone ?? 'N/A' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-[9px] text-slate-300 font-black uppercase mb-1">Mã dùng</p>
                                            <p class="text-[10px] font-mono text-blue-600 font-black">{{ $log->inviter_code }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-slate-300 font-bold uppercase tracking-widest text-[10px]">Trống</div>
                                @endforelse
                            </div>
                        </div>
                    @elseif ($activeTab === 'sales')
                        <!-- Filters -->
                        <div class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                             <div class="flex items-center gap-3 w-full md:w-auto">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-filter"></i>
                                </div>
                                <h3 class="text-slate-800 font-black uppercase text-[10px] tracking-widest">Lọc kết quả</h3>
                            </div>
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <select wire:model.live="filterYear" wire:change="loadTabData" class="flex-1 md:flex-none border border-gray-200 rounded-xl px-4 py-2 text-[10px] md:text-xs font-black text-slate-700 outline-none bg-slate-50 uppercase tracking-widest focus:ring-2 focus:ring-blue-500">
                                    @for ($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                <select wire:model.live="filterQuarter" wire:change="loadTabData" class="flex-1 md:flex-none border border-gray-200 rounded-xl px-4 py-2 text-[10px] md:text-xs font-black text-slate-700 outline-none bg-slate-50 uppercase tracking-widest focus:ring-2 focus:ring-blue-500">
                                    <option value="all">Tất cả quý</option>
                                    <option value="1">Quý 1</option>
                                    <option value="2">Quý 2</option>
                                    <option value="3">Quý 3</option>
                                    <option value="4">Quý 4</option>
                                </select>
                            </div>
                        </div>

                        <!-- Stat Cards -->
                        <div class="mb-8 grid grid-cols-2 lg:grid-cols-4 gap-4 uppercase font-black">
                            <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-hand-holding-dollar text-7xl"></i>
                                </div>
                                <div class="text-[9px] opacity-70 mb-2 uppercase tracking-widest">Giá trị GD</div>
                                <div class="text-xl font-black font-mono leading-none">{{ number_format($salesTotal / 1000000, 1) }}M</div>
                            </div>
                            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-chart-line text-7xl"></i>
                                </div>
                                <div class="text-[9px] opacity-70 mb-2 uppercase tracking-widest">Doanh thu</div>
                                <div class="text-xl font-black font-mono leading-none">{{ number_format($revenueTotal / 1000000, 1) }}M</div>
                            </div>
                            <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-gift text-7xl"></i>
                                </div>
                                <div class="text-[9px] opacity-70 mb-2 uppercase tracking-widest">Thưởng</div>
                                <div class="text-xl font-black font-mono leading-none">{{ number_format($bonusTotal / 1000, 0) }}K</div>
                            </div>
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 text-white p-5 rounded-3xl shadow-lg relative overflow-hidden group">
                                <div class="text-[9px] opacity-70 mb-2 uppercase tracking-widest">Số Hợp đồng</div>
                                <div class="text-xl font-black font-mono leading-none">{{ count($saleLogs) }}</div>
                            </div>
                        </div>

                        <!-- Sales Table -->
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <!-- Desktop Table -->
                            <table class="hidden md:table w-full text-left border-collapse">
                                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                                    <tr>
                                        <th class="px-6 py-5">Sản phẩm/Dự án</th>
                                        <th class="px-6 py-5 text-right">Giá bán</th>
                                        <th class="px-6 py-5 text-right">Hoa hồng</th>
                                        <th class="px-6 py-5 text-right">Thưởng</th>
                                        <th class="px-6 py-5 text-right">Tổng nhận</th>
                                        <th class="px-6 py-5">Ngày bán</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-[11px] font-bold uppercase">
                                    @forelse ($saleLogs as $sale)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-slate-800 leading-tight">{{ $sale->project_name }}</div>
                                                <div class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $sale->listing->code ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-slate-700">
                                                {{ number_format($sale->actual_price, 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-emerald-600">
                                                {{ number_format($sale->revenue_amount, 0, ',', '.') }} đ
                                                <div class="text-[9px] text-slate-400 font-normal">({{ $sale->revenue_percent }}%)</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-orange-500">
                                                {{ number_format($sale->bonus_amount ?? 0, 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-blue-600 bg-blue-50/30">
                                                {{ number_format(($sale->revenue_amount ?? 0) + ($sale->bonus_amount ?? 0), 0, ',', '.') }} đ
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 normal-case font-medium">
                                                {{ $sale->sold_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-slate-300 italic uppercase tracking-widest text-[10px]">
                                                Chưa có giao dịch.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Mobile View -->
                            <div class="md:hidden divide-y divide-gray-50">
                                @forelse ($saleLogs as $sale)
                                    <div class="p-4 bg-white">
                                        <div class="flex items-center justify-between gap-4 mb-3">
                                            <div class="min-w-0">
                                                <p class="text-[12px] font-black text-slate-800 uppercase truncate leading-tight">{{ $sale->project_name }}</p>
                                                <p class="text-[9px] font-mono text-slate-400 mt-1 uppercase">{{ $sale->listing->code ?? 'N/A' }}</p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="text-[10px] font-black text-blue-600 font-mono text-lg">
                                                    {{ number_format((($sale->revenue_amount ?? 0) + ($sale->bonus_amount ?? 0)) / 1000, 0) }}K
                                                </p>
                                                <p class="text-[8px] text-slate-400 font-black uppercase">Thực nhận</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-3">
                                            <div>
                                                <p class="text-[8px] text-slate-400 font-black uppercase mb-1">Giá bán</p>
                                                <p class="text-[10px] font-black text-slate-700 font-mono">
                                                    {{ number_format($sale->actual_price / 1000000, 1) }}M
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[8px] text-slate-400 font-black uppercase mb-1">Ngày bán</p>
                                                <p class="text-[10px] font-black text-slate-700 lowercase font-mono">
                                                    {{ $sale->sold_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-slate-300 font-black uppercase tracking-widest text-[10px]">Trống</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
