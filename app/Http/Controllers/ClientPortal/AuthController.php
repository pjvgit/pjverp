<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Traits\UserTrait;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller 
{
    use UserTrait;
    /**
     * Verify user email
     */
    public function activeClientAccount($token, Request $request)
    {
        $verifyUser = User::where('token', $token)->first();
        
        if(isset($verifyUser) ) {
            if(isset($request->forgot_password)) {
                return view("client_portal.auth.forgot_password", ['user' => $verifyUser]);
            } else {
                if($this->updateSameEmailUserDetail($verifyUser)) {
                    return redirect()->route('get/client/profile', $token);
                } else {
                    if($verifyUser->user_status == 1 && $verifyUser->verified == 1 && $verifyUser->last_login){
                        return redirect()->route('get/client/profile', $token);
                    }else{
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
            if (Auth::attempt(['email' => $user->email, 'password' => trim($request->password)])) {
                $userStatus = Auth::User()->user_status;
                if($userStatus == '1' && $user->userAdditionalInfo->client_portal_enable == '1') { 
                    session(['layout' => 'horizontal']);
                    $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                    $user->auto_logout = 'on';
                    $user->sessionTime = 60;
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
    public function updateClientProfile($token, Request $request)
    {
        $user =  User::where(["token" => $token])->with('userAdditionalInfo')->first();
        if(isset($user) ) { 
            if (Hash::check(trim($request->password_confirmation), $user->password) && $user->user_status == '1') {
                auth()->login($user);
                session(['layout' => 'horizontal']);
                $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                $user->save();
                
                // Add history
                $data=[];
                $data['user_id']= $user->id;
                $data['client_id']= $user->id;
                $data['activity']='logged in to LegalCase';
                $data['activity_for']=$user->id;
                $data['type']='user';
                $data['action']='login';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                if($user->getUserFirms() > 1) {
                    return redirect()->route('login/sessions/launchpad', encodeDecodeId($user->id, 'encode'));
                } else if($user->user_level == '2' && $user->userAdditionalInfo->client_portal_enable == '1') {
                    return redirect()->route('client/home')->with('success','Login Successfully');
                } else if($user->user_level == '3') {
                    return redirect()->route('dashboard')->with('success','Login Successfully');
                } else {
                    Auth::logout();
                    session()->flush();
                    return redirect('login')->with('error', 'Something went wrong, please try again later');
                }
            } else {
                session()->flash('password_error', "Password doesn't match.");
                return redirect()->back();
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
        Auth::logout();
        // session()->flush();
        $userId = encodeDecodeId($id, 'decode');
        $client = User::whereId($userId)->whereIn("user_level", ['2','3'])->where("user_status", '1')->first();
        if($client) {
            $firms = $this->getUserFirms($client);
            return view('client_portal.auth.switch_account', compact('client', 'firms'));
        } else {
            return redirect('login')->with('error', 'Something went wrong, please try again later');
        }
    }

    /**
     * LOgin to selected user account
     */
    public function loginUserAccount(Request $request)
    {
        // return $request->all();
        $userId = encodeDecodeId($request->client_id, 'decode');
        $user = User::whereId($userId)->whereIn("user_level", ['2','3'])->where("user_status", '1')
                    ->with(['userAdditionalInfo' => function($query) {
                        $query->where("client_portal_enable", '1')->select(["user_id","client_portal_enable"]);
                    }])->first();
        if($user) {
            Auth::logout();
            session()->flush();
            if($user->user_level == '2' && $user->userAdditionalInfo && $user->userAdditionalInfo->client_portal_enable == '1') {
                Auth::loginUsingId($user->id);
                return redirect()->route('client/home')->with('success','Login Successfully');
            } else if($user->user_level == '3') {
                Auth::loginUsingId($user->id);
                return redirect()->route('dashboard')->with('success','Login Successfully');
            } else {
                session()->flush();
                return redirect('login')->with('error', 'Something went wrong, please try again later');
            }
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
        $userId = encodeDecodeId($request->client_id, 'decode');
        try {
            $user = User::whereId($userId)->whereIn("user_level", ['2','3'])->where("user_status", '1')
                        /* ->whereHas("userAdditionalInfo", function($q) {
                            $q->where("client_portal_enable", '1');
                        }) */->first();
            User::whereEmail($user->email)->update(["is_primary_account" => "no"]);
            $user->fill(["is_primary_account" => 'yes'])->save();
            $firms = $this->getUserFirms($user);
            $view = view('client_portal.auth.partial.load_user_account_list', compact('firms'))->render();
            return response()->json(['view' => $view, 'success' => true]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Save reset password
     */
    public function resetPassword($token, Request $request)
    {
        // return $request->all();
        $request->validate([
            'password' => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ]);

        $user =  User::where(["token" => $token])->with('userAdditionalInfo')->first();

        if(isset($user) ) {
            $user->fill([
                'password' => Hash::make(trim($request->password)),
                'verified'=>"1",
                'user_status'=>"1",
            ])->save();

            // Update same email passwords
            User::where("email", $user->email)->update(['password' => Hash::make(trim($request->password))]);

            Auth::loginUsingId($user->id);
            if (Auth::check()) {
                if($user->user_status == '1') {
                    if($user->user_level == '2' && $user->userAdditionalInfo->client_portal_enable == '1') { 
                        $redirectUrl = "client/home";
                    } else {
                        $redirectUrl = "dashboard";
                    }
                    session(['layout' => 'horizontal']);
                    $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();

                    // Add history
                    $data=[];
                    $data['user_id']= $user->id;
                    $data['client_id']= $user->id;
                    $data['activity']='logged in to LegalCase';
                    $data['activity_for']=$user->id;
                    $data['type']='user';
                    $data['action']='login';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                    return redirect()->route($redirectUrl)->with('success','Login Successfully');
                } else {
                    Auth::logout();
                    session()->flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            } else {
                session()->flush();
                return redirect('login')->with('warning', INACTIVE_ACCOUNT);
            }
        }else{
            session()->flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
    }
}