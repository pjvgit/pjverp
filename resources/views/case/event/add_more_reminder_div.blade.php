<div class="fieldGroupEventReminderCopy copy hide add_more_reminder_div" style="display: none;">
    <div class="">
        <div class="d-flex col-10 pl-0 align-items-center">
            <div class="pl-0 col-3">
                <div>
                    <div class="">
                        <select id="reminder_user_type" name="reminder_user_type[]"
                            class="form-control custom-select  "  onchange="changeEventReminderUserType(this)">
                            @forelse (reminderUserType() as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
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
                            class="form-control custom-select  ">
                            @foreach(getEventReminderTpe() as $k =>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div><input name="reminder_number[]" type="number" min="0" class="form-control col-2 reminder-number" value="1">
            <div class="col-4">
                <div>
                    <div class="">
                        <select id="reminder_time_unit" name="reminder_time_unit[]"
                            class="form-control custom-select  ">
                            <option value="minute">minutes</option>
                            <option value="hour">hours</option>
                            <option value="day">days</option>
                            <option value="week">weeks</option>
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