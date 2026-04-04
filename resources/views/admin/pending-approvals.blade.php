@extends('layouts.app')

@section('title', 'Pending Approvals | SIPR')
@section('pageTitle', 'Pending Approvals')
@section('pageSubtitle', 'Approve or reject registrations waiting for admin review.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            @if ($pending->isEmpty())
                <div class="empty">No pending registrations.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Member ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach ($pending as $approval)
                                <tr>
                                    <td>{{ $approval->user?->member_id }}</td>
                                    <td>{{ $approval->user?->name }}</td>
                                    <td>{{ $approval->user?->email }}</td>
                                    <td><span class="pill pill-blue">{{ ucfirst($approval->status) }}</span></td>
                                    <td>
                                        <div class="btn-row">
                                            <form method="POST" action="{{ route('admin.approvals.approve', $approval) }}">@csrf<button type="submit" class="primary-btn">Approve</button></form>
                                            <form method="POST" action="{{ route('admin.approvals.reject', $approval) }}">@csrf<button type="submit" class="danger-btn">Reject</button></form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection