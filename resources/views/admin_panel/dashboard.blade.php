@extends('admin_panel.layouts.master')

@section('page-title', 'Dashboard')

@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/daterangepicker.css')}}">
@endsection

@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h1>Dashboard</h1>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
    <div>
        <form id="applyFilter" name="applyFilter">
            @csrf
            <input type="text" class="form-control" id="daterange" name="date_range" value="{{$date_range}}" placeholder="" />
        </form>
    </div>
    
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <!-- CARD ICON -->
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6 m2">
                <div class="card card-icon mb-4">
                    <div class="card-body text-center">
                        <i class="i-Data-Upload"></i>
                        <p class="text-muted mt-2 mb-2">Today's Upload</p>
                        <p class="text-primary text-24 line-height-1 m-0">21</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="card card-icon mb-4">
                    <div class="card-body text-center">
                        <i class="i-Add-User"></i>
                        <p class="text-muted mt-2 mb-2">New sign ups</p>
                        <p class="text-primary text-24 line-height-1 m-0">{{ $signupUsers }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 m2">
                <div class="card card-icon mb-4">
                    <div class="card-body text-center">
                        <i class="i-Money-2"></i>
                        <p class="text-muted mt-2 mb-2">Total sales</p>
                        <p class="text-primary text-24 line-height-1 m-0">4021</p>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 col-md-6 col-sm-6 m2">
                <div class="card card-icon-big mb-4">
                    <div class="card-body text-center">
                        <i class="i-Money-2"></i>
                        <p class="line-height-1 text-title text-18 mt-2 mb-0">4021</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 m2">
                <div class="card card-icon-big mb-4">
                    <div class="card-body text-center">
                        <i class="i-Gear"></i>
                        <p class="line-height-1 text-title text-18 mt-2 mb-0">4021</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 m2">
                <div class="card card-icon-big mb-4">
                    <div class="card-body text-center">
                        <i class="i-Bell"></i>
                        <p class="line-height-1 text-title text-18 mt-2 mb-0">4021</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-0">
                <h5 class="card-title m-0 p-3">New sign ups</h5>
                @if($signupUsers == 0)
                <h6 class="text-center"> No Record Found </h6>
                @else
                <div id="basicLine" style="height: 300px"></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script src="{{asset('assets/js/vendor/echarts.min.js')}}"></script>
<script src="{{asset('assets/js/es5/echart.options.min.js')}}"></script>
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/daterangepicker.min.js')}}"></script>
<script>
    @if($signupUsers > 0)
    var s6Data  = new Array();
    var label6Data  = new Array();
    <?php foreach($signupChartUsers as $key => $val){ ?>
        s6Data.push('<?php echo $val; ?>');
        label6Data.push('<?php echo $key; ?>');
    <?php } ?>
    var monthlySeriesData = s6Data;
    var monthlylabelsData = label6Data;

    // line charts
    // ================= Basic Line ================
    let basicLineElem = document.getElementById('basicLine');
    if (basicLineElem) {
        let basicLine = echarts.init(basicLineElem);
        basicLine.setOption({
            tooltip: {
                show: true,
                trigger: 'axis',
                axisPointer: {
                    type: 'line',
                    animation: true
                }
            },
            grid: {
                top: '10%',
                left: '40',
                right: '40',
                bottom: '40'
            },
            xAxis: {
                type: 'category',
                data: monthlylabelsData,
                axisLine: {
                    show: false
                },
                axisLabel: {
                    show: true
                },
                axisTick: {
                    show: false
                }
            },
            yAxis: {
                type: 'value',
                axisLine: {
                    show: false
                },
                axisLabel: {
                    show: true
                },
                axisTick: {
                    show: false
                },
                splitLine: {
                    show: true
                }
            },
            series: [{
                    data: monthlySeriesData,
                    type: 'line',
                    showSymbol: true,
                    smooth: true,
                    color: '#639',
                    lineStyle: {
                        opacity: 1,
                        width: 2,
                    },
                },
            ]
        });
        $(window).on('resize', function() {
            setTimeout(() => {
                basicLine.resize();
            }, 500);
        });
    }
    @endif

    $('#daterange').daterangepicker({
        locale: {
            applyLabel: 'Select'
        },
        ranges: {
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
        },
        "showCustomRangeLabel": false,
        "alwaysShowCalendars": true,
        "autoUpdateInput": true,
        "opens": "center",
        "minDate": "01/01/2020"
    }, function (start, end, label) {
        $("#daterange").val(label);
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $("#applyFilter").submit();
    });
</script>
@endsection