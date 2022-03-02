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

class ShareInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected  $id, $email, $token, $message, $firmData, $getTemplateData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $email, $token, $message, $firmData, $getTemplateData)
    {
        $this->id = $id;
        $this->email = $email;
        $this->firmData = $firmData;
        $this->token = $token;
        $this->message = $message;
        $this->getTemplateData = $getTemplateData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {       
        Log::info("Share Invoice Email job calling..." . date("Y-m-d H:i:s"));
        if(isset($this->email) && $this->email != ''){
            $mail_body = $this->getTemplateData->content;
            $mail_body = str_replace('{message}', $this->message, $mail_body);
            $mail_body = str_replace('{token}', $this->token, $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $this->firmData->firm_name, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            
            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $this->firmData->firm_name." has sent you an invoice",
                "to" => $this->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            $sendEmail = $this->sendMail($user);
            \Log::info("ShareInvoiceJob > email > ".$this->email. ' > sendMail > '.$sendEmail.' > at '.date("Y-m-d H:i:s"));
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
