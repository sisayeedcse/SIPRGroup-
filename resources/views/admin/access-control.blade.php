@extends('layouts.app')

@section('title', 'Access Control | SIPR')
@section('pageTitle', 'Access Control')
@section('pageSubtitle', 'Set member roles and define which system options each role can use.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            <h3 class="section-title">Role Option Matrix</h3>
            <p class="muted" style="margin-top:-4px">Toggle access for each role. Changes apply instantly to users with that
                role.</p>

            <div class="stack" style="margin-top:12px">
                @foreach ($roles as $role)
                    @php
                        $roleValue = $role->value;
                        $enabledMap = $roleOptionMap[$roleValue] ?? [];
                    @endphp
                    <form method="POST" action="{{ route('admin.access-control.roles.options.update', $roleValue) }}"
                        class="panel" style="margin:0;padding:14px">
                        @csrf
                        @method('PUT')

                        <div class="top-tools" style="align-items:flex-start">
                            <div>
                                <strong>{{ $role->label() }}</strong>
                                <div class="muted" style="font-size:12px;margin-top:2px">Configure menu and route access for
                                    {{ strtolower($role->label()) }} users.</div>
                            </div>
                            <button type="submit" class="primary-btn">Save {{ $role->label() }} Access</button>
                        </div>

                        <div class="grid auto-fit" style="margin-top:10px">
                            @foreach ($options as $optionKey => $optionLabel)
                                <label class="kpi" style="display:flex;align-items:center;gap:8px;padding:12px;cursor:pointer">
                                    <input type="checkbox" name="options[]" value="{{ $optionKey }}"
                                        @checked(($enabledMap[$optionKey] ?? false) === true)>
                                    <span style="font-size:13px;font-weight:700">{{ $optionLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                @endforeach
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Member Role Assignment</h3>
            <p class="muted" style="margin-top:-4px">Assign whether a user is admin, secretary, advisor, finance, or member.
            </p>

            <div class="table-wrap" style="margin-top:12px">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Member ID</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Update Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->member_id }}</td>
                                <td>{{ $member->email }}</td>
                                <td><span class="pill pill-blue">{{ ucfirst($member->role->value ?? $member->role) }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.access-control.users.update', $member) }}"
                                        class="btn-row">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="select" style="min-width:170px" required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->value }}" @selected(($member->role->value ?? $member->role) === $role->value)>{{ $role->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="soft-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">No members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px">{{ $users->links() }}</div>
        </section>
    </div>
@endsection