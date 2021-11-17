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
        dbStart();
        try {
            $user = User::whereId($request->id)->first();
            $user->fill($request->user)->save();
            dbCommit();
            return redirect()->route("client/account")->with("success", "Contact info saved");
        } catch(Exception $e) {
            dbEnd();
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    /**
     * CHange/update client password
     */
    public function changePassword(Request $request)
    {
        // return $request->all();
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'password' => 'required|confirmed',
        ]);
        try {
            dbStart();
            User::whereEmail(auth()->user()->email)->update(['password'=> Hash::make($request->password)]);
            Auth::loginUsingId(auth()->id());
            dbCommit();
            return response()->json(["success" => true, "message" => "Password saved"]);
        } catch(Exception $e) {
            dbEnd();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update client preferences
     */
    public function savePreferences(Request $request)
    {
        // return $request->all();
        dbStart();
        try {
            $user = User::whereId($request->id)->first();
            $user->fill($request->all())->save();
            dbCommit();
            return redirect()->route("client/account/preferences")->with("success", "Preferences saved");
        } catch(Exception $e) {
            dbEnd();
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    /**
     * CHange/update client password
     */
    public function changeEmail(Request $request)
    {
        // return $request->all();
        $authEmail = auth()->user()->email;
        $request->request->add(['email' => $request->new_email]);
        $request->validate([
            'new_email' => ['required', 'email', 'not_in:'.$authEmail, new UniqueEmail],
            'old_password' => ['required', new MatchOldPassword],
        ], [
            'new_email.not_in' => 'New email is the same as the old one',
        ]);
        try {
            dbStart();
            User::whereEmail($authEmail)->update(['email'=> $request->new_email]);
            dbCommit();
            return response()->json(["success" => true, "message" => "New email saved", 'email' => $request->new_email]);
        } catch(Exception $e) {
            dbEnd();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}