@extends('layouts.app')

@section('title', 'Document | SIPR')
@section('pageTitle', 'Document Detail')
@section('pageSubtitle', 'Review the document metadata and open the stored source securely.')

@section('content')
    <div class="page-stack">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Document #{{ $document->id }}</div>
                    <h2>{{ $document->name }}</h2>
                    <p>{{ str($document->category)->replace('-', ' ')->title() }} · Uploaded by {{ $document->uploader?->name ?? '-' }}</p>
                </div>
                <a class="soft-btn" href="{{ route('documents.index') }}">Back to documents</a>
            </div>
        </section>

        <section class="panel">
            <div class="grid grid-2">
                <div>
                    <h3 class="section-title">Document Metadata</h3>
                    <p><strong>Category:</strong> {{ str($document->category)->replace('-', ' ')->title() }}</p>
                    <p><strong>Uploaded By:</strong> {{ $document->uploader?->name ?? '-' }}</p>
                    <p><strong>Uploaded At:</strong> {{ $document->created_at?->format('Y-m-d H:i') ?? '-' }}</p>
                    <p><strong>Updated At:</strong> {{ $document->updated_at?->format('Y-m-d H:i') ?? '-' }}</p>
                </div>

                <div>
                    <h3 class="section-title">Source</h3>
                    @if ($document->url)
                        <p><strong>External Link:</strong> <a href="{{ $document->url }}" target="_blank" rel="noopener">Open link</a></p>
                    @else
                        <p><strong>External Link:</strong> -</p>
                    @endif
                    @if ($document->file_path)
                        <p><strong>Stored File:</strong> {{ $document->file_path }}</p>
                        <p><a class="primary-btn" href="{{ route('documents.download', $document) }}">Download File</a></p>
                    @else
                        <p><strong>Stored File:</strong> -</p>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
