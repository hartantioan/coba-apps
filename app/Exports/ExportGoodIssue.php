<?php

namespace App\Exports;

use App\Models\GoodIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportGoodIssue implements FromView
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
            return view('admin.exports.good_issue', [
                'data' => GoodIssue::where(function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                })
                ->get()
            ]);
        }elseif($this->mode == '2'){
            return view('admin.exports.good_issue', [
                'data' => GoodIssue::withTrashed()->where(function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                })
                ->get()
            ]);
        }
    }
}
