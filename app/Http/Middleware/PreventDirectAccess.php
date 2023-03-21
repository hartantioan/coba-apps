<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventDirectAccess
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
        $referer = $request->header('referer');
        if(empty($referer)) {
            abort(401);
        }
        return $next($request);
    }
}
