<?php

namespace App\Exports;

use App\Models\StandardCustomerPrice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageStandarCustomerPrice implements FromView,ShouldAutoSize
{
    protected $search, $status;
    public function __construct(string $search ,string $status)
    {
        $this->search = $search ? $search : '';

        $this->status   = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.standard_customer_price', [
            'data' => StandardCustomerPrice::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%")
                            ->orWhere('price', 'like', "%$this->search%")
                            ->orWhere('start_date', 'like', "%$this->search%")
                            ->orWhere('end_date', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('group',function($query){
                                $query->where('name','like',"%$this->search%")
                                ->orWhere('code','like',"%$this->search%");
                            });
                    });
                }
               
                if($this->status){
                    $query->where('status', $this->status);
                }
            })
            ->get()
        ]);
    }
}
