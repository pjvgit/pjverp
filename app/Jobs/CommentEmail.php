<?php

namespace App\Jobs;

use App\User,DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\CaseEvent,App\CaseEventLinkedContactLead,App\Firm,App\EmailTemplate,App\CaseEventComment;
class CommentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event_id,$firm,$CaseEventComment,$fromUser;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct($event_id,$firm,$CaseEventComment,$fromUser)
    {
        $this->event_id = $event_id;
        $this->firm = $firm;
        $this->CaseEventComment=$CaseEventComment;   
        $this->fromUser=$fromUser;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        $CommonController= new \App\Http\Controllers\CommonController();
        $BaseController= new \App\Http\Controllers\BaseController();
        $eventData=CaseEvent::find($this->event_id);
        $CaseEventLinkedContactLead=CaseEventLinkedContactLead::where("event_id",$this->event_id)->get();
        $fromUserData=User::find($this->fromUser);

        $CaseEventComment=CaseEventComment::find($this->CaseEventComment);
        foreach($CaseEventLinkedContactLead as $k=>$v){
            $firmData=Firm::find($this->firm); 
            if($v->lead_id!=NULL){
                $findUSer=User::find($v->lead_id);
            }else{
                $findUSer=User::find($v->contact_id);
            }   
            $getTemplateData = EmailTemplate::find(22);
            $email=$findUSer['email'];
            $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
            $sender=$fromUserData['first_name']." ".$fromUserData['last_name'];

            
            $timezone=$findUSer->user_timezone;
            $convertedDate=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($eventData->start_date ." " .$eventData->start_time)),$timezone);
            $Edates=date('m-d-Y h:i A',strtotime($convertedDate));


            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{receiver}', $fullName,$mail_body);
            $mail_body = str_replace('{sender}', $sender,$mail_body);
            $mail_body = str_replace('{event_name}', $eventData->event_title,$mail_body);
            $mail_body = str_replace('{date_time}', $Edates ,$mail_body);
            $mail_body = str_replace('{comment}', $CaseEventComment->comment,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
            $mail_body = str_replace('{regards}', $firmData['firm_name'], $mail_body);  
            $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{url}', QUEUE_BASE_URL."login", $mail_body);  

            $userEmail = [
                "from" => FROM_EMAIL,
                "from_title" => $firmData['firm_name'],
                "subject" => $getTemplateData->subject,
                "to" => 'testing.testuser6@gmail.com',
                "full_name" => $fullName,
                "mail_body" => $mail_body
                ];
            $sendEmail = $BaseController->sendMail($userEmail);
        }
    }
}