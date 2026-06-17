@props([
    'name',               // wire:model của file mới (mảng nếu multiple, đơn nếu không)
    'images' => [],       // ảnh đã có (mảng URL/đường dẫn)
    'previews' => [],     // file vừa chọn (TemporaryUploadedFile, dạng mảng)
    'onRemove' => 'removeImage', // method Livewire xoá ảnh đã có theo index
    'multiple' => true,
    'label' => 'Kéo thả hoặc bấm để tải ảnh',
    'hint' => 'JPG, PNG · tối đa 4MB mỗi ảnh · ảnh đầu là ảnh bìa',
])

@once
<style>
    .iu { display: grid; gap: 10px; }
    .iu-drop { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; min-height: 92px; padding: 14px; border: 1.5px dashed #cbd0d6; border-radius: 12px; background: #fafbfc; color: #6b7280; cursor: pointer; text-align: center; transition: border-color .15s, background .15s; }
    .iu-drop:hover { border-color: #f4bf19; background: #fffdf5; }
    .iu-drop svg { width: 26px; height: 26px; color: #9aa2ab; }
    .iu-drop b { color: #374151; font-size: 13px; font-weight: 800; }
    .iu-drop small { font-size: 11px; color: #9aa2ab; }
    .iu-loading { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.82); border-radius: 12px; font-weight: 800; color: #374151; }
    .iu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: 8px; }
    .iu-single .iu-grid { grid-template-columns: repeat(auto-fill, 130px); }
    .iu-thumb { position: relative; aspect-ratio: 1 / 1; border-radius: 10px; overflow: hidden; border: 1px solid #e6e8ec; background: #f4f5f7; }
    .iu-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .iu-thumb button { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; display: grid; place-items: center; padding: 0; border: 0; border-radius: 50%; background: rgba(17,17,17,.72); color: #fff; font-size: 15px; line-height: 1; cursor: pointer; }
    .iu-thumb button:hover { background: #c62828; }
    .iu-new { position: absolute; left: 4px; bottom: 4px; padding: 2px 7px; border-radius: 999px; background: rgba(244,191,25,.95); color: #1a1300; font-size: 10px; font-weight: 800; }
    .iu-cover { position: absolute; left: 4px; top: 4px; padding: 2px 7px; border-radius: 999px; background: rgba(17,17,17,.78); color: #fff; font-size: 10px; font-weight: 800; }
</style>
@endonce

<div class="iu {{ $multiple ? '' : 'iu-single' }}">
    <label class="iu-drop">
        <input type="file" wire:model="{{ $name }}" accept="image/*" @if($multiple) multiple @endif hidden>
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 13v4a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-4h2v4h10v-4h2ZM11 5.83 8.41 8.41 7 7l5-5 5 5-1.41 1.41L13 5.83V15h-2V5.83Z"/></svg>
        <b>{{ $label }}</b>
        <small>{{ $hint }}</small>
        <div class="iu-loading" wire:loading wire:target="{{ $name }}">Đang tải ảnh...</div>
    </label>

    @if(count($images) || count($previews))
        <div class="iu-grid">
            @foreach($images as $index => $image)
                <div class="iu-thumb">
                    <img src="{{ \Illuminate\Support\Str::startsWith($image, 'http') ? $image : asset('storage/' . ltrim($image, '/')) }}" alt="Ảnh {{ $index + 1 }}">
                    @if($multiple && $loop->first)<span class="iu-cover">Ảnh bìa</span>@endif
                    <button type="button" wire:click="{{ $onRemove }}({{ $index }})" title="Xoá ảnh">&times;</button>
                </div>
            @endforeach
            @foreach($previews as $file)
                <div class="iu-thumb">
                    <img src="{{ $file->temporaryUrl() }}" alt="Ảnh mới">
                    <span class="iu-new">Mới</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
