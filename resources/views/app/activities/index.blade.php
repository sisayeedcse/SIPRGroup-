@extends('layouts.app')

@section('title', 'Activity Log | SIPR')
@section('pageTitle', 'Activity Log')
@section('pageSubtitle', 'Track who changed financial, governance, and member records across the system.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            <form method="GET" action="{{ route('activities.index') }}" class="grid grid-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search detail or member" class="input">
                <select name="action" class="select">
                    <option value="">All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('-', ' ', $action) }}</option>
                    @endforeach
                </select>
                <select name="role" class="select">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">System Activity</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Time</th><th>Action</th><th>User</th><th>Role</th><th>Detail</th></tr></thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>{{ $activity->created_at?->format('Y-m-d H:i') }}</td>
                                <td><span class="pill pill-blue">{{ str_replace('-', ' ', $activity->action) }}</span></td>
                                <td>{{ $activity->user?->name ?? 'System' }}<br><span class="muted">{{ $activity->user?->member_id }}</span></td>
                                <td>{{ ucfirst($activity->role ?? '-') }}</td>
                                <td>{{ $activity->detail }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">No activity records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $activities->links() }}</div>
        </section>
    </div>
@endsection