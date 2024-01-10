<?php

namespace App\Exports;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\SalaryReport;
use App\Models\SalaryReportTemplate;
use App\Models\SalaryReportDetail;
use App\Models\SalaryReportUser;
use App\Models\Place;
use App\Models\EmployeeRewardPunishmentPayment;
class ExportSalaryReport implements  FromView,ShouldAutoSize,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }

    public function title(): string
    {
        return 'Report Gaji Karyawan Payment Bulanan'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $salary_report  = SalaryReport::where('period_id', $this->period_id)
        ->first();

        $salary_report_template = SalaryReportTemplate::where('salary_report_id',$salary_report->id)->get();
        
        $salary_report_user = SalaryReportUser::where('salary_report_id', $salary_report->id)
        ->whereHas('user', function ($query) {
            $query->where('type_payment', 1);
        })
        ->get();

        $plant = Place::where('status',1)->get();
        $plant_for_user = [];
        $title = [];
        
        foreach($plant as $row){
            if (!isset($title[$row->id])) {
                $title[$row->id] = '';
            }
            $title[$row->id]=$row->name; 
        }
        foreach($plant as $row_plant){
            if (!isset($plant_for_user[$row_plant->id])) {
                $plant_for_user[$row_plant->id] = [];
               
            }

            $plant_for_user[$row_plant->id]['thead'][] ='Nama';
            $plant_for_user[$row_plant->id]['thead'][] ='NIK';

        }
        foreach($salary_report_template as $row_template){
            if($row_template->lookable_type == 'salary_components' && $row_template->lookable_id != 2 ){
                foreach($plant_for_user as $key=>$rowws){
                    $plant_for_user[$key]['thead'][]=$row_template->salaryComponent->name;
                }
            }
            if($row_template->lookable_type == 'punishments'){
                $plant_for_user[$row_template->punishment->place_id]['thead'][]=$row_template->punishment->name;
            }
            
        }
        foreach($plant_for_user as $key=>$rowws){
            $plant_for_user[$key]['thead'][]='Total Lembur';
            $plant_for_user[$key]['thead'][]='Total Lembur Kusus';
            $plant_for_user[$key]['thead'][]='Total Hukuman';
            $plant_for_user[$key]['thead'][]='Total Denda';
            $plant_for_user[$key]['thead'][]='Total Tunjangan Kinerja';
            $plant_for_user[$key]['thead'][]='Total Gaji';
        }

        foreach($salary_report_user as $row_user_report){
            $query_detail = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','!=','overtime_requests')->get();
            $query_detail_overtime = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','overtime_requests')->get();
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_user_report->user->name;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_user_report->user->employee_no;
            
            $nominal_plus = 0 ;
            $nominal_minus = 0 ;
            foreach($salary_report_template as $row_template){
                
                foreach($query_detail as $row_detail){
                    
                    if($row_detail->lookable_type == $row_template->lookable_type && $row_detail->lookable_id == $row_template->lookable_id){
                        if($row_detail->lookable_type == 'punishments'){
                            $nominal_minus+=$row_detail->nominal;
                        }
                       
                        $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_detail->nominal;
                    }
                    
                }
            }
            $query_payment = EmployeeRewardPunishmentPayment::where('period_id',$this->period_id)->
            where('user_id',$row_user_report->user->id)->get();
            foreach($query_payment as $row_payment){
               
                if($row_payment->employeeRewardPunishmentDetail->employeeRewardPunishment->type == 1){
                    $nominal_plus+=$row_payment->nominal; 
                }
            }
            foreach($query_detail_overtime as $row_overtime_detail){
                $nominal_plus += $row_overtime_detail->nominal ;
                $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_overtime_detail->nominal;
                
            }  
           
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$nominal_minus;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_user_report->total_minus;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$nominal_plus;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][]=$row_user_report->total_received;
            
        }
            
      
        return view('admin.exports.salary_report', [
            'data' => $plant_for_user,
            'title'=> $title,
        ]);
        
    }
}
