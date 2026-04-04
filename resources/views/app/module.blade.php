@extends('layouts.app')

@section('title', $title.' | SIPR')
@section('pageTitle', $title)
@section('pageSubtitle', 'Module shell created. Next step: implement controllers, requests, policies, and UI for '.$module.'.')

@section('content')
    <section class="hero">
        <div class="hero-kicker">Module Shell</div>
        <h2>{{ $title }}</h2>
        <p>This area is reserved for the remaining SIPR module surface.</p>
    </section>
@endsection