@extends('layouts.app')

@section('title', 'Notifications | SIPR')
@section('pageTitle', 'Notifications')
@section('pageSubtitle', 'A central inbox for approvals, reports, and governance events.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PUT')
                <button type="submit" class="primary-btn">Mark all as read</button>
            </form>
        </section>

        <section class="panel">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Type</th><th>Message</th><th>Created</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            @php
                                $data = is_array($notification->data) ? $notification->data : [];
                                $message = $data['message'] ?? ($data['title'] ?? json_encode($data));
                            @endphp
                            <tr>
                                <td>{{ class_basename($notification->type) }}</td>
                                <td>{{ $message }}</td>
                                <td>{{ $notification->created_at?->format('Y-m-d H:i') }}</td>
                                <td><span class="pill {{ $notification->read_at ? 'pill-green' : 'pill-blue' }}">{{ $notification->read_at ? 'Read' : 'Unread' }}</span></td>
                                <td>
                                    @if (! $notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="soft-btn">Mark read</button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">No notifications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $notifications->links() }}</div>
        </section>
    </div>
@endsection
