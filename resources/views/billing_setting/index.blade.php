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
                        <div id="firm-billing-defaults">
                            @include('billing_setting.partial.view_invoice_preferences')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('page-js-inner')
<script>
$(document).on("click", ".edit-billing-defaults", function() {
    var settingId = $(this).attr("data-setting-id");
    var url = $(this).attr("data-url");
    $.ajax({
        url: url,
        type: 'GET',
        data: {setting_id: settingId},
        success: function(data) {
            $("#firm-billing-defaults").html(data);
        }
    })
});

$(document).on("click", "#save_billing_settings", function() {
    $.ajax({
        url: $("#billing_defaults_form").attr("action"),
        type: 'POST',
        data: $("#billing_defaults_form").serialize(),
        success: function(data) {
            $("#firm-billing-defaults").html(data);
        }
    })
});

$(document).on("click", "#cancel_edit_billing_settings", function() {
    var settingId = $(this).attr("data-setting-id");
    var url = $(this).attr("data-url");
    $.ajax({
        url: url,
        type: 'GET',
        data: {setting_id: settingId},
        success: function(data) {
            $("#firm-billing-defaults").html(data);
        }
    })
});
</script>
@stop
@endsection
