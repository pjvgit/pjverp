<?php
 
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait EventTrait {
    /**
     * Get event reminders json
     */
    function getEventReminderJson($caseEvent, $request)
    {
        $eventReminders = [];
        $authUserId = auth()->id();
        if($request->reminder_user_type && count($request['reminder_user_type']) > 1) {
            for($i=0; $i < count($request['reminder_user_type'])-1; $i++) {
                $eventReminders[] = [
                    'event_id' => $caseEvent->id,
                    'reminder_type' => $request['reminder_type'][$i],
                    'reminer_number' => $request['reminder_number'][$i],
                    'reminder_frequncy' => $request['reminder_time_unit'][$i],
                    'reminder_user_type' => $request['reminder_user_type'][$i],
                    'created_by' => $authUserId,
                    'remind_at' => Carbon::now(),
                ];
            }
        }
        return encodeDecodeJson($eventReminders, 'encode');
    }
}
 