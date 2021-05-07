@extends('layouts.pdflayout')
<?php
    $CommonController= new App\Http\Controllers\CommonController();
    $filledData=json_decode($alreadyFilldedData['form_value']); 
    // print_r($filledData);exit;
?>
<div class="main-content">
    <h4 class="heading mb-3 test-firm-name">{{$firmData['firm_name']}}</h4>
    <h3 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm['form_name']}}</h3>
    <p class=" heading mb-3 test-form-name">{{$intakeForm['form_introduction']}}</p>

    @foreach($intakeFormFields as $k=>$v)
        @if($v->form_field=="name")
            <div><b> {{($v->client_friendly_lable)??'Name'}}</b></div>
            <div> {{$filledData->first_name}} {{$filledData->middle_name}} {{$filledData->last_name}}</div>
        @elseif($v->form_field=="email")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Email'}}</b></div>
            <div> {{$filledData->email}} </div>
        @elseif($v->form_field=="home_phone")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Home Phone'}}</b></div>
            <div> {{$filledData->home_phone}} </div>
        @elseif($v->form_field=="cell_phone")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Cell Phone'}}</b></div>
            <div> {{$filledData->cell_phone}} </div>
        @elseif($v->form_field=="work_phone")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Work Phone'}}</b></div>
            <div> {{$filledData->work_phone}} </div>
        @elseif($v->form_field=="address")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Address'}}</b></div>
            <div> {{$filledData->address1}} {{$filledData->address2}} {{$filledData->city}} {{$filledData->state}} {{$filledData->postal}}
            @foreach($country as $c=>$v)
                @if($v->id==$filledData->country)
                    {{$v->name}};
                @endif
            @endforeach
            </div>
        @elseif($v->form_field=="birthday")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Birthday'}}</b></div>
            <div> {{$filledData->birthday}} </div>
        @elseif($v->form_field=="driver_license")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Driver license'}}</b></div>
            <div> {{$filledData->driver_license_number}} {{$filledData->driver_license_state}}</div>

        @elseif($v->form_field=="sort_text")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Sort Text'}}</b></div>
            <div> {{$filledData->sort_text}} </div>
        @elseif($v->form_field=="long_text")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Long Text'}}</b></div>
            <div> {{$filledData->long_text}} </div>
        @elseif($v->form_field=="yesno")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Yes/No'}}</b></div>
            <div> {{($filledData->yesno)??''}} </div>
        @elseif($v->form_field=="number")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Number'}}</b></div>
            <div> {{($filledData->number)??''}} </div>
        @elseif($v->form_field=="currency")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Currency'}}</b></div>
            <div> {{$filledData->currency}} </div>
        @elseif($v->form_field=="date")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Date'}}</b></div>
            <div> {{$filledData->date}} </div>
        @elseif($v->form_field=="multiple_choice")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Multiple Choice'}}</b></div>
            <div> {{$filledData->multiple_choice}} </div>
        @elseif($v->form_field=="checkboxes")
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Checkboxes'}}</b></div>
            <?php  $options=json_decode($v->extra_value);
                foreach($options as $kkey=>$kVal){ ?>
                <label class="form-check-label">
                     <?php if(isset($filledData->checkboxes) && in_array($kVal,$filledData->checkboxes)) { echo $kVal; } ?>
                </label>
            <?php } ?>
        @endif
    @endforeach
</div>
