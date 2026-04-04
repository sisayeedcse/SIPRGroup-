@extends('layouts.app')

@section('title', 'Investment | SIPR')
@section('pageTitle', 'Investment Detail')
@section('pageSubtitle', 'Read-only project overview with milestone and collection history.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canManage = in_array($role, ['admin', 'finance', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Investment #{{ $investment->id }}</div>
                    <h2>{{ $investment->name }}</h2>
                    <p>{{ $investment->sector ?? '-' }} · {{ $investment->partner ?? '-' }} · {{ ucfirst($investment->status) }}</p>
                </div>
                <a class="soft-btn" href="{{ route('investments.index') }}">Back to investments</a>
            </div>
        </section>

        <section class="panel">
            <div class="grid grid-2">
                <div>
                    <h3 class="section-title">Project Overview</h3>
                    <p><strong>Date:</strong> {{ $investment->date?->format('Y-m-d') }}</p>
                    <p><strong>Capital Deployed:</strong> {{ number_format((float) $investment->capital_deployed, 2) }}</p>
                    <p><strong>Expected Return:</strong> {{ number_format((float) ($investment->expected_return ?? 0), 2) }}</p>
                    <p><strong>Actual Return:</strong> {{ number_format((float) ($investment->actual_return ?? 0), 2) }}</p>
                    <p><strong>Description:</strong> {{ $investment->description ?? '-' }}</p>
                    <p><strong>Notes:</strong> {{ $investment->notes ?? '-' }}</p>
                </div>

                <div>
                    <h3 class="section-title">Operations Snapshot</h3>
                    <p><strong>Milestones:</strong> {{ $investment->milestones->count() }}</p>
                    <p><strong>Collection Entries:</strong> {{ $investment->collections->count() }}</p>
                    <p><strong>Team Members:</strong> {{ is_array($investment->team_members) ? implode(', ', $investment->team_members) : '-' }}</p>
                </div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Milestones</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Date</th><th>Done</th><th>Note</th>@if ($canManage)<th>Action</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($investment->milestones as $milestone)
                            <tr>
                                <td>{{ $milestone->title }}</td>
                                <td>{{ $milestone->date?->format('Y-m-d') }}</td>
                                <td>{{ $milestone->done ? 'Yes' : 'No' }}</td>
                                <td>{{ $milestone->note }}</td>
                                @if ($canManage)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <div class="stack" style="margin-top:10px">
                                                <form method="POST" action="{{ route('investments.milestones.update', [$investment, $milestone]) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="title" value="{{ $milestone->title }}" class="input" required>
                                                    <textarea name="note" class="textarea">{{ $milestone->note }}</textarea>
                                                    <input type="date" name="date" value="{{ $milestone->date?->format('Y-m-d') }}" class="input">
                                                    <label style="display:flex;align-items:center;gap:8px;color:var(--mut)"><input type="checkbox" name="done" value="1" @checked($milestone->done)> Done</label>
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('investments.milestones.destroy', [$investment, $milestone]) }}" onsubmit="return confirm('Delete milestone?');">
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
                            <tr><td colspan="{{ $canManage ? 5 : 4 }}" class="empty">No milestones recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Collections</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Date</th><th>KG</th><th>Sold KG</th><th>Revenue</th><th>Cost</th><th>Profit</th><th>Source</th>@if ($canManage)<th>Action</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($investment->collections as $collection)
                            <tr>
                                <td>{{ $collection->date?->format('Y-m-d') }}</td>
                                <td>{{ number_format((float) $collection->kg, 2) }}</td>
                                <td>{{ number_format((float) $collection->sold_kg, 2) }}</td>
                                <td>{{ number_format((float) $collection->revenue, 2) }}</td>
                                <td>{{ number_format((float) $collection->cost, 2) }}</td>
                                <td>{{ number_format((float) $collection->profit, 2) }}</td>
                                <td>{{ $collection->source }}</td>
                                @if ($canManage)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <div class="stack" style="margin-top:10px">
                                                <form method="POST" action="{{ route('investments.collections.update', [$investment, $collection]) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="date" name="date" value="{{ $collection->date?->format('Y-m-d') }}" class="input" required>
                                                    <div class="grid grid-2">
                                                        <input type="number" step="0.01" min="0" name="kg" value="{{ (float) $collection->kg }}" class="input">
                                                        <input type="text" name="type" value="{{ $collection->type }}" class="input" placeholder="Type">
                                                    </div>
                                                    <input type="text" name="source" value="{{ $collection->source }}" class="input" placeholder="Source">
                                                    <div class="grid grid-2">
                                                        <input type="number" step="0.01" min="0" name="sold_kg" value="{{ (float) $collection->sold_kg }}" class="input">
                                                        <input type="number" step="0.01" min="0" name="revenue" value="{{ (float) $collection->revenue }}" class="input">
                                                    </div>
                                                    <div class="grid grid-2">
                                                        <input type="number" step="0.01" min="0" name="cost" value="{{ (float) $collection->cost }}" class="input">
                                                        <input type="number" step="0.01" name="profit" value="{{ (float) $collection->profit }}" class="input">
                                                    </div>
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('investments.collections.destroy', [$investment, $collection]) }}" onsubmit="return confirm('Delete collection entry?');">
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
                            <tr><td colspan="{{ $canManage ? 8 : 7 }}" class="empty">No collection entries recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection