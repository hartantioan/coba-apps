<?php

namespace App\Exports;

use App\Models\SampleTestInput;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportFromTransactionPageSampleTestInput implements FromView,ShouldAutoSize
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
        $data = SampleTestInput::where(function($query){
            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }
            if($this->status){
                $array = explode(',', $this->status);
                $query->whereIn('status',$array);
            }

            if($this->search) {
                $query->where(function($query)  {
                    $query->orWhereHas('sampleTypeInput',function($query) {
                        $query->where('code','like',"%$this->search%")
                        ->orWhere('note','like',"%$this->search%")
                        ->orWhere('supplier','like',"%$this->search%")
                        ->orWhere('supplier_name','like',"%$this->search%")
                        ->orWhereHas('city',function($query) {
                            $query->where('name','like',"%$this->search%");
                        })->orWhereHas('province',function($query) {
                            $query->where('name','like',"%$this->search%");
                        })->orWhereHas('subdistrict',function($query) {
                            $query->where('name','like',"%$this->search%");
                        });
                    })
                    ->orWhere('note','like',"%$this->search%")
                    ->orWhere('supplier','like',"%$this->search%")
                    ->orWhere('supplier_name','like',"%$this->search%");

                });
            }
        })
        ->get();
        activity()
                ->performedOn(new SampleTestInput())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Sample Test.');
            return view('admin.exports.sample_test_input', [
                'data'      => $data,
            ]);
    }
}
