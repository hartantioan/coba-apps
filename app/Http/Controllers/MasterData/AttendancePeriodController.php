<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportPeriod;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceDailyReports;
use App\Models\AttendanceMonthlyReport;
use App\Models\AttendancePeriod;
use App\Models\AttendancePunishment;
use App\Models\Attendances;
use App\Models\EmployeeSchedule;
use App\Models\Place;
use App\Models\PresenceReport;
use App\Models\Punishment;
use App\Models\User;
use App\Models\UserAbsensiMesin;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AttendancePeriodController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Aset',
            'content'       => 'admin.master_data.attendance_period',
            'place'         => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'name',
            'start_date',
            'end_date',
            'plant_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = AttendancePeriod::count();
        
        $query_data = AttendancePeriod::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = AttendancePeriod::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                if($val->status == '2'){
                    $btn = 
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Laporan Harian" onclick="reportDaily(' . $val->id . ')"><i class="material-icons dp48" style="color:black">assignment</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2 btn-small" data-popup="tooltip" title="Laporan Presensi" onclick="reportPresence(' . $val->id . ')"><i class="material-icons dp48" style="color:black">directions_walk</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Laporan Monthly" onclick="goToMonth(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons dp48" style="color:black">event</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Excel" onclick="exportExcel(`'.$val->id.'`)"><i class="material-icons dp48" style="color:black">view_list</i></button>
                    ';
                      
                }else{
                    $btn = 
                '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown white-text btn-small" data-popup="tooltip" title="Close Period" onclick="closed(' . $val->id . ')"><i class="material-icons dp48">close</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>   
                ';
                }
                
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->start_date,
                    $val->end_date,
                    $val->plant->name,
                    $btn
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function close(Request $request){
        $attendance_period = AttendancePeriod::find($request->id)->first();
        
        $start_time = microtime(true);
        $start_date = Carbon::parse($attendance_period->start_date);
        $end_date = Carbon::parse($attendance_period->end_date);
        
        $user_data = User::where(function($query) use ( $request) {
            $query->where('type','1');
            // $query->whereIn('nik', [' 123034']);
                
            })->get();
        $user_datas = [];
        $attendance_detail = [];
        $user_counter_effective_day = [];
        $user_counter_absent=[];
        $user_counter_arrived_on_time=[];
        $user_counter_out_on_time=[];
        if($start_date && $end_date){
            foreach($user_data as $c=>$row_user){
                $user_id = $row_user->employee_no;
                $date = $start_date->copy();
                $counter_effective_day=0;
                $counter_absent=0;
                $counter_alpha=0;
                $counter_arrived_on_time=0;
                $counter_arrived_forget=0;
                $counter_out_on_time=0;
                $counter_out_forget=0;
                $date_out_forget=[];
                $date_arrived_forget=[];


                $all_exact_in=[];
                $all_exact_out=[];  
                $query_late_punishment = Punishment::where('place_id',$row_user->place_id)
                                            ->where('type','1')
                                            ->where('status','1')
                                            ->get();
                $tipe_punish_counter=[];

                $query_tidak_check_masuk = Punishment::where('place_id',$row_user->place_id)
                                        ->where('type','2')
                                        ->where('status','1')
                                        ->first();

                $query_tidak_check_pulang = Punishment::where('place_id',$row_user->place_id)
                                        ->where('type','3')
                                        ->where('status','1')
                                        ->first();
             
                foreach($query_late_punishment as $row_type){
                    $tipe_punish_counter[$row_type->code]['counter']=0;
                    $tipe_punish_counter[$row_type->code]['date']=[]; 
                    $tipe_punish_counter[$row_type->code]['uid']=$row_user->id;
                    $tipe_punish_counter[$row_type->code]['punish_id']=$row_type->id;
                    $tipe_punish_counter[$row_type->code]['price']=$row_type->price;   
                }  
                while ($date->lte($end_date)) {
                    $user_datas[$c]['user_name']=$row_user->nama;
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
                    $shift_id=[];
                    $different_masuk=[];
                    $different_keluar=[];
                    $tipe=[];
                    foreach($query_data as $key=> $row_schedule_filter){
                        $exact_in[$key]=0;
                        $exact_out[$key]=0;
                        $counter_effective_day++;
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
                        $shift_id[]=$row_schedule_filter->shift->id;
                       
                        if($masuk_awal){
                            $pembandingdate = Carbon::parse($masuk_awal);
                            $pembanding= $pembandingdate->format('H:i:s');
                            $carbonTimeIn = Carbon::parse($time_in);
                            
                            if($pembanding > $time_in ){
                                if($query_late_punishment){
                                    
                                    
                                    
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
                            
                        }

                    }
                    
                    foreach($exact_in as $key=>$row_exact){
                     
                        $all_exact_in[]=$row_exact;
                        $all_exact_out[]=$exact_out[$key];
                    }
                    
                    $attendance_detail[$row_user->name][]=[
                        'date' => Carbon::parse($date)->format('d/m/Y'),
                        'user_no'=>$row_user->id,
                        'user' =>$row_user->name,
                        'in'   => $exact_in,
                        'out'  => $exact_out,
                        'login'=> $masuk_awal,
                        'logout'=>$muleh,
                        'tipe' => $tipe,
                        'punish_type'=>$tipe_punish_counter,
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
                    $lanjoet=1;
                    
                    foreach($exact_in as $key=>$row_arrive){
                        if($row_arrive == 0 && $exact_out[$key] == 1){
                            $date_arrived_forget[]=Carbon::parse($date)->format('d/m/Y');
                            $lanjoet=0;
                            break;
                        }
                    }
                    if($lanjoet==1){
                        
                        foreach($exact_out as $row_out){
                            if($row_out==0 && $exact_in[$key] == 1){
                                $date_out_forget[]=Carbon::parse($date)->format('d/m/Y');
                                break;
                            }
                        }
                    }
                    
                    
                    $date->addDay();
                    
                }
               
                foreach($all_exact_in as $key=>$row_exact){
                    
                    
                    if($row_exact==1){
                        $counter_arrived_on_time++;
                    }if($row_exact != 1 && $all_exact_out[$key] == 1){
                        $counter_arrived_forget++;
                    }
                    if($all_exact_out[$key]==1){
                        $counter_out_on_time++;
                    }if($all_exact_out[$key] != 1 && $row_exact == 1){
                        $counter_out_forget++;
                    }
                    if($row_exact==1 && $all_exact_out[$key] == 1){
                        $counter_absent++;
                    }if($row_exact == 0 && $all_exact_out[$key] ==0){
                        $counter_alpha++;
                    }
                }
                $user_datas[$c]["arrived_on_time"]=$counter_arrived_on_time;
                $user_datas[$c]["out_on_time"]=$counter_out_on_time;
                $user_datas[$c]["effective_day"]=$counter_effective_day;
                $user_datas[$c]["alpha"]=$counter_alpha;
                $user_datas[$c]["absent"]=$counter_absent;
                $user_datas[$c]["out_forget"]=$counter_out_forget;
                $user_datas[$c]["arrived_forget"]=$counter_arrived_forget;

                $query = AttendanceMonthlyReport::create([
                    'user_id'                  => $row_user->id,
                    'period_id'			        => $attendance_period->id,
                    'effective_day'             => $counter_effective_day,
                    'absent'           => $counter_absent,
                    // 'special_occasion'           => $request->plant_id,
                    // 'sick'           => $request->plant_id,
                    // 'outstation'           => $request->plant_id,
                    // 'furlough'           => $request->plant_id,
                    // 'dispen'           => $request->plant_id,
                    // 'alpha'           => $request->plant_id,
                    // 'wfh'           => $request->plant_id,
                    'arrived_on_time'           => $counter_arrived_on_time,
                    'out_on_time'           => $counter_out_on_time,
                    'out_log_forget'           => $counter_out_forget,
                    'arrived_forget'           => $counter_arrived_forget,
                ]);
                DB::beginTransaction();
                try {
                    $query_close = AttendancePeriod::find($request->id);
                    $query_close->status            = "2";
                    $query_close->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                $counter_user_monthly[$row_user->employee_no]=$tipe_punish_counter;
               
                if(count($date_arrived_forget) != 0){
              
                    $query_presence_report = AttendancePunishment::create([
                        'user_id'                  => $row_user->id,
                        'period_id'                => $request->id,
                        'employee_id'              => session('bo_id'),
                        'punishment_id'            => $query_tidak_check_masuk->id,
                        'frequent'                 => $counter_arrived_forget,
                        'total'                    => $counter_arrived_forget*$query_tidak_check_masuk->price,
                        'dates'                    => implode(',',$date_arrived_forget)
                    ]);
                }
                if(count($date_out_forget) != 0){
                    $query_presence_report = AttendancePunishment::create([
                        'user_id'                  => $row_user->id,
                        'period_id'                => $request->id,
                        'employee_id'              => session('bo_id'),
                        'punishment_id'            => $query_tidak_check_pulang->id,
                        'frequent'                 => $counter_out_forget,
                        'total'                    => $counter_out_forget*$query_tidak_check_pulang->price,
                        'dates'                    => implode(',',$date_out_forget)
                    ]);
                }


            }
           
            $end_time = microtime(true);
            foreach($attendance_detail as $row_attd){
                foreach($row_attd as $row_attd_detail){
                   if (is_array($row_attd_detail['nama_shift']) && empty(($row_attd_detail['nama_shift']))) {
                    $query_presence_report = PresenceReport::create([
                        'user_id'                  => $row_attd_detail['user_no'],
                        'period_id'                => $attendance_period->id,
                        'date'                     => $row_attd_detail['date'],
                        'late_status'              => '7',
                        'status'                   => '1',
                    ]);
                    $query_daily_report= AttendanceDailyReports::create([
                        'user_id'                  => $row_attd_detail['user_no'],
                        'masuk'                    => '',
                        'pulang'                   => '',
                        'period_id'                => $attendance_period->id,
                        'status'                   => '7',
                        'date'                     => $row_attd_detail['date'],
                    ]);
                } else {
                   foreach($row_attd_detail['in'] as $key_masuk=>$row_masuk){
                 
                    $status='';
                        if($row_masuk == '1' && $row_attd_detail['out'][$key_masuk] == '1'){
                            $status = '1';
                        }
                        if($row_masuk == '0' && $row_attd_detail['out'][$key_masuk] == '1'){
                            $status = '2';
                        }
                        if($row_masuk == '1' && $row_attd_detail['out'][$key_masuk] == '0'){
                            $status = '3';
                        }
                        if($row_masuk == '2' && $row_attd_detail['out'][$key_masuk] == '1'){
                            $status = '4';
                        }
                        if($row_masuk == '2' && $row_attd_detail['out'][$key_masuk] == '0'){
                            $status = '5';
                        }
                        if($row_masuk == '0' && $row_attd_detail['out'][$key_masuk] == '0'){
                            $status = '6';
                        }
                        $query_presence_report = PresenceReport::create([
                            'user_id'                  => $row_attd_detail['user_no'],
                            'nama_shift'               => $row_attd_detail['nama_shift'][$key_masuk],
                            'period_id'                => $attendance_period->id,
                            'date'                     => $row_attd_detail['date'],
                            'late_status'              => $status,
                            'status'                   => '1',
                        ]);
                        $query_daily_report= AttendanceDailyReports::create([
                            'user_id'                  => $row_attd_detail['user_no'],
                            'masuk'                    => $row_attd_detail['jam_masuk'][$key_masuk],
                            'pulang'                   => $row_attd_detail['jam_keluar'][$key_masuk],
                            'status'                   => $status,
                            'period_id'                => $attendance_period->id,
                            'date'                     => $row_attd_detail['date'],
                            'shift_id'                 => $row_attd_detail['shift_id'][$key_masuk],
                        ]);
                   }
                }
                }
                
            }
            
            //untuk menghitung denda terlambat
            foreach($counter_user_monthly as $row_user){
                foreach($row_user as $row_counter){
                    $string_date = implode(',',$row_counter['date']);
                    if($row_counter['counter']!='0'){
                        $query_presence_report = AttendancePunishment::create([
                            'user_id'                  => $row_counter['uid'],
                            'period_id'                => $request->id,
                            'employee_id'              => session('bo_id'),
                            'punishment_id'            => $row_counter['punish_id'],
                            'frequent'                 => $row_counter['counter'],
                            'total'                    => $row_counter['counter']*$row_counter['price'],
                            'dates'                    => implode(',',$row_counter['date'])
                        ]);
                    }
                }
            }

            $execution_time = ($end_time - $start_time);
           
            $response =[
                'status'   =>200,
                'message'  =>$attendance_detail,
                'punishment' => $counter_user_monthly,
            ];
        }else{
            $response =[
                'status'  =>500,
                'message' =>'ada yang error'
            ];
        }
        return response()->json($response);
    }
    public function presenceReport(Request $request){
        $distinctUserIds = PresenceReport::where('period_id',$request->id)->groupBy('user_id')->pluck('user_id')->toArray();
    
        $distinctDates = PresenceReport::where('period_id', $request->id)
                ->groupBy('date')
                ->pluck('date')
                ->map(function ($date) {
                    return Carbon::createFromFormat('d/m/Y', $date); // Parse the date with the correct format
                })
                ->sortBy(function ($date) {
                    return $date;
                })
                ->map(function ($date) {
                    return $date->format('d/m/Y'); // Format the date as 'dd/mm/yyyy'
                })
                ->toArray();
        
        $userDetail=[];
      
        foreach($distinctUserIds as $key_user=>$user_id){
            foreach($distinctDates as $key_dates=>$row_dates){
                $userDetail[$key_user][$key_dates]=[
                    'user_name'     =>'',
                    'late_status'   =>[],
                    'date'          =>'',
                    'nama_shift'    =>[],
                ];
                foreach(PresenceReport::where('user_id',$user_id)->where('date',$row_dates)->where('period_id',$request->id)->get() as $key_presence=>$row_presence){
                    $userDetail[$key_user][$key_dates]['user_name']=$row_presence->user->name??'';
                    $userDetail[$key_user][$key_dates]['date']=$row_dates;
                    $userDetail[$key_user][$key_dates]['nama_shift'][]=$row_presence->nama_shift;
                    $userDetail[$key_user][$key_dates]['late_status'][]=$row_presence->late_status;
                }
            }
        }
        
        $string_table="";

        foreach($userDetail as $key_presenced => $row_user_presence){
        
            $string_table.="
                <tr>
                    <td rowspan='3'>".$row_user_presence[0]['user_name']."
            ";
            foreach($row_user_presence as $key_detailed=>$row_detailed){
                $string_table.="
                    <td colspan=".count($row_detailed['nama_shift']).">".$row_detailed['date']."</td>
                ";
            }
            $string_table .="
                </tr>
                
                <tr> 
            ";
            foreach($row_user_presence as $key_detailed=>$row_detailed){
                foreach($row_detailed['nama_shift'] as $key_d=>$row_nama_shift){
                    $string_table .="
                        <td>".$row_nama_shift."</td>
                    ";
                }
            }
            $string_table .="
                </tr>
                <tr> 
            ";
            foreach($row_user_presence as $row_detailed){
                foreach($row_detailed['late_status'] as $key_status=>$row_status){
                    if($row_status=='1'){
                        $string_table.=" <td style='color: green;    font-weight: 700;'><i class='material-icons right'>check</i></td>";
                    }
                    if($row_status=='2'){
                        $string_table.="<td style='color: purple;    font-weight: 700;'><i class='material-icons right'>check</i></td>";
                    }
                    if($row_status=='3'){
                        $string_table.="<td style='color: goldenrod;    font-weight: 700;'><i class='material-icons right'>check</i></td>";
                    }
                    if($row_status=='4'){
                        $string_table.="<td style='color: blue;    font-weight: 700;'><i class='material-icons right'>check</i></td>";
                    }
                    if($row_status=='5'){
                        $string_table.="<td style='color: crimson;    font-weight: 700;'><i class='material-icons right'>check</i></td>";
                    }
                    if($row_status=='6'){
                        $string_table.="<td style='color: red;    font-weight: 700;'><i class='material-icons right'>close</i></td>";
                    }
                    if($row_status=='7'){
                        $string_table.="<td style='color: black;    font-weight: 700;'>Tidak Ada Shift Pada Tanggal Ini</td>";
                    }

                }
            }
            $string_table .="
                </tr>
            ";
        }
        $response = [
            'status'    => 200,
            'message'   => $string_table,
        ];
        return response()->json($response);
    }
    public function dailyReport(Request $request){
        $dailyReports  = AttendanceDailyReports::where('period_id',$request->id)->get();
        $distinctUserIds = $dailyReports->pluck('user_id')->unique()->toArray();
        $distinctDates = $dailyReports->pluck('date')->unique()->toArray();
        $attendanceDetail=[];
        foreach($distinctDates as $key_date=>$row_dates){
            foreach($dailyReports as $key_daily=>$row_daily){
                if($row_daily['date']==$row_dates){
                    $attendanceDetail[$key_date][]=[
                        'user_id'=>$row_daily->user->employee_no??'',
                        'user_name'=>$row_daily->user->name??'',
                        'nama_shift'=>$row_daily->shift->name ?? 'tidak ada shift',
                        'min_masuk'=>$row_daily->shift->min_time_in ?? 'tidak ada shift',
                        'max_keluar'=>$row_daily->shift->max_time_out ?? 'tidak ada shift',
                        'limit_masuk'=>$row_daily->shift->time_in ?? 'tidak ada shift',
                        'limit_keluar'=>$row_daily->shift->time_out ?? 'tidak ada shift',
                        'masuk'=>$row_daily->masuk,
                        'pulang'=>$row_daily->pulang,
                        'date'=>$row_daily->date,
                        'status'=>$row_daily->status(),
                    ];
                }
            }
            
        }
        $string_table="";
        $iterasi=0;
        foreach($attendanceDetail as $row_detail){
            foreach($row_detail as $key_daily=>$row_daily){
                $iterasi++;
                $string_table.="
                <tr>      
                    <td class='center-align'>".$iterasi."</td>
                    <td class='center-align'>".$row_daily['user_id']."</td>
                    <td class='center-align'>".$row_daily['user_name']."</td>
                    <td class='center-align'>".$row_daily['date']."</td>
                    <td class='center-align'>".$row_daily['nama_shift']."</td>
                    <td class='center-align'>".$row_daily['min_masuk']."</td>
                    <td class='center-align'>".$row_daily['limit_masuk']."</td>
                    <td class='center-align'>".$row_daily['masuk']."</td>
                    <td class='center-align'>".$row_daily['limit_keluar']."</td>
                    <td class='center-align'>".$row_daily['pulang']."</td>
                    <td class='center-align'>".$row_daily['max_keluar']."</td>
                    <td class='center-align'>".$row_daily['status']."</td>
                </tr>
                ";
            }
        }
        $response = [
            'status'    => 200,
            'message'   => $string_table,
        ];
        return response()->json($response);
    }
    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			          => $request->temp ? ['required', Rule::unique('attendance_periods', 'code')->ignore($request->temp)] : 'required|unique:attendance_periods,code',
            'name'                    => 'required',
            'start_date'              => 'required',
            'end_date'                => 'required',
            'plant_id'                => 'required',

        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah dipakai',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'start_date.required'              => 'Awal Periode belum diisi',
            'end_date.required'                => 'Akhir Periode tidak boleh kosong',
            'plant_id.required'                => 'Harap Pilih Plant',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = AttendancePeriod::find($request->temp);
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->start_date          = $request->start_date;
                    $query->end_date              = $request->end_date;
                    $query->plant_id = $request->plant_id;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = AttendancePeriod::create([
                        'code'                  => $request->code,
                        'name'			        => $request->name,
                        'start_date'             => $request->start_date,
                        'end_date'                => $request->end_date,
                        'plant_id'           => $request->plant_id,
                       
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }
    public function export(Request $request){        
        $period_id = $request->period_id ? $request->period_id:'';
		return Excel::download(new ExportPeriod($period_id), 'period_report'.uniqid().'.xlsx');
    }
}
