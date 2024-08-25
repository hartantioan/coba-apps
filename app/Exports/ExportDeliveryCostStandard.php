<?php

namespace App\Exports;
use App\Models\DeliveryCostStandard;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDeliveryCostStandard implements FromView,ShouldAutoSize
{
    protected $search, $status , $start_date,$end_date;
    public function __construct(string $search ,string $status,string $start_date ,string $end_date)
    {
        $this->search = $search ? $search : '';

        $this->status   = $status ? $status : '';
        $this->start_date = $start_date ? $start_date : '';

        $this->end_date   = $end_date ? $end_date : '';
    }

    public function view(): View
    {
        return view('admin.exports.delivery_cost_standard', [
            'data' => DeliveryCostStandard::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('price', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('city',function($query) {
                                $query->where('name','like',"%$this->search%");
                            })->orWhereHas('district',function($query) {
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
