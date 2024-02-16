<?php

namespace App\Exports;

use App\Models\FundRequest;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingFundRequest implements FromView,ShouldAutoSize
{
    public function view(): View
    {
        $data = FundRequest::whereIn('status',['2','3'])->get();
        info($data);
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->post_date));
            $entry["nama_supp"]=$row->account->name;
            $entry["note"] = $row->note;
            $entry["status"] = $row->statusRaw();
            $entry["grandtotal"] = number_format($row->grandtotal,3,',','.');
            $entry["total_pr"] = number_format($row->totalPaymentRequest(),3,',','.');
            $entry["tunggakan"] = number_format($row->balancePaymentRequest(),3,',','.');
            
            if($row->balancePaymentRequest()> 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_fr', [
            'data' => $array,
            
        ]);
    }
}
