<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <style>
        body {
            font-family: Nunito, sans-serif;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.25rem;
        }

        .card>hr {
            margin-right: 0;
            margin-left: 0;
        }

        .card>.list-group:first-child .list-group-item:first-child {
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }

        .card>.list-group:last-child .list-group-item:last-child {
            border-bottom-right-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }

        .card-body {
            flex: 1 1 auto;
            padding: 1.25rem;
        }

        .card-title {
            margin-bottom: 0.75rem;
        }

        .card-subtitle {
            margin-top: -0.375rem;
            margin-bottom: 0;
        }

        .card-text:last-child {
            margin-bottom: 0;
        }

        .card-link:hover {
            text-decoration: none;
        }

        .card-link+.card-link {
            margin-left: 1.25rem;
        }

        .card-header {
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-header:first-child {
            border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
        }

        .card-header+.list-group .list-group-item:first-child {
            border-top: 0;
        }

        .card-footer {
            padding: 0.75rem 1.25rem;
            background-color: rgba(0, 0, 0, 0.03);
            border-top: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-footer:last-child {
            border-radius: 0 0 calc(0.25rem - 1px) calc(0.25rem - 1px);
        }

        .card-header-tabs {
            margin-right: -0.625rem;
            margin-bottom: -0.75rem;
            margin-left: -0.625rem;
            border-bottom: 0;
        }

        .card-header-pills {
            margin-right: -0.625rem;
            margin-left: -0.625rem;
        }

        .card-img-overlay {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            padding: 1.25rem;
        }

        .card-img {
            width: 100%;
            border-radius: calc(0.25rem - 1px);
        }

        .card-img-top {
            width: 100%;
            border-top-left-radius: calc(0.25rem - 1px);
            border-top-right-radius: calc(0.25rem - 1px);
        }

        .card-img-bottom {
            width: 100%;
            border-bottom-right-radius: calc(0.25rem - 1px);
            border-bottom-left-radius: calc(0.25rem - 1px);
        }

        .card-deck {
            display: flex;
            flex-direction: column;
        }

        .card-deck .card {
            margin-bottom: 15px;
        }

        col-lg-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
        @media (min-width: 992px)
.col-lg-3 {
    flex: 0 0 25%;
    max-width: 25%;
}
@media (min-width: 768px)
.col-md-3 {
    flex: 0 0 25%;
    max-width: 25%;
}
        .w-25 {
            width: 25% !important;
        }

        .w-50 {
            width: 50% !important;
        }

        .w-75 {
            width: 75% !important;
        }

        .w-100 {
            width: 100% !important;
        }

        .w-auto {
            width: auto !important;
        }

        .h-25 {
            height: 25% !important;
        }

        .h-50 {
            height: 50% !important;
        }

        .h-75 {
            height: 75% !important;
        }

        .h-100 {
            height: 100% !important;
        }

        .h-auto {
            height: auto !important;
        }

        .mw-100 {
            max-width: 100% !important;
        }

        .mh-100 {
            max-height: 100% !important;
        }

        .m-0 {
            margin: 0 !important;
        }

        .mt-0,
        .my-0 {
            margin-top: 0 !important;
        }

        .mr-0,
        .mx-0 {
            margin-right: 0 !important;
        }

        .mb-0,
        .my-0 {
            margin-bottom: 0 !important;
        }

        .ml-0,
        .mx-0 {
            margin-left: 0 !important;
        }

        .m-1 {
            margin: 0.25rem !important;
        }

        .mt-1,
        .my-1 {
            margin-top: 0.25rem !important;
        }

        .mr-1,
        .mx-1 {
            margin-right: 0.25rem !important;
        }

        .mb-1,
        .my-1 {
            margin-bottom: 0.25rem !important;
        }

        .ml-1,
        .mx-1 {
            margin-left: 0.25rem !important;
        }

        .m-2 {
            margin: 0.5rem !important;
        }

        .mt-2,
        .my-2 {
            margin-top: 0.5rem !important;
        }

        .mr-2,
        .mx-2 {
            margin-right: 0.5rem !important;
        }

        .mb-2,
        .my-2 {
            margin-bottom: 0.5rem !important;
        }

        .ml-2,
        .mx-2 {
            margin-left: 0.5rem !important;
        }

        .m-3 {
            margin: 1rem !important;
        }

        .mt-3,
        .my-3 {
            margin-top: 1rem !important;
        }

        .mr-3,
        .mx-3 {
            margin-right: 1rem !important;
        }

        .mb-3,
        .my-3 {
            margin-bottom: 1rem !important;
        }

        .ml-3,
        .mx-3 {
            margin-left: 1rem !important;
        }

        .m-4 {
            margin: 1.5rem !important;
        }

        .mt-4,
        .my-4 {
            margin-top: 1.5rem !important;
        }

        .mr-4,
        .mx-4 {
            margin-right: 1.5rem !important;
        }

        .mb-4,
        .my-4 {
            margin-bottom: 1.5rem !important;
        }

        .ml-4,
        .mx-4 {
            margin-left: 1.5rem !important;
        }

        .m-5 {
            margin: 3rem !important;
        }

        .mt-5,
        .my-5 {
            margin-top: 3rem !important;
        }

        .mr-5,
        .mx-5 {
            margin-right: 3rem !important;
        }

        .mb-5,
        .my-5 {
            margin-bottom: 3rem !important;
        }

        .ml-5,
        .mx-5 {
            margin-left: 3rem !important;
        }

        .p-0 {
            padding: 0 !important;
        }

        .pt-0,
        .py-0 {
            padding-top: 0 !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pb-0,
        .py-0 {
            padding-bottom: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .p-1 {
            padding: 0.25rem !important;
        }

        .pt-1,
        .py-1 {
            padding-top: 0.25rem !important;
        }

        .pr-1,
        .px-1 {
            padding-right: 0.25rem !important;
        }

        .pb-1,
        .py-1 {
            padding-bottom: 0.25rem !important;
        }

        .pl-1,
        .px-1 {
            padding-left: 0.25rem !important;
        }

        .p-2 {
            padding: 0.5rem !important;
        }

        .pt-2,
        .py-2 {
            padding-top: 0.5rem !important;
        }

        .pr-2,
        .px-2 {
            padding-right: 0.5rem !important;
        }

        .pb-2,
        .py-2 {
            padding-bottom: 0.5rem !important;
        }

        .pl-2,
        .px-2 {
            padding-left: 0.5rem !important;
        }

        .p-3 {
            padding: 1rem !important;
        }

        .pt-3,
        .py-3 {
            padding-top: 1rem !important;
        }

        .pr-3,
        .px-3 {
            padding-right: 1rem !important;
        }

        .pb-3,
        .py-3 {
            padding-bottom: 1rem !important;
        }

        .pl-3,
        .px-3 {
            padding-left: 1rem !important;
        }

        .p-4 {
            padding: 1.5rem !important;
        }

        .pt-4,
        .py-4 {
            padding-top: 1.5rem !important;
        }

        .pr-4,
        .px-4 {
            padding-right: 1.5rem !important;
        }

        .pb-4,
        .py-4 {
            padding-bottom: 1.5rem !important;
        }

        .pl-4,
        .px-4 {
            padding-left: 1.5rem !important;
        }

        .p-5 {
            padding: 3rem !important;
        }

        .pt-5,
        .py-5 {
            padding-top: 3rem !important;
        }

        .pr-5,
        .px-5 {
            padding-right: 3rem !important;
        }

        .pb-5,
        .py-5 {
            padding-bottom: 3rem !important;
        }

        .pl-5,
        .px-5 {
            padding-left: 3rem !important;
        }

        .m-auto {
            margin: auto !important;
        }

        .mt-auto,
        .my-auto {
            margin-top: auto !important;
        }

        .mr-auto,
        .mx-auto {
            margin-right: auto !important;
        }

        .mb-auto,
        .my-auto {
            margin-bottom: auto !important;
        }

        .ml-auto,
        .mx-auto {
            margin-left: auto !important;
        }
    </style>
</head>

<body style="padding:25px;">
    <h2>Leads</h2>
    <br>
    <table width="100%">
        <tr>
            <td style="width:25%;">
                <label for="picker1">Select a Lead</label><br>
                <select id="ld" name="ld" class="form-control custom-select col dropdown_list">
                    <option value="">Select...</option>
                    <?php 
                        foreach($allLeadsDropdown as $kcs=>$vcs){?>
                    <option <?php if($ld==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">
                        {{$vcs->first_name}} {{$vcs->last_name}}</option>
                    <?php } ?>

                </select>
            </td>
            <td style="width:25%;">
                <label for="picker1">Practice Area</label><br>
                <select id="pa" name="pa" class="form-control custom-select col dropdown_list">
                    <option value="">Select...</option>
                    <?php 
                        foreach($allPracticeAreaDropdown as $kcs=>$vcs){?>
                    <option <?php if($pa==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}
                    </option>
                    <?php } ?>

                </select>
            </td>


            <td style=" width:20%;">
                <label for="picker1">Office Location</label><br>
                <select id="ol" name="ol" class="form-control custom-select col dropdown_list">
                    <option value="">Select...</option>
                    <option value="1" <?php if($ol=="1"){ echo "selected=selected"; }?>>Primary</option>
                </select>
            </td>
            <td style="width:30%;">
                <label for="picker1">Show Leads Assigned To
                </label><br>
                <select id="at" name="at" >
                    <option value="">Select...</option>
                    <option <?php if($at=="all"){ echo "selected=selected"; }?> value="all">All</option>
                    <option <?php if($at=="unassigned"){ echo "selected=selected"; }?> value="unassigned">Unassigned
                    </option>
                    <option <?php if($at=="me"){ echo "selected=selected"; }?> value="me">Me</option>
                </select>
            </td>
        </tr>
    </table>
    <br>

    <?php foreach($LeadStatus as $k=>$v){?>
    <h5 class="font-weight-bold text-truncate" title="{{$v->title}}">{{$v->title}}</h5>
    <hr>
    <div class="opportunity-status-column-subheader mb-3 font-weight-bold row ">
        <table width="100%">
            <tr>
                <td style="float: left;" class="total-leads col-8">
                    <div><span>{{$extraInfo[$v->id]['totalLeads']}} Leads</span></div>
                </td>
                <td style="float: right;" class="justify-content-end col-4">
                    <div>
                        <span class="total-value column-header">${{number_format($extraInfo[$v->id]['sum'],2)}}</span>
                    </div>
                </td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td class="row ">
                    <?php foreach($allLEadByGroup[$v->id] as $kk=>$vv){?>
                    <div class="col-md-3 col-lg-3 m-2" id="{{$vv->user_id}}">
                        <div class="card">
                            <div class="card-header">
                                <table width="100%">
                                    <tr>
                                        <td class="col-8 float-left">
                                            <div><span>{{$vv->first_name}} {{$vv->last_name}}</span></div>
                                        </td>
                                        <td class="col-4 float-right">
                                            <div>
                                                ${{number_format($vv->potential_case_value,2)}}
                                            </div>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                            <div class="card-body">
                                <div class="pb-1">
                                    <div class="opportunity-status-timestamp align-items-center text-muted">
                                        <span class="text-nowrap">{{$v->title}} as of
                                            {{date('m/d/Y',strtotime($vv->date_added))}}
                                        </span>
                                    </div>
                                </div>
                                <?php 
                                    if($vv->email!=""){?>
                                <div class="align-items-center pb-1">
                                    <a class="mr-1" href="mailto:{{$vv->email}}">{{$vv->email}}</a>
                                    <i aria-hidden="true" class="fa fa-envelope icon-envelope icon"
                                        style="opacity: 0.6; width: 20px;"></i>
                                </div>
                                <?php } ?>
                                <?php 
                                    if($vv->mobile_number!=""){?>
                                <div class="align-items-center pb-1">
                                    <span class="mr-1">{{$vv->mobile_number}}</span>
                                    <i aria-hidden="true" class="fa fa-phone icon-phone icon"
                                        style="opacity: 0.6; width: 20px;"></i>
                                </div>
                                <?php } ?>
                                <?php 
                                    if($vv->notes!=""){?>
                                <div id="collapseExampleArea{{$vv->id}}" class="collapse border-left">
                                    <p>{{substr($vv->notes,0,200)}}</p>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
    <?php } ?>

</body>

</html>