@extends('errors.minimal')

@section('title', __('Forbidden'))
{{-- @section('code', '403') --}}
@section('message')

<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="alert alert-danger browser-warning">
                <span class="fa fa-lg fa-exclamation-triangle"></span>
                You are using an unsupported browser.
                <a href="http://support.mycase.com/customer/portal/articles/646538-what-web-browser-should-i-use-for-running-mycase-">Learn More</a>
            </div>
        </div>
    </div>
    <h1>Forbidden</h1>

    <p> Your account does not have access to the requested application. </p>

    <p> It's possible you've reached this page due to an out of date link or bookmark. If so, please update your link and try again. </p>

    <p>
    <a href="{{ route('autologout') }}">Log out</a>
    </p>

</div>

@endsection