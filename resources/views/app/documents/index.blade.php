@extends('layouts.app')

@section('title', 'Documents | SIPR')
@section('pageTitle', 'Documents')
@section('pageSubtitle', 'Upload references, links, and records for the organization.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canWrite = in_array($role, ['admin', 'finance', 'secretary'], true);
        $canDelete = in_array($role, ['admin', 'secretary'], true);
    @endphp

    <div class="page-stack">
        <section class="panel">
            <form method="GET" action="{{ route('documents.index') }}" class="grid grid-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or link" class="input">
                <select name="category" class="select">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ str($category)->replace('-', ' ')->title() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        @if ($canWrite)
            <section class="panel">
                <h3 class="section-title">Add Document</h3>
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="grid grid-2">
                    @csrf
                    <input type="text" name="name" placeholder="Document name" class="input" required>
                    <select name="category" class="select" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ str($category)->replace('-', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <input type="url" name="url" placeholder="External URL (optional if file selected)" class="input">
                    <input type="file" name="file" class="input">
                    <button type="submit" class="primary-btn" style="grid-column:1 / -1">Add document</button>
                </form>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Document Library</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Category</th><th>Source</th><th>Uploaded By</th><th>Date</th>@if ($canDelete)<th>Action</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td><a href="{{ route('documents.show', $document) }}">{{ $document->name }}</a></td>
                                <td>{{ str($document->category)->replace('-', ' ')->title() }}</td>
                                <td>
                                    @if ($document->url)
                                        <a href="{{ $document->url }}" target="_blank" rel="noopener">Open Link</a>
                                    @elseif ($document->file_path)
                                        <a href="{{ route('documents.download', $document) }}">Download File</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $document->uploader?->name }}</td>
                                <td>{{ $document->created_at?->format('Y-m-d H:i') }}</td>
                                @if ($canDelete)
                                    <td>
                                        <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Delete document?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-btn">Delete</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canDelete ? 6 : 5 }}" class="empty">No documents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $documents->links() }}</div>
        </section>
    </div>
@endsection
