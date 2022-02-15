<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Rules\MatchOldPassword;
use App\Rules\UniqueEmail;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Messages,App\ReplyMessages;
use DB,Validator,Session,Mail,Storage,Image;
// use Datatables;
use Yajra\Datatables\Datatables;

class ClientdashboardController extends Controller 
{
    public function messages(Request $request){
        
        $messages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*', "messages.updated_at as last_post", DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as sender_name'),"case_master.case_title");
        $messages = $messages->where("messages.user_id",'like', '%'.Auth::User()->id.'%');
        $messages = $messages->where("messages.firm_id",Auth::User()->firm_name);
        $messages = $messages->orderBy("messages.updated_at", 'desc');
        $messages = $messages->get();

        return view("client_portal.messages.index",compact('messages', 'request'));            
    }
    public function messageInfo(Request $request){
        
        $messagesData = Messages::leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*',DB::raw("DATE_FORMAT(messages.updated_at,'%d %M %H:%i %p') as last_post"),"case_master.case_title","case_master.case_unique_number")
        ->where('messages.id', $request->id)
        ->first();
        if(!empty($messagesData)){
        $count = 0;
        if($messagesData->created_by == Auth::User()->id){
            $count++;
        }
        if($messagesData->user_id == Auth::User()->id){
            $count++;
        }
        if($count == 0){
            abort(404);
        }
  
        $messageList = ReplyMessages::leftJoin("messages","reply_messages.message_id","=","messages.id")
        ->select('reply_messages.*')
        ->where('reply_messages.message_id', $request->id)
        ->get();
    
        $clientList = [];    
        $userlist = explode(',', $messagesData->user_id);
        foreach ($userlist as $key => $value) {
            $userInfo =  User::where('id',$value)->select('first_name','last_name','user_level')->first();
            $clientList[$value] =  strtolower($userInfo['first_name'].' '.$userInfo['last_name']);
        }

        // return view('communications.messages.viewMessage',compact('messagesData','messageList','clientList'));            
        return view("client_portal.messages.viewMessage",compact('messagesData','messageList','clientList'));            
        }else{
            abort(404);
        }
    }
    
    public function sendMailGlobal($request,$id, $messageID)
    {
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(11);
        $mail_body = $getTemplateData->content;
        $senderName=Auth::User()->first_name." ".Auth::User()->last_name;
        $mail_body = str_replace('{sender}', $senderName, $mail_body);
        $mail_body = str_replace('{subject}', $request['subject'], $mail_body);
        $mail_body = str_replace('{loginurl}', BASE_URL.'login', $mail_body);
        $mail_body = str_replace('{url}', BASE_URL.'messages/'.$messageID.'/info', $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        $clientData=User::find($id);
        if(isset($clientData->email)){
            $user = [
                "from" => FROM_EMAIL,
                // "from_title" => FROM_EMAIL_TITLE,
                "from_title" => $firmData->firm_name,
                "replyto"=>DO_NOT_REPLAY_FROM_EMAIL,
                "replyto_title"=>DO_NOT_REPLAY_FROM_EMAIL_TITLE,
                "subject" => "You have a new message on ".$firmData->firm_name,
                "to" => $clientData->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];            
            $sendEmail = $this->sendMail($user);
        }
        return true;
    }
}