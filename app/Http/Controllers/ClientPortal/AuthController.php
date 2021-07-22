<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller 
{
    /**
     * Verify user email
     */
    public function activeClientAccount($token, Request $request)
    {
        $verifyUser = User::where('token', $token)->first();
        
        if(isset($verifyUser) ) {
            if($verifyUser->user_status==1 && $verifyUser->verified == 1){
                return redirect('login')->with('warning', EMAIL_ALREADY_VERIFIED);
            }else{
                if($request->forgot_password) {
                    return view("client_portal.auth.forgot_password", ['user' => $verifyUser]);
                } else {
                    $status = EMAIL_VERIFIED;
                    return redirect()->route('setup/client/profile', $token);
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
        /* $request->validate([
            'password' => 'required|min:6',
            'confirm_password' => 'soemtimes|required|min:6',
            'user_timezone' => 'soemtimes|required',
        ]); */

        $user =  User::where(["token" => $request->token])->with('userAdditionalInfo')->first();

        if(isset($user) ) {
            $user->fill([
                'password' => Hash::make(trim($request->password)),
                'user_timezone'=>$request->user_timezone,
                'verified'=>"1",
                'user_status'=>"1"
            ])->save();
 
            if (Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
                $userStatus = Auth::User()->user_status;
                if($userStatus == '1' && $user->userAdditionalInfo->client_portal_enable == 1) { 
                    session(['layout' => 'horizontal']);
                    return redirect()->route('client/home')->with('success','Login Successfully');
                } else {
                    Auth::logout();
                    session()->flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
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
}