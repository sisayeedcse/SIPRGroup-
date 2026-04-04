@extends('layouts.app')

@section('title', 'Proposal | SIPR')
@section('pageTitle', 'Proposal Detail')
@section('pageSubtitle', 'Read-only governance detail with voting history and decision context.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canSetProposalStatus = in_array($role, ['admin', 'finance', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Proposal #{{ $proposal->id }}</div>
                    <h2>{{ $proposal->title }}</h2>
                    <p>{{ ucfirst($proposal->status) }} · Proposed by {{ $proposal->proposer?->name ?? '-' }} ·
                        {{ $proposal->date?->format('Y-m-d') }}</p>
                </div>
                <a class="soft-btn" href="{{ route('noticeboard.index') }}">Back to noticeboard</a>
            </div>
        </section>

        <section class="panel">
            <div class="grid grid-2">
                <div>
                    <h3 class="section-title">Proposal Summary</h3>
                    <p><strong>Description:</strong> {{ $proposal->description }}</p>
                    <p><strong>Amount:</strong> {{ number_format((float) ($proposal->amount ?? 0), 2) }}</p>
                    <p><strong>Date:</strong> {{ $proposal->date?->format('Y-m-d') }}</p>
                    <p><strong>Closes At:</strong> {{ $proposal->closes_at?->format('Y-m-d') ?? '-' }}</p>
                    <p><strong>Finalized At:</strong> {{ $proposal->finalized_at?->format('Y-m-d H:i') ?? 'Not finalized' }}
                    </p>
                </div>

                <div>
                    <h3 class="section-title">Governance Snapshot</h3>
                    <p><strong>Quorum Required:</strong> {{ $proposal->quorum_required }}</p>
                    <p><strong>Yes Votes:</strong> {{ $proposal->yes_votes_count ?? 0 }}</p>
                    <p><strong>No Votes:</strong> {{ $proposal->no_votes_count ?? 0 }}</p>
                    <p><strong>Your Vote:</strong> {{ strtoupper($myVote ?? '-') }}</p>
                    <p><strong>Proposer:</strong> {{ $proposal->proposer?->name ?? '-' }}
                        ({{ $proposal->proposer?->member_id ?? '-' }})</p>
                </div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Votes</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Vote</th>
                            <th>Cast At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proposal->votes as $vote)
                            <tr>
                                <td>{{ $vote->user?->name ?? '-' }}</td>
                                <td>{{ strtoupper($vote->vote) }}</td>
                                <td>{{ $vote->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty">No votes recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Actions</h3>
            <div class="btn-row">
                @if ($proposal->status === 'active' && !$proposal->finalized_at)
                    <form method="POST" action="{{ route('proposals.vote', $proposal) }}" class="btn-row">
                        @csrf
                        <button type="submit" name="vote" value="yes" class="soft-btn">Vote Yes</button>
                        <button type="submit" name="vote" value="no" class="ghost-btn">Vote No</button>
                    </form>
                @endif

                @if ($canSetProposalStatus && $proposal->status === 'active' && !$proposal->finalized_at)
                    <form method="POST" action="{{ route('proposals.finalize', $proposal) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="primary-btn">Finalize Proposal</button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection