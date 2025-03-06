<?php

namespace App\Exports;

use App\Models\IssueGlaze;
use App\Models\MergeStock;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ExportMergeStock extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromView, ShouldAutoSize, WithCustomValueBinder
{
    protected $start_date, $end_date, $mode, $nominal, $line_id;
    public function __construct(string $start_date, string $end_date, string $mode, string $nominal)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->nominal = $nominal ?? '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data = MergeStock::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new MergeStock())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export merge stock fg.');
            return view('admin.exports.merge_stock', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }elseif($this->mode == '2'){
            $data = MergeStock::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new MergeStock())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export merge stock fg.');
            return view('admin.exports.merge_stock', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }
    }
}
