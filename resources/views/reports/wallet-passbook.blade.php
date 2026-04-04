<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Wallet Passbook</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            margin-bottom: 14px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .meta {
            margin-top: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">SIPR Wallet Passbook</div>
        <div class="meta">Generated: {{ $generatedAt->format('Y-m-d H:i') }}</div>
        <div class="meta">Member: {{ $member->name }} ({{ $member->member_id }})</div>
        <div class="meta">Available: {{ number_format((float) ($wallet->available ?? 0), 2) }} | Locked:
            {{ number_format((float) ($wallet->locked ?? 0), 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Label</th>
                <th>Amount</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ $entry->date?->format('Y-m-d') }}</td>
                    <td>{{ strtoupper($entry->type) }}</td>
                    <td>{{ $entry->label }}</td>
                    <td>{{ number_format((float) $entry->amount, 2) }}</td>
                    <td>{{ $entry->note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No passbook entries.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>