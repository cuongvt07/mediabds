<div class="flex-1 overflow-y-auto p-6 lg:p-10">
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Quản lý License Extension</h1>
            <p class="mt-1 text-sm text-slate-500">Tạo license cho khách hàng, giới hạn thiết bị và thu hồi quyền truy cập API.</p>
        </div>

        @if ($newLicenseKey)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5">
                <b class="text-amber-900">License mới — chỉ hiển thị một lần:</b>
                <div class="mt-2 select-all break-all rounded-xl bg-white p-3 font-mono text-sm">{{ $newLicenseKey }}</div>
                <button type="button" wire:click="$set('newLicenseKey', null)" class="mt-3 text-sm font-bold text-amber-800">Đã sao chép, đóng</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-black text-slate-900">Tạo license cho khách hàng</h2>
            <div class="grid gap-4 md:grid-cols-[1fr_140px_220px_auto] md:items-end">
                <label>
                    <span class="mb-1 block text-sm font-bold text-slate-700">Tên khách hàng</span>
                    <input wire:model="label" class="h-11 w-full rounded-xl border border-slate-300 px-3" placeholder="Ví dụ: Nguyễn Văn A">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-bold text-slate-700">Số thiết bị</span>
                    <input type="number" min="1" max="100" wire:model="maxDevices" class="h-11 w-full rounded-xl border border-slate-300 px-3">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-bold text-slate-700">Ngày hết hạn</span>
                    <input type="datetime-local" wire:model="expiresAt" class="h-11 w-full rounded-xl border border-slate-300 px-3">
                </label>
                <button type="button" wire:click="createLicense" wire:loading.attr="disabled" wire:target="createLicense" class="h-11 rounded-xl bg-blue-600 px-5 font-bold text-white hover:bg-blue-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="createLicense">Tạo license</span>
                    <span wire:loading wire:target="createLicense">Đang tạo...</span>
                </button>
            </div>
        </section>

        <div class="space-y-4">
            @forelse ($licenses as $license)
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-black text-slate-900">{{ $license->label }}</h2>
                            <p class="font-mono text-xs text-slate-500">{{ $license->key_hint }}</p>
                        </div>
                        <button type="button" wire:click="toggleLicense({{ $license->id }})" class="rounded-lg px-3 py-2 text-sm font-bold {{ $license->active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $license->active ? 'Đang bật' : 'Đã tắt' }}
                        </button>
                    </div>
                    <div class="mt-3 text-sm text-slate-500">
                        Thiết bị: {{ $license->devices->whereNull('revoked_at')->count() }}/{{ $license->max_devices }}
                        · Hết hạn: {{ $license->expires_at?->format('d/m/Y H:i') ?? 'Không giới hạn' }}
                    </div>
                    <div class="mt-4 space-y-2">
                        @forelse ($license->devices as $device)
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 text-sm">
                                <div>
                                    <b>{{ $device->device_name ?: 'Thiết bị '.$device->id }}</b>
                                    <div class="text-xs text-slate-500">Hoạt động gần nhất: {{ $device->last_seen_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                </div>
                                @if (!$device->revoked_at)
                                    <button type="button" wire:click="revokeDevice({{ $device->id }})" wire:confirm="Thu hồi thiết bị này?" class="font-bold text-red-600">Thu hồi</button>
                                @else
                                    <span class="font-bold text-slate-400">Đã thu hồi</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Chưa có thiết bị kích hoạt.</p>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Chưa có license nào. Dùng form phía trên để tạo license đầu tiên.</div>
            @endforelse
        </div>
    </div>
</div>
