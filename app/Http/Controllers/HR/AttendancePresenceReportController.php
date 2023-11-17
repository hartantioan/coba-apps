<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendances;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserAbsensiMesin;
use App\Models\UserSpecial;
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
            $query->where('type',1);
            $query->where('employee_no','323012');
        })->get();
        
        $attendance_detail = [];
        
        if($request->start_date && $request->end_date){
            $date = $start_date->copy();
            $date = $start_date->copy();
            while ($date->lte($end_date)) {
                
                foreach($user_data as $c=>$row_user){
                   
                    $query_data = EmployeeSchedule::join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
                        ->whereDate('employee_schedules.date', $date->toDateString())
                        ->where('employee_schedules.user_id', $row_user->employee_no)
                        ->whereIn('employee_schedules.status', [1, 4])
                        ->orderBy('shifts.time_in') // Order by next_day (1 comes last)
                        ->select('employee_schedules.*') // Select the columns you need
                        ->get();
                    $cleanedNik = str_replace(' ', '', $row_user->employee_no);
              
                    $query_special = UserSpecial::where('user_id',$row_user->id)
                                ->where('start_date','<=', $date)
                                ->where('end_date','>=',$date)
                                ->first();      
                    
                    
                    
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
                        $time_in = $row_schedule_filter->shift->time_in;
                        $min_time_in = Carbon::parse($time_in)->subHours($row_schedule_filter->shift->tolerant)->toTimeString();
                        $real_time_in =$date->format('Y-m-d') . ' ' . $time_in;
                        $combinedDateTimeInCarbon = Carbon::parse($real_time_in);
                        $real_min_time_in = $combinedDateTimeInCarbon->copy()->subHours($row_schedule_filter->shift->tolerant);
                      
                        $time_out = $row_schedule_filter->shift->time_out;
                        $max_time_out = Carbon::parse($time_out)->addHours($row_schedule_filter->shift->tolerant)->toTimeString();
                        if($time_in>$time_out){
                            $tambah1hari=$date->copy()->addDay();
                         
                            $real_time_out =$tambah1hari->format('Y-m-d'). ' ' . $time_out;
                            
                        }else{
                            $real_time_out =$date->format('Y-m-d') . ' ' . $time_out;
                        }
                        
                        $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                        
                        $real_max_time_out = $combinedDateTimeOutCarbon->copy()->addHours($row_schedule_filter->shift->tolerant);
                        if($time_in>$time_out){
                            
                            $real_max_time_out->addDay();
                        }
                        if($row_schedule_filter->status == 4){
                         
                            $exact_in[$key]=3;
                            $exact_out[$key]=3;
                            $different_masuk[$key]='';
                            $different_keluar[$key]='';
                            $login[$key]=null;
                            $logout=null;
                                
                            $array_masuk[$key]='Cuti Melahirkan';
                            $array_keluar[$key]='Cuti Melahirkan';
                        }
                        else{
                            $exact_in[$key]=0;
                            $exact_out[$key]=0;
                            $login[$key]=null;
                            $logout=null;
                            $tipe[$key]='';
                        

                            $different_masuk[$key]='';
                            $different_keluar[$key]='';
                            // $min_time_in = $row_schedule_filter->shift->min_time_in;
                            
                            $array_masuk[$key]='Tidak Check Clock';
                            $array_keluar[$key]='Tidak Check Clock';
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
                                        $exact_in[$key]=1;
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
                                        // info($date);
                                        // info($dateAttd);
                                        // info($real_min_time_in);
                                        // info($real_time_in);
                                        // info('masuk sayang');
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
                                    $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($time_in);
                                    
                                    info($diffHoursTimePartMinIn);
                                    //mengetahui apabila jam yang ada melebihi toleransi pada shift.
                                    if($diffHoursTimePartMinIn<=$row_schedule_filter->shift->tolerant && $exact_in[$key] != 1){
                                        $exact_in[$key]= 2 ;
                                        info($masuk_awal);
                                        $login[$key]= $timePart;
                                        if (count($query_data) == 3 && $key > 0 ) {
                                            $exact_in[$key]= 1 ;
                                            $login[$key]= $timePart;
                                        }
                                        $array_masuk[$key]=$timePart;
                                    }                                      
                                }elseif($dateAttd > $real_max_time_out){
                                    $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($max_time_out);
                                    
                                    if($diffHoursTimePartMaxOut<=$row_schedule_filter->shift->tolerant){
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
                                $exact_in[1]=1;
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

                    $parse_date = Carbon::parse($date->format('Y-m-d'))->toDateString();
                
                    $query_data_leaveRequest = LeaveRequest::whereRaw("'$parse_date' BETWEEN start_date AND end_date ")
                                    ->where('account_id',$row_user->id)
                                    ->whereHas('leaveRequestShift', function($query) use($parse_date){
                                        $query->whereHas('employeeSchedule', function($query) use($parse_date){
                                            // $query->where('date',$parse_date);
                                        });
                                    })
                                    ->get();
                            
                    foreach($query_data_leaveRequest as $key=>$row_leave_request){
                        foreach($row_leave_request->leaveRequestShift as $key2=>$schedule_leave_request){
                        
                            if($schedule_leave_request->employeeSchedule->date == $parse_date){
                                
                                if($row_leave_request->leaveType->furlough_type == 1){
                                    

                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                                    $attendance_detail[(string)$date][$c]['in'][]='4';
                                    $attendance_detail[(string)$date][$c]['out'][]='4';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                    
                                }
                                if($row_leave_request->leaveType->furlough_type == 2){
                                    
                                    
                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                                    $attendance_detail[(string)$date][$c]['in'][]='4';
                                    $attendance_detail[(string)$date][$c]['out'][]='4';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                }
                                if($row_leave_request->leaveType->furlough_type == 3){
                                    
                                    
                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                                    $attendance_detail[(string)$date][$c]['in'][]='4';
                                    $attendance_detail[(string)$date][$c]['out'][]='4';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                }
                                
                                if($row_leave_request->leaveType->furlough_type == 6){
                                
                                
                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                                    $attendance_detail[(string)$date][$c]['in'][]='4';
                                    $attendance_detail[(string)$date][$c]['out'][]='4';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                }
                                if($row_leave_request->leaveType->furlough_type == 8){
                                    
                                    
                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                                    $attendance_detail[(string)$date][$c]['in'][]='4';
                                    $attendance_detail[(string)$date][$c]['out'][]='4';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                }
                                if($row_leave_request->leaveType->furlough_type == 9){
                                    $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                                    $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                                    $startTime = Carbon::createFromFormat('H:i', $row_leave_request->start_time);
                                
                                    $temp_is_late = $startTime->format('H:i:s');
                                
                                    $diffWithTimeIn = $startTime->diffInHours($time_in);
                                    
                                    $diffWithTimeOut = $startTime->diffInHours($time_out);
                                    if($diffWithTimeIn>$diffWithTimeOut){
                                        $time_out=$temp_is_late;
                                    }else{
                                        $time_in = $temp_is_late;
                                    }

                                    $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    $real_time_in =$date->format('Y-m-d') . ' ' . $time_in;
                                    $combinedDateTimeInCarbon = Carbon::parse($real_time_in);
                                    $real_min_time_in = $combinedDateTimeInCarbon->copy()->subHours($schedule_leave_request->employeeSchedule->shift->tolerant);
                                    
                                
                                    
                                    $max_time_out = Carbon::parse($time_out)->addHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                                    if($time_in>$time_out){
                                        $tambah1hari=$date->copy()->addDay();
                                    
                                        $real_time_out =$tambah1hari->format('Y-m-d'). ' ' . $time_out;
                                        
                                    }else{
                                        $real_time_out =$date->format('Y-m-d') . ' ' . $time_out;
                                    }
                                    
                                    $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                                    
                                    $real_max_time_out = $combinedDateTimeOutCarbon->copy()->addHours($schedule_leave_request->employeeSchedule->shift->tolerant);
                                    if($time_in>$time_out){
                                        
                                        $real_max_time_out->addDay();
                                    }
                                    //perhitungan baru utk jam masuk yng berubah
                                

                                    $attendance_detail[(string)$date][$c]['time_in'][]=$time_in;
                                    $attendance_detail[(string)$date][$c]['time_out'][]=$time_out;
                                    $attendance_detail[(string)$date][$c]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[(string)$date][$c]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[(string)$date][$c]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[(string)$date][$c]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[(string)$date][$c]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[(string)$date][$c]['logout']=$row_leave_request->leaveType->name;
                                    


                                    $query_attendance = Attendances::where(function ($query) use ($date,$cleanedNik) {
                                        $mulaiDate = Carbon::parse($date)->subDays(1)->startOfDay()->toDateTimeString();
                                        $akhirDate = Carbon::parse($date)->addDays(1)->endOfDay()->toDateTimeString();
                                        
                                        $query->where('date', '>=', $mulaiDate)
                                            ->where('date', '<=', $akhirDate)
                                            ->where('employee_no',$cleanedNik);
                                    })->orderBy('date')->get();
                                
                                    $masuk_awal = null;
                                    $muleh = null;
                                    $perlu_nambah = true;
                                    $ada_pulang = false;
                                    
                                    foreach($query_attendance as $row_attendance_filter){
                                        $dateAttd = Carbon::parse($row_attendance_filter->date);
                                    
                                        $timePart = $dateAttd->format('H:i:s');
                                        
                                        if ($dateAttd >= $real_min_time_in && $dateAttd <= $real_time_in) {
                                            $attendance_detail[(string)$date][$c]['in'][]='1';
                                            
                                                
                                            if($masuk_awal==null){
                                                $masuk_awal =$timePart;
                                                $attendance_detail[(string)$date][$c]['login']=$timePart;
                                            }elseif($masuk_awal > $timePart){
                                                $attendance_detail[(string)$date][$c]['login']=$timePart;
                                                $masuk_awal =$timePart;
                                            }
                                            $attendance_detail[(string)$date][$c]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                            $attendance_detail[(string)$date][$c]['perbedaan_jam_masuk'][]='';
                                            
                                            
                                        }elseif($dateAttd > $real_time_in && $dateAttd < $real_time_out){
                                            $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($time_in);
                                           
                                            $perlu_nambah = false;
                                            if($diffHoursTimePartMinIn<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                               
                                                $attendance_detail[(string)$date][$c]['in'][]='2';
                                                $attendance_detail[(string)$date][$c]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                            }elseif($perlu_nambah != false && $diffHoursTimePartMinIn>=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                                $attendance_detail[(string)$date][$c]['jam_masuk'][]='Tidak check Clock';
                                                $attendance_detail[(string)$date][$c]['in'][]='0';
                                            }                                      
                                        }elseif($dateAttd > $real_max_time_out){
                                            $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($max_time_out);
                                            info('masuk');
                                            info($dateAttd);
                                            info($diffHoursTimePartMaxOut.' time out');
                                            
                                        
                                            if($diffHoursTimePartMaxOut<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                                $array_keluar[$key]=$timePart;
                                                $ada_pulang=true;
                                            }
                                        }


                                        //perhitungan pulang
                                        if ($dateAttd >= $real_time_out && $dateAttd <= $real_max_time_out) {
                                            $attendance_detail[(string)$date][$c]['out'][]=1;
                                           
                                            $ada_pulang=true;
                                            if($muleh==null){
                                                $muleh=$timePart;
                                                $attendance_detail[(string)$date][$c]['logout']=$timePart;
                                                $attendance_detail[(string)$date][$c]['jam_keluar'][]=$time_out.' | '.$timePart .' | '.$max_time_out;
                                                $attendance_detail[(string)$date][$c]['perbedaan_jam_keluar'][]=''; 
                                            }elseif($muleh < $timePart){
                                                $muleh=$timePart;
                                                $attendance_detail[(string)$date][$c]['logout']=$timePart;
                                            }
                                            
                                        }
                                    }
                                    if($masuk_awal){//perhitungan toleransi untuk ijin telat?
                                        $pembandingdate = Carbon::parse($masuk_awal);
                                        $pembanding= $pembandingdate->format('H:i:s');
                                        $carbonTimeIn = Carbon::parse($time_in);
                                        
                                        if($pembanding > $time_in ){
                                            if(count($query_late_punishment)> 0){
                                            
                                                if($pembanding > $time_in && $pembanding <= Carbon::parse($time_in)->addMinutes($query_late_punishment[0]->minutes)->format('H:i:s')){
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['counter']++;
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['date'][]=Carbon::parse($date)->format('d/M/y');  
                                                }else{
                                                    foreach($query_late_punishment as $key=>$row_punish_type){
                                                        $newCarbonTime = Carbon::parse($time_in)->addMinutes($row_punish_type->minutes)->format('H:i:s');
                                                        if($key !=0 ){
                                                            
                                                            if($pembanding < Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s') && $pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key-1]->minutes)->format('H:i:s')){
                                                                $tipe_punish_counter[$query_late_punishment[$key-1]->code]['counter']++;
                                                                $tipe_punish_counter[$query_late_punishment[$key-1]->code]['date'][]=$date->format('d/M/y');
                                                                break;
                                                            }
                                                        }
                                                        if($key == count($query_late_punishment)){
                                                            if($pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s')&&$pembanding<Carbon::parse($time_in)->addHours(2)->format('H:i:s')){
                                                                $tipe_punish_counter[$row_punish_type->code]['counter']++;
                                                                $tipe_punish_counter[$row_punish_type->code]['date'][]=$date->format('d/M/y');
                                                            }else{
                                                                $tipe_punish_counter['over_t']['counter']++;
                                                                $tipe_punish_counter['over_t']['date'][]=$date->format('d/M/y');
                                                            }
                                                        }
                                                        
                                                    }
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }else{//hanya utk telat di iji
                                        
                                        if($perlu_nambah == true){
                                            
                                            $attendance_detail[(string)$date][$c]['in'][]='0';
                                            $attendance_detail[(string)$date][$c]['jam_masuk'][]='Tidak check Clock';
                                    
                                        }
                                    }
                                    
                                    if($ada_pulang == false){
                                        $attendance_detail[(string)$date][$c]['out'][]='0';
                                        $attendance_detail[(string)$date][$c]['jam_keluar'][]='Tidak check Clock';
                                    }
                                    
                                
                                }
                            }  
                        }
                    }
                    //special user previlegessss itunggg
                    foreach($attendance_detail[(string)$date][$c]['in'] as $j=>$row_data){
                        if($query_special){
                            if($query_special->type == 1){
                                $attendance_detail[(string)$date][$c]['in'][$j] = 1;
                                $attendance_detail[(string)$date][$c]['out'][$j] = 1;
                            }
                            if($query_special->type == 2){
                                if( $attendance_detail[(string)$date][$c]['in'][0] == '1' ||  $attendance_detail[(string)$date][$c]['out'][count( $attendance_detail[(string)$date][$c]['out'])-1]==1 ||  $attendance_detail[(string)$date][$c]['login'] != '' ||  $attendance_detail[(string)$date][$c]['logout'] != ''){
                                    $attendance_detail[(string)$date][$c]['in'][$j] = 1;
                                    $attendance_detail[(string)$date][$c]['out'][$j] = 1;  
                                }
                            }
    
                        }
                    }

                }
                
                $date->addDay();
            }
            foreach($attendance_detail as $key_row=>$row){
                
                foreach($row as $key_1=>$data){
                    
                    
                    if(count($data['in']) == 3){
                        if($data['in'][0] == '0' && $data['out'][2]==0){
                            foreach($data['in'] as $key_2=>$data_in){
                                $attendance_detail[$key_row][$key_1]['in'][$key_2] = 0;
                                $attendance_detail[$key_row][$key_1]['out'][$key_2] = 0;
                                $attendance_detail[$key_row][$key_1]['jam_masuk'][$key_2] = 'tidak check clock';
                                $attendance_detail[$key_row][$key_1]['jam_keluar'][$key_2] = 'tidak check clock';
                            }
                        }
                    }
                }
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

