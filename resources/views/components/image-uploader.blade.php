@props([
    'name',                 // tên property file của Livewire (vd "imageFiles" / "avatarFile")
    'images' => [],         // ảnh đã lưu (mảng URL) — chế độ sửa
    'previews' => [],       // (không dùng nữa — preview ở client) giữ để tương thích
    'onRemove' => 'removeImage', // method Livewire xoá ảnh đã lưu theo index
    'multiple' => true,
    'label' => 'Kéo thả hoặc bấm để tải ảnh',
    'hint' => 'JPG, PNG · tối đa 2MB · ảnh được nén tự động để tải nhanh',
    'maxKB' => 2048,        // ngưỡng báo "vượt quá" (KB) trên ảnh GỐC
])

@once
<style>
    [x-cloak] { display: none !important; }
    .iu { display: grid; gap: 10px; }
    .iu-drop { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; min-height: 92px; padding: 14px; border: 1.5px dashed #cbd0d6; border-radius: 12px; background: #fafbfc; color: #6b7280; cursor: pointer; text-align: center; transition: border-color .15s, background .15s; }
    .iu-drop:hover { border-color: #f4bf19; background: #fffdf5; }
    .iu-drop svg { width: 26px; height: 26px; color: #9aa2ab; }
    .iu-drop b { color: #374151; font-size: 13px; font-weight: 800; }
    .iu-drop small { font-size: 11px; color: #9aa2ab; }
    .iu-loading { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.86); border-radius: 12px; font-weight: 800; color: #374151; }
    .iu-error { display: grid; gap: 3px; margin: 0; color: #c0392b; font-size: 12px; font-weight: 600; }
    .iu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: 8px; }
    .iu-single .iu-grid { grid-template-columns: repeat(auto-fill, 130px); }
    .iu-thumb { position: relative; aspect-ratio: 1 / 1; border-radius: 10px; overflow: hidden; border: 1px solid #e6e8ec; background: #f4f5f7; }
    .iu-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .iu-thumb button { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; display: grid; place-items: center; padding: 0; border: 0; border-radius: 50%; background: rgba(17,17,17,.72); color: #fff; font-size: 15px; line-height: 1; cursor: pointer; }
    .iu-thumb button:hover { background: #c62828; }
    .iu-new { position: absolute; left: 4px; bottom: 4px; padding: 2px 7px; border-radius: 999px; background: rgba(244,191,25,.95); color: #1a1300; font-size: 10px; font-weight: 800; }
    .iu-cover { position: absolute; left: 4px; top: 4px; padding: 2px 7px; border-radius: 999px; background: rgba(17,17,17,.78); color: #fff; font-size: 10px; font-weight: 800; }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageUploader', (opts) => ({
            maxKB: opts.maxKB, multiple: opts.multiple, model: opts.model,
            files: [], previews: [], errors: [], uploading: false, progress: 0,

            onChange(e) {
                this.errors = [];
                const selected = Array.from(e.target.files || []);
                e.target.value = '';
                const accepted = [];
                for (const f of selected) {
                    if (f.size > this.maxKB * 1024) {
                        this.errors.push(`"${f.name}" ${(f.size / 1048576).toFixed(1)}MB vượt quá ${(this.maxKB / 1024).toFixed(0)}MB`);
                        continue;
                    }
                    accepted.push(f);
                }
                if (!accepted.length) return;
                Promise.all(accepted.map(f => this.compress(f))).then(done => {
                    if (this.multiple) { this.files.push(...done); }
                    else { this.files = done.slice(0, 1); }
                    this.rebuildPreviews();
                    this.sync();
                });
            },

            rebuildPreviews() {
                this.previews.forEach(p => URL.revokeObjectURL(p.url));
                this.previews = this.files.map(f => ({ url: URL.createObjectURL(f) }));
            },

            removePreview(i) {
                this.files.splice(i, 1);
                this.rebuildPreviews();
                this.sync();
            },

            sync() {
                this.uploading = true; this.progress = 0;
                const done = () => { this.uploading = false; };
                const prog = (ev) => { this.progress = (ev.detail && ev.detail.progress) || 0; };
                if (this.multiple) {
                    if (this.files.length) this.$wire.uploadMultiple(this.model, this.files, done, done, prog);
                    else { this.$wire.set(this.model, []); done(); }
                } else {
                    if (this.files[0]) this.$wire.upload(this.model, this.files[0], done, done, prog);
                    else { this.$wire.set(this.model, null); done(); }
                }
            },

            compress(file) {
                return new Promise(resolve => {
                    if (!file.type || !file.type.startsWith('image/')) return resolve(file);
                    const url = URL.createObjectURL(file);
                    const img = new Image();
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                        const max = 1600;
                        let w = img.width, h = img.height;
                        if (w > max || h > max) {
                            if (w >= h) { h = Math.round(h * max / w); w = max; }
                            else { w = Math.round(w * max / h); h = max; }
                        }
                        try {
                            const c = document.createElement('canvas');
                            c.width = w; c.height = h;
                            c.getContext('2d').drawImage(img, 0, 0, w, h);
                            c.toBlob(blob => {
                                if (!blob || blob.size >= file.size) return resolve(file);
                                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                            }, 'image/jpeg', 0.82);
                        } catch (err) { resolve(file); }
                    };
                    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
                    img.src = url;
                });
            },
        }));
    });
</script>
@endonce

<div class="iu {{ $multiple ? '' : 'iu-single' }}"
     x-data="imageUploader({ maxKB: {{ (int) $maxKB }}, multiple: {{ $multiple ? 'true' : 'false' }}, model: '{{ $name }}' })">
    <label class="iu-drop">
        <input type="file" accept="image/*" @if($multiple) multiple @endif hidden x-on:change="onChange($event)">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 13v4a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-4h2v4h10v-4h2ZM11 5.83 8.41 8.41 7 7l5-5 5 5-1.41 1.41L13 5.83V15h-2V5.83Z"/></svg>
        <b>{{ $label }}</b>
        <small>{{ $hint }}</small>
        <div class="iu-loading" x-show="uploading" x-cloak>Đang tải ảnh... <span x-text="progress + '%'"></span></div>
    </label>

    <p class="iu-error" x-show="errors.length" x-cloak>
        <template x-for="(er, i) in errors" :key="i"><span x-text="er"></span></template>
    </p>

    <div class="iu-grid">
        @foreach($images as $index => $image)
            <div class="iu-thumb" @if(! $multiple) x-show="previews.length === 0" x-cloak @endif>
                <img src="{{ \Illuminate\Support\Str::startsWith($image, 'http') ? $image : asset('storage/' . ltrim($image, '/')) }}" alt="Ảnh {{ $index + 1 }}">
                @if($multiple && $loop->first)<span class="iu-cover">Ảnh bìa</span>@endif
                <button type="button" wire:click="{{ $onRemove }}({{ $index }})" title="Xoá ảnh">&times;</button>
            </div>
        @endforeach

        <template x-for="(p, i) in previews" :key="p.url">
            <div class="iu-thumb">
                <img :src="p.url" alt="Ảnh mới">
                <span class="iu-new">Mới</span>
                <button type="button" x-on:click="removePreview(i)" title="Bỏ ảnh">&times;</button>
            </div>
        </template>
    </div>
</div>
