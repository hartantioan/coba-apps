<?php

namespace App\Exports;

use App\Models\CloseBill;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportCloseBill implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            return view('admin.exports.close_bill', [
                'data' => CloseBill::where(function($query) {
                    if($this->start_date && $this->end_date) {
                        $query->whereDate('post_date', '>=', $this->start_date)
                            ->whereDate('post_date', '<=', $this->end_date);
                    } else if($this->start_date) {
                        $query->whereDate('post_date','>=', $this->start_date);
                    } else if($this->end_date) {
                        $query->whereDate('post_date','<=', $this->end_date);
                    }
                })
                ->get()
            ]);
        }elseif($this->mode == '2'){
            return view('admin.exports.close_bill', [
                'data' => CloseBill::withTrashed()->where(function($query) {
                    if($this->start_date && $this->end_date) {
                        $query->whereDate('post_date', '>=', $this->start_date)
                            ->whereDate('post_date', '<=', $this->end_date);
                    } else if($this->start_date) {
                        $query->whereDate('post_date','>=', $this->start_date);
                    } else if($this->end_date) {
                        $query->whereDate('post_date','<=', $this->end_date);
                    }
                })
                ->get()
            ]);
        }
    }
}
