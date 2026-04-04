@extends('layouts.auth')

@section('title', 'Reset Password | SIPR')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field"><label class="label">Email</label><input class="input" type="email" name="email" value="{{ old('email', $email) }}" required></div>
        <div class="field"><label class="label">New Password</label><input class="input" type="password" name="password" required></div>
        <div class="field"><label class="label">Confirm Password</label><input class="input" type="password" name="password_confirmation" required></div>
        <button type="submit" class="btn">Reset password</button>
    </form>
@endsection