<div class="bg-white rounded-3xl border border-slate-200 shadow-[0_24px_70px_rgba(17,17,17,0.12)] p-8 sm:p-10 w-full animate-in fade-in slide-in-from-bottom-6 duration-500">

    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto mb-4 grid place-items-center rounded-2xl bg-slate-900 text-white text-2xl shadow-lg">
            <i class="fa-solid fa-house"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">nhatrosv</h2>
        <p class="mt-1 text-slate-500 text-sm font-semibold">
            {{ $isRegistering ? 'Tạo tài khoản mới' : 'Đăng nhập trang quản trị' }}
        </p>
    </div>

    @if(!$isRegistering)
        <form wire:submit="login" class="space-y-5">
            {{-- Phone --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Số điện thoại</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <input type="text" wire:model="phone" autocomplete="username"
                        class="block w-full pl-11 pr-4 h-12 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-900 focus:bg-white transition"
                        placeholder="Nhập số điện thoại">
                </div>
                @error('phone') <span class="block text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</span> @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Mật khẩu</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" wire:model="password" autocomplete="current-password"
                        class="block w-full pl-11 pr-4 h-12 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-900 focus:bg-white transition"
                        placeholder="Nhập mật khẩu">
                </div>
                @error('password') <span class="block text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</span> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 select-none cursor-pointer">
                <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded accent-slate-900">
                Ghi nhớ đăng nhập
            </label>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full h-12 flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white font-bold tracking-wide hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="login"><i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Đăng nhập</span>
                <span wire:loading wire:target="login"><i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...</span>
            </button>

            <div class="text-center pt-1">
                <button type="button" wire:click="toggleRegister" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                    Chưa có tài khoản? <span class="underline">Đăng ký ngay</span>
                </button>
            </div>
        </form>
    @else
        <form wire:submit="register" class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Họ và tên</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-user"></i></span>
                    <input type="text" wire:model="registerName"
                        class="block w-full pl-11 pr-4 h-12 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-900 focus:bg-white transition"
                        placeholder="Họ và tên">
                </div>
                @error('registerName') <span class="block text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Số điện thoại</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-phone"></i></span>
                    <input type="text" wire:model="registerPhone"
                        class="block w-full pl-11 pr-4 h-12 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-900 focus:bg-white transition"
                        placeholder="Số điện thoại">
                </div>
                @error('registerPhone') <span class="block text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Mã giới thiệu</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-gift"></i></span>
                    <input type="text" wire:model="registerInviteCode"
                        class="block w-full pl-11 pr-4 h-12 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-900 focus:bg-white transition"
                        placeholder="Mã người giới thiệu (bắt buộc)">
                </div>
                @error('registerInviteCode') <span class="block text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</span> @enderror
            </div>

            <button type="submit"
                class="w-full h-12 flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white font-bold tracking-wide hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition">
                <i class="fa-solid fa-user-plus"></i> Đăng ký
            </button>

            <div class="text-center pt-1">
                <button type="button" wire:click="toggleRegister" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                    Đã có tài khoản? <span class="underline">Quay lại đăng nhập</span>
                </button>
            </div>
        </form>
    @endif
</div>
