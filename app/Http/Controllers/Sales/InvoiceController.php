<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportInvoiceStore;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title'         => 'List Invoice',
            'content'       => 'admin.sales.store_invoice',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'store_customer_id',
            'post_date',
            'grandtotal',
            'discount',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column', 'tonnage')];
        $dir    = $request->input('order.0.dir', 'asc');
        $search = $request->input('search.value');

        $total_data = Invoice::count();
        $query_data = Invoice::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('name','like',"%$search%")
                    ->orWhere('code','like',"%$search%")
                    ->orWhereHas('storeCustomer',function($query) use ($search) {
                        $query->where('no_telp', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }

            if($request->store_customer_id){
                $query->where('store_customer_id', $request->store_customer_id);
            }


            if($request->start_date && $request->finish_date) {
                $query->where(function($query) use ($request) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_from', '<=', $request->finish_date);
                })->orWhere(function($query) use ($request) {
                    $query->whereDate('valid_to', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                });
            } else if($request->start_date) {
                $query->whereDate('valid_from','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('valid_to','<=', $request->finish_date);
            }
        })
        ->offset($start)
        ->limit($length)
         ->orderBy($order, $dir)
        ->get();
        $total_filtered = Invoice::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('name','like',"%$search%")
                    ->orWhere('code','like',"%$search%")
                    ->orWhereHas('storeCustomer',function($query) use ($search) {
                        $query->where('no_telp', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }

            if($request->store_customer_id){
                $query->where('store_customer_id', $request->store_customer_id);
            }


            if($request->start_date && $request->finish_date) {
                $query->where(function($query) use ($request) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_from', '<=', $request->finish_date);
                })->orWhere(function($query) use ($request) {
                    $query->whereDate('valid_to', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                });
            } else if($request->start_date) {
                $query->whereDate('valid_from','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('valid_to','<=', $request->finish_date);
            }
        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->user->name,
                    $val->storeCustomer?->name ?? '',
                    $val->post_date,
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="whatPrinting(\'' . CustomHelper::encrypt($val->code) . '\')"><i class="material-icons dp48">local_printshop</i></button>

                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(\'' . CustomHelper::encrypt($val->code) . '\')"><i class="material-icons dp48">delete</i></button>
                    '
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

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        if($request->store_customer_id == "null"){
            $store_customer_id = '';
        }else{
		    $store_customer_id = $request->store_customer_id ? $request->store_customer_id : '';
        }
		return Excel::download(new ExportInvoiceStore($search,$post_date,$end_date,$store_customer_id), 'invoice'.uniqid().'.xlsx');
    }

    public function printIndividual(Request $request,$id){

        $pr = Invoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($pr){
            $pdf = PrintHelper::print($pr,'Invoice',[0, 0, 80, 200] ,'portrait','admin.print.sales.invoice_store_individual','all');

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);

            return $document_po;
        }else{
            abort(404);
        }
    }
}
