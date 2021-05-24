@extends('layouts.master')
@section('title', 'Event Types
')
@section('main-content')

<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <a href={{BASE_URL}}events?view=month>Back to Calendar</a>               
                <h4 class="mt-4">Event Types</h4>
             
                <div>
                    <div class="row ">
                        <div class="col-2">Manage your Event Types</div>
                        <div class="col-4">
                            <div class="float-right">
                                <div>
                                    <div><button type="button" class="mr-2 btn btn-secondary">Add Event Type</button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <?php foreach($allEventType as $k){ ?>
                            <li class="list-group-item">
                                <div class="align-items-center category-row-details m-2 d-flex row ">
                                    <div class="col-1">
                                        <div style="cursor: grab;"><i aria-hidden="true" class="fa fa-bars text-black-50"></i></div>
                                    </div>
                                    <div class="col-2">  
                                        <div id="{{$k->id}}" onclick="openBox({{$k->id}})" class="colorSelector colorSelector_{{$k->id}}" ></div>
                                        <input type="text" class="colorSelectorText" name="Ccode" value="{{$k->color_code}}" id="Ccode">
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control" value="{{$k->title}}">
                                    </div>
                                    <div class="col-2">
                                        <div class="float-right">
                                            <button type="button" class="btn btn-link btn-sm"><i class="delete-icon" title="Delete"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .colorSelector {
        background: url('{{BASE_LOGO_URL}}/public/assets/styles/css/images/select.png');
    }
</style>
@endsection
@section('page-js')

<script type="text/javascript">
    $(document).ready(function () {
        // $('.colorSelector').ColorPicker({
        //     flat:false,
        //     livePreview:true,
        //     onShow: function (colpkr) {
        //         $(colpkr).fadeIn(500);
        //         return false;
        //     },
        //     onHide: function (colpkr) {
        //         $(colpkr).fadeOut(500);
        //         return false;
        //     },
        //     onChange: function (hsb, hex, rgb) {
        //         // $('#'+cid).css('backgroundColor', '#' + hex);
        //         $("#Ccode").val(hex);
        //     }
        // }).bind('keyup', function(){
        //     alert("S")
        // });;    
    });

    function openBox(id){
        callId(id);
        $('.colorSelector_'+id).trigger("click");
    }
    function callId(id){
        $('.colorSelector_'+id).ColorPicker({
            flat:false,
            livePreview:true,
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
                onChange: function (hsb, hex, rgb) {
                    $('#'+id).css('backgroundColor', '#' + hex);
                    $("#Ccode").val(hex);
            }
        })
    }
</script>
@stop
