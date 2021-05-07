<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    @yield('before-css')
    {{-- theme css --}}
    <link id="gull-theme" rel="stylesheet" href="{{  asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/vendor/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome-free-5.10.1-web/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/metisMenu.min.css') }}">

    <link rel="stylesheet" href="{{asset('public/assets/styles/css/plugins/toastr.css')}}" />
    {{-- page specific css --}}
    @yield('page-css')
</head>

<body class="text-left">
    @php
    $layout = session('layout');
    @endphp
    <!-- Pre Loader Strat  -->
    <div class='loadscreen' id="preloader">
        <div class="loader spinner-bubble spinner-bubble-primary">
        </div>
    </div>
    {{-- normal layout --}}
    <div class="app-admin-wrap layout-horizontal-bar">
        <div class="main-header">
            <div class="logo"> <img src="{{asset('assets/images/logo.png')}}" alt="">
            </div>
            <div class="menu-toggle">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="d-flex align-items-center">
                <!-- Mega menu-->
                <div class="dropdown mega-menu d-none d-md-block"><a class="btn text-muted dropdown-toggle mr-3"
                        id="dropdownMegaMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">Mega Menu</a>
                    <div class="dropdown-menu text-left" aria-labelledby="dropdownMenuButton">
                        <div class="row m-0">
                            <div class="col-md-4 p-4 bg-img">
                                <h2 class="title">Mega Menu <br /> Sidebar</h2>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Asperiores natus laboriosam
                                    fugit, consequatur.</p>
                                <p class="mb-4">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Exercitationem
                                    odio amet eos dolore suscipit placeat.</p>
                                <button class="btn btn-lg btn-rounded btn-outline-warning">Learn More</button>
                            </div>
                            <div class="col-md-4 p-4">
                                <p class="text-primary text--cap border-bottom-primary d-inline-block">Features</p>
                                <div class="menu-icon-grid w-auto p-0"><a href="#"><i class="i-Shop-4"></i> Home</a><a
                                        href="#"><i class="i-Library"></i> UI Kits</a><a href="#"><i class="i-Drop"></i>
                                        Apps</a><a href="#"><i class="i-File-Clipboard-File--Text"></i> Forms</a><a
                                        href="#"><i class="i-Checked-User"></i> Sessions</a><a href="#"><i
                                            class="i-Ambulance"></i> Support</a></div>
                            </div>
                            <div class="col-md-4 p-4">
                                <p class="text-primary text--cap border-bottom-primary d-inline-block">Components</p>
                                <ul class="links">
                                    <li><a href="accordion.html">Accordion</a></li>
                                    <li><a href="alerts.html">Alerts</a></li>
                                    <li><a href="buttons.html">Buttons</a></li>
                                    <li><a href="badges.html">Badges</a></li>
                                    <li><a href="carousel.html">Carousels</a></li>
                                    <li><a href="lists.html">Lists</a></li>
                                    <li><a href="popover.html">Popover</a></li>
                                    <li><a href="tables.html">Tables</a></li>
                                    <li><a href="datatables.html">Datatables</a></li>
                                    <li><a href="modals.html">Modals</a></li>
                                    <li><a href="nouislider.html">Sliders</a></li>
                                    <li><a href="tabs.html">Tabs</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / Mega menu-->
                <div class="search-bar">
                    <input type="text" placeholder="Search" /><i class="search-icon text-muted i-Magnifi-Glass1"></i>
                </div>
            </div>
            <div style="margin: auto"></div>
            <div class="header-part-right">
                <!-- Full screen toggle--><i class="i-Full-Screen header-icon d-none d-sm-inline-block"
                    data-fullscreen=""></i>
                <!-- Grid menu Dropdown-->


            </div>
        </div>
        <!-- header top menu end-->
        <div class="horizontal-bar-wrap">
            <div class="header-topnav">
                <div class="container-fluid">
                    <div class="topnav rtl-ps-none" id="" data-perfect-scrollbar="" data-suppress-scroll-x="true">
                        <ul class="menu float-left">
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">Features</label><a href="#"><i
                                                class="nav-icon mr-2 i-Bar-Chart"></i> Features</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li><a href="dashboard1.html"><i class="nav-icon mr-2 i-Clock-3"></i><span
                                                        class="item-name">Version 1</span></a></li>
                                            <li><a href="dashboard2.html"><i class="nav-icon mr-2 i-Clock-4"></i><span
                                                        class="item-name">Version 2</span></a></li>
                                            <li><a href="dashboard3.html"><i class="nav-icon mr-2 i-Over-Time"></i><span
                                                        class="item-name">Version 3</span></a></li>
                                            <li><a href="dashboard4.html"><i class="nav-icon mr-2 i-Clock"></i><span
                                                        class="item-name">Version 4</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">UI kits</label><a href="#"><i
                                                class="nav-icon mr-2 i-Suitcase"></i> Pricing</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li><a href="alerts.html"><i class="nav-icon mr-2 i-Bell1"></i><span
                                                        class="item-name">Alerts</span></a></li>
                                            <li><a href="accordion.html"><i
                                                        class="nav-icon mr-2 i-Split-Horizontal-2-Window"></i><span
                                                        class="item-name">Accordion</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <!-- end ui kits-->
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">Extra UI kits</label><a href="#"><i
                                                class="nav-icon i-Library mr-2"></i> Why Legalcase</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li><a href="cards.html"><i class="nav-icon mr-2 i-Line-Chart-2"></i><span
                                                        class="item-name">Cards</span></a></li>
                                            <li><a href="card.metrics.html"><i class="nav-icon mr-2 i-ID-Card"></i><span
                                                        class="item-name">Card Metrics</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <!-- end extra uikits-->
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">Apps</label><a href="#"><i
                                                class="nav-icon mr-2 i-Computer-Secure"></i> Happy Customer</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li><a href="invoice.html"><i class="nav-icon mr-2 i-Add-File"></i><span
                                                        class="item-name">Invoice</span></a></li>
                                            <li><a href="inbox.html"><i class="nav-icon mr-2 i-Email"></i><span
                                                        class="item-name">Inbox</span></a></li>
                                            <li><a href="chat.html"><i class="nav-icon mr-2 i-Speach-Bubble-3"></i><span
                                                        class="item-name">Chat</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <!-- end apps-->
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">Blog</label><a href="#"><i
                                                class="nav-icon mr-2 i-File-Clipboard-File--Text"></i> Blog</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li><a href="form.basic.html"><i
                                                        class="nav-icon mr-2 i-File-Clipboard-Text--Image"></i><span
                                                        class="item-name">Basic Elements</span></a></li>
                                            <li><a href="form.layouts.html"><i
                                                        class="nav-icon mr-2 i-Split-Vertical"></i><span
                                                        class="item-name">Form Layouts</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <!-- end Forms-->
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2">Charts</label><a href="#"><i
                                                class="nav-icon mr-2 i-Bar-Chart-5"></i> Support</a>
                                        <input id="drop-2" type="checkbox" />
                                        <ul>
                                            <li class="nav-item"><a href="charts.echarts.html" title="charts"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i><span
                                                        class="item-name">echarts</span></a></li>
                                            <li class="nav-item"><a href="charts.chartsjs.html"><i
                                                        class="nav-icon mr-2 i-File-Clipboard-Text--Image"></i><span
                                                        class="item-name">ChartJs</span></a></li>
                                            <li><a href="charts.apexAreaCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Area</a></li>
                                            <li><a href="charts.apexBarCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Bar</a></li>
                                            <li><a href="charts.apexBubbleCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Bubble</a></li>
                                            <li><a href="charts.apexColumnCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Column</a></li>
                                            <li><a href="charts.apexCandleStickCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>CandleStick</a></li>
                                            <li><a href="charts.apexLineCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Line</a></li>
                                            <li><a href="charts.apexMixCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Mix</a></li>
                                            <li><a href="charts.apexPieDonutCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>PieDonut</a></li>
                                            <li><a href="charts.apexRadarCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Radar</a></li>
                                            <li><a href="charts.apexRadialBarCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>RadialBar</a></li>
                                            <li><a href="charts.apexScatterCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Scatter</a></li>
                                            <li><a href="charts.apexSparklineCharts.html"><i
                                                        class="nav-icon mr-2 i-Bar-Chart-2"></i>Sparkline</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            

                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2"></label>
                                        <?php  if(isset(Auth::User()->id)){ ?>
                                            <a href="{{ route('dashboard') }}"><i class="nav-icon mr-2 i-Safe-Box1"></i>Dashboard </a>
                                        <?php }else{ ?>
                                            <a href="{{ route('login') }}"><i class="nav-icon mr-2 i-Safe-Box1"></i>Login </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <div>
                                        <label class="toggle" for="drop-2"></label><a
                                            href="{{ route('register') }}"><i
                                                class="nav-icon mr-2 i-Safe-Box1"></i>Start Free Trial</a>
                                    </div>
                                </div>
                            </li>
                            <!--end-doc  -->
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============ Body content start ============= -->
        <div class="main-content-wrap d-flex flex-column">
            <div class="main-content">
                <div class="row">
                    <div class="col-md-1">

                    </div>
                    <div class="col-md-10">
                        @yield('content')
                    </div>
                    <div class="col-md-1">

                    </div>
                </div>
            </div>
            <!-- Footer Start -->
            <div class="flex-grow-1"></div>
            <div class="app-footer">
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Gull - Laravel 7 + Bootstrap 4 admin Dashboard template</strong></p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Libero quis beatae officia saepe
                            perferendis
                            voluptatum minima eveniet voluptates dolorum, temporibus nisi maxime nesciunt totam
                            repudiandae commodi
                            sequi dolor quibusdam
                            sunt.
                        </p>
                    </div>
                </div>
                <div class="footer-bottom border-top pt-3 d-flex flex-column flex-sm-row align-items-center">
                    <span class="flex-grow-1"></span>
                    <div class="d-flex align-items-center">
                        <img class="logo" src="{{asset('assets/images/logo.png')}}" alt="">
                        <div>
                            <p class="m-0">&copy; {{date('Y')}} {{config('app.name')}}</p>
                            <p class="m-0">All rights reserved</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fotter end -->
        </div>
        <!-- ============ Body content End ============= -->
    </div>
    <!--=============== End app-admin-wrap ================-->

    <!-- ============ Search UI End ============= -->
    {{-- @include('layouts.large-sidebar-customizer') --}}
    <!-- ============ Large Sidebar Layout End ============= -->

    {{-- common js --}}
    <script src="{{  asset('assets/js/common-bundle-script.js')}}"></script>
    {{-- page specific javascript --}}
    @yield('page-js')
    <script src="{{asset('assets/js/script.js')}}"></script>
    <script src="{{asset('assets/js/sidebar-horizontal.script.js')}}"></script>
    <script src="{{asset('assets/js/customizer.script.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/toastr.min.js')}}"></script>

    @if ($message = Session::get('success'))
    <p>{{ $message }}</p>
    <script>
        toastr.success('{{ $message }}', "", {
            progressBar: !0
        })

    </script>
    @endif
    @if ($message = Session::get('error'))
    <p>{{ $message }}</p>
    <script>
        toastr.error('{{ $message }}', "", {
            progressBar: !0
        })

    </script>
    @endif


    @yield('bottom-js')
</body>

</html>
