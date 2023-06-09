<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportPurchaseMemo implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $company = null, string $account = null, string $start_date = null, string $finish_date = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->account = $account ? $account : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.purchase_memo', [
            'data' => PurchaseMemo::where(function($query){
                if($this->search) {
                    $query->where(function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('total', 'like', "%$this->search%")
                            ->orWhere('tax', 'like', "%$this->search%")
                            ->orWhere('wtax', 'like', "%$this->search%")
                            ->orWhere('grandtotal', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }

                if($this->start_date && $this->finish_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('post_date','<=', $this->finish_date);
                }

                if($this->account){
                    $query->whereIn('account_id',$this->account);
                }
                
                if($this->company){
                    $query->where('company_id',$this->company);
                }
            })
            ->get()
        ]);
    }
}
