<?php

namespace App\Exports;

use App\Models\OutgoingPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportOutgoingPayment implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $company = null, string $account = null, string $currency = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.outgoing_payment', [
            'data' => OutgoingPayment::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('grandtotal', 'like', "%$this->search%")
                            ->orWhere('admin', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('account',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }

                if($this->account){
                    $arrAccount = explode(',',$this->account);
                    $query->whereIn('account_id',$arrAccount);
                }

                if($this->currency){
                    $arrCurrency = explode(',',$this->currency);
                    $query->whereIn('currency_id',$arrCurrency);
                }

                if($this->company){
                    $query->where('company_id',$this->company);
                }
            })
            ->get()
        ]);
    }
}
