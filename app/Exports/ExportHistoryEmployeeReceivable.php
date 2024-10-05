<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\FundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportHistoryEmployeeReceivable implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $account_id;

    public function __construct(string $start_date, string $end_date, string $account_id)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->account_id = $account_id ? $account_id : '';
    }
    public function view(): View
    {

        $data = FundRequest::where('type','1')->whereIn('status',['2','3'])->where('document_status','3')->whereHas('hasPaymentRequestDetail',function($query){
            $query->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            });
        })
        ->whereDate('post_date','<=',$this->end_date)
        ->whereDate('post_date','>=',$this->start_date)
        ->where(function($query){
            if($this->account_id){
                $arr = explode(',',$this->account_id);
                $query->whereIn('account_id',$arr);
            }
        })
        ->get();

        $results = [];

        foreach($data as $row){
            $detail = [];

            foreach($row->personalCloseBillDetail as $rowcb){
                $detail[] = [
                    'no'            => $rowcb->personalCloseBill->code,
                    'post_date'     => date('d/m/Y',strtotime($rowcb->personalCloseBill->post_date)),
                    'status'        => $rowcb->personalCloseBill->statusRaw(),
                    'nominal'       => $rowcb->nominal,
                ];
            }

            foreach($row->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment',function($query){
                    $query->whereHas('paymentRequestCross');
                });
            })->get() as $rowpay){
                foreach($rowpay->paymentRequest->outgoingPayment->paymentRequestCross as $rowcross){
                    $detail[] = [
                        'no'            => $rowcross->paymentRequest->code,
                        'post_date'     => date('d/m/Y',strtotime($rowcross->paymentRequest->post_date)),
                        'status'        => $rowcross->paymentRequest->statusRaw(),
                        'nominal'       => $rowcross->nominal,
                    ];
                }
            }

            $results[] = [
                'code'          => $row->code,
                'employee_name' => $row->account->name,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'required_date' => date('d/m/Y',strtotime($row->required_date)),
                'note'          => $row->note,
                'grandtotal'    => $row->grandtotal,
                'details'       => $detail,
            ];
        }

        return view('admin.exports.history_employee_receivable', [
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
