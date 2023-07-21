<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportPriceHistoryPO;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PriceHistoryPOController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Histori Harga Item Pada Purchase Order',
            'content'   => 'admin.purchase.price_history_po',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function datatable(Request $request){
        $column = [
            'purchase_order_id',
            'purchase_request_detail_id',
            'good_issue_detail_id',
            'item_id',
            'post_date',
            'price',
            'percent_discount_1',
            'percent_discount_2',
            'discount_3',    
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseOrderDetail::count();
        
        $query_data = PurchaseOrderDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('purchaseOrder',function($query) use($search){
                        $query->where('code', 'like', "%$search%");
                    });
                }
                if($request->item){
                    $query->where('item_id',$request->item);
                }
                if($request->inventory_type !== 'all'){
                    $query->whereHas('purchaseOrder',function($query) use($request){
                        $query->where('inventory_type', $request->inventory_type);
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseOrderDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('purchaseOrder',function($query) use($request,$search){
                        $query->where('code', 'like', "%$search%");
                    });
                }
                if($request->item){
                    $query->where('item_id',$request->item);
                }
                if($request->inventory_type !== 'all'){
                    $query->whereHas('purchaseOrder',function($query) use($request){
                        $query->where('inventory_type', $request->inventory_type);
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $finalpricedisc1 = $val->price * ($val->percent_discount_1 / 100);
                $finalpricedisc2 = ($val->price - $finalpricedisc1) * ($val->percent_discount_2 / 100);
                $total_final = $val->price - $finalpricedisc1 - $finalpricedisc2 - $val->discount_3;
                $isi='';
                if($val->item()->exists()){
                    $isi = $val->item->code.' - '.$val->item->name;
                }else{
                    $isi = $val->coa->code.' - '.$val->coa->name;
                }

                $response['data'][] = [
                    $nomor,
                    $val->purchaseOrder->supplier->name,
                    $val->purchaseOrder->code,
                    $isi,
                    date('d/m/y',strtotime($val->purchaseOrder->post_date)),
                    number_format($val->price,2,',','.'),
                    number_format($finalpricedisc1,2,',','.'),
                    number_format($finalpricedisc2,2,',','.'),
                    number_format($val->discount_3,2,',','.'),
                    number_format($total_final,2,',','.'),
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function export(Request $request){
		$item = $request->item ? $request->item:'';
        $inventory_type = $request->inventory_type?$request->inventory_type:'';
        $search = $request->input('search.value')?$request->input('search.value'):'';
		return Excel::download(new ExportPriceHistoryPO($search,$item,$inventory_type), 'history_price_item_po'.uniqid().'.xlsx');
    }
}
