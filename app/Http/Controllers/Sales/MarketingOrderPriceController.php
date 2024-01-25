<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingPrice;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderPriceController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Histori Harga Item Pada Sales Order',
            'content'   => 'admin.sales.price_history',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function datatable(Request $request){
        $column = [
            'marketing_order_id',
            'item_id',
            'account_id',
            'code',
            'post_date',
            'price',
            'margin',
            'percent_discount_1',
            'percent_discount_2',
            'discount_3',
            'price_after_discount',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDetail::whereHas('marketingOrder',function($query){
            $query->whereIn('status',['2','3']);
        })->count();
        
        $query_data = MarketingOrderDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('marketingOrder',function($query) use($search){
                        $query->where('code', 'like', "%$search%");
                    })->orWhereHas('item',function($query)use($search){
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }
                if($request->item){
                    $query->where('item_id',$request->item);
                }
            })
            ->whereHas('marketingOrder',function($query){
                $query->whereIn('status',['2','3']);
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderDetail::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('marketingOrder',function($query) use($search){
                        $query->where('code', 'like', "%$search%");
                    })->orWhereHas('item',function($query)use($search){
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }
                if($request->item){
                    $query->where('item_id',$request->item);
                }
            })
            ->whereHas('marketingOrder',function($query){
                $query->whereIn('status',['2','3']);
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $finalpricedisc1 = ($val->price - $val->margin) * ($val->percent_discount_1 / 100);
                $finalpricedisc2 = (($val->price - $val->margin) - $finalpricedisc1) * ($val->percent_discount_2 / 100);

                $response['data'][] = [
                    $nomor,
                    $val->item->code.' - '.$val->item->name,
                    $val->marketingOrder->account->name,
                    $val->marketingOrder->code,
                    date('d/m/Y',strtotime($val->marketingOrder->post_date)),
                    $val->place->code,
                    number_format($val->price,2,',','.'),
                    number_format($val->margin,2,',','.'),
                    number_format($finalpricedisc1,2,',','.'),
                    number_format($finalpricedisc2,2,',','.'),
                    number_format($val->discount_3,2,',','.'),
                    number_format($val->price_after_discount,2,',','.'),
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
        $search = $request->input('search.value')?$request->input('search.value'):'';
		return Excel::download(new ExportMarketingPrice($search,$item), 'history_price_item_so'.uniqid().'.xlsx');
    }
}
