<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('images/fav.png')}}" />
    <title>Invoice</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/plugins/toastr.css')}}" />
</head>
<?php $s = sprintf('%06d', $findInvoice->id);?>
<body class="">
    <div class="main-content">
        <div class="mt-5">
            <link rel="stylesheet" media="screen" href="#">
            <div id="invoice_page" class="ml-2 mr-2">
            <div class="invoice-container border shadow m-auto">
                <div class="m-5">
                    <div class="mb-4 row ">
                        <div class="mb-2 col-8 col-md-8"></div>
                        <div class="mb-2 col-4 col-md-4">
                            <div class="mt-auto pt-1 d-flex flex-column">
                                <span> {{$firmData->firm_name}}</span>
                            </div>
                        </div>
                    </div>
                <div class="row ">
                    <div class="mb-2 col-8 col-md-8">
                        <div class="mt-auto pt-1 d-flex flex-column">
                            <span>{{$LeadDetails['first_name']}} {{$LeadDetails['middle_name']}} {{$LeadDetails['last_name']}}</span>
                            <span>{{$LeadDetails['email']}}</span>
                        </div>
                    </div>
                <div class="mb-2 col-4 col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Invoice Number</strong>
                        <span class="test-invoice-number">{{$s}}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Date</strong>
                        <span> {{date('M d, Y',strtotime($findInvoice->created_at))}}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Due</strong>
                        <span>{{($findInvoice->due_date) ? date('M j, Y',strtotime(convertUTCToUserTime($findInvoice->due_date.' 00:00:00', auth()->user()->user_timezone ?? 'UTC'))) : NULL}}</span>
                    </div>
                </div>
            </div>
            <?php
            $invoiceAmt=$findInvoice['total_amount'];
            $paidAmt=$findInvoice['amount_paid'];
            $dueAmt=$invoiceAmt-$paidAmt;
            if($dueAmt<=0){
                $dueAmt=0;
            }

            ?>
        <hr><h3 class="mb-3"><strong>Amount Due: <span class="test-amount-due">${{number_format($dueAmt,2)}}</span></strong></h3>
        <span><strong>Description:</strong></span></br>{!! nl2br($findInvoice->notes) !!}</div></div>
    <br>
    <br>    </div>
            </div>
        </div>
        <div class="mt-4 d-flex flex-row justify-content-center">
            <span class="font-weight-light powered-by">Powered by
                <img src="{{asset('assets/images/logo.png')}}" width="64" height="50">
            </span>
        </div>
    </div>
</body>
<style>
    .invoice-container {
        max-width: 800px;
    }
</style>

    <!-- jQuery -->
    <script src="{{asset('assets/js/common-bundle-script.js')}}"></script>
    <script src="{{asset('assets/js/script.js')}}"></script>

    <script src="{{asset('public/assets/js/plugins/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('public/assets/js/scripts/script.min.js')}}"></script>
    <script src="{{asset('public/assets/js/scripts/sidebar.large.script.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/toastr.min.js')}}"></script>
    <script src="{{asset('public/assets/js/jquery.validate.min.js')}}"></script>
</body>
</html>