<?php

namespace App\Exports;

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
    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }
    public function view(): View
    {
        $totalAll=0;
        $request='kas';
        $array_filter = [];
        $query_data = PurchaseInvoice::where(function($query) use ( $request) {
                if($this->date) {
                    $query->whereDate('post_date', '<=', $this->date);
                }
            })
            ->get();
            foreach($query_data as $row_invoice){
                $data_tempura = [
                    'code' => $row_invoice->code,
                    'vendor' => $row_invoice->account->name,
                    'post_date'=>date('d M Y',strtotime($row_invoice->post_date)),
                    'rec_date'=>date('d M Y',strtotime($row_invoice->received_date)),
                    'due_date'=>date('d M Y',strtotime($row_invoice->due_date)),
                    'grandtotal'=>number_format($row_invoice->balance,2,',','.'),
                    'payed'=>$row_invoice->totalMemoByDate($this->date),
                    'sisa'=>$row_invoice->getTotalPaidByDate($this->date)
                ];
                $detail=[];
                $totalAll+=$row_invoice->balance;
                foreach($row_invoice->purchaseInvoiceDetail as $row){
                    
                    if($row->purchaseOrderDetail()){
                        $detail[] = [
                            'po'=> $row->lookable->purchaseOrder->code,
                            'top'=>$row->lookable->purchaseOrder->payment_term,
                            'item_name'=>$row->lookable->item_id ? $row->lookable->item->name : $row->lookable->coa->code,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>$row->qty,
                            'unit'=>$row->lookable->item_id ? $row->lookable->item->uomUnit->code : '-',
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                        ];
                    }
                    elseif($row->landedCostDetail()){
                        $detail[] = [
                            'po'=> $row->lookable->landedCost->code,
                            'top'=>'',
                            'item_name'=>$row->lookable->item->name,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>$row->qty,
                            'unit'=>$row->lookable->item->uomUnit->code,
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                           
                        ];
                    }
                    elseif($row->goodReceiptDetail()){
                        $detail[] = [
                            'po'=> $row->lookable->goodReceipt->code,
                            'top'=>'',
                            'item_name'=>$row->lookable->item->name,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>$row->qty,
                            'unit'=>$row->lookable->item->uomUnit->code,
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                           
                        ];
                    }
                    elseif($row->coa()){
                        $detail[] = [
                            'po'=> '-',
                            'top'=>'',
                            'item_name'=>$row->lookable->code.' '.$row->lookable->name,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>$row->qty,
                            'unit'=>'-',
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                        
                        ];
                    }

                   
                }
                if($data_tempura['sisa'] != number_format(0,2,',','.')){
                    $data_tempura['details']=$detail;
                    $array_filter[]=$data_tempura;
                }
                
            }  
        return view('admin.exports.outstanding', [
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
