<?php

namespace App\Exports;

use App\Models\PresenceReport;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPresenceReport implements FromView,ShouldAutoSize,WithTitle
{
    public function __construct(string $period_id)
    {
        $this->period_id = $period_id ? $period_id : '';

    }

    public function title(): string
    {
        return 'Presence Report'; // Set the custom name for the first sheet
    }
    public function view(): View
    {
       
        $distinctUserIds = PresenceReport::where('period_id',$this->period_id)->groupBy('user_id')->pluck('user_id')->toArray();
    
        $distinctDates = PresenceReport::where('period_id', $this->period_id)
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
        info($distinctDates);
        info($distinctUserIds);
        foreach($distinctUserIds as $key_user=>$user_id){
            foreach($distinctDates as $key_dates=>$row_dates){
                $userDetail[$key_user][$key_dates]=[
                    'user_name'     =>'',
                    'late_status'   =>[],
                    'date'          =>'',
                    'nama_shift'    =>[],
                ];
                foreach(PresenceReport::where('user_id',$user_id)->where('date',$row_dates)->where('period_id',$this->period_id)->get() as $key_presence=>$row_presence){
                    $userDetail[$key_user][$key_dates]['user_name']=$row_presence->user->name??'';
                    $userDetail[$key_user][$key_dates]['date']=$row_dates;
                    $userDetail[$key_user][$key_dates]['nama_shift'][]=$row_presence->nama_shift;
                    $userDetail[$key_user][$key_dates]['late_status'][]=$row_presence->late_status;
                }
            }
        }

        info($userDetail);
        return view('admin.exports.attendance_presence_report', [
            'data' => $userDetail,
        ]);
    }
}
