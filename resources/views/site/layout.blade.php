<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tìm phòng trọ, studio, duplex và phòng có gác tại TP.HCM.">
    <title>@yield('title', 'nhatrosv.com')</title>
    @vite('resources/css/site.css')
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
            <a class="site-hotline" href="tel:{{ config('app.contact_phone', '0900000000') }}">Tư vấn tìm phòng</a>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="site-shell">
            <span>© {{ date('Y') }} nhatrosv.com. Thông tin phòng trọ tại TP.HCM.</span>
            <span>Minh bạch thông tin · Hỗ trợ nhanh · Không cần đăng nhập</span>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
