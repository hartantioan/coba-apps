<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingInvoice implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = PurchaseInvoice::whereIn('status',['2','3'])->whereNull('status')->get();
        
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->post_date));
            $entry["note"] = $row->note;
            $entry["status"] = $row->statusRaw();
            $entry["due_date"] = $row->due_date;
            $entry["kode_bp"] = $row->user->code;
            $entry["nama_bp"] = $row->user->name;
            $entry["tagihan"] = number_format($row->balance,2,',','.');
            $entry["dibayar"] = number_format($row->getTotalPaid(),2,',','.');
            $sisa = $row->balance - $row->getTotalPaid();
            $entry["sisa"] = number_format($sisa,2,',','.');
            if($sisa > 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_invoice', [
            'data' => $array,
            
        ]);
    }
}
