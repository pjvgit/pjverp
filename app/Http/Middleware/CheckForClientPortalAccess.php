<?php

namespace App\Http\Middleware;

use App\UsersAdditionalInfo;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckForClientPortalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currentRouteName = $request->route()->getName();
        if(!Auth::check() && $currentRouteName == "client/bills/payment") {
            $clientId = $request->route('client_id');
            $client = UsersAdditionalInfo::where("user_id", encodeDecodeId($clientId, 'decode'))->first();
            if($client && $client->client_portal_enable == "1") {
                auth()->loginUsingId($client->user_id);
                return $next($request);
                // return $request->url();
            } else {
                abort(403);
            }
        } else {
            $user = Auth::user();
            if($user->userAdditionalInfo->client_portal_enable == '1') {
                return $next($request);
            } else {
                abort(403);
            }
        }
    }
}
