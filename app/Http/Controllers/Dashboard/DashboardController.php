<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\ItemStock;
use App\Models\Attendances;
use App\Models\UserSpecial;
use App\Models\EmployeeSchedule;
use App\Models\Punishment;
use App\Models\OvertimeRequest;
use App\Models\LeaveRequest;
use App\Models\AttendanceMonthlyReport;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    protected $user;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->user = $user;
    }
    public function index()
    {
        $data = [
            'title'         => 'Dashboard',
            'content'       => 'admin.dashboard.main',
            /* 'itemcogs'      => ItemCogs::orderByDesc('date')->orderByDesc('id')->get(), */
            /* 'itemstocks'    => ItemStock::where('qty','>',0)->get(), */
            'user'          => $this->user,
        ];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        if(session('bo_reminder')){
            $query_reminder = Task::whereIn('id',session('bo_reminder'))->get();
            if($query_reminder){
                $data['reminder'] = $query_reminder;
            }
        }
        // Calculate the start and end dates
        $startDate = Carbon::create($currentYear, $currentMonth - 1, 21);
        $endDate = Carbon::create($currentYear, $currentMonth, 20);
        if ($endDate->isFuture()) {
            $endDate = Carbon::today();
        }
        // Retrieve employee schedules and attendances within the date range
        $schedule = EmployeeSchedule::join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
        ->where('employee_schedules.user_id', $this->user->employee_no)
        ->whereBetween('employee_schedules.date', [$startDate, $endDate])
        ->orderBy('shifts.time_in')
        ->select('employee_schedules.*') 
        ->get();
    
        $attendance = Attendances::where('employee_no', $this->user->employee_no)
        ->orderBy('date', 'desc') // Order by 'date' column in descending order
        ->limit(20) // Limit the result to 20 records
        ->get();

        $attendance_per_day=[];
        
        foreach ($attendance as $row_att) {
            $carbonDate = Carbon::parse($row_att->date);
            $matchingScheduleFirst = $schedule->where('date', $carbonDate->format('Y-m-d'))->first();
            $matchingScheduleLast = $schedule->where('date', $carbonDate->format('Y-m-d'))->last();
            if( $matchingScheduleFirst){
                $shift_in   = $matchingScheduleFirst->shift->time_in;
                $shift_out  = $matchingScheduleLast->shift->time_out;
            }else{
                $shift_in   = '';
                $shift_out  = '';
            }
            $attendance_per_day[] = [
                'date' => $carbonDate->format('d-m-Y'), // Format date as dd-mm-yyyy
                'time' => $carbonDate->format('h:i A'), // Format time as hh:mm AM/PM
                'schedulefirst' => $shift_in,
                'schedulelast'  => $shift_out,
            ];
        }
        
        $date = $startDate->copy();
        $date_leave_req = $startDate->copy();
        $attendance_detail=[];
        $total_telat_masuk=0;
        $total_tidak_check_m=0;
        $total_tidak_check_k=0;
        $total_absen=0;
        $counter_effective_day=0;
        
        $total_lembur = 0;
        $counter_absent=0;
        $counter_alpha=0;
        
        $total_tepat_masuk=0;
        $total_tepat_keluar=0;

        $counter_cuti = 0;
        $counter_sakit= 0;
        $counter_ijin = 0;
        $counter_dinas_luar = 0;
        $counter_cuti_kusus = 0;
        $counter_lain_lain = 0;
        $counter_dispen = 0;
        $counter_wfh = 0; 
        while ($date->lte($endDate)) {
                   
           
                     
            $cleanedNik = str_replace(' ', '', $this->user->employee_no);
            
            
     
            
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
            $shift_id=[];
            $different_masuk=[];
            $different_keluar=[];
            $tipe=[];
            $query_data = EmployeeSchedule::join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
                        ->whereDate('employee_schedules.date', $date->toDateString())
                        ->where('employee_schedules.user_id', $this->user->employee_no)
                        ->whereIn('employee_schedules.status', [1, 4 , 5])
                        ->orderBy('shifts.time_in') // Order by next_day (1 comes last)
                        ->select('employee_schedules.*') // Select the columns you need
                        ->get();
            $query_late_punishment = Punishment::where('place_id',$this->user->place_id)
                                            ->where('type','1')
                                            ->where('status','1')
                                            ->orderBy('minutes')
                                            ->get();
    
            $query_special = UserSpecial::where('user_id',$this->user->id)
                        ->where('start_date','<=', $date)
                        ->where('end_date','>=',$date)
                        ->where('status',1)
                        ->first();
            
            //perhitungan schedule biasa dari user pada tanggal yang ada di loop
            if($schedule){
                foreach($query_data as $key=> $row_schedule_filter){//perlusd
                    $query_lembur= null;
                    $lembur = 0;
                    $lembur_awal_shift = 0;
                    $time_in = $row_schedule_filter->shift->time_in;
                    $time_out = $row_schedule_filter->shift->time_out;
                    
                    if($row_schedule_filter->status == 5){
                        
                        $query_lembur = OvertimeRequest::where('schedule_id',$row_schedule_filter->id)
                        ->where('account_id',$this->user->id)->first();
                        if($query_lembur){
                           
                            if($time_in <= $query_lembur->time_in){//smpe dimana pas kurang dari awl shift
                                $lembur=1;
                                
                               
                                $time_out = $query_lembur->time_out;
                               
                            }
                            if($query_lembur->time_out <= $time_in){
                                $lembur_awal_shift = 1;
                                $time_in =$query_lembur->time_in;
                            }
                        }
                      
                        //dihitung dari time in lembur dan kapan terakhir orang tsb logout berapa jam yang didapat
                    }
                    $min_time_in = Carbon::parse($time_in)->subHours($row_schedule_filter->shift->tolerant)->toTimeString();
                    $real_time_in =$date->format('Y-m-d') . ' ' . $time_in;
                    $combinedDateTimeInCarbon = Carbon::parse($real_time_in);
                    $real_min_time_in = $combinedDateTimeInCarbon->copy()->subHours($row_schedule_filter->shift->tolerant);
               
                    
                    $max_time_out = Carbon::parse($time_out)->addHours($row_schedule_filter->shift->tolerant)->toTimeString();
                    if($time_in>$time_out){
                        $tambah1hari=$date->copy()->addDay();
                     
                        $real_time_out =$tambah1hari->format('Y-m-d'). ' ' . $time_out;
                        
                    }else{
                        $real_time_out =$date->format('Y-m-d') . ' ' . $time_out;
                    }
                    
                    $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                    
                    $real_max_time_out = $combinedDateTimeOutCarbon->copy()->addHours($row_schedule_filter->shift->tolerant);
                    if($time_in>$time_out){//ik disek digae op y ooo di buat untuk apabila max time out nya itu di hari yang sama tapi jam 00:00 seperti itu atau kurang dari jam masuknya jadie ditambahi tanggalnya jadi nextday ehey
                        
                        $real_max_time_out->addDay();
                    }
                   
                    if($row_schedule_filter->status == 4){
                        $counter_effective_day++;
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
                        $counter_effective_day++;
                        $login[$key]=null;
                        $logout=null;
                    
                        
    
                        $different_masuk[$key]='';
                        $different_keluar[$key]='';
                        
    
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
                        
                        //
                        foreach($query_attendance as $row_attendance_filter){
                            $dateAttd = Carbon::parse($row_attendance_filter->date);
                            
                            $timePart = $dateAttd->format('H:i:s');
                            
    
                            //perhitungan masuk tepat atau tidak dan pulang tepat atau tidak
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
                                
                            }if($dateAttd > $real_time_in && $dateAttd < $real_time_out){// tataru sini bisa haruse utk yang tidak ada ituuu
                                $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($time_in);
                               
                                if($row_schedule_filter->status == 5 && $query_lembur && $lembur_awal_shift == 0){////perhitungan untuk time in dimana lembur tidak masuk tapi ada jadwal lembur yang bergabung dengan jam
                                    $lembur = 0;
                                   
                                
                                    if($diffHoursTimePartMinIn<=$row_schedule_filter->shift->tolerant && $exact_in[$key] != 1){
                                        $exact_in[$key]= 2 ;
                                        
                                        $login[$key]= $timePart;
                                        if (count($query_data) == 3 && $key > 0 ) {
                                            $exact_in[$key]= 1 ;
                                            $login[$key]= $timePart;
                                        }
                                        $array_masuk[$key]=$timePart;
                                    } 
                                }
                                if($row_schedule_filter->status == 5 && $query_lembur && $lembur_awal_shift == 1){////perhitungan untuk time in dimana lembur tidak masuk tapi ada jadwal lembur yang bergabung dengan jam
                                  
                                    $time_in_temp = $row_schedule_filter->shift->time_in;
                                    $time_out_temp = $row_schedule_filter->shift->time_out;
                                    $real_time_in_temp =$date->format('Y-m-d') . ' ' . $time_in_temp;
                                    $real_time_out_temp =$date->format('Y-m-d') . ' ' . $time_out_temp;
                                    if($dateAttd > $real_time_in_temp && $dateAttd < $real_time_out_temp){
                                        $lembur = 0;
                                        $diffHoursTimePartMinInTemp = Carbon::parse($timePart)->diffInHours($time_in_temp);
                                        if($diffHoursTimePartMinInTemp<=$row_schedule_filter->shift->tolerant && $exact_in[$key] != 1){
                                            $exact_in[$key]= 2 ;
                                            
                                            $login[$key]= $timePart;
                                            if (count($query_data) == 3 && $key > 0 ) {
                                                $exact_in[$key]= 1 ;
                                                $login[$key]= $timePart;
                                            }
                                            $array_masuk[$key]=$timePart;
                                        }
                                    }else{
                                        $array_masuk[$key]=$timePart;
                                        if(!$login[$key]){
                                            $lembur = 1;
                                            $exact_in[$key]= 1 ;
                                           
                                            $login[$key]= $timePart;
                                            if (count($query_data) == 3 && $key > 0 ) {
                                                $exact_in[$key]= 1 ;
                                                $login[$key]= $timePart;
                                            }
                                            $array_masuk[$key]=$timePart;
                                        }
                                       
                                    }
                                    
                                   
                                }
                                //mengetahui apabila jam yang ada melebihi toleransi pada shift.
                                if($diffHoursTimePartMinIn<=$row_schedule_filter->shift->tolerant && $exact_in[$key] != 1 &&$row_schedule_filter->status != 5){
                                    $exact_in[$key]= 2 ;
                                    
                                    
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
                            if($dateAttd >= $real_time_out && $dateAttd <= $real_max_time_out){
                               
                                    
                                
                                
                                $exact_out [$key]= 1 ;
                                
                               
                                if(!$logout){
                                    $logout = $timePart;
                                    $muleh=$timePart;
                                    $array_keluar[$key]=$timePart;
                                    $different_keluar[$key]='';
                                }
                                if($logout<$timePart){
                                   
                                    
                                    $logout = $timePart;
                                    $muleh=$timePart;
                                    $array_keluar[$key]=$timePart;
                                    $different_keluar[$key]='';
                                }
                                
                            }
                            
                            if ($dateAttd <= $real_time_out && $dateAttd <= $real_max_time_out && $date->toDateString() == $dateAttd->toDateString()&&count($query_data)==1) {
                                
                                if($muleh==null && $date->toDateString() == $dateAttd->toDateString()){
                                    if($masuk_awal == null){
                                        $masuk_awal = $timePart;
                                    }else{
                                        
                                        if($masuk_awal != $timePart){
                                            $muleh=$timePart;
                                        }
                                    }
                                    
                                }elseif($muleh < $timePart){
                                    $muleh=$timePart;
                                    $array_keluar[$key]=$timePart;
                                    
                                }
                                if(!$logout&& $date->toDateString() == $dateAttd->toDateString()){
                                    
                                    if($timePart != $masuk_awal || $dateAttd > $real_max_time_out || $dateAttd > $real_time_in){
                                       
                                        $logout = $timePart;
                                    }
                                   
                                    if($login && $timePart != $masuk_awal){
                                    
                                        $array_keluar[$key]=$timePart;
                                    }
                                    $different_keluar[$key]='';
                                    
                                }  
                            }
                            
                        }
                        
                        $latestRecord = $query_attendance->last();
                        
                       
                        if($row_schedule_filter->status == 5 && $query_lembur &&  $lembur_awal_shift != 1){//perhitungan lemboer
                            
                            $time_in_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_in;
                            $time_out_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_out;
                    
                            if(Carbon::parse($latestRecord->date) < Carbon::parse($real_time_out)){
                               
                               
                              
                                $timeDifference = Carbon::parse($time_in_lembur)->diff($latestRecord->date);
                                
                   
                                $total_lembur+=$timeDifference->h;
    
                            }else{
                                $timeDifference = Carbon::parse($time_in_lembur)->diff(Carbon::parse($time_out_lembur));
                                
                                
                                $total_lembur+=$timeDifference->h;
                
                                
                            }
                        }
                        
                        if($row_schedule_filter->status == 5 && $query_lembur &&  $lembur_awal_shift == 1 && $exact_out[$key] == 1 && $masuk_awal){
                            $masuk_awal_combine = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' .$masuk_awal;
                            $time_in_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_in;
                            $time_out_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_out;
                            if(Carbon::parse($masuk_awal_combine) < Carbon::parse($real_time_in)){
                                
                              
                                $timeDifference = Carbon::parse($time_in_lembur)->diff(Carbon::parse($time_out_lembur));
                                
                               
                                $total_lembur+=$timeDifference->h;
    
                            
                            }else{
                                $timeDifference = Carbon::parse($masuk_awal_combine)->diff(Carbon::parse($time_out_lembur));
                                
                              
                                $total_lembur+=$timeDifference->h;
                               
                                
                            }
                        }
                        //perhitungan untuk time in dimana lembur tidak masuk tapi ada jadwal lembur yang bergabung dengan jam
                       
    
                        if(!$exact_in){// untuk memberi apabila dia tidak masuk memberi tanda kalau tidak ada checkclock
                            $exact_in[$key]=0;
                            
                        }
    
                        if(count($exact_in) == 3){
                            if($exact_in[0] == 0 && $exact_out[2] == 0){
                                foreach($exact_in as $key=>$row){
                                    $exact_in[$key]=0;
                                    $exact_out[$key]= 0;
                                }
                            }
                            
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
                                    $logout_real =$date->format('Y-m-d') . ' ' . $logout;
    
                                    if($logout_real<$real_time_out){
                                        $exact_out[$key]=0;
                                    }
                                }else{
                                   
                                    $exact_out[$key]= 0 ;
                                }
                                
                            }
                          
                            
                        }
                        if($key == 1 &&  count($query_data) == 2){//mengecek saat schedule yang di tengah dan jumlahnya dua dan mengecek kalau dia itu tidak check saat masuk dan check saat pulang
                            if($exact_out[$key]==1){
                                $exact_out[$key-1]=1;
                            }
                            if($exact_in[$key-1]==0 && !$query_lembur&& $exact_out[$key] !=1 ){
                                $exact_in[$key]=0;
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
                    $shift_id[]=$row_schedule_filter->shift->id;
                   
                    
    
                    if($query_special){
                       
                        if($query_special->type == 1){
                            foreach($exact_in as  $key_in=>$row_exact_in){
                                $exact_in[$key_in]=1;
                                $exact_out[$key_in]=1;
                            }
                            
                        } if($query_special->type == 2){
                            if($exact_in[0] == 1 || $exact_out[count($exact_out)-1]== 1){
                                foreach($exact_in as  $key_in=>$row_exact_in){
                                    $exact_in[$key_in]=1;
                                    $exact_out[$key_in]=1;
                                }
                                 
                            }
                        }
                    }
    
                }
            }
            
            
            
            $attendance_detail[]=[
                'date' => Carbon::parse($date)->format('d/m/Y'),
                'user_no'=>$this->user->id,
                'user' =>$this->user->name,
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
                'shift_id'=>$shift_id,
            ];
            //perhitungan cuti atau ijin
       
            foreach($exact_in as $key=>$row_exact){
                
                $all_exact_in[]=$row_exact;
                $all_exact_out[]=$exact_out[$key];
            }
            
            $lanjoet=1;
           
            foreach($exact_in as $key=>$row_arrive){
               
                if($row_arrive == 1){
                    $total_tepat_masuk++;
                }
                if($row_arrive == 2){
                    $total_telat_masuk++;
                }  
                if($row_arrive == 0 && $exact_out[$key] == 1){
                    $total_tidak_check_m++;
                    $date_arrived_forget[]=Carbon::parse($date)->format('d/m/Y');
                    
                }
                if($row_arrive == 0 && $exact_out[$key] == 0){
                    $total_absen++;
                }
            }
            
           
            if($lanjoet==1){
                  
                foreach($exact_out as $key3=>$row_out){
                    if($row_out == 1){
                        $total_tepat_keluar++;
                    }
                    if($row_out==0 && $exact_in[$key3] == 1){
                        $total_tidak_check_k++;
                        $date_out_forget[]=Carbon::parse($date)->format('d/m/Y');
                        break;
                    }
                    
                }
            }
            
            
            
            $date->addDay();
            
        }
        $counter_ps= 0;
        while($date_leave_req->lte($endDate)){
            $exact_in=[];
            $exact_out=[];
            $parse_date = Carbon::parse($date_leave_req->format('Y-m-d'))->toDateString();
           
            $query_data_leaveRequest = LeaveRequest::whereRaw("'$parse_date' BETWEEN start_date AND end_date ")
                            ->where('account_id',$this->user->id)
                            ->where('status', 2)
                            ->whereHas('leaveRequestShift', function($query) use($parse_date){
                                $query->whereHas('employeeSchedule', function($query) use($parse_date){
                                    // $query->where('date',$parse_date);
                                });
                            })
                            ->get();
            $query_special = UserSpecial::where('user_id',$this->user->id)
                        ->where('start_date','<=', $date_leave_req)
                        ->where('end_date','>=',$date_leave_req)
                        ->where('status',1)
                        ->first();
            
            foreach($query_data_leaveRequest as $key=>$row_leave_request){
                foreach($row_leave_request->leaveRequestShift as $key2=>$schedule_leave_request){
                   
                    if($schedule_leave_request->employeeSchedule->date == $parse_date){
                        
                        if($row_leave_request->leaveType->furlough_type == 1){
                           
                            $counter_cuti++;
                            $counter_effective_day++;

                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $exact_in[]='4';
                            $exact_out[]= '4';
                              $attendance_detail[$counter_ps]['in'][]='4';
                              $attendance_detail[$counter_ps]['out'][]='4';
                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                            
                        }
                        if($row_leave_request->leaveType->furlough_type == 2){
                            $counter_sakit++;
                            $counter_effective_day++;
                            
                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $exact_in[]='4';
                            $exact_out[]= '4';
                              $attendance_detail[$counter_ps]['in'][]='4';
                              $attendance_detail[$counter_ps]['out'][]='4';
                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                        }
                        if($row_leave_request->leaveType->furlough_type == 3){
                            $counter_cuti_kusus++;
                            $counter_effective_day++;
                            
                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $exact_in[]='4';
                            $exact_out[]= '4';
                              $attendance_detail[$counter_ps]['in'][]='4';
                              $attendance_detail[$counter_ps]['out'][]='4';
                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                        }
                        if($row_leave_request->leaveType->furlough_type == 4){
                            $counter_dinas_luar++;
                            $counter_effective_day++;
                        }//no 4 dan 5 dinas dan wfh perlu dibuat sign in
                        if($row_leave_request->leaveType->furlough_type == 5){
                            $counter_wfh++;
                            $counter_effective_day++;
                        }
                        if($row_leave_request->leaveType->furlough_type == 6){
                            $counter_dispen++;
                            $counter_effective_day++;
                          
                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $exact_in[]='4';
                            $exact_out[]= '4';
                              $attendance_detail[$counter_ps]['in'][]='4';
                              $attendance_detail[$counter_ps]['out'][]='4';
                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                        }
                        if($row_leave_request->leaveType->furlough_type == 8){
                            $counter_ijin++;
                            $counter_effective_day++;
                            $exact_in[]='4';
                            $exact_out[]= '4';
                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $max_time_out = Carbon::parse($time_out)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();

                              $attendance_detail[$counter_ps]['in'][]='4';
                              $attendance_detail[$counter_ps]['out'][]='4';
                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                        }
                        if($row_leave_request->leaveType->furlough_type == 9){
                            $counter_effective_day++;//perlusd
                            $time_in = $schedule_leave_request->employeeSchedule->shift->time_in;
                            $time_out = $schedule_leave_request->employeeSchedule->shift->time_out;
                            $startTime = Carbon::createFromFormat('H:i', $row_leave_request->start_time);
                            
                            $temp_is_late = $startTime->format('H:i:s');
                           
                            $diffWithTimeIn = $startTime->diffInHours($time_in);
                            
                            $diffWithTimeOut = $startTime->diffInHours($time_out);
                            if($diffWithTimeIn>$diffWithTimeOut){
                                $time_out = $temp_is_late;
                                // $total_tidak_pulang++;
                            }else{
                                $time_in = $temp_is_late;
                                $total_telat_masuk++;
                            }

                            $min_time_in = Carbon::parse($time_in)->subHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $real_time_in =$date_leave_req->format('Y-m-d') . ' ' . $time_in;
                            $combinedDateTimeInCarbon = Carbon::parse($real_time_in);
                            $real_min_time_in = $combinedDateTimeInCarbon->copy()->subHours($schedule_leave_request->employeeSchedule->shift->tolerant);
                            
                          
                            $max_time_out = Carbon::parse($time_out)->addHours($schedule_leave_request->employeeSchedule->shift->tolerant)->toTimeString();
                            $real_time_out =$date_leave_req->format('Y-m-d') . ' ' . $time_out;
                            $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                            $real_max_time_out = $combinedDateTimeOutCarbon->copy()->addHours($schedule_leave_request->employeeSchedule->shift->tolerant);
                            //perhitungan baru utk jam masuk yng berubah
                           

                              $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                              $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]='';
                              $attendance_detail[$counter_ps]['time_in'][]=$time_in;
                              $attendance_detail[$counter_ps]['time_out'][]=$time_out;
                              $attendance_detail[$counter_ps]['min_time_in'][]=$min_time_in;
                              $attendance_detail[$counter_ps]['max_time_out'][]=$max_time_out;
                              $attendance_detail[$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                              $attendance_detail[$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                              $attendance_detail[$counter_ps]['login']=$row_leave_request->leaveType->name;
                              $attendance_detail[$counter_ps]['logout']=$row_leave_request->leaveType->name;
                            


                            $query_attendance = Attendances::where(function ($query) use ($date_leave_req,$cleanedNik) {
                                $mulaiDate = Carbon::parse($date_leave_req)->subDays(1)->startOfDay()->toDateTimeString();
                                $akhirDate = Carbon::parse($date_leave_req)->addDays(1)->endOfDay()->toDateTimeString();
                                
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
                                if($masuk_awal==null){
                                    if ($dateAttd >= $real_min_time_in && $dateAttd <= $real_time_in) {
                                        $exact_in[]='1';
                                        
                                          $attendance_detail[$counter_ps]['in'][]='1';
                                     
                                        if($masuk_awal==null){
                                            $masuk_awal =$timePart;
                                              $attendance_detail[$counter_ps]['login']=$timePart;
                                        }elseif($masuk_awal > $timePart){
                                              $attendance_detail[$counter_ps]['login']=$timePart;
                                            $masuk_awal =$timePart;
                                        }
                                       
                                          $attendance_detail[$counter_ps]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                          $attendance_detail[$counter_ps]['perbedaan_jam_masuk'][]='';
                                        
                                        
                                    }elseif($dateAttd > $real_time_in && $dateAttd < $real_time_out){
                                        $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($time_in);
                                        if($masuk_awal==null){
                                            $masuk_awal=$timePart;
                                        }
                                        $perlu_nambah = false;
                                        if($diffHoursTimePartMinIn<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                            $exact_in[]='2';
                                            
                                              $attendance_detail[$counter_ps]['in'][]='2';
                                              $attendance_detail[$counter_ps]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                        }elseif($perlu_nambah != false && $diffHoursTimePartMinIn>=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                            $exact_in[]='0';
                                              $attendance_detail[$counter_ps]['jam_masuk'][]='';
                                              $attendance_detail[$counter_ps]['in'][]='0';
                                        }                                      
                                    }elseif($dateAttd > $real_max_time_out){
                                        $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($max_time_out);

                                        if($diffHoursTimePartMaxOut<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                              $attendance_detail[$counter_ps]['jam_keluar'][]=$time_out.' | '.$timePart .' | '.$max_time_out;
                                            $ada_pulang=true;
                                        }
                                    }
                                }
                                


                                //perhitungan pulang
                                if ($dateAttd >= $real_time_out && $dateAttd <= $real_max_time_out) {
                                      $attendance_detail[$counter_ps]['out'][]=1;
                                    $exact_out[]='1';
                                 
                                    $ada_pulang=true;
                                    if($muleh==null){
                                        $muleh=$timePart;
                                          $attendance_detail[$counter_ps]['logout']=$timePart;
                                          $attendance_detail[$counter_ps]['jam_keluar'][]=$time_out.' | '.$timePart .' | '.$max_time_out;
                                          $attendance_detail[$counter_ps]['perbedaan_jam_keluar'][]=''; 
                                    }elseif($muleh < $timePart){
                                        $muleh=$timePart;
                                          $attendance_detail[$counter_ps]['logout']=$timePart;
                                    }
                                     
                                }
                            }
                            // if($query_special){
                            //     if($query_special->type == 1){
                            //         foreach($exact_in as  $key_in=>$row_exact_in){
                            //             $exact_in[$key_in]=1;
                            //             $exact_out[$key_in]=1;
                            //         }
                                    
                            //     } if($query_special->type == 2){
                            //         if($exact_in[0] == 1 || $exact_out[count($exact_out)-1]== 1){
                            //             foreach($exact_in as  $key_in=>$row_exact_in){
                            //                 $exact_in[$key_in]=1;
                            //                 $exact_out[$key_in]=1;
                            //             }
                                         
                            //         }
                            //     }else{
                            //         if($masuk_awal){//perhitungan toleransi punya specialll?
    
                            //             $pembandingdate = Carbon::parse($masuk_awal);
                            //             if(count($query_data)==2 && $key == 1){
                            //                 $currentSchedule = $query_data[1];
                            //                 $previousSchedule = $query_data[0];
                                            
                            //                 $currentTimeIn = Carbon::parse($currentSchedule->shift->time_in);
                            //                 $previousTimeIn = Carbon::parse($previousSchedule->shift->time_out);
                                            
                            //                 $timeDifference = $currentTimeIn->diffInHours($previousTimeIn);
                            //                 if($timeDifference >= 2){
                            //                     $pembandingdate = Carbon::parse($login[1]);
                            //                 }
                            //             }
                                        
                            //             $pembanding= $pembandingdate->format('H:i:s');
                            //             $carbonTimeIn = Carbon::parse($time_in);
                                        
                            //             if($pembanding > $time_in ){
                            //                 if(count($query_late_punishment)> 0){
                                                
                            //                     if($pembanding > $time_in && $pembanding <= Carbon::parse($time_in)->addMinutes($query_late_punishment[0]->minutes)->format('H:i:s')){
                            //                         if (($max_punish_id != null && $limit_temp > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[$key]->code]['punish_id']&&($key_id_punish!=null && $key <= $key_id_punish))) {
                            //                             $limit_temp--;
                            //                             info($pembanding);
                            //                             foreach ($exact_in as $key_in => $row_ins) {
                            //                                 $exact_in[$key_in] = 1;
                            //                             }
                            //                             break;  
                            //                         }
                            //                         else{
                            //                             $tipe_punish_counter[$query_late_punishment[0]->code]['counter']++;
                            //                             $tipe_punish_counter[$query_late_punishment[0]->code]['date'][]=Carbon::parse($date_leave_req)->format('d/M/y');  
                            //                         }
                            //                     }else{
                            //                         foreach($query_late_punishment as $key=>$row_punish_type){
                            //                             $newCarbonTime = Carbon::parse($time_in)->addMinutes($row_punish_type->minutes)->format('H:i:s');
                            //                             if($key !=0 ){
                                                            
                            //                                 if($pembanding < Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s') && $pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key-1]->minutes)->format('H:i:s')){
                            //                                     if (($max_punish_id != null && $limit_temp > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[$key]->code]['punish_id'] &&($key_id_punish!=null && $key <= $key_id_punish))) {
                            //                                         $limit_temp--;
                            //                                        
                            //                                         foreach ($exact_in as $key_in => $row_ins) {
                            //                                             $exact_in[$key_in] = 1;
                            //                                         }
                            //                                         break;  
                            //                                     }else{
                            //                                         $tipe_punish_counter[$query_late_punishment[$key]->code]['counter']++;
                            //                                         $tipe_punish_counter[$query_late_punishment[$key]->code]['date'][]=$date_leave_req->format('d/M/y');
                            //                                         break;
                            //                                     }
                            //                                 }
                            //                             }
                            //                             if($key == count($query_late_punishment)){
                            //                                 if($pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s')&&$pembanding<Carbon::parse($time_in)->addHours(2)->format('H:i:s')){
                            //                                     if (($max_punish_id != null && $limit_temp > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[$key]->code]['punish_id']&&($key_id_punish!=null && $key <= $key_id_punish))) {
                            //                                       
                            //                                         $limit_temp--;
                            //                                         foreach ($exact_in as $key_in => $row_ins) {
                            //                                             $exact_in[$key_in] = 1;
                            //                                         }
                            //                                         break;  
                            //                                     }else{
                            //                                         $tipe_punish_counter[$row_punish_type->code]['counter']++;
                            //                                         $tipe_punish_counter[$row_punish_type->code]['date'][]=$date_leave_req->format('d/M/y');
                            //                                         break;  
                            //                                     }
                            //                                 }else{
                            //                                     $tipe_punish_counter['over_t']['counter']++;
                            //                                     $tipe_punish_counter['over_t']['date'][]=$date_leave_req->format('d/M/y');
                            //                                 }
                            //                             }
                                                        
                            //                         }
                            //                     }
                                                
                            //                 }
                                            
                            //             }
                                        
                            //         }else{//hanya utk telat di ijin
                            //             if($perlu_nambah == true){
                            //                 $exact_in[]=0;
                            //                   $attendance_detail[$counter_ps]['in'][]='0';
                            //                   $attendance_detail[$counter_ps]['jam_masuk'][]='';
                                    
                            //             }
                            //         }
                            //     }
                            // }else{
                            //     $limit_temp =0;
                            //     if($masuk_awal){//perhitungan toleransi untuk ijin telat?
                            //         $pembandingdate = Carbon::parse($masuk_awal);
                            //         $pembanding= $pembandingdate->format('H:i:s');
                            //         $carbonTimeIn = Carbon::parse($time_in);
                                    
                            //         if($pembanding > $time_in ){
                            //             if(count($query_late_punishment)> 0){
                                        
                            //                 if($pembanding > $time_in && $pembanding <= Carbon::parse($time_in)->addMinutes($query_late_punishment[0]->minutes)->format('H:i:s')){
                            //                     $tipe_punish_counter[$query_late_punishment[0]->code]['counter']++;
                            //                     $tipe_punish_counter[$query_late_punishment[0]->code]['date'][]=Carbon::parse($date)->format('d/M/y');  
                            //                 }else{
                            //                     foreach($query_late_punishment as $key=>$row_punish_type){
                            //                         $newCarbonTime = Carbon::parse($time_in)->addMinutes($row_punish_type->minutes)->format('H:i:s');
                            //                         if($key !=0 ){
                                                        
                            //                             if($pembanding < Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s') && $pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key-1]->minutes)->format('H:i:s')){
                            //                                 $tipe_punish_counter[$query_late_punishment[$key-1]->code]['counter']++;
                            //                                 $tipe_punish_counter[$query_late_punishment[$key-1]->code]['date'][]=$date->format('d/M/y');
                            //                                 break;
                            //                             }
                            //                         }
                            //                         if($key == count($query_late_punishment)-1){
                            //                             if($pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s')&&$pembanding<Carbon::parse($time_in)->addHours(2)->format('H:i:s')){
                            //                                 $tipe_punish_counter[$row_punish_type->code]['counter']++;
                            //                                 $tipe_punish_counter[$row_punish_type->code]['date'][]=$date->format('d/M/y');
                            //                             }else{
                            //                                 $tipe_punish_counter['over_t']['counter']++;
                            //                                 $tipe_punish_counter['over_t']['date'][]=$date->format('d/M/y');
                            //                             }
                            //                         }
                                                    
                            //                     }
                            //                 }
                                            
                            //             }
                                        
                            //         }
                                    
                            //     }else{//hanya utk telat di ijin
                            //         if($perlu_nambah == true){
                            //             $exact_in[]=0;
                            //               $attendance_detail[$counter_ps]['in'][]='0';
                            //               $attendance_detail[$counter_ps]['jam_masuk'][]='';
                                
                            //         }
                            //     }
                            // }
                           
                            if($perlu_nambah == true){
                                $exact_in[]=0;
                                    $attendance_detail[$counter_ps]['in'][]='0';
                                    $attendance_detail[$counter_ps]['jam_masuk'][]='';
                        
                            }
                            
                            if($ada_pulang == false){
                                $exact_out[]='0';
                                  $attendance_detail[$counter_ps]['out'][]='0';
                                  $attendance_detail[$counter_ps]['jam_keluar'][]='';
                            }
                       
                           
                        }
                    }  
                }
            }
            $lanjoet=1;
           
            foreach($exact_in as $key=>$row_exact){
              
                $all_exact_in[]=$row_exact;
                $all_exact_out[]=$exact_out[$key];
            }
            foreach($exact_in as $key=>$row_arrive){
                if($row_arrive == 0 && $exact_out[$key] == 1){
                    $date_arrived_forget[]=Carbon::parse($date_leave_req)->format('d/m/Y');
                    
                }
            }
            if($lanjoet==1){
                
                foreach($exact_out as $row_out){
                    if($row_out==0 && $exact_in[$key] == 1){
                        $date_out_forget[]=Carbon::parse($date_leave_req)->format('d/m/Y');
                        break;
                    }
                }
            }
            $counter_ps++;
            $date_leave_req->addDay();
        }
        
        $data['attendance'] = $attendance_detail;
        $data['attendance_perday'] = $attendance_per_day;
        $data['tepatkeluar'] = $total_tepat_keluar;
        $data['tepatmasuk'] = $total_tepat_masuk;
        $data['terlambat'] = $total_telat_masuk;
        $data['total_absen'] = $total_absen;
        $data['total_tidak_datang'] = $total_tidak_check_m;
        $data['total_tidak_pulang'] = $total_tidak_check_k;
        $data['attendance_count'] = $counter_effective_day;
        $data['start_date']= $startDate->format('d/m/Y');
        $data['end_date']=  $endDate->format('d/m/Y');
        $data['total_telat_masuk']=$total_telat_masuk;
        $data['counter_cuti'] = $counter_cuti;
        $data['counter_sakit'] = $counter_sakit;
        $data['counter_ijin'] = $counter_ijin;
        $data['counter_dinas_luar'] = $counter_dinas_luar;
        $data['counter_cuti_kusus'] = $counter_cuti_kusus;
        $data['counter_lain_lain'] = $counter_lain_lain;
        $data['counter_dispen'] = $counter_dispen;
        $data['counter_wfh'] = $counter_wfh;

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function changePeriod(Request $request){
        $attendance_monthly_user = AttendanceMonthlyReport::where('period_id',$request->id)
        ->where('user_id',session('bo_id'))->first();
     
        if($attendance_monthly_user){
            $attendance = Attendances::where('employee_no', $this->user->employee_no)
            ->whereBetween('date', [$attendance_monthly_user->period->start_date, $attendance_monthly_user->period->end_date])
            ->orderBy('date', 'desc') // Order by 'date' column in descending order
            ->limit(20) // Limit the result to 20 records
            ->get();
            $schedule = EmployeeSchedule::join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
            ->where('employee_schedules.user_id', $this->user->employee_no)
            ->whereBetween('employee_schedules.date', [$attendance_monthly_user->period->start_date, $attendance_monthly_user->period->end_date])
            ->orderBy('shifts.time_in')
            ->select('employee_schedules.*') 
            ->get();
            foreach ($attendance as $row_att) {
                $carbonDate = Carbon::parse($row_att->date);
                $matchingScheduleFirst = $schedule->where('date', $carbonDate->format('Y-m-d'))->first();
                $matchingScheduleLast = $schedule->where('date', $carbonDate->format('Y-m-d'))->last();
                if( $matchingScheduleFirst){
                    $shift_in   = $matchingScheduleFirst->shift->time_in;
                    $shift_out  = $matchingScheduleLast->shift->time_out;
                }else{
                    $shift_in   = '';
                    $shift_out  = '';
                }
                $attendance_per_day[] = [
                    'date' => $carbonDate->format('d-m-Y'), // Format date as dd-mm-yyyy
                    'time' => $carbonDate->format('h:i A'), // Format time as hh:mm AM/PM
                    'schedulefirst' => $shift_in,
                    'schedulelast'  => $shift_out,
                ];
            }
            $data['attendance'] = $attendance;
            $data['attendance_perday'] = $attendance_per_day;
            $data['tepatkeluar'] = $attendance_monthly_user->out_on_time;
            $data['tepatmasuk'] = $attendance_monthly_user->arrived_on_time;
            $data['terlambat'] = $attendance_monthly_user->late;
            $data['total_absen'] = $attendance_monthly_user->alpha;
            $data['total_tidak_datang'] = $attendance_monthly_user->arrived_forget;
            $data['total_tidak_pulang'] =$attendance_monthly_user->out_log_forget;
            $data['attendance_count'] = $attendance_monthly_user->effective_day;
            $data['start_date']= Carbon::parse($attendance_monthly_user->period->start_date)->format('d/m/Y');
            $data['end_date']=  Carbon::parse($attendance_monthly_user->period->end_date)->format('d/m/Y');
            $data['total_telat_masuk']=$attendance_monthly_user->late;
            $data['counter_cuti'] = $attendance_monthly_user->furlough;
            $data['counter_sakit'] = $attendance_monthly_user->sick;
            $data['counter_ijin'] = $attendance_monthly_user->permit;
            $data['counter_dinas_luar'] = $attendance_monthly_user->outstation;
            $data['counter_cuti_kusus'] = $attendance_monthly_user->special_occassion;
            $data['counter_lain_lain'] = $attendance_monthly_user->special_occasion;
            $data['counter_dispen'] = $attendance_monthly_user->dispen;
            $data['counter_wfh'] = $attendance_monthly_user->wfh;
        }else{
            $data['attendance'] = null;
            $data['attendance_perday'] = null;
            $data['tepatkeluar'] =  0;
            $data['tepatmasuk'] =  0;
            $data['terlambat'] =  0;
            $data['total_absen'] =  0;
            $data['total_tidak_datang'] =  0;
            $data['total_tidak_pulang'] =  0;
            $data['attendance_count'] =  0;
            $data['start_date'] =  null;
            $data['end_date'] =  null;
            $data['total_telat_masuk'] = 0;
            $data['counter_cuti'] = 0;
            $data['counter_sakit'] = 0;
            $data['counter_ijin'] = 0;
            $data['counter_dinas_luar'] = 0;
            $data['counter_cuti_kusus'] = 0;
            $data['counter_lain_lain'] = 0;
            $data['counter_dispen'] = 0;
            $data['counter_wfh'] = 0;
        }

        $response = [
            'status'    => 200,
            'message'   => $data,
        ];
        return response()->json($response);
    }
}
