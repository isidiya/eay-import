<?php

namespace App\Http\Middleware;

use App\Models\user;
use Closure;

class WebsiteApiMiddleware
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

        if(!empty($request->server('REDIRECT_HTTP_AUTHORIZATION'))){
            $user = user::where('user_token',$request->server('REDIRECT_HTTP_AUTHORIZATION'))->first();
            if(!empty($user) && $user->is_admin){
                return $next($request);
            }
        }

		if(!empty($request->server('HTTP_AUTHORIZATION'))){
            $user = user::where('user_token',$request->server('HTTP_AUTHORIZATION'))->first();
            if(!empty($user) && $user->is_admin){
                return $next($request);
            }
        }

        return redirect('/');

    }
}
