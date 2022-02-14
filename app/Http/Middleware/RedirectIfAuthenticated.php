<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            switch ($guard) {
                case 'admin':
                    return redirect()->route('admin/dashboard');
                    break;
                default:
                    if(auth()->user()->user_level == "2") {
                        return redirect('client/home');
                    } else {
                        return redirect()->route("dashboard");
                    }
                    break;
            }
        }


        /* if ($guard == "admin" && Auth::guard($guard)->check()) {
            return redirect('admin/dashboard');
        }
        if (Auth::guard($guard)->check()) {
            if(auth()->user()->user_level == "2") {
                return redirect('client/home');
            } else {
                // return redirect(RouteServiceProvider::HOME);
                return redirect()->route("dashboard");
            }
        } */
        return $next($request);
    }
}
