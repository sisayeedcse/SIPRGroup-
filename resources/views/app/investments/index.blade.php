@extends('layouts.app')

@section('title', 'Investments | SIPR')
@section('pageTitle', 'Investments')
@section('pageSubtitle', 'Manage projects, milestones, and collection records in one place.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canWrite = in_array($role, ['admin', 'finance', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <section class="panel">
            <form method="GET" action="{{ route('investments.index') }}" class="grid grid-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name/sector/partner" class="input">
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        @if ($canWrite)
            <section class="panel">
                <h3 class="section-title">Create Investment</h3>
                <form method="POST" action="{{ route('investments.store') }}" class="grid grid-4">
                    @csrf
                    <input type="text" name="name" placeholder="Name" class="input" required>
                    <input type="text" name="sector" placeholder="Sector" class="input">
                    <input type="text" name="partner" placeholder="Partner" class="input">
                    <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                    <input type="number" step="0.01" min="0" name="capital_deployed" placeholder="Capital" class="input" required>
                    <input type="number" step="0.01" min="0" name="expected_return" placeholder="Expected return" class="input">
                    <input type="number" step="0.01" min="0" name="actual_return" placeholder="Actual return" class="input">
                    <select name="status" class="select" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <textarea name="description" placeholder="Description" class="textarea" style="grid-column:1 / span 2"></textarea>
                    <textarea name="notes" placeholder="Notes" class="textarea" style="grid-column:3 / span 2"></textarea>
                    <button type="submit" class="primary-btn" style="grid-column:1 / -1">Create</button>
                </form>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Project List</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Sector</th><th>Status</th><th>Date</th><th>Capital</th><th>Expected</th><th>Actual</th><th>Open</th>@if ($canWrite)<th>Action</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($investments as $investment)
                            <tr>
                                <td>{{ $investment->name }}</td>
                                <td>{{ $investment->sector }}</td>
                                <td><span class="pill pill-blue">{{ ucfirst($investment->status) }}</span></td>
                                <td>{{ $investment->date?->format('Y-m-d') }}</td>
                                <td>{{ number_format((float) $investment->capital_deployed, 2) }}</td>
                                <td>{{ number_format((float) ($investment->expected_return ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($investment->actual_return ?? 0), 2) }}</td>
                                <td><a href="{{ route('investments.show', $investment) }}">View</a></td>
                                @if ($canWrite)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <div style="margin-top:10px" class="stack">
                                                <form method="POST" action="{{ route('investments.update', $investment) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="name" value="{{ $investment->name }}" class="input" required>
                                                    <input type="text" name="sector" value="{{ $investment->sector }}" class="input">
                                                    <input type="text" name="partner" value="{{ $investment->partner }}" class="input">
                                                    <input type="date" name="date" value="{{ $investment->date?->format('Y-m-d') }}" class="input" required>
                                                    <input type="number" step="0.01" min="0" name="capital_deployed" value="{{ (float) $investment->capital_deployed }}" class="input" required>
                                                    <input type="number" step="0.01" min="0" name="expected_return" value="{{ (float) ($investment->expected_return ?? 0) }}" class="input">
                                                    <input type="number" step="0.01" min="0" name="actual_return" value="{{ (float) ($investment->actual_return ?? 0) }}" class="input">
                                                    <select name="status" class="select" required>
                                                        @foreach ($statuses as $status)
                                                            <option value="{{ $status }}" @selected($investment->status === $status)>{{ ucfirst($status) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <textarea name="description" class="textarea">{{ $investment->description }}</textarea>
                                                    <textarea name="notes" class="textarea">{{ $investment->notes }}</textarea>
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('investments.destroy', $investment) }}" onsubmit="return confirm('Delete investment?');">
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
                            <tr><td colspan="{{ $canWrite ? 9 : 8 }}" class="empty">No investments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $investments->links() }}</div>
        </section>

        @if ($selected)
            <section class="hero">
                <div class="hero-top">
                    <div>
                        <div class="hero-kicker">Selected Project</div>
                        <h2>{{ $selected->name }}</h2>
                        <p>{{ ucfirst($selected->status) }} · Capital {{ number_format((float) $selected->capital_deployed, 2) }}</p>
                    </div>
                    <a class="soft-btn" href="{{ route('investments.show', $selected) }}">Open Detail Page</a>
                </div>
            </section>

            @if ($canWrite)
                <section class="panel">
                    <h3 class="section-title">Add Milestone</h3>
                    <form method="POST" action="{{ route('investments.milestones.store', $selected) }}" class="grid grid-4">
                        @csrf
                        <input type="text" name="title" placeholder="Milestone title" class="input" required>
                        <input type="text" name="note" placeholder="Note" class="input">
                        <input type="date" name="date" class="input">
                        <label style="display:flex;align-items:center;gap:8px;color:var(--mut)"><input type="checkbox" name="done" value="1"> Done</label>
                        <button type="submit" class="primary-btn" style="grid-column:1 / -1">Add milestone</button>
                    </form>
                </section>

                <section class="panel">
                    <h3 class="section-title">Add Collection Record</h3>
                    <form method="POST" action="{{ route('investments.collections.store', $selected) }}" class="grid grid-4">
                        @csrf
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                        <input type="number" step="0.01" min="0" name="kg" placeholder="Collected kg" class="input">
                        <input type="text" name="type" placeholder="Type" class="input">
                        <input type="text" name="source" placeholder="Source" class="input">
                        <input type="number" step="0.01" min="0" name="sold_kg" placeholder="Sold kg" class="input">
                        <input type="number" step="0.01" min="0" name="revenue" placeholder="Revenue" class="input">
                        <input type="number" step="0.01" min="0" name="cost" placeholder="Cost" class="input">
                        <input type="number" step="0.01" name="profit" placeholder="Profit (optional)" class="input">
                        <button type="submit" class="primary-btn" style="grid-column:1 / -1">Add collection</button>
                    </form>
                </section>
            @endif

            <section class="panel">
                <h3 class="section-title">Milestones</h3>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Title</th><th>Date</th><th>Done</th><th>Note</th></tr></thead>
                        <tbody>
                            @forelse ($selected->milestones as $ms)
                                <tr><td>{{ $ms->title }}</td><td>{{ $ms->date?->format('Y-m-d') }}</td><td>{{ $ms->done ? 'Yes' : 'No' }}</td><td>{{ $ms->note }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="empty">No milestones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <h3 class="section-title">Collections</h3>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Date</th><th>KG</th><th>Sold KG</th><th>Revenue</th><th>Cost</th><th>Profit</th><th>Source</th></tr></thead>
                        <tbody>
                            @forelse ($selected->collections as $co)
                                <tr>
                                    <td>{{ $co->date?->format('Y-m-d') }}</td>
                                    <td>{{ number_format((float) $co->kg, 2) }}</td>
                                    <td>{{ number_format((float) $co->sold_kg, 2) }}</td>
                                    <td>{{ number_format((float) $co->revenue, 2) }}</td>
                                    <td>{{ number_format((float) $co->cost, 2) }}</td>
                                    <td>{{ number_format((float) $co->profit, 2) }}</td>
                                    <td>{{ $co->source }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty">No collection entries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
