<?php

namespace App\Exports;
use App\Models\MarketingOrderDelivery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageOrderDelivery implements FromView,ShouldAutoSize
{
    protected $search, $status,$account_id,$company,$marketing_order,$end_date,$start_date;
    public function __construct(string $search,string $status ,string $account_id,string $company,string $marketing_order,string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';

        $this->status   = $status ? $status : '';
        $this->account_id   = $account_id ? $account_id : '';
        $this->company   = $company ? $company : '';
        $this->marketing_order   = $marketing_order ? $marketing_order : '';
        $this->end_date   = $end_date ? $end_date : '';
        $this->start_date   = $start_date ? $start_date : '';
    }

    public function view(): View
    {
        return view('admin.exports.marketing_order_delivery', [
            'data' => MarketingOrderDelivery::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note_internal', 'like', "%$this->search%")
                            ->orWhere('note_external', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('account',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('marketingOrderDeliveryDetail',function($query){
                                $query->whereHas('item',function($query){
                                    $query->where('code','like',"%$this->search%")
                                        ->orWhere('name','like',"%$this->search%");
                                });
                            });
                    });
                }
               
                if($this->status){
                    $array = explode(',', $this->status);
                    $query->whereIn('status',$array);
                }

                if($this->start_date && $this->end_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $query->whereDate('post_date','<=', $this->end_date);
                }

                if($this->account_id){
                    $array = explode(',', $this->account_id);
                    $query->whereIn('account_id',$array);
                }

                if($this->marketing_order){
                    $array = explode(',', $this->marketing_order);
                    $query->whereIn('marketing_order_id',$array);
                }
                
                if($this->company){
                    $query->where('company_id',$this->company);
                }
            })
            ->get()
        ]);
    }
}
