<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('public/images/fav.png')}}" />
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="{{ asset('favicon.png')}}" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Ionicons -->
    {{-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> --}}
    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css')}}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css')}}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css')}}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.css')}}">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.css')}}">

    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css')}}">
    
    <link rel="stylesheet" href="{{ asset('/css/custom.css')}}">

    <script>
        var baseUrl = '<?php echo URL('/'); ?>';
       
    </script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <div class="loader"></div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
               
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
            
                {{-- <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a data-toggle="modal" data-target="#logoutModel" data-placement="bottom" href="javascript:;" class="btn btn-danger btn-sm"><i data-toggle="tooltip" data-placement="bottom"  class="fas fa-sign-out-alt"></i> </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-light-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('user.index') }}" class="brand-link">
             
                <img src="{{ asset('/images/Sensytec_logo.png')}}" style="width: 100%;" alt="{{ config('app.name')}}">
                {{-- <span class="brand-text font-weight-light">{{ config('app.name')}}</span> --}}
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <?php
                            $controllerLoad= new App\Http\Controllers\BaseController();
                            $profile=Auth::user()->profile_image;
                            if($controllerLoad->fileExists(USER_IMAGE_FOLDER_PATH."thumb/".$profile)){
                                $profile=AMAZON_AWS_BASE_PATH.USER_IMAGE_FOLDER_PATH."thumb/".$profile;
                            }else{
                                $profile=asset('/images/default/profile.png');
                            }
                        ?>
                        <a href="{{ route('users.profile') }} " class="d-block"> <img src="{{$profile}}" class="img-circle elevation-2" alt="Profile Image"></a>
                    </div>
                    <div class="info">
                        <a href="{{ route('users.profile') }} " class="d-block">
                            <?php
                            $username=Auth::user()->first_name ." ". Auth::user()->last_name;
                            echo substr($username,0,20);
                            ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                @include('pages.sidebar')
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        <div id="logoutModel" class="modal fade" role="dialog">
            <div class="modal-dialog ">
                <!-- Modal content-->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" >
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-danger">
                            <h4 class="modal-title">Logout Confirmation?</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                           
                            <p>Do you really want to logout from the system?</p>
                            
                            <p class="logoutTimerAlert">You have a timer running right now. If you logout, your active timer will be paused.</p>
                            
                        </div>
                        <div class="modal-footer">
                            <center>
                                <button type="button" class="btn btn-success" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="sss" class="btn btn-danger">Yes, Sure</button>
                            </center>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @yield('content')
        
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{date('Y')}} <a href="https://sensytec.com/">Sensytec</a>.</strong>
            All rights reserved.
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js')}}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
        $(".loader").fadeOut("slow");
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>

    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <script src="{{ asset('plugins/select2/js/select2.full.min.js')}}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('plugins/chart.js/Chart.min.js')}}"></script>
    <!-- JQVMap -->
    <script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js')}}"></script>
    <script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js')}}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js')}}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('plugins/moment/moment.min.js')}}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js')}}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
    <!-- Summernote -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.js')}}"></script>
    <!-- Toastr -->
    <script src="{{ asset('plugins/toastr/toastr.min.js')}}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.js')}}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js')}}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('dist/js/demo.js')}}"></script>

    <script src="{{ asset('public/js/custom.js')}}"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
        $(function () {
            $('.select1').select2()
            $('.select2').select2()
            $("#example11").DataTable();
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
            });
            var dataTable =  $('#example1').DataTable( {
				serverSide: true,
				ajax:{
						url :"user/load", // json datasource
						type: "post",  // method  , by default get
						error: function(){  // error handling
							$(".employee-grid-error").html("");
							$("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
							$("#employee-grid_processing").css("display","none");
						}
					},
				dom: "frtiS",
				scrollY: 200,
				deferRender: true,
				scrollCollapse: true,
				scroller: {
				    loadingIndicator: true
				}
			    } );
        });
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

    </script>

    @if ($message = Session::get('success'))
    <p>{{ $message }}</p>
    <script>
        toastr.success('{{ $message }}')
    </script>
    @endif
    @if ($message = Session::get('error'))
    <p>{{ $message }}</p>
    <script>
        toastr.error('<p>{{ $message }}</p>')

    </script>
    @endif
    @yield('page-js-script')

</html>
