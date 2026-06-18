<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Quản trị site nhà trọ' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-base: #f5f6f8;
            --bg-surface: #ffffff;
            --bg-soft: #f0f2f5;
            --border: #dde1e7;
            --text-primary: #111111;
            --text-secondary: #4b5563;
            --text-muted: #8a929e;
            --accent: #111111;
            --success: #087f3f;
            --danger: #c62828;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13px;
        }

        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .mono { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }

        .site-admin-shell {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
        }

        .site-admin-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(14px);
        }

        .site-admin-bar-inner {
            width: min(1280px, calc(100% - 32px));
            min-height: 64px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .site-admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .site-admin-brand-mark {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #111;
            color: #fff;
        }

        .site-admin-subtitle {
            display: block;
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: normal;
        }

        .site-admin-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .site-admin-content {
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 16px 0 36px;
        }

        .site-cms {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }

        .site-cms-sidebar {
            position: sticky;
            top: 80px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(17,17,17,.06);
            overflow: hidden;
        }

        .site-cms-sidebar-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid var(--border);
        }

        .site-cms-sidebar-head strong {
            display: block;
            font-size: 14px;
            font-weight: 900;
        }

        .site-cms-sidebar-head span:not(.site-cms-sidebar-mark) {
            display: block;
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .site-cms-sidebar-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #111;
            color: #fff;
        }

        .site-cms-nav-list {
            display: grid;
            gap: 6px;
            padding: 10px;
        }

        .site-cms-nav {
            width: 100%;
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
            border-radius: 14px;
            background: transparent;
            color: var(--text-secondary);
            padding: 0 12px;
            font-weight: 900;
            text-align: left;
            cursor: pointer;
            transition: .18s;
        }

        .site-cms-nav i { width: 18px; text-align: center; }
        .site-cms-nav:hover { background: #f4f5f7; color: #111; }
        .site-cms-nav.is-active {
            border-color: #111;
            background: #111;
            color: #fff;
            box-shadow: 0 12px 24px rgba(17,17,17,.12);
        }

        .site-cms-sidebar-note {
            margin: 0 10px 10px;
            border-radius: 14px;
            background: #f6f7f9;
            color: var(--text-muted);
            padding: 12px;
            font-size: 12px;
            line-height: 1.45;
        }

        .site-cms-main { display: grid; gap: 14px; min-width: 0; }

        .site-cms-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #fff;
            padding: 14px 16px;
            box-shadow: 0 14px 38px rgba(17,17,17,.05);
        }

        .site-cms-kicker {
            margin: 0 0 4px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .site-cms-toolbar h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .site-cms-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .site-cms-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .site-cms-stat {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
            padding: 16px;
            box-shadow: 0 14px 38px rgba(17,17,17,.05);
        }

        .site-cms-stat-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #111;
            color: #fff;
            margin-bottom: 12px;
        }

        .site-cms-stat small {
            display: block;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .site-cms-stat strong {
            display: block;
            margin-top: 7px;
            font-size: 26px;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -.05em;
        }

        .site-cms-stat em,
        .site-cms-action em,
        .site-cms-logo-preview em {
            display: block;
            margin-top: 8px;
            color: var(--text-muted);
            font-style: normal;
            line-height: 1.45;
        }

        .site-cms-action-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 14px;
        }

        .site-cms-action {
            display: grid;
            justify-items: start;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            color: #111;
            padding: 16px;
            text-align: left;
            cursor: pointer;
            transition: .18s;
        }

        .site-cms-action:hover {
            border-color: #111;
            box-shadow: 0 12px 24px rgba(17,17,17,.08);
            transform: translateY(-1px);
        }

        .site-cms-action span {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #111;
            color: #fff;
        }

        .site-cms-action strong { font-size: 14px; font-weight: 950; }

        .site-cms-panel-note {
            padding: 12px 16px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            background: #fafafa;
        }

        .site-cms-logo-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 12px;
            background: #fff;
        }

        .site-cms-logo-preview img,
        .site-cms-logo-preview > span {
            width: 48px;
            height: 48px;
            flex: 0 0 auto;
            object-fit: contain;
            border-radius: 14px;
        }

        .site-cms-logo-preview > span {
            display: grid;
            place-items: center;
            background: #111;
            color: #fff;
        }

        .site-cms-logo-preview strong { font-size: 15px; font-weight: 950; }
        .site-cms-banner-thumb {
            width: 150px;
            height: 52px;
            object-fit: cover;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        .site-cms-amenity-icon {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            padding: 4px;
        }

        .site-cms-seg {
            display: inline-flex;
            gap: 4px;
            padding: 4px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #f4f5f7;
        }

        .site-cms-seg-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: var(--text-secondary);
            padding: 7px 14px;
            font-weight: 900;
            font-size: 12px;
            cursor: pointer;
            transition: .15s;
        }

        .site-cms-seg-btn span {
            min-width: 20px;
            padding: 1px 6px;
            border-radius: 999px;
            background: #e3e6ea;
            color: var(--text-muted);
            font-size: 11px;
        }

        .site-cms-seg-btn.is-active { background: #111; color: #fff; }
        .site-cms-seg-btn.is-active span { background: rgba(255,255,255,.22); color: #fff; }

        .site-cms-inline-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex: 1;
        }

        .site-cms-search {
            max-width: 320px;
            min-width: 240px;
        }

        .site-cms-listing-cover,
        .site-cms-empty-cover {
            width: 96px;
            height: 64px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .site-cms-listing-cover { object-fit: cover; }

        .site-cms-empty-cover {
            display: grid;
            place-items: center;
            background: #f4f5f7;
            color: var(--text-muted);
        }

        .cms-checkbox-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .cms-checkbox-grid label {
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            padding: 0 12px;
            color: var(--text-secondary);
            font-weight: 800;
            cursor: pointer;
        }

        .cms-checkbox-grid input { accent-color: #111; }

        .site-cms-image-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
        }

        .site-cms-image-grid > div {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #f4f5f7;
            aspect-ratio: 4 / 3;
        }

        .site-cms-image-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .site-cms-image-grid button,
        .site-cms-image-grid span {
            position: absolute;
            left: 8px;
            bottom: 8px;
            border: 0;
            border-radius: 999px;
            background: rgba(17,17,17,.82);
            color: #fff;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 900;
        }

        .cms-panel {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--bg-surface);
            box-shadow: 0 14px 38px rgba(17,17,17,.05);
            overflow: hidden;
        }

        .cms-panel-head {
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            padding: 0 16px;
        }

        .cms-panel-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .cms-table-wrap { overflow-x: hidden; }
        .cms-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cms-table th {
            height: 42px;
            border-bottom: 1px solid var(--border);
            background: #fafafa;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }

        .cms-table td {
            height: 74px;
            border-bottom: 1px solid #edf0f3;
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .cms-table tr:last-child td { border-bottom: 0; }
        .cms-table th, .cms-table td { padding: 0 12px; }
        .cms-table .right { text-align: right; }
        .cms-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .cms-input, .cms-select, .cms-textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--text-primary);
            outline: none;
        }

        .cms-input, .cms-select {
            height: 42px;
            padding: 0 12px;
        }

        .cms-textarea {
            min-height: 96px;
            padding: 10px 12px;
            resize: vertical;
        }

        .cms-input:focus, .cms-select:focus, .cms-textarea:focus {
            border-color: #111;
            box-shadow: 0 0 0 3px rgba(17,17,17,.08);
        }

        .cms-btn {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid #111;
            border-radius: 999px;
            background: #fff;
            color: #111;
            padding: 0 15px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            cursor: pointer;
            transition: .18s;
            white-space: nowrap;
        }

        .cms-btn:hover { background: #111; color: #fff; }
        .cms-btn.sm {
            min-height: 32px;
            gap: 5px;
            padding: 0 10px;
            font-size: 11px;
            letter-spacing: 0;
        }
        .cms-btn.primary { background: #111; color: #fff; }
        .cms-btn.primary:hover { background: #333; }
        .cms-btn.danger { border-color: var(--danger); color: var(--danger); }
        .cms-btn.danger:hover { background: var(--danger); color: #fff; }

        .cms-icon-btn {
            width: 38px;
            height: 38px;
            display: inline-grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: #fff;
            color: #111;
            cursor: pointer;
        }

        /* Compact icon-only action buttons for table rows */
        .cms-row-actions {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }

        .cms-act {
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            display: inline-grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
            transition: .15s;
        }

        .cms-act:hover { border-color: #111; background: #111; color: #fff; }
        .cms-act.danger { color: var(--danger); }
        .cms-act.danger:hover { border-color: var(--danger); background: var(--danger); color: #fff; }
        .cms-act.ok { color: #087f3f; }
        .cms-act.ok:hover { border-color: #087f3f; background: #087f3f; color: #fff; }
        .cms-act.warn { color: #b45309; }
        .cms-act.warn:hover { border-color: #b45309; background: #b45309; color: #fff; }

        .cms-mod-select {
            appearance: none;
            -webkit-appearance: none;
            display: inline-block;
            padding: 4px 24px 4px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            background-color: #f1f2f4;
            color: var(--text-muted);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%238a929e' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            transition: border-color .15s;
            max-width: 130px;
        }
        .cms-mod-select:focus { outline: 2px solid #f4bf19; outline-offset: 1px; }
        .cms-mod-select.ok { background-color: #eaf8ef; color: #087f3f; border-color: rgba(8,127,63,.22); }
        .cms-mod-select.warn { background-color: #fff7ed; color: #b45309; border-color: rgba(180,83,9,.22); }
        .cms-mod-select.err { background-color: #fef2f2; color: #dc2626; border-color: rgba(220,38,38,.22); }

        .cms-badge {
            min-height: 28px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0 10px;
            background: #fff;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
            cursor: pointer;
        }

        .cms-badge.success { border-color: rgba(8,127,63,.22); background: #eaf8ef; color: var(--success); }
        .cms-badge.muted { background: #f1f2f4; color: var(--text-muted); }
        .cms-badge.danger { border-color: rgba(220,38,38,.22); background: #fef2f2; color: var(--danger); }
        .cms-badge.warning { border-color: rgba(180,83,9,.22); background: #fff7ed; color: #b45309; }

        .cms-pagination {
            border-top: 1px solid var(--border);
            padding: 12px 16px;
            color: var(--text-secondary);
        }

        .cms-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .cms-field { display: grid; gap: 6px; }
        .cms-field.full { grid-column: 1 / -1; }
        .cms-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .cms-error {
            color: var(--danger);
            font-size: 12px;
            font-weight: 700;
        }

        .cms-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(17,17,17,.58);
        }

        .cms-modal {
            width: min(720px, calc(100vw - 32px));
            max-height: calc(100vh - 40px);
            overflow: auto;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: #fff;
            box-shadow: 0 30px 70px rgba(0,0,0,.2);
        }

        .cms-modal.wide { width: min(980px, calc(100vw - 32px)); }

        .cms-flash {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 90;
            border: 1px solid rgba(8,127,63,.22);
            border-radius: 14px;
            background: #eaf8ef;
            color: var(--success);
            padding: 12px 14px;
            font-weight: 800;
            box-shadow: 0 14px 34px rgba(17,17,17,.12);
        }

        @media (max-width: 1024px) {
            .site-cms { grid-template-columns: 1fr; }
            .site-cms-sidebar { position: static; }
            .site-cms-nav-list { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .site-cms-nav { justify-content: center; }
            .site-cms-sidebar-note { display: none; }
            .site-cms-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 720px) {
            .site-admin-bar-inner {
                min-height: 64px;
                padding: 10px 0;
                align-items: flex-start;
                flex-direction: column;
            }

            .site-admin-actions,
            .site-cms-toolbar-actions { width: 100%; }
            .site-admin-actions .cms-btn,
            .site-cms-toolbar-actions .cms-btn,
            .site-admin-actions form { flex: 1; }
            .site-admin-actions form .cms-btn { width: 100%; }
            .site-cms-toolbar { align-items: flex-start; flex-direction: column; }
            .site-cms-toolbar h1 { font-size: 20px; }
            .site-cms-inline-actions { width: 100%; flex-direction: column; align-items: stretch; }
            .site-cms-search { max-width: none; min-width: 0; }
            .site-cms-nav-list { grid-template-columns: 1fr 1fr; }
            .site-cms-nav { justify-content: flex-start; }
            .site-cms-stat-grid,
            .site-cms-action-grid,
            .cms-checkbox-grid,
            .site-cms-image-grid,
            .cms-form-grid { grid-template-columns: 1fr; }
            .cms-field.full { grid-column: auto; }
            .cms-panel-head {
                min-height: auto;
                padding: 14px;
                align-items: flex-start;
                flex-direction: column;
            }
            .cms-btn { min-width: 0; }
        }
    </style>
</head>
<body>
    <div class="site-admin-shell">
        <header class="site-admin-topbar">
            <div class="site-admin-bar-inner">
                <div class="site-admin-brand">
                    <span class="site-admin-brand-mark"><i class="fa-solid fa-house"></i></span>
                    <div>
                        <div>QUẢN TRỊ SITE NHÀ TRỌ</div>
                        <span class="site-admin-subtitle">CMS riêng cho trang chủ nhà trọ</span>
                    </div>
                </div>
                <div class="site-admin-actions">
                    <a class="cms-btn" href="{{ route('site.home') }}" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trang chủ</a>
                    <a class="cms-btn" href="{{ route('site.admin', ['tab' => 'listings']) }}"><i class="fa-regular fa-rectangle-list"></i> Tin đăng</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="cms-btn" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="site-admin-content">
            {{ $slot }}
        </main>
    </div>

    @if (session()->has('message'))
        <div class="cms-flash">{{ session('message') }}</div>
    @endif

    <script>
        // Đăng ký Alpine imageUploader trước khi Alpine khởi động.
        // Phải nằm ở đây (layout) vì component x-image-uploader chỉ được render trong modal (có điều kiện)
        // nên script của nó không chạy khi trang load lần đầu.
        document.addEventListener('alpine:init', function () {
            Alpine.data('imageUploader', function (opts) {
                return {
                    maxKB: opts.maxKB, multiple: opts.multiple, model: opts.model,
                    files: [], previews: [], errors: [], uploading: false, progress: 0,
                    onChange: function (e) {
                        this.errors = [];
                        var self = this;
                        var selected = Array.from(e.target.files || []);
                        e.target.value = '';
                        var accepted = [];
                        for (var i = 0; i < selected.length; i++) {
                            var f = selected[i];
                            if (f.size > self.maxKB * 1024) {
                                self.errors.push('"' + f.name + '" ' + (f.size / 1048576).toFixed(1) + 'MB vượt quá ' + (self.maxKB / 1024).toFixed(0) + 'MB');
                                continue;
                            }
                            accepted.push(f);
                        }
                        if (!accepted.length) return;
                        Promise.all(accepted.map(function (f) { return self.compress(f); })).then(function (done) {
                            if (self.multiple) { self.files.push.apply(self.files, done); }
                            else { self.files = done.slice(0, 1); }
                            self.rebuildPreviews();
                            self.sync();
                        });
                    },
                    rebuildPreviews: function () {
                        this.previews.forEach(function (p) { URL.revokeObjectURL(p.url); });
                        this.previews = this.files.map(function (f) { return { url: URL.createObjectURL(f) }; });
                    },
                    removePreview: function (i) {
                        this.files.splice(i, 1);
                        this.rebuildPreviews();
                        this.sync();
                    },
                    sync: function () {
                        this.uploading = true; this.progress = 0;
                        var self = this;
                        var done = function () { self.uploading = false; };
                        var prog = function (ev) { self.progress = (ev.detail && ev.detail.progress) || 0; };
                        if (this.multiple) {
                            if (this.files.length) this.$wire.uploadMultiple(this.model, this.files, done, done, prog);
                            else { this.$wire.set(this.model, []); done(); }
                        } else {
                            if (this.files[0]) this.$wire.upload(this.model, this.files[0], done, done, prog);
                            else { this.$wire.set(this.model, null); done(); }
                        }
                    },
                    compress: function (file) {
                        return new Promise(function (resolve) {
                            if (!file.type || !file.type.startsWith('image/')) return resolve(file);
                            var url = URL.createObjectURL(file);
                            var img = new Image();
                            img.onload = function () {
                                URL.revokeObjectURL(url);
                                var max = 1600, w = img.width, h = img.height;
                                if (w > max || h > max) {
                                    if (w >= h) { h = Math.round(h * max / w); w = max; }
                                    else { w = Math.round(w * max / h); h = max; }
                                }
                                try {
                                    var c = document.createElement('canvas');
                                    c.width = w; c.height = h;
                                    c.getContext('2d').drawImage(img, 0, 0, w, h);
                                    c.toBlob(function (blob) {
                                        if (!blob || blob.size >= file.size) return resolve(file);
                                        resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                                    }, 'image/jpeg', 0.82);
                                } catch (err) { resolve(file); }
                            };
                            img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
                            img.src = url;
                        });
                    },
                };
            });
            window._iuDefined = true;
        });
    </script>

    @livewireScripts
</body>
</html>
