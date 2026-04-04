@extends('layouts.auth')

@section('title', 'Forgot Password | SIPR')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field"><label class="label">Email</label><input class="input" type="email" name="email" value="{{ old('email') }}" required></div>
        <button type="submit" class="btn">Send reset link</button>
    </form>
    <div class="link-row"><a href="{{ route('login') }}">Back to login</a></div>
@endsection