<?php

namespace App\Http\Controllers\Dashboard;
use App\Models\Attendances;
use App\Models\EmployeeSchedule;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Dashboard',
            'content'       => 'admin.dashboard.main',
            'itemcogs'      => ItemCogs::orderByDesc('date')->orderByDesc('id')->get(),
            'itemstocks'    => ItemStock::all(),
            /* 'pr'        => PurchaseRequest::all(),
            'pr1'       => PurchaseRequest::find(5),
            'po'        => PurchaseOrder::find(1), */
        ];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Calculate the start and end dates
        $startDate = Carbon::create($currentYear, $currentMonth - 1, 21);
        $endDate = Carbon::create($currentYear, $currentMonth, 20);
        
        // Retrieve employee schedules and attendances within the date range
        $schedule = EmployeeSchedule::where('user_id', 1)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $attendance = Attendances::where('employee_no', '123017')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

   
        $date = $startDate->copy();
        $attendance_detail=[];
        $total_telat_masuk=0;
        $total_telat_keluar=0;
        $total_absen=0;
        while ($date->lte($endDate)) {
            $schedule_otd = null;
            $attendance_otd=[];
            foreach($schedule as $row_schedule){
               
                if ($date->eq(Carbon::parse($row_schedule->date))) {
                    
                    $schedule_otd = $row_schedule;
                }
            }
            foreach($attendance as $row_attendance){
               
                if ($date->toDateString() === Carbon::parse($row_attendance->date)->toDateString()) {
            
                    $attendance_otd[] = $row_attendance;
                }
            }
            if($schedule_otd){
     
                if($attendance_otd){
                    $exact_in  = 0;
                    $exact_out = 0;
                    foreach($attendance_otd as $row_attendance_filter){
                        
                        $dateAttd = Carbon::parse($row_attendance_filter->date);
                        info($dateAttd);
                        $timePart = $dateAttd->format('H:i:s');
                        $min_time_in = $schedule_otd->shift->min_time_in;
                        $time_in = $schedule_otd->shift->time_in;
                        $max_time_out = $schedule_otd->shift->max_time_out;
                        $time_out = $schedule_otd->shift->time_out;
                        if ($timePart >= $min_time_in && $timePart <= $time_in) {
                            $exact_in = 1 ;  
                        }
                    
                        if ($timePart >= $time_out && $timePart <= $max_time_out) {
                          
                            $exact_out = 1 ;
                              
                        }
                    }
                    $attendance_detail []=[
                        'date' => Carbon::parse($schedule_otd->date)->format('d/m/Y'),
                        'in'   => $exact_in,
                        'out'  => $exact_out,
                    ];
                    if($exact_in!=1){
                        $total_telat_masuk++;
                    }
                    if($exact_out!=1){
                        $total_telat_keluar++;
                    }
                    if($exact_in!=1&&$exact_out!=1){
                        $total_absen++;
                    }
                    // hardarti if(t1-t4 tapi exact_in is 1 then it will not be any t1-t4)
                    //hardarti if(1 hari ada lebih dari 1 shift shift di loop di ambil min time in shift terpertama dan mengambil max timeout shift terakhir)
                    // jadi apabila ada shift yang berganti hari alangkah baiknya sy mengambil attendance dari database yang memiliki tanggal yang sama
                }
            }
            
            
            
            $date->addDay();
        }
        usort($attendance_detail, function ($a, $b) {
            $dateA = Carbon::createFromFormat('d/m/Y', $a['date'])->timestamp;
            $dateB = Carbon::createFromFormat('d/m/Y', $b['date'])->timestamp;
            return $dateB - $dateA;
        });
        
        $data['attendance'] = $attendance_detail;
        $data['total_absen'] = $total_absen;
        $data['attendance_count'] = count($attendance_detail)-$total_absen;
        $data['start_date']= $startDate->format('d/m/Y');
        $data['end_date']=  $endDate->format('d/m/Y');
        $data['total_telat_keluar']=$total_telat_keluar;
        $data['total_telat_masuk']=$total_telat_masuk;

        return view('admin.layouts.index', ['data' => $data]);
    }
}
