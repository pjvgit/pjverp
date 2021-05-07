<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <title> {{config('app.name')}} - Simplify Your Law Practice | Cloud Based Practice Management</title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/preview/bootstrap-style-preview.css')}}">
    <link rel="stylesheet" media="screen" href="{{asset('public/assets/styles/css/preview/spacing.css')}}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{asset('public/assets/js/jquery-ui.js')}}"></script>
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/jquery-ui.css')}}">
</head>
<body class="">
    <div class="main-content">
        <div class="mt-5">
            <link rel="stylesheet" media="screen" href="#">
            <div id="invoice_page" class="ml-2 mr-2">
                <div class="invoice-container border shadow m-auto">
                    <div class="m-5">
                        <div class="mb-4 row ">
                            <div class="mb-2 col-md-8"></div>
                            <div class="mb-2 col-md-4">
                                <div class="mt-auto pt-1 d-flex flex-column"><span>{{$firmData->firm_name}}</span></div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="mb-2 col-md-8">
                                <div class="mt-auto pt-1 d-flex flex-column"><span>{{($userData['first_name'])??''}}
                                    {{($userData['middle_name'])??''}} {{($userData['last_name'])??''}}</span>
                                    <span>{{($userData['street'])??''}}</span>
                                    <span>{{($userData['address2'])??''}}</span>
                                    <span>{{($userData['city'])??''}} {{($userData['state'])??''}} {{($userData['postal_code'])??''}}</span>
                                    <span>{{($userData['countryname'])??''}}</span>
                                    <span>{{($userData['email'])??''}}</span></div>
                            </div>
                            <div class="mb-2 col-md-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Invoice Number</strong>
                                    <span>{{($PotentialCaseInvoice->invoice_number)??''}}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Date</strong>
                                    <span>{{date('M j, Y',strtotime($PotentialCaseInvoice->invoice_date))}}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Due</strong>
                                    <span>{{date('M j, Y',strtotime($PotentialCaseInvoice->due_date))}}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h3 class="mb-3"><strong>Amount Due: <span class="test-amount-due">${{ number_format($PotentialCaseInvoice->invoice_amount,2)}}</span></strong></h3>
                        <span><strong>Description:</strong></span>
                        <div>
                            <p>{{($PotentialCaseInvoice->description)??''}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex flex-row justify-content-center">
            <span class="font-weight-light powered-by">Powered by
                <img src="{{BASE_URL}}assets/images/logo.png" width="64" height="50">
            </span>
        </div>
    </div>
</body>
<style>
    .invoice-container {
        max-width: 800px;
    }
</style>
</html>
