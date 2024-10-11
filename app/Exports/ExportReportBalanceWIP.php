<?php

namespace App\Exports;

use App\Models\GoodIssue;
use App\Models\ProductionIssue;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportBalanceWIP implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Tanggal',
        'No Issue',
        'Qty Terpakai',
        'Qty Diterima',
        'Qty Sisa',
        'Ref Production Receive',

    ];
    public function collection()
    {
        $data = ProductionIssue::where(function($query) {
            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }
        })->get();
        $arr = [];
        $x=0;
        foreach($data as $key => $row){
            $x++;

            $sisa = $row->qty() - $row->qtyReceive();
            $arr[] = [
                'no' => $x,
                'tanggal'=> $row->post_date,
                'no_issue'=> $row->code,
                'total'=> $row->qty(),
                'diterima'=> $row->qtyReceive(),
                'sisa'=> $sisa,
                'list'=>$row->listReceive(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Report WIP';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
