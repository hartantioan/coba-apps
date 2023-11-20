<?php

namespace App\Http\Controllers\Sales;
use App\Exports\ExportMarketingRecapitulation;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrder;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderReportController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }
    
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Rekapitulasi',
            'content'       => 'admin.sales.recapitulation',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filterByDate(Request $request){
        $start_time = microtime(true);
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $mo = MarketingOrder::whereIn('status',['2','3'])
                ->whereDate('post_date', '>=', $start_date)
                ->whereDate('post_date', '<=', $end_date)->get();
        
        $newData = [];

        foreach($mo as $row){
            $totalInvoice = $row->totalInvoice();
            $totalMemo = $row->totalMemo();
            $totalPayment = $row->totalPayment();
            $balance = $totalInvoice - $totalMemo - $totalPayment;
            $newData[] = [
                'code'              => $row->code,
                'customer'          => $row->account->name,
                'post_date'         => date('d/m/y',strtotime($row->post_date)),
                'top'               => $row->top_customer,
                'note'              => $row->note_internal.' - '.$row->note_external,
                'subtotal'          => number_format($row->subtotal,2,',','.'),
                'discount'          => number_format($row->discount,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'total_after_tax'   => number_format($row->total_after_tax,2,',','.'),
                'rounding'          => number_format($row->rounding,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'schedule'          => number_format($row->totalMod(),2,',','.'),
                'sent'              => number_format($row->totalModProcess(),2,',','.'),
                'return'            => number_format($row->totalReturn(),2,',','.'),
                'invoice'           => number_format($totalInvoice,2,',','.'),
                'memo'              => number_format($totalMemo,2,',','.'),
                'payment'           => number_format($totalPayment,2,',','.'),
                'balance'           => number_format($balance,2,',','.'),
            ];            
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $newData,
            'execution_time'    => round($execution_time,5),
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportMarketingRecapitulation($request->date), 'sales_recapitulation_'.uniqid().'.xlsx');
    }
}