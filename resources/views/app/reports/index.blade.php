@extends('layouts.app')

@section('title', 'Reports | SIPR')
@section('pageTitle', 'Reports')
@section('pageSubtitle', 'Generate CSV and PDF outputs or queue them for async delivery.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            <h3 class="section-title">Transactions CSV</h3>
            <form method="GET" action="{{ route('reports.transactions.csv') }}" class="grid grid-4">
                <select name="type" class="select">
                    <option value="">All types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select name="user_id" class="select">
                    <option value="">All members</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                    @endforeach
                </select>
                <input type="date" name="from" class="input">
                <input type="date" name="to" class="input">
                <button type="submit" class="soft-btn">Download CSV</button>
            </form>
            <form method="POST" action="{{ route('reports.exports.transactions') }}" class="grid grid-4" style="margin-top:10px">
                @csrf
                <select name="type" class="select">
                    <option value="">All types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select name="user_id" class="select">
                    <option value="">All members</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                    @endforeach
                </select>
                <input type="date" name="from" class="input">
                <input type="date" name="to" class="input">
                <button type="submit" class="primary-btn">Queue Export</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">Investments CSV</h3>
            <form method="GET" action="{{ route('reports.investments.csv') }}" class="grid auto-fit" style="max-width:560px">
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="paused">Paused</option>
                </select>
                <button type="submit" class="soft-btn">Download CSV</button>
            </form>
            <form method="POST" action="{{ route('reports.exports.investments') }}" class="grid auto-fit" style="max-width:560px;margin-top:10px">
                @csrf
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="paused">Paused</option>
                </select>
                <button type="submit" class="primary-btn">Queue Export</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">Wallet Passbook PDF</h3>
            <form method="GET" action="{{ route('reports.wallet.passbook.pdf') }}" class="grid auto-fit" style="max-width:560px">
                <select name="user_id" class="select" required>
                    <option value="">Select member</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                    @endforeach
                </select>
                <button type="submit" class="soft-btn">Download PDF</button>
            </form>
            <form method="POST" action="{{ route('reports.exports.wallet-passbook') }}" class="grid auto-fit" style="max-width:560px;margin-top:10px">
                @csrf
                <select name="user_id" class="select" required>
                    <option value="">Select member</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Queue Export</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">Recent Export History</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Type</th><th>Status</th><th>Requested</th><th>Completed</th><th>File</th></tr></thead>
                    <tbody>
                        @forelse ($exports as $export)
                            <tr>
                                <td>{{ $export->type }}</td>
                                <td><span class="pill {{ $export->status === 'completed' ? 'pill-green' : ($export->status === 'failed' ? 'pill-red' : 'pill-blue') }}">{{ ucfirst($export->status) }}</span></td>
                                <td>{{ $export->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $export->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    @if ($export->status === 'completed' && $export->file_path)
                                        <a href="{{ route('reports.exports.download', $export) }}">Download</a>
                                    @elseif ($export->error_message)
                                        Failed
                                    @else
                                        Pending
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">No export history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection