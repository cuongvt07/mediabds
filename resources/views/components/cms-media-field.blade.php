@props(['label', 'value' => '', 'target', 'full' => true])

<div class="cms-field {{ $full ? 'full' : '' }}">
    <span class="cms-label">{{ $label }}</span>
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        @if (!empty($value))
            <img src="{{ $value }}" alt="{{ $label }}" style="height:38px; max-width:150px; object-fit:contain; border:1px solid var(--border); background:var(--bg-raised); padding:3px;">
        @else
            <span style="color:var(--text-muted); font-size:11px;">Chưa chọn ảnh</span>
        @endif
        <button type="button" class="cms-btn" wire:click="openMediaPicker('{{ $target }}')"><i class="fa-solid fa-image"></i> Chọn / Tải ảnh</button>
        @if (!empty($value))
            <button type="button" class="cms-btn danger" wire:click="clearMedia('{{ $target }}')">Xóa</button>
        @endif
    </div>
</div>
