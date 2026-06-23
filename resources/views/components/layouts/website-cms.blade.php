<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Quản trị BĐS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    @livewireStyles
    <style>
        :root {
            --bg-base: #0f1117;
            --bg-surface: #181c27;
            --bg-raised: #222636;
            --border: #2e3347;
            --text-primary: #e8eaf0;
            --text-secondary: #8b91a7;
            --text-muted: #4e5368;
            --accent: #3d6fff;
            --accent-dim: #1a2e6b;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            overflow: hidden;
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13px;
        }

        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .mono { font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Consolas, monospace; }

        .cms-shell {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            grid-template-rows: 40px minmax(0, 1fr) 28px;
            height: 100vh;
            min-width: 1280px;
            background: var(--bg-base);
        }

        .cms-topbar {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            background: #10131b;
            padding: 0 12px;
        }

        .cms-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .cms-brand-mark {
            display: grid;
            width: 24px;
            height: 24px;
            place-items: center;
            border: 1px solid #385aa8;
            background: var(--accent-dim);
            color: #9bb7ff;
            font-size: 12px;
        }

        .cms-breadcrumb {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .cms-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
        }

        .cms-icon-btn {
            display: inline-grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            color: var(--text-secondary);
            cursor: pointer;
        }

        .cms-icon-btn:hover { border-color: var(--accent); color: var(--text-primary); }

        .cms-sidebar {
            grid-row: 2 / 4;
            border-right: 1px solid var(--border);
            background: var(--bg-surface);
            overflow-y: auto;
        }

        .cms-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 36px;
            border-bottom: 1px solid var(--border);
            padding: 0 10px;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .cms-nav { padding: 10px 0 12px; }
        .cms-nav-group { margin-bottom: 10px; }
        .cms-nav-title {
            padding: 8px 12px 5px;
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .cms-nav-link {
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr) auto;
            align-items: center;
            gap: 8px;
            min-height: 32px;
            border-left: 2px solid transparent;
            padding: 0 10px;
            color: var(--text-secondary);
        }

        .cms-nav-link:hover { background: var(--bg-raised); color: var(--text-primary); }
        .cms-nav-link.is-active {
            border-left-color: var(--accent);
            background: var(--accent-dim);
            color: var(--text-primary);
        }

        .cms-main {
            display: grid;
            grid-template-rows: 36px minmax(0, 1fr);
            min-width: 0;
            background: var(--bg-base);
        }

        .cms-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            background: #121620;
            padding: 0 12px;
        }

        .cms-page-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .cms-content {
            min-width: 0;
            overflow: auto;
            padding: 12px;
        }

        .cms-statusbar {
            grid-column: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--border);
            background: #10131b;
            padding: 0 12px;
            color: var(--text-muted);
            font-size: 11px;
        }

        .cms-scrollbar::-webkit-scrollbar { width: 10px; height: 10px; }
        .cms-scrollbar::-webkit-scrollbar-thumb { background: #343a50; border: 2px solid var(--bg-base); }
        .cms-scrollbar::-webkit-scrollbar-track { background: var(--bg-base); }

        .cms-panel {
            border: 1px solid var(--border);
            background: var(--bg-surface);
        }

        .cms-panel-head {
            display: flex;
            min-height: 36px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            padding: 0 10px;
        }

        .cms-panel-title {
            color: #9bb7ff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: 3px 8px 3px 9px;
            border-left: 3px solid var(--accent);
            background: linear-gradient(90deg, rgba(61,111,255,.16), rgba(61,111,255,0));
            border-radius: 0 3px 3px 0;
        }

        .cms-grid-2 {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(360px, .65fr);
            gap: 12px;
        }

        .cms-data-list { display: grid; }
        .cms-data-row {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) 100px 86px;
            align-items: center;
            min-height: 34px;
            border-bottom: 1px solid var(--border);
            padding: 0 10px;
            gap: 10px;
        }

        .cms-data-row:last-child { border-bottom: 0; }
        .cms-data-row:hover, .cms-table tbody tr:hover { background: var(--bg-raised); }

        .cms-table-wrap { overflow-x: auto; }
        .cms-table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cms-table th {
            height: 32px;
            border-bottom: 1px solid var(--accent);
            background: #1a2238;
            color: #9bb7ff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }

        .cms-table td {
            height: 36px;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .cms-table th, .cms-table td { padding: 0 8px; }
        .cms-table .right { text-align: right; }
        .cms-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .cms-input, .cms-select, .cms-textarea {
            border: 1px solid var(--border);
            background: var(--bg-raised);
            color: var(--text-primary);
            outline: none;
        }

        .cms-input, .cms-select {
            height: 28px;
            padding: 0 8px;
        }

        .cms-textarea {
            min-height: 96px;
            padding: 8px;
            resize: vertical;
        }

        .cms-input:focus, .cms-select:focus, .cms-textarea:focus { border-color: var(--accent); }

        .cms-btn {
            display: inline-flex;
            height: 28px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid var(--border);
            background: var(--bg-raised);
            color: var(--text-primary);
            padding: 0 10px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
        }

        .cms-btn:hover { border-color: var(--accent); color: #fff; }
        .cms-btn.primary { border-color: var(--accent); background: var(--accent); color: #fff; }
        .cms-btn.danger { border-color: rgba(239,68,68,.45); background: #2e0f0f; color: var(--danger); }
        .cms-btn.success { border-color: rgba(34,197,94,.45); background: #0f2e1a; color: var(--success); }

        .cms-badge {
            display: inline-flex;
            height: 22px;
            align-items: center;
            gap: 5px;
            border: 1px solid var(--border);
            padding: 0 7px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .cms-badge.success { border-color: rgba(34,197,94,.35); background: #0f2e1a; color: var(--success); }
        .cms-badge.warning { border-color: rgba(245,158,11,.35); background: #3d2e00; color: var(--warning); }
        .cms-badge.danger { border-color: rgba(239,68,68,.35); background: #2e0f0f; color: var(--danger); }
        .cms-badge.muted { color: var(--text-secondary); }
        .cms-badge.info { border-color: rgba(6,182,212,.35); background: #06252c; color: var(--info); }

        .cms-kpi-row {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) 110px 82px;
            min-height: 34px;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding: 0 10px;
        }

        .cms-pagination {
            border-top: 1px solid var(--border);
            padding: 8px 10px;
            color: var(--text-secondary);
        }

        .cms-flash {
            position: fixed;
            right: 14px;
            bottom: 40px;
            z-index: 80;
            width: 320px;
            border: 1px solid rgba(34,197,94,.35);
            background: #0f2e1a;
            color: var(--success);
            padding: 10px 12px;
            font-weight: 700;
        }

        .cms-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: grid;
            place-items: center;
            background: rgba(0,0,0,.72);
        }

        .cms-modal {
            width: min(720px, calc(100vw - 48px));
            max-height: calc(100vh - 48px);
            overflow: auto;
            border: 1px solid var(--border);
            background: var(--bg-surface);
        }

        .cms-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            padding: 12px;
        }

        /* CKEditor inside the dark CMS — keep editable area light with dark text. */
        .ck.ck-editor__main > .ck-editor__editable { min-height: 240px; color: #1a1a1a; }
        .ck.ck-editor__editable_inline { background: #fff; }
        .ck.ck-toolbar { background: #f1f1f4; border-color: var(--border); }
        .ck-body-wrapper { position: relative; z-index: 90; }

        .cms-field { display: grid; gap: 4px; }
        .cms-field.full { grid-column: 1 / -1; }
        .cms-label {
            align-self: start;
            display: inline-block;
            width: fit-content;
            color: #aeb9d6;
            background: rgba(155, 183, 255, .10);
            border-left: 2px solid var(--accent);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 0 3px 3px 0;
        }

        @media (max-width: 1439px) {
            .cms-shell { grid-template-columns: 48px minmax(0, 1fr); }
            .cms-nav-title, .cms-nav-text, .cms-sidebar-head span { display: none; }
            .cms-nav-link { grid-template-columns: 26px; justify-content: center; padding: 0; }
        }
    </style>
</head>

@php
    $activeTab = request('tab', 'overview');
    $mainItems = [
        'overview' => ['Tổng quan', 'fa-chart-pie', null],
        'listings' => ['Tin đăng', 'fa-newspaper', $stats['pending_listings'] ?? null],
        'home' => ['Trang chủ', 'fa-house-chimney-window', null],
    ];
    $catalogItems = [
        'categories' => ['Danh mục', 'fa-layer-group', null],
        'blogs' => ['Bài viết/SEO', 'fa-pen-nib', null],
    ];
    $userItems = [
        'accounts' => ['Tài khoản', 'fa-users-gear', $stats['accounts'] ?? null],
        'leads' => ['Khách liên hệ', 'fa-address-book', $stats['open_leads'] ?? null],
        'favorites' => ['Yêu thích', 'fa-heart', null],
        'saved-searches' => ['Tìm kiếm lưu', 'fa-bookmark', null],
        'analytics' => ['Thống kê', 'fa-chart-line', null],
    ];
    $systemItems = [
        'reports' => ['Báo cáo vi phạm', 'fa-flag', $stats['open_reports'] ?? null],
        'settings' => ['Cài đặt', 'fa-gear', null],
    ];
@endphp

<body>
    <div class="cms-shell">
        <header class="cms-topbar">
            <div class="cms-brand">
                <span class="cms-brand-mark"><i class="fa-solid fa-building"></i></span>
                <span>QUẢN TRỊ BĐS</span>
                <span class="cms-breadcrumb">/ quản trị website bất động sản</span>
            </div>
            <div class="cms-top-actions">
                <a class="cms-btn" href="{{ route('listings') }}"><i class="fa-solid fa-arrow-left"></i> Quản trị nội bộ</a>
                <a class="cms-btn" href="{{ route('docs.api') }}" target="_blank"><i class="fa-solid fa-code"></i> API</a>
                <a class="cms-icon-btn" href="https://b-s-pink.vercel.app" target="_blank" title="Mở website"><i class="fa-solid fa-up-right-from-square"></i></a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="cms-icon-btn" type="submit" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </header>

        <aside class="cms-sidebar cms-scrollbar">
            <div class="cms-sidebar-head">
                <span>{{ auth()->user()->name ?? 'Quản trị viên' }}</span>
                <i class="fa-solid fa-bars"></i>
            </div>
            <nav class="cms-nav">
                <x-website-cms-nav-group title="Vận hành" :items="$mainItems" :active-tab="$activeTab" />
                <x-website-cms-nav-group title="Nội dung" :items="$catalogItems" :active-tab="$activeTab" />
                <x-website-cms-nav-group title="Người dùng" :items="$userItems" :active-tab="$activeTab" />
                <x-website-cms-nav-group title="Hệ thống" :items="$systemItems" :active-tab="$activeTab" />
            </nav>
        </aside>

        <main class="cms-main">
            <div class="cms-toolbar">
                <div class="cms-page-title">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Điều phối quản trị</span>
                </div>
                <div class="cms-top-actions">
                    <span class="mono">{{ now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</span>
                    <span>{{ auth()->user()->phone ?? auth()->user()->email ?? '' }}</span>
                </div>
            </div>

            <section class="cms-content cms-scrollbar">
                {{ $slot }}
            </section>
        </main>

        <footer class="cms-statusbar">
            <span>Timezone: Asia/Ho_Chi_Minh</span>
            <span>Phân hệ: tin đăng, danh mục, trang chủ, bài viết, khách liên hệ, yêu thích, tìm kiếm lưu</span>
        </footer>
    </div>

    {{-- Flash toast is rendered inside the Livewire component so it appears on AJAX saves. --}}

    @livewireScripts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</body>

</html>
