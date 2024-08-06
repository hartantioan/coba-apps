<?php

namespace App\Exports;

use App\Models\MarketingOrderPlan;
use Maatwebsite\Excel\Concerns\FromCollection;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportMarketingOrderPlan implements FromView, ShouldAutoSize
{
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
            $data = MarketingOrderPlan::where(function($query){
                    $query->where('post_date', '>=',$this->start_date)
                        ->where('post_date', '<=', $this->end_date);
                })
                ->get();
            activity()
                ->performedOn(new MarketingOrderPlan())
                ->causedBy(session('bo_id'))
                ->withProperties($data)
                ->log('Export marketing order plan data.');
            return view('admin.exports.marketing_order_plan', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data = MarketingOrderPlan::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new MarketingOrderPlan())
                ->causedBy(session('bo_id'))
                ->withProperties($data)
                ->log('Export marketing order plan data.');
            return view('admin.exports.marketing_order_plan', [
                'data' => $data
            ]);
        }
    }
}
