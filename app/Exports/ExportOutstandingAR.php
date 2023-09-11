<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderDownPayment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportOutstandingAR implements FromView , WithEvents
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
        $array_filter = [];
        $grandtotalAll = 0;

        $query_data = MarketingOrderInvoice::where(function($query){
            if($this->date) {
                $query->whereDate('post_date', '<=', $this->date);
            }
        })
        ->whereIn('status',['2','3'])
        ->get();
        $query_data2 = MarketingOrderDownPayment::where(function($query){
            if($this->date) {
                $query->whereDate('post_date', '<=', $this->date);
            }
        })
        ->whereIn('status',['2','3'])
        ->get();
        
        foreach($query_data as $row){
            if($row->balancePaymentIncoming() > 0){
                foreach($row->marketingOrderInvoiceDeliveryProcess as $rowdetail){
                    $price = $rowdetail->getPrice();
                    $rounding = $rowdetail->getRounding();
                    $grandtotal = $rowdetail->getGrandtotal();
                    $memo = $rowdetail->getMemo();
                    $payment = $rowdetail->getDownPayment() + $rowdetail->getPayment();
                    $balance = $grandtotal - $memo - $payment;
                    $array_filter[] = [
                        'code'              => $row->code,
                        'customer'          => $row->account->name,
                        'post_date'         => date('d/m/y',strtotime($row->post_date)),
                        'top'               => $row->account->top,
                        'item_name'         => $rowdetail->lookable->item->name,
                        'qty_order'         => number_format($rowdetail->lookable->marketingOrderDetail->qty,3,',','.'),
                        'qty'               => number_format($rowdetail->qty,3,',','.'),
                        'unit'              => $rowdetail->lookable->item->sellUnit->code,
                        'price'             => number_format($price,2,',','.'),
                        'total'             => number_format($rowdetail->total,2,',','.'),
                        'tax'               => number_format($rowdetail->tax,2,',','.'),
                        'total_after_tax'   => number_format($rowdetail->grandtotal,2,',','.'),
                        'rounding'          => number_format($rounding,2,',','.'),
                        'grandtotal'        => number_format($grandtotal,2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'payment'           => number_format($payment,2,',','.'),
                        'balance'           => number_format($balance,2,',','.'),
                        'note'              => $rowdetail->note,
                    ];
                }
                $grandtotalAll += $balance;
            }
        }

        foreach($query_data2 as $row){
            if($row->balancePaymentIncoming() > 0){
                $rounding = 0;
                $memo = $row->totalMemo();
                $payment = $row->totalPay();
                $balance = $row->grandtotal - $memo - $payment;
                $array_filter[] = [
                    'code'              => $row->code,
                    'customer'          => $row->account->name,
                    'post_date'         => date('d/m/y',strtotime($row->post_date)),
                    'top'               => $row->account->top,
                    'item_name'         => '-',
                    'qty_order'         => 1,
                    'qty'               => 1,
                    'unit'              => '-',
                    'price'             => number_format($row->total,2,',','.'),
                    'total'             => number_format($row->total,2,',','.'),
                    'tax'               => number_format($row->tax,2,',','.'),
                    'total_after_tax'   => number_format($row->grandtotal,2,',','.'),
                    'rounding'          => number_format(0,2,',','.'),
                    'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                    'memo'              => number_format($memo,2,',','.'),
                    'payment'           => number_format($payment,2,',','.'),
                    'balance'           => number_format($balance,2,',','.'),
                    'note'              => $row->note,
                ];
                $grandtotalAll += $balance;
            }
        }

        return view('admin.exports.outstanding_ar', [
            'data'          => $array_filter,
            'grandtotal'    => number_format($grandtotalAll,2,',','.'),
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
