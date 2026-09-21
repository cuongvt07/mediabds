<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Quản trị Vault' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /*
         * Layout CMS RIÊNG cho module Vault (Ví sinh lời) — KHÔNG dùng chung
         * components.layouts.website-cms (dark theme) với CMS BĐS chính, theo
         * yêu cầu tách biệt giao diện quản trị. Nền sáng, sidebar tối, accent
         * xanh dương — đơn giản, khác biệt trực quan với CMS BĐS. Vẫn dùng
         * chung tài khoản đăng nhập admin (guard 'web'+'admin').
         */
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --accent: #2563eb;
            --accent-soft: #eff6ff;
            --border: #e2e8f0;
            --bg-page: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg-page);
            color: var(--text-primary);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }

        .vcms-shell {
            display: flex;
            min-height: 100vh;
        }

        .vcms-sidebar {
            width: 240px;
            flex-shrink: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .vcms-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            font-weight: 800;
            color: #fff;
        }

        .vcms-brand-mark {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
        }

        .vcms-nav { padding: 14px 10px; flex: 1; }
        .vcms-nav-title {
            padding: 10px 10px 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }

        .vcms-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            color: var(--sidebar-text);
        }

        .vcms-nav-link:hover { background: var(--sidebar-hover); color: #fff; }
        .vcms-nav-link.is-active { background: var(--accent); color: #fff; }

        .vcms-sidebar-foot {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: 12px;
        }

        .vcms-sidebar-foot form button {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--sidebar-text);
            cursor: pointer;
            font-size: 13px;
        }
        .vcms-sidebar-foot form button:hover { background: var(--sidebar-hover); color: #fff; }

        .vcms-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .vcms-topbar {
            height: 60px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .vcms-topbar-title { font-weight: 700; font-size: 16px; color: var(--text-primary); }
        .vcms-topbar-meta { font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 14px; }

        .vcms-content { padding: 24px; flex: 1; }

        .mono { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }

        @media (max-width: 900px) {
            .vcms-shell { flex-direction: column; }
            .vcms-sidebar { width: 100%; height: auto; position: static; flex-direction: row; overflow-x: auto; }
            .vcms-brand { border-bottom: none; border-right: 1px solid rgba(255,255,255,.08); }
            .vcms-nav { display: flex; padding: 10px; }
            .vcms-nav-title { display: none; }
            .vcms-nav-link { white-space: nowrap; }
            .vcms-sidebar-foot { display: none; }
            .vcms-content { padding: 14px; }
        }
    </style>
</head>

<body>
    <div class="vcms-shell">
        <aside class="vcms-sidebar">
            <div class="vcms-brand">
                <span class="vcms-brand-mark"><i class="fa-solid fa-vault"></i></span>
                <span>Ví sinh lời</span>
            </div>
            <nav class="vcms-nav">
                <div class="vcms-nav-title">Quản trị</div>
                <a href="{{ route('vault.dashboard') }}" class="vcms-nav-link {{ request()->routeIs('vault.dashboard') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> Tổng quan
                </a>
                <a href="{{ route('vault.deposits') }}" class="vcms-nav-link {{ request()->routeIs('vault.deposits') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-arrow-down-to-bracket"></i> Nạp tiền
                </a>
                <a href="{{ route('vault.withdrawals') }}" class="vcms-nav-link {{ request()->routeIs('vault.withdrawals') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Rút tiền
                </a>
                <a href="{{ route('vault.sepay-settings') }}" class="vcms-nav-link {{ request()->routeIs('vault.sepay-settings') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-gear"></i> Cấu hình SePay
                </a>
                <a href="{{ route('vault.ekyc.review') }}" class="vcms-nav-link {{ request()->routeIs('vault.ekyc.*') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-id-card"></i> Duyệt eKYC
                </a>
            </nav>
            <div class="vcms-sidebar-foot">
                <a href="{{ route('listings') }}" class="vcms-nav-link" style="padding-left:0">
                    <i class="fa-solid fa-arrow-left"></i> Về quản trị BĐS
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
                </form>
            </div>
        </aside>

        <div class="vcms-main">
            <header class="vcms-topbar">
                <span class="vcms-topbar-title">{{ $title ?? 'Quản trị Vault' }}</span>
                <span class="vcms-topbar-meta">
                    <span class="mono">{{ now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</span>
                    <span>{{ auth()->user()->name ?? auth()->user()->phone ?? '' }}</span>
                </span>
            </header>

            <main class="vcms-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</body>

</html>
