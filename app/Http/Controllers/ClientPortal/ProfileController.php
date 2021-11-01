<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Rules\MatchOldPassword;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller 
{
    /**
     * Get client portal dashboard
     */
    public function edit()
    {
        $user = auth()->user();

        return view("client_portal.profile.edit_profile", compact('user'));
    }

    /**
     * Update client profile
     */
    public function update(Request $request)
    {
        // return $request->user;
        $user = User::whereId($request->id)->first();
        $user->fill($request->user)->save();

        return redirect()->route("client/account")->with("success", "Contact info saved");
    }

    /**
     * CHange/update client password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required', new MatchOldPassword],
            'password' => 'required|confirmed',
        ]);
        return $request->all();
    }
}