<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportOutstandingAP implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $date;

    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }
    public function view(): View
    {
        $totalAll=0;
        $array_filter = [];
        $query_data = PurchaseInvoice::where(function($query) {
            if($this->date) {
                $query->whereDate('post_date', '<=', $this->date);
            }
        })
        ->whereIn('status',['2','3'])
        ->get();
        $query_data2 = PurchaseDownPayment::where(function($query){
            if($this->date) {
                $query->whereDate('post_date', '<=', $this->date);
            }
        })
        ->whereIn('status',['2','3'])
        ->get();

        foreach($query_data as $row_invoice){
            $data_tempura = [
                'code'      => $row_invoice->code,
                'vendor'    => $row_invoice->account->name,
                'post_date' =>date('d/m/Y',strtotime($row_invoice->post_date)),
                'rec_date'  =>date('d/m/Y',strtotime($row_invoice->received_date)),
                'due_date'  =>date('d/m/Y',strtotime($row_invoice->due_date)),
                'top'       => $row_invoice->getTop(),
                'total'     =>number_format($row_invoice->total,2,',','.'),
                'tax'       =>number_format($row_invoice->tax,2,',','.'),
                'wtax'      =>number_format($row_invoice->wtax,2,',','.'),
                'grandtotal'=>number_format($row_invoice->balance,2,',','.'),
                'payed'     =>number_format($row_invoice->getTotalPaidDate($this->date),2,',','.'),
                'sisa'      =>number_format($row_invoice->getTotalPaidByDate($this->date),2,',','.'),
            ];

            if($data_tempura['sisa'] != number_format(0,2,',','.')){
                $totalAll += str_replace(',','.',str_replace('.','',$data_tempura['sisa']));
                $array_filter[] = $data_tempura;
            }
            
        }

        foreach($query_data2 as $row_dp){
            $total = $row_dp->balancePaymentRequestByDate($this->date);
            $due_date = $row_dp->due_date ? $row_dp->due_date : date('Y-m-d', strtotime($row_dp->post_date. ' + '.$row_dp->top.' day'));
            $data_tempura = [
                'code'      => $row_dp->code,
                'vendor'    => $row_dp->supplier->name,
                'post_date' =>date('d/m/Y',strtotime($row_dp->post_date)),
                'rec_date'  =>'',
                'due_date'  =>date('d/m/Y',strtotime($due_date)),
                'top'       => 0,
                'total'     =>number_format($row_invoice->total,2,',','.'),
                'tax'       =>number_format($row_invoice->tax,2,',','.'),
                'wtax'      =>number_format($row_invoice->wtax,2,',','.'),
                'grandtotal'=>number_format($row_dp->grandtotal,2,',','.'),
                'payed'     =>number_format($row_dp->totalMemoByDate($this->date),2,',','.'),
                'sisa'      =>number_format($total,2,',','.'),
            ];

            if($total > 0){
                $totalAll += $total;
                $array_filter[] = $data_tempura;
            }
        }

        return view('admin.exports.outstanding_ap', [
            'data' => $array_filter,
            'totalall' =>number_format($totalAll,2,',','.')
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
