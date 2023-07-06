<?php

namespace App\Exports;

use App\Models\GoodReceive;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ExportGoodReceive implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }

    public function view(): View
    {
        return view('admin.exports.good_receive', [
            'data' => GoodReceive::where(function($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get()
        ]);
    }
}
