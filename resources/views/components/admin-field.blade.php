@props(['label', 'model', 'type' => 'text', 'disabled' => false])

<div>
    <label class="mb-1 block text-xs font-black uppercase tracking-widest text-slate-500">{{ $label }}</label>
    <input type="{{ $type }}" wire:model="{{ $model }}" @disabled($disabled)
        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold outline-none focus:border-emerald-500 disabled:bg-slate-100 disabled:text-slate-400">
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
