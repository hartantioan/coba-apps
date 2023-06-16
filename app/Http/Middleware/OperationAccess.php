<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MenuUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OperationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $url, $type)
    {
        $access = MenuUser::where('user_id', session('bo_id'))
            ->where('type',$type)
            ->whereHas('menu', function($query) use ($url) {
                $query->where('url', $url);
            })
            ->count();

        if($access > 0) {
            return $next($request);
        } else {
            if($request->isMethod('get')){
                if($request->ajax()){
                    $response = [
                        'status'  => 500,
                        'message' => 'Ups. Anda tidak boleh menggunakan fitur ini.'
                    ];
                    
                    return response()->json($response);
                }else{
                    return abort(403);
                }
            }elseif($request->isMethod('post')){
                $response = [
                    'status'  => 500,
                    'message' => 'Ups. Anda tidak boleh menggunakan fitur ini.'
                ];
                
                return response()->json($response);
            }
        }
    }
}
