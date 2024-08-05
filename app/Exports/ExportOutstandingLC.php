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
            $entry["user_code"] = $row->landedCost->user->employee_no;
            $entry["user_name"] = $row->landedCost->user->name;
            $entry["status"] = $row->landedCost->statusRaw();
            $entry["void_user"] = $row->landedCost->voidUser->name ?? '';
            $entry["void_note"] = $row->landedCost->void_note ?? '';
            $entry["void_date"] = $row->landedCost->void_date ?? '';
            $entry["delete_note"] = $row->landedCost->delete_note ?? '';
            $entry["delete_user"] = $row->landedCost->deleteUser->name ?? '';
            $entry["delete_date"] = $row->landedCost->delete_date ?? '';
            $entry["done_user"] = $row->landedCost->doneUser->name ?? '';
            $entry["done_note"] = $row->landedCost->done_date ?? '';
            $entry["done_date"] = $row->landedCost->done_note ?? '';
            $entry["due_date"] = $row->landedCost->due_date;
            $entry["currency"] = $row->landedCost->currency->code;
            $entry["kode_vendor"] = $row->landedCost->vendor->employee_no ?? '';
            $entry["nama_vendor"] = $row->landedCost->vendor->name ?? '';
            $entry["kode_bp"] = $row->landedCost->supplier->employee_no;
            $entry["nama_bp"] = $row->landedCost->supplier->name;
            $entry["kode_biaya"] = $row->landedCostFee->code;
            $entry["nama_biaya"] = $row->landedCostFee->name;
            $entry["kode_coa"] = $row->landedCostFee->coa->code;
            $entry["nama_coa"] = $row->landedCostFee->coa->name;
            $entry["total_rupiah"] = number_format($row->landedCost->grandtotal*$row->landedCost->currency_rate,2,',','.');
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
