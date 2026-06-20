<div class="flex-1 overflow-y-auto p-6 lg:p-10">
    <div class="mx-auto max-w-6xl space-y-6">
        <div><h1 class="text-2xl font-black text-slate-900">License Extension</h1><p class="mt-1 text-sm text-slate-500">Tạo license, giới hạn thiết bị và thu hồi quyền truy cập API.</p></div>

        @if ($newLicenseKey)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5">
                <b class="text-amber-900">License mới — chỉ hiển thị một lần:</b>
                <div class="mt-2 select-all break-all rounded-xl bg-white p-3 font-mono text-sm">{{ $newLicenseKey }}</div>
                <button wire:click="$set('newLicenseKey', null)" class="mt-3 text-sm font-bold text-amber-800">Đã sao chép, đóng</button>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-black">Tạo license</h2>
            <div class="grid gap-4 md:grid-cols-[1fr_140px_220px_auto] md:items-end">
                <label><span class="mb-1 block text-sm font-bold">Tên khách hàng</span><input wire:model="label" class="w-full rounded-xl border-slate-300"></label>
                <label><span class="mb-1 block text-sm font-bold">Số thiết bị</span><input type="number" wire:model="maxDevices" class="w-full rounded-xl border-slate-300"></label>
                <label><span class="mb-1 block text-sm font-bold">Hết hạn</span><input type="datetime-local" wire:model="expiresAt" class="w-full rounded-xl border-slate-300"></label>
                <button wire:click="createLicense" class="rounded-xl bg-blue-600 px-5 py-2.5 font-bold text-white">Tạo license</button>
            </div>
            @error('label')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </section>

        @foreach ($licenses as $license)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="font-black text-slate-900">{{ $license->label }}</h2><p class="font-mono text-xs text-slate-500">{{ $license->key_hint }}</p></div>
                    <button wire:click="toggleLicense({{ $license->id }})" class="rounded-lg px-3 py-2 text-sm font-bold {{ $license->active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $license->active ? 'Đang bật' : 'Đã tắt' }}</button>
                </div>
                <div class="mt-3 text-sm text-slate-500">Thiết bị: {{ $license->devices->whereNull('revoked_at')->count() }}/{{ $license->max_devices }} · Hết hạn: {{ $license->expires_at?->format('d/m/Y H:i') ?? 'Không giới hạn' }}</div>
                <div class="mt-4 space-y-2">
                    @forelse ($license->devices as $device)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 text-sm">
                            <div><b>{{ $device->device_name ?: 'Thiết bị '.$device->id }}</b><div class="text-xs text-slate-500">Hoạt động: {{ $device->last_seen_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
                            @if (!$device->revoked_at)<button wire:click="revokeDevice({{ $device->id }})" wire:confirm="Thu hồi thiết bị này?" class="font-bold text-red-600">Thu hồi</button>@else<span class="font-bold text-slate-400">Đã thu hồi</span>@endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Chưa có thiết bị kích hoạt.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
