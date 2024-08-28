<?php

namespace App\Exports;

use App\Models\ProductionWorkingHour;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionWorkingHourTransactionPage implements FromView, ShouldAutoSize
{
    protected $status, $finish_date, $start_date , $search;
    public function __construct(string $search,string $status, string $finish_date, string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->finish_date = $finish_date ? $finish_date : '';
        $this->status = $status ? $status : '';
    
        
    }

    public function view(): View
    {
        $data = ProductionWorkingHour::where(function ($query) {
            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('note','like',"%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })->orWhereHas('area',function($query) {
                            $query->where('name','like',"%$this->search%")
                            ->orWhere('code','like',"%$this->search%");
                        })->orWhereHas('shift',function($query) {
                            $query->where('name','like',"%$this->search%")
                            ->orWhere('code','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->whereIn('status', $this->status);
            }

            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }
            
        })->get();

        activity()
                ->performedOn(new ProductionWorkingHour())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Item plan data.');
        return view('admin.exports.production_working_hour', [
            'data' => $data
        ]);
    }
}
