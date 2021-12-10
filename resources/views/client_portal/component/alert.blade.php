@if ($message = Session::get('message'))
<div class="m-alert m-alert--icon alert alert-success" role="alert" id="m_form_1_msg">
    <div class="m-alert__icon">
        <i class="la la-check"></i>
    </div>
    <div class="m-alert__text">
        {{ $message }}
    </div>
    <div class="m-alert__close">
        <button type="button" class="close" data-close="alert" aria-label="Close">
        </button>
    </div>
</div>
@endif

@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@endif



@if ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@endif

@if(session()->get('status') && auth()->check())
    <div class="alert alert-success alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ session()->get('status') }}</strong>
    </div>
@endif
