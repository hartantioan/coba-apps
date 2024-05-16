<?php

namespace App\Exports;

use App\Models\FundRequest;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingFundRequest implements FromView,ShouldAutoSize
{
    public function view(): View
    {
        $data = FundRequest::whereIn('status',['2','3'])->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->post_date));
            $entry["nama_supp"]=$row->account->name;
            $entry["note"] = $row->note;
            $entry["status"] = $row->statusRaw();
            $entry["grandtotal"] = number_format($row->grandtotal,2,',','.');
            $entry["total_pr"] = number_format($row->totalPaymentRequest(),2,',','.');
            $entry["tunggakan"] = number_format($row->balancePaymentRequest(),2,',','.');
            
            if($row->balancePaymentRequest()> 0){
                $array[] = $entry;
            }
        }
        
        
        return view('admin.exports.outstanding_fr', [
            'data' => $array,
            
        ]);
    }
}
