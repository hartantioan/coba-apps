<?php

namespace App\Exports;

use App\Models\GoodReturnIssue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReturnIssue implements FromView,ShouldAutoSize
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
            return view('admin.exports.good_return_issue', [
                'data' => GoodReturnIssue::where(function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                })
                ->get()
            ]);
        }elseif($this->mode == '2'){
            return view('admin.exports.good_return_issue', [
                'data' => GoodReturnIssue::withTrashed()->where(function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                })
                ->get()
            ]);
        }
    }
}
