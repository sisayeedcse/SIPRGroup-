@extends('layouts.app')

@section('title', 'Profile | SIPR')
@section('pageTitle', 'My Profile')
@section('pageSubtitle', 'Update your contact details and profile image.')

@section('content')
    <div class="page-stack">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Member Profile</div>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->member_id }} · {{ $user->role->value ?? $user->role }} · {{ ucfirst($user->status) }}</p>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="grid grid-2">
                <div>
                    <h3 class="section-title">Current Details</h3>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Phone:</strong> {{ $user->phone ?? '-' }}</p>
                    <p><strong>Title:</strong> {{ $user->title ?? '-' }}</p>
                    <p><strong>Address:</strong> {{ $user->address ?? '-' }}</p>
                    <p><strong>Wallet:</strong>
                        {{ $user->wallet ? number_format((float) $user->wallet->available, 2) : 'No wallet yet' }}</p>
                </div>

                <div>
                    <h3 class="section-title">Update Profile</h3>
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="stack">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="input"
                            placeholder="Phone">
                        <input type="text" name="title" value="{{ old('title', $user->title) }}" class="input"
                            placeholder="Title">
                        <textarea name="address" class="textarea"
                            placeholder="Address">{{ old('address', $user->address) }}</textarea>
                        <input type="file" name="photo" class="input" accept="image/*">
                        <button type="submit" class="primary-btn">Save Profile</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="panel" style="max-width:720px">
            <h3 class="section-title">Change Password</h3>
            <form method="POST" action="{{ route('profile.password.update') }}" class="stack">
                @csrf
                @method('PUT')
                <div class="field">
                    <label class="label">Current password</label>
                    <input type="password" name="current_password" class="input" required>
                </div>
                <div class="field">
                    <label class="label">New password</label>
                    <input type="password" name="new_password" class="input" required>
                </div>
                <div class="field">
                    <label class="label">Confirm new password</label>
                    <input type="password" name="new_password_confirmation" class="input" required>
                </div>
                <button type="submit" class="primary-btn">Update Password</button>
            </form>
        </section>
    </div>
@endsection