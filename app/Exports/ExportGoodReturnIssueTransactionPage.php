<?php

namespace App\Exports;

use App\Models\GoodReturnIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReturnIssueTransactionPage implements FromView,ShouldAutoSize
{
    protected $search,$start_date, $end_date, $status, $modedata;
    public function __construct(string $search ,string $start_date, string $end_date,string $status, string $modedata)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? $status : '';
        $this->modedata = $modedata ? $modedata : '';
    }
    public function view(): View
    {
        return view('admin.exports.good_return_issue', [
            'data' => GoodReturnIssue::where(function ($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('delivery_no', 'like', "%$this->search%")
                            ->orWhere('vehicle_no', 'like', "%$this->search%")
                            ->orWhere('driver', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query){
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
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
                    $query->whereIn('status', $this->status);
                }
            })
            ->get()
        ]);
    }
}
