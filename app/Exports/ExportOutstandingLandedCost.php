<?php

namespace App\Exports;

use App\Models\LandedCost;
use App\Models\LandedCostFee;
use App\Models\LandedCostFeeDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingLandedCost implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $date,$type;

    public function __construct(string $date, string $type)
    {
        $this->date = $date ? $date : '';
        $this->type = $type ? $type : '';
    }

    public function view(): View
    {
        $data = LandedCostFeeDetail::whereHas('landedCost',function($query){
            $query->whereIn('status',['2','3','8'])
                ->where('post_date','<=',$this->date)/* 
                ->whereHas('landedCostDetail',function($query){
                    $query->whereDoesntHave('landedCostDetailSelf');
                }) */;
        })
        ->where(function($query){
            if($this->type !== 'all'){
                $query->whereHas('landedCostFee',function($query){
                    $query->where('type',$this->type);
                });
            }
        })
        ->get();

        $array=[];
        foreach($data as $row){
            $balance = $row->balanceInvoiceByDate($this->date);
            if($balance > 0 && !$row->landedCost->hasCancelDocumentByDate($this->date)){
                if(!$row->totalLandedCostFeeSelfByDate($this->date)){
                    $entry = [];
                    $entry["code"]=$row->landedCost->code;
                    $entry["post_date"] = date('d/m/Y',strtotime($row->landedCost->post_date));
                    $entry["note"] = preg_replace('/[\x00-\x1F\x7F]/', '', $row->landedCost->note ?? '');
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
                    $entry["total_rupiah"] = number_format($row->landedCost->total*$row->landedCost->currency_rate,2,',','.');
                    $entry["tagihan"] = number_format($row->total * $row->landedCost->currency_rate,2,',','.');
                    $entry["dibayar"] = number_format($row->totalInvoiceByDate($this->date) * $row->landedCost->currency_rate,2,',','.');
                    $entry["sisa"] = number_format($balance * $row->landedCost->currency_rate,2,',','.');
                    $entry["grpo_no"] = $row->landedCost->getGoodReceiptNo();
                    $array[] = $entry;
                }
            }
        }
        activity()
            ->performedOn(new LandedCostFeeDetail())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export outstanding Lc.');

        return view('admin.exports.outstanding_lc', [
            'data' => $array,

        ]);
    }
}
