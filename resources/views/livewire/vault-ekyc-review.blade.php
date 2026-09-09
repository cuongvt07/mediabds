<div class="h-full flex flex-col p-6 max-w-[1600px] w-full mx-auto relative flex-1 pb-24">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 flex-shrink-0">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight flex items-center justify-center sm:justify-start gap-3">
                <div class="p-2 sm:p-2.5 bg-emerald-100 rounded-xl">
                    <i class="fa-solid fa-id-card text-emerald-600 text-lg sm:text-xl"></i>
                </div>
                Duyệt hồ sơ eKYC (Vault)
            </h1>
            <p class="text-slate-500 mt-2 font-medium text-sm sm:text-base">
                Xác thực CCCD/CMND người dùng module Ví sinh lời — hiện đang duyệt thủ công.
            </p>
        </div>
        @if ($pendingCount > 0)
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-100 text-amber-700 font-semibold text-sm">
                <i class="fa-solid fa-clock"></i> {{ $pendingCount }} hồ sơ đang chờ
            </span>
        @endif
    </div>

    @if ($flashMessage)
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100/50 flex items-start gap-3"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600 shrink-0">
                <i class="fa-solid fa-check text-sm leading-none"></i>
            </div>
            <p class="text-emerald-700 text-sm font-medium">{{ $flashMessage }}</p>
        </div>
    @endif

    <div class="mb-6 flex gap-2">
        @foreach (['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Đã từ chối'] as $key => $label)
            <button wire:click="$set('statusFilter', '{{ $key }}')"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ $statusFilter === $key ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($submissions as $submission)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start gap-5">
                    <div class="flex gap-3">
                        <div class="text-center">
                            <img src="{{ route('vault.ekyc.image', ['submission' => $submission->id, 'side' => 'front']) }}"
                                 alt="Mặt trước CCCD" loading="lazy"
                                 class="w-40 h-24 object-cover rounded-lg border border-slate-200 bg-slate-50">
                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">Mặt trước</p>
                        </div>
                        <div class="text-center">
                            <img src="{{ route('vault.ekyc.image', ['submission' => $submission->id, 'side' => 'back']) }}"
                                 alt="Mặt sau CCCD" loading="lazy"
                                 class="w-40 h-24 object-cover rounded-lg border border-slate-200 bg-slate-50">
                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">Mặt sau</p>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900">{{ $submission->vaultUser->name }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $submission->vaultUser->phone }} · Mã {{ $submission->vaultUser->vault_code }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Nộp lúc {{ $submission->created_at->format('d/m/Y H:i') }}
                        </p>

                        @if ($submission->status === 'rejected' && $submission->rejection_reason)
                            <p class="mt-2 text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $submission->rejection_reason }}
                            </p>
                        @endif
                    </div>

                    @if ($submission->status === 'pending')
                        <div class="flex md:flex-col gap-2 shrink-0">
                            <button wire:click="approve({{ $submission->id }})"
                                    wire:confirm="Xác nhận DUYỆT hồ sơ này? Tài khoản sẽ được nâng lên eKYC cấp 2."
                                    class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-semibold text-sm">
                                <i class="fa-solid fa-check"></i> Duyệt
                            </button>
                            <button wire:click="openReject({{ $submission->id }})"
                                    class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 rounded-xl hover:bg-red-50 font-semibold text-sm">
                                <i class="fa-solid fa-xmark"></i> Từ chối
                            </button>
                        </div>
                    @else
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase
                            {{ $submission->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $submission->status === 'approved' ? 'Đã duyệt' : 'Đã từ chối' }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-slate-400">
                <i class="fa-solid fa-inbox text-3xl mb-3"></i>
                <p>Không có hồ sơ nào ở trạng thái này.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $submissions->links() }}</div>

    {{-- Modal từ chối --}}
    @if ($rejectingId)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="closeReject">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
                <h3 class="font-bold text-lg text-slate-900 mb-3">Từ chối hồ sơ #{{ $rejectingId }}</h3>
                <textarea wire:model="rejectionReason" rows="3" placeholder="Lý do từ chối (vd: ảnh mờ, thông tin không khớp...)"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500"></textarea>
                @error('rejectionReason') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="closeReject" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">Huỷ</button>
                    <button wire:click="confirmReject" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Xác nhận từ chối</button>
                </div>
            </div>
        </div>
    @endif
</div>
