@extends('layouts.pdflayout')
<?php
    $CommonController= new App\Http\Controllers\CommonController();
    $filledData=json_decode($alreadyFilldedData['form_value']);
?>
<div class="main-content">
    <h4 class="heading mb-3 test-firm-name">{{$firmData['firm_name']}}</h4>
    <h3 class="font-weight-bold heading mb-3 test-form-name">{{$intakeForm['form_name']}}</h3>
    @foreach($intakeFormFields as $k=>$v)
        @if($v->form_field=="name")
            @if(isset($filledData->first_name) || isset($filledData->last_name) || isset($filledData->middle_name))
            <div><b> Name</b></div>
            <div> {{$filledData->first_name}} {{$filledData->middle_name}} {{$filledData->last_name}}</div>
            @endif
        @elseif($v->form_field=="email")
            @if(isset($filledData->email))
            <div><br></div>
            <div><b> Email</b></div>
            <div> {{$filledData->email}} </div>
            @endif
        @elseif($v->form_field=="home_phone")
            @if(isset($filledData->home_phone))
            <div><br></div>
            <div><b> Home Phone</b></div>
            <div> {{$filledData->home_phone}} </div>
            @endif
        @elseif($v->form_field=="cell_phone")
            @if(isset($filledData->cell_phone))
            <div><br></div>
            <div><b> Cell Phone</b></div>
            <div> {{$filledData->cell_phone}} </div>
            @endif
        @elseif($v->form_field=="work_phone")
            @if(isset($filledData->work_phone))
            <div><br></div>
            <div><b> Work Phone</b></div>
            <div> {{$filledData->work_phone}} </div>
            @endif
        @elseif($v->form_field=="address")
            @if(isset($filledData->address1) || isset($filledData->address2) || isset($filledData->city) || isset($filledData->state) || isset($filledData->postal))
            <div><br></div>
            <div><b> Address</b></div>
            <div> {{$filledData->address1}} {{$filledData->address2}} {{$filledData->city}} {{$filledData->state}} {{$filledData->postal}}
            @foreach($country as $c=>$v)
                @if($v->id==$filledData->country)
                    {{$v->name}};
                @endif
            @endforeach
            </div>
            @endif
        @elseif($v->form_field=="birthday")
            @if(isset($filledData->birthday))
            <div><br></div>
            <div><b> Birthday</b></div>
            <div> {{$filledData->birthday}} </div>
            @endif
        @elseif($v->form_field=="driver_license")
            @if(isset($filledData->driver_license_number) || isset($filledData->driver_license_state))
            <div><br></div>
            <div><b> Driver license</b></div>
            <div> {{$filledData->driver_license_number}} {{$filledData->driver_license_state}}</div>
            @endif
        @elseif($v->form_field=="short_text")
            @if(isset($filledData->sort_text))
                <div><br></div>
                <div><b> {{($v->client_friendly_lable)??'Short Text'}}</b></div>
                <div> {{$filledData->sort_text}} </div>
            @endif
        @elseif($v->form_field=="long_text")
            @if(isset($filledData->long_text))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Long Text'}}</b></div>
            <div> {{$filledData->long_text}} </div>
            @endif
        @elseif($v->form_field=="yesno")
            @if(isset($filledData->yesno))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Yes/No'}}</b></div>
            <div> {{$filledData->yesno}} </div>
            @endif
        @elseif($v->form_field=="number")
            @if(isset($filledData->number))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Number'}}</b></div>
            <div> {{$filledData->number}} </div>
            @endif
        @elseif($v->form_field=="currency")
            @if(isset($filledData->currency))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Currency'}}</b></div>
            <div> {{$filledData->currency}} </div>
            @endif
        @elseif($v->form_field=="date")
            @if(isset($filledData->date))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Date'}}</b></div>
            <div> {{$filledData->date}} </div>
            @endif
        @elseif($v->form_field=="multiple_choice")
            @if(isset($filledData->multiple_choice))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Multiple Choice'}}</b></div>
            <div> {{$filledData->multiple_choice}} </div>
            @endif
        @elseif($v->form_field=="checkboxes")
            @if(isset($filledData->checkboxes))
            <div><br></div>
            <div><b> {{($v->client_friendly_lable)??'Checkboxes'}}</b></div>
            <?php  $options=json_decode($v->extra_value);
                foreach($options as $kkey=>$kVal){ ?>
                <label class="form-check-label">
                     <?php if(isset($filledData->checkboxes) && in_array($kVal,$filledData->checkboxes)) { echo $kVal; } ?>
                </label>
            <?php } ?>
            @endif
        @endif
    @endforeach
</div>
