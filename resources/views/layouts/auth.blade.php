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
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap');

        :root { --bg-0:#070c16; --bg-1:#0d1424; --surface:rgba(16,23,40,.88); --surface-2:rgba(22,30,52,.86); --line:rgba(150,177,222,.24); --line-strong:rgba(150,177,222,.36); --text:#e9eefb; --muted:#97a7c8; --accent:#3ecf8e; --accent-2:#4ca9ff; --danger:#ff6b7f; --shadow:0 22px 58px rgba(3,9,22,.6); }
        *{box-sizing:border-box}
        html,body{margin:0;padding:0}
        body{font-family:'Manrope',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:radial-gradient(1200px 600px at 100% -10%,rgba(76,169,255,.2),transparent 60%),radial-gradient(900px 500px at 0% 0%,rgba(62,207,142,.14),transparent 58%),linear-gradient(160deg,var(--bg-0),var(--bg-1));color:var(--text);min-height:100vh}
        a{color:#9bcaff;text-decoration:none;transition:color .18s ease}
        a:hover{color:#c0ddff}
        .auth-shell{min-height:100vh;display:grid;place-items:center;padding:24px}
        .auth-card{width:min(100%,470px);background:var(--surface);border:1px solid var(--line);border-radius:24px;padding:28px;box-shadow:var(--shadow);backdrop-filter:blur(12px);animation:fadeIn .35s ease}
        .auth-brand{display:flex;flex-direction:column;align-items:center;text-align:center;margin-bottom:24px}
        .auth-mark{width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,#1f619a,#3ecf8e);display:grid;place-items:center;font-size:24px;font-weight:800;margin-bottom:12px;box-shadow:0 10px 26px rgba(62,207,142,.24)}
        .auth-title{font-family:'Sora','Manrope',sans-serif;font-size:24px;font-weight:700;margin:0;letter-spacing:.02em}
        .auth-sub{margin-top:6px;font-size:10px;letter-spacing:.3em;text-transform:uppercase;color:var(--muted)}
        .auth-alert{border-radius:13px;padding:12px 14px;margin-bottom:14px;border:1px solid transparent}
        .auth-success{background:rgba(62,207,142,.14);border-color:rgba(62,207,142,.38);color:#b6f0d2}
        .auth-error{background:rgba(255,107,127,.14);border-color:rgba(255,107,127,.4);color:#ffd0d7}
        .field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
        .label{font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);font-weight:700}
        .input,.select,.textarea{width:100%;background:rgba(20,30,53,.74);border:1px solid var(--line);border-radius:12px;padding:12px 13px;color:var(--text);font:inherit;outline:none;transition:.2s border-color,.2s box-shadow,.2s background}
        .input:focus,.select:focus,.textarea:focus{border-color:rgba(76,169,255,.65);box-shadow:0 0 0 3px rgba(76,169,255,.18);background:rgba(22,34,58,.9)}
        .btn{width:100%;padding:12px 14px;border:none;border-radius:12px;background:linear-gradient(135deg,#1f619a,#3ecf8e);color:#f7feff;font:inherit;font-weight:700;cursor:pointer;transition:.2s transform,.2s box-shadow;box-shadow:0 8px 24px rgba(62,207,142,.2)}
        .btn:hover{transform:translateY(-1px)}
        .link-row{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:12px;font-size:12px}
        .divider{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:11px;margin:16px 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
        .stack{display:flex;flex-direction:column;gap:10px}
        .ghost{width:100%;padding:11px 14px;border-radius:12px;border:1px solid var(--line);background:var(--surface-2);color:var(--text);font-weight:700;cursor:pointer}
        .small{font-size:12px;color:var(--muted)}
        @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
        @media (max-width: 560px){
            .auth-shell{padding:14px}
            .auth-card{padding:20px;border-radius:20px}
            .auth-title{font-size:21px}
            .auth-sub{font-size:10px;letter-spacing:.25em}
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