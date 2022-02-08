<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{$firmData->firm_name}} - {{$intakeForm['form_name']}} - {{config('app.name')}}
    </title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{asset('assets/styles/css/preview/bootstrap-style-preview.css')}}">
    <link rel="stylesheet" media="screen" href="{{asset('assets/styles/css/preview/spacing.css')}}">
 
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
    {{-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> --}}

    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
    async defer>
    </script>
    <style>
        .logoView {
            width: 50px;
        }
        .error {
            color: red;
        }
    </style>
    <script>
    var baseUrl = '<?php echo URL('/');?>';
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
    if($caseIntakeForm['status']=="2"){
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
            <img class="logoView" src="{{BASE_URL}}assets/images/logo.png">
        </div>
    </div>
    <?php
    }else{
        ?>


    <form class="collectContactUsDataForm" id="collectContactUsDataForm" name="collectContactUsDataForm" method="POST">
       <input type="hidden" name="form_unique_id" value="{{$intakeForm['form_unique_id']}}">
        @csrf
        <div class="main-content">
            <div id="form-preview-root" class="form-container">
                <div class="form p-4 p-md-5 mx-auto shadow" data-testid="form-container"
                    style="background-color: {{$intakeForm['background_color']}}'; color: rgb(0, 0, 0); font-family: {{$intakeForm['form_font']}}">
                    <input id="form_id" name="form_id" class="form-control" type="hidden" value="{{$intakeForm['id']}}">
                    <div id="form-info">
                        <h3 class="heading mb-3 test-firm-name">{{$firmData['firm_name']}}</h3>
                        <h4 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm['form_name']}}</h4>
                        <p class=" heading mb-3 test-form-name">{{$intakeForm['form_introduction']}}</p>
                    </div>
                    <hr class="mt-4">
                    <div id="form-body">
                       
                        <?php 
                            foreach($intakeFormFields as $k=>$v){
                                if($v->form_field=="name"){?>
                                <div class="form-fields">
                                    <div id="field-row-name" class="form-field-container mb-3 name-test">
                                        <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Name'}}
                                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?> </label>
                                        <div class="">
                                            <div class="">
                                                <div class="row no-gutters">
                                                    <div class="col-5 pr-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="first_name" name="first_name"
                                                                        class="form-control "
                                                                        required
                                                                        type="text" placeholder="First Name"
                                                                        data-testid="first_name">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="fnameError"></div>
                                                    </div>
                                                   
                                                    <div class="col-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="middle_initial" name="middle_name"
                                                                        class="form-control " type="text" 
                                                                        placeholder="Middle Name" data-testid="middle_name">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-5 pl-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group"><input id="last_name" name="last_name"
                                                                        class="form-control " type="text"
                                                                        required
                                                                        placeholder="Last Name" data-testid="last_name"></div>
                                                            </div>
                                                        </div>
                                                        <div id="lnameError"></div>
                                                    </div>
                                                </div>
                                               
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <?php }else if($v->form_field=="email"){?>
                                <div class="form-fields">
                                    <div id="field-row-name" class="form-field-container mb-3 name-test">
                                        <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Email'}}
                                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                        <div class="">
                                            <div class="">
                                                <div class="row no-gutters">
                                                    <div class="col-12 pr-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="email" name="email"
                                                                        <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                        class="form-control " type="email" placeholder=""
                                                                        data-testid="first_name">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="emailError"></div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php }else if($v->form_field=="home_phone"){?>
                                <div class="form-fields">
                                    <div id="field-row-name" class="form-field-container mb-3 name-test">
                                        <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Home phone'}}
                                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                        <div class="">
                                            <div class="">
                                                <div class="row no-gutters">
                                                    <div class="col-12 pr-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="home_phone" name="home_phone"
                                                                        class="form-control " type="text" placeholder=""
                                                                        <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                        data-testid="home_phone">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="homeError"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else if($v->form_field=="cell_phone"){?>
                                <div class="form-fields">
                                    <div id="field-row-name" class="form-field-container mb-3 name-test">
                                        <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Cell phone'}}
                                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                        <div class="">
                                            <div class="">
                                                <div class="row no-gutters">
                                                    <div class="col-12 pr-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="cell_phone"
                                                                        <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                        name="cell_phone" class="form-control " type="text"
                                                                        placeholder="" data-testid="cell_phone">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="cellError"></div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php }else if($v->form_field=="work_phone"){?>
                                <div class="form-fields">
                                    <div id="field-row-name" class="form-field-container mb-3 name-test">
                                        <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Work phone'}}
                                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                        <div class="">
                                            <div class="">
                                                <div class="row no-gutters">
                                                    <div class="col-12 pr-2">
                                                        <div>
                                                            <div class="">
                                                                <div class="input-group">
                                                                    <input id="first_name"
                                                                        <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                        name="work_phone" class="form-control " type="text"
                                                                        placeholder="" data-testid="first_name">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="workError"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php }else if($v->form_field=="address"){
                                            ?>
                                <div id="field-row-address" class="form-field-container mb-3 address-test"><label
                                        class="field-label font-weight-bold">Address
                                        <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="" id="address">
                                            <div class="row form-group"><label for="address_address1"
                                                    class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'Address'}}</label>
                                                <div class="col-12"><input id="address_address1"
                                                        <?php if($v->is_required=="yes"){ echo "required";} ?> name="address1"
                                                        placeholder="Address" type="text" class="form-control"></div>
                                            </div>
                                            <div class="row form-group"><label for="address_address2"
                                                    class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'Address 2'}}</label>
                                                <div class="col-12"><input id="address_address2" name="address2"
                                                        placeholder="Address 2" type="text" class="form-control"></div>
                                            </div>
                                            <div class="no-gutters row ">
                                                <div class="pr-sm-3 col-12 col-sm-6">
                                                    <div class="row form-group"><label for="address_city"
                                                            class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'City'}}</label>
                                                        <div class="col-12"><input id="address_city" name="city"
                                                                placeholder="City" type="text" class="form-control"></div>
                                                    </div>
                                                </div>
                                                <div class="pr-3 col-4 col-sm-3 col-md-2">
                                                    <div class="row form-group"><label for="address_state"
                                                            class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'State'}}</label>
                                                        <div class="col-12"><input id="address_postal" name="state"
                                                                placeholder="State" type="text" class="form-control"></div>
                                                    </div>
                                                </div>
                                                <div class="col-8 col-sm-3 col-md-4">
                                                    <div class="row form-group"><label for="address_postal"
                                                            class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'Zip'}}</label>
                                                        <div class="col-12"><input id="address_postal" name="postal"
                                                                placeholder="Zip" type="text" class="form-control"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-0 row form-group"><label for="address_countryCode"
                                                    class="sr-only sr-only-focusable col-sm-12 col-form-label">{{($v->client_friendly_lable)??'Country'}}</label>
                                                <div class="col-12">
                                                    <select class="form-control country" id="country" name="country"
                                                        data-placeholder="Select Country" style="width: 100%;">
                                                        <option value="">Select Country</option>
                                                        <?php foreach($country as $key=>$val){?>
                                                        <option value="{{$val->id}}"> {{$val->name}}</option>
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
                                        class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Birthday'}} 
                                        <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="p-0 ">
                                            <div class="">
                                                <div>
                                                    <div class="dropdown">
                                                        <div disabled="" aria-haspopup="true" class="" aria-expanded="false">
                                                            <div class="test-form-date-field input-group">
                                                                <input id="birthday" name="birthday"
                                                                    <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                                    class="form-control" type="text">
                                                            </div>
                                                        </div>
                                                        <div id="birthdayError"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else if($v->form_field=="driver_license"){
                                        ?>
                                <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                                        class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Driver license'}}
                                        <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                                    <div class="">
                                        <div class="row ">
                                            <div class="pr-2 col-6"><input id="driver license_number"
                                                    <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                    name="driver_license_number" placeholder="Driver License" autocomplete="off"
                                                    type="text" class="form-control form-control"></div>
                                            <div class="p-0 col-2">
                                                <input id="driver license_number"
                                                    <?php if($v->is_required=="yes"){ echo "required";} ?>
                                                    name="driver_license_state" placeholder="State" autocomplete="off"
                                                    type="text" class="form-control form-control">
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
                                                <textarea id="long_text"
                                                    <?php if($v->is_required=="yes"){ echo "required";} ?> name="long_text"
                                                    placeholder="" autocomplete="off" class="form-control form-control"
                                                    value="{{($filledData->client_friendly_lable)??''}}"></textarea></div>
            
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
                                                <textarea id="short_text"
                                                    <?php if($v->is_required=="yes"){ echo "required";} ?> name="sort_text"
                                                    placeholder="" autocomplete="off" class="form-control form-control"
                                                    value="{{($filledData->client_friendly_lable)??''}}"></textarea></div>
            
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
                                                <select id="multiple_choice" name="multiple_choice" class="form-control form-control"
                                                    <?php if($v->is_required=="yes"){ echo "required";} ?>>
                                                    <?php  $options=json_decode($v->extra_value);
                                                        foreach($options as $kkey=>$kVal){ ?>
                                                    <option value="{{$kVal}}">{{$kVal}}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="multipleError"></div>
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
                                                    <input id="checkbox" type="checkbox" name="checkboxes[]"  <?php if($v->is_required=="yes"){ echo "required";} ?>  class="pick-option ml-2" value="{{$kVal}}">
                                                    <span>{{$kVal}}</span>
                                                </label>
                                                <?php } ?>
                                                <div id="checkboxError"></div>
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
                                                    <input id="yesno" type="radio" name="yesno"  <?php if($v->is_required=="yes"){ echo "required";} ?>  class="pick-option ml-2" value="yes">
                                                    <span>Yes</span>
                                                </label>
                                                <label class="form-check-label">
                                                    <input id="yesno" type="radio" name="yesno"   <?php if($v->is_required=="yes"){ echo "required";} ?> class="pick-option ml-2" value="No">
                                                    <span>No</span>
                                                </label>
                                            </div>
                                            
                                        </div>
                                        <div id="yesnoError"></div>

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
                                                <input id="number"  <?php if($v->is_required=="yes"){ echo "required";} ?> name="number" placeholder="Number"
                                                    autocomplete="off" type="number" class="form-control form-control" value="">
                                            </div>
                                        </div>
                                        <div id="numberError"></div>

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
                                                <input id="driver license_number"  <?php if($v->is_required=="yes"){ echo "required";} ?> name="currency" placeholder="Currency"
                                                    autocomplete="off" type="number" min="0" class="form-control form-control" value=""></div>
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
                                                <input id="" name="date"  <?php if($v->is_required=="yes"){ echo "required";} ?> placeholder="Date" autocomplete="off" type="text"
                                                    class="dp form-control form-control" value=""></div>
                                        </div>
                                    </div>
                                </div>
                                <?php }else if($v->form_category==NULL){?>
                                <div class="test-header">
                                    <hr class="mt-4">
                                    <h3>{{$v->header_text}}</h3>
                                </div>
                        <?php } ?>
                      
                        <?php } ?>
                        
                        <span class="showError"></span>
                        @if ($message = session('form_success'))
                            <div class="alert alert-success" role="alert">
                                {{$message}}
                            </div>
                            {{session(['form_success' => ''])}}
                        @endif
                        <div id="form-footer" class="border-top pt-3 mt-3 d-print-none">
                            <div class="form-fields">
                                <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test">
                                    <div class="">
                                        <div class="row ">
                                            <div class="pr-2 col-12">
                                                <div id="g-recaptcha"></div>
                                                    {{-- <div class="g-recaptcha" data-sitekey="6LfC0JQaAAAAAGfDzY23bY9WHG1pKx43B5ZJraMX"></div> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>  <button type="submit" id="submit"  name="saveform" value="saveform"
                                class="submit btn btn-cta-primary submit"
                                style="background-color:#{{$intakeForm['button_color']}}; color:#{{$intakeForm['button_font_color']}}; font-family: {{$intakeForm['button_font']}}; border-color:#{{$intakeForm['button_color']}};">Submit
                                Form
                            </button>
                            <span id="last-saved-timestamp"></span>
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mycase-watermark text-muted text-center">
                <span class="mr-1">Powered by</span>
                <img class="logoView" src="{{BASE_URL}}assets/images/logo.png">
            </div>
        </div>
        <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden"
            placeholder="example@email.com">
    </form>
    <?php
    }?>
</body>
<script type="text/javascript">
    var verifyCallback = function(response) {
        if(response!=''){
            $("#submit-button").removeAttr("disabled");
        }
      };
      var onloadCallback = function() {
        grecaptcha.render('g-recaptcha', {
          'sitekey' : '{{GOOGLE_CAPTCHA_SITE_KEY}}',
          'callback' : verifyCallback,
        });
      };

    $(document).ready(function () {
        $("#collectContactUsDataForm").validate({
            rules: {
                   <?php foreach($intakeFormFields as $k=>$v){
                        if($v->form_field=="name" && $v->is_required=="yes" ){?>
                            first_name: {
                                required: true,
                                minlength: 2
                            },
                            last_name: {
                                required: true,
                                minlength: 2
                            },
                        <?php } ?>
                        <?php if($v->form_field=="email" && $v->is_required=="yes" ){?>
                            email: {
                                email: false
                            },
                        
                        <?php } ?>
                        <?php if($v->form_field=="home_phone" && $v->is_required=="yes" ){?>
                            home_phone: {
                                number: true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="work_phone" && $v->is_required=="yes" ){?>
                            work_phone: {
                                number: true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="short_text" && $v->is_required=="yes" ){?>
                            short_text: {
                                required: true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="cell_phone" && $v->is_required=="yes" ){?>
                            cell_phone: {
                                number: true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="checkboxes" && $v->is_required=="yes" ){?>
                            "checkboxes[]":{
                                required:true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="yesno" && $v->is_required=="yes" ){?>   
                            "yesno":{
                                required: true
                            }, 
                        <?php } ?>
                        <?php if($v->form_field=="multiple_choice" && $v->is_required=="yes" ){?>
                            "multiple_choice":{
                                required: true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="number" && $v->is_required=="yes" ){?>
                              number: {
                                number: true,
                                required:true
                            },
                        <?php } ?>

                        <?php if($v->form_field=="long_text" && $v->is_required=="yes" ){?>
                            long_text: {
                                required:true
                            },
                        <?php } ?>
                        <?php if($v->form_field=="currency" && $v->is_required=="yes" ){?>
                            currency: {
                                required:true
                            },
                        <?php } ?>
                    <?php } ?>
            },
            messages: {
                first_name: {
                    required: "Please enter first name",
                    minlength: "First name must consist of at least 2 characters"
                },
                last_name: {
                    required: "Please enter last name",
                    minlength: "Last name must consist of at least 2 characters"
                },
                email: {
                    required: "Please enter email",
                    minlength: "Email is not formatted correctly",
                    remote:"The email address already exist.",
                },
                website: {
                    url: "Please enter valid website url"
                },
                home_phone: {
                    number: "Please enter numeric value"
                },
                work_phone: {
                    number: "Please enter numeric value"
                },
                cell_phone: {
                    number: "Please enter numeric value"
                },
                "checkboxes[]":{
                    required:"Please select atleast one option."
                },
                "yesno":{
                    required:"Please select atleast one option."
                },
                "multiple_choice":{
                    required:"Please select atleast one option."
                },
                number: {
                    required: "Please enter a number",
                    number: "Please enter numeric value"
                }
            },

            errorPlacement: function (error, element) {
                if (element.is('#first_name')) {
                    error.appendTo('#fnameError');
                } else if (element.is('#last_name')) {
                    error.appendTo('#lnameError');
                } else if (element.is('#email')) {
                    error.appendTo('#emailError');
                } else if (element.is('#home_phone')) {
                    error.appendTo('#homeError');
                } else if (element.is('#work_phone')) {
                    error.appendTo('#workError');
                } else if (element.is('#cell_phone')) {
                    error.appendTo('#cellError');
                } else if (element.is('#birthday')) {
                    error.appendTo('#birthdayError');
                } else if (element.is('#checkbox')) {
                    error.appendTo('#checkboxError');
                } else if (element.is('#email')) {
                    error.appendTo('#emailError');
                }else if (element.is('#yesno')) {
                    error.appendTo('#yesnoError');
                } else if (element.is('#multiple')) {
                    error.appendTo('#multipleError');
                } else if (element.is('#number')) {
                    error.appendTo('#numberError');
                } else {
                    element.after(error);
                }
            }

        });

        $('#birthday').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            endDate: '+0d',
            "orientation": "bottom",
            'todayHighlight': true
        });
        
        $('.dp').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            endDate: '+0d',
            "orientation": "bottom",
            'todayHighlight': true
        });
        $(document).on("click", ":submit", function (e) {
            $("#current_submit").val($(this).val());
        });
        $('#collectContactUsDataForm').submit(function (e) {
            
            if (!$('#collectContactUsDataForm').valid()) {
                return false;
            }
            
            $("#last-saved-timestamp").text("Processing...");
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#collectContactUsDataForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#collectContactUsDataForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl+'/collectContactUSFormData', // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, Please fix the following error.<br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $(".innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        $("#last-saved-timestamp").text("");
                        grecaptcha.reset();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (jqXHR, exception) {
                    $(".innerLoader").css('display', 'none');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, Please fix the following error.</div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#submit').removeAttr("disabled");
                    $("#last-saved-timestamp").text("");
                    grecaptcha.reset();
                },
            });
        });
    });

</script>
</html>
