@extends('layouts.master')
@section('title','Case Revenue - Report')
@section('main-content')

<div class="row d-flex">
    <div class="col-md-2">
    @include('reports.sidebar')
    </div>    
    <div class="col-md-10">
        <div class="nav-header">
            <h3 class="font-weight-bold">
                Case Revenue Report
            </h3>
        </div>
        <div class="card text-left">
            <div class="card-body">
                <div class="d-flex align-items-center pl-2 pb-2">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>
@stop
