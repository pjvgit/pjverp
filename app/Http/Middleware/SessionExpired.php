<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Session\Store;
use Auth;
use Session;
use Carbon\Carbon;
class SessionExpired {
    protected $session;
    protected $timeout = 10;

    public function __construct(Store $session){
        $this->session = $session;
    }
    public function handle($request, Closure $next)
    {
        if(!$this->session->has('lastActivityTime'))
            $this->session->put('lastActivityTime',time());
        elseif(time() - $this->session->get('lastActivityTime') > $this->getTimeOut()){
            $this->session->forget('lastActivityTime');
            Auth::logout();
            return redirect('login')->withErrors(['You had not activity in 15 minutes']);
        }
        $this->session->put('lastActivityTime',time());
        return $next($request);
    }
    protected function getTimeOut()
    {
        return (env('TIMEOUT')) ?: $this->timeout;
    }
    // public function handle($request, Closure $next)
    // {
    //   // If user is not logged in...
    //   if (!Auth::check()) {
    //     return $next($request);
    //   }
   
    //   $user = Auth::guard()->user();
   
    //   $now = Carbon::now();
   
    //   $last_seen = Carbon::parse($user->last_seen_at);
   
    //   $absence = $now->diffInMinutes($last_seen);
   
    //   // If user has been inactivity longer than the allowed inactivity period
    //   //if ($absence > config('session.lifetime')) {
    //     if ($absence > $user->sessionTime) {
    //         Auth::guard()->logout();
            
    //     $request->session()->invalidate();
   
    //     return $next($request);
    //   }
   
    //   $user->last_seen_at = $now->format('Y-m-d H:i:s');
    //   $user->save();
   
    //   return $next($request);
    // }
  }
  