<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

    protected $redirectTo = '/admin';

    public function __construct()
    {
        // $this->middleware('guest:admin')->except(['logout', 'userLoginByAdmin']);
    }

    /**
     * Get admin login form
     */
    public function showLoginForm()
    {
        return view('admin_panel.auth.login');
    }

    /**
     * Admin login detail
     */
    public function login(Request $request)
    {
        // return $request->all();
        $this->validate($request, [
            'email'    => 'required|email|exists:admins',
            'password' => 'required'
        ]);

        // if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
        if (Auth::guard('admin')->attempt(['email' => strtolower($request->email), 'password' => $request->password])) {
            return redirect()->intended(route('admin/dashboard'))->with('status','You are Logged in as Admin!');;
        }
        return back()->withInput($request->only('email'))->with('error', 'These credentials do not match with our records.');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        return redirect()->route('admin/login');
    }

    /**
     * Firm user login by Admin
     */
    public function userLoginByAdmin($userId)
    {
        $uId = encodeDecodeId($userId, 'decode');
        $user = User::where('id', $uId)->first();
        if($user && $user->user_level == '3') {
            Auth::loginUsingId($uId);
            return redirect()->intended('dashboard')->with('success','Login Successfully');
        }
    }
}