<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <title>
        {{$firmData->firm_name}} - {{$intakeForm->form_name}} - {{config('app.name')}}
    </title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/preview/bootstrap-style-preview.css')}}">
    <link rel="stylesheet" media="screen" href="{{asset('public/assets/styles/css/preview/spacing.css')}}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/bootstrap-datepicker3.min.css')}}">
    <script src="{{asset('public/assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <style>
        .logoView {
            width: 50px;
        }

        .error {
            color: red;
        }

        .form-field-container {
            color: {{$intakeForm->form_font_color}};
        }

    </style>
</head>

<body class="">
    <div class="main-content">
        <div id="form-preview-root" class="form-container">
            <div class="form p-4 p-md-5 mx-auto shadow" data-testid="form-container"
                style="background-color: {{$intakeForm->background_color}}; color: rgb(0, 0, 0); font-family: {{$intakeForm->form_font}}">
                <div class="alert alert-primary preview-banner" role="alert">This preview allows you to see what clients
                    will see. No data will be collected or recorded.</div>
                <div id="form-info">
                    <h3 class="heading mb-3 test-firm-name"></h3>
                    <h4 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm->form_name}}</h4>

                    <p class=" heading mb-3 test-form-name">{{$intakeForm->form_introduction}}</p>
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
                                                        <input id="first_name" name="first_name" class="form-control "
                                                            type="text" placeholder="First Name"
                                                            data-testid="first_name" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group"><input id="middle_initial"
                                                            name="middle_initial" class="form-control " type="text"
                                                            placeholder="Middle Name" data-testid="middle_initial"
                                                            value=""></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-5 pl-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group"><input id="last_name" name="last_name"
                                                            class="form-control " type="text" placeholder="Last Name"
                                                            data-testid="last_name" value=""></div>
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
                            <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Email'}}
                                <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                            <div class="">
                                <div class="">
                                    <div class="row no-gutters">
                                        <div class="col-12 pr-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group">
                                                        <input id="first_name" name="first_name" class="form-control "
                                                            type="text" placeholder="" data-testid="first_name"
                                                            value="">
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
                            <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Home phone'}}
                                <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                            <div class="">
                                <div class="">
                                    <div class="row no-gutters">
                                        <div class="col-12 pr-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group">
                                                        <input id="first_name" name="first_name" class="form-control "
                                                            type="text" placeholder="" data-testid="first_name"
                                                            value="">
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
                            <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Cell phone'}}
                                <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                            <div class="">
                                <div class="">
                                    <div class="row no-gutters">
                                        <div class="col-12 pr-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group">
                                                        <input id="first_name" name="first_name" class="form-control "
                                                            type="text" placeholder="" data-testid="first_name"
                                                            value="">
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
                            <label class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Work phone'}}
                                <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                            <div class="">
                                <div class="">
                                    <div class="row no-gutters">
                                        <div class="col-12 pr-2">
                                            <div>
                                                <div class="">
                                                    <div class="input-group">
                                                        <input id="first_name" name="first_name" class="form-control "
                                                            type="text" placeholder="" data-testid="first_name"
                                                            value="">
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
                            class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Address'}}
                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                        <div class="">
                            <div class="" id="address">
                                <div class="row form-group"><label for="address_address1"
                                        class="sr-only sr-only-focusable col-sm-12 col-form-label">Address</label>
                                    <div class="col-12"><input id="address_address1" name="address1"
                                            placeholder="Address" type="text" class="form-control" value=""></div>
                                </div>
                                <div class="row form-group"><label for="address_address2"
                                        class="sr-only sr-only-focusable col-sm-12 col-form-label">Address 2</label>
                                    <div class="col-12"><input id="address_address2" name="address2"
                                            placeholder="Address 2" type="text" class="form-control" value=""></div>
                                </div>
                                <div class="no-gutters row ">
                                    <div class="pr-sm-3 col-12 col-sm-6">
                                        <div class="row form-group"><label for="address_city"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">City</label>
                                            <div class="col-12"><input id="address_city" name="city" placeholder="City"
                                                    type="text" class="form-control" value=""></div>
                                        </div>
                                    </div>
                                    <div class="pr-3 col-4 col-sm-3 col-md-2">
                                        <div class="row form-group"><label for="address_state"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">State</label>
                                            <div class="col-12"><input id="address_postal" name="postal"
                                                    placeholder="State" type="text" class="form-control" value=""></div>
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-3 col-md-4">
                                        <div class="row form-group"><label for="address_postal"
                                                class="sr-only sr-only-focusable col-sm-12 col-form-label">Zip</label>
                                            <div class="col-12"><input id="address_postal" name="postal"
                                                    placeholder="Zip" type="text" class="form-control" value=""></div>
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
                                                    <input id="birthday" class="form-control" type="text" value="">
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
                            class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Driver license'}}
                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                        <div class="">
                            <div class="row ">
                                <div class="pr-2 col-6"><input id="driver license_number" name="driver license_number"
                                        placeholder="Driver License" autocomplete="off" type="text"
                                        class="form-control form-control" value=""></div>
                                <div class="p-0 col-2">
                                    <input id="driver license_number" name="driver license_number" placeholder="State"
                                        autocomplete="off" type="text" class="form-control form-control" value="">
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
                                        value="{{($filledData->client_friendly_lable)??''}}"></textarea></div>

                            </div>
                        </div>
                    </div>
                    <?php } else if($v->form_field=="short_text"){
                            ?>
                    <div id="field-row-driver license" class="form-field-container mb-3 driver-license-test"><label
                            class="field-label font-weight-bold">{{($v->client_friendly_lable)??'Sort Text'}}
                            <?php if($v->is_required=="yes"){ echo "<span class='error'>*</span>";} ?></label>
                        <div class="">
                            <div class="row ">
                                <div class="pr-2 col-12">
                                    <textarea id="driver license_number"
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
                                    <select name="multiple_choice_{{$v->id}}" class="form-control form-control"
                                        <?php if($v->is_required=="yes"){ echo "required";} ?>>
                                        <?php  $options=json_decode($v->extra_value);
                                            foreach($options as $kkey=>$kVal){ ?>
                                        <option value="{{$kVal}}">{{$kVal}}</option>
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
                                        <input type="checkbox" class="pick-option ml-2" value="{{$kVal}}">
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
                                        <input type="radio" name="yesno" class="pick-option ml-2" value="yes">
                                        <span>Yes</span>
                                    </label>
                                    <label class="form-check-label">
                                        <input type="radio" name="yesno" class="pick-option ml-2" value="No">
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
                                    <input id="driver license_number" name="number" placeholder="Number"
                                        autocomplete="off" type="number" class="form-control form-control" value="">
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
                                    <input id="driver license_number" name="currency" placeholder="Currency"
                                        autocomplete="off" type="text" class="form-control form-control" value=""></div>
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
                                    <input id="datepicker" name="date" placeholder="Date" autocomplete="off" type="text"
                                        class="form-control form-control" value=""></div>
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
                    <div id="form-footer" class="border-top pt-3 mt-3 d-print-none">
                        <button type="button" id="submit-button" disabled="" class="btn btn-cta-primary disabled"
                            style="background-color:#{{$intakeForm->button_color}}; color:#{{$intakeForm->button_font_color}}; font-family: {{$intakeForm->button_font}}; border-color:#{{$intakeForm->button_color}};">Submit
                            Form</button>
                        <button type="button" id="save-progress-button" disabled=""
                            class="ml-2 btn btn-secondary disabled"
                            style="background-color:#{{$intakeForm->button_color}}; color:#{{$intakeForm->button_font_color}}; font-family: {{$intakeForm->button_font}}; border-color:#{{$intakeForm->button_color}};">Save
                            Progress</button>
                        <div class="py-3 py-md-0 d-md-inline-block ml-md-3"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mycase-watermark text-muted text-center">
            <span class="mr-1">Powered by</span>
            <img class="logoView" src="{{BASE_URL}}assets/images/logo.png">
        </div>
    </div>
</body>

<script type="text/javascript">
    $(document).ready(function () {
        $('#birthday').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            endDate: '+0d',
            'todayHighlight': true
        });
        $('#datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
    });
</script>
</html>
