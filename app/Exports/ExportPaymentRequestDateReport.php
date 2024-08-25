<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use App\Models\PaymentRequestDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPaymentRequestDateReport implements FromView,ShouldAutoSize
{
    protected $start_date, $end_date,$filter_payment_request;
    public function __construct(string $start_date, string $end_date,string $filter_payment_request)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->filter_payment_request = $filter_payment_request ? $filter_payment_request : '';

    }
    public function view(): View
    {
        $query_data = PaymentRequestDetail::whereHas('paymentRequest',function($query){
            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }
            if($this->filter_payment_request){
                $groupIds = explode(',', $this->filter_payment_request);
                $query->whereIn('id',$groupIds);
            }
        })->where('lookable_type','purchase_invoices')->get();

        if ($query_data->isEmpty()) {
            $query_data = [];
        }
        
        $array_filter = [];
       
        foreach($query_data as $row){
            
                $data_tempura = [
                    'code' => $row->paymentRequest->code,
                    'invoice_code' => $row->lookable->code,
                    'invoice_no' => $row->lookable->invoice_no,
                    'status' => $row->paymentRequest->statusRaw(),
                    'vendor' => $row->lookable->account->name,
                    'pay_date' => date('d/m/Y',strtotime($row->paymentRequest->pay_date)),
                ];
            
                $array_filter[]=$data_tempura;
            
            
        }
        activity()
            ->performedOn(new PaymentRequest())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock in qty data  .');
        return view('admin.exports.payment_request_date_report', [
            'data' => $array_filter,
        ]);
    }
}
