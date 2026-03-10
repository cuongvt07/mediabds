<div class="h-full flex flex-col bg-slate-50">
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3 uppercase">
            <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <span>Thống kê CTV</span>
        </h1>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="filterYear" class="flex-1 md:flex-none bg-slate-50 border border-gray-100 rounded-2xl px-4 py-3 text-[10px] font-black text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500 uppercase tracking-widest transition-all">
                @for ($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endfor
            </select>
            <select wire:model.live="filterQuarter" class="flex-1 md:flex-none bg-slate-50 border border-gray-100 rounded-2xl px-4 py-3 text-[10px] font-black text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500 uppercase tracking-widest transition-all">
                <option value="all">Tất cả quý</option>
                <option value="1">Quý 1</option>
                <option value="2">Quý 2</option>
                <option value="3">Quý 3</option>
                <option value="4">Quý 4</option>
            </select>
        </div>
    </div>

    <div class="flex-1 overflow-auto p-6 space-y-8">
        <!-- Overview Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white p-6 md:p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-blue-500/5 transition-all">
                <div class="absolute -right-4 -bottom-4 text-blue-500/5 text-7xl font-black italic group-hover:scale-110 transition-transform">rev</div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Doanh thu</p>
                <h3 class="text-2xl md:text-4xl font-mono font-black text-slate-800 truncate">{{ number_format($overallStats['total_revenue'] / 1000000, 1) }}<span class="text-xs font-sans ml-1 text-slate-400 uppercase">Tr.vnđ</span></h3>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-emerald-500/5 transition-all">
                <div class="absolute -right-4 -bottom-4 text-emerald-500/5 text-7xl font-black italic group-hover:scale-110 transition-transform">bn</div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2 text-emerald-500/50">Tiền thưởng</p>
                <h3 class="text-2xl md:text-4xl font-mono font-black text-slate-800 truncate">{{ number_format($overallStats['total_bonus'] / 1000000, 1) }}<span class="text-xs font-sans ml-1 text-slate-400 uppercase">Tr.vnđ</span></h3>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-orange-500/5 transition-all">
                <div class="absolute -right-4 -bottom-4 text-orange-500/5 text-7xl font-black italic group-hover:scale-110 transition-transform">deal</div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Giao dịch</p>
                <h3 class="text-2xl md:text-4xl font-mono font-black text-slate-800">{{ number_format($overallStats['total_deals']) }}</h3>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-[32px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
                <div class="absolute -right-4 -bottom-4 text-indigo-500/5 text-7xl font-black italic group-hover:scale-110 transition-transform">new</div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">CTV mới</p>
                <h3 class="text-2xl md:text-4xl font-mono font-black text-slate-800">{{ number_format($overallStats['new_ctvs']) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Ranking -->
            <div class="lg:col-span-1 bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Bảng vàng CTV</h3>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-[10px] font-black uppercase rounded-full">Top 10</span>
                </div>
                <div class="flex-1 overflow-auto">
                    <div class="divide-y divide-gray-50">
                        @foreach($topCtvs as $index => $ctv)
                        <div class="px-8 py-5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <span class="w-8 font-mono font-black {{ $index < 3 ? 'text-indigo-600' : 'text-gray-300' }}">0{{ $index + 1 }}</span>
                                <div>
                                    <div class="text-sm font-black text-slate-700 uppercase tracking-tight">{{ $ctv->name }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">{{ $ctv->rank->name ?? 'Người mới' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-mono font-black text-indigo-600">{{ number_format($ctv->revenue / 1000000, 1) }}M</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Placeholder for Chart -->
            <div class="lg:col-span-2 bg-white rounded-[40px] border border-gray-100 shadow-sm p-10 flex flex-col items-center justify-center min-h-[400px]">
                <div class="text-center">
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-400 rounded-3xl flex items-center justify-center text-4xl mb-6 mx-auto">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 uppercase mb-2">Biểu đồ tăng trưởng</h3>
                    <p class="text-gray-400 text-sm max-w-sm">Tính năng biểu đồ đang được cập nhật. Bạn có thể xem số liệu thô trong bảng thống kê bên trái.</p>
                </div>
            </div>
        </div>
    </div>
</div>
