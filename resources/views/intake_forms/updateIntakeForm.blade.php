@extends('layouts.master')
@section('title', "Intake Form")
@section('main-content')
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <form class="UpdateAndSaveIntakeForm" id="UpdateAndSaveIntakeForm" name="UpdateAndSaveIntakeForm" method="POST"
            action="{{ route('intake_form/saveUpdateIntakeForm') }}">
            <input type="hidden" name="form_id" value="{{$intakeForm->id}}">
            <span id="response"></span>
            @csrf
            <input type="hidden" name="pressButton" id="pressButton">
            <div class="card mb-4 o-hidden">
                @include('pages.errors')
               
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div id="showError" style="display:none"></div>
                            <div class="header d-flex justify-content-between align-items-center py-3 border-bottom">
                                <h3 class="mb-0">Intake Forms / {{$intakeForm->form_name}}</h3>
                                <div class="d-flex flex-shrink-0 align-items-center">
                                    <div class="d-flex align-items-center mr-4">
                                        <?php if($intakeForm->form_type=="1"){?>
                                        <div>
                                            <a id="intake-forms-help-article-link"
                                                class="btn btn-link " href="#" data-toggle="tooltip" data-trigger="hover" title="" data-content="Edit"
                                                data-placement="top" data-html="true" data-original-title="Learn about Contact Us form" target="_blank"
                                                rel="noopener noreferrer">
                                                <i class="far fa-question-circle"></i>
                                            </a>
                                            <a onclick="copyLink('{{$intakeForm->id}}')" link="{{BASE_URL}}contact_us/{{$intakeForm->form_unique_id}}" id="{{$intakeForm->id}}" data-placement="bottom" href="javascript:;"   title="Copy"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Copy Link"><i class="fas fa-link align-middle" data="MyText"></i></span>
                                            </a>

                                            <a onclick="copyHTMLCode('{{$intakeForm->id}}')" link='<iframe src="{{BASE_URL}}contact_us/{{$intakeForm->form_unique_id}}" title="Contact Us Form" style="border:none;" width="600" height="800"><!-- Specify a width and height by changing the width/height properties of this iframe --></iframe>' id="Code{{$intakeForm->id}}" data-placement="bottom" href="javascript:;"   title="Copy"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Copy HTML Code"><i
                                                class="fas fa-code"></i></span>
                                            </a>

                                           
                                        </div>
                                        <?php }else{ ?>
                                             <div>
                                                <a id="intake-forms-help-article-link"
                                                    class="btn btn-link " href="#" data-toggle="tooltip" data-trigger="hover" title="" data-content="Edit"
                                                    data-placement="top" data-html="true" data-original-title="Learn about Intake form" target="_blank"
                                                    rel="noopener noreferrer">
                                                    <i class="far fa-question-circle"></i>
                                                </a>
                                            </div>
                                         <?php } ?>
                                    </div>
                                    <a target="_blank" href="{{ route('preview/{id}',[$intakeForm->form_unique_id]) }}">
                                        <button type="button" id="preview-form-button" class="mr-2 btn btn-secondary">Preview Form</button>
                                    </a>
                                    <div role="group" class="btn-group">
                                        <button type="submit" name=""  onclick="saveform('s')"  id="save-changes-button" class="btn btn-primary">
                                            Save Changes
                                        </button>
                                        <div class="btn-group">
                                            <button type="button" aria-haspopup="true" aria-expanded="false"
                                                class="dropdown-toggle btn btn-primary" data-toggle="dropdown">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true"
                                                class="dropdown-menu dropdown-menu-right">
                                                <button type="submit"  onclick="saveform('sc')"  id="save-and-close-button" tabindex="0"
                                                    role="menuitem" class="dropdown-item">Save &amp; Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="mb-3">
                        <h4>Basic Form Information</h4>
                        <small class="form-text text-muted"> Appears at the top of the form when the client fills it out.</small>
                    </div>
                    <?php if($intakeForm->form_type=="1"){?>
                        <div class="row col-md-12">
                            <div class="col-md-6 form-group mb-3 dynamicDomain">
                                <label for="firstName1">Authorized Website Domains</label> <i id="csp-opt-out-tooltip" data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="The contact form will only be allowed to be embedded on your website. Please note that only HTTPS domains are allowed." class="far fa-question-circle"></i>
                                <input class="form-control disableornot" value="{{($IntakeFormDomain[0]->domain_url)??''}}" maxlength="255"
                                    id="domain_name" name="domain_name[]" type="text" placeholder="Example: https://www.yourlawfirm.com">
                            </div>
                         
                            <div class="col-md-6 form-group pt-3 mb-0">
                                <label for="firstName1">&nbsp;</label>
                                <div class="form-check-inline ml-4"><input class="form-check-input" id="csp-opt-out" name="authorised_domain" type="checkbox" <?php if($intakeForm->authorised_domain=="yes"){ echo "checked=checked"; } ?>><label class="form-check-label mr-1" for="csp-opt-out">Require Authorized Domain</label><i id="csp-opt-out-tooltip" class="far fa-question-circle" data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="The use of authorized domains is recommended for web security."></i></div>
                            </div>
                        </div>
                        <?php 
                        foreach($IntakeFormDomain as $kk=>$vv){
                            if($kk>0){?>
                                <div class="row col-md-12" id="rowDom_{{$kk}}">
                                    <div class="col-md-6 form-group mb-3 dynamicDomain">
                                        <input class="form-control domain_name disableornot" value="{{$vv->domain_url}}" maxlength="255"   id="domain_name" name="domain_name[]" type="text" placeholder="Example: https://www.yourlawfirm.com">
                                       
                                    </div>
                                    <i class="mt-2 fas fa-trash text-black-50 cursor-pointer" onclick="removeDom({{$kk}})"></i>
                                </div>
                        <?php } } ?>
                         <div class="" id="addMoreDomainArea"></div>
                        <button type="button" class="btn btn-link disableornot" onclick="addMoreDomainArea()">Add Domain</button>
                       
                    <?php } ?>
                    <div class="col-md-6 form-group mb-3">
                        <label for="firstName1">Form Name</label>
                        <input class="form-control" value="{{($intakeForm->form_name)??''}}" maxlength="255"
                            id="form_name" name="form_name" type="text" placeholder="E.g. Family Law Intake Form">
                    </div>
                    <div class="col-md-12 form-group mb-3">
                        <label for="firstName1">Form Introduction</label>
                        <textarea id="introduction-input" name="form_introduction"
                            placeholder="The purpose of this form is..." rows="3"
                            class="form-control">{{($intakeForm->form_introduction)??''}}</textarea>
                    </div>
                    <h4 class="mb-3">Form Fields</h4>
                    <div class="fields-header row mb-2">
                        <div class="col-3">
                            <h5 class="mb-1">Category</h5><small class="form-text text-muted">Select the field
                                category.</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-1">Field</h5><small class="form-text text-muted">Select a MyCase field or
                                choose a question format for unmapped fields.</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-1">Client Friendly Label</h5><small class="form-text text-muted">Rephrase your
                                label to how you want your client to see it.</small>
                        </div>
                    </div>
                    <div>
                        <div style="" id="sortable">
                            <?php 
                            foreach($intakeFormFields as $k=>$v){
                            
                                if($v->form_category==NULL) { ?>
                            <div class="field-row p-2 border" id="row{{$k}}">
                                <div class="d-flex flex-row">
                                    <div class="field-row-left">
                                        <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div>
                                    </div>
                                    <div class="field-row-center flex-fill">
                                        <div class="d-flex flex-row">
                                            <div class="col">
                                                <div class="label-field-container">
                                                    <div class=""> 
                                                        <input name="category[{{$k}}]" placeholder=""
                                                            type="text" id="header_{{$k}}" class="label-input form-control" value="{{$v->header_text}}"> 
                                                        </div>
                                                </div>
                                                <div class="collapse"></div>
                                            </div>
                                            <div class="field-row-right flex-shrink-0"> <button type="button"
                                                    title="Toggle Options"
                                                    class="collapse-button px-1 mr-2 invisible btn btn-link"> <i
                                                        class="fas fa-caret-down"></i> </button> <button type="button"
                                                    title="Delete Field" class="px-1 mr-1 btn btn-link btn_remove"
                                                    name="remove" id="{{$k}}"><i class="fas fa-trash text-black-50"></i>
                                                </button> </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <?php 
                                 }else{
                                     
                                ?>
                            <div class="field-row p-2 border" id="row{{$k}}">
                                <div class="d-flex flex-row">
                                    <div class="field-row-left">
                                        <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i>
                                        </div>
                                    </div>
                                    <div class="field-row-center flex-fill">
                                        <div class="d-flex flex-row">
                                            <div class="col-3">
                                                <div class="select-category-container">
                                                    <select class="category_list form-control" name="category[{{$k}}]"
                                                        onchange="changeCategory({{$k}})" id="category_{{$k}}"
                                                        style="width: 100%;">
                                                        <option value="">Select...</option>
                                                        <option
                                                            <?php if($v->form_category=="contact_field") { echo "selected=selected"; } ?>
                                                            value="contact_field">Contact Field
                                                        </option>
                                                        <option
                                                            <?php if($v->form_category=="case_field") { echo "selected=selected"; } ?>
                                                            value="case_field">Case Field</option>
                                                        <option
                                                            <?php if($v->form_category=="unmapped_field") { echo "selected=selected"; } ?>
                                                            value="unmapped_field">Unmapped Field</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="select-field-container">
                                                    <?php if($v->form_category=="contact_field") {?>
                                                    <select class="fields_list form-control country"
                                                        onchange="changeFields({{$k}})" id="field_{{$k}}"
                                                        name="form_field[{{$k}}]" data-placeholder="Select"
                                                        style="width: 100%;">
                                                        <option value="">Select...</option>
                                                        <option
                                                            <?php if($v->form_field=="name") { echo "selected=selected"; } ?>
                                                            value="name">Name</option>
                                                        <option
                                                            <?php if($v->form_field=="email") { echo "selected=selected"; } ?>
                                                            value="email">Email</option>
                                                        <option
                                                            <?php if($v->form_field=="work_phone") { echo "selected=selected"; } ?>
                                                            value="work_phone">Work Phone</option>
                                                        <option
                                                            <?php if($v->form_field=="home_phone") { echo "selected=selected"; } ?>
                                                            value="home_phone">Home Phone</option>
                                                        <option
                                                            <?php if($v->form_field=="cell_phone") { echo "selected=selected"; } ?>
                                                            value="cell_phone">Cell Phone</option>
                                                        <option
                                                            <?php if($v->form_field=="birthday") { echo "selected=selected"; } ?>
                                                            value="birthday">Birthday</option>
                                                        <option
                                                            <?php if($v->form_field=="driver_license") { echo "selected=selected"; } ?>
                                                            value="driver_license">Driver license</option>
                                                        <option
                                                            <?php if($v->form_field=="address") { echo "selected=selected"; } ?>
                                                            value="address">Address</option>
                                                        <optgroup label="Custom Fields">
                                                            <option value="custom_field"> + <a href="">Create a
                                                                    custom field</a></option>
                                                        </optgroup>
                                                    </select>
                                                    <?php }else if($v->form_category=="unmapped_field") {?>
                                                        <select class="fields_list form-control country"
                                                        onchange="changeFields({{$k}})" id="field_{{$k}}"
                                                        name="form_field[{{$k}}]" data-placeholder="Select"
                                                        style="width: 100%;">
                                                        <option value="">Select...</option>
                                                        <option  <?php if($v->form_field=="short_text") { echo "selected=selected"; } ?> value="short_text">Short Text</option>
                                                        <option  <?php if($v->form_field=="long_text") { echo "selected=selected"; } ?> value="long_text">Long Text</option>
                                                        <option  <?php if($v->form_field=="yesno") { echo "selected=selected"; } ?> value="yesno">Yes/No</option>
                                                        <option  <?php if($v->form_field=="number") { echo "selected=selected"; } ?> value="number">Number</option>
                                                        <option  <?php if($v->form_field=="currency") { echo "selected=selected"; } ?> value="currency">Currency</option>
                                                        <option  <?php if($v->form_field=="date") { echo "selected=selected"; } ?> value="date">Date</option>
                                                        <option  <?php if($v->form_field=="multiple_choice") { echo "selected=selected"; } ?> value="multiple_choice">Multiple Choice</option>
                                                        <option  <?php if($v->form_field=="checkboxes") { echo "selected=selected"; } ?> value="checkboxes">Checkboxes</option>
                                                    </select>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="label-field-container">
                                                    <div class="">
                                                        <input name="user_friendly_label[{{$k}}]"
                                                            id="user_friendly_label_{{$k}}" placeholder="" type="text"
                                                            class="label-input form-control" value="{{($v->client_friendly_lable)??''}}">
                                                    </div>
                                                </div>
                                                <div class="required-checkbox pt-2 text-black-50">
                                                    <label class="d-inline-flex align-items-center">
                                                        <input id="-option" name="requiredCheckbox[{{$k}}]" type="checkbox" <?php if($v->is_required=="yes") { echo "checked=checked"; } ?>>
                                                        <span class="ml-2 ">Required</span>
                                                        
                                                    </label>
                                                    <?php if($v->form_field=="multiple_choice" || $v->form_field=="checkboxes")  {
                                                   
                                                        ?>
                                                        <div class="col">
                                                            <?php 
                                                            $c=0;
                                                             foreach(json_decode($v->extra_value) as $k11=>$v11){?>
                                                                <div id="remove_row_{{$k}}_{{$k11}}" class="d-flex align-items-center pb-2 test-list-option">
                                                                    <input placeholder="Option" name="currentRow[{{$k}}][{{$k11}}]"
                                                                        type="text" class="form-control form-control-{{$k}}" value="{{$v11}}"> 
                                                                        <i class="fas fa-trash text-black-50 ml-2 cursor-pointer"
                                                                        onclick="removeRow({{$k}},{{$k11}});"></i> 
                                                                </div>
                                                            <?php  $c++;
                                                            } ?>
                                                            <span id="am_{{$k}}"></span>
                                                            <input type="hidden" id="rowCounter_{{$k}}"  name="row_{{$k}}" value="{{$c}}">
                                                            <button type="button" class="btn btn-link" onclick="addMore({{$k}})">Add Another</button>
                                                        </div>
                                                     <?php } ?>
                                                </div>
                                                <div id="dyncamic_{{$k}}"></div>
                                            </div>
                                        </div>
                                        <div class="collapse"></div>
                                    </div>
                                    <div class="field-row-right flex-shrink-0">
                                        <button type="button" title="Delete Field"
                                            class="px-1 mr-1 btn btn-link btn_remove" name="remove" id="{{$k}}">
                                            <i class="fas fa-trash text-black-50"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php } 
                            }
                            ?>
                        </div>
                        <div class="d-flex flex-row align-items-center mt-3">
                            <button class="btn btn-outline-secondary m-1 btn-rounded" id="addField" type="button">Add
                                Field</button>
                            <button class="btn btn-outline-secondary m-1 btn-rounded" id="addHeader" type="button">Add
                                Header</button>
                        </div>

                    </div>
                    <?php if($intakeForm->form_type=="1"){?>
                    <section>
                        <hr>
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="form-check-inline my-2 mr-4">
                                <input class="form-check-input" id="automatic-email" name="automatic_email" type="checkbox" <?php if($intakeForm->send_confimation_mail=="yes"){ echo "checked=checked";}?> >
                                <label class="form-check-label" for="automatic-email">Send a confirmation email to the lead when the form is submitted &nbsp;&nbsp;</label>
                                <a data-toggle="modal"  data-target="#reactivateLead" data-placement="bottom" href="javascript:;"    data-testid="mark-no-hire-button" class="reactivate" > View confirmation email</a>
                                
                            </div>
                            <a href="/account/action_updates" target="_blank" class="btn btn-secondary">Setup My Form Submission Notifications</a>
                        </div>
                        <hr>
                    </section>
                    <?php } ?>
                    <div class="p-0 container-fluid">
                        <div>
                            <h3 class="d-inline pr-3">Theme</h3><span class="h5 text-black-50">Applies to all
                                Intake/Contact Us
                                Forms</span>
                        </div>
                        <hr>
                        <div class="d-flex w-75">
                            <div>
                                <div class="d-flex">
                                    <div class="my-2 mr-4">
                                        <div id="background-color-button">
                                            <label class="d-block text-nowrap ">Background Color</label>
                                            <button aria-label="Background Color" id="background-color-button1"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black;outline: none;background-color:#{{$intakeForm->background_color}}">
                                            </button>
                                            <input type="hidden" name="background_color_code" id="background_color_code"
                                                value="{{$intakeForm->background_color}}">
                                        </div>
                                    </div>
                                    <div class="my-2 undefined">
                                        <div id="button-color-button">
                                            <label class="d-block text-nowrap ">Button Color</label>
                                            <button aria-label="Button Color" id="background-color-button2"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color:#{{$intakeForm->button_color}};"outline: none;"></button>
                                            <input type="hidden" name="button_color_code" id="button_color_code"
                                                value="{{$intakeForm->button_color}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="my-2 mr-4">
                                        <div id="form-font-color-button">
                                            <label class="d-block text-nowrap ">Form Font Color</label>
                                            <button aria-label="Form Font Color" id="background-color-button3"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color:#{{$intakeForm->form_font_color}};" outline: none;"></button>
                                            <input type="hidden" name="form_font_color_code" id="form_font_color_code"
                                                value="{{$intakeForm->form_font_color}}">

                                        </div>
                                    </div>
                                    <div class="my-2 undefined">
                                        <div id="button-font-color-button">
                                            <label class="d-block text-nowrap ">Button Font Color</label>
                                            <button aria-label="Button Font Color" id="background-color-button4"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color:#{{$intakeForm->button_font_color}};" outline: none;">

                                            </button>
                                            <input type="hidden" name="button_font_color_code"
                                                id="button_font_color_code" value="{{$intakeForm->button_font_color}}">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-3 w-50">
                                <div class="m-2 w-75">
                                    <label class="text-nowrap ">Select a Form Font</label>
                                    <div class="Select has-value is-clearable is-searchable Select--single">
                                        <select class="form-control" id="form_font_style" name="form_font_style" style="width: 100%;">
                                            <option <?php if($intakeForm->form_font=="arial") { echo "selected=selected"; } ?> value="arial">Arial (Sans Serif)</option>
                                            <option <?php if($intakeForm->form_font=="times_new_roman") { echo "selected=selected"; } ?> value="times_new_roman">Times New Roman (Serif)</option>
                                            <option <?php if($intakeForm->form_font=="helvetica") { echo "selected=selected"; } ?> value="helvetica">Helvetica (Sans Serif)
                                            </option>
                                            <option <?php if($intakeForm->form_font=="palatino") { echo "selected=selected"; } ?> value="palatino">Palatino (Serif)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="m-2 w-75">
                                    <label class="text-nowrap ">Select a Button Font</label>
                                    <div class="Select has-value is-clearable is-searchable Select--single">
                                        <select class="form-control" id="form_button_font_style" name="form_button_font_style" style="width: 100%;">
                                            <option <?php if($intakeForm->button_font=="arial") { echo "selected=selected"; } ?> value="arial">Arial (Sans Serif)</option>
                                            <option <?php if($intakeForm->button_font=="times_new_roman") { echo "selected=selected"; } ?> value="times_new_roman">Times New Roman (Serif)</option>
                                            <option <?php if($intakeForm->button_font=="helvetica") { echo "selected=selected"; } ?> value="helvetica">Helvetica (Sans Serif)
                                            </option>
                                            <option <?php if($intakeForm->button_font=="palatino") { echo "selected=selected"; } ?> value="palatino">Palatino (Serif)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div><button type="button" class="btn btn-secondary mt-3" onclick="resetOptions();">Set to Default</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="reactivateLead" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form class="reactivateLeadForm" id="reactivateLeadForm" name="reactivateLeadForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation Email Preview</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <table id="email_content" border="0" cellpadding="0" cellspacing="0" class="body">
                        <tbody><tr>
                          <td class="container">
                            <div class="content">
                              <span class="hidden"></span>
                              <section class="message-section">
                                <div class="message">
                                    <h2>Thank you for your inquiry.</h2>
                                    <p>
                                        Our team will reply to you very shortly.<br>
                                        We look forward to working with you.
                                    </p>
                                </div>
                              </section>
                              <section class="closing-section">
                                <p>
                                    Thank you,<br>
                                   {{$firmData->firm_name}}
                                </p>
                              </section>
                  
                              <footer class="">
                                <p>
                                  This is an automated notification. To protect the confidentiality of these communications,<br>
                                  <b>PLEASE DO NOT REPLY TO THIS EMAIL.</b>
                                </p>
                                <p>
                                  This email was sent to you by {{$firmData->firm_name}}. <br>
                                  Powered by <a href="">{{config('app.name')}}</a> | {{ADDRESS}} 
                                </p>
                              </footer>
                            </div>
                          </td>
                        </tr>
                      </tbody></table>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Close</button>
                            </a>
                           
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<input type="text" value="" id="myInput" style="opacity: 00000;">
<input type="text" value="" id="htmlCode" style="opacity: 00000;">

    <style>
        #sortable {
            background-color: #FAFAFB;
        }

        .grabcursor {
            cursor: grab;
        }

    </style>
    @endsection
    @section('page-js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('button').attr('disabled', false);
            $("#preloader").hide();
            $("#sortable").sortable();
            $("[data-toggle=popover]").popover();
            $("[data-toggle=tooltip]").tooltip();
            $("#UpdateAndSaveIntakeForm").validate({
                rules: {
                    form_name: {
                        required: true,
                    },
                    'category[]': "required",
                    'form_field[]': "required"
                },
                messages: {
                    form_name: {
                        required: "Please enter a name for your intake form.",
                    },
                    'category[]': "Please select a category.",
                    'form_field[]': "Please select a field."

                },
            });

            var i = $(".field-row ").length;
            $('#addField').click(function (e) {
                i++;
                $('#sortable').append('<div class="field-row p-2 border" id="row' + i +
                    '"> <div class="d-flex flex-row"> <div class="field-row-left"> <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div></div><div class="field-row-center flex-fill"> <div class="d-flex flex-row"> <div class="col-3"> <div class="select-category-container"> <select class="category_list form-control " onchange="changeCategory(' +
                    i + ')"  id="category_' + i + '" name="category[' + i +
                    ']" style="width: 100%;"> <option value="">Select...</option> <option value="contact_field">Contact Field</option> <option value="case_field">Case Field</option> <option value="unmapped_field">Unmapped Field</option> </select> </div></div><div class="col"> <div class="select-field-container"> <select class="fields_list form-control country" onchange="changeFields(' +
                    i + ')" disabled id="field_' + i + '"  name="form_field[' + i +
                    ']" style="width: 100%;"> <option value="">Select...</option> </select> </div></div><div class="col"> <div class="label-field-container"> <div class=""> <input disabled id="user_friendly_label_' +
                    i + '" name="user_friendly_label[' + i +
                    ']" placeholder="" type="text" id="text_' + i +
                    '"  class="label-input form-control" value=""> </div></div><div class="required-checkbox pt-2 text-black-50"> <label class="d-inline-flex align-items-center"> <input id="-option" name="requiredCheckbox[' +
                    i +
                    ']" type="checkbox" checked=""> <span class="ml-2 ">Required</span> </label> </div><div id="dyncamic_'+i+'"></div></div></div><div class="collapse"></div></div><div class="field-row-right flex-shrink-0"> <button type="button" title="Toggle Options" class="collapse-button px-1 mr-2 invisible btn btn-link"> <i class="fas fa-caret-down"></i> </button> <button type="button" title="Delete Field" class="px-1 mr-1 btn btn-link btn_remove" name="remove" id="' +
                    i + '"> <i class="fas fa-trash text-black-50"></i></button></div></div></div>');

            });
            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
                $('#row' + button_id + '').remove();
            });

            $('#addHeader').click(function () {
                i++;
                $('#sortable').append('<div class="field-row p-2 border" id="row' + i +
                    '"> <div class="d-flex flex-row"> <div class="field-row-left"> <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div></div><div class="field-row-center flex-fill"> <div class="d-flex flex-row"> <div class="col"> <div class="label-field-container"> <div class=""> <input  name="category[' +
                    i + ']" placeholder="" type="text" id="header_' + i +
                    '" class="label-input form-control" value=""> </div></div><div class="collapse"></div></div><div class="field-row-right flex-shrink-0"> <button type="button" title="Toggle Options" class="collapse-button px-1 mr-2 invisible btn btn-link"> <i class="fas fa-caret-down"></i> </button> <button type="button" title="Delete Field" class="px-1 mr-1 btn btn-link btn_remove" name="remove" id="' +
                    i + '"><i class="fas fa-trash text-black-50"></i> </button> </div></div></div>');
            });
            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
                $('#row' + button_id + '').remove();
            });
            $("#background-color-button1").ColorPicker({
                color: '#{{$intakeForm->background_color}}',
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#background-color-button1').css('backgroundColor', '#' + hex);
                    $('#background_color_code').val(hex);
                }
            });
            $("#background-color-button2").ColorPicker({
                color: '#0070C0',
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#background-color-button2').css('backgroundColor', '#' + hex);
                    $('#button_color_code').val(hex);
                }
            });
            $("#background-color-button3").ColorPicker({
                color: '#000000',
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#background-color-button3').css('backgroundColor', '#' + hex);
                    $('#form_font_color_code').val(hex);

                }
            });
            $("#background-color-button4").ColorPicker({
                color: '#F7F7F7',
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#background-color-button4').css('backgroundColor', '#' + hex);
                    $('#button_font_color_code').val(hex);

                }
            });

            $('#UpdateAndSaveIntakeForm').submit(function (e) {
                $('.category_list').each(function() {
                    $(this).rules("add",{ required: true, messages: { required: "Please select a category."} });
                });
                $('.fields_list').each(function() {
                    $(this).rules("add",{required: true,messages: { required: "Please select a field."} });
                });   

                $('.domain_name').each(function() {
                    $(this).rules("add",{required: true,messages: { required: "Please enter a valid HTTPS domain."} });
                    $(this).rules("add",{url: true,messages: { required: "Please enter a valid HTTPS domain."} });
                });   
            });
            $("#UpdateAndSaveIntakeForm").validate();
            $("input:checkbox#csp-opt-out").click(function () {
                if ($(this).is(":checked")) {
                   $(".disableornot").removeAttr('disabled');
                } else {
                    $(".disableornot").attr('disabled',true);
                }
            });

        });
        function addMore(row){
            var c= $("#rowCounter_"+row).val();
            $("#rowCounter_"+row).val(++c);
            var c1= $("#rowCounter_"+row).val();
            $("#am_" + row).append('<div id="remove_row_'+row+'_'+c1+'" class="d-flex align-items-center pb-2 test-list-option"><input placeholder="Option" type="text" class="form-control" value="" name="currentRow['+row+']['+c1+']"> <i class="fas fa-trash text-black-50 ml-2 cursor-pointer" onclick="removeRow('+row+','+c1+');"></i> </div>');
            
        }

        function removeRow(row,counter){
            $("#remove_row_" + row+'_'+counter).remove();
            var c= $("#rowCounter_"+row).val();
            $("#rowCounter_"+row).val(--c);
        }
        function changeCategory(currentRow) {
            var array = [];
            $('.fields_list').each(function() {
                var curid= $(this).attr('id');
                var selectedOption = $("#"+curid +" option:selected").val();
                array.push(selectedOption);
              });  

            var selectedOption = $("#category_" + currentRow + " option:selected").val();
            if (selectedOption == "contact_field" || selectedOption == "case_field" || selectedOption ==
                "unmapped_field") {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/intake_form/loadFields",
                    data: {
                        "typpe": selectedOption,
                        "alreadySelected" : JSON.stringify(array)
                    },
                    success: function (res) {
                        $("#field_" + currentRow).removeAttr('disabled');
                        $("#user_friendly_label_" + currentRow).removeAttr('disabled');
                        $("#field_" + currentRow).html(res);
                    }
                })
            } else {
                $("#field_" + currentRow).attr('disabled', 'disabled');
                $("#user_friendly_label_" + currentRow).attr('disabled', 'disabled');
                $("#field_" + currentRow).prop('selectedIndex', "");

            }
        }

        function changeCategorySelected(currentRow, selectedRow) {
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/loadFieldsSelected",
                data: {
                    "typpe": 'contact_field',
                    "selectedRow": selectedRow
                },
                success: function (res) {
                    $("#field_" + currentRow).removeAttr('disabled');
                    $("#user_friendly_label_" + currentRow).removeAttr('disabled');
                    $("#field_" + currentRow).html(res);
                }
            })
        }

        // function changeFields(currentRow) {
        //     var selectedOption = $("#field_" + currentRow + " option:selected").text();
        //     $("#user_friendly_label_" + currentRow).attr('placeholder', selectedOption);
        // }
        function changeFields(currentRow) {
            var selectedOption = $("#field_" + currentRow + " option:selected").text();
            $("#user_friendly_label_" + currentRow).attr('placeholder', selectedOption);

            var selectedOption = $("#category_" + currentRow + " option:selected").val();
            if (selectedOption == "unmapped_field") {
                $("#user_friendly_label_" + currentRow).rules("add",{required: true,messages: { required: "Label cannot be blank."} });

                var selectedOptionList = $("#field_" + currentRow + " option:selected").val();
                if(selectedOptionList=="multiple_choice" || selectedOptionList=="checkboxes"){
                    $("#dyncamic_" + currentRow).html('<div class="col"><div id="remove_row_'+currentRow+'_1" class="d-flex align-items-center pb-2 test-list-option"><input placeholder="Option" name="currentRow['+currentRow+'][1]" type="text" class="form-control form-control-'+currentRow+'" value=""> <i class="fas fa-trash text-black-50 ml-2 cursor-pointer" onclick="removeRow('+currentRow+',1);"></i> </div><div id="remove_row_'+currentRow+'_2" class="d-flex align-items-center pb-2 test-list-option"><input placeholder="Option" name="currentRow['+currentRow+'][2]" type="text" class="form-control form-control-'+currentRow+'" value=""> <i class="fas fa-trash text-black-50 ml-2 cursor-pointer" onclick="removeRow('+currentRow+',2);"></i> </div><div id="remove_row_'+currentRow+'_3" class="d-flex align-items-center pb-2 test-list-option"><input name="currentRow['+currentRow+'][3]" placeholder="Option" type="text" class="form-control form-control-'+currentRow+'" value=""> <i class="fas fa-trash text-black-50 ml-2 cursor-pointer" onclick="removeRow('+currentRow+',3);"></i> </div><span id="am_'+currentRow+'"></span> <input type="hidden" id="rowCounter_'+currentRow+'"  name="row_'+currentRow+'" value="3"><button type="button" class="btn btn-link" onclick="addMore('+currentRow+')">Add Another</button></div>');
                }else{
                    $("#dyncamic_" + currentRow).html('');
                }
            }else{
                $("#user_friendly_label_" + currentRow).rules("remove");
            }
        }

        function resetOptions(){
            $('#background-color-button1').css('backgroundColor', '#ffffff');
            $('#background_color_code').val("ffffff");

            $('#background-color-button2').css('backgroundColor', '#0070c0');
            $('#button_color_code').val('0070c0');

            $('#background-color-button3').css('backgroundColor', '#000000');
            $('#form_font_color_code').val('000000');

            $('#background-color-button4').css('backgroundColor', '#ffffff');
            $('#button_font_color_code').val("ffffff");
            $('#form_font_style').prop('selectedIndex', "2");
            $('#form_button_font_style').prop('selectedIndex', "2");

        }
        function saveform(id){
            $("#pressButton").val(id);
            $('#UpdateAndSaveIntakeForm').submit();
        }

        $('#UpdateAndSaveIntakeForm').submit(function (e) {
           
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
           
            if (!$('#UpdateAndSaveIntakeForm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }

            var me = $(this);
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);
            var dataString = '';
            dataString = $("#UpdateAndSaveIntakeForm").serialize();
           
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/saveUpdateIntakeForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        me.data('requestRunning', true);
                        return false;
                    } else {
                        window.location.href=baseUrl+res.url;
                    }
                },
                complete: function() {
                    me.data('requestRunning', false);
                }
            });
        });
        $(".dropdown-toggle").trigger("click");

        function copyLink(id) {
            var links=$("#"+id).attr("link");
            $("#myInput").val(links);
            var copyText = document.getElementById("myInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            document.execCommand("copy");
            toastr.info('Code copied to clipboard!', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }
        function copyHTMLCode(id) {
            var links=$("#Code"+id).attr("link");
            $("#htmlCode").val(links);
            var copyText = document.getElementById("htmlCode");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            document.execCommand("copy");
            toastr.info('Code copied to clipboard!', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }
        function addMoreDomainArea(){
            var curCounter=$(".dynamicDomain").length+1;
            $("#addMoreDomainArea").append('<div class="row col-md-12 dynamicDomain" id="rowDom_'+curCounter+'"> <div class="col-md-6 form-group mb-3"><input class="domain_name form-control disableornot" value="" maxlength="255" id="domain_name" name="domain_name[]" type="text" placeholder="Example: https://www.yourlawfirm.com"></div> <i class="mt-2 fas fa-trash text-black-50 cursor-pointer" onclick="removeDom('+curCounter+')"></i> </div>');
            
        }
        function removeDom(domid){
            $("#rowDom_"+domid).remove();
        }
    </script>
    @stop
