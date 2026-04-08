<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'SIPR Group')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap');

        :root {
            --bg-0: #070c16;
            --bg-1: #0d1424;
            --surface: rgba(16, 23, 40, 0.8);
            --surface-2: rgba(22, 30, 52, 0.86);
            --surface-3: #1a2440;
            --line: rgba(150, 177, 222, 0.2);
            --line-strong: rgba(150, 177, 222, 0.34);
            --text: #e9eefb;
            --muted: #97a7c8;
            --accent: #3ecf8e;
            --accent-2: #4ca9ff;
            --warning: #f7bf4d;
            --danger: #ff6b7f;
            --shadow-soft: 0 10px 34px rgba(4, 8, 19, 0.45);
            --shadow-strong: 0 24px 60px rgba(3, 9, 22, 0.62);
            --radius-lg: 22px;
            --radius-md: 14px;
            --radius-sm: 10px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(76, 169, 255, 0.22), transparent 60%),
                radial-gradient(900px 500px at 0% 0%, rgba(62, 207, 142, 0.16), transparent 58%),
                linear-gradient(160deg, var(--bg-0), var(--bg-1));
        }

        a {
            color: #91c5ff;
            text-decoration: none;
            transition: color .18s ease;
        }

        a:hover {
            color: #c1ddff;
        }

        .shell {
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        .sidebar {
            width: 258px;
            background: linear-gradient(180deg, rgba(10, 15, 30, 0.94), rgba(12, 18, 34, 0.92));
            backdrop-filter: blur(14px);
            border-right: 1px solid var(--line);
            position: fixed;
            inset: 0 auto 0 0;
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            z-index: 30;
            overflow: auto;
            box-shadow: var(--shadow-soft);
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 16, 0.66);
            backdrop-filter: blur(2px);
            z-index: 20;
        }

        .brand {
            padding: 10px 8px 16px;
            border-bottom: 1px solid var(--line);
            text-align: center;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            margin: 0 auto 10px;
            border-radius: 16px;
            background: linear-gradient(135deg, #184f83, #3ecf8e);
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 8px 24px rgba(62, 207, 142, 0.25);
        }

        .brand-title {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .brand-sub {
            font-size: 9px;
            letter-spacing: .28em;
            color: var(--muted);
            margin-top: 6px;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            transition: .2s transform, .2s background, .2s color, .2s border-color;
        }

        .nav-item:hover {
            color: #d6e7ff;
            border-color: rgba(124, 186, 255, 0.25);
            background: rgba(76, 169, 255, 0.1);
            transform: translateX(2px);
        }

        .nav-item.active {
            color: #ecf5ff;
            border-color: rgba(124, 186, 255, 0.38);
            background: linear-gradient(110deg, rgba(33, 82, 141, 0.34), rgba(17, 55, 95, 0.2));
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 50%;
            width: 3px;
            height: 56%;
            border-radius: 999px;
            transform: translateY(-50%);
            background: linear-gradient(180deg, #53d59a, #4ca9ff);
        }

        .nav-emoji {
            width: 22px;
            text-align: center;
            filter: saturate(1.2);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            padding: 10px 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: linear-gradient(135deg, #245b94, #49c997);
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #fff;
            flex: none;
        }

        .user-meta {
            min-width: 0;
            flex: 1;
        }

        .user-name {
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .13em;
        }

        .signout {
            width: 100%;
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 107, 127, 0.36);
            background: rgba(255, 107, 127, 0.08);
            color: #ff8fa1;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: .2s background, .2s color;
        }

        .signout:hover {
            background: rgba(255, 107, 127, 0.18);
            color: #ffd0d7;
        }

        .main {
            margin-left: 258px;
            flex: 1;
            padding: 24px 26px 34px;
            min-width: 0;
            animation: fadeIn .35s ease;
        }

        .topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .titleblock {
            min-width: 0;
            flex: 1;
        }

        .eyebrow {
            font-size: 10px;
            letter-spacing: .24em;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .page-title {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 31px;
            line-height: 1.15;
            font-weight: 700;
            margin: 0;
            word-break: break-word;
            text-wrap: balance;
        }

        .page-sub {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
            max-width: 70ch;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .icon-btn,
        .ghost-btn,
        .primary-btn,
        .danger-btn,
        .soft-btn {
            font: inherit;
            border-radius: var(--radius-sm);
            border: 1px solid var(--line);
            cursor: pointer;
            transition: .2s transform, .2s box-shadow, .2s border-color, .2s background;
        }

        .icon-btn {
            display: none;
            width: 42px;
            height: 42px;
            background: var(--surface-2);
            color: var(--text);
        }

        .icon-btn:hover {
            transform: translateY(-1px);
        }

        .profile {
            position: relative;
            z-index: 120;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            color: var(--text);
            border-radius: 12px;
            box-shadow: var(--shadow-soft);
        }

        .profile-drop {
            display: none;
            position: absolute;
            right: 0;
            top: 52px;
            width: 248px;
            background: rgba(17, 24, 42, 0.97);
            border: 1px solid var(--line-strong);
            border-radius: 16px;
            padding: 10px;
            box-shadow: var(--shadow-strong);
            z-index: 130;
        }

        .profile.open .profile-drop {
            display: block;
        }

        .profile-item {
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 10px 12px;
            margin-top: 8px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--text);
            font-weight: 600;
            text-decoration: none;
        }

        .profile-drop form {
            width: 100%;
            margin: 0;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: var(--shadow-soft);
        }

        .card-pad {
            padding: 18px;
        }

        .hero {
            background:
                radial-gradient(90% 130% at 0% 0%, rgba(62, 207, 142, 0.2), transparent 50%),
                linear-gradient(135deg, rgba(24, 41, 76, 0.92), rgba(16, 26, 46, 0.95));
            border: 1px solid rgba(110, 164, 232, 0.32);
            border-radius: var(--radius-lg);
            padding: 24px 24px;
            box-shadow: var(--shadow-strong);
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            right: -100px;
            top: -100px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(76, 169, 255, 0.16), transparent 70%);
            pointer-events: none;
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-kicker {
            font-size: 10px;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: #a3c1e5;
            font-weight: 700;
        }

        .hero h2 {
            margin: 8px 0 10px;
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 31px;
            line-height: 1.1;
        }

        .hero p {
            margin: 0;
            color: #b7c8e8;
        }

        .grid {
            display: grid;
            gap: 14px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .auto-fit {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .kpi {
            background: linear-gradient(180deg, rgba(28, 39, 67, 0.66), rgba(22, 30, 52, 0.72));
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            transition: .2s transform, .2s border-color, .2s box-shadow;
        }

        .kpi:hover {
            transform: translateY(-2px);
            border-color: var(--line-strong);
            box-shadow: 0 14px 32px rgba(6, 13, 30, 0.42);
        }

        .kpi .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .2em;
            color: var(--muted);
            font-weight: 700;
        }

        .kpi .value {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 28px;
            font-weight: 700;
            margin-top: 7px;
        }

        .kpi .note {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
        }

        .section-title {
            margin: 0 0 13px;
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            box-shadow: var(--shadow-soft);
        }

        .panel+.panel {
            margin-top: 14px;
        }

        .muted {
            color: var(--muted);
        }

        .alert {
            border-radius: 13px;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid transparent;
            animation: fadeIn .2s ease;
        }

        .alert-success {
            background: rgba(62, 207, 142, 0.14);
            border-color: rgba(62, 207, 142, 0.38);
            color: #b6f0d2;
        }

        .alert-error {
            background: rgba(255, 107, 127, 0.14);
            border-color: rgba(255, 107, 127, 0.4);
            color: #ffd0d7;
        }

        .btn-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .primary-btn,
        .ghost-btn,
        .soft-btn,
        .danger-btn {
            padding: 10px 14px;
            font-weight: 700;
        }

        .primary-btn {
            background: linear-gradient(135deg, #1f619a, #3ecf8e);
            color: #f7feff;
            border: none;
            box-shadow: 0 8px 24px rgba(62, 207, 142, 0.22);
        }

        .primary-btn:hover {
            transform: translateY(-1px);
        }

        .ghost-btn {
            background: var(--surface-2);
            color: var(--text);
        }

        .soft-btn {
            background: rgba(76, 169, 255, 0.14);
            color: #d8ebff;
            border-color: rgba(76, 169, 255, 0.28);
        }

        .danger-btn {
            background: rgba(255, 107, 127, 0.1);
            color: #ffb8c3;
            border-color: rgba(255, 107, 127, 0.38);
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .label {
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
        }

        .input,
        .select,
        .textarea {
            width: 100%;
            background: rgba(20, 30, 53, 0.74);
            border: 1px solid var(--line);
            border-radius: 11px;
            padding: 11px 12px;
            color: var(--text);
            font: inherit;
            outline: none;
            transition: .2s border-color, .2s box-shadow, .2s background;
        }

        .input:focus,
        .select:focus,
        .textarea:focus {
            border-color: rgba(76, 169, 255, 0.65);
            box-shadow: 0 0 0 3px rgba(76, 169, 255, 0.18);
            background: rgba(22, 34, 58, 0.9);
        }

        .textarea {
            min-height: 96px;
            resize: vertical;
        }

        .table-wrap {
            overflow: auto;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(15, 22, 38, 0.55);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
            min-width: 660px;
        }

        .table th,
        .table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
            white-space: nowrap;
        }

        .table th {
            position: sticky;
            top: 0;
            z-index: 1;
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--muted);
            background: rgba(20, 28, 47, 0.95);
            backdrop-filter: blur(6px);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .pill-blue {
            background: rgba(76, 169, 255, 0.14);
            color: #b7daff;
            border: 1px solid rgba(76, 169, 255, 0.34);
        }

        .pill-green {
            background: rgba(62, 207, 142, 0.14);
            color: #b4edce;
            border: 1px solid rgba(62, 207, 142, 0.34);
        }

        .pill-red {
            background: rgba(255, 107, 127, 0.14);
            color: #ffd0d7;
            border: 1px solid rgba(255, 107, 127, 0.38);
        }

        .empty {
            padding: 18px;
            text-align: center;
            color: var(--muted);
        }

        .page-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .page-stack>* {
            animation: riseIn .34s ease both;
        }

        .page-stack>*:nth-child(2) {
            animation-delay: .04s;
        }

        .page-stack>*:nth-child(3) {
            animation-delay: .08s;
        }

        .page-stack>*:nth-child(4) {
            animation-delay: .12s;
        }

        .top-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        details summary {
            list-style: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        details summary::-webkit-details-marker {
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1180px) {
            .grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-104%);
                transition: transform .24s ease;
                box-shadow: var(--shadow-strong);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-backdrop.open {
                display: block;
            }

            .main {
                margin-left: 0;
                padding: 16px 14px 28px;
            }

            .icon-btn {
                display: inline-grid;
                place-items: center;
            }

            .topbar {
                margin-bottom: 14px;
            }

            .toolbar {
                width: 100%;
                justify-content: flex-start;
            }

            .profile {
                margin-left: auto;
            }

            .profile-drop {
                right: 0;
                left: auto;
                width: min(92vw, 320px);
            }

            .hero {
                padding: 18px;
                border-radius: 18px;
            }

            .hero h2 {
                font-size: 24px;
            }

            .hero-top {
                flex-direction: column;
            }

            .grid-4,
            .grid-3,
            .grid-2 {
                grid-template-columns: 1fr;
            }

            .top-tools {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-row {
                width: 100%;
            }

            .btn-row>*,
            .top-tools form,
            .top-tools .select,
            .top-tools .input,
            .top-tools .primary-btn,
            .top-tools .soft-btn,
            .top-tools .ghost-btn,
            .top-tools .danger-btn {
                width: 100%;
            }

            .table th,
            .table td {
                padding: 10px 12px;
            }
        }

        @media (max-width: 560px) {
            .main {
                padding: 12px 10px 22px;
            }

            .hero h2 {
                font-size: 22px;
            }

            .page-title {
                font-size: 22px;
            }

            .page-sub {
                font-size: 12px;
            }

            .panel,
            .kpi {
                padding: 14px;
            }

            .profile-btn {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="brand-mark">S</div>
                <div class="brand-title">SIPR GROUP</div>
                <div class="brand-sub">INVEST • GROW • PROSPER</div>
            </div>

            @php
                $access = \App\Support\RoleAccess::class;
                $role = auth()->user()->role->value ?? auth()->user()->role;
                $canAccess = fn(string $option): bool => $access::allows($role, $option);
                $unreadCount = auth()->user()->unreadNotifications()->count();
            @endphp

            <div class="nav-group">
                @if ($canAccess('dashboard'))
                    <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}"><span class="nav-emoji">⬡</span>Dashboard</a>
                @endif
                @if ($canAccess('transactions'))
                    <a class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}"
                        href="{{ route('transactions.index') }}"><span class="nav-emoji">⇅</span>Transactions</a>
                @endif
                @if ($canAccess('wallets'))
                    <a class="nav-item {{ request()->routeIs('wallets.*') ? 'active' : '' }}"
                        href="{{ route('wallets.index') }}"><span class="nav-emoji">💰</span>Wallets</a>
                @endif
                @if ($canAccess('investments'))
                    <a class="nav-item {{ request()->routeIs('investments.*') ? 'active' : '' }}"
                        href="{{ route('investments.index') }}"><span class="nav-emoji">📈</span>Investments</a>
                @endif
                @if ($canAccess('members'))
                    <a class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}"
                        href="{{ route('members.index') }}"><span class="nav-emoji">○</span>Members</a>
                @endif
                @if ($canAccess('noticeboard'))
                    <a class="nav-item {{ request()->routeIs('noticeboard.*') || request()->routeIs('announcements.*') || request()->routeIs('proposals.*') ? 'active' : '' }}"
                        href="{{ route('noticeboard.index') }}"><span class="nav-emoji">🔔</span>Noticeboard</a>
                @endif
                @if ($canAccess('documents'))
                    <a class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}"
                        href="{{ route('documents.index') }}"><span class="nav-emoji">📄</span>Documents</a>
                @endif
                @if ($canAccess('reports'))
                    <a class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                        href="{{ route('reports.index') }}"><span class="nav-emoji">🧾</span>Reports</a>
                @endif
                @if ($canAccess('activities'))
                    <a class="nav-item {{ request()->routeIs('activities.*') ? 'active' : '' }}"
                        href="{{ route('activities.index') }}"><span class="nav-emoji">🗂</span>Activity</a>
                @endif
                @if ($canAccess('notifications'))
                    <a class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                        href="{{ route('notifications.index') }}"><span class="nav-emoji">🕐</span>Notifications <span
                            class="pill pill-blue" style="margin-left:auto">{{ $unreadCount }}</span></a>
                @endif
                @if ($canAccess('approvals'))
                    <a class="nav-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}"
                        href="{{ route('admin.approvals.index') }}"><span class="nav-emoji">⚙</span>Approvals</a>
                @endif
                @if ($canAccess('access_control'))
                    <a class="nav-item {{ request()->routeIs('admin.access-control.*') ? 'active' : '' }}"
                        href="{{ route('admin.access-control.index') }}"><span class="nav-emoji">🛡</span>Access Control</a>
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="user-chip">
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-meta">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ $role }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="signout">Sign Out</button>
                </form>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <button class="icon-btn" type="button" onclick="toggleSidebar()">☰</button>
                <div class="titleblock">
                    <div class="eyebrow">SIPR SYSTEM</div>
                    <h1 class="page-title">@yield('pageTitle', 'Dashboard')</h1>
                    @hasSection('pageSubtitle')
                        <p class="page-sub">@yield('pageSubtitle')</p>
                    @endif
                </div>
                <div class="toolbar">
                    <div class="profile" id="profileMenu">
                        <button type="button" class="profile-btn" id="profileToggleBtn" aria-expanded="false">
                            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <div style="text-align:left">
                                <div style="font-size:13px;font-weight:800">{{ auth()->user()->name }}</div>
                                <div
                                    style="font-size:10px;color:var(--mut);text-transform:uppercase;letter-spacing:.12em">
                                    {{ $role }}</div>
                            </div>
                        </button>
                        <div class="profile-drop">
                            <div style="padding:4px 4px 10px;border-bottom:1px solid var(--line)">
                                <div style="font-size:13px;font-weight:800">{{ auth()->user()->member_id }}</div>
                                <div style="font-size:11px;color:var(--mut)">{{ auth()->user()->email }}</div>
                            </div>
                            <a class="profile-item" href="{{ route('profile.show') }}">My Profile</a>
                            @if ($canAccess('wallets'))
                                <a class="profile-item" href="{{ route('wallets.index') }}">My Wallet</a>
                            @endif
                            @if ($canAccess('activities'))
                                <a class="profile-item" href="{{ route('activities.index') }}">Activity Log</a>
                            @endif
                            @if ($canAccess('notifications'))
                                <a class="profile-item" href="{{ route('notifications.index') }}">Notifications</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="profile-item danger-btn">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
    <script>
        function toggleProfileMenu(forceState) {
            const profile = document.getElementById('profileMenu');
            const trigger = document.getElementById('profileToggleBtn');
            if (!profile || !trigger) {
                return;
            }

            const shouldOpen = typeof forceState === 'boolean' ? forceState : !profile.classList.contains('open');
            profile.classList.toggle('open', shouldOpen);
            trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }

        function toggleSidebar(forceState) {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const shouldOpen = typeof forceState === 'boolean' ? forceState : !sidebar.classList.contains('open');

            sidebar.classList.toggle('open', shouldOpen);
            backdrop.classList.toggle('open', shouldOpen);
            document.body.style.overflow = shouldOpen ? 'hidden' : '';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                toggleSidebar(false);
                toggleProfileMenu(false);
            }
        });

        document.getElementById('profileToggleBtn')?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleProfileMenu();
        });

        document.addEventListener('click', function (event) {
            const profile = document.getElementById('profileMenu');
            if (profile && !profile.contains(event.target)) {
                toggleProfileMenu(false);
            }
        });
    </script>
</body>

</html>