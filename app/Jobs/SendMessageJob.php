<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\User;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request, $id, $messageID, $senderName, $firmData, $getTemplateData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $id, $messageID, $senderName, $firmData, $getTemplateData)
    {
        $this->request = $request;
        $this->id = $id;
        $this->messageID = $messageID;
        $this->senderName = $senderName;
        $this->firmData = $firmData;
        $this->getTemplateData = $getTemplateData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {       
        Log::info("Send Meassage Email job calling..." . date("Y-m-d H:i:s"));
        $clientData=User::find($this->id);        
        if(isset($clientData->email)){
            
            $mail_body = $this->getTemplateData->content;
            $mail_body = str_replace('{sender}', $this->senderName, $mail_body);
            $mail_body = str_replace('{subject}', $this->request['subject'], $mail_body);
            $mail_body = str_replace('{loginurl}', BASE_URL.'login', $mail_body);
            $mail_body = str_replace('{url}', BASE_URL.'messages/'.$this->messageID.'/info', $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $this->firmData->firm_name, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        
            $user = [
                "from" => FROM_EMAIL,
                // "from_title" => FROM_EMAIL_TITLE,
                "from_title" => $this->firmData->firm_name,
                "replyto"=>DO_NOT_REPLAY_FROM_EMAIL,
                "replyto_title"=>DO_NOT_REPLAY_FROM_EMAIL_TITLE,
                "subject" => "You have a new message from ".$this->firmData->firm_name,
                "to" => $clientData->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];            
            $sendEmail = $this->sendMail($user);
            \Log::info("sendMailGlobal > email > ".$clientData->email. ' > sendMail > '.$sendEmail.' > at '.date("Y-m-d H:i:s"));
        }        
    }

    public function sendMail($user){
        try{
            Mail::send('emails.reminder', ['user' => $user], function ($m) use ($user) {
                $m->from($user['from'], $user['from_title']);
                if(isset($user['replyto'])){
                    $m->replyTo($user['replyto'], $user['replyto_title']);
                }
                
                $m->to($user['to'],$user['full_name'])->subject($user['subject']);
            });
            
            if( count(Mail::failures()) > 0 ) {
                foreach(Mail::failures() as $email_address) {
                    \Log::info("failed email:". $email_address);
                    return 0;
                }
            } else {
                return 1;
                \Log::info("email sent");
            }
        }
        catch(\Exception $e){
            \Log::info("mail sent failed:". $e->getMessage());
            return 0;
        }
    }
}
