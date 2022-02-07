@extends('layouts.master')
@section('title', "Intake Form")
@section('main-content')
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <form class="SaveIntakeForm" id="SaveIntakeForm" name="SaveIntakeForm">
            <span id="response"></span>
            @csrf
            <input type="hidden" name="pressButton" id="pressButton">
            <div class="card mb-4 o-hidden">
                @include('pages.errors')
                <div id="showError" style="display:none"></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="header d-flex justify-content-between align-items-center py-3 border-bottom">
                                <h3 class="mb-0">Intake Forms / New Intake Form</h3>
                                <div class="d-flex flex-shrink-0 align-items-center">
                                    <div class="d-flex align-items-center mr-4">
                                        <div>
                                            <a id="intake-forms-help-article-link"
                                                class="btn btn-link p-0 mr-2 text-black-50" href="#" target="_blank"
                                                rel="noopener noreferrer">
                                                <i class="far fa-question-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <button type="button" id="preview-form-button" class="mr-2 btn btn-secondary">Preview Form</button>
                                    <div role="group" class="btn-group">
                                        <button type="submit" name="" onclick="saveform('s')" value="savechanges" id="save-changes-button" class=" saveform btn btn-primary">
                                            Save Changes
                                        </button>
                                        <div class="btn-group">
                                            <button type="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-primary" data-toggle="dropdown">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                                <button type="submit" onclick="saveform('sc')" value="saveclose" id="save-and-close-button" tabindex="0" role="menuitem" class="saveform dropdown-item cursor-pointer">Save &amp; Close</button>
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>


                    <div class="mb-3">
                        <h4>Basic Form Information</h4><small class="form-text text-muted">Appears at the top of the
                            form when the client fills it out.</small>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="firstName1">Form Name</label>
                        <input class="form-control" value="" maxlength="255" id="form_name" name="form_name" type="text"
                            placeholder="E.g. Family Law Intake Form">
                    </div>
                    <div class="col-md-12 form-group mb-3">
                        <label for="firstName1">Form Introduction</label>
                        <textarea id="introduction-input" name="form_introduction"
                            placeholder="The purpose of this form is..." rows="3" class="form-control"></textarea>
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
                        <?php
                        $defaultArray=array("1"=>"name","2"=>"email","3"=>"cell_phone","4"=>"address"); 
                        ?>
                        <div style="" id="sortable">
                            <?php 
                            foreach($defaultArray as $k=>$v){?>
                            <div class="field-row p-2 border" id="row{{$k}}">
                                <div class="d-flex flex-row">
                                    <div class="field-row-left">
                                        <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div>
                                    </div>
                                    <div class="field-row-center flex-fill">
                                        <div class="d-flex flex-row">
                                            <div class="col-3">
                                                <div class="select-category-container">
                                                    <select class="category_list form-control" name="category[{{$k}}]"
                                                        onchange="changeCategory({{$k}})" id="category_{{$k}}"
                                                        style="width: 100%;">
                                                        <option value="">Select...</option>
                                                        <option selected="selected" value="contact_field">Contact Field
                                                        </option>
                                                        <option value="unmapped_field">Unmapped Field</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="select-field-container">
                                                    <select class="fields_list form-control country" onchange="changeFields({{$k}})" id="field_{{$k}}"
                                                        name="form_field[{{$k}}]" data-placeholder="Select"
                                                        style="width: 100%;">
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="label-field-container">
                                                    <div class="">
                                                        <input name="user_friendly_label[{{$k}}]"  id="user_friendly_label_{{$k}}" placeholder="" type="text" class="label-input form-control" value="">
                                                    </div>
                                                </div>
                                                <div class="required-checkbox pt-2 text-black-50">
                                                    <label class="d-inline-flex align-items-center">
                                                        <input id="-option" name="requiredCheckbox[{{$k}}]" type="checkbox"
                                                            checked="">
                                                        <span class="ml-2 ">Required</span>
                                                    </label>
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

                            <?php } ?>
                        </div>
                        <div class="d-flex flex-row align-items-center mt-3">
                            <button class="btn btn-outline-secondary m-1 btn-rounded" id="addField" type="button">Add
                                Field</button>
                            <button class="btn btn-outline-secondary m-1 btn-rounded" id="addHeader" type="button">Add
                                Header</button>
                        </div>

                    </div>
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
                                                style="width: 110px; height: 30px; border: 2px solid black;outline: none;">
                                            </button>
                                            <input type="hidden" name="background_color_code" id="background_color_code"
                                                value="F0F0F0">
                                        </div>
                                    </div>
                                    <div class="my-2 undefined">
                                        <div id="button-color-button">
                                            <label class="d-block text-nowrap ">Button Color</label>
                                            <button aria-label="Button Color" id="background-color-button2"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color: rgb(0, 112, 192); outline: none;"></button>
                                            <input type="hidden" name="button_color_code" id="button_color_code"
                                                value="0070C0">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="my-2 mr-4">
                                        <div id="form-font-color-button">
                                            <label class="d-block text-nowrap ">Form Font Color</label>
                                            <button aria-label="Form Font Color" id="background-color-button3"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color: rgb(0, 0, 0); outline: none;"></button>
                                            <input type="hidden" name="form_font_color_code" id="form_font_color_code"
                                                value="000000">

                                        </div>
                                    </div>
                                    <div class="my-2 undefined">
                                        <div id="button-font-color-button">
                                            <label class="d-block text-nowrap ">Button Font Color</label>
                                            <button aria-label="Button Font Color" id="background-color-button4"
                                                type="button"
                                                style="width: 110px; height: 30px; border: 2px solid black; background-color: rgb(255, 255, 255); outline: none;">

                                            </button>
                                            <input type="hidden" name="button_font_color_code"
                                                id="button_font_color_code" value="F7F7F7">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-3 w-50">
                                <div class="m-2 w-75">
                                    <label class="text-nowrap ">Select a Form Font</label>
                                    <div class="Select has-value is-clearable is-searchable Select--single">
                                        <select class="form-control" id="form_font_style" name="form_font_style" style="width: 100%;">
                                            <option value="arial">Arial (Sans Serif)</option>
                                            <option value="times_new_roman">Times New Roman (Serif)</option>
                                            <option selected="selected" value="helvetica">Helvetica (Sans Serif)
                                            </option>
                                            <option value="palatino">Palatino (Serif)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="m-2 w-75">
                                    <label class="text-nowrap ">Select a Button Font</label>
                                    <div class="Select has-value is-clearable is-searchable Select--single">
                                        <select class="form-control" id="form_button_font_style" name="form_button_font_style"
                                            style="width: 100%;">
                                            <option value="arial">Arial (Sans Serif)</option>
                                            <option value="times_new_roman">Times New Roman (Serif)</option>
                                            <option selected="selected" value="helvetica">Helvetica (Sans Serif)
                                            </option>
                                            <option value="palatino">Palatino (Serif)</option>
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
            
            $("#SaveIntakeForm").validate({
                rules: {
                    form_name: {
                        required: true,
                    },
                    'category[]': "required",
                    'form_field[]': "required",
                },
                messages: {
                    form_name: {
                        required: "Please enter a name for your intake form.",
                    },
                    'category[]': "Please select a category.",
                    'form_field[]': "Please select a field.",
                },
            });

            var i = $(".field-row ").length;
            $('#addField').click(function (e) {
                i++;
                $('#sortable').append('<div class="field-row p-2 border" id="row' + i +
                    '"> <div class="d-flex flex-row"> <div class="field-row-left"> <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div></div><div class="field-row-center flex-fill"> <div class="d-flex flex-row"> <div class="col-3"> <div class="select-category-container"> <select class="category_list form-control " onchange="changeCategory('+i+')"  id="category_'+i+'" name="category[' + i +']" style="width: 100%;"> <option value="">Select...</option> <option value="contact_field">Contact Field</option><option value="unmapped_field">Unmapped Field</option> </select> </div></div><div class="col"> <div class="select-field-container"> <select class="fields_list form-control country" onchange="changeFields(' +i + ')" disabled id="field_'+i+'"  name="form_field[' + i +']" style="width: 100%;"> <option value="">Select...</option> </select> </div></div><div class="col"> <div class="label-field-container"> <div class=""> <input disabled id="user_friendly_label_'+i+'" name="user_friendly_label['+i+']" placeholder="" type="text" id="text_'+i+'"  class="label-input form-control" value=""> </div></div><div class="required-checkbox pt-2 text-black-50"> <label class="d-inline-flex align-items-center"> <input id="-option" name="requiredCheckbox[' + i +']" type="checkbox" checked=""> <span class="ml-2 ">Required</span> </label> </div><div id="dyncamic_'+i+'"></div> </div></div><div class="collapse"></div></div><div class="field-row-right flex-shrink-0"> <button type="button" title="Toggle Options" class="collapse-button px-1 mr-2 invisible btn btn-link"> <i class="fas fa-caret-down"></i> </button> <button type="button" title="Delete Field" class="px-1 mr-1 btn btn-link btn_remove" name="remove" id="' +
                    i + '"> <i class="fas fa-trash text-black-50"></i> </button> </div></div></div>'
                    );

            });
            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
                $('#row' + button_id + '').remove();
            });

            $('#addHeader').click(function () {
                i++;
                $('#sortable').append('<div class="field-row p-2 border" id="row' + i +
                    '"> <div class="d-flex flex-row"> <div class="field-row-left"> <div class="p-2 grabcursor" tabindex="0"><i class="fas fa-bars"></i></div></div><div class="field-row-center flex-fill"> <div class="d-flex flex-row"> <div class="col"> <div class="label-field-container"> <div class=""> <input  name="category['+i+']" placeholder="" type="text" id="header_' +
                    i +
                    '"   class="label-input form-control" value=""> </div></div><div class="collapse"></div></div><div class="field-row-right flex-shrink-0"> <button type="button" title="Toggle Options" class="collapse-button px-1 mr-2 invisible btn btn-link"> <i class="fas fa-caret-down"></i> </button> <button type="button" title="Delete Field" class="px-1 mr-1 btn btn-link btn_remove" name="remove" id="' +
                    i + '"> <i class="fas fa-trash text-black-50"></i> </button> </div></div></div>'
                    );
            });
            $(document).on('click', '.btn_remove', function () {
                var button_id = $(this).attr("id");
                $('#row' + button_id + '').remove();
            });
            $("#background-color-button1").ColorPicker({
                color: '#F0F0F0',
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


            $('#SaveIntakeForm').submit(function (e) {
                $('.category_list').each(function() {
                    $(this).rules("add",{ required: true, messages: { required: "Please select a category."} });
                });
                $('.fields_list').each(function() {
                    $(this).rules("add",{required: true,messages: { required: "Please select a field."} });
                });   
                $('.unmappted_required').each(function() {
                    $(this).rules("add",{required: true,messages: { required: "Label cannot be blank."} });
                });   
                
            });
            $("#SaveIntakeForm").validate();
        });

        function changeCategory(currentRow) {
            var array = [];
            $('.fields_list').each(function() {
                var curid= $(this).attr('id');
                var selectedOption = $("#"+curid +" option:selected").val();
                array.push(selectedOption);
              });  

            var selectedOption = $("#category_" + currentRow + " option:selected").val();
            if (selectedOption == "contact_field" || selectedOption == "unmapped_field") {
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
            var array = [];
            $('.fields_list').each(function() {
                var curid= $(this).attr('id');
                var selectedOption = $("#"+curid +" option:selected").val();
                array.push(selectedOption);
              });  

            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/loadFieldsSelected",
                data: {
                    "typpe": 'contact_field',
                    "selectedRow": selectedRow,
                    "alreadySelected" : JSON.stringify(array)
                },
                success: function (res) {
                    $("#field_" + currentRow).removeAttr('disabled');
                    $("#user_friendly_label_" + currentRow).removeAttr('disabled');
                    $("#field_" + currentRow).html(res);
                }
            })
        }

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
            $('#SaveIntakeForm').submit();
        }
        $('#preview-form-button').on('click', function(){
            $('.category_list').each(function() {
                $(this).rules("add",{ required: true, messages: { required: "Please select a category."} });
            });
            $('.fields_list').each(function() {
                $(this).rules("add",{required: true,messages: { required: "Please select a field."} });
            });   
            $('.unmappted_required').each(function() {
                $(this).rules("add",{required: true,messages: { required: "Label cannot be blank."} });
            });   
                
            $("#SaveIntakeForm").validate();

            if (!$('#SaveIntakeForm').valid()) {
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
            dataString = $("#SaveIntakeForm").serialize();
           
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/saveTempIntakeForm", // json datasource
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
                        var x=window.open();
                        x.document.open();
                        x.document.write(res.html);
                        x.document.close();
                    }
                },
                complete: function() {
                    me.data('requestRunning', false);
                }
            });

        });

        $('#SaveIntakeForm').submit(function (e) {
           
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
           
            if (!$('#SaveIntakeForm').valid()) {
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
            dataString = $("#SaveIntakeForm").serialize();
           
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/saveIntakeForm", // json datasource
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
                        window.location.href=res.url;
                    }
                },
                complete: function() {
                    me.data('requestRunning', false);
                }
            });
        });
        <?php foreach($defaultArray as $k => $v) { ?>
                changeCategorySelected("{{$k}}", "{{$v}}"); 
            <?php } ?>
        $(".dropdown-toggle").trigger("click");
    </script>
    @stop
