<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth() -> user() != null && auth() -> user() -> email_verified == 0) {
            auth() -> logout();
            return redirect('error/emailVerifiedFailed');
        }

        return $next($request);

    }
}
