<?php if (!$TaskReminders->isEmpty()) {
    foreach ($TaskReminders as $kr => $kv) {?>
        <div>
            <div><strong>{{ucfirst($kv->reminder_user_type)}}</strong> - {{ucfirst($kv->reminder_type)}} {{$kv->reminer_number}} day before due date.</div>
        </div>
    <?php }
    } else { ?>
        <div class="mb-3"><span class="text-black-50">None</span></div>
    <?php } ?>