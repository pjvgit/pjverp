@if(count($UserPreferanceReminder) > 0)
@foreach($UserPreferanceReminder as $rk =>$rv)
<div class="form-group task-fieldGroup">
    <div class="">
        <div class="d-flex col-12 pl-0 align-items-center">
            <div class="pl-0 col-2">
                <div>
                    <div class="">
                        <select id="reminder_user_type" onchange="chngeTy(this)" name="reminder_user_type[]" class="reminder_user_type form-control custom-select  ">
                            @forelse (reminderUserType() as $key => $item)
                            @if($key != 'client-lead')
                            <option value="{{ $key }}">{{ $item }}</option>
                            @endif
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <div class="pl-0 col-2">
                <div>
                    <div class="">
                        <select id="reminder_type" name="reminder_type[]"
                            class="reminder_type form-control custom-select  ">
                            @foreach(getEventReminderTpe() as $k =>$v)
                                <option value="{{$k}}" {{ ($rv->reminder_type == strtolower($key)) ? 'selected' : '' }} >{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <input name="reminder_number[]" type="number" min="0" class="form-control col-2 reminder-number valid" value="{{$rv->reminer_number}}" aria-invalid="false">
            <div class="col-3">
                <div>
                    <div class="">
                        <select id="reminder_time_unit" name="reminder_time_unit[]"
                            class="form-control custom-select  ">
                            <option value="day" {{ ($rv->reminder_frequncy == 'day') ? 'selected' : '' }}>days</option>
                            <option value="week" {{ ($rv->reminder_frequncy == 'week') ? 'selected' : '' }}>weeks</option>
                        </select>
                    </div>
                </div>
            </div>
            before task &nbsp;
            <button class="btn remove" type="button">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
@endforeach
@endif