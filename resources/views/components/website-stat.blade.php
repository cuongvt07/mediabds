@props(['label', 'value' => 0, 'icon' => 'fa-circle'])

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-black text-slate-800">{{ number_format((int) $value) }}</p>
        </div>
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
            <i class="fa-solid {{ $icon }}"></i>
        </div>
    </div>
</div>
