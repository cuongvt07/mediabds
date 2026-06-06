@props(['title', 'rows', 'empty' => 'Chưa có dữ liệu.'])

<div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">{{ $title }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-5 py-4">ID</th>
                    <th class="px-5 py-4">Tên/Tiêu đề</th>
                    <th class="px-5 py-4">Thông tin</th>
                    <th class="px-5 py-4">Trạng thái</th>
                    <th class="px-5 py-4">Ngày tạo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $row->id ?? '-' }}</td>
                        <td class="px-5 py-3 font-bold text-slate-800">
                            {{ $row->title ?? $row->name ?? $row->label ?? 'Bản ghi website' }}
                        </td>
                        <td class="px-5 py-3 text-slate-500">
                            {{ $row->phone ?? $row->slug ?? $row->message ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black uppercase text-slate-500">
                                {{ $row->status ?? $row->source ?? 'active' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $row->created_at ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ $empty }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
