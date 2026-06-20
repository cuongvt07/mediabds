<div class="flex-1 overflow-y-auto p-6 lg:p-10">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Cấu hình Extension</h1>
                <p class="mt-1 text-sm text-slate-500">Quản lý cấu hình public trả về từ <code>/api/v1/extension/config</code>.</p>
            </div>
            <button wire:click="save" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                Lưu cấu hình
            </button>
        </div>

        @if (session()->has('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('message') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Trạng thái và phiên bản</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
                    <input type="checkbox" wire:model="enabled" class="h-5 w-5 rounded border-slate-300 text-blue-600">
                    <span><b class="block text-slate-900">Cho phép client hoạt động</b><small class="text-slate-500">Tắt để client hợp lệ chuyển sang trạng thái bảo trì.</small></span>
                </label>
                <label class="block"><span class="mb-1 block text-sm font-bold text-slate-700">Phiên bản tối thiểu</span><input wire:model="minVersion" class="w-full rounded-xl border-slate-300" placeholder="1.0.0"></label>
                <label class="block"><span class="mb-1 block text-sm font-bold text-slate-700">Chu kỳ tải cấu hình (giây)</span><input type="number" wire:model="pollIntervalSeconds" class="w-full rounded-xl border-slate-300" min="60"></label>
                <label class="block md:col-span-2"><span class="mb-1 block text-sm font-bold text-slate-700">Thông báo bảo trì</span><textarea wire:model="maintenanceMessage" class="w-full rounded-xl border-slate-300" rows="3"></textarea></label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-black text-slate-900">Thông tin hiển thị</h2>
            <div class="grid gap-5 md:grid-cols-3">
                <label class="block"><span class="mb-1 block text-sm font-bold text-slate-700">Tên công cụ</span><input wire:model="brandingTitle" class="w-full rounded-xl border-slate-300"></label>
                <label class="block"><span class="mb-1 block text-sm font-bold text-slate-700">Số hỗ trợ</span><input wire:model="supportPhone" class="w-full rounded-xl border-slate-300"></label>
                <label class="block"><span class="mb-1 block text-sm font-bold text-slate-700">URL hỗ trợ</span><input wire:model="supportUrl" class="w-full rounded-xl border-slate-300" placeholder="https://..."></label>
            </div>
            <div class="mt-5 flex flex-wrap gap-5">
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="uiEnabled" class="rounded border-slate-300 text-blue-600"><span class="text-sm font-semibold">Hiển thị giao diện</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="autoNavigation" class="rounded border-slate-300 text-blue-600"><span class="text-sm font-semibold">Cho phép điều hướng tự động hợp lệ</span></label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">Danh sách khóa học</h2>
                <button wire:click="addCourse" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700">Thêm khóa học</button>
            </div>
            <div class="space-y-3">
                @foreach ($courses as $index => $course)
                    <div wire:key="course-{{ $index }}" class="grid gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-[1fr_1fr_100px_90px_44px] md:items-end">
                        <label><span class="mb-1 block text-xs font-bold text-slate-500">Đường dẫn</span><input wire:model="courses.{{ $index }}.path" class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label><span class="mb-1 block text-xs font-bold text-slate-500">Tên hiển thị</span><input wire:model="courses.{{ $index }}.label" class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label><span class="mb-1 block text-xs font-bold text-slate-500">Ưu tiên</span><input type="number" wire:model="courses.{{ $index }}.priority" class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="flex h-10 items-center gap-2"><input type="checkbox" wire:model="courses.{{ $index }}.enabled" class="rounded border-slate-300 text-blue-600"><span class="text-sm">Bật</span></label>
                        <button wire:click="removeCourse({{ $index }})" class="h-10 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Khóa ký Ed25519</h2>
                    <p class="mt-1 text-sm text-slate-500">Secret key được mã hóa trong database bằng APP_KEY hiện có. Không hiển thị trên giao diện.</p>
                </div>
                <button wire:click="generateSigningKeys" wire:confirm="Tạo khóa mới sẽ làm public key cũ mất hiệu lực. Tiếp tục?" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">
                    {{ $signingPublicKey ? 'Đổi cặp khóa' : 'Tạo cặp khóa' }}
                </button>
            </div>
            <label class="mt-4 block"><span class="mb-1 block text-xs font-bold text-slate-500">Public key để ghim trong client</span><textarea readonly class="w-full rounded-xl border-slate-300 bg-slate-50 font-mono text-xs" rows="3">{{ $signingPublicKey }}</textarea></label>
        </section>
    </div>
</div>
