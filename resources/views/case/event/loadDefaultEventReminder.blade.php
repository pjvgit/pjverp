@if(count($UserPreferanceReminder) > 0)
@foreach($UserPreferanceReminder as $rk =>$rv)
<div class="form-group fieldGroup">
    <div class="">
        <div class="d-flex col-10 pl-0 align-items-center">
            <div class="pl-0 col-3">
                <div>
                    <div class="">
                        <select id="reminder_user_type" onchange="chngeTy(this)" name="reminder_user_type[]" class="reminder_user_type form-control custom-select  ">
                            @forelse (reminderUserType() as $key => $item)
                            <option value="{{ $key }}" >{{ $item }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <div class="pl-0 col-3">
                <div>
                    <div class="">
                        <select id="reminder_type" name="reminder_type[]"
                            class="reminder_type form-control custom-select  ">
                            @foreach(getEventReminderTpe() as $k =>$v)
                                <option value="{{$k}}" {{ ($rv->reminder_type == strtolower($k)) ? 'selected' : '' }}>{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div><input name="reminder_number[]" type="number" min="0" class="form-control col-2 reminder-number" type="number" value="{{$rv->reminer_number}}" id="reminder_number">
            <div class="col-4">
                <div>
                    <div class="">
                        <select id="reminder_time_unit" name="reminder_time_unit[]"
                            class="form-control custom-select  ">
                            <option value="minute" {{ ($rv->reminder_frequncy == 'minute') ? 'selected' : '' }}>minutes</option>
                            <option value="hour" {{ ($rv->reminder_frequncy == 'hour') ? 'selected' : '' }}>hours</option>
                            <option value="day" {{ ($rv->reminder_frequncy == 'day') ? 'selected' : '' }}>days</option>
                            <option value="week" {{ ($rv->reminder_frequncy == 'week') ? 'selected' : '' }}>weeks</option>
                        </select>
                    </div>
                </div>
            </div>
            <button class="btn remove" type="button">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
@endforeach
@endif