<?php

namespace App\Exports;

use App\Models\CustomerDiscount;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportCustomerDiscount implements FromView,ShouldAutoSize
{
    protected $search, $status;
    public function __construct(string $search ,string $status)
    {
        $this->search = $search ? $search : '';

        $this->status   = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.customer_discount', [
            'data' => CustomerDiscount::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('group',function($query) {
                                $query->where('name','like',"%$this->search%")
                                ->orWhere('code','like',"%$this->search%");
                            })->orWhereHas('account',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('brand',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('city',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('type',function($query) {
                                $query->where('name','like',"%$this->search%");
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
