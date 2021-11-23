@extends('errors.minimal')

@section('title', __('Forbidden'))
{{-- @section('code', '403') --}}
@section('message')

<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="alert alert-danger browser-warning">
                <div class="logo">
                    <img src="{{ @firmDetail(auth()->user()->firm_name)->firm_logo_url }}" alt="" style="width: 120px; height: auto;">
                </div>
            </div>
        </div>
    </div>
    <h1>Forbidden</h1>

    <p> Your account does not have access to the requested application. </p>

    <p> It's possible you've reached this page due to an out of date link or bookmark. If so, please update your link and try again. </p>

    <p>
    <a href="{{ route('dashboard') }}">Home</a>
    </p>
    <p>
    <a href="{{ route('autologout') }}">Log out</a>
    </p>

</div>

@endsection