@extends('layouts.master')
@section('title', 'Client Billing & Invoice Settings')

@section('main-content')
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class="breadcrumb">
            <h3>Client Billing & Invoice Settings</h3>
        </div>
        <div class="row">
            <div class="col-md-4">
                <h6>Billing &amp; Invoice Preferences</h6>
                <p>Update billing and invoice preferences for your firm.</p>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div id="firm-payment-defaults">
                            @include('billing_setting.partial.edit_payment_preferences')
                        </div>
                    </div>
                </div>
                <div class="my-4"></div>
                <div class="card">
                    <div class="card-body">
                        <div id="firm-billing-defaults">
                            @include('billing_setting.partial.view_invoice_preferences')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="row ">
            <div class="pr-5 col-md-4">
                <h6>Invoice Customization</h6>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div id="invoice-customization-defaults">
                            @include('billing_setting.partial.view_invoice_customization')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('bottom-js')
<script src="{{ asset('assets\js\custom\billing_setting\index.js?').env('CACHE_BUSTER_VERSION') }}"></script>
@stop
@endsection
