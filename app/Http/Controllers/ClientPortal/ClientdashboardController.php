<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Rules\MatchOldPassword;
use App\Rules\UniqueEmail;
use App\User, App\CaseStaff, App\CaseClientSelection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Messages,App\ReplyMessages,App\Firm,App\EmailTemplate,App\CaseMaster;
use Carbon\Carbon;
use DB,Validator,Session,Mail,Storage,Image;
// use Datatables;
use Yajra\Datatables\Datatables;

class ClientdashboardController extends Controller 
{
    public function messages(Request $request){
        $authUser = auth()->user();
        Messages::where('created_by',Auth::user()->id)->whereNull('subject')->whereNull('user_id')->whereNull('message')->forceDelete();

        $messages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*', "messages.updated_at as last_post", DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as sender_name'),"case_master.case_title");
        // $messages = $messages->where("messages.user_id",'like', '%'.Auth::User()->id.'%');
        $messages = $messages->where("messages.firm_id",Auth::User()->firm_name);
        $messages = $messages->whereNull("case_master.deleted_at");
        /* $messages = $messages->where(function($messages){
            $messages = $messages->orWhere("messages.user_id",'like', '%'.Auth::User()->id.'%');
            $messages = $messages->orWhere("messages.created_by",Auth::user()->id);
        }); */
        $messages = $messages->where(function($query) use($authUser) {
            $query->whereRaw('FIND_IN_SET(?, messages.user_id)', [$authUser->id])
                ->orWhere("messages.created_by", $authUser->id);
        });
        if($request->folder == 'archived'){
            // $messages = $messages->where("messages.is_archive",1);
            $messages = $messages->whereJsonContains('users_json', ['user_id' => (string)$authUser->id, "is_archive" => 'yes']);
        }else if($request->folder == 'draft'){
            $messages = $messages->where("messages.is_draft",1);
        }else if($request->folder == 'sent'){
            $messages = $messages->where("messages.is_sent",1);
        }else{
            // $messages = $messages->where("messages.is_archive",0);
            $messages = $messages->where("messages.is_draft",0);
            $messages = $messages->whereJsonContains('users_json', ['user_id' => (string)$authUser->id, "is_archive" => 'no']);
        }

        if(isset($request->case_id) && $request->case_id != ''){
            $messages = $messages->where("messages.case_id",$request->case_id);
        }
        

        $messages = $messages->orderBy("messages.updated_at", 'desc');
        $messages = $messages->get();

        $caseList = User::where('id', Auth::User()->id)->select('id')->with('clientCases')->first();

        return view("client_portal.messages.index",compact('messages', 'request', 'caseList'));            
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
            $authUserId = (string) auth()->id();
            $linkedContact = encodeDecodeJson($messagesData->users_json);
            if(count($linkedContact)) {
                foreach($linkedContact as $key => $item) {
                    if($item->user_id == $authUserId) {
                        $item->is_read = 'yes';
                    }
                    $updatedLinkedContact[] = $item;
                }
                $messagesData->users_json = encodeDecodeJson($updatedLinkedContact, 'encode');
            }
            // $messagesData->is_read = 0;
            $messagesData->save();
    
            $messageList = ReplyMessages::leftJoin("messages","reply_messages.message_id","=","messages.id")
            ->select('reply_messages.*')
            ->where('reply_messages.message_id', $request->id)
            ->get();
        
            $clientList = [];    
            $userlist = explode(',', $messagesData->user_id);
            foreach ($userlist as $key => $value) {
                if($value != auth()->id()) {
                $userInfo =  User::where('id',$value)->select('first_name','last_name','user_level')->first();
                $clientList[$value] =  strtolower($userInfo['first_name'].' '.$userInfo['last_name']);
                }
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
        $clientData=User::find($id);
        $mail_body = $getTemplateData->content;
        $senderName=Auth::User()->first_name." ".Auth::User()->last_name;
        $mail_body = str_replace('{sender}', $senderName, $mail_body);
        $mail_body = str_replace('{subject}', $request['subject'], $mail_body);
        $mail_body = str_replace('{loginurl}', route('login'), $mail_body);
        if($clientData->user_level == '3') {
        $mail_body = str_replace('{url}', route('messages/info',$messageID), $mail_body);
        } else {
        $mail_body = str_replace('{url}', route('client/messages/info',$messageID), $mail_body);
        }
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

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
        // $Messages->is_archive = 1;
        $jsonData = [];
        $decodeData = encodeDecodeJson($Messages->users_json);
        if(count($decodeData)) {
            foreach($decodeData as $item) {
                if(auth()->id() == $item->user_id) {
                    $item->is_archive = 'yes';
                }
                $jsonData[] = $item;
            }
            $Messages->users_json = encodeDecodeJson($jsonData, 'encode');
        }
        $Messages->save();
        session(['popup_success' => 'Message was archived']);
        return response()->json(['errors'=>'']);
    }

    public function unarchiveMessageToUserCase(Request $request){
        $Messages=Messages::find($request->message_id);
        // $Messages->is_archive = 0;
        $jsonData = [];
        $decodeData = encodeDecodeJson($Messages->users_json);
        if(count($decodeData)) {
            foreach($decodeData as $item) {
                if(auth()->id() == $item->user_id) {
                    $item->is_archive = 'no';
                }
                $jsonData[] = $item;
            }
            $Messages->users_json = encodeDecodeJson($jsonData, 'encode');
        }
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
            $Messages->is_sent=1;
            $authUser = auth()->user();
            $linkedContact = encodeDecodeJson($Messages->users_json);
            if(count($linkedContact)) {
                foreach($linkedContact as $key => $item) {
                    if($authUser->id == $item->user_id)       {
                        $item->is_read = 'yes';
                    } else {
                        $item->is_read = 'no';
                    }
                    $updatedLinkedContact[] = $item;
                }
                $Messages->users_json = encodeDecodeJson($updatedLinkedContact, 'encode');
            }
            $Messages->last_post_at = Carbon::now();
            $Messages->save();
            $userList = explode(',', $Messages->user_id);
            foreach($userList as $uitem) {
                if($uitem != $authUser->id) {
                    $this->sendMailGlobal($request, $uitem, $request->message_id);
                }
            }
            session(['popup_success' => 'Your message has been sent']);
            return response()->json(['errors'=>'']);
            exit;       
        }           
    }

    public function addMessagePopup(){
        $firmCases = $firmOwner = [];
        
        // $Messages = Messages::where('user_id',Auth::user()->id)->whereNull('subject')->first();
        // if(empty($Messages)){
            $Messages=new Messages;
            $Messages->user_id = Null;
            $Messages->case_id=NUll;
            $Messages->is_read=0;
            $Messages->is_draft=1;
            $Messages->for_staff='yes';
            $Messages->firm_id = Auth::User()->firm_name;
            $Messages->created_by = Auth::User()->id;
            $Messages->save();
        // }      
        
        // show list of user cases staff 
        $firmOwner = $userCaseStaffList = [];
        $caseList = User::where('id', Auth::User()->id)->select('id')->with('clientCases')->first();
        $caseListCount = count($caseList->clientCases);
        if($caseListCount > 0 && $caseListCount == 1){
            $userCaseStaffList =  CaseStaff::join('case_client_selection','case_staff.case_id','=','case_client_selection.case_id')
            ->join('users','case_staff.user_id','=','users.id')
            ->select("users.id","users.first_name","users.last_name","users.user_title")
            ->where('case_client_selection.selected_user',Auth::User()->id)  
            ->get();
        }elseif($caseListCount <= 0){
            $firmOwner = User::find(Auth::User()->parent_user);
        }
        // return $Messages;
        return view("client_portal.messages.addMessage",compact('Messages','firmOwner', 'caseList', 'caseListCount', 'userCaseStaffList'));                
    }

    public function sendOrDraftMessage(Request $request){
        $sendTo = [];
        if(isset($request->send_to)) {
            $sendTo = $request->send_to;
        }
        array_push($sendTo, auth()->id());

        foreach($sendTo as $uid) {
            $jsonData[] = [
                'user_id' => (string) $uid,
                'is_read' => (auth()->id() == $uid) ? 'yes' : 'no',
                'is_archive' => 'no',
            ];
        } 

        // echo "<pre>"; print_r(encodeDecodeJson($jsonData, 'encode')); exit();
        $redirect = 'no';
        $Messages= Messages::find($request->message_id);
        $Messages->case_id=$request->case_id ?? NUll;
        $Messages->user_id=NUll;
        if(isset($sendTo) && count($sendTo) >= 0){
            $Messages->user_id= implode(",",$sendTo);
        }
        $Messages->subject=$request->subject ?? NUll;
        $Messages->message=$request->msg ?? NUll;
        $Messages->created_by = Auth::User()->id;
        if($request->action != ''){
            $Messages->is_sent=1;
            $Messages->is_read=0;
            $Messages->is_draft=0;
            $redirect = 'yes';
        }
        $Messages->users_json = encodeDecodeJson($jsonData, 'encode');
        $Messages->last_post_at = Carbon::now();
        $Messages->save();

        if($request->action != ''){
            $ReplyMessages=new ReplyMessages;
            $ReplyMessages->message_id=$request->message_id;
            $ReplyMessages->reply_message=$request->msg;
            $ReplyMessages->created_by = Auth::User()->id;
            $ReplyMessages->save();

            if(isset($sendTo) && count($sendTo) > 1){
                foreach($sendTo as $k => $staff_id){
                    if($staff_id != auth()->id()) {
                        $this->sendMailGlobal($request, $staff_id, $request->message_id);    
                    }
                }                
            }else{
                $this->sendMailGlobal($request, $sendTo[0], $request->message_id);
            }
            
        }
        return response()->json(['redirect'=>$redirect]);
    }

    public function discardDraftMessage(Request $request){
        Messages::where('id', $request->id)->forceDelete();
    }

    public function openDraftMessage(Request $request){
        $Messages = Messages::where('id', $request->id)->first();
        // $firmOwner = User::find(Auth::User()->parent_user);
        // $firmCases = CaseMaster::where('firm_id', Auth::User()->firm_name)->get();
        
        // show list of user cases staff 
        $firmOwner = $userCaseStaffList = [];
        $caseList = User::where('id', Auth::User()->id)->select('id')->with('clientCases')->first();
        $caseListCount = count($caseList->clientCases);
        if($caseListCount > 0 && $caseListCount == 1){
            $userCaseStaffList =  CaseStaff::join('case_client_selection','case_staff.case_id','=','case_client_selection.case_id')
            ->join('users','case_staff.user_id','=','users.id')
            ->select("case_client_selection.id as ccs","users.id","users.first_name","users.last_name","users.user_title")
            ->whereNull('case_client_selection.deleted_at')  
            ->where('case_client_selection.selected_user',Auth::User()->id)  
            ->get();
        }else if($caseListCount >= 2){
            $userCaseStaffList =  CaseStaff::join('users','case_staff.user_id','=','users.id')
            ->select("users.id","users.first_name","users.last_name","users.user_title")
            ->where('case_staff.case_id',$Messages->case_id)  
            ->whereNull('case_staff.deleted_at')  
            ->get();
        }else if($caseListCount <= 0){
            $firmOwner = User::find(Auth::User()->parent_user);
        }
        return view("client_portal.messages.addMessage",compact('Messages','firmOwner', 'caseList', 'caseListCount', 'userCaseStaffList'));                
    }

    public function getCaseStaffList(Request $request){
        $userCaseStaffList =  CaseStaff::join('users','case_staff.user_id','=','users.id')
        ->select("users.id","users.first_name","users.last_name","users.user_title")
        ->where('case_staff.case_id',$request->case_id)  
        ->get();

        return response()->json(['staffList'=>$userCaseStaffList]);

    }
}