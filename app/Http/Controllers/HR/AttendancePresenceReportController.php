<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendances;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Models\UserAbsensiMesin;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class AttendancePresenceReportController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Laporan Presensi',
            'user'          =>   User::where('type','1')->where('status',1)->get(),
            'content'       => 'admin.hr.attendance_presence_report',
        ];

        return view('admin.layouts.index', ['data' => $data]); 
    }

    public function filterByDate(Request $request){
        info('mulais');
        $start_time = microtime(true);
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);
        
        $user_data = User::where(function($query) use ( $request) {
          
        })->get();
        
        $attendance_detail = [];
        info($user_data);
        if($request->start_date && $request->end_date){
            
            $date = $start_date->copy();
            while ($date->lte($end_date)) {
                
                foreach($user_data as $c=>$row_user){
                   
                    $query_data = EmployeeSchedule::whereDate('date', $date->toDateString())
                              ->where('user_id',$row_user->employee_no)
                              ->get();
                    $cleanedNik = str_replace(' ', '', $row_user->employee_no);
                    $query_data2 = Attendances::where('date','like',$date->toDateString()."%")
                                ->where('employee_no',$cleanedNik)
                                ->get();
                    
                    //diloop per user untuk mendapatkan schedule yang dibuat untuk peruser masing masing
                    $array_masuk=[];
                    $array_keluar=[];
                    $muleh=null;
                    
                    $exact_in  = [];
                    $exact_out = [];
                    $masuk_awal = null;

                    $time_ins=[];
                    $time_outs=[];
                    $min_time_ins=[];
                    $max_time_outs=[];
                    $nama_shifts=[];
                    $different_masuk=[];
                    $different_keluar=[];
                    $tipe=[];
                    
                    foreach($query_data as $key=> $row_schedule_filter){
                        $exact_in[$key]=0;
                        $exact_out[$key]=0;
                        $login[$key]=null;
                        $logout=null;
                        $tipe[$key]='';
                       

                        $different_masuk[$key]='';
                        $different_keluar[$key]='';
                        // $min_time_in = $row_schedule_filter->shift->min_time_in;
                        $time_in = $row_schedule_filter->shift->time_in;
                        $min_time_in = Carbon::parse($time_in)->subHours($row_schedule_filter->shift->tolerant)->toTimeString();
                        $real_time_in =$date->format('Y-m-d') . ' ' . $time_in;
                        $combinedDateTimeInCarbon = Carbon::parse($real_time_in);
                        $real_min_time_in = $combinedDateTimeInCarbon->copy()->subHours($row_schedule_filter->shift->tolerant);
                        // $max_time_out = $row_schedule_filter->shift->max_time_out;
                        $time_out = $row_schedule_filter->shift->time_out;
                        $max_time_out = Carbon::parse($time_out)->addHours($row_schedule_filter->shift->tolerant)->toTimeString();
                        $real_time_out =$date->format('Y-m-d') . ' ' . $time_out;
                        $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                        $real_max_time_out = $combinedDateTimeOutCarbon->copy()->addHours($row_schedule_filter->shift->tolerant);
                        
                        $array_masuk[$key]='';
                        $array_keluar[$key]='';
                        if($key != 0){// mengetahui apabila shift merupakan yang pertama atau bukan melalui iterasi
                            $currentSchedule = $query_data[$key];
                            $previousSchedule = $query_data[$key - 1];
                            
                            $currentTimeIn = Carbon::parse($currentSchedule->shift->time_in);
                            $previousTimeIn = Carbon::parse($previousSchedule->shift->time_out);
                            
                            $timeDifference = $currentTimeIn->diffInHours($previousTimeIn);
                           
                            if($timeDifference>2 && $key <= 2){
                                
                                $exact_in[$key]=0;
                                $exact_out[$key]=0;
                                if(count(($query_data))==3){
                                    $exact_out[$key-1]=1;
                                }
                            }elseif($key===2){
                                if($login[0] !=null){
                                    $exact_in[$key]=1;
                                    $exact_out[$key]=0;
                                    $array_masuk[$key]='Lanjutan';
                                }else{
                                    $exact_in[$key]=0;
                                    $exact_out[$key]=0;
                                }
                               
                                
                            }elseif($timeDifference <= 2 && $key < 2 && count($query_data) !=3){
                                
                                if($login[0]!=null){//utk melihat shift ke 2 dalam shift yang totalnya ada 2
                                    
                                    $exact_in[$key]=1;
                                    
                                    $array_masuk[$key]='Lanjutan';
                                    if($key>0){
                                        $array_keluar[$key-1]='Lanjutan';
                                        $exact_out[$key-1]=1;
                                    }
                                    
                                }else{
                                    $exact_in[$key]=0;
                                    $exact_out[$key]=0; 
                                }
                                
                            }elseif($timeDifference <= 2 && $key <= 2 && count($query_data) ==3){
                                
                                if($login[0]!=null){
                           
                                    $exact_in[$key]=1;
                                    $exact_out[$key]=1;
                                    $array_masuk[$key]='Lanjutan';
                                    $array_keluar[$key]='Lanjutan';
                                }else{
                                   
                                    $exact_in[$key]=0;
                                    $exact_out[$key]=0; 
                                }
                            }
                            
                            //pengurangan apabila lebih besar dari 1 maka shift tidak bersamaan
                        }
                        $query_attendance = Attendances::where(function ($query) use ($date,$cleanedNik) {
                            $mulaiDate = Carbon::parse($date)->subDays(1)->startOfDay()->toDateTimeString();
                            $akhirDate = Carbon::parse($date)->addDays(1)->endOfDay()->toDateTimeString();
                           
                            $query->where('date', '>=', $mulaiDate)
                                ->where('date', '<=', $akhirDate)
                                ->where('employee_no',$cleanedNik);
                        })->orderBy('date')->get();
                       
                        foreach($query_attendance as $row_attendance_filter){
                            $dateAttd = Carbon::parse($row_attendance_filter->date);
                            
                           
                            $timePart = $dateAttd->format('H:i:s');
                            
                                if(!$masuk_awal  && $date->toDateString() == $dateAttd->toDateString()){
                                    
                                    $masuk_awal=$timePart;
                                    $diffInSeconds = Carbon::parse($timePart)->diffInSeconds($time_in);
                                    $minutes = floor($diffInSeconds / 60);
                                    $seconds = $diffInSeconds % 60;
                                    
                                    $different_masuk[$key]=$minutes." menit  ".$seconds." detik";
                                    
                                }
                                if(!$muleh  && $date->toDateString() == $dateAttd->toDateString()){
                                    if ($timePart >= $time_out && $timePart > $masuk_awal) 
                                    {
                                        $muleh=$timePart;
                                        $diffInSeconds = Carbon::parse($timePart)->diffInSeconds($max_time_out);
                                        $minutes = floor($diffInSeconds / 60);
                                        $seconds = $diffInSeconds % 60;
                                        
                                        $different_keluar[$key]=$minutes." menit  ".$seconds." detik";
                                    }
                                }
                                if($cleanedNik == '123017'){
                                    info($date);
                                    info($dateAttd);
                                    info($real_min_time_in);
                                    info($real_time_in);
                                    info('masuk sayang');
                                }
                            if ($dateAttd >= $real_min_time_in && $dateAttd <= $real_time_in) {
                                $exact_in[$key]= 1 ;
                                
                                
                                if(!$login[$key]){
                                    
                                    if($masuk_awal==null){
                                        $masuk_awal=$timePart;
                                    }elseif($masuk_awal > $timePart){
                                        $masuk_awal=$timePart;
                                    }
                                    $login[$key] = $timePart;
                                    $array_masuk[$key]=$timePart;
                                    $different_masuk[$key]='';
                                    
                                }
                                
                            }elseif($dateAttd > $real_time_in && $dateAttd < $real_time_out){
                                $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($min_time_in);
                                
                                
                                if($diffHoursTimePartMinIn<=3 && $exact_in[$key] != 1){
                                    $exact_in[$key]= 2 ;
                                    
                                    
                                    if (count($query_data) == 3 && $key > 0 ) {
                                        $exact_in[$key]= 1 ;
                                        
                                    }
                                    $array_masuk[$key]=$timePart;
                                }                                      
                            }elseif($dateAttd > $real_max_time_out){
                                $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($max_time_out);
                                
                                if($diffHoursTimePartMaxOut<=3){
                                    $array_keluar[$key]=$timePart;
                                }
                            }
                            //perhitungan pulang
                            if ($dateAttd >= $real_time_out && $dateAttd <= $real_max_time_out) {
                                $exact_out [$key]= 1 ;
                                if($muleh==null){
                                    $muleh=$timePart;
                                }elseif($muleh < $timePart){
                                    $muleh=$timePart;
                                }
                                if(!$logout){
                                    
                                    $logout = $timePart;
                                    $array_keluar[$key]=$timePart;
                                    $different_keluar[$key]='';
                                    
                                }  
                            }
                        }
                        
                        if(!$exact_in){
                            $exact_in[$key]=0;
                        }
                        if($key==0){// melakukan pengecekan saat pertama kali looping schedule untuk memberikan keterangan masuk / tidaknya atau berupa lanjutan atau tidak
                            
                           
                            if (count($query_data) === 3) {
                                if($login[$key]==null){
                                    $exact_in[$key]=0;
                                    $exact_out[$key]= 0 ;
                                }
                                
                                if($login[$key]!=null){
                                    
                                    $exact_out[$key]= 1 ;
                                }
                            }else{
                                if($login[$key] == null && $masuk_awal == null){
                                    
                                    $exact_in[$key]= 0 ;
                                    $exact_out[$key]= 0 ;
                                    if($logout!=null){
                                        $exact_out[$key]=1;
                                    }
                                }
                                
                                if($logout != null ){
                                    $exact_out[$key]=1;
                                }else{
                                    $exact_out[$key]= 0 ;
                                }
                                
                            }
                            
                        }
                        if($key == 1 &&  count($query_data) == 2){//mengecek saat schedule yang di tengah dan jumlahnya dua dan mengecek kalau dia itu tidak check saat masuk dan check saat pulang
                            if($exact_out[$key]==1){
                                $exact_out[$key-1]=1;
                               
                            }
                        }if($key==2 && $exact_in[0]==null && $exact_out[$key]==1){
                            $exact_out[0]=1;
                            $exact_in[1]=0;
                            $exact_out[1]=1;
                            $array_keluar[0]="Lanjutan";
                            $array_masuk[1]="Lanjutan";
                            $array_keluar[1]="Lanjutan";
                        }if($key==2 && $exact_in[0]!=null){
                            $exact_in[2]=1;
                            $array_masuk[2]="Lanjutan";
                            $array_keluar[1]="Lanjutan";
                            $array_keluar[0]="Lanjutan";
                        }
                        
                        $time_ins[]=$time_in;
                        $time_outs[]=$time_out;
                        $min_time_ins[]=$min_time_in;
                        $max_time_outs[]=$max_time_out;        
                        $nama_shifts[]=$row_schedule_filter->shift->name;
                        
                    }
                  
                    $attendance_detail[(string)$date][]=[
                        'date' => Carbon::parse($date)->format('d/m/Y'),
                        'user_no'=>$row_user->employee_no,
                        'user' =>$row_user->name,
                        'in'   => $exact_in,
                        'out'  => $exact_out,
                        'login'=> $masuk_awal,
                        'logout'=>$muleh,
                        'tipe' => $tipe,
                        'jam_masuk'=>$array_masuk,
                        'jam_keluar'=>$array_keluar,
                        'perbedaan_jam_masuk'=>$different_masuk,
                        'perbedaan_jam_keluar'=>$different_keluar,
                        'time_in'=>$time_ins,
                        'time_out'=>$time_outs,
                        'min_time_in'=>$min_time_ins,
                        'max_time_out'=>$max_time_outs,
                        'nama_shift'=>$nama_shifts,
                    ];
                   
                }
                
                
                
                $date->addDay();
            }
            $end_time = microtime(true);
        
            $execution_time = ($end_time - $start_time);
      
            info($execution_time);
            $response =[
                'status'=>200,
                'message'  =>$attendance_detail,
            ];
        }else{
            $response =[
                'status'  =>500,
                'message' =>'ada yang error'
            ];
        }
        return response()->json($response);
    }
}

