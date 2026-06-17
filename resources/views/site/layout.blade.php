<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tìm phòng trọ, studio, duplex và phòng có gác tại TP.HCM.">
    <title>@yield('title', 'nhatrosv.com')</title>
    @vite('resources/css/site.css')
    @livewireStyles
</head>
<body>
    @php
        $siteName = 'nhatrosv.com';
        $siteLogo = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $settings = \App\Models\SiteSetting::query()
                ->whereIn('key', ['site_name', 'logo_url'])
                ->pluck('value', 'key');
            $siteName = $settings['site_name'] ?: $siteName;
            $siteLogo = $settings['logo_url'] ?: null;
        }
        $authUser = auth()->user();
        $isSiteAdmin = $authUser && method_exists($authUser, 'isAdmin') && $authUser->isAdmin();
        $postUrl = $isSiteAdmin ? route('site.admin', ['tab' => 'listings']) : ($authUser ? route('user.listing.create') : route('login'));
        $manageUrl = $isSiteAdmin ? route('site.admin', ['tab' => 'listings']) : ($authUser ? route('user.dashboard') : route('login'));
        $accountUrl = $authUser ? ($isSiteAdmin ? route('site.admin') : route('user.dashboard')) : route('login');
    @endphp
    <header class="site-header">
        <div class="site-shell site-nav">
            <a class="site-brand" href="{{ route('site.home') }}">
                <span class="site-brand-mark">
                    @if($siteLogo)
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 11.3 12 4l9 7.3v8.2a.5.5 0 0 1-.5.5H15v-6H9v6H3.5a.5.5 0 0 1-.5-.5v-8.2Z" fill="currentColor"/>
                        </svg>
                    @endif
                </span>
                <b>{{ $siteName }}</b>
            </a>
            <nav class="site-menu" aria-label="Điều hướng chính">
                <a href="{{ route('site.home') }}">Trang chủ</a>
                <a href="{{ route('site.home') }}#gioi-thieu">Giới thiệu</a>
                <a href="{{ route('site.home') }}#lien-he">Liên hệ</a>
            </nav>
            <div class="site-header-actions">
                <a class="site-hd-link site-hd-contact" href="{{ route('site.home') }}#lien-he">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.3a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.25 1z"/></svg>
                    Liên hệ
                </a>
                @guest
                    <button type="button" class="site-hd-link site-hd-login" data-auth-open="login">Đăng nhập</button>
                @endguest
                @auth
                    <a class="site-hd-post" href="{{ $postUrl }}">+ Đăng tin</a>
                @else
                    <a class="site-hd-post" href="{{ route('login') }}" data-auth-open="login">+ Đăng tin</a>
                @endauth

                <div class="site-account" data-account>
                    <button type="button" class="site-account-btn" data-account-toggle aria-label="Tài khoản">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 6v1h18v-1c0-3.5-4-6-9-6Z"/></svg>
                        <svg class="site-account-caret" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>
                    </button>
                    <div class="site-account-pop" data-account-pop>
                        @auth
                            <div class="site-account-head">
                                <strong>{{ $authUser->name ?: 'Tài khoản' }}</strong>
                                <span>{{ $authUser->phone }}</span>
                            </div>
                            <div class="site-account-menu">
                                @if($isSiteAdmin)
                                    <a href="{{ route('site.admin') }}"><i></i> Trang quản trị</a>
                                    <a href="{{ route('site.admin', ['tab' => 'listings']) }}"><i></i> Quản lý tin đăng</a>
                                @else
                                    <a href="{{ route('user.dashboard') }}"><i></i> Trang cá nhân</a>
                                    <a href="{{ route('user.listing.create') }}"><i></i> Đăng tin mới</a>
                                @endif
                                <a href="{{ route('site.home') }}"><i></i> Trang chủ</a>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button type="submit"><i></i> Đăng xuất</button>
                                </form>
                            </div>
                        @else
                            <div class="site-account-promo">
                                <div>
                                    <strong>Tìm trọ dễ, theo dõi tin nhanh.</strong>
                                    <span>Đăng nhập cái đã!</span>
                                </div>
                                <span class="site-account-emoji">🏠</span>
                            </div>
                            <div class="site-account-cta">
                                <button type="button" class="site-account-ghost" data-auth-open="register">Tạo tài khoản</button>
                                <button type="button" class="site-account-primary" data-auth-open="login">Đăng nhập</button>
                            </div>
                            <div class="site-account-menu">
                                <a href="{{ route('site.home') }}"><i></i> Trang chủ</a>
                                <a href="{{ route('site.home') }}#danh-sach"><i></i> Danh sách phòng</a>
                                <a href="{{ route('site.home') }}#lien-he"><i></i> Liên hệ</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>@yield('content'){{ $slot ?? '' }}</main>

    <footer class="site-footer">
        <div class="site-shell">
            <span>© {{ date('Y') }} nhatrosv.com. Thông tin phòng trọ tại TP.HCM.</span>
            <span>Minh bạch thông tin · Hỗ trợ nhanh · Không cần đăng nhập</span>
        </div>
    </footer>

    <nav class="site-bottom-nav" aria-label="Điều hướng nhanh">
        <a class="site-bn-item" href="{{ route('site.home') }}">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 11.3 12 4l9 7.3v8.2a.5.5 0 0 1-.5.5H15v-6H9v6H3.5a.5.5 0 0 1-.5-.5z"/></svg>
            <span>Trang chủ</span>
        </a>
        <a class="site-bn-item" href="{{ $manageUrl }}" @guest data-auth-open="login" @endguest>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm1 5h8v2H8V8Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
            <span>Quản lý tin</span>
        </a>
        <a class="site-bn-item site-bn-center" href="{{ $postUrl }}" aria-label="Đăng tin" @guest data-auth-open="login" @endguest>
            <span class="site-bn-fab"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg></span>
            <span>Đăng tin</span>
        </a>
        <a class="site-bn-item" href="{{ route('site.home') }}#lien-he">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.3a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.25 1z"/></svg>
            <span>Liên hệ</span>
        </a>
        <a class="site-bn-item" href="{{ $accountUrl }}" @guest data-auth-open="login" @endguest>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 6v1h18v-1c0-3.5-4-6-9-6Z"/></svg>
            <span>{{ $authUser ? 'Tài khoản' : 'Đăng nhập' }}</span>
        </a>
    </nav>

    <div class="site-auth-modal" data-auth-modal data-open="{{ session('authMode') }}" hidden>
        <div class="site-auth-backdrop" data-auth-close></div>
        <div class="site-auth-box" role="dialog" aria-modal="true">
            <button type="button" class="site-auth-x" data-auth-close aria-label="Đóng">&times;</button>
            <div class="site-auth-logo">{{ $siteName }}</div>
            <div class="site-auth-tabs">
                <button type="button" data-auth-tab="login" class="is-active">Đăng nhập</button>
                <button type="button" data-auth-tab="register">Đăng ký</button>
            </div>

            <form method="POST" action="{{ route('site.auth.login') }}" class="site-auth-pane" data-auth-pane="login">
                @csrf
                <label class="site-auth-field"><span>Số điện thoại</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="098..." autocomplete="username">
                </label>
                <label class="site-auth-field"><span>Mật khẩu</span>
                    <input type="password" name="password" placeholder="Nhập mật khẩu" autocomplete="current-password">
                </label>
                <label class="site-auth-remember"><input type="checkbox" name="remember" value="1" checked> Ghi nhớ đăng nhập</label>
                @if(session('authMode') === 'login' && $errors->any())
                    <div class="site-auth-err">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="site-auth-submit">Đăng nhập</button>
                <p class="site-auth-switch">Chưa có tài khoản? <button type="button" data-auth-tab="register">Đăng ký ngay</button></p>
            </form>

            <form method="POST" action="{{ route('site.auth.register') }}" class="site-auth-pane" data-auth-pane="register" hidden>
                @csrf
                <label class="site-auth-field"><span>Họ và tên</span>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="VD: Nguyễn Văn A">
                </label>
                <label class="site-auth-field"><span>Số điện thoại</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="098...">
                </label>
                <label class="site-auth-field"><span>Mật khẩu</span>
                    <input type="password" name="password" placeholder="Tối thiểu 6 ký tự">
                </label>
                @if(session('authMode') === 'register' && $errors->any())
                    <div class="site-auth-err">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="site-auth-submit">Tạo tài khoản</button>
                <p class="site-auth-switch">Đã có tài khoản? <button type="button" data-auth-tab="login">Đăng nhập</button></p>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-auth-modal]');
            var account = document.querySelector('[data-account]');
            function closeAccount() { if (account) account.classList.remove('is-open'); }
            function setTab(mode) {
                if (!modal) return;
                modal.querySelectorAll('[data-auth-tab]').forEach(function (b) { b.classList.toggle('is-active', b.dataset.authTab === mode); });
                modal.querySelectorAll('[data-auth-pane]').forEach(function (p) { p.hidden = p.dataset.authPane !== mode; });
            }
            function openModal(mode) { if (!modal) return; modal.hidden = false; setTab(mode || 'login'); document.body.classList.add('site-noscroll'); closeAccount(); }
            function closeModal() { if (!modal) return; modal.hidden = true; document.body.classList.remove('site-noscroll'); }

            document.addEventListener('click', function (e) {
                var open = e.target.closest('[data-auth-open]');
                if (open) { e.preventDefault(); openModal(open.dataset.authOpen); return; }
                if (modal && !modal.hidden) {
                    var tab = e.target.closest('[data-auth-tab]');
                    if (tab) { e.preventDefault(); setTab(tab.dataset.authTab); return; }
                    if (e.target.closest('[data-auth-close]')) { closeModal(); return; }
                }
                if (e.target.closest('[data-account-toggle]')) { if (account) account.classList.toggle('is-open'); return; }
                if (account && !e.target.closest('[data-account]')) { closeAccount(); }
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeModal(); closeAccount(); } });

            if (modal && modal.dataset.open) { openModal(modal.dataset.open); }
        })();
    </script>

    @stack('scripts')
    @livewireScripts
</body>
</html>
