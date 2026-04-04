@extends('layouts.auth')

@section('title', 'Link Invite Code | SIPR')

@section('content')
    <p class="small">Signed in as <strong>{{ $profile['name'] ?? 'User' }}</strong> ({{ $profile['email'] ?? '' }})</p>
    <form method="POST" action="{{ route('auth.invite.link.store') }}">
        @csrf
        <div class="field"><label class="label">Invite code</label><input class="input" type="text" name="invite_code" value="{{ old('invite_code') }}" placeholder="SIPR26-XX-0000" required></div>
        <button type="submit" class="btn">Link account</button>
    </form>
    <div class="link-row"><a href="{{ route('login') }}">Back to login</a></div>
@endsection
