@extends('layouts.auth')

@section('title', 'Login | SIPR')

@section('content')
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field">
            <label class="label">Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
        </div>

        <div class="field">
            <label class="label">Password</label>
            <input class="input" type="password" name="password" placeholder="Password" required>
        </div>

        <label style="display:flex;align-items:center;gap:8px;margin:4px 0 16px;color:var(--mut);font-size:13px">
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>

        <button type="submit" class="btn">Login</button>
    </form>

    <div class="divider">OR</div>

    <div class="stack">
        <a class="ghost" href="{{ route('auth.google.redirect') }}">Continue with Google</a>
        <a class="ghost" href="{{ route('register') }}">New member? Register with invite code</a>
        <a class="ghost" href="{{ route('password.request') }}">Forgot password?</a>
    </div>
@endsection