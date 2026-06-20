<div class="flex-1 overflow-y-auto bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{-- Page heading --}}
        <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-600 text-white shadow-lg shadow-violet-200 sm:flex">
                    <i class="fa-solid fa-puzzle-piece text-lg"></i>
                </div>
                <div>
                    <div class="mb-1 flex items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Cấu hình Extension</h1>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full {{ $enabled ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $enabled ? 'Đang hoạt động' : 'Bảo trì' }}
                        </span>
                    </div>
                    <p class="text-sm leading-6 text-slate-500">Quản lý phiên bản, tính năng và nội dung được phân phối đến Extension.</p>
                </div>
            </div>
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100 disabled:cursor-wait disabled:opacity-70">
                <i wire:loading.remove wire:target="save" class="fa-solid fa-check"></i>
                <i wire:loading wire:target="save" class="fa-solid fa-circle-notch animate-spin"></i>
                <span wire:loading.remove wire:target="save">Lưu thay đổi</span>
                <span wire:loading wire:target="save">Đang lưu...</span>
            </button>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800" role="status">
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-500"></i>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800" role="alert">
                <div class="flex gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <div>
                        <p class="font-semibold">Chưa thể lưu cấu hình</p>
                        <ul class="mt-1 list-disc space-y-1 pl-4 text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                {{-- Runtime settings --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-sliders"></i></span>
                            <div>
                                <h2 class="font-semibold text-slate-900">Vận hành & phiên bản</h2>
                                <p class="text-xs text-slate-500">Kiểm soát trạng thái và chu kỳ đồng bộ của client.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <label class="group flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 transition hover:border-violet-200 hover:bg-violet-50/40 sm:col-span-2">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900">Cho phép Extension hoạt động</span>
                                <span class="mt-0.5 block text-xs leading-5 text-slate-500">Khi tắt, client sẽ nhận thông báo bảo trì bên dưới.</span>
                            </span>
                            <span class="relative inline-flex shrink-0">
                                <input type="checkbox" wire:model.live="enabled" class="peer sr-only">
                                <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-violet-600 peer-focus:ring-4 peer-focus:ring-violet-100 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:after:translate-x-5"></span>
                            </span>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Phiên bản tối thiểu <span class="text-red-500">*</span></span>
                            <div class="relative">
                                <i class="fa-solid fa-code-branch absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input wire:model="minVersion" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-100" placeholder="1.0.0">
                            </div>
                            <span class="mt-1.5 block text-xs text-slate-400">Định dạng semantic version, ví dụ 1.2.0</span>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Chu kỳ tải cấu hình</span>
                            <div class="relative">
                                <i class="fa-regular fa-clock absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input type="number" wire:model="pollIntervalSeconds" min="60" max="86400" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-14 text-sm text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">giây</span>
                            </div>
                            <span class="mt-1.5 block text-xs text-slate-400">Tối thiểu 60 giây, tối đa 24 giờ</span>
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Thông báo bảo trì</span>
                            <textarea wire:model="maintenanceMessage" rows="3" maxlength="500" class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-100" placeholder="Extension đang được bảo trì, vui lòng quay lại sau..."></textarea>
                            <span class="mt-1 block text-right text-xs text-slate-400">Tối đa 500 ký tự</span>
                        </label>
                    </div>
                </section>

                {{-- Branding --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-palette"></i></span>
                            <div>
                                <h2 class="font-semibold text-slate-900">Thương hiệu & hỗ trợ</h2>
                                <p class="text-xs text-slate-500">Thông tin người dùng nhìn thấy trên Extension.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Tên công cụ <span class="text-red-500">*</span></span>
                            <input wire:model="brandingTitle" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" placeholder="Tên hiển thị của Extension">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Số điện thoại hỗ trợ</span>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input wire:model="supportPhone" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" placeholder="0900 000 000">
                            </div>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Trang hỗ trợ</span>
                            <div class="relative">
                                <i class="fa-solid fa-link absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="url" wire:model="supportUrl" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" placeholder="https://hotro.example.com">
                            </div>
                        </label>
                        <div class="grid gap-3 sm:col-span-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:bg-slate-50">
                                <input type="checkbox" wire:model="uiEnabled" class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <span><span class="block text-sm font-medium text-slate-800">Hiển thị giao diện</span><span class="block text-xs text-slate-400">Bật UI cho người dùng</span></span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:bg-slate-50">
                                <input type="checkbox" wire:model="autoNavigation" class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <span><span class="block text-sm font-medium text-slate-800">Điều hướng tự động</span><span class="block text-xs text-slate-400">Tự chuyển đến trang hợp lệ</span></span>
                            </label>
                        </div>
                    </div>
                </section>

                {{-- Courses --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-graduation-cap"></i></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="font-semibold text-slate-900">Danh sách khóa học</h2>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-500">{{ count($courses) }}</span>
                                </div>
                                <p class="hidden text-xs text-slate-500 sm:block">Thiết lập đường dẫn và thứ tự hiển thị.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="addCourse" class="inline-flex h-9 shrink-0 items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-3 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                            <i class="fa-solid fa-plus"></i><span class="hidden sm:inline">Thêm khóa học</span><span class="sm:hidden">Thêm</span>
                        </button>
                    </div>
                    <div class="p-4 sm:p-6">
                        @forelse ($courses as $index => $course)
                            <div wire:key="course-{{ $index }}" class="mb-3 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-4 last:mb-0 lg:grid-cols-[36px_minmax(160px,1fr)_minmax(160px,1fr)_90px_70px_40px] lg:items-end">
                                <div class="hidden h-10 items-center justify-center text-slate-300 lg:flex"><i class="fa-solid fa-grip-vertical"></i></div>
                                <label>
                                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Đường dẫn</span>
                                    <input wire:model="courses.{{ $index }}.path" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 outline-none transition focus:border-violet-500 focus:ring-3 focus:ring-violet-100" placeholder="/slides/ten-khoa-hoc">
                                </label>
                                <label>
                                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Tên hiển thị</span>
                                    <input wire:model="courses.{{ $index }}.label" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-3 focus:ring-violet-100" placeholder="Tên khóa học">
                                </label>
                                <label>
                                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Ưu tiên</span>
                                    <input type="number" wire:model="courses.{{ $index }}.priority" min="1" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-3 focus:ring-violet-100">
                                </label>
                                <label class="flex h-10 cursor-pointer items-center gap-2 lg:justify-center">
                                    <input type="checkbox" wire:model="courses.{{ $index }}.enabled" class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    <span class="text-xs font-medium text-slate-600">Bật</span>
                                </label>
                                <button type="button" wire:click="removeCourse({{ $index }})" wire:confirm="Xóa khóa học này?" class="h-10 rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50" title="Xóa khóa học" aria-label="Xóa khóa học">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center">
                                <i class="fa-solid fa-graduation-cap text-2xl text-slate-300"></i>
                                <p class="mt-3 text-sm font-medium text-slate-600">Chưa có khóa học nào</p>
                                <button type="button" wire:click="addCourse" class="mt-2 text-sm font-semibold text-violet-600 hover:text-violet-700">Thêm khóa học đầu tiên</button>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Sticky side panel --}}
            <aside class="space-y-6 xl:sticky xl:top-8">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-900">Tổng quan cấu hình</h2>
                    </div>
                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Trạng thái</span>
                            <span class="font-semibold {{ $enabled ? 'text-emerald-600' : 'text-amber-600' }}">{{ $enabled ? 'Hoạt động' : 'Bảo trì' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Phiên bản tối thiểu</span>
                            <code class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">v{{ $minVersion ?: '—' }}</code>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Khóa học</span>
                            <span class="font-semibold text-slate-700">{{ count($courses) }}</span>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">API công khai</p>
                            <div class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2.5">
                                <code class="min-w-0 flex-1 truncate text-[11px] text-slate-200">/api/v1/extension/config</code>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-500"></i>
                            </div>
                        </div>
                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-violet-600 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-70">
                            <i wire:loading.remove wire:target="save" class="fa-solid fa-floppy-disk"></i>
                            <i wire:loading wire:target="save" class="fa-solid fa-circle-notch animate-spin"></i>
                            Lưu cấu hình
                        </button>
                        <p wire:dirty class="text-center text-xs font-medium text-amber-600">Bạn đang có thay đổi chưa lưu</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ copied: false }">
                    <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i class="fa-solid fa-shield-halved"></i></span>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Khóa ký Ed25519</h2>
                            <p class="text-[11px] text-slate-500">Xác thực dữ liệu cấu hình</p>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($signingPublicKey)
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Public key</span>
                                    <button type="button" @click="navigator.clipboard.writeText($refs.publicKey.value); copied = true; setTimeout(() => copied = false, 1600)" class="text-xs font-semibold text-violet-600 hover:text-violet-700">
                                        <span x-show="!copied"><i class="fa-regular fa-copy mr-1"></i>Sao chép</span>
                                        <span x-show="copied" x-cloak class="text-emerald-600"><i class="fa-solid fa-check mr-1"></i>Đã chép</span>
                                    </button>
                                </div>
                                <textarea x-ref="publicKey" readonly rows="3" class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-[10px] leading-5 text-slate-600 outline-none">{{ $signingPublicKey }}</textarea>
                            </div>
                        @else
                            <div class="mb-4 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-700">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Chưa có khóa ký. Hãy tạo khóa trước khi phát hành client.
                            </div>
                        @endif
                        <button type="button" wire:click="generateSigningKeys" wire:loading.attr="disabled" wire:target="generateSigningKeys" wire:confirm="Tạo khóa mới sẽ làm public key cũ mất hiệu lực. Tiếp tục?" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-70">
                            <i wire:loading.remove wire:target="generateSigningKeys" class="fa-solid fa-key"></i>
                            <i wire:loading wire:target="generateSigningKeys" class="fa-solid fa-circle-notch animate-spin"></i>
                            {{ $signingPublicKey ? 'Tạo lại cặp khóa' : 'Tạo cặp khóa' }}
                        </button>
                        <p class="mt-3 text-center text-[11px] leading-4 text-slate-400">Secret key được mã hóa và không hiển thị trên giao diện.</p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
