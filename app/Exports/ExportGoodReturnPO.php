<?php

namespace App\Exports;

use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReturnPO implements FromView,ShouldAutoSize
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
            $data = GoodReturnPO::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new GoodReturnPO())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Good return PO  data.');
            return view('admin.exports.good_return_po', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data = GoodReturnPO::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new GoodReturnPO())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Good return PO  data.');
            return view('admin.exports.good_return_po', [
                'data' => $data
            ]);
        }
    }
}
