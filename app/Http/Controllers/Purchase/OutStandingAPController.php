<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingAP;
use App\Http\Controllers\Controller;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OutStandingAPController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding AP',
            'content'   => 'admin.purchase.outstanding_ap',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filterByDate(Request $request){
        $array_filter = [];
        $query_data = PurchaseInvoice::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        $query_data2 = PurchaseDownPayment::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        $totalAll = 0;
        if($query_data || $query_data2){
            foreach($query_data as $row_invoice){
                $data_tempura = [
                    'code' => $row_invoice->code,
                    'vendor' => $row_invoice->account->name,
                    'post_date'=>date('d/m/Y',strtotime($row_invoice->post_date)),
                    'rec_date'=>date('d/m/Y',strtotime($row_invoice->received_date)),
                    'due_date'=>date('d/m/Y',strtotime($row_invoice->due_date)),
                    'grandtotal'=>number_format($row_invoice->balance,2,',','.'),
                    'payed'=>number_format($row_invoice->totalMemoByDate($request->date),2,',','.'),
                    'sisa'=>number_format($row_invoice->getTotalPaidByDate($request->date),2,',','.'),
                ];

                $detail=[];
                
                foreach($row_invoice->purchaseInvoiceDetail as $row){
                    if($row->purchaseOrderDetail()){
                        $detail[] = [
                            'po'=> $row->lookable->purchaseOrder->code,
                            'top'=>$row->lookable->purchaseOrder->payment_term,
                            'item_name'=>$row->lookable->item_id ? $row->lookable->item->name : $row->lookable->coa->code,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>number_format($row->qty,3,',','.'),
                            'unit'=>$row->lookable->item_id ? $row->lookable->item->uomUnit->code : '-',
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                        ];
                    }
                    elseif($row->landedCostFeeDetail()){
                        $detail[] = [
                            'po'=> $row->lookable->landedCost->code,
                            'top'=>'',
                            'item_name'=>$row->lookable->landedCostFee->name,
                            'note1'=>$row->note,
                            'note2'=>$row->note2,
                            'qty'=>number_format($row->qty,3,',','.'),
                            'unit'=>'-',
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
                            'qty'=>number_format($row->qty,3,',','.'),
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
                            'qty'=>number_format($row->qty,3,',','.'),
                            'unit'=>'-',
                            'price_o'=>number_format($row->price,2,',','.'),
                            'total' =>number_format($row->total,2,',','.'),
                            'ppn'=>number_format($row->tax,2,',','.'),
                            'pph'=>number_format($row->wtax,2,',','.'),
                        
                        ];
                    }
                }

                if($data_tempura['sisa'] != number_format(0,2,',','.')){
                    $totalAll += str_replace(',','.',str_replace('.','',$data_tempura['sisa']));
                    $data_tempura['details'] = $detail;
                    $array_filter[] = $data_tempura;
                }
                
            }

            foreach($query_data2 as $row_dp){
                $total = $row_dp->balancePaymentRequestByDate($request->date);
                $due_date = $row_dp->due_date ? $row_dp->due_date : date('Y-m-d', strtotime($row_dp->post_date. ' + '.$row_dp->top.' day'));
                $data_tempura = [
                    'code' => $row_dp->code,
                    'vendor' => $row_dp->supplier->name,
                    'post_date'=>date('d/m/Y',strtotime($row_dp->post_date)),
                    'rec_date'=>'',
                    'due_date'=>date('d/m/Y',strtotime($due_date)),
                    'grandtotal'=>number_format($row_dp->grandtotal,2,',','.'),
                    'payed'=>number_format($row_dp->totalMemoByDate($request->date),2,',','.'),
                    'sisa'=>number_format($total,2,',','.'),
                ];
                
                $detail=[];
                $detail[] = [
                    'po'=> $row_dp->code,
                    'top'=>0,
                    'item_name'=>'-',
                    'note1'=>$row_dp->note,
                    'note2'=>'-',
                    'qty'=>number_format(1,2,',','.'),
                    'unit'=>'-',
                    'price_o'=>number_format(0,2,',','.'),
                    'total' =>number_format($row_dp->nominal,2,',','.'),
                    'ppn'=>number_format(0,2,',','.'),
                    'pph'=>number_format(0,2,',','.'),
                ];

                if($total > 0){
                    $totalAll += $total;
                    $data_tempura['details'] = $detail;
                    $array_filter[] = $data_tempura;
                }
            }

            $response =[
                'status'=>200,
                'message'  =>$array_filter,
                'totalall' =>number_format($totalAll,2,',','.')
            ];
        }else{
            $response =[
                'status'  =>500,
                'message' =>'Data error'
            ];
        }
        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportOutstandingAP($request->date), 'outstanding_ap_'.uniqid().'.xlsx');
    }
}
