@extends('layouts.app')

@section('title', 'Members | SIPR')
@section('pageTitle', 'Members Directory')
@section('pageSubtitle', 'Search, review, and manage the member roster and access roles.')

@section('content')
    @php
        $canManage = (auth()->user()->role->value ?? auth()->user()->role) === 'admin';
    @endphp

    <div class="page-stack">
        <section class="panel">
            <form method="GET" action="{{ route('members.index') }}" class="grid grid-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name/email/member ID" class="input">
                <select name="role" class="select">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ \App\Models\User::roleDisplayLabel($role) }}</option>
                    @endforeach
                </select>
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">Member List</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Locked</th><th>Title</th><th>Phone</th>@if ($canManage)<th>Action</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $member->member_id }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td><span class="pill pill-blue">{{ \App\Models\User::roleDisplayLabel($member->role->value ?? $member->role) }}</span></td>
                                <td><span class="pill {{ $member->status === 'active' ? 'pill-green' : 'pill-red' }}">{{ ucfirst($member->status) }}</span></td>
                                <td>{{ $member->locked ? 'Yes' : 'No' }}</td>
                                <td>{{ $member->title }}</td>
                                <td>{{ $member->phone }}</td>
                                @if ($canManage)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <form method="POST" action="{{ route('members.update', $member) }}" class="stack" style="margin-top:10px;min-width:220px">
                                                @csrf
                                                @method('PUT')
                                                <select name="role" class="select" required>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role }}" @selected(($member->role->value ?? $member->role) === $role)>{{ \App\Models\User::roleDisplayLabel($role) }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="status" class="select" required>
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}" @selected($member->status === $status)>{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="locked" class="select" required>
                                                    <option value="0" @selected(! $member->locked)>No</option>
                                                    <option value="1" @selected($member->locked)>Yes</option>
                                                </select>
                                                <input type="text" name="title" value="{{ $member->title }}" class="input" placeholder="Title">
                                                <input type="text" name="phone" value="{{ $member->phone }}" class="input" placeholder="Phone">
                                                <button type="submit" class="soft-btn">Save</button>
                                            </form>
                                        </details>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canManage ? 9 : 8 }}" class="empty">No members found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $members->links() }}</div>
        </section>
    </div>
@endsection
