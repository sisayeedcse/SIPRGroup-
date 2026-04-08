@extends('layouts.app')

@section('title', 'Dashboard | SIPR')
@section('pageTitle', 'Dashboard')
@section('pageSubtitle', 'Your operational snapshot across members, treasury, governance, and reports.')

@section('content')
    <div class="page-stack">
        @php
            $userModel = \App\Models\User::class;
            $role = auth()->user()->role->value ?? auth()->user()->role;
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">SIPR GROUP</div>
                    <h2>Welcome, {{ auth()->user()->name }}</h2>
                    <p>{{ auth()->user()->member_id }} · {{ $userModel::roleDisplayLabel($role) }} ·
                        {{ auth()->user()->status }}
                    </p>
                </div>
                <div class="btn-row">
                    <a class="soft-btn" href="{{ route('noticeboard.index') }}">Noticeboard</a>
                    <a class="ghost-btn" href="{{ route('reports.index') }}">Reports</a>
                </div>
            </div>
        </section>

        <section class="grid grid-4">
            <div class="kpi">
                <div class="label">Role</div>
                <div class="value">{{ $userModel::roleDisplayLabel($role) }}</div>
                <div class="note">Current access profile</div>
            </div>
            <div class="kpi">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst(auth()->user()->status) }}</div>
                <div class="note">Account lifecycle</div>
            </div>
            <div class="kpi">
                <div class="label">Unread</div>
                <div class="value">{{ $unreadCount }}</div>
                <div class="note">Inbox notifications</div>
            </div>
            <div class="kpi">
                <div class="label">Modules</div>
                <div class="value">8+</div>
                <div class="note">Core SIPR workflows</div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">My Monthly Due Status</h3>
            <div class="grid grid-4" style="margin-top:10px">
                <div class="kpi">
                    <div class="label">Month</div>
                    <div class="value">{{ $dueSnapshot['month'] }}</div>
                    <div class="note">Current due period</div>
                </div>
                <div class="kpi">
                    <div class="label">Status</div>
                    <div class="value">{{ ucfirst($dueSnapshot['current_status']) }}</div>
                    <div class="note">This month contribution</div>
                </div>
                <div class="kpi">
                    <div class="label">This Month</div>
                    <div class="value">{{ number_format((float) $dueSnapshot['current_paid'], 2) }} /
                        {{ number_format((float) $dueSnapshot['current_expected'], 2) }}</div>
                    <div class="note">Paid vs expected</div>
                </div>
                <div class="kpi">
                    <div class="label">Arrears</div>
                    <div class="value">{{ $dueSnapshot['arrears_count'] }}</div>
                    <div class="note">Outstanding months ({{ number_format((float) $dueSnapshot['arrears_amount'], 2) }})
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Quick Actions</h3>
            <div class="grid grid-3">
                <a class="kpi" href="{{ route('transactions.index') }}">
                    <div class="label">Transactions</div>
                    <div class="value">Open</div>
                    <div class="note">Manage deposits, expenses, and fines</div>
                </a>
                <a class="kpi" href="{{ route('wallets.index') }}">
                    <div class="label">Wallets</div>
                    <div class="value">Open</div>
                    <div class="note">Review passbook balances</div>
                </a>
                <a class="kpi" href="{{ route('notifications.index') }}">
                    <div class="label">Notifications</div>
                    <div class="value">Open</div>
                    <div class="note">Read alerts and approvals</div>
                </a>
            </div>
        </section>
    </div>
@endsection