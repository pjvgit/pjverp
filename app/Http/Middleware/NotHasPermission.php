<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class NotHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::user();
        $userPermissions = $user->getPermissionNames()->toArray();
        if(!in_array($permission, $userPermissions)) {
            return $next($request);
        } else {
            abort(403);
        }
    }
}
