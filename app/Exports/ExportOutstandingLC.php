<?php

namespace App\Exports;

use App\Models\LandedCost;
use App\Models\LandedCostFeeDetail;
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
        $data = LandedCostFeeDetail::whereHas('landedCost',function($query){
            $query->whereIn('status',['2','3']);
        })->get();
        
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->landedCost->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->landedCost->post_date));
            $entry["note"] = $row->landedCost->note;
            $entry["status"] = $row->landedCost->statusRaw();
            $entry["due_date"] = $row->landedCost->due_date;
            $entry["kode_bp"] = $row->landedCost->supplier->code;
            $entry["nama_bp"] = $row->landedCost->supplier->name;
            $entry["tagihan"] = number_format($row->landedCost->grandtotal,2,',','.');
            $entry["dibayar"] = number_format($row->landedCost->totalInvoice(),2,',','.');
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
