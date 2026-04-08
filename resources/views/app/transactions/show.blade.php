@extends('layouts.app')

@section('title', 'Transaction | SIPR')
@section('pageTitle', 'Transaction Detail')
@section('pageSubtitle', 'Read-only transaction record with member context.')

@section('content')
    <div class="page-stack">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Transaction #{{ $transaction->id }}</div>
                    <h2>{{ ucfirst($transaction->type) }} · {{ number_format((float) $transaction->amount, 2) }}</h2>
                    <p>{{ $transaction->user?->name }} ({{ $transaction->user?->member_id }}) ·
                        {{ $transaction->date?->format('Y-m-d') }}</p>
                </div>
                <a class="soft-btn" href="{{ route('transactions.index') }}">Back to transactions</a>
            </div>
        </section>

        <section class="panel">
            <div class="grid grid-2">
                <div>
                    <h3 class="section-title">Record Details</h3>
                    <p><strong>Type:</strong> {{ ucfirst($transaction->type) }}</p>
                    <p><strong>Amount:</strong> {{ number_format((float) $transaction->amount, 2) }}</p>
                    <p><strong>Date:</strong> {{ $transaction->date?->format('Y-m-d') }}</p>
                    <p><strong>Note:</strong> {{ $transaction->note ?? '-' }}</p>
                </div>

                <div>
                    <h3 class="section-title">Member Context</h3>
                    <p><strong>Name:</strong> {{ $transaction->user?->name }}</p>
                    <p><strong>Member ID:</strong> {{ $transaction->user?->member_id }}</p>
                    <p><strong>Role:</strong>
                        {{ \App\Models\User::roleDisplayLabel($transaction->user?->role->value ?? $transaction->user?->role) }}
                    </p>
                    <p><strong>Status:</strong> {{ ucfirst($transaction->user?->status ?? '-') }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection