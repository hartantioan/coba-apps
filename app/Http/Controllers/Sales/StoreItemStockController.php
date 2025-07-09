<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportStoreItemStock;
use App\Http\Controllers\Controller;
use App\Models\StoreItemStock;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StoreItemStockController extends Controller
{
    public function index()
    {

        $data = [
            'title'     => 'Item Stock ',
            'content'   => 'admin.sales.store_item_stock',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'item_id',
            'item_stock_new_id',
            'qty',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = StoreItemStock::count();

        $query_data = StoreItemStock::where(function($query) use ($search, $request) {
                if ($search) {
                    $query->whereHas('item', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%");
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = StoreItemStock::where(function($query) use ($search, $request) {
                if ($search) {
                    $query->whereHas('item', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$$search%");
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    $nomor,
                    $val->item->name,
                    $val->itemStockNew?->item?->name??'-',
                    $val->qty,
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
        $search = $request->search ? $request->search : '';

		return Excel::download(new ExportStoreItemStock($search), 'store_item_stock_'.uniqid().'.xlsx');
    }
}
