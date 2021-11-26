@extends('errors.minimal')

@section('title', __('Not Found'))
{{-- @section('code', '404') --}}
@section('message')
<div class="not-found-wrap text-center">
    <img src="{{ asset('icon/404-error.svg') }}" style="width: 84px; height: 84px; opacity: 0.8;"/>
    <h1 class="text-60">Error 404</h1>
    <p class="mb-5 text-muted text-18">The page you requested cannot be found.</p>
    <p>It may have been removed, or just moved to a new URL.  Please double-check that you typed the URL correctly, or try below link:</p>
    <p>
        <a class="btn btn-lg btn-primary btn-rounded" href="{{ route('dashboard') }}">Go back to home</a>
    </p>
</div>
@endsection
