<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class ExportOutstandingDP implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = PurchaseDownPayment::whereIn('status',['2','3'])->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->post_date));
            $entry["note"] = $row->note;
            $entry["status"] = $row->statusRaw();
            $entry["due_date"] = $row->due_date;
            $entry["kode_bp"] = $row->supplier->employee_no;
            $entry["nama_bp"] = $row->supplier->name;
            $entry["tagihan"] = number_format($row->grandtotal,2,',','.');
            $entry["dibayar"] = number_format($row->totalPaid(),2,',','.');
            $sisa = $row->getTotalPaid();
            $entry["sisa"] = number_format($sisa,2,',','.');
            if($sisa > 0){
                $array[] = $entry;
            }
        }
        
        info($array);
        
        return view('admin.exports.outstanding_down_payment', [
            'data' => $array,
            
        ]);
    }
}
