<?php

namespace App\Exports;

use App\Models\CloseBill;
use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportCloseBillTransactionPage implements FromView
{
    protected $search,$company,$start_date, $end_date, $status, $modedata;
    public function __construct(string $search,string $company,string $start_date, string $end_date,string $status, string $modedata)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? $status : '';
        $this->modedata = $modedata ? $modedata : '';
        $this->company = $company ? $company : '';
    }
    public function view(): View
    {
        
        return view('admin.exports.close_bill', [
            'data' => CloseBill::where(function($query) {
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
    
                if($this->company){
                    $query->where('company_id', $this->company);
                }
    
                if($this->start_date && $this->end_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $query->whereDate('post_date','<=', $this->end_date);
                }
    
                if($this->status){
                    $groupIds = explode(',', $this->status);
                    $query->whereIn('status', $groupIds);
                }
    
                if(!$this->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            ->get()
        ]);
        
    }
}
