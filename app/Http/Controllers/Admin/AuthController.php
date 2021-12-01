<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

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
            'email'   => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {

            return redirect()->intended('admin/');
        }
        return back()->withInput($request->only('email'));
    }
}