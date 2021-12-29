@extends('admin_panel.layouts.master')
@section('page-title', 'Usuarios')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/sweetalert2.min.css')}}" />
@endsection
@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h1>Perfil Usuario</h1>
    <button class="btn btn-primary exportAllStaff">Exportar usuario</button>
    <a href="{{ route('admin/userlist') }} " class="m2"><span class="text-info">Lista de usuarios</span></a>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row" bladefile="resources/views/admin_panel/users/index.blade.php">    
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-2">                
                <h5 class="d-flex justify-content-start align-items-center">Email
                </h5>
                <div class="d-flex justify-content-start align-items-center">
                    <input type="text" placeholder="Escriba aquí la dirección de correo electrónico" class="col-md-4 form-control" id="search_staff" value="" required>
                    <button class="btn btn-primary search_staff">Ver perfil</button>
                </div>                
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 loadStaffData"></div>
</div>
@endsection
@section('page-js')
<script src="{{asset('assets/js/plugins/sweetalert2.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $("#search_staff").val();
        $(".search_staff").on('click', function(){
            if($("#search_staff").val() == '') {
                $("#search_staff").focus();
                $(".loadStaffData").html('');
            }else {
                $("#preloader").show();
                $.ajax({
                    url : '{{ route("admin/loadallstaffdata") }}',
                    headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data : {'email' : $("#search_staff").val()},
                    type : "POST",
                    success: function (res) {
                        $(".loadStaffData").html('').html(res);
                        $("#preloader").hide();
                    }
                });
            }
        });
        
        $(".exportAllStaff").on('click', function(){
            $("#preloader").show();
            $.ajax({
                url : '{{ route("admin/exportAllStaff") }}',
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type : "GET",
                success: function (res) {
                    $("#preloader").hide();
                    swal('Success!', res.msg, 'success');
                    window.open(res.url);
                }
            });
        });

        $(document).on('click', '.searchStaff', function() { 
            $("#preloader").show();
            $.ajax({
                url : '{{ route("admin/checkStaffDetails") }}',
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data : {'staff_id' : $(this).attr('staff_id')},
                type : "POST",
                success: function (res) {
                    $(".loadStaffData").html('').html(res);
                    $("#preloader").hide();
                }
            });            
        });
    });
</script>
@endsection