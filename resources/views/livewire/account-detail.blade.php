<div class="min-h-screen bg-slate-50 relative overflow-hidden">
    {{-- Decor --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] -mr-64 -mt-64"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-[120px] -ml-64 -mb-64"></div>

    {{-- Content --}}
    <div class="relative z-10 p-4 md:p-8 space-y-8">
        
        {{-- Header & Identity --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 bg-white/40 backdrop-blur-md p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-[2rem] bg-gradient-to-tr from-blue-600 to-indigo-700 text-white flex items-center justify-center text-3xl font-black shadow-2xl ring-4 ring-white">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-1">{{ $user->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest border border-blue-200">
                            {{ $user->phone }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest border border-slate-200">
                            Mã: {{ $user->invite_code ?: 'ROOT' }}
                        </span>
                        @if($user->rank)
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest border border-amber-200 flex items-center gap-1">
                                <i class="fa-solid fa-crown text-[8px]"></i> {{ $user->rank->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('account-management') }}" class="px-6 py-3 rounded-2xl bg-white text-slate-600 font-bold text-sm shadow-sm hover:shadow-md transition-all border border-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> QUAY QUAY LẠI
                </a>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 group-hover:text-blue-500 transition-colors">TỔNG DOANH THU (CÁ NHÂN)</p>
                <p class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($user->total_revenue, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 ml-1">VNĐ</span></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 group-hover:text-emerald-500 transition-colors">DỰ ÁN ĐÃ THAM GIA</p>
                <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $transactions->total() }} <span class="text-xs font-normal text-slate-400 ml-1">DEALS</span></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 group-hover:text-orange-500 transition-colors">SỐ CTV GIỚI THIỆU</p>
                <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $user->invitees_count }} <span class="text-xs font-normal text-slate-400 ml-1">ACCOUNTS</span></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-500 transition-colors">LƯỢT DÙNG (INVITE)</p>
                <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $user->sent_invite_logs_count }} <span class="text-xs font-normal text-slate-400 ml-1">USED</span></p>
            </div>
        </div>

        {{-- Main Panels --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Left: Transaction History --}}
            <div class="lg:col-span-8 space-y-6">
                <div class="flex items-center gap-3 ml-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-lg shadow-blue-200/50">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Lịch Sử Giao Dịch Dự Án</h3>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden shadow-slate-200/40">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-5">Ngày Bán</th>
                                    <th class="px-6 py-5">Thông Tin Dự Án</th>
                                    <th class="px-6 py-5 text-right">Giá Trị Deal</th>
                                    <th class="px-6 py-5 text-right bg-blue-50/30">Hoa Hồng Chia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($transactions as $tx)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-slate-700 leading-none mb-1">
                                            {{ \Carbon\Carbon::parse($tx->sold_at)->format('d/m/Y') }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">
                                            {{ \Carbon\Carbon::parse($tx->sold_at)->diffForHumans() }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-black text-slate-800 leading-tight mb-0.5 line-clamp-1 group-hover:text-blue-600 transition-colors">
                                            {{ $tx->listing_title }}
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                            Dự án: {{ $tx->project_name ?: 'N/A' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <p class="text-sm font-black text-slate-800">
                                            {{ number_format($tx->actual_price, 0, ',', '.') }} đ
                                        </p>
                                        <p class="text-[10px] font-bold text-emerald-500 uppercase">
                                            Tổng nhận: {{ number_format($tx->revenue_amount, 0, ',', '.') }} đ
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right bg-blue-50/10">
                                        <p class="text-base font-black text-blue-600">
                                            {{ number_format($tx->received_amount, 0, ',', '.') }} đ
                                        </p>
                                        <p class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">
                                            Thực nhận
                                        </p>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                                                <i class="fa-solid fa-box-open text-4xl"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold">Chưa tham gia dự án nào.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 mt-auto">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>

            {{-- Right: Referrals --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="flex items-center gap-3 ml-2">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shadow-lg shadow-orange-200/50">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">CTV Được Giới Thiệu</h3>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden shadow-slate-200/40 p-1">
                    <div class="space-y-1">
                        @forelse($referrals as $ref)
                        <div class="flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-100 to-slate-200 text-slate-500 flex items-center justify-center font-black text-xs group-hover:from-blue-500 group-hover:to-blue-600 group-hover:text-white transition-all shadow-sm">
                                {{ substr($ref->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-800 truncate">{{ $ref->name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 font-mono tracking-wider">{{ $ref->phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Tham gia</p>
                                <p class="text-[11px] font-bold text-slate-600">{{ $ref->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="p-12 text-center text-slate-300">
                             <p class="text-xs font-black uppercase tracking-[0.2em]">Chưa có giới thiệu</p>
                        </div>
                        @endforelse
                    </div>
                    <div class="p-4 border-t border-slate-50">
                        {{ $referrals->links() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
