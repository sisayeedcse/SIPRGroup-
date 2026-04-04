@extends('layouts.app')

@section('title', 'Noticeboard | SIPR')
@section('pageTitle', 'Noticeboard')
@section('pageSubtitle', 'Announcements and proposal governance live together in the same operating board.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canManageAnnouncements = in_array($role, ['admin', 'secretary'], true);
        $canSetProposalStatus = in_array($role, ['admin', 'finance', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <section class="panel">
            <h3 class="section-title">Announcements</h3>
            @if ($canManageAnnouncements)
                <form method="POST" action="{{ route('announcements.store') }}" class="stack" style="margin-bottom:16px">
                    @csrf
                    <input type="text" name="title" placeholder="Announcement title" class="input" required>
                    <textarea name="message" placeholder="Message" class="textarea" required></textarea>
                    <label style="display:flex;align-items:center;gap:8px;color:var(--mut)"><input type="checkbox" name="pinned" value="1"> Pin this announcement</label>
                    <button type="submit" class="primary-btn">Post announcement</button>
                </form>
            @endif

            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Pinned</th><th>Title</th><th>Message</th><th>Author</th><th>Created</th>@if ($canManageAnnouncements)<th>Action</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>{{ $announcement->pinned ? 'Yes' : 'No' }}</td>
                                <td>{{ $announcement->title }}</td>
                                <td>{{ $announcement->message }}</td>
                                <td>{{ $announcement->author?->name }}</td>
                                <td>{{ $announcement->created_at?->format('Y-m-d H:i') }}</td>
                                @if ($canManageAnnouncements)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <div class="stack" style="margin-top:10px">
                                                <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="title" value="{{ $announcement->title }}" class="input" required>
                                                    <textarea name="message" class="textarea" required>{{ $announcement->message }}</textarea>
                                                    <label style="display:flex;align-items:center;gap:8px;color:var(--mut)"><input type="checkbox" name="pinned" value="1" @checked($announcement->pinned)> Pin</label>
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete announcement?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-btn">Delete</button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canManageAnnouncements ? 6 : 5 }}" class="empty">No announcements yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $announcements->links() }}</div>
        </section>

        <section class="panel">
            <h3 class="section-title">Proposals & Voting</h3>
            <form method="POST" action="{{ route('proposals.store') }}" class="stack" style="margin-bottom:16px">
                @csrf
                <input type="text" name="title" placeholder="Proposal title" class="input" required>
                <textarea name="description" placeholder="Proposal description" class="textarea" required></textarea>
                <div class="grid grid-4">
                    <input type="number" name="amount" min="0" step="0.01" placeholder="Amount (optional)" class="input">
                    <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                    <input type="date" name="closes_at" value="{{ now()->addDays(7)->toDateString() }}" class="input" required>
                    <input type="number" name="quorum_required" min="1" step="1" value="3" class="input" required>
                </div>
                <button type="submit" class="primary-btn">Submit proposal</button>
            </form>

            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Description</th><th>Amount</th><th>Status</th><th>Governance</th><th>Votes</th><th>Your Vote</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse ($proposals as $proposal)
                            <tr>
                                <td>{{ $proposal->title }}</td>
                                <td>{{ $proposal->description }}</td>
                                <td>{{ number_format((float) ($proposal->amount ?? 0), 2) }}</td>
                                <td><span class="pill {{ $proposal->status === 'approved' ? 'pill-green' : ($proposal->status === 'rejected' ? 'pill-red' : 'pill-blue') }}">{{ ucfirst($proposal->status) }}</span></td>
                                <td>Quorum: {{ $proposal->quorum_required }}<br>Closes: {{ $proposal->closes_at?->format('Y-m-d') ?? 'N/A' }}<br>Finalized: {{ $proposal->finalized_at?->format('Y-m-d H:i') ?? 'No' }}</td>
                                <td>Yes: {{ $proposal->yes_votes_count }} | No: {{ $proposal->no_votes_count }}</td>
                                <td>{{ strtoupper($myVotes[$proposal->id] ?? '-') }}</td>
                                <td>
                                    <a class="pill pill-blue" href="{{ route('proposals.show', $proposal) }}" style="display:inline-flex;margin-bottom:8px">View</a>
                                    @if ($proposal->status === 'active' && !$proposal->finalized_at)
                                        <form method="POST" action="{{ route('proposals.vote', $proposal) }}" class="btn-row" style="margin-bottom:8px">
                                            @csrf
                                            <button type="submit" name="vote" value="yes" class="soft-btn">Vote Yes</button>
                                            <button type="submit" name="vote" value="no" class="ghost-btn">Vote No</button>
                                        </form>
                                    @endif

                                    @if ($canSetProposalStatus)
                                        <form method="POST" action="{{ route('proposals.status.update', $proposal) }}" class="btn-row">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="select">
                                                <option value="active" @selected($proposal->status === 'active')>Active</option>
                                                <option value="approved" @selected($proposal->status === 'approved')>Approved</option>
                                                <option value="rejected" @selected($proposal->status === 'rejected')>Rejected</option>
                                            </select>
                                            <button type="submit" class="soft-btn">Set</button>
                                        </form>
                                        @if ($proposal->status === 'active' && !$proposal->finalized_at)
                                            <form method="POST" action="{{ route('proposals.finalize', $proposal) }}" style="margin-top:8px">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="primary-btn">Finalize Now</button>
                                            </form>
                                        @endif
                                    @endif

                                    @can('update', $proposal)
                                        <details style="margin-top:10px">
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit Proposal</summary>
                                            <div class="stack" style="margin-top:10px">
                                                <form method="POST" action="{{ route('proposals.update', $proposal) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="title" value="{{ $proposal->title }}" class="input" required>
                                                    <textarea name="description" class="textarea" required>{{ $proposal->description }}</textarea>
                                                    <div class="grid grid-4">
                                                        <input type="number" name="amount" min="0" step="0.01" value="{{ (float) ($proposal->amount ?? 0) }}" class="input">
                                                        <input type="date" name="date" value="{{ $proposal->date?->format('Y-m-d') }}" class="input" required>
                                                        <input type="date" name="closes_at" value="{{ $proposal->closes_at?->format('Y-m-d') }}" class="input" required>
                                                        <input type="number" name="quorum_required" min="1" step="1" value="{{ $proposal->quorum_required }}" class="input" required>
                                                    </div>
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                            </div>
                                        </details>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty">No proposals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $proposals->links() }}</div>
        </section>
    </div>
@endsection