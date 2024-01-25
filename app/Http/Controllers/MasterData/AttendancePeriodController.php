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
use App\Models\LeaveRequest;
use App\Models\Place;
use App\Models\PresenceReport;
use App\Models\SalaryReport;
use App\Models\SalaryReportTemplate;
use App\Models\SalaryReportDetail;
use App\Models\SalaryReportUser;
use App\Models\Punishment;
use App\Models\Holiday;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Models\EmployeeRewardPunishment;
use App\Models\EmployeeRewardPunishmentDetail;
use App\Models\EmployeeRewardPunishmentPayment;
use App\Models\OvertimeRequest;
use App\Models\OvertimeCost;
use App\Models\User;
use App\Models\UserAbsensiMesin;
use App\Models\UserSpecial;
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
            'title'         => 'Periode Absen',
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Laporan Denda" onclick="reportPunishment(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons dp48" style="color:black">money_off</i></button>

                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Laporan Salary" onclick="reportSalaryMonthly(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons dp48" style="color:black">money_off</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light yellow darken-2  btn-small" data-popup="tooltip" title="Buka Kembali " onclick="reOpen(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons dp48" style="color:black">lock_open</i></button>
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
        
        $attendance_period = AttendancePeriod::find($request->id);
        $start_time = microtime(true);
        $start_date = Carbon::parse($attendance_period->start_date);
        $end_date = Carbon::parse($attendance_period->end_date);
        
        $user_data = User::where(function($query) use ( $request) {
            $query->where('type','1');
            $query->whereIn('employee_no',['323004','E000006','E000008','E000007','E000005','E000010']);
            // $query->whereIn('employee_no',['E000010']);    
            })->get();

        $query_salary_report = SalaryReport::create([
            'code'                                  => SalaryReport::generateCode('SrPo'),
            'period_id'                             => $request->id,
            'post_date'                             => Carbon::now(),
        ]);
        
        $query_salary_component= SalaryComponent::where('status',1)->get();
        $punishment_get = Punishment::where('status',1)->get();
        
        foreach($query_salary_component as $row_component){
            $report_salary_template = SalaryReportTemplate::create([
                'lookable_type'                             => 'salary_components',
                'salary_report_id'                          => $query_salary_report->id,
                'lookable_id'                               => $row_component->id,
            ]);
        }
        foreach($punishment_get as $row_punishment){
            $report_salary_template = SalaryReportTemplate::create([
                'lookable_type'                             => 'punishments',
                'salary_report_id'                          => $query_salary_report->id,
                'lookable_id'                               => $row_punishment->id,
            ]);
        }
        $report_salary_template = SalaryReportTemplate::create([
            'lookable_type'                             => 'overtime_requests',
            'salary_report_id'                          => $query_salary_report->id,
            'lookable_id'                               => $row_punishment->id,
        ]);

        $attendance_detail = [];
        $user_counter_effective_day = [];
        $user_counter_absent=[];
        $user_counter_arrived_on_time=[];
        $user_counter_out_on_time=[];
        if($start_date && $end_date){
            foreach($user_data as $c=>$row_user){
                $user_id = $row_user->employee_no;
                $date = $start_date->copy();
                $date_leave_req = $start_date->copy();
                $date_overtime_no_schedule = $start_date->copy();
                $counter_effective_day=0;
                $counter_absent=0;
                $counter_alpha=0;
                $counter_arrived_on_time=0;
                $counter_arrived_forget=0;
                $counter_out_on_time=0;
                $counter_out_forget=0;

                $counter_late=0;
                $counter_leave_early = 0;

                $counter_cuti = 0;
                $counter_sakit= 0;
                $counter_ijin = 0;
                $counter_dinas_luar = 0;
                $counter_cuti_kusus = 0;
                $counter_lain_lain = 0;
                $counter_dispen = 0;
                $counter_wfh = 0;

                $date_out_forget=[];
                $date_arrived_forget=[];

                


                $all_exact_in=[];
                $all_exact_out=[];  
                $query_late_punishment = Punishment::where('place_id',$row_user->place_id)
                                            ->where('type','1')
                                            ->where('status','1')
                                            ->orderBy('minutes')
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
                $query_special_1_forLimit = UserSpecial::where('user_id',$row_user->id)
                        ->where('start_date','<=', $date)
                        ->where('status',1)->first();
                $limit = $query_special_1_forLimit->limit ?? 999;
                $limit_temp = 0;
                $max_punish_id = $query_special_1_forLimit->punishment->id ?? null;
                $key_id_punish = null;
                if($max_punish_id){
                    foreach($query_late_punishment as $key_mod=>$row_punishment){
                        if($max_punish_id == $row_punishment->id){
                            $key_id_punish=$key_mod;
                        }
                    }
                } 
                while ($date->lte($end_date)) {

                    $query_data = EmployeeSchedule::join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
                        ->whereDate('employee_schedules.date', $date->toDateString())
                        ->where('employee_schedules.user_id', $row_user->employee_no)
                        ->whereIn('employee_schedules.status', [1, 4 , 5])
                        ->orderBy('shifts.time_in') // Order by next_day (1 comes last)
                        ->select('employee_schedules.*') // Select the columns you need
                        ->get();
                    
                             
                    $cleanedNik = str_replace(' ', '', $row_user->employee_no);
                    
                    
             
                    
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

            
                    $query_special = UserSpecial::where('user_id',$row_user->id)
                                ->where('start_date','<=', $date)
                                ->where('end_date','>=',$date)
                                ->where('status',1)
                                ->first();
                    
                    //perhitungan schedule biasa dari user pada tanggal yang ada di loop
                    foreach($query_data as $key=> $row_schedule_filter){//perlusd
                        $query_lembur= null;
                        $lembur = 0;
                        $query_data[$key]->is_closed = '2';
                        $query_data[$key]->save();
                        $lembur_awal_shift = 0;
                        $time_in = $row_schedule_filter->shift->time_in;
                        $time_out = $row_schedule_filter->shift->time_out;
                  
                        if($row_schedule_filter->status == 5){
                            
                            $query_lembur = OvertimeRequest::where('schedule_id',$row_schedule_filter->id)
                            ->where('account_id',$row_user->id)->first();
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
                                //ini tempat kalau tidak ada masuk awal dan pulang 
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
                                //end

                                //perhitungan masuk tepat atau tidak dan pulang tepat atau tidak
                                if ($dateAttd >= $real_min_time_in && $dateAttd <= $real_time_in) {
                                    $exact_in[$key]= 1 ;
                                    info('masuk1');
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
                                    info('masuk2');
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
                                    if(!$muleh){
                                        $muleh=$timePart;
                                        $array_keluar[$key]=$timePart;
                                        $different_keluar[$key]='';
                                    }
                                    if($muleh<$timePart){
                                        $muleh=$timePart;
                                        $array_keluar[$key]=$timePart;
                                        $different_keluar[$key]='';
                                    }
                                    
                                }
                                // if ($dateAttd>=$real_time_in && $dateAttd <= $real_time_out && $dateAttd <= $real_max_time_out && $date->toDateString() == $dateAttd->toDateString()) {
                                //     if(count($query_data)>1 || $date->toDateString() == $dateAttd->toDateString()){
                                //         $exact_out [$key]= 1 ;
                                       
                                //     }
                                // }
                                if ($dateAttd <= $real_time_out && $dateAttd <= $real_max_time_out && $date->toDateString() == $dateAttd->toDateString()&&count($query_data)==1) {
                                    if(count($query_data)>1 || $date->toDateString() == $dateAttd->toDateString()){
                                        $exact_out [$key]= 1 ;
                                      
                                    }
                                    if($muleh==null && $date->toDateString() == $dateAttd->toDateString()){
                                        if($masuk_awal == null){
                                            $masuk_awal = $timePart;
                                        }else{
                                            $muleh=$timePart;
                                        }
                                        
                                    }elseif($muleh < $timePart){
                                        $muleh=$timePart;
                                        $array_keluar[$key]=$timePart;
                                    }
                                    if(!$logout&& $date->toDateString() == $dateAttd->toDateString()){
                                        
                                        $logout = $timePart;
                                        if($login && $login != $timePart){
                                
                                            $array_keluar[$key]=$timePart;
                                        }
                                        $different_keluar[$key]='';
                                        
                                    }  
                                }
                            }
                            
                            $latestRecord = $query_attendance->last();
                            if($query_lembur){
                                $query_libur = Holiday::where('date',$query_lembur->date)->first();
                                    
                                if($query_libur){
                                    
                                    $query_cost = OvertimeCost::whereRaw("'$query_lembur->date' BETWEEN start_date AND end_date ")
                                        ->where('place_id',$row_user->place_id)
                                        ->where('level_id',$row_user->position->level_id)
                                        ->where('type','1')
                                        ->first();
                                    
                                }else{
                                    
                                    $query_cost = OvertimeCost::whereRaw("'$query_lembur->date' BETWEEN start_date AND end_date ")
                                        ->where('place_id',$row_user->place_id)
                                        ->where('level_id',$row_user->position->level_id)
                                        ->where('type','2')
                                        ->first();
                                }
                            }
                           
                            if($row_schedule_filter->status == 5 && $query_lembur &&  $lembur_awal_shift != 1){//perhitungan lemboer
                                
                                $time_in_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_in;
                                $time_out_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_out;
                        
                                if(Carbon::parse($latestRecord->date) < Carbon::parse($real_time_out)){
                                   
                                   
                                  
                                    $timeDifference = Carbon::parse($time_in_lembur)->diff($latestRecord->date);
                                    $hoursDifference = $timeDifference->h;
                                    
                                    $query_lembur->total            = $hoursDifference;
                                    
                                    
                                    
                                    if($query_cost){
                                        
                                        $query_lembur->grandtotal         = $hoursDifference * $query_cost->nominal;
                                       
                                        $query_lembur->save();
                                    }
                                }else{
                                    $timeDifference = Carbon::parse($time_in_lembur)->diff(Carbon::parse($time_out_lembur));
                                    $hoursDifference = $timeDifference->h;
                                    
                                    $query_lembur->total            = $hoursDifference;
                    
                                    if($query_cost){
                                        $query_lembur->grandtotal         = $hoursDifference * $query_cost->nominal;
                                        $query_lembur->save();
                                    } 
                                }
                            }
                            
                            if($row_schedule_filter->status == 5 && $query_lembur &&  $lembur_awal_shift == 1 && $exact_out[$key] == 1 && $masuk_awal){
                                $masuk_awal_combine = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' .$masuk_awal;
                                $time_in_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_in;
                                $time_out_lembur = Carbon::parse($query_lembur->date)->format('Y-m-d') . ' ' . $query_lembur->time_out;
                                if(Carbon::parse($masuk_awal_combine) < Carbon::parse($real_time_in)){
                                    
                                  
                                    $timeDifference = Carbon::parse($time_in_lembur)->diff(Carbon::parse($time_out_lembur));
                                    $hoursDifference = $timeDifference->h;
                                   
                                    $query_lembur->total            = $hoursDifference;
 
                                    if($query_cost){
                                        
                                        $query_lembur->grandtotal         = $hoursDifference * $query_cost->nominal;
                                        $query_lembur->save();
                                    }
                                }else{
                                    $timeDifference = Carbon::parse($masuk_awal_combine)->diff(Carbon::parse($time_out_lembur));
                                    $hoursDifference = $timeDifference->h;
                                  
                                    $query_lembur->total            = $hoursDifference;
                                   
                                    if($query_cost){
                                        $query_lembur->grandtotal         = $hoursDifference * $query_cost->nominal;
                                        $query_lembur->save();
                                    } 
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
                            }else{
                                if($masuk_awal ){//perhitungan toleransi punya specialll?

                                    $pembandingdate = Carbon::parse($masuk_awal);
                                    if(count($query_data)==2 && $key == 1){
                                        $currentSchedule = $query_data[1];
                                        $previousSchedule = $query_data[0];
                                        
                                        $currentTimeIn = Carbon::parse($currentSchedule->shift->time_in);
                                        $previousTimeIn = Carbon::parse($previousSchedule->shift->time_out);
                                        
                                        $timeDifference = $currentTimeIn->diffInHours($previousTimeIn);
                                        if($timeDifference >= 2){
                                            $pembandingdate = Carbon::parse($login[1]);
                                        }
                                    }
                                    
                                    $pembanding= $pembandingdate->format('H:i:s');
                                    $carbonTimeIn = Carbon::parse($time_in);
                                    
                                    if($pembanding > $time_in ){
                                        if(count($query_late_punishment)> 0){
                                      
                                            if($pembanding > $time_in && $pembanding <= Carbon::parse($time_in)->addMinutes($query_late_punishment[0]->minutes)->format('H:i:s')){
                                                if (($max_punish_id != null && $limit > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[0]->code]['punish_id'] && ($key_id_punish!=null && $key <= $key_id_punish) ) ) {
                                                    $limit--;
                                                    
                                                    foreach ($exact_in as $key_in => $row_ins) {
                                                        $exact_in[$key_in] = 1;
                                                    }
                                                    break;  
                                                }
                                                else{
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['counter']++;
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['date'][]=Carbon::parse($date)->format('d/M/y');  
                                                }
                                            }else{
                                                foreach($query_late_punishment as $key=>$row_punish_type){
                                                    $newCarbonTime = Carbon::parse($time_in)->addMinutes($row_punish_type->minutes)->format('H:i:s');
                                                    if($key !=0 ){
                                                        
                                                        if($pembanding < Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s') && $pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key-1]->minutes)->format('H:i:s')){
                                                            if (($max_punish_id != null && $limit > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[$key]->code]['punish_id']&&($key_id_punish!=null && $key <= $key_id_punish))) {
                                                                $limit--;
                                                                
                                                                foreach ($exact_in as $key_in => $row_ins) {
                                                                    $exact_in[$key_in] = 1;
                                                                }
                                                                break;  
                                                            }
                                                            else{
                                                                
                                                                $tipe_punish_counter[$query_late_punishment[$key]->code]['counter']++;
                                                                $tipe_punish_counter[$query_late_punishment[$key]->code]['date'][]=$date->format('d/M/y');
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if($key == count($query_late_punishment)){
                                                        if($pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s')&&$pembanding<Carbon::parse($time_in)->addHours(2)->format('H:i:s')){
                                                            if (($max_punish_id != null && $limit > 0 && $max_punish_id != $tipe_punish_counter[$query_late_punishment[$key]->code]['punish_id']&&($key_id_punish!=null && $key <= $key_id_punish))) {
                                                                $limit--;
                                                                
                                                                foreach ($exact_in as $key_in => $row_ins) {
                                                                    $exact_in[$key_in] = 1;
                                                                }
                                                                break;  
                                                            }
                                                            else{
                                                                $tipe_punish_counter[$row_punish_type->code]['counter']++;
                                                                $tipe_punish_counter[$row_punish_type->code]['date'][]=$date->format('d/M/y');
                                                                break;  
                                                            }
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
                        }else{
                            if($limit_temp == 0){
                                $limit_temp = $limit_temp+$limit;
                            }
                            $limit =0;
                            if($masuk_awal && $lembur == 0){//perhitungan toleransi okeeeeey?
                              
                                $pembandingdate = Carbon::parse($masuk_awal);
                                if(count($query_data)==2 && $key == 1){
                                    $currentSchedule = $query_data[1];
                                    $previousSchedule = $query_data[0];
                                    
                                    $currentTimeIn = Carbon::parse($currentSchedule->shift->time_in);
                                    $previousTimeIn = Carbon::parse($previousSchedule->shift->time_out);
                                    
                                    $timeDifference = $currentTimeIn->diffInHours($previousTimeIn);
                                    if($timeDifference >= 2){
                                        $pembandingdate = Carbon::parse($login[1]);
                                    }
                                }
                                
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
                                                       
                                                        $tipe_punish_counter[$query_late_punishment[$key]->code]['counter']++;
                                                        $tipe_punish_counter[$query_late_punishment[$key]->code]['date'][]=$date->format('d/M/y');
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

                    }
                    
                    
                    $attendance_detail[$row_user->id][]=[
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
                    //perhitungan cuti atau ijin
                    
                    foreach($exact_in as $key=>$row_exact){
                        
                        $all_exact_in[]=$row_exact;
                        $all_exact_out[]=$exact_out[$key];
                    }
                    
                    $lanjoet=1;
                   
                    foreach($exact_in as $key=>$row_arrive){
                       
                        
                        if($row_arrive == 0 && $exact_out[$key] == 1){
                            if($row_user->id == '21'){

                            }    
                            $date_arrived_forget[]=Carbon::parse($date)->format('d/m/Y');
                            
                        }
                    }
                    
                   
                    if($lanjoet==1){
                          
                        foreach($exact_out as $key3=>$row_out){
                            if($row_user->id == '21'){

                            }  
                            if($row_out==0 && $exact_in[$key3] == 1){
                                
                                $date_out_forget[]=Carbon::parse($date)->format('d/m/Y');
                                break;
                            }
                        }
                    }
                    
                    
                    
                    $date->addDay();
                    
                }
                if($limit_temp == 0){
                    $limit_temp = $limit_temp+$limit;
                }
                $counter_ps= 0;
                
                while($date_leave_req->lte($end_date)){
                    $exact_in=[];
                    $exact_out=[];
                    $parse_date = Carbon::parse($date_leave_req->format('Y-m-d'))->toDateString();
                   
                    $query_data_leaveRequest = LeaveRequest::whereRaw("'$parse_date' BETWEEN start_date AND end_date ")
                                    ->where('account_id',$row_user->id)
                                    ->where('status', 2)
                                    ->whereHas('leaveRequestShift', function($query) use($parse_date){
                                        $query->whereHas('employeeSchedule', function($query) use($parse_date){
                                            // $query->where('date',$parse_date);
                                        });
                                    })
                                    ->get();
                    $query_special = UserSpecial::where('user_id',$row_user->id)
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
                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['out'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
                                    
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
                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['out'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
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
                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['out'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
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
                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['out'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
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
    
                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['out'][]='4';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$row_leave_request->leaveType->name;
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
                                        $counter_leave_early++;
                                    }else{
                                        $time_in = $temp_is_late;
                                        $counter_late++;
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
                                   

                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]='';
                                    $attendance_detail[$row_user->id][$counter_ps]['time_in'][]=$time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['time_out'][]=$time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['min_time_in'][]=$min_time_in;
                                    $attendance_detail[$row_user->id][$counter_ps]['max_time_out'][]=$max_time_out;
                                    $attendance_detail[$row_user->id][$counter_ps]['nama_shift'][]=$schedule_leave_request->employeeSchedule->shift->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['shift_id'][]=$schedule_leave_request->employeeSchedule->shift->id;
                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$row_leave_request->leaveType->name;
                                    $attendance_detail[$row_user->id][$counter_ps]['logout']=$row_leave_request->leaveType->name;
                                    


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
                                                
                                                $attendance_detail[$row_user->id][$counter_ps]['in'][]='1';
                                             
                                                if($masuk_awal==null){
                                                    $masuk_awal =$timePart;
                                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$timePart;
                                                }elseif($masuk_awal > $timePart){
                                                    $attendance_detail[$row_user->id][$counter_ps]['login']=$timePart;
                                                    $masuk_awal =$timePart;
                                                }
                                               
                                                $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                                $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_masuk'][]='';
                                                
                                                
                                            }elseif($dateAttd > $real_time_in && $dateAttd < $real_time_out){
                                                $diffHoursTimePartMinIn = Carbon::parse($timePart)->diffInHours($time_in);
                                                if($masuk_awal==null){
                                                    $masuk_awal=$timePart;
                                                }
                                                $perlu_nambah = false;
                                                if($diffHoursTimePartMinIn<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                                    $exact_in[]='2';
                                                    
                                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='2';
                                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]=$min_time_in.' | '.$timePart .' | '.$time_in;
                                                }elseif($perlu_nambah != false && $diffHoursTimePartMinIn>=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                                    $exact_in[]='0';
                                                    $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]='';
                                                    $attendance_detail[$row_user->id][$counter_ps]['in'][]='0';
                                                }                                      
                                            }elseif($dateAttd > $real_max_time_out){
                                                $diffHoursTimePartMaxOut = Carbon::parse($timePart)->diffInHours($max_time_out);
    
                                                if($diffHoursTimePartMaxOut<=$schedule_leave_request->employeeSchedule->shift->tolerant){
                                                    $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$time_out.' | '.$timePart .' | '.$max_time_out;
                                                    $ada_pulang=true;
                                                }
                                            }
                                        }
                                        


                                        //perhitungan pulang
                                        if ($dateAttd >= $real_time_out && $dateAttd <= $real_max_time_out) {
                                            $attendance_detail[$row_user->id][$counter_ps]['out'][]=1;
                                            $exact_out[]='1';
                                         
                                            $ada_pulang=true;
                                            if($muleh==null){
                                                $muleh=$timePart;
                                                $attendance_detail[$row_user->id][$counter_ps]['logout']=$timePart;
                                                $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]=$time_out.' | '.$timePart .' | '.$max_time_out;
                                                $attendance_detail[$row_user->id][$counter_ps]['perbedaan_jam_keluar'][]=''; 
                                            }elseif($muleh < $timePart){
                                                $muleh=$timePart;
                                                $attendance_detail[$row_user->id][$counter_ps]['logout']=$timePart;
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
                                    //                 $attendance_detail[$row_user->id][$counter_ps]['in'][]='0';
                                    //                 $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]='';
                                            
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
                                    //             $attendance_detail[$row_user->id][$counter_ps]['in'][]='0';
                                    //             $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]='';
                                        
                                    //         }
                                    //     }
                                    // }
                                    if($masuk_awal){//perhitungan toleransi untuk ijin telat?
                                        $pembandingdate = Carbon::parse($masuk_awal);
                                        $pembanding= $pembandingdate->format('H:i:s');
                                        $carbonTimeIn = Carbon::parse($time_in);
                                        
                                        if($pembanding > $time_in ){
                                            if(count($query_late_punishment)> 0){
                                            
                                                if($pembanding > $time_in && $pembanding <= Carbon::parse($time_in)->addMinutes($query_late_punishment[0]->minutes)->format('H:i:s')){
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['counter']++;
                                                    $tipe_punish_counter[$query_late_punishment[0]->code]['date'][]=Carbon::parse($date_leave_req)->format('d/M/y');  
                                                }else{
                                                    foreach($query_late_punishment as $key=>$row_punish_type){
                                                        $newCarbonTime = Carbon::parse($time_in)->addMinutes($row_punish_type->minutes)->format('H:i:s');
                                                        if($key !=0 ){
                                                            
                                                            if($pembanding < Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s') && $pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key-1]->minutes)->format('H:i:s')){
                                                                $tipe_punish_counter[$query_late_punishment[$key-1]->code]['counter']++;
                                                                $tipe_punish_counter[$query_late_punishment[$key-1]->code]['date'][]=$date_leave_req->format('d/M/y');
                                                                break;
                                                            }
                                                        }
                                                        if($key == count($query_late_punishment)-1){
                                                            if($pembanding > Carbon::parse($time_in)->addMinutes($query_late_punishment[$key]->minutes)->format('H:i:s')&&$pembanding<Carbon::parse($time_in)->addHours(2)->format('H:i:s')){
                                                                $tipe_punish_counter[$row_punish_type->code]['counter']++;
                                                                $tipe_punish_counter[$row_punish_type->code]['date'][]=$date_leave_req->format('d/M/y');
                                                            }else{
                                                                $tipe_punish_counter['over_t']['counter']++;
                                                                $tipe_punish_counter['over_t']['date'][]=$date_leave_req->format('d/M/y');
                                                            }
                                                        }
                                                        
                                                    }
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }else{//hanya utk telat di ijin
                                        if($perlu_nambah == true){
                                            $exact_in[]=0;
                                            $attendance_detail[$row_user->id][$counter_ps]['in'][]='0';
                                            $attendance_detail[$row_user->id][$counter_ps]['jam_masuk'][]='';
                                    
                                        }
                                    }
                                    if($ada_pulang == false){
                                        $exact_out[]='0';
                                        $attendance_detail[$row_user->id][$counter_ps]['out'][]='0';
                                        $attendance_detail[$row_user->id][$counter_ps]['jam_keluar'][]='';
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
               
                $counter_overtime_date= 0;
                while($date_overtime_no_schedule->lte($end_date)){//perlusd
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

                    $query_data_overtime = OvertimeRequest::whereDate('date', $date_overtime_no_schedule)
                        ->where('account_id', $row_user->id)
                        ->where('schedule_id', null)
                        ->where('status', 2)
                        ->get();
                    $query_attendance = Attendances::where(function ($query) use ($date_overtime_no_schedule,$cleanedNik) {
                        $mulaiDate = Carbon::parse($date_overtime_no_schedule)->subDays(1)->startOfDay()->toDateTimeString();
                        $akhirDate = Carbon::parse($date_overtime_no_schedule)->addDays(1)->endOfDay()->toDateTimeString();
                
                        $query->where('date', '>=', $mulaiDate)
                            ->where('date', '<=', $akhirDate)
                            ->where('employee_no',$cleanedNik);
                    })->orderBy('date')->get();
                    foreach($query_data_overtime as $key=>$row_overtime){
                        $exact_in[$key]= 0 ;
                        $exact_out[$key]= 0 ;
                        $time_in = $row_overtime->time_in;
                        $time_out = $row_overtime->time_out;
                        $login[$key] = null;
                        $logout = null;
                        
                        $real_time_in =$date_overtime_no_schedule->format('Y-m-d') . ' ' . $time_in;
                        $combinedDateTimeInCarbonz = Carbon::parse($real_time_in);

                        $real_time_out =$date_overtime_no_schedule->format('Y-m-d') . ' ' . $time_out;
                        $combinedDateTimeOutCarbon = Carbon::parse($real_time_out);
                        $subx=$combinedDateTimeInCarbonz->copy()->subHours(2);
                        $addx=$combinedDateTimeInCarbonz->copy()->addHours(2);
                        foreach($query_attendance as $row_attendance_filter){
                            $dateAttd = Carbon::parse($row_attendance_filter->date);
                           
                            $timePart = $dateAttd->format('H:i:s');
                                      
                            //perhitungan masuk tepat atau tidak dan pulang tepat atau tidak
                            if ( $dateAttd > $subx && $dateAttd <= $addx) {
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
                                    $attendance_detail[$row_user->id][$counter_overtime_date]['jam_masuk'][]=  $timePart;
                                }
                                
                            }
                        }
                        
                        $last_attendance = $query_attendance->last();
                    
                       
                        if($last_attendance&&$exact_in[$key]==1){
                            $exact_out[$key]= 1 ;
                            $dateAttdLast = Carbon::parse($last_attendance->date);
                           
                            $timePartlast = $dateAttd->format('H:i:s');
                         
                            if($login[$key]!=$timePartlast){
                                $attendance_detail[$row_user->id][$counter_overtime_date]['jam_keluar'][]=  $timePartlast;
                                if($dateAttdLast >= $combinedDateTimeOutCarbon){
                                     
                                    $timeDifference = Carbon::parse($row_overtime->time_in)->diff(Carbon::parse($row_overtime->time_out));
                                
                                    $hoursDifference = $timeDifference->h;
                                    $row_overtime->total            = $hoursDifference;
                                    $query_libur = Holiday::where('date',$row_overtime->date)->get();
                                    if($query_libur){
                                        $query_cost = OvertimeCost::whereRaw("'$row_overtime->date' BETWEEN start_date AND end_date ")
                                            ->where('place_id',$row_user->place_id)
                                            ->where('level_id',$row_user->position->level_id)
                                            ->where('type','1')
                                            ->first();
                                        
                                    }else{
                                       
                                        $query_cost = OvertimeCost::whereRaw("'$row_overtime->date' BETWEEN start_date AND end_date ")
                                            ->where('place_id',$row_user->place_id)
                                            ->where('level_id',$row_user->position->level_id)
                                            ->where('type','2')
                                            ->first();
                                    }
                                    if($query_cost){
                                        $row_overtime->grandtotal         = $hoursDifference * $query_cost->nominal;
                                        $row_overtime->save();
                                    }
                                }if($dateAttdLast <= $combinedDateTimeOutCarbon && $dateAttdLast >= $combinedDateTimeInCarbonz){
                                    $attendance_detail[$row_user->id][$counter_overtime_date]['jam_keluar'][]=  $timePartlast;
                                    $timeDifference = $combinedDateTimeInCarbonz->diff($dateAttdLast);
                                    
                                    $hoursDifference = $timeDifference->h;
                                
                                    $row_overtime->total            = $hoursDifference;
                                    
                                    $query_cost = OvertimeCost::whereRaw("'$row_overtime->date' BETWEEN start_date AND end_date ")
                                    ->where('place_id',$row_user->place_id)
                                    ->where('level_id',$row_user->position->level_id)
                                    ->where('type','2')
                                    ->first();
                                
                                    if($query_cost){
                                        $row_overtime->grandtotal         = $hoursDifference * $query_cost->nominal;
                                        $row_overtime->save();
                                    }
                                }
                            }
                            
                        }
                        if($last_attendance&&$exact_in[$key]!=1){
                            $attendance_detail[$row_user->id][$counter_overtime_date]['jam_masuk'][]='';
                            $attendance_detail[$row_user->id][$counter_overtime_date]['jam_keluar'][]='';
                        }

                        $attendance_detail[$row_user->id][$counter_overtime_date]['in'][]=$exact_in[$key];
                        $attendance_detail[$row_user->id][$counter_overtime_date]['out'][]=$exact_out[$key];
                        $attendance_detail[$row_user->id][$counter_overtime_date]['perbedaan_jam_masuk'][]='';
                        $attendance_detail[$row_user->id][$counter_overtime_date]['perbedaan_jam_keluar'][]='';
                        $attendance_detail[$row_user->id][$counter_overtime_date]['time_in'][]=$time_in;
                        $attendance_detail[$row_user->id][$counter_overtime_date]['time_out'][]=$time_out;
                        $attendance_detail[$row_user->id][$counter_overtime_date]['min_time_in'][]=$time_in;
                        $attendance_detail[$row_user->id][$counter_overtime_date]['max_time_out'][]=$time_out;
                        $attendance_detail[$row_user->id][$counter_overtime_date]['nama_shift'][]='lembur';
                        $attendance_detail[$row_user->id][$counter_overtime_date]['shift_id'][]=null;

                    }
                    

                    
            

                    $counter_overtime_date++;
                    $date_overtime_no_schedule->addDay();
                }
                
                
                
                //mencari apakah dia datang tidak checkclock atau pulang tidak checkclock salah tol
                
                
                //melakukan perhitungan datang pulang
                
                foreach($all_exact_in as $key=>$row_exact){
             
                    if($row_exact==1){

                        $counter_arrived_on_time++;
                    }if($row_exact == 0 && $all_exact_out[$key] == 1){
                        
                        $counter_arrived_forget++;
                    }
                    if($all_exact_out[$key]==1){
                        $counter_out_on_time++;
                    }if($all_exact_out[$key] == 0 && $row_exact == 1){
                       
                        
                        $counter_out_forget++;
                    }
                    if($row_exact==1 && $all_exact_out[$key] == 1){
                        $counter_absent++;
                    }if($row_exact == 0 && $all_exact_out[$key] ==0){
                        $counter_alpha++;
                    }
                }

                
                DB::beginTransaction();
                $query_monthly_report = AttendanceMonthlyReport::create([
                    'user_id'                   => $row_user->id,
                    'period_id'			        => $attendance_period->id,
                    'late'                      => $counter_late,
                    'leave_early'               => $counter_leave_early,
                    // 'permit'                    => $counter_permit,
                    'special_occasion'          => $counter_cuti_kusus,
                    'effective_day'             => $counter_effective_day,
                    'absent'                    => $counter_absent,
                    'furlough'                  => $counter_cuti,
                    'sick'                      => $counter_sakit,
                    'outstation'                => $counter_dinas_luar,
                    'dispen'                    => $counter_dispen,
                    'alpha'                     => $counter_alpha,
                    'wfh'                       => $counter_wfh,
                    'arrived_on_time'           => $counter_arrived_on_time,
                    'out_on_time'               => $counter_out_on_time,
                    'out_log_forget'            => $counter_out_forget,
                    'arrived_forget'            => $counter_arrived_forget,
                ]);
                
                try {
                    $query_close = AttendancePeriod::find($request->id);
                    $query_close->status            = "2";
                    $query_close->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                $counter_user_monthly[$row_user->employee_no]=$tipe_punish_counter;
                
                $temp_for_punishment_forget = [];
                if($query_tidak_check_masuk){
                    $temp_for_punishment_forget[]=[
                        'salary_report_user_id' => null,
                        'lookable_type'         =>'punishments',
                        'lookable_id'           =>$query_tidak_check_masuk->id,
                        'type'                  =>2,
                        'nominal'               =>0,
                    ];
                }if($query_tidak_check_pulang){
                    $temp_for_punishment_forget[]=[
                        'salary_report_user_id' => null,
                        'lookable_type'         =>'punishments',
                        'lookable_id'           =>$query_tidak_check_pulang->id,
                        'type'                  =>2,
                        'nominal'               =>0,
                    ];
                }
               //memasukkan data saat user memiliki kelupaan cecklclok masuk
                if(count($date_arrived_forget) != 0){
                    
                    if($query_tidak_check_masuk){
                        
                        $query_presence_report = AttendancePunishment::create([
                            'user_id'                  => session('bo_id'),
                            'period_id'                => $request->id,
                            'employee_id'              => $row_user->id,
                            'punishment_id'            => $query_tidak_check_masuk->id,
                            'frequent'                 => $counter_arrived_forget,
                            'total'                    => $counter_arrived_forget*$query_tidak_check_masuk->price,
                            'dates'                    => implode(',',$date_arrived_forget)
                        ]);
                        $temp_for_punishment_forget[0]['nominal'] = $counter_arrived_forget*$query_tidak_check_masuk->price;
                    }
                }
                 //memasukkan data saat user memiliki kelupaan cecklclok keluar
                if(count($date_out_forget) != 0){
                    
                    if($query_tidak_check_pulang){
                      
                        $query_presence_report = AttendancePunishment::create([
                            'user_id'                  => session('bo_id'),
                            'period_id'                => $request->id,
                            'employee_id'              => $row_user->id,
                            'punishment_id'            => $query_tidak_check_pulang->id,
                            'frequent'                 => $counter_out_forget,
                            'total'                    => $counter_out_forget*$query_tidak_check_pulang->price,
                            'dates'                    => implode(',',$date_out_forget)
                        ]);
                        $temp_for_punishment_forget[1]['nominal'] = $counter_out_forget*$query_tidak_check_pulang->price;
                    }
                }
                //pembuatan tidak cek masuk // keluar untuk ditaruh ke report

            }
           
            $end_time = microtime(true);
            
            
            foreach($attendance_detail as $row_attd){
                foreach($row_attd as $row_attd_detail){
                   if (is_array($row_attd_detail['nama_shift']) && empty(($row_attd_detail['nama_shift']))) {
                    //membuat data kehadiran dimana user absen
                    $query_presence_report = PresenceReport::create([
                        'user_id'                  => $row_attd_detail['user_no'],
                        'period_id'                => $attendance_period->id,
                        'date'                     => $row_attd_detail['date'],
                        'late_status'              => '7',
                        'status'                   => '1',
                    ]);
                    //membuat data harian kehadiran laporan dimana user tidak masuk dan pulang // absen
                    $query_daily_report= AttendanceDailyReports::create([
                        'user_id'                  => $row_attd_detail['user_no'],
                        'masuk'                    => 'tidak check clock',
                        'pulang'                   => 'tidak check clock',
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
                        if($row_masuk == '4' && $row_attd_detail['out'][$key_masuk] == '4'){
                            $status = '8';
                        }
                        if($row_masuk == '3' && $row_attd_detail['out'][$key_masuk] == '3'){
                            $status = '9';
                        }
                        //saat user masuk membuat report yang seperti ini
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
                            'shift_id'                 => $row_attd_detail['shift_id'][$key_masuk] ?? null,
                        ]);
                   }
                }
                }
                
            }
           
            //untuk menghitung denda terlambat
            foreach($counter_user_monthly as $row_user){
                $uid_new = null;
                $counter = 0;
                $minus = 0;
                foreach($row_user as $key=>$row_counter){
               
                    if(!$uid_new){
                        $uid_new=$row_counter['uid'];
                        $query_report_peruser= SalaryReportUser::create([
                            'user_id'                                    => $uid_new,
                            'salary_report_id'                           => $query_salary_report->id,
                            'total_plus'                                 => 0,
                            'total_minus'                                => 0,
                            'total_received'                             => 0,
                        ]);
                        //component hhitung dari master data
                        if($query_report_peruser->user->type_payment == 2){
                            $query_employee_salary_day = EmployeeSalaryComponent::where('user_id', $row_counter['uid'])
                                ->where('salary_component_id',2)
                                ->first();
                            $kali = $query_monthly_report->effective_day * $query_employee_salary_day->nominal;
                        
                            $query_report_peruser->total_plus = $query_report_peruser->total_plus+$kali;
                            $query_salary_reports_detail = SalaryReportDetail::create([
                                'salary_report_user_id' =>$query_report_peruser->id,
                                'lookable_type'         =>'salary_components',
                                'lookable_id'           =>$query_employee_salary_day->salary_component_id,
                                'type'                  =>1,
                                'nominal'               =>$query_employee_salary_day->nominal,
                            ]);
                        }
                        if($query_report_peruser->user->type_payment == 1){
                            $query_employee_salary_month = EmployeeSalaryComponent::where('user_id', $row_counter['uid'])
                                ->where('salary_component_id',1)
                                ->first();
                            $query_salary_reports_detail = SalaryReportDetail::create([
                                'salary_report_user_id' =>$query_report_peruser->id,
                                'lookable_type'         =>'salary_components',
                                'lookable_id'           =>$query_employee_salary_month->salary_component_id,
                                'type'                  =>1,
                                'nominal'               =>$query_employee_salary_month->nominal,
                            ]);
                            $query_report_peruser->total_plus = $query_report_peruser->total_plus+$query_employee_salary_month->nominal;
                        }
                       

                        //pakai 1 dan 2 tidak sama dengan karena ingin mengambil komponen yang bukan gaji harian atau gaji bulanan.
                        $query_employee_salary_component = EmployeeSalaryComponent::where('user_id', $row_counter['uid'])
                        ->whereNotIn('salary_component_id', [1, 2])
                        ->get();
                        

                        foreach($query_employee_salary_component as $row_salary_component){
                            $type = ($row_salary_component->nominal > 0) ? 1 : 2;
                            $query_salary_reports_detail = SalaryReportDetail::create([
                                'salary_report_user_id' =>$query_report_peruser->id,
                                'lookable_type'         =>'salary_components',
                                'lookable_id'           =>$row_salary_component->salary_component_id,
                                'type'                  =>$type,
                                'nominal'               =>$row_salary_component->nominal,
                            ]);
                            if($type==1){
                                
                                $query_report_peruser->total_plus = $query_report_peruser->total_plus+$row_salary_component->nominal;
                            }else{
                               
                                $query_report_peruser->total_minus = $query_report_peruser->total_minus+$row_salary_component->nominal; 
                            }
                            
                        }
                        
                        //lembur itung
                        $lembur_get = OvertimeRequest::whereBetween('date', [$start_date, $end_date])
                        ->where('status', 2)
                        ->get();
                      
                        $total_biasa = 0;
                        $total_kusus = 0;
                        
                        $nominal_biasa = 0 ;
                        $nominal_kusus = 0 ;

                        foreach($lembur_get as $row_lembur){
                            $query_libur = Holiday::where('date',$row_lembur->date)->first();
                            if($query_libur){
                                $total_kusus += $row_lembur->total;
                                $nominal_kusus += $row_lembur->grandtotal;
                            }else{
                                $total_biasa += $row_lembur->total;
                                $nominal_biasa += $row_lembur->grandtotal;
                            }
                        }
                        
                        $query_salary_reports_detail = SalaryReportDetail::create([
                            'salary_report_user_id' =>$query_report_peruser->id,
                            'lookable_type'         =>'overtime_requests',
                            'lookable_id'           => -1,
                            'type'                  =>1,
                            'nominal'               =>$nominal_biasa,
                        ]);
                    
                    
                        $query_salary_reports_detail = SalaryReportDetail::create([
                            'salary_report_user_id' =>$query_report_peruser->id,
                            'lookable_type'         =>'overtime_requests',
                            'lookable_id'           => -2,
                            'type'                  =>1,
                            'nominal'               => $nominal_kusus,
                        ]);
                        $query_report_peruser->total_plus = $query_report_peruser->total_plus+$nominal_kusus+$nominal_biasa;
                    
                        
                    }
                    
                    

                    $string_date = implode(',',$row_counter['date']);
                    if($row_counter['counter']!='0'){
                       
                        $query_presence_report = AttendancePunishment::create([
                            'user_id'                  => session('bo_id'),
                            'period_id'                => $request->id,
                            'employee_id'              => $row_counter['uid'],
                            'punishment_id'            => $row_counter['punish_id'],
                            'frequent'                 => $row_counter['counter'],
                            'total'                    => $row_counter['counter']*$row_counter['price'],
                            'dates'                    => implode(',',$row_counter['date'])
                        ]);

                        $query_salary_reports_detail = SalaryReportDetail::create([
                            'salary_report_user_id' =>$query_report_peruser->id,
                            'lookable_type'         =>'punishments',
                            'lookable_id'           =>$row_counter['punish_id'],
                            'type'                  =>2,
                            'nominal'               =>$row_counter['counter']*$row_counter['price'],
                        ]);
                        $minus += $row_counter['counter']*$row_counter['price']; 
                        $query_report_peruser->total_minus = $query_report_peruser->total_minus+$query_salary_reports_detail->nominal;
                    
                        $query_report_peruser->save();
                    }else{
                        $query_salary_reports_detail = SalaryReportDetail::create([
                            'salary_report_user_id' =>$query_report_peruser->id,
                            'lookable_type'         =>'punishments',
                            'lookable_id'           =>$row_counter['punish_id'],
                            'type'                  =>2,
                            'nominal'               =>0,
                        ]);
                    }
                   
                  
                    if($counter == count($row_counter)-1){
                        $minus_forget = 0;
                        foreach($temp_for_punishment_forget as $row_forget){
                            
                            $query_salary_reports_detail = SalaryReportDetail::create([
                                'salary_report_user_id' =>$query_report_peruser->id,
                                'lookable_type'         =>'punishments',
                                'lookable_id'           =>$row_forget['lookable_id'],
                                'type'                  =>2,
                                'nominal'               =>$row_forget['nominal'],
                            ]);
                            $minus_forget+=$row_forget['nominal'];
                        }
                        $query_report_peruser->total_minus = $query_report_peruser->total_minus+$minus_forget; 
                        $query_report_peruser->total_received = $query_report_peruser->total_plus-$minus-$minus_forget; 

                        $query_report_peruser->save();
                    }
                    $counter++;
                }
            }
           

            $query_employee_reward_or_punishment = EmployeeRewardPunishmentDetail::where('nominal_total','>',0)->get();
            
            
            foreach($query_employee_reward_or_punishment as $row_detail_punr){
                $total_pembayaran = 0;
             
                if($row_detail_punr->nominal_total >= $row_detail_punr->nominal_payment){
                    
                    $query_employee_re_or_pu_payment = EmployeeRewardPunishmentPayment::where('employee_reward_punishment_detail_id',$row_detail_punr->id)
                    ->get();
                    foreach($query_employee_re_or_pu_payment as $row_payment){
                        $total_pembayaran+=$row_payment->nominal;
                    }
                    $sisa = $row_detail_punr->nominal_total-$total_pembayaran;
                  
                    if($sisa > 0 ){
                        if($sisa > $row_detail_punr->nominal_payment){
                            $query_mbeng1=EmployeeRewardPunishmentPayment::create([
                                'user_id'                  => $row_detail_punr->user_id,
                                'period_id'                    => $request->id,
                                'post_date'                   => Carbon::now(),
                                'nominal'                    => $row_detail_punr->nominal_payment,
                                'employee_reward_punishment_detail_id'                     =>$row_detail_punr->id ,
                            ]);
                            
                        }else{
                            $query_mbeng1=EmployeeRewardPunishmentPayment::create([
                                'user_id'                               => $row_detail_punr->user_id,
                                'period_id'                             => $request->id,
                                'post_date'                             => Carbon::now(),
                                'nominal'                               => $sisa,
                                'employee_reward_punishment_detail_id'  =>$row_detail_punr->id ,
                            ]);
                        }
                        
                        if($query_mbeng1){
                            
                            if($row_detail_punr->employeeRewardPunishment->type == 1 ){
                                $query_salary_report_user = SalaryReportUser::where('user_id', $row_detail_punr->user_id)
                                ->whereHas('salaryReport', function($query) use ($request) {
                                    $query->where('period_id', $request->id)
                                        ->orderBy('created_at', 'desc');
                                })
                                ->orderBy('created_at', 'desc')
                                ->first();
                                
                               
                                if( $query_salary_report_user){
                             
                                    $query_salary_report_user->total_plus = $query_salary_report_user->total_plus+$query_mbeng1->nominal;
                        
                                    $query_salary_report_user->total_received = $query_salary_report_user->total_received+$query_mbeng1->nominal;
                                    $query_salary_report_user->save();
                                }
                                
                            }
                            if($row_detail_punr->employeeRewardPunishment->type == 2 ){
                                $query_salary_report_user = SalaryReportUser::where('user_id', $row_detail_punr->user_id)
                                ->whereHas('salaryReport', function($query) use ($request) {
                                    $query->where('period_id', $request->id)
                                        ->orderBy('created_at', 'desc');
                                })
                                ->orderBy('created_at', 'desc')
                                ->first();
                              
                                
                                if( $query_salary_report_user){
                                    $query_salary_report_user->total_minus = $query_salary_report_user->total_minus+$query_mbeng1->nominal;
                                    $query_salary_report_user->total_received = $query_salary_report_user->total_received-$query_mbeng1->nominal;
                                    $query_salary_report_user->save();
                                }
                              
                            }
                        }
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

    public function reopen(Request $request){
        $query_salary_report = SalaryReport::where('period_id',CustomHelper::decrypt($request->id))->first();
        
        $query_salary_report->salaryReportUser->each(function ($salaryReportUser) {
            $salaryReportUser->salaryReportDetail()->delete();
        });
        $query_salary_report->salaryReportUser()->delete();
        $query_salary_report->salaryReportTemplate()->delete();
        $query_salary_report->delete();
        AttendanceMonthlyReport::where('period_id',CustomHelper::decrypt($request->id))->delete();
        AttendancePunishment::where('period_id',CustomHelper::decrypt($request->id))->delete();
        PresenceReport::where('period_id',CustomHelper::decrypt($request->id))->delete();
        AttendanceDailyReports::where('period_id',CustomHelper::decrypt($request->id))->delete();
        EmployeeRewardPunishmentPayment::where('period_id',CustomHelper::decrypt($request->id))->delete();
        if($query_salary_report){
            $response =[
                'status'   =>200,
                'message'  =>'sep',
            ];
            $query_close = AttendancePeriod::find(CustomHelper::decrypt($request->id));
            info($query_close);
            $date = Carbon::parse($query_close->start_date)->copy();
            while($date->lte($query_close->end_date)){
                $query_data = EmployeeSchedule::whereDate('date', $date->toDateString())->get();
                foreach($query_data as $key=>$row_schedule){
                    $query_data[$key]->is_closed = '1';
                    $query_data[$key]->save();
                }
                
                $date->addDay();
            }
            $query_close->status            = "1";
            $query_close->save();
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
                    return $date->format('d/m/Y'); // Format the date as 'd/m/Y'
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
                    if($row_status=='8'){
                        $string_table.="<td style='color: black;    font-weight: 700;'>Ada Ijin</td>";
                    }
                    if($row_status=='9'){
                        $string_table.="<td style='color: pink;    font-weight: 700;'>Cuti Melahirkan</td>";
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
                $x=0;
                if($row_daily['date']==$row_dates){
                    $nama_shift="";
                    $time_in = '';
                    $min_time_in='';
                    $max_time_out='';
                    $time_out='';
                    if($row_daily->shift()->exists()){
                        $time_in = $row_daily->shift->time_in;
                        $min_time_in = Carbon::parse($time_in)->subHours($row_daily->shift->tolerant)->toTimeString();
                        $time_out = $row_daily->shift->time_out;
                        $max_time_out = Carbon::parse($time_out)->addHours($row_daily->shift->tolerant)->toTimeString();
                    }else{
                        info($row_daily['date']);
                        $overtime_request_perday = OvertimeRequest::where('date',Carbon::createFromFormat('d/m/Y', $row_daily['date'])->format('Y-m-d'))
                            ->where('account_id',$row_daily->user_id)
                            ->where('schedule_id',null)
                            ->get();
                        info($overtime_request_perday);
                        foreach($overtime_request_perday as $key_overtime=>$row_overtime){
                           if($key_overtime == $x){
                                $time_in = $row_overtime->time_in;
                                $min_time_in='lembur';
                                $max_time_out='lembur';
                                $time_out=$row_overtime->time_out;
                                $nama_shift = 'lembur';
                           } 
                        }
                        $min_time_in = "";
                        
                        $max_time_out ="";
                    }
                    $attendanceDetail[$key_date][]=[
                        'user_id'=>$row_daily->user->employee_no??'',
                        'user_name'=>$row_daily->user->name??'',
                        'nama_shift'=>$row_daily->shift->name ?? $nama_shift,
                        'min_masuk'=>$min_time_in ?? '',
                        'max_keluar'=>$max_time_out ?? '',
                        'limit_masuk'=>$row_daily->shift->time_in ?? $time_in,
                        'limit_keluar'=>$row_daily->shift->time_out ?? $time_out,
                        'masuk'=>$row_daily->masuk,
                        'pulang'=>$row_daily->pulang,
                        'date'=>$row_daily->date,
                        'status'=>$row_daily->status(),
                    ];
                    $x++;
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

    public function punishmentReport(Request $request){
        $dailyReports  = AttendancePunishment::where('period_id',CustomHelper::decrypt($request->id))
                      
        ->get();
        
        $distinctUserIds = $dailyReports->pluck('employee_id')->unique()->toArray();

        $attendanceDetail=[];
      
            foreach($dailyReports as $key_daily=>$row_punishment){
                
                $attendanceDetail[$key_daily][]=[
                    'user_id'=>$row_punishment->employee->employee_no??'',
                    'user_name'=>$row_punishment->employee->name??'',
                    'nama_periode'=>$row_punishment->period->name ?? 'tidak ada shift',
                    'tipe_punish'=>$row_punishment->punishment->name ?? '',
                    'frequent'=>$row_punishment->frequent ?? '',
                    'date'=>$row_punishment->dates,
                    'total'=> $row_punishment->total,
                ];
        
            }
         

        $string_table="";
        $iterasi=0;
        foreach($attendanceDetail as $row_detail){
            foreach($row_detail as $key_daily=>$row_punish){
                $iterasi++;
                $string_table.="
                <tr>      
                    <td class='center-align'>".$iterasi."</td>
                    <td class='center-align'>".$row_punish['user_id']."</td>
                    <td class='center-align'>".$row_punish['user_name']."</td>
                    <td class='center-align'>".$row_punish['nama_periode']."</td>
                    <td class='center-align'>".$row_punish['tipe_punish']."</td>
                    <td class='center-align'>".$row_punish['frequent']."</td>
                    <td class='center-align'>".$row_punish['date']."</td>
                    <td class='center-align'>".$row_punish['total']."</td>
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

    public function salaryReport(Request $request){
        $salary_report  = SalaryReport::where('period_id',CustomHelper::decrypt($request->id))
        ->first();
        info($salary_report);

        $salary_report_template = SalaryReportTemplate::where('salary_report_id',$salary_report->id)->get();
        info($salary_report_template);
        $salary_report_user = SalaryReportUser::where('salary_report_id', $salary_report->id)
        ->whereHas('user', function ($query) {
            $query->where('type_payment', 1);
        })
        ->get();
        info($salary_report_user);
        $salary_report_user_harian = SalaryReportUser::where('salary_report_id', $salary_report->id)
        ->whereHas('user', function ($query) {
            $query->where('type_payment', 2);
        })
        ->get();
        info($salary_report_user_harian);
        $plant = Place::where('status',1)->get();
        $plant_for_user = [];
        $salary_for_perday_user = [];
        $title = [];
        foreach($plant as $row){
            if (!isset($title[$row->id])) {
                $title[$row->id] = '';
            }
            $title[$row->id]=$row->name; 
        }
        //dinamis plant
        foreach($plant as $row_plant){
            if (!isset($plant_for_user[$row_plant->id])) {
                $plant_for_user[$row_plant->id] = '';
                $salary_for_perday_user[$row_plant->id] ='';
            }

            $plant_for_user[$row_plant->id] .='<tr>
            <th>Nama</th>
            <th>NIK</th>
            ';

            $salary_for_perday_user[$row_plant->id] .='<tr>
            <th>Nama</th>
            <th>NIK</th>
            ';
        }
        foreach($salary_report_template as $row_template){
            if($row_template->lookable_type == 'salary_components' && $row_template->lookable_id != 2 ){
                foreach($plant_for_user as $key=>$rowws){
                    $plant_for_user[$key].='<th>'.$row_template->salaryComponent->name.'</th>';
                }
            }
            if($row_template->lookable_type == 'salary_components' && $row_template->lookable_id != 1 ){
                foreach($salary_for_perday_user as $key=>$rowws){
                    $salary_for_perday_user[$key].='<th>'.$row_template->salaryComponent->name.'</th>';
                }
            }
            if($row_template->lookable_type == 'punishments'){
                $plant_for_user[$row_template->punishment->place_id].='<th>'.$row_template->punishment->name.'</th>';
                $salary_for_perday_user[$row_template->punishment->place_id].='<th>'.$row_template->punishment->name.'</th>';
            }
            
        }

        foreach($plant_for_user as $key=>$rowws){
            $plant_for_user[$key].='
            <th>Total Lembur</th>
            <th>Total Lembur Kusus</th>
            <th>Total Hukuman</th>
            <th>Total Denda</th>
            <th>Total Tunjangan Kinerja</th>
            <th>Total Gaji</th>
            </tr>';

            $salary_for_perday_user[$key].='
            <th>Total Lembur</th>
            <th>Total Lembur Kusus</th>
            <th>Total Hukuman</th>
            <th>Total Denda</th>
            <th>Total Tunjangan Kinerja</th>
            <th>Total Gaji</th>
            </tr>';
        }

        foreach($salary_report_user as $row_user_report){
            $query_detail = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','!=','overtime_requests')->get();
            $query_detail_overtime = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','overtime_requests')->get();
            $plant_for_user[$row_user_report->user->place_id].='
                    <tr>
                    <td>'.$row_user_report->user->name.'</td>
                    <td>'.$row_user_report->user->employee_no.'</td>';
            $nominal_plus = 0 ;
            $nominal_minus = 0 ;
            foreach($salary_report_template as $row_template){
                
                foreach($query_detail as $row_detail){
                    
                    if($row_detail->lookable_type == $row_template->lookable_type && $row_detail->lookable_id == $row_template->lookable_id){
                        if($row_detail->lookable_type == 'punishments'){
                            $nominal_minus+=$row_detail->nominal;
                        }
                       
                        $plant_for_user[$row_user_report->user->place_id].='
                            <td>'.$row_detail->nominal.'</td>
                        ';
                    }
                    
                }
            }
            $query_payment = EmployeeRewardPunishmentPayment::where('period_id',CustomHelper::decrypt($request->id))->
            where('user_id',$row_user_report->user->id)->get();
            
            foreach($query_payment as $row_payment){
               
                if($row_payment->employeeRewardPunishmentDetail->employeeRewardPunishment->type == 1){
                    $nominal_plus+=$row_payment->nominal; 
                }
            }
            foreach($query_detail_overtime as $row_overtime_detail){
                $nominal_plus += $row_overtime_detail->nominal ;
                $plant_for_user[$row_user_report->user->place_id].='
                    <td>'.$row_overtime_detail->nominal.'</td>
                ';
                
            }  
           
            $plant_for_user[$row_user_report->user->place_id].='
                <td>'.$nominal_minus.'</td>
                <td>'.$row_user_report->total_minus.'</td>
                <td>'.$nominal_plus.'</td>
                <td>'.$row_user_report->total_received.'</td>
                </tr>
            ';
            
        }

        foreach($salary_report_user_harian as $row_user_report){
            $query_detail = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','!=','overtime_requests')->get();
            $query_detail_overtime = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','overtime_requests')->get();
            $salary_for_perday_user[$row_user_report->user->place_id].='
                    <tr>
                    <td>'.$row_user_report->user->name.'</td>
                    <td>'.$row_user_report->user->employee_no.'</td>';
            $nominal_plus = 0 ;
            $nominal_minus = 0 ;
            
            foreach($salary_report_template as $row_template){
                
                foreach($query_detail as $row_detail){
                    
                    if($row_detail->lookable_type == $row_template->lookable_type && $row_detail->lookable_id == $row_template->lookable_id){
                        if($row_detail->lookable_type == 'punishments'){
                            $nominal_minus+=$row_detail->nominal;
                        }
                        $salary_for_perday_user[$row_user_report->user->place_id].='
                            <td>'.$row_detail->nominal.'</td>
                        ';
                       
                    }
                    
                }
            }
            $query_payment = EmployeeRewardPunishmentPayment::where('period_id',CustomHelper::decrypt($request->id))->
            where('user_id',$row_user_report->user->id)->get();
            
            foreach($query_payment as $row_payment){
              
                if($row_payment->employeeRewardPunishmentDetail->employeeRewardPunishment->type == 1){
                    $nominal_plus+=$row_payment->nominal; 
                }
            }
            foreach($query_detail_overtime as $row_overtime_detail){
                $nominal_plus += $row_overtime_detail->nominal ;
               
                $salary_for_perday_user[$row_user_report->user->place_id].='
                    <td>'.$row_overtime_detail->nominal.'</td>
                ';
            }  
            $salary_for_perday_user[$row_user_report->user->place_id].='
                <td>'.$nominal_minus.'</td>
                <td>'.$row_user_report->total_minus.'</td>
                <td>'.$nominal_plus.'</td>
                <td>'.$row_user_report->total_received.'</td>
                </tr>
            ';
           
            
        }

       
        $response = [
            'status'    => 200,
            'message'   => $plant_for_user,
            'perday'    => $salary_for_perday_user,
            'title'     => $title,
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
