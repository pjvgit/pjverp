@extends('errors.minimal')

@section('title', __('Forbidden'))
{{-- @section('code', '403') --}}
@section('message')
<style>
div.show_red_box {
    border: 3px solid #a56867;
    background-color: #f3f3f3;
    padding: 10px 25px;
    margin: 0;
    border-radius: 8px;
    -webkit-box-shadow: 3px 3px 5px 0 #666;
    box-shadow: 3px 3px 5px 0 #666;
}
</style>
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
    <div style="padding: 10px 20px;">
        <div class="show_red_box">
            <table style="width: 100%;">
                <tbody><tr>
                    <td style="width: 35px;">
                        <img src="https://assets.mycase.com/packs/exclaimation_red-4c16104e84.jpg">
                    </td>
                    <td style="color: #737373; font-weight: bold;">
                        Access to this invoice is no longer allowed because the invoice has been unshared or removed by a linked firm user.
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p>
        <a href="{{ route('dashboard') }}" style="margin-right: 5px;">Home</a>
        
        <a href="{{ route('autologout') }}" class="ms-5" style="margin-left: 5px;">Log out</a>
    </p>

</div>

@endsection