<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportPurchaseInvoice implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $type = null, string $place = null, string $department = null, string $account = null, string $currency = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->place = $place ? $place : '';
        $this->department = $department ? $department : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.purchase_invoice', [
            'data' => PurchaseInvoice::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('total', 'like', "%$this->search%")
                            ->orWhere('tax', 'like', "%$this->search%")
                            ->orWhere('grandtotal', 'like', "%$this->search%")
                            ->orWhere('downpayment', 'like', "%$this->search%")
                            ->orWhere('balance', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('account',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query){
                                $query->whereHas('landedCost',function($query){
                                    $query->where('code','like',"%$this->search%");
                                })->orWhereHas('goodReceiptMain',function($query){
                                    $query->where('code','like',"%$this->search%");
                                });
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }

                if($this->type){
                    $query->where('type',$this->type);
                }

                if($this->account){
                    $arrAccount = explode(',',$this->account);
                    $query->whereIn('account_id',$arrAccount);
                }

                if($this->currency){
                    $arrCurrency = explode(',',$this->currency);
                    $query->whereIn('currency_id',$arrCurrency);
                }

                if($this->place){
                    $query->where('place_id',$this->place);
                }

                if($this->department){
                    $query->where('department_id',$this->department);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
        ]);
    }
}
