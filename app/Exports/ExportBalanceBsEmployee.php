<?php

namespace App\Exports;

use App\Models\FundRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportBalanceBsEmployee implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function view(): View
    {
        $data = User::where('type','1')->where('status','1')->orderBy('employee_no')->get();

        $results = [];

        foreach($data as $row){
            $results[] = [
                'code'          => $row->employee_no,
                'name'          => $row->name,
                'limit'         => round($row->limit_credit,2),
                'usage'         => round($row->count_limit_credit,2),
                'balance'       => round($row->limit_credit - $row->count_limit_credit,2),
            ];
        }

        return view('admin.exports.balance_bs_employee', [
            'data'      => $results,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
    }
}
