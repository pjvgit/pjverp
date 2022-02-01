<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $userRole)
    {
        if (!Auth::check()) // I included this check because you have it, but it really should be part of your 'auth' middleware, most likely added as part of a route group.
            return redirect('login');

        $user = Auth::user();
        if($userRole == "client" && in_array($user->user_level, [2, 4, 5])) {
            return $next($request);
        } else if($userRole == "user" && $user->user_level == 3 && $user->user_status == 1) {
            return $next($request);
        } else {
            abort(403);
        }
        return redirect('login');
    }
}
