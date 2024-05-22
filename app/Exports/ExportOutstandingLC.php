<?php

namespace App\Exports;

use App\Models\LandedCost;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingLC implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = LandedCost::whereIn('status',['2'])->whereNull('status')->get();
        
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->post_date));
            $entry["note"] = $row->note;
            $entry["status"] = $row->statusRaw();
            $entry["due_date"] = $row->due_date;
            $entry["kode_bp"] = $row->supplier->code;
            $entry["nama_bp"] = $row->supplier->name;
            $entry["tagihan"] = number_format($row->grandtotal,2,',','.');
            $entry["dibayar"] = number_format($row->totalInvoice(),2,',','.');
            $entry["sisa"] = number_format($row->balanceInvoice(),2,',','.');
            if($row->balanceInvoice() > 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_lc', [
            'data' => $array,
            
        ]);
    }
}
