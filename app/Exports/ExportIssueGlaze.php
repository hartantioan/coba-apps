<?php

namespace App\Exports;

use App\Models\IssueGlaze;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ExportIssueGlaze extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromView, ShouldAutoSize, WithCustomValueBinder
{
    protected $start_date, $end_date, $mode, $nominal, $line_id;
    public function __construct(string $start_date, string $end_date, string $mode, string $nominal,string $line_id)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->nominal = $nominal ?? '';
        $this->line_id = $line_id ?? '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data = IssueGlaze::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<='   , $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new IssueGlaze())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export issue glaze.');
            return view('admin.exports.issue_glaze', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }elseif($this->mode == '2'){
            $data = IssueGlaze::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                if($this->line_id){
                    $query->where('line_id',$this->line_id);
                }
            })
            ->get();
            activity()
                ->performedOn(new IssueGlaze())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export issue glaze.');
            return view('admin.exports.issue_glaze', [
                'data'      => $data,
                'nominal'   => $this->nominal,
            ]);
        }
    }
}
