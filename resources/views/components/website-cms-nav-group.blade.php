@props(['title', 'items', 'activeTab'])

<div class="mb-6">
    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-500">{{ $title }}</p>
    <div class="space-y-1">
        @foreach ($items as $tab => $meta)
            <a href="{{ route('website.admin', ['tab' => $tab]) }}"
                class="group flex items-center gap-3 rounded-2xl px-3 py-3 transition {{ $activeTab === $tab ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <span class="grid h-9 w-9 place-items-center rounded-xl {{ $activeTab === $tab ? 'bg-white/15 text-white' : 'bg-slate-900 text-slate-400 group-hover:text-emerald-300' }}">
                    <i class="fa-solid {{ $meta[1] }}"></i>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-black">{{ $meta[0] }}</span>
                    <span class="mt-0.5 block truncate text-[11px] {{ $activeTab === $tab ? 'text-emerald-50' : 'text-slate-500 group-hover:text-slate-400' }}">{{ $meta[2] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
