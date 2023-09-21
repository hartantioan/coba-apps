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
            'user'          =>  User::join('departments','departments.id','=','users.department_id')->select('departments.name as department_name','users.*')->orderBy('department_name')->get(),
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
                        $min_time_in = $row_schedule_filter->shift->min_time_in;
                        $time_in = $row_schedule_filter->shift->time_in;
                        
                        $max_time_out = $row_schedule_filter->shift->max_time_out;
                        $time_out = $row_schedule_filter->shift->time_out;
                       
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
                        
                       
                        if($row_schedule_filter->shift->is_next_day == '1'){
                  
                            if (count($query_data) < 3) {
                                foreach($query_data2 as $row_attendance_filter){
                                    $dateAttd = Carbon::parse($row_attendance_filter->date);
                                    
                                    $timePart = $dateAttd->format('H:i:s');
                                    
                                        if(!$masuk_awal){
                                            
                                            $masuk_awal=$timePart;
                                            $diffInSeconds = Carbon::parse($timePart)->diffInSeconds($time_in);
                                            $minutes = floor($diffInSeconds / 60);
                                            $seconds = $diffInSeconds % 60;
                                            
                                            
                                            $different_masuk[$key]=$minutes." menit  ".$seconds." detik";
                                            
                                        }
                                    
                                     
                                    if ($timePart >= $min_time_in && $timePart <= $time_in) {
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
                                        
                                    }elseif($timePart > $time_in && $timePart < $time_out){
                                        $time1 = new DateTime($time_in);
                                        $time2 = new DateTime($timePart);
                                        $timeDifference = $time1->diff($time2);

                                        // Extract the hours from the time difference
                                        $hoursDifference = $timeDifference->h;

                                        
                                        if($hoursDifference<2){
                                            $exact_in [$key]= 2 ;
                                            $array_masuk[$key]=$timePart;
                                        }
                                       
                                        
                                       
                                    }
                                }
                            }
                            $query_nextday = Attendances::where('date', 'like', $date->copy()->addDay()->toDateString()."%")
                                            ->where('employee_no', $cleanedNik)
                                            ->get();
                            
                            foreach($query_nextday as $row_attendance_filter){
                                $dateAttd = Carbon::parse($row_attendance_filter->date);
                                
                                $timePart = $dateAttd->format('H:i:s');
                                if ($timePart >= $time_out && $timePart <= $max_time_out) {
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
                            
                        }else{
                           
                              
                            $minTimeIn = Carbon::parse($min_time_in);
                            $maxTimeOut = Carbon::parse($max_time_out);
                            foreach($query_data2 as $row_attendance_filter){
                                $dateAttd = Carbon::parse($row_attendance_filter->date);
                                
                                $timePart = $dateAttd->format('H:i:s');
                                
                                    if(!$masuk_awal){
                                        
                                        $masuk_awal=$timePart;
                                        $diffInSeconds = Carbon::parse($timePart)->diffInSeconds($time_in);
                                        $minutes = floor($diffInSeconds / 60);
                                        $seconds = $diffInSeconds % 60;
                                      
                                        
                                        $different_masuk[$key]=$minutes." menit  ".$seconds." detik";
                                        
                                    }
                                    if(!$muleh){
                                        if ($timePart >= $time_out && $timePart > $masuk_awal) 
                                        {
                                            $muleh=$timePart;
                                            $diffInSeconds = Carbon::parse($timePart)->diffInSeconds($max_time_out);
                                            $minutes = floor($diffInSeconds / 60);
                                            $seconds = $diffInSeconds % 60;
                                            
                                            $different_keluar[$key]=$minutes." menit  ".$seconds." detik";
                                        }
                                    }
                                if ($timePart >= $min_time_in && $timePart <= $time_in) {
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
                                    
                                }elseif($timePart > $time_in && $timePart < $time_out){
                                    $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($minTimeIn);
                                    
                                  
                                    if($diffHoursTimePartMinIn<=3 && $exact_in[$key] != 1){
                                        $exact_in[$key]= 2 ;
                                        
                                        
                                        if (count($query_data) == 3 && $key > 0 ) {
                                            $exact_in[$key]= 1 ;
                                            
                                        }
                                        $array_masuk[$key]=$timePart;
                                    }                                      
                                }elseif($timePart > $max_time_out){
                                    $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($maxTimeOut);
                                    
                                    if($diffHoursTimePartMaxOut<=3){
                                        $array_keluar[$key]=$timePart;
                                    }
                                }
                                if ($timePart >= $time_out && $timePart <= $max_time_out) {
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
                        if($masuk_awal){
                            $pembandingdate = Carbon::parse($masuk_awal);
                            $pembanding= $pembandingdate->format('H:i:s');
                            if($row_schedule_filter->shift->t1){
                                
                                if($pembanding > $row_schedule_filter->shift->t1){
                                    $tipe[$key]='t1';
                                }
                            }
                            if($row_schedule_filter->shift->t2){
                                
                                if($pembanding > $row_schedule_filter->shift->t2){
                                    $tipe[$key]='t2';
                                }
                            }
                            if($row_schedule_filter->shift->t3){
                                
                                if($pembanding > $row_schedule_filter->shift->t3){
                                    $tipe[$key]='t3';
                                }
                            }
                            if($row_schedule_filter->shift->t4){
                                
                                if($pembanding > $row_schedule_filter->shift->t4){
                                    $tipe[$key]='t4';
                                }
                            }
                            
                        }
                    }
                  
                    $attendance_detail[$row_user->nama][]=[
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

