<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div data-testid="retainer-request-modal">
    <form class="sendReminderForm" id="sendReminderForm" name="sendReminderForm" method="POST">
        <input class="form-control" value="{{$invoice_id}}" id="invoice_id" maxlength="250" name="invoice_id" type="hidden">
        @csrf
        <div class="modal-body retainer-requests-popup-content">
            <div class="alert alert-info" role="alert">Automated Reminders {{ ($invoice->automated_reminder == "yes") ? "On" : "Off" }}
                <div style="display: inline; position: relative; top: -1px; left: 0px; padding: 0px 0.5rem;">
                    @php
                        $content = '';
                        if($invoice->automated_reminder == "no") {
                            $content = "<br><br> To turn on/off Automated Reminders, from the invoice screen select edit and toggle the Automated Reminders setting.<br><br>Note: Automated reminders will be sent based on the next installment date. If Automatic Payment is On, reminders will show automatic payment status. ";
                        }
                    @endphp     
                    <span class="tooltip-wrapper" style="position: relative;"><span>
                        <span data-toggle="tooltip" data-placement="top" title="{{ (isset($invoice->invoice_setting) && $invoice->invoice_setting['reminder']) ? $invoice->getReminderMessage().$content : '' }}" data-html="true">
                            <i class="pl-1 fas fa-question-circle fa-lg"></i></span>
                        </span>
                    </span>
                </div>
                @if($invoice->automated_reminder == "yes" && $invoice->due_date)
                    @php
                        $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
                        if (!empty($invoice->invoiceFirstInstallment)) {
                            $dueDate = $invoice->invoiceFirstInstallment->due_date;
                        } else if($invoice->due_date) {
                            $dueDate = $invoice->due_date;
                        } else {
                            $dueDate = "";
                        }
                        $remindDate = ""; $emailType = "";
                        if($dueDate) {
                            $currentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
                            $dueDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate);
                            /* if($dueDate->eq($currentDate)) {
                                $remindDate = $currentDate;
                                $emailType = "present";
                            } else if($dueDate->gt($currentDate)) {
                                $remindDate = $dueDate->subDays(7);
                                $emailType = "future";
                            } else if($dueDate->lt($currentDate)) {
                                $remindDate = $dueDate->addDays(7);
                                $emailType = "past";
                            } else {
                                $remindDate = "";
                                $emailType = "";
                            } */

                            $remindSetting = collect($invoice->invoice_setting['reminder'] ?? []);
                            if($dueDate->eq($currentDate)) { // For present due date
                                $onDue = $remindSetting->where("remind_type", "on the due date")->where('is_reminded', 'no');
                                if($onDue->count()) {
                                    $remindDate = $currentDate;
                                    $emailType = "present";
                                }
                            } else if($dueDate->gt($currentDate)) { // For future due date
                                $dueIn = $remindSetting->where("remind_type", 'due in')->where('is_reminded', 'no');
                                if($dueIn->count()) {
                                    $dueIn = $dueIn->sortByDesc('days')->first();
                                    $remindDate = $dueDate->subDays($dueIn['days']);
                                    $emailType = "future";
                                }
                            } else if($dueDate->lt($currentDate)) { // For past due date
                                $overDue = $remindSetting->where("remind_type", 'overdue by')->where('is_reminded', 'no');
                                if($overDue->count()) {
                                    $overDue = $overDue->sortBy('days')->first();
                                    $remindDate = $dueDate->addDays($overDue['days']);
                                    $emailType = "past";
                                }
                            } else {
                                $remindDate = "";
                                $emailType = "";
                            }
                        }
                    @endphp
                    @if($remindDate)
                    Next automated reminder: {{ $remindDate->format("M d, Y") }}
                    @endif
                @endif
                <input type="hidden" name="email_type" value="{{ $emailType ?? "" }}" >
            </div>
            <span class="showError"></span>

            <div class="row">
                <div class="col-12">
                    <p class="helper-text">Send an email reminder to: </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="reminders" style="width: 100%;">
                        <thead>
                            <tr class="payable-reminder-header">
                                <th>Contact Name</th>
                                <th>Reminders Sent</th>
                                <th>Last Reminder Sent</th>
                                <th>Viewed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($getAllClientForSharing as $k=>$v){?>
                            <tr class="payable-reminder-row">
                                <td class="user-checkbox">
                                    <input type="checkbox" class="mr-2 mb-1" id="send-reminder-21214949"
                                        name="client[]" value="{{$v->user_id}}">
                                    <span class="payable-reminders-user-name">{{substr($v->unm,0,100)}}
                                   <?php if($v->user_level==2){ echo "(Client)"; }else{ echo "(Company)"; } ?></span></td>
                                <td class="total-num-reminders-sent">{{($v->reminder_sent_counter)??0}}</td>
                                <td class="last_reminder_sent_time">
                                    <?php
                                    if($v->last_reminder_sent_on!=NULL){
                                        $cDate=$CommonController->convertUTCToUserTime($v->last_reminder_sent_on,Auth::User()->user_timezone);
                                            echo date('d/m/Y h:i A',strtotime($cDate));
                                    }else{ echo "Never"; } 
                                    ?></td>
                                <td class="has_client_viewed_on">
                                    <?php
                                    if($v->is_viewed=='no'){
                                        echo "Never";
                                    }else{
                                        echo "Yes";
                                    }?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class="modal-footer  pt-2 pb-2 send-email-form-footer">
                <div class="mr-auto">
                    <a href="#" target="_blank" rel="noopener noreferrer">What does my client see?</a>
                </div>
               
                <div class="col-md-2 form-group mb-3">
                    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                    </div>
                </div>
                    <a href="#">
                        <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button class="btn btn-primary m-1 submit" id="submitButton" type="submit">Send Reminder</button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .payable-reminder-header {
        height: 45px;
    }

    table.reminders th {
        background-color: #dedede;
        padding: 6px 5px;
        color: #000;
        font-weight: 700;
        border: 1px solid #dbdbdb;
        text-align: left;
        overflow: hidden;
        font-size: 13px;
    }

    .payable-reminder-row {
        border-left: 1px solid#dbdbdb;
        border-right: 1px solid#dbdbdb;
    }

    table.reminders td {
        border-bottom: 1px solid #dbdbdb;
        vertical-align: top;
        padding: 10px 5px;
        font-size: 12px;
        overflow: hidden;
    }

</style>

<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $('[data-toggle="tooltip"]').tooltip();

        $('.showError').html('');
        $('#sendReminderForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#sendReminderForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#sendReminderForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/saveSendReminder", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
    });

</script>
