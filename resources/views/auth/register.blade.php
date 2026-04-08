@extends('layouts.auth')

@section('title', 'Register | SIPR')

@section('content')
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="field"><label class="label">Full name</label><input class="input" type="text" name="name"
                value="{{ old('name') }}" required></div>
        <div class="field"><label class="label">Email</label><input class="input" type="email" name="email"
                value="{{ old('email') }}" required></div>
        <div class="field"><label class="label">Password</label><input class="input" type="password" name="password"
                required></div>
        <div class="field"><label class="label">Confirm password</label><input class="input" type="password"
                name="password_confirmation" required></div>
        <button type="submit" class="btn">Create account</button>
    </form>
    <div class="link-row"><a href="{{ route('login') }}">Back to login</a></div>
@endsection