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
        :root{--bg:#07090f;--sur:#0e1118;--s2:#13161e;--bor:#1c2030;--txt:#e6e9f0;--mut:#6a7388;--grn:#4f8ef7;--gdm:#0a1226;--gbr:#0d1f4a;--red:#ef4444;--gold:#f59e0b;--teal:#14b8a6;--shadow:0 16px 48px rgba(0,0,0,.45)}
        *{box-sizing:border-box}
        html,body{margin:0;padding:0}
        body{font-family:'Plus Jakarta Sans',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:
            radial-gradient(ellipse at 20% 0%,rgba(13,31,74,.9),transparent 35%),
            linear-gradient(180deg,#07090f 0%,#090c14 100%);
            color:var(--txt);min-height:100vh;overflow-x:hidden}
        a{color:#7cb0ff;text-decoration:none}
        a:hover{text-decoration:underline}
        .shell{min-height:100vh;display:flex;position:relative}
        .sidebar{width:240px;background:rgba(14,17,24,.92);backdrop-filter:blur(10px);border-right:1px solid var(--bor);position:fixed;inset:0 auto 0 0;padding:18px 12px;display:flex;flex-direction:column;gap:14px;z-index:30;overflow:auto}
        .sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(3,7,18,.62);backdrop-filter:blur(2px);z-index:20}
        .brand{padding:6px 8px 14px;border-bottom:1px solid var(--bor);text-align:center}
        .brand-mark{width:44px;height:44px;margin:0 auto 10px;border-radius:14px;background:linear-gradient(135deg,var(--gbr),var(--grn));display:grid;place-items:center;font-weight:900;color:#fff}
        .brand-title{font-size:14px;font-weight:900;letter-spacing:.08em}
        .brand-sub{font-size:9px;letter-spacing:.24em;color:var(--mut);margin-top:4px}
        .nav-group{display:flex;flex-direction:column;gap:4px}
        .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;color:var(--mut);font-size:13px;font-weight:700;transition:.15s background,.15s color}
        .nav-item:hover,.nav-item.active{background:var(--gdm);color:#dce8ff}
        .nav-item.active{border:1px solid var(--gbr)}
        .nav-emoji{width:22px;text-align:center}
        .sidebar-footer{margin-top:auto;padding-top:14px;border-top:1px solid var(--bor)}
        .user-chip{display:flex;align-items:center;gap:10px;background:var(--s2);border:1px solid var(--bor);border-radius:14px;padding:10px 12px}
        .avatar{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--gbr),var(--grn));display:grid;place-items:center;font-weight:900;color:#fff;flex:none}
        .user-meta{min-width:0;flex:1}
        .user-name{font-size:13px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .user-role{font-size:10px;color:var(--mut);text-transform:uppercase;letter-spacing:.12em}
        .signout{width:100%;margin-top:10px;padding:10px 12px;border-radius:12px;border:1px solid rgba(127,29,29,.35);background:rgba(26,4,6,.55);color:#fb7185;font-weight:800;cursor:pointer;font-family:inherit}
        .main{margin-left:240px;flex:1;padding:22px 24px 32px;min-width:0}
        .topbar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap}
        .titleblock{min-width:0;flex:1}
        .eyebrow{font-size:10px;letter-spacing:.24em;color:var(--mut);text-transform:uppercase;font-weight:800;margin-bottom:4px}
        .page-title{font-size:28px;line-height:1.15;font-weight:900;margin:0;word-break:break-word}
        .page-sub{margin:6px 0 0;color:var(--mut);font-size:13px}
        .toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
        .icon-btn,.ghost-btn,.primary-btn,.danger-btn,.soft-btn{font:inherit;border-radius:12px;border:1px solid var(--bor);cursor:pointer}
        .icon-btn{display:none;width:42px;height:42px;background:var(--s2);color:var(--txt)}
        .profile{position:relative}
        .profile-btn{display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--sur);border:1px solid var(--bor);color:var(--txt);box-shadow:var(--shadow)}
        .profile-drop{display:none;position:absolute;right:0;top:50px;width:240px;background:var(--sur);border:1px solid var(--bor);border-radius:16px;padding:10px;box-shadow:var(--shadow);z-index:20}
        .profile.open .profile-drop{display:block}
        .profile-item{width:100%;text-align:left;padding:10px 12px;margin-top:8px;background:var(--s2);border:1px solid var(--bor);border-radius:12px;color:var(--txt);font-weight:700}
        .card{background:rgba(14,17,24,.94);border:1px solid var(--bor);border-radius:18px;box-shadow:var(--shadow)}
        .card-pad{padding:18px}
        .hero{background:linear-gradient(135deg,rgba(10,18,38,.98),rgba(13,20,36,.98));border:1px solid var(--gbr);border-radius:22px;padding:22px 24px;box-shadow:var(--shadow)}
        .hero-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .hero-kicker{font-size:10px;letter-spacing:.28em;text-transform:uppercase;color:#8ea6d9;font-weight:900}
        .hero h2{margin:8px 0 10px;font-size:30px;line-height:1.1}
        .hero p{margin:0;color:#b8c3dc}
        .grid{display:grid;gap:14px}
        .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
        .grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
        .grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}
        .auto-fit{grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
        @media (max-width: 1100px){.grid-4{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width: 900px){.sidebar{transform:translateX(-102%);transition:transform .22s ease;box-shadow:var(--shadow)}.sidebar.open{transform:translateX(0)}.sidebar-backdrop.open{display:block}.main{margin-left:0;padding:16px 14px 28px}.icon-btn{display:inline-grid;place-items:center}.topbar{margin-bottom:14px}.toolbar{width:100%;justify-content:flex-start}.profile{margin-left:auto}.profile-drop{right:0;left:auto;width:min(92vw,320px)}.hero{padding:18px}.hero h2{font-size:24px}.hero-top{flex-direction:column}.grid-4,.grid-3,.grid-2{grid-template-columns:1fr}.top-tools{flex-direction:column;align-items:stretch}.btn-row{width:100%}.btn-row > *,.top-tools form,.top-tools .select,.top-tools .input,.top-tools .primary-btn,.top-tools .soft-btn,.top-tools .ghost-btn,.top-tools .danger-btn{width:100%}.table th,.table td{padding:10px 12px}.table{min-width:720px}}
        @media (max-width: 560px){.main{padding:12px 10px 22px}.hero h2{font-size:22px}.page-title{font-size:22px}.page-sub{font-size:12px}.panel{padding:14px}.kpi{padding:14px}.auth-card{padding:22px;border-radius:20px}.auth-shell{padding:16px}.profile-btn{width:100%;justify-content:flex-start}}
        .kpi{background:var(--s2);border:1px solid var(--bor);border-radius:16px;padding:16px}
        .kpi .label{font-size:10px;text-transform:uppercase;letter-spacing:.22em;color:var(--mut);font-weight:900}
        .kpi .value{font-size:26px;font-weight:900;margin-top:6px}
        .kpi .note{margin-top:6px;color:var(--mut);font-size:13px}
        .section-title{margin:0 0 12px;font-size:18px;font-weight:900}
        .panel{background:rgba(19,22,30,.86);border:1px solid var(--bor);border-radius:18px;padding:16px;box-shadow:var(--shadow)}
        .panel + .panel{margin-top:14px}
        .muted{color:var(--mut)}
        .alert{border-radius:14px;padding:12px 14px;margin-bottom:14px;border:1px solid transparent}
        .alert-success{background:rgba(4,120,87,.15);border-color:rgba(16,185,129,.35);color:#9ee8c6}
        .alert-error{background:rgba(127,29,29,.18);border-color:rgba(239,68,68,.4);color:#fecaca}
        .btn-row{display:flex;gap:10px;flex-wrap:wrap}
        .primary-btn,.ghost-btn,.soft-btn,.danger-btn{padding:10px 14px;font-weight:800}
        .primary-btn{background:linear-gradient(135deg,var(--gbr),var(--grn));color:#fff;border:none}
        .ghost-btn{background:var(--s2);color:var(--txt)}
        .soft-btn{background:var(--gdm);color:#dbeafe;border-color:var(--gbr)}
        .danger-btn{background:rgba(26,4,6,.56);color:#fb7185;border-color:rgba(127,29,29,.35)}
        .field{display:flex;flex-direction:column;gap:6px}
        .label{font-size:10px;letter-spacing:.22em;text-transform:uppercase;color:var(--mut);font-weight:900}
        .input,.select,.textarea{width:100%;background:var(--s2);border:1px solid var(--bor);border-radius:12px;padding:11px 12px;color:var(--txt);font:inherit;outline:none}
        .input:focus,.select:focus,.textarea:focus{border-color:var(--grn)}
        .textarea{min-height:92px;resize:vertical}
        .table-wrap{overflow:auto;border-radius:16px;border:1px solid var(--bor)}
        .table{width:100%;border-collapse:collapse;background:rgba(14,17,24,.94)}
        .table th,.table td{padding:12px 14px;border-bottom:1px solid var(--bor);text-align:left;vertical-align:top}
        .table th{font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--mut);background:rgba(19,22,30,.96)}
        .table tr:last-child td{border-bottom:none}
        .pill{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800}
        .pill-blue{background:rgba(79,142,247,.14);color:#a8c8ff;border:1px solid rgba(79,142,247,.25)}
        .pill-green{background:rgba(16,185,129,.14);color:#9ee8c6;border:1px solid rgba(16,185,129,.25)}
        .pill-red{background:rgba(239,68,68,.14);color:#fecaca;border:1px solid rgba(239,68,68,.25)}
        .empty{padding:18px;text-align:center;color:var(--mut)}
        .page-stack{display:flex;flex-direction:column;gap:16px}
        .top-tools{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
        .stack{display:flex;flex-direction:column;gap:10px}
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
            $role = auth()->user()->role->value ?? auth()->user()->role;
            $isAdmin = $role === 'admin';
            $isFinance = in_array($role, ['admin', 'finance'], true);
            $isPrivileged = in_array($role, ['admin', 'finance', 'secretary'], true);
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        <div class="nav-group">
            <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-emoji">⬡</span>Dashboard</a>
            <a class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}"><span class="nav-emoji">⇅</span>Transactions</a>
            <a class="nav-item {{ request()->routeIs('wallets.*') ? 'active' : '' }}" href="{{ route('wallets.index') }}"><span class="nav-emoji">💰</span>Wallets</a>
            <a class="nav-item {{ request()->routeIs('investments.*') ? 'active' : '' }}" href="{{ route('investments.index') }}"><span class="nav-emoji">📈</span>Investments</a>
            <a class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}"><span class="nav-emoji">○</span>Members</a>
            <a class="nav-item {{ request()->routeIs('noticeboard.*') || request()->routeIs('announcements.*') || request()->routeIs('proposals.*') ? 'active' : '' }}" href="{{ route('noticeboard.index') }}"><span class="nav-emoji">🔔</span>Noticeboard</a>
            <a class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}"><span class="nav-emoji">📄</span>Documents</a>
            <a class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span class="nav-emoji">🧾</span>Reports</a>
            @if ($isPrivileged)
                <a class="nav-item {{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}"><span class="nav-emoji">🗂</span>Activity</a>
            @endif
            <a class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><span class="nav-emoji">🕐</span>Notifications <span class="pill pill-blue" style="margin-left:auto">{{ $unreadCount }}</span></a>
            @if ($isAdmin)
                <a class="nav-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}" href="{{ route('admin.approvals.index') }}"><span class="nav-emoji">⚙</span>Approvals</a>
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
                    <button type="button" class="profile-btn" onclick="document.getElementById('profileMenu').classList.toggle('open')">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <div style="text-align:left">
                            <div style="font-size:13px;font-weight:800">{{ auth()->user()->name }}</div>
                            <div style="font-size:10px;color:var(--mut);text-transform:uppercase;letter-spacing:.12em">{{ $role }}</div>
                        </div>
                    </button>
                    <div class="profile-drop">
                        <div style="padding:4px 4px 10px;border-bottom:1px solid var(--bor)">
                            <div style="font-size:13px;font-weight:800">{{ auth()->user()->member_id }}</div>
                            <div style="font-size:11px;color:var(--mut)">{{ auth()->user()->email }}</div>
                        </div>
                        <a class="profile-item" href="{{ route('profile.show') }}">My Profile</a>
                        <a class="profile-item" href="{{ route('wallets.index') }}">My Wallet</a>
                        @if ($isPrivileged)
                            <a class="profile-item" href="{{ route('activities.index') }}">Activity Log</a>
                        @endif
                        <a class="profile-item" href="{{ route('notifications.index') }}">Notifications</a>
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
            const profile = document.getElementById('profileMenu');
            if (profile) {
                profile.classList.remove('open');
            }
        }
    });

    document.addEventListener('click', function (event) {
        const profile = document.getElementById('profileMenu');
        if (profile && !profile.contains(event.target)) {
            profile.classList.remove('open');
        }
    });
</script>
</body>
</html>