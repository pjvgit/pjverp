<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller 
{
    /**
     * Verify user email
     */
    public function activeClientAccount($token, Request $request)
    {
        $verifyUser = User::where('token', $token)->first();
        
        if(isset($verifyUser) ) {
            $existVerifiedUser = User::whereEmail($verifyUser->email)->whereVerified(1)->whereNotNull('password')->first();
            if($existVerifiedUser) {
                $verifyUser->user_status = 1;
                $verifyUser->verified = 1;
                $verifyUser->password = $existVerifiedUser->password;
                $verifyUser->user_timezone = $existVerifiedUser->user_timezone;
                $verifyUser->save();

                return redirect()->route('get/client/profile', $token);
            } else {
                if($verifyUser->user_status == 1 && $verifyUser->verified == 1 && $verifyUser->last_login){
                    return redirect()->route('get/client/profile', $token);
                }else{
                    if($request->forgot_password) {
                        return view("client_portal.auth.forgot_password", ['user' => $verifyUser]);
                    } else {
                        $status = EMAIL_VERIFIED;
                        return redirect()->route('setup/client/profile', $token);
                    }
                }
            }
        }else{
            return redirect('login')->with('warning', EMAIL_NOT_IDENTIFIED);
        }
    }

    /**
     * Get client profile setup view
     */
    public function setupClientProfile($token)
    {
        $verifyUser = User::where('token', $token)->with('firmDetail')->first();
        return view('client_portal.auth.setup_profile', ['user'=>$verifyUser]);
    }  

    /**
     * Save client profile
     */
    public function saveClientProfile(Request $request)
    {
        // return $request->all();
        $request->validate([
            'password' => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
            'user_timezone' => 'required',
        ]);

        $user =  User::where(["token" => $request->token])->with('userAdditionalInfo')->first();

        if(isset($user) ) {
            $user->fill([
                'password' => Hash::make(trim($request->password)),
                'user_timezone'=>$request->user_timezone,
                'verified'=>"1",
                'user_status'=>"1",
                'is_primary_account' => "yes",
            ])->save();
            $user->refresh();
            // return $user->password;
            // if (Auth::attempt(['email' => $user->email, 'password' => trim($request->password)])) {
            if (Auth::attempt(['email' => $user->email, 'password' => trim($request->password)])) {
                $userStatus = Auth::User()->user_status;
                if($userStatus == '1' && $user->userAdditionalInfo->client_portal_enable == '1') { 
                    session(['layout' => 'horizontal']);
                    $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                    $user->auto_logout = 'on';
                    $user->sessionTime = 10;
                    $user->save();

                    // Add history
                    $data=[];
                    $data['user_id']= $user->id;
                    $data['client_id']= $user->id;
                    $data['activity']='logged in to LegalCase';
                    $data['activity_for']=$request->invoice_id;
                    $data['type']='user';
                    $data['action']='login';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                    return redirect()->route('client/home')->with('success','Login Successfully');
                } else {
                    Auth::logout();
                    session()->flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            } else {
                // return $user;
                session()->flush();
                return redirect('login')->with('warning', INACTIVE_ACCOUNT);
            }
        }else{
            session()->flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
    }

    /**
     * Get client profile setup view
     */
    public function getClientProfile($token)
    {
        $verifyUser = User::where('token', $token)->with('firmDetail')->first();
        return view('client_portal.auth.setup_password', ['user'=>$verifyUser]);
    }  

    /**
     * Check user password and login
     */
    public function updateClientProfile(Request $request)
    {
        $user =  User::where(["token" => $request->token])->with('userAdditionalInfo')->first();
        if(isset($user) ) { 
            if (Auth::attempt(['email' => $user->email, 'password' => trim($request->password_confirmation)])) {
                if($user->user_status == '1' && $user->userAdditionalInfo->client_portal_enable == '1') { 
                    session(['layout' => 'horizontal']);
                    $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();
                    
                    // Add history
                    $data=[];
                    $data['user_id']= $user->id;
                    $data['client_id']= $user->id;
                    $data['activity']='logged in to LegalCase';
                    $data['activity_for']=$request->invoice_id;
                    $data['type']='user';
                    $data['action']='login';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);

                    if($user->getClientFirms() > 1) {
                        return redirect()->route('login/sessions/launchpad', encodeDecodeId($user->id, 'encode'));
                    }

                    return redirect()->route('client/home')->with('success','Login Successfully');
                } else {
                    Auth::logout();
                    session()->flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            } else {
                session()->flash('password_error', "Password doesn't match.");
                return Redirect::back();
            }
        }else{
            Auth::logout();
            session()->flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
    }

    /**
     * Get client portal terms and conditions
     */
    public function termsCondition()
    {
        return view("client_portal.terms_condition");
    }

    /**
     * Get timezone
     */
    public function getTimezone(Request $request)
    {
        $timezone_offset_minutes = $request->timezone_offset_minutes;  // $_GET['timezone_offset_minutes']

        // Convert minutes to seconds
        $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);
        session()->put('local_timezone', $timezone_name);
        // Asia/Kolkata
        echo $timezone_name;
    }

    /**
     * Get switch account view 
     */
    public function getSwitchAccount($id, Request $request)
    {
        // Auth::logout();
        $clientId = encodeDecodeId($id, 'decode');
        $client = User::whereId($clientId)->first();
        $firms = Firm::whereHas("user", function($query) use($client) {
                    $query->whereEmail($client->email)->where("user_level", '2')
                    ->whereHas("userAdditionalInfo", function($q) {
                        $q->where("client_portal_enable", '1');
                    });
                })->with(['user' => function($query) use($client) {
                    $query->whereEmail($client->email)->where("user_level", '2');
                }])->get();
        return view('client_portal.auth.switch_account', compact('client', 'firms'));
    }

    /**
     * LOgin to selected user account
     */
    public function loginUserAccount(Request $request)
    {
        // return $request->all();
        $clientId = encodeDecodeId($request->client_id, 'decode');
        $client = User::whereId($clientId)->where("user_level", '2')->where("user_status", '1')->whereHas("userAdditionalInfo", function($q) {
                        $q->where("client_portal_enable", '1');
                    })->first();
        if($client) {
            Auth::logout();
            session()->flush();
            Auth::loginUsingId($client->id);
            return redirect()->route('client/home')->with('success','Login Successfully');
        } else {
            session()->flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
    }

    /**
     * Set client's primary account
     */
    public function setPrimaryAccount(Request $request)
    {
        $clientId = encodeDecodeId($request->client_id, 'decode');
        try {
            $client = User::whereId($clientId)->where("user_level", '2')->where("user_status", '1')->whereHas("userAdditionalInfo", function($q) {
                            $q->where("client_portal_enable", '1');
                        })->first();
            User::whereEmail($client->email)->update(["is_primary_account" => "no"]);
            $client->fill(["is_primary_account" => 'yes'])->save();
            $firms = Firm::whereHas("user", function($query) use($client) {
                        $query->whereEmail($client->email)->where("user_level", '2')
                        ->whereHas("userAdditionalInfo", function($q) {
                            $q->where("client_portal_enable", '1');
                        });
                    })->with(['user' => function($query) use($client) {
                        $query->whereEmail($client->email)->where("user_level", '2');
                    }])->get();

            $view = view('client_portal.auth.partial.load_user_account_list', compact('firms'))->render();
            return response()->json(['view' => $view, 'success' => true]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }
}