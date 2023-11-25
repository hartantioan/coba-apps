<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\LockPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LockAccounting
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
        if(in_array($request->segment(count($request->segments())),['create'])){
            if(isset($request->post_date)){
                $month = date('Y-m',strtotime($request->post_date));
                $cekLock = LockPeriod::where('month',$month)->whereIn('status',['2','3'])->first();
                if($cekLock){
                    $passedSpecial = false;
    
                    if($cekLock->status_closing == '2'){
                        foreach($cekLock->lockPeriodDetail as $row){
                            if($row->user_id == session('bo_id')){
                                $passedSpecial = true;
                            }
                        }
                    }elseif($cekLock->status_closing == '1'){
                        $passedSpecial = true;
                    }
    
                    if($passedSpecial){
                        return $next($request);
                    }else{
                        return response()->json([
                            'status'    => 500,
                            'message' => 'Mohon maaf pada bulan '.date('F Y').' seluruh transaksi telah ditutup oleh pihak Akunting dengan nomor '.$cekLock->code.' dan status '.$cekLock->statusClosing().'.',
                        ]);
                    }
                }else{
                    return $next($request);
                }
            }else{
                return $next($request);
            }
        }else{
            return $next($request);
        }
    }
}
