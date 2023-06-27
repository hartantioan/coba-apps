<?php

namespace App\Exports;

use App\Models\IncomingPayment;
use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportIncomingPayment implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $company = null, string $account = null, string $currency = null, string $start_date = null, string $finish_date = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function view(): View
    {
        return view('admin.exports.incoming_payment', [
            'data' => IncomingPayment::where(function($query){
                if($this->search) {
                    $query->where(function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('total', 'like', "%$this->search%")
                            ->orWhere('wtax', 'like', "%$this->search%")
                            ->orWhere('grandtotal', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('account',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('incomingPaymentDetail',function($query){
                                $query->where('note','like',"%$this->search%");
                            });
                    });
                }
                if($this->start_date && $this->finish_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->this('post_date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('post_date','<=', $this->finish_date);
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
