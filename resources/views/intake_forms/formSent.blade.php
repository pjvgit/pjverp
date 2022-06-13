<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{$firmData->firm_name ?? ''}} - {{$intakeForm['form_name'] ?? ''}} - {{config('app.name')}}
    </title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{asset('assets/styles/css/preview/bootstrap-style-preview.css')}}">
    <link rel="stylesheet" media="screen" href="{{asset('assets/styles/css/preview/spacing.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/fontawesome-free-5.10.1-web/css/all.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
    <style>
        .logoView {
            width: 50px;
        }
        .error{
            color: red;
        }

    </style>
    <script>
        var baseUrl = '<?php echo URL('/'); ?>';
    </script>
</head>
<?php   
$CommonController= new App\Http\Controllers\CommonController();
if(!empty($alreadyFilldedData['form_value'])){
$filledData=json_decode($alreadyFilldedData['form_value']); 
}
?>
<body class="">
    <?php 
    if(empty($intakeForm)){
        ?>
         <div class="main-content">
            <div id="form-preview-root" class="form-container">
                <div id="submission-success-page" class="form p-4 p-md-5 mx-auto shadow">
                    <h3 class="heading mb-3 test-firm-name">This form is unavailable.</h3>
                    <p class="test-success-message">This form is not available at this time. Please contact your law firm for a new form.</p>
                </div>
            </div>
                <div class="mycase-watermark text-muted text-center">
                    <span class="mr-1">Powered by</span>
                    <img class="logoView" src="{{asset('assets/images/logo.png')}}">
                </div>
         </div>
        <?php
    }else if(@$caseIntakeForm['status']=="2"){
        ?>
         <div class="main-content">
            <div id="form-preview-root" class="form-container">
                <div id="submission-success-page" class="form p-4 p-md-5 mx-auto shadow">
                    <h3 class="heading mb-3 test-firm-name">{{$firmData->firm_name}}</h3>
                    <h4><strong>Thank you.</strong></h4>
                    <p class="test-success-message">Your form has been submitted to {{$firmData->firm_name}}.</p>
                </div>
            </div>
                <div class="mycase-watermark text-muted text-center">
                    <span class="mr-1">Powered by</span>
                    <img class="logoView" src="{{asset('assets/images/logo.png')}}">
                </div>
         </div>
        <?php
    }else{
        ?>
         <form class="collectDataForm" id="collectDataForm" name="collectDataForm" method="POST">
            @csrf
            <input type="hidden" value="{{$formId}}" name="case_intake_form_token">
            <div class="main-content">
                <div id="form-preview-root" class="form-container">
                    <div class="form p-4 p-md-5 mx-auto shadow" data-testid="form-container"
                        style="background-color: {{$intakeForm['background_color']}}'; color: rgb(0, 0, 0); font-family: {{$intakeForm['form_font']}}">
                        <input id="form_id" name="form_id" class="form-control" type="hidden" value="{{$intakeForm['id']}}">
                        <input id="case_intake_form_id" name="case_intake_form_id" class="form-control" type="hidden" value="{{$caseIntakeForm['id']}}">                        
                        <div id="form-info">
                            <h3 class="heading mb-3 test-firm-name">{{$firmData->firm_name}}</h3>
                            <h4 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm['form_name']}}</h4>
                        </div>
                        <hr class="mt-4">
                        <div id="form-body">
                            <?php 
                            foreach($intakeFormFields as $k=>$v){
                                if($v->form_field=="name"){?>
                            <div class="form-fields">
                                <div id="field-row-name" class="form-field-container mb-3 name-test">
                                    <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Name'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?> </label>
                                    <div class="">
                                        <div class="">
                                            <div class="row no-gutters">
                                                <div class="col-5 pr-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="first_name" name="first_name" class="form-control " <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                    type="text" placeholder="First Name" data-testid="first_name" value="{{($filledData->first_name)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="middle_initial" name="middle_name" class="form-control " type="text" placeholder="Middle Name" data-testid="middle_name" value="{{($filledData->middle_name)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-5 pl-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group"><input id="last_name" name="last_name"
                                                                    class="form-control " type="text"  <?php if($v->is_required=="yes"){ echo "required";} ?> placeholder="Last Name"
                                                                    data-testid="last_name" value="{{($filledData->last_name)??''}}"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_field=="email"){?>
                            <div class="form-fields">
                                <div id="field-row-name" class="form-field-container mb-3 name-test">
                                    <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Email'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="">
                                            <div class="row no-gutters">
                                                <div class="col-12 pr-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="first_name" name="email"  <?php if($v->is_required=="yes"){ echo "required";} ?> class="form-control "
                                                                    type="email" placeholder="" data-testid="first_name"
                                                                    value="{{($filledData->email)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_field=="home_phone"){?>
                            <div class="form-fields">
                                <div id="field-row-name" class="form-field-container mb-3 name-test">
                                    <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Home phone'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="">
                                            <div class="row no-gutters">
                                                <div class="col-12 pr-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="home_phone" name="home_phone" class="form-control "
                                                                    type="text" placeholder="" <?php if($v->is_required=="yes"){ echo "required";} ?>  data-testid="home_phone"
                                                                    value="{{($filledData->home_phone)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="cell_phone"){?>
                            <div class="form-fields">
                                <div id="field-row-name" class="form-field-container mb-3 name-test">
                                    <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Cell phone'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="">
                                            <div class="row no-gutters">
                                                <div class="col-12 pr-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="cell_phone" <?php if($v->is_required=="yes"){ echo "required";} ?>  name="cell_phone" class="form-control "
                                                                    type="text" placeholder="" data-testid="cell_phone"
                                                                    value="{{($filledData->cell_phone)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_field=="work_phone"){?>
                            <div class="form-fields">
                                <div id="field-row-name" class="form-field-container mb-3 name-test">
                                    <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Work phone'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="">
                                            <div class="row no-gutters">
                                                <div class="col-12 pr-2">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group">
                                                                <input id="first_name"  <?php if($v->is_required=="yes"){ echo "required";} ?> name="work_phone" class="form-control "
                                                                    type="text" placeholder="" data-testid="first_name"
                                                                    value="{{($filledData->work_phone)??''}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_field=="address"){
                                    ?>
                            <div id="field-row-address" class="form-field-container mb-3 address-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Address'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="" id="address">
                                        <div class="row form-group"><label for="address_address1"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">Address</label>
                                            <div class="col-12"><input id="address_address1"  <?php if($v->is_required=="yes"){ echo "required";} ?> name="address1"
                                                    placeholder="Address" type="text" class="form-control" value="{{($filledData->address1)??''}}"></div>
                                        </div>
                                        <div class="row form-group"><label for="address_address2"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">Address 2</label>
                                            <div class="col-12"><input id="address_address2" name="address2"
                                                    placeholder="Address 2" type="text" class="form-control" value="{{($filledData->address2)??''}}"></div>
                                        </div>
                                        <div class="no-gutters row ">
                                            <div class="pr-sm-3 col-12 col-sm-6">
                                                <div class="row form-group"><label for="address_city"
                                                        class="sr-only sr-only-focusable col-sm-12 col-form-label">City</label>
                                                    <div class="col-12"><input id="address_city" name="city" placeholder="City"
                                                            type="text" class="form-control" value="{{($filledData->city)??''}}"></div>
                                                </div>
                                            </div>
                                            <div class="pr-3 col-4 col-sm-3 col-md-2">
                                                <div class="row form-group"><label for="address_state"
                                                        class="sr-only sr-only-focusable col-sm-12 col-form-label">State</label>
                                                    <div class="col-12"><input id="address_postal" name="state"
                                                            placeholder="State" type="text" class="form-control" value="{{($filledData->state)??''}}"></div>
                                                </div>
                                            </div>
                                            <div class="col-8 col-sm-3 col-md-4">
                                                <div class="row form-group"><label for="address_postal"
                                                        class="sr-only sr-only-focusable col-sm-12 col-form-label">Zip</label>
                                                    <div class="col-12"><input id="address_postal" name="postal"
                                                            placeholder="Zip" type="text" class="form-control" value="{{($filledData->postal)??''}}"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-0 row form-group"><label for="address_countryCode"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">Country</label>
                                            <div class="col-12">
                                                <select class="form-control country" id="country" name="country"
                                                    data-placeholder="Select Country" style="width: 100%;">
                                                    <option value="">Select Country</option>
                                                    <?php foreach($country as $key=>$val){?>
                                                    <option value="{{$val->id}}" {{ (isset($filledData) && isset($filledData->country) && $filledData->country == $val->id) ? 'selected' : '' }}> {{$val->name}}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_field=="birthday"){
                                ?>
                            <div id="field-row-birthday" class="form-field-container mb-3 birthday-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Birthday'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="p-0 ">
                                        <div class="">
                                            <div>
                                                <div class="dropdown">
                                                    <div disabled="" aria-haspopup="true" class="" aria-expanded="false">
                                                        <div class="test-form-date-field input-group">
                                                            <input id="birthday" autocomplete="off"  name="birthday" <?php if($v->is_required=="yes"){ echo "required";} ?> class="birthday form-control" type="text" value="{{($filledData->birthday)??''}}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="driver_license"){
                                ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Driver license'}} <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-6"><input id="driver license_number" <?php if($v->is_required=="yes"){ echo "required";} ?> name="driver_license_number"
                                                placeholder="Driver License" autocomplete="off" type="text"
                                                class="form-control form-control" value="{{($filledData->driver_license_number)??''}}"></div>
                                        <div class="p-0 col-2">
                                            <input id="driver license_number" <?php if($v->is_required=="yes"){ echo "required";} ?> name="driver_license_state"
                                                placeholder="State" autocomplete="off" type="text"
                                                class="form-control form-control" value="{{($filledData->driver_license_state)??''}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="long_text"){
                                ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Long Text'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <textarea id="driver license_number"
                                                <?php if($v->is_required=="yes"){ echo "required";} ?> name="long_text"
                                                placeholder="" autocomplete="off" class="form-control form-control"
                                                value="{{($filledData->long_text)??''}}">{{($filledData->long_text)??''}}</textarea></div>
        
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="short_text"){
                                    ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Short Text'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <input type="text" id="driver license_number"
                                                <?php if($v->is_required=="yes"){ echo "required";} ?> name="sort_text"
                                                placeholder="" autocomplete="off" class="form-control form-control"
                                                value="{{($filledData->sort_text)??''}}" />
                                        </div>        
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="multiple_choice"){
                                    ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test">
                                <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Multiple Choice'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <select name="multiple_choice" class="form-control form-control"
                                                <?php if($v->is_required=="yes"){ echo "required";} ?>>
                                                <?php  $options=json_decode($v->extra_value);
                                                    foreach($options as $kkey=>$kVal){ ?>
                                                <option <?php if(isset($filledData->multiple_choice) && $filledData->multiple_choice==$kVal) { echo "selected=selected"; } ?> value="{{$kVal}}">{{$kVal}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="checkboxes"){
                                    ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test">
                                <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Checkboxes'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <?php  $options=json_decode($v->extra_value);
                                                    foreach($options as $kkey=>$kVal){ ?>
                                            <label class="form-check-label">
                                                <input type="checkbox" <?php if($v->is_required=="yes"){ echo "required";} ?> name="checkboxes[]" <?php if(isset($filledData->checkboxes) && in_array($kVal,$filledData->checkboxes)) { echo "checked=checked"; } ?> class="pick-option ml-2" value="{{$kVal}}">
                                                <span>{{$kVal}}</span>
                                            </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="yesno"){
                                        ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test">
                                <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Yes/No'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <label class="form-check-label">
                                                <input type="radio" <?php if($v->is_required=="yes"){ echo "required";} ?> <?php if( isset($filledData->yesno) && $filledData->yesno=="yes") { echo "checked=checked"; }?> name="yesno" class="pick-option ml-2" value="yes">
                                                <span>Yes</span>
                                            </label>
                                            <label class="form-check-label">
                                                <input type="radio" <?php if($v->is_required=="yes"){ echo "required";} ?> name="yesno" <?php if(isset($filledData->yesno) && $filledData->yesno=="no") { echo "checked=checked"; } ?>  class="pick-option ml-2" value="no">
                                                <span>No</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="number"){
                                    ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Number'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <input id="driver license_number" name="number" placeholder="Number" <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                autocomplete="off" type="number" class="form-control form-control" value="{{($filledData->number)??''}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="currency"){
                                    ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Currency'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <input id="driver license_number" name="currency" placeholder="Currency" <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                autocomplete="off" type="number" min="0" step="0.01" class="form-control form-control" value="{{($filledData->currency)??''}}"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($v->form_field=="date"){
                                        ?>
                            <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                    class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Date'}}
                                    <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                <div class="">
                                    <div class="row ">
                                        <div class="pr-2 col-12">
                                            <div class="test-form-date-field input-group">
                                                <input id="datepicker" name="date" placeholder="Date" autocomplete="off" type="text" <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                    class="form-control form-control datepicker" value="{{($filledData->date)??''}}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }else if($v->form_category==NULL){?>
                                <div class="test-header"><hr class="mt-4"><h3>{{$v->header_text}}</h3></div>
                            <?php } ?>
                            <?php } ?>
                            <span class="showError"></span>
                            <div id="form-footer" class="border-top pt-3 mt-3 d-print-none">
                                <button id="submit-button" name="saveform" value="saveform" class="submit btn btn-cta-primary "
                                    style="background-color:#{{$intakeForm['button_color']}}; color:#{{$intakeForm['button_font_color']}}; font-family: {{$intakeForm['button_font']}}; border-color:#{{$intakeForm['button_color']}};">
                                    Submit Form
                                </button>
                                <button id="submit-button" name="saveprogress" value="saveprogress" class="submit ml-2 btn btn-secondary " 
                                    style="background-color:#{{$intakeForm['button_color']}}; color:#{{$intakeForm['button_font_color']}}; font-family: {{$intakeForm['button_font']}}; border-color:#{{$intakeForm['button_color']}};">
                                    Save Progress
                                </button>
                                <div class="py-3 py-md-0 d-md-inline-block ml-md-3">
                                    <?php 
                                        if(isset($alreadyFilldedData['updated_at'])){
                                            $tz=(isset(Auth::User()->user_timezone)) ? Auth::User()->user_timezone : "UTC";
                                            $currentConvertedDate= $CommonController->convertUTCToUserTime($alreadyFilldedData['updated_at'],$tz);
                                        ?>
                                        <span id="last-saved-timestamp" class="text-muted">Last saved {{date('M d,Y h:i a',strtotime($currentConvertedDate))}}</span>
                                        <?php } ?>    
                                </div>
                                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" ></div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="mycase-watermark text-muted text-center">
                    <span class="mr-1">Powered by</span>
                    <img class="logoView" src="{{asset('assets/images/logo.png')}}">
                </div>
            </div>
            <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden">
        </form>
        <?php
    }?>
   
</body>
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {        
        $('.birthday').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            endDate: '+0d',
            'todayHighlight': true
        });
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        
        $(document).on("click", ":submit", function(e){
            $("#current_submit").val($(this).val());
        });
        $('#collectDataForm').submit(function (e) {
           $("#last-saved-timestamp").text("Processing...");
           $(".submit").attr("disabled", true);
           $(".innerLoader").css('display', 'block');
           e.preventDefault();

           if (!$('#collectDataForm').valid()) {
               $(".innerLoader").css('display', 'none');
               $('.submit').removeAttr("disabled");
               return false;
           }
           var dataString = '';
           dataString = $("#collectDataForm").serialize();
           $.ajax({
               type: "POST",
               url: baseUrl + "/leads/collectFormData", // json datasource
               data: dataString,
               headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               beforeSend: function (xhr, settings) {
                   settings.data += '&save=yes';
               },
               success: function (res) {
                   $(".innerLoader").css('display', 'block');
                   if (res.errors != '') {
                       $(".innerLoader").css('display', 'none');
                       $('.submit').removeAttr("disabled");
                       $('.showError').html('');
                       var errotHtml =
                           '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                       $.each(res.errors, function (key, value) {
                           errotHtml += '<li>' + value + '</li>';
                       });
                       errotHtml += '</ul></div>';
                       $('.showError').append(errotHtml);
                       $('.showError').show();
                       return false;
                   } else {
                        window.location.reload();
                   }
               },
               error: function (jqXHR, exception) {
                   $(".innerLoader").css('display', 'none');
                   $('.showError').html('');
                   var errotHtml =
                       '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                   $('.showError').append(errotHtml);
                   $('.showError').show();
                   $(".innerLoader").css('display', 'none');
                    $('.submit').removeAttr("disabled");
                    $("#last-saved-timestamp").text('');
               },
           });
       });
    });
    jQuery(function($) {
        var requiredCheckboxes = $(':checkbox[required]');
        requiredCheckboxes.on('change', function(e) {
            var checkboxGroup = requiredCheckboxes.filter('[name="' + $(this).attr('name') + '"]');
            var isChecked = checkboxGroup.is(':checked');
            checkboxGroup.prop('required', !isChecked);
        });
        requiredCheckboxes.trigger('change');
    });

</script>

</html>
