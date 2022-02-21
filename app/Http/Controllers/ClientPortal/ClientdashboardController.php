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
use App\Messages,App\ReplyMessages,App\Firm,App\EmailTemplate,App\CaseMaster;
use DB,Validator,Session,Mail,Storage,Image;
// use Datatables;
use Yajra\Datatables\Datatables;

class ClientdashboardController extends Controller 
{
    public function messages(Request $request){
        Messages::where('created_by',Auth::user()->id)->whereNull('subject')->whereNull('user_id')->whereNull('message')->forceDelete();

        $messages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*', "messages.updated_at as last_post", DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as sender_name'),"case_master.case_title");
        // $messages = $messages->where("messages.user_id",'like', '%'.Auth::User()->id.'%');
        $messages = $messages->where("messages.firm_id",Auth::User()->firm_name);
        $messages = $messages->whereNull("case_master.deleted_at");
        $messages = $messages->where(function($messages){
            $messages = $messages->orWhere("messages.user_id",'like', '%'.Auth::User()->id.'%');
            $messages = $messages->orWhere("messages.created_by",Auth::user()->id);
        });
        if($request->folder == 'archived'){
            $messages = $messages->where("messages.is_archive",1);
        }else if($request->folder == 'draft'){
            $messages = $messages->where("messages.is_draft",1);
        }else if($request->folder == 'sent'){
            $messages = $messages->where("messages.is_sent",1);
        }else{
            $messages = $messages->where("messages.is_archive",0);
            $messages = $messages->where("messages.is_draft",0);
        }
        

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
            $userlist = explode(',', $messagesData->user_id);
            $count = 0;
            if($messagesData->created_by == Auth::User()->id){
                $count++;
            }
            if($messagesData->user_id == Auth::User()->id){
                $count++;
            }
            if(in_array(Auth::User()->id, $userlist)){
                $count++;
            }
            if($count == 0){
                abort(404);
            }

            // read mesages 
            $messagesData->is_read = 0;
            $messagesData->save();
    
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
                    return 0;
                }
            } else {
                return 1;
            }
        }
        catch(\Exception $e){
            return 0;
        }
    }

    public function archiveMessageToUserCase(Request $request){
        $Messages=Messages::find($request->message_id);
        $Messages->is_archive = 1;
        $Messages->save();
        session(['popup_success' => 'Message was archived']);
        return response()->json(['errors'=>'']);
    }

    public function unarchiveMessageToUserCase(Request $request){
        $Messages=Messages::find($request->message_id);
        $Messages->is_archive = 0;
        $Messages->save();
        session(['popup_success' => 'Message was unarchived']);
        return response()->json(['errors'=>'']);
    }


    public function replyMessageToUserCase(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'selected_case_id' => ($request->is_global_for == 'client') ? 'required' : '',
            'selected_user_id' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $ReplyMessages=new ReplyMessages;
            $ReplyMessages->message_id=$request->message_id;
            $ReplyMessages->reply_message=$request->delta;
            $ReplyMessages->created_by = Auth::User()->id;
            $ReplyMessages->save();

            $Messages=Messages::find($request->message_id);
            $Messages->message=substr($request->delta,0,50);
            $Messages->save();
            
            $this->sendMailGlobal($request, $request->selected_user_id, $request->message_id);

            session(['popup_success' => 'Your message has been sent']);
            return response()->json(['errors'=>'']);
            exit;       
        }           
    }

    public function addMessagePopup(){
        $firmCases = $firmOwner = [];
        
        $Messages = Messages::where('user_id',Auth::user()->id)->whereNull('subject')->first();
        if(empty($Messages)){
            $Messages=new Messages;
            $Messages->user_id = Null;
            $Messages->case_id=NUll;
            $Messages->is_read=0;
            $Messages->is_draft=1;
            $Messages->for_staff='yes';
            $Messages->firm_id = Auth::User()->firm_name;
            $Messages->created_by = Auth::User()->id;
            $Messages->save();
        }      
        
        $firmOwner = User::find(Auth::User()->parent_user);
        
        return view("client_portal.messages.addMessage",compact('Messages','firmOwner'));                
    }

    public function sendOrDraftMessage(Request $request){
        $redirect = 'no';
        $Messages= Messages::find($request->message_id);
        $Messages->case_id=$request->case ?? NUll;
        $Messages->user_id=$request->send_to[0] ?? NUll;
        $Messages->subject=$request->subject ?? NUll;
        $Messages->message=$request->msg ?? NUll;
        $Messages->created_by = Auth::User()->id;
        if($request->action != ''){
            $Messages->is_sent=1;
            $Messages->is_read=0;
            $Messages->is_draft=0;
            $redirect = 'yes';
        }
        $Messages->save();

        if($request->action != ''){
            $ReplyMessages=new ReplyMessages;
            $ReplyMessages->message_id=$request->message_id;
            $ReplyMessages->reply_message=$request->msg;
            $ReplyMessages->created_by = Auth::User()->id;
            $ReplyMessages->save();

            $this->sendMailGlobal($request, $request->send_to[0], $request->message_id);
        }
        return response()->json(['redirect'=>$redirect]);
    }

    public function discardDraftMessage(Request $request){
        Messages::where('id', $request->id)->forceDelete();
    }

    public function openDraftMessage(Request $request){
        $Messages = Messages::where('id', $request->id)->first();
        $firmOwner = User::find(Auth::User()->parent_user);
        $firmCases = CaseMaster::where('firm_id', Auth::User()->firm_name)->get();

        return view("client_portal.messages.addMessage",compact('Messages','firmCases','firmOwner'));                
    }
}