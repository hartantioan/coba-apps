<?php

namespace App\Http\Middleware;

use Cookie;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Lock
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
        if(session('bo_is_lock') !== 1) {
            return $next($request);
        } else {
            return redirect('/admin/lock');
        }
    }
}
