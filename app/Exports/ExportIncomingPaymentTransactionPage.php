<?php

namespace App\Exports;


use App\Models\IncomingPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportIncomingPaymentTransactionPage implements FromView,ShouldAutoSize
{
    protected $status, $company,$account, $currency, $end_date, $start_date , $search , $modedata;

    public function __construct(string $search,string $status, string $company,string $account, string $currency, string $end_date, string $start_date,  string $modedata )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->modedata = $modedata ? $modedata : '';
        
    }
    public function view(): View
    {
       
        return view('admin.exports.incoming_payment', [
            'data' => IncomingPayment::where(function($query){
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('due_date', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }
                if($this->status){
                    $groupIds = explode(',', $this->status);
                    $query->whereIn('status', $groupIds);
                }
        
                if($this->start_date && $this->end_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $query->whereDate('post_date','<=', $this->end_date);
                }
        
                if($this->account){
                    $groupIds = explode(',', $this->account);
                    $query->whereIn('account_id',$groupIds);
                }
                
                if($this->company){
                    $query->where('company_id',$this->company);
                }
                     
                
                if($this->currency){
                    $groupIds = explode(',', $this->currency);
                    $query->whereIn('currency_id',$groupIds);
                }
        
                if(!$this->modedata){
                    $query->where('user_id',session('bo_id'));
                }
            })
            ->get()
        ]);
        
    }
}
