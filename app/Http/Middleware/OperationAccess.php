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
            ->first();

        if($access) {
            $user = User::find(session('bo_id'));
            $cekDate = $user->cekMinMaxPostDate($url);
            $minDate = $cekDate ? date('Y-m-d', strtotime('-'.$cekDate->userDate->count_backdate.' days')) : date('Y-m-d');
            $maxDate = $cekDate ? date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$cekDate->userDate->count_futuredate.' days')) : date('Y-m-d');
            $request->attributes->set('minDate', $minDate);
            $request->attributes->set('maxDate', $maxDate);
            if(isset($request->post_date)){
                if($request->post_date < $minDate || $request->post_date > $maxDate){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Ups, Tanggal post anda tidak boleh kurang dari '.date('d/m/Y',strtotime($minDate)).' atau lebih dari '.date('d/m/Y',strtotime($maxDate)).'.',
                    ]);
                }
            }
            if($access->menu->is_maintenance){
                $passed = false;
                $whitelists = $access->menu->whitelist ? explode(',',$access->menu->whitelist) : [];
                info($request->ip());
                if(in_array($request->ip(),$whitelists)){
                    $passed = true;
                }
                if(!$passed){
                    abort(503);
                }
            }
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
