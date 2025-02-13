<?php

namespace App\Exports;

use App\Models\SampleTestInput;
use App\Models\SampleTestInputPICNote;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportFromTransactionPageSampleTestInputPicNote implements FromView,ShouldAutoSize
{
    protected $start_date, $end_date,$status,$search;
    public function __construct(string $start_date, string $end_date, string $status, string $search)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
		$this->status = $status ? $status : '';
		$this->search = $search ? $search : '';
    }
    public function view(): View
    {
        $data = SampleTestInputPICNote::where(function($query){
            if($this->start_date && $this->end_date) {
                $query->whereDate('created_at', '>=', $this->start_date)
                    ->whereDate('created_at', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('created_at','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('created_at','<=', $this->end_date);
            }
            if($this->status){
                $array = explode(',', $this->status);
                $query->whereIn('status',$array);
            }

            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                    ->orWhereHas('sampleType',function($query) {
                        $query->where('name','like',"%$this->search%");
                    })->orWhereHas('city',function($query) {
                        $query->where('name','like',"%$this->search%");
                    })->orWhereHas('province',function($query) {
                        $query->where('name','like',"%$this->search%");
                    })->orWhereHas('subdistrict',function($query) {
                        $query->where('name','like',"%$this->search%");
                    })
                    ->orWhere('note','like',"%$this->search%")
                    ->orWhere('supplier','like',"%$this->search%")
                    ->orWhere('supplier_name','like',"%$this->search%");

                });
            }
        })
        ->get();
        activity()
                ->performedOn(new SampleTestInputPICNote())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Sample Test.');
            return view('admin.exports.sample_test_input_pic_note', [
                'data'      => $data,
            ]);
    }
}
