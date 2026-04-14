@extends('layouts.app')

@section('title', 'Investments | SIPR')
@section('pageTitle', 'Investments')
@section('pageSubtitle', 'Track active projects, returns, and portfolio progress.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canWrite = in_array($role, ['admin', 'finance', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <style>
            .invest-hero {
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
                padding: 16px;
                background: linear-gradient(135deg, rgba(13, 23, 40, 0.95), rgba(9, 16, 29, 0.92));
                box-shadow: var(--shadow-soft);
            }

            .invest-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 14px;
            }

            .invest-title {
                margin: 0;
                font-size: 22px;
                line-height: 1.2;
            }

            .invest-sub {
                margin: 4px 0 0;
                color: var(--muted);
                font-size: 13px;
            }

            .new-project {
                border: 1px solid rgba(89, 175, 255, 0.45);
                background: linear-gradient(120deg, rgba(23, 103, 201, 0.95), rgba(12, 84, 173, 0.95));
                color: #e9f3ff;
                padding: 9px 14px;
                border-radius: 10px;
                font-weight: 800;
                font-size: 12px;
                letter-spacing: .03em;
                text-transform: uppercase;
                cursor: pointer;
            }

            .new-project:hover {
                filter: brightness(1.08);
            }

            .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
            }

            .sum-card {
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 12px;
                background: rgba(20, 31, 52, 0.72);
            }

            .sum-label {
                color: var(--muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 6px;
            }

            .sum-value {
                font-size: 27px;
                font-weight: 900;
                line-height: 1;
            }

            .sum-value.blue {
                color: #6cc1ff;
            }

            .sum-value.green {
                color: #6ef0b5;
            }

            .sum-value.amber {
                color: #ffd97d;
            }

            .create-wrap {
                margin-top: 14px;
                border: 1px solid var(--line);
                border-radius: 12px;
                background: rgba(9, 18, 33, 0.72);
                overflow: hidden;
            }

            .create-wrap summary {
                list-style: none;
                cursor: pointer;
                padding: 12px 14px;
                font-weight: 800;
                color: #cce5ff;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: .04em;
                border-bottom: 1px solid transparent;
            }

            .create-wrap[open] summary {
                border-bottom-color: var(--line);
            }

            .create-body {
                padding: 14px;
            }

            .project-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
            }

            .project-card {
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 14px;
                background: rgba(19, 30, 50, 0.7);
                display: flex;
                flex-direction: column;
                gap: 10px;
                min-height: 210px;
            }

            .project-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 8px;
            }

            .project-name {
                margin: 0;
                font-size: 17px;
                line-height: 1.25;
            }

            .project-meta {
                color: var(--muted);
                font-size: 12px;
                line-height: 1.45;
            }

            .project-kpis {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .mini-kpi {
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 8px;
                background: rgba(8, 15, 27, 0.6);
            }

            .mini-label {
                color: var(--muted);
                font-size: 10px;
                text-transform: uppercase;
                font-weight: 700;
                letter-spacing: .05em;
            }

            .mini-value {
                margin-top: 4px;
                font-size: 14px;
                font-weight: 800;
            }

            .project-actions {
                margin-top: auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
            }

            .project-link {
                color: #91c5ff;
                font-weight: 700;
                font-size: 12px;
            }

            .project-empty {
                border: 1px dashed var(--line);
                border-radius: 12px;
                padding: 32px 14px;
                text-align: center;
                color: var(--muted);
                background: rgba(13, 22, 38, 0.6);
            }

            .status-pill {
                border-radius: 999px;
                padding: 4px 9px;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: .04em;
                border: 1px solid transparent;
            }

            .status-pill.active {
                background: rgba(63, 199, 140, 0.2);
                color: #8df0c2;
                border-color: rgba(63, 199, 140, 0.35);
            }

            .status-pill.completed {
                background: rgba(108, 193, 255, 0.18);
                color: #9ed5ff;
                border-color: rgba(108, 193, 255, 0.35);
            }

            .status-pill.paused {
                background: rgba(255, 180, 110, 0.2);
                color: #ffd299;
                border-color: rgba(255, 180, 110, 0.4);
            }

            @media (max-width: 1100px) {
                .project-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 820px) {
                .summary-grid {
                    grid-template-columns: 1fr;
                }

                .project-grid {
                    grid-template-columns: 1fr;
                }

                .invest-title {
                    font-size: 20px;
                }
            }
        </style>

        <section class="invest-hero">
            <div class="invest-top">
                <div>
                    <h2 class="invest-title">Investment Projects</h2>
                    <p class="invest-sub">Active projects are shown by default. Use filters to explore completed or paused
                        projects.</p>
                </div>
                @if ($canWrite)
                    <button type="button" class="new-project"
                        onclick="document.getElementById('new-project-panel')?.setAttribute('open','open');document.getElementById('new-project-panel')?.scrollIntoView({behavior:'smooth',block:'start'});">
                        + New Project
                    </button>
                @endif
            </div>

            <div class="summary-grid">
                <div class="sum-card">
                    <div class="sum-label">Deployed</div>
                    <div class="sum-value blue">BDT {{ number_format((float) ($summary['deployed'] ?? 0), 0) }}</div>
                </div>
                <div class="sum-card">
                    <div class="sum-label">Returned</div>
                    <div class="sum-value green">BDT {{ number_format((float) ($summary['returned'] ?? 0), 0) }}</div>
                </div>
                <div class="sum-card">
                    <div class="sum-label">Active</div>
                    <div class="sum-value amber">{{ (int) ($summary['active'] ?? 0) }}</div>
                </div>
            </div>

            @if ($canWrite)
                <details class="create-wrap" id="new-project-panel">
                    <summary>Open Project Form</summary>
                    <div class="create-body">
                        <form method="POST" action="{{ route('investments.store') }}" class="grid grid-4">
                            @csrf
                            <input type="text" name="name" placeholder="Project name" class="input" required>
                            <input type="text" name="sector" placeholder="Sector" class="input">
                            <input type="text" name="partner" placeholder="Partner" class="input">
                            <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                            <input type="number" step="0.01" min="0" name="capital_deployed" placeholder="Capital deployed"
                                class="input" required>
                            <input type="number" step="0.01" min="0" name="expected_return" placeholder="Expected return"
                                class="input">
                            <input type="number" step="0.01" min="0" name="actual_return" placeholder="Actual return"
                                class="input">
                            <select name="status" class="select" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($status === 'active')>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <textarea name="description" placeholder="Description" class="textarea"
                                style="grid-column:1 / span 2"></textarea>
                            <textarea name="notes" placeholder="Notes" class="textarea"
                                style="grid-column:3 / span 2"></textarea>
                            <button type="submit" class="primary-btn" style="grid-column:1 / -1">Save Project</button>
                        </form>
                    </div>
                </details>
            @endif
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('investments.index') }}" class="grid grid-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search project, sector, partner"
                    class="input">
                <select name="status" class="select">
                    <option value="all" @selected(($selectedStatus ?? 'active') === 'all')>All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($selectedStatus ?? 'active') === $status)>{{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Apply Filters</button>
            </form>
        </section>

        <section class="panel">
            <h3 class="section-title">Opened Project List</h3>

            @if ($investments->count() > 0)
                <div class="project-grid">
                    @foreach ($investments as $investment)
                        <article class="project-card">
                            <div class="project-head">
                                <h4 class="project-name">{{ $investment->name }}</h4>
                                <span class="status-pill {{ $investment->status }}">{{ ucfirst($investment->status) }}</span>
                            </div>

                            <div class="project-meta">
                                {{ $investment->sector ?: 'General sector' }}
                                @if ($investment->partner)
                                    · {{ $investment->partner }}
                                @endif
                                <br>
                                Started {{ $investment->date?->format('Y-m-d') }}
                            </div>

                            <div class="project-kpis">
                                <div class="mini-kpi">
                                    <div class="mini-label">Capital</div>
                                    <div class="mini-value">{{ number_format((float) $investment->capital_deployed, 2) }}</div>
                                </div>
                                <div class="mini-kpi">
                                    <div class="mini-label">Expected</div>
                                    <div class="mini-value">{{ number_format((float) ($investment->expected_return ?? 0), 2) }}
                                    </div>
                                </div>
                                <div class="mini-kpi">
                                    <div class="mini-label">Returned</div>
                                    <div class="mini-value">{{ number_format((float) ($investment->actual_return ?? 0), 2) }}</div>
                                </div>
                                <div class="mini-kpi">
                                    <div class="mini-label">Progress</div>
                                    <div class="mini-value">
                                        {{ (float) $investment->capital_deployed > 0 ? number_format(((float) ($investment->actual_return ?? 0) / (float) $investment->capital_deployed) * 100, 1) : '0.0' }}%
                                    </div>
                                </div>
                            </div>

                            <div class="project-actions">
                                <a class="project-link" href="{{ route('investments.show', $investment) }}">Open Details</a>
                                @if ($canWrite)
                                    <details>
                                        <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                        <div style="margin-top:10px" class="stack">
                                            <form method="POST" action="{{ route('investments.update', $investment) }}" class="stack">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $investment->name }}" class="input" required>
                                                <input type="text" name="sector" value="{{ $investment->sector }}" class="input">
                                                <input type="text" name="partner" value="{{ $investment->partner }}" class="input">
                                                <input type="date" name="date" value="{{ $investment->date?->format('Y-m-d') }}"
                                                    class="input" required>
                                                <input type="number" step="0.01" min="0" name="capital_deployed"
                                                    value="{{ (float) $investment->capital_deployed }}" class="input" required>
                                                <input type="number" step="0.01" min="0" name="expected_return"
                                                    value="{{ (float) ($investment->expected_return ?? 0) }}" class="input">
                                                <input type="number" step="0.01" min="0" name="actual_return"
                                                    value="{{ (float) ($investment->actual_return ?? 0) }}" class="input">
                                                <select name="status" class="select" required>
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}" @selected($investment->status === $status)>
                                                            {{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="description" class="textarea">{{ $investment->description }}</textarea>
                                                <textarea name="notes" class="textarea">{{ $investment->notes }}</textarea>
                                                <button type="submit" class="soft-btn">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('investments.destroy', $investment) }}"
                                                onsubmit="return confirm('Delete investment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="danger-btn">Delete</button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="margin-top:14px">{{ $investments->links() }}</div>
            @else
                <div class="project-empty">
                    No projects matched your filter. Try switching status to All or create a new project.
                </div>
            @endif
        </section>
    </div>
@endsection