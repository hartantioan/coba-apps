<?php

namespace App\Exports;

use App\Models\FundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportEmployeeReceivable implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }
    public function view(): View
    {
        $totalbalance=0;
        
        $data = FundRequest::where('type','1')->whereIn('status',['2','3'])->where('document_status','3')->whereDate('post_date','<=',$this->date)->get();

        $results = [];

        $totalbalance = 0;

        foreach($data as $row){
            $totalReceivable = $row->totalReceivableByDate($this->date);
            $totalReceivableUsed = $row->totalReceivableUsedPaidByDate($this->date);
            $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
            if($totalReceivableBalance > 0){
                $results[] = [
                    'code'          => $row->code,
                    'employee_name' => $row->account->name,
                    'plant'         => $row->place->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'required_date' => date('d/m/y',strtotime($row->required_date)),
                    'note'          => $row->note,
                    'total'         => $row->total,
                    'tax'           => $row->tax,
                    'wtax'          => $row->wtax,
                    'grandtotal'    => $row->grandtotal,
                    'received'      => $totalReceivable,
                    'used'          => $totalReceivableUsed,
                    'balance'       => $totalReceivableBalance,
                ];
                $totalbalance += $totalReceivableBalance;
            }
        }

        return view('admin.exports.employee_receivable', [
            'data'      => $results,
            'totalall'  => $totalbalance
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
