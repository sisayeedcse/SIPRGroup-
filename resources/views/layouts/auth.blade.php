<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'SIPR Access')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root{--bg:#07090f;--sur:#0e1118;--s2:#13161e;--bor:#1c2030;--txt:#e6e9f0;--mut:#6a7388;--grn:#4f8ef7;--gdm:#0a1226;--gbr:#0d1f4a;--red:#ef4444;--shadow:0 20px 54px rgba(0,0,0,.48)}
        *{box-sizing:border-box}
        html,body{margin:0;padding:0}
        body{font-family:'Plus Jakarta Sans',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:radial-gradient(ellipse at 20% 0%,rgba(13,31,74,.85),transparent 35%),linear-gradient(180deg,#07090f 0%,#090c14 100%);color:var(--txt);min-height:100vh}
        a{color:#7cb0ff;text-decoration:none}
        .auth-shell{min-height:100vh;display:grid;place-items:center;padding:24px}
        .auth-card{width:min(100%,460px);background:rgba(14,17,24,.94);border:1px solid var(--bor);border-radius:24px;padding:28px;box-shadow:var(--shadow)}
        .auth-brand{display:flex;flex-direction:column;align-items:center;text-align:center;margin-bottom:24px}
        .auth-mark{width:58px;height:58px;border-radius:18px;background:linear-gradient(135deg,var(--gbr),var(--grn));display:grid;place-items:center;font-size:24px;font-weight:900;margin-bottom:12px}
        .auth-title{font-size:22px;font-weight:900;margin:0}
        .auth-sub{margin-top:6px;font-size:11px;letter-spacing:.26em;text-transform:uppercase;color:var(--mut)}
        .auth-alert{border-radius:14px;padding:12px 14px;margin-bottom:14px;border:1px solid transparent}
        .auth-success{background:rgba(4,120,87,.15);border-color:rgba(16,185,129,.35);color:#9ee8c6}
        .auth-error{background:rgba(127,29,29,.18);border-color:rgba(239,68,68,.4);color:#fecaca}
        .field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
        .label{font-size:10px;letter-spacing:.22em;text-transform:uppercase;color:var(--mut);font-weight:900}
        .input,.select,.textarea{width:100%;background:var(--s2);border:1px solid var(--bor);border-radius:12px;padding:12px 13px;color:var(--txt);font:inherit;outline:none}
        .input:focus,.select:focus,.textarea:focus{border-color:var(--grn)}
        .btn{width:100%;padding:12px 14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--gbr),var(--grn));color:#fff;font:inherit;font-weight:900;cursor:pointer}
        .link-row{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:12px;font-size:12px}
        .divider{display:flex;align-items:center;gap:10px;color:var(--mut);font-size:11px;margin:16px 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--bor)}
        .stack{display:flex;flex-direction:column;gap:10px}
        .ghost{width:100%;padding:11px 14px;border-radius:12px;border:1px solid var(--bor);background:var(--s2);color:var(--txt);font-weight:800;cursor:pointer}
        .small{font-size:12px;color:var(--mut)}
        @media (max-width: 560px){
            .auth-shell{padding:14px}
            .auth-card{padding:20px;border-radius:20px}
            .auth-title{font-size:20px}
            .auth-sub{font-size:10px}
            .link-row{flex-direction:column;align-items:stretch}
            .link-row a{display:block}
            .btn,.ghost{width:100%}
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-mark">S</div>
            <h1 class="auth-title">SIPR GROUP</h1>
            <div class="auth-sub">Invest · Grow · Prosper</div>
        </div>

        @if (session('status'))
            <div class="auth-alert auth-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="auth-alert auth-error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>
</div>
</body>
</html>