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
            $plant_for_user[$row_plant->id]['thead'][] ='Bagian';
            $plant_for_user[$row_plant->id]['thead'][] ='Total Hari';

        }
        $total_component = [];
        foreach($salary_report_template as $row_template){
            if($row_template->lookable_type == 'salary_components' && $row_template->lookable_id != 1 ){
                foreach($plant_for_user as $key=>$rowws){
                    $total_component[$row_template->salaryComponent->name]= 0 ;
                    $plant_for_user[$key]['thead'][]=$row_template->salaryComponent->name;
                }
            }
            if($row_template->lookable_type == 'punishments'){
                $total_component[$row_template->punishment->name]= 0 ;
                $plant_for_user[$row_template->punishment->place_id]['thead'][]=$row_template->punishment->name;
            } 
            
        }
        $total_component['Total Lembur Kusus']= 0 ;
        $total_component['Total Lembur']= 0 ;
        $total_component['Total Hari']= 0 ;
        foreach($plant_for_user as $key=>$rowws){
            $plant_for_user[$key]['thead'][]='Total Lembur';
            $plant_for_user[$key]['thead'][]='Total Lembur Kusus';
            $plant_for_user[$key]['thead'][]='Total Hukuman';
            $plant_for_user[$key]['thead'][]='Total Denda';
            $plant_for_user[$key]['thead'][]='Total Tunjangan Kinerja';
            $plant_for_user[$key]['thead'][]='Total Gaji';
        }
        $total_semua = 0;
        $total_efektif_day = 0;
        $total_hukuman = 0;
        $total_minus_denda = 0;
        $total_tunjangan=0;
        foreach($salary_report_user as $key_tbody=>$row_user_report){
            $query_detail = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','!=','overtime_requests')->get();
            $query_detail_overtime = SalaryReportDetail::where('salary_report_user_id',$row_user_report->id)
            ->where('lookable_type','overtime_requests')->get();
            $query_monthly_report_by_user = AttendanceMonthlyReport::where('period_id', $this->period_id)->
            where('user_id',$row_user_report->user->id)->first();
            $total_efektif_day += $query_monthly_report_by_user->effective_day;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=$row_user_report->user->name;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=$row_user_report->user->employee_no;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=$row_user_report->user->position->name;
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=$query_monthly_report_by_user->effective_day;
            $nominal_plus = 0 ;
            $nominal_minus = 0 ;
            foreach($salary_report_template as $row_template){
                
                foreach($query_detail as $row_detail){
                    
                    if($row_detail->lookable_type == $row_template->lookable_type && $row_detail->lookable_id == $row_template->lookable_id){
                        if($row_detail->lookable_type == 'punishments'){
                            $nominal_minus+=$row_detail->nominal;
                            $total_component[$row_template->punishment->name]+=$row_detail->nominal;
                        }
                        if($row_detail->lookable_type == 'salary_components'){
                            $total_component[$row_template->salaryComponent->name]+=$row_detail->nominal;
                           
                        }
                        
                        $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($row_detail->nominal,2,',','.');
                        
                        
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
               
                $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($row_overtime_detail->nominal,2,',','.');
                if($row_overtime_detail->lookable_id == -1 ){
                        
                    $total_component['Total Lembur']+=$row_overtime_detail->nominal;
                }
                if( $row_overtime_detail->lookable_id == -2){
                    
                    $total_component['Total Lembur Kusus']+=$row_overtime_detail->nominal;
                }
            }  
           
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($nominal_minus,2,',','.');
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($row_user_report->total_minus,2,',','.');
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($nominal_plus,2,',','.');
            $plant_for_user[$row_user_report->user->place_id]['tbody'][$key_tbody][]=number_format($row_user_report->total_received,2,',','.');
            $total_semua+=$row_user_report->total_received;
            $total_hukuman+=$nominal_minus;
            $total_minus_denda += $row_user_report->total_minus;
            $total_tunjangan +=$nominal_plus; 
        }
        $total_component['Total Hukuman']=$total_hukuman;
        $total_component['Total Denda']=$total_minus_denda;     
        $total_component['Total Tunjangan Kinerja']=$total_tunjangan;     
        $total_component['Total Gaji']=$total_semua;          
        $formattedData = array_map(function ($value) {
            return is_numeric($value) ? number_format($value, 2, ',', '.') : $value;
        }, $total_component);
        
      
        return view('admin.exports.salary_report', [
            'data' => $plant_for_user,
            'last_total'=>$formattedData,
            'title'=> $title,
        ]);
        
    }
}
