@extends('errors.minimal')

@section('title', __('Not Found'))
{{-- @section('code', '404') --}}
@section('message')
<div class="not-found-wrap text-center">
    <img src="{{ asset('icon/404-error.svg') }}" style="width: 84px; height: 84px; opacity: 0.8;"/>
    <h1 class="text-60">Error 404</h1>
    {{-- <p class="mb-5 text-muted text-18">The page you requested cannot be found.</p> --}}
    <p>No cuenta con los privilegios suficientes para ver la página solicitada. Puede pedir cambio de privilegios a cualquier usuario del Despacho que tenga permiso para editarlos.</p>
    <p>
        @php
            $url = url('/');
            if(auth()->check()) {
                if(auth()->user()->user_level == '2') {
                    $url = route('client/home');
                } else {
                    $url = route('dashboard');
                }
            }
        @endphp
        <a class="btn btn-lg btn-primary btn-rounded" href="{{ $url }}">Go back to home</a>
    </p>
</div>
@endsection
