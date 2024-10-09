<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportTransactionPageMarketingOrderDetail2;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Currency;
use App\Models\MarketingOrder;
use App\Models\Menu;
use App\Models\Place;
use App\Models\Tax;
use App\Models\Transportation;
use Maatwebsite\Excel\Facades\Excel;

class ReportSalesOrderController extends Controller
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
        $parentSegment = request()->segment(2);
        $data = [
            'title'     => 'Report Sales',
            'content'   => 'admin.sales.report_sales_order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'transportation'=> Transportation::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search = $request->search;
        $query_data = MarketingOrder::where(function($query) use($request){
            // Apply the search conditions within the 'purchaseOrder' relationship
            $query->where(function($query) use($request){
                $query->where('code', 'like', "%$request->search%")
                ->orWhere('document_no', 'like', "%$request->search%")
                ->orWhere('note_internal', 'like', "%$request->search%")
                ->orWhere('note_external', 'like', "%$request->search%")
                ->orWhere('discount', 'like', "%$request->search%")
                ->orWhere('total', 'like', "%$request->search%")
                ->orWhere('tax', 'like', "%$request->search%")
                ->orWhere('grandtotal', 'like', "%$request->search%")
                ->orWhere('phone', 'like', "%$request->search%")
                ->orWhereHas('user',function($query)use($request){
                    $query->where('name','like',"%$request->search%")
                        ->orWhere('employee_no','like',"%$request->search%");
                })
                ->orWhereHas('marketingOrderDetail',function($query)use($request) {
                    $query->whereHas('item',function($query)use($request) {
                        $query->where('code','like',"%$request->search%")
                            ->orWhere('name','like',"%$request->search%");
                    });
                });
            });

            // Other conditions for the 'purchaseOrder' relationship
            if($request->status){
                $groupIds = explode(',', $request->status);
                $query->whereIn('status', $groupIds);
            }

            if($request->start_date && $request->end_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->end_date);
            } else if($request->start_date) {
                $query->whereDate('post_date','>=', $request->start_date);
            } else if($request->end_date) {
                $query->whereDate('post_date','<=', $request->end_date);
            }

            if($request->type_sales){
                $query->where('type',$request->type_sales);
            }

            if($request->type_deliv){
                $query->where('shipping_type',$request->type_deliv);
            }

            if($request->customer){
                $groupIds = explode(',', $request->customer);
                $query->whereIn('account_id',$groupIds);
            }

            if($request->sales){
                $groupIds = explode(',', $request->sales);
                $query->whereIn('sales_id',$groupIds);
            }

            if($request->delivery){
                $groupIds = explode(',', $request->delivery);
                $query->whereIn('sender_id',$groupIds);
            }

            if($request->company){
                $query->where('company_id',$request->company);
            }

            if($request->type_pay){
                $query->where('payment_type',$request->type_pay);
            }

            if($request->currency){
                $groupIds = explode(',', $request->currency);
                $query->whereIn('currency_id',$groupIds);
            }


        })->get();

        $newData = [];

        foreach($query_data as $index_data =>$row){
            foreach($row->marketingOrderDeliveryDetail as $row_detail){
                $newData[] = [
                    'no'                => ($index_data+1),
                    'no_document'       => $row->code,
                    'status'          => $row->statusRaw(),
                    'voider'          => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'tgl_void'         => $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' ,
                    'ket_void'               => $row->voidUser()->exists() ? $row->void_note : '' ,
                    'deleter'              =>$row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'tgl_delete'             => $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '',
                    'ket_delete'               => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'        => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'tgl_done'          => $row->doneUser ? $row->done_date : '',
                    'ket_done'              => $row->doneUser ? $row->done_note : '' ,
                    'nik'            => $row->user->employee_no,
                    'user'           =>  $row->user->name,
                    'post_date'              => date('d/m/Y',strtotime($row->post_date)),
                    'status_kirim'          => $row->sendStatus(),
                    'tgl_kirim'         => date('d/m/Y',strtotime($row->delivery_date)),
                    'tipe_pengiriman'               => $row->deliveryType(),
                    'ekspedisi'              => $row->account->name ?? '-',
                    'pelanggan'             => $row->customer->name  ?? '-',
                    'kode_item'               =>  $row_detail->item->code,
                    'item'        =>    $row_detail->item->name,
                    'plant'          => $row_detail->place->code,
                    'qty_konversi'          => $row_detail->qty,
                    'satuan_konversi'         => $row_detail->marketingOrderDetail->itemUnit->unit->code,
                    'qty'               => round($row_detail->qty * $row_detail->marketingOrderDetail->qty_conversion,3),
                    'unit'              => $row_detail->item->uomUnit->code,
                    'note_internal'             => $row->note_internal,
                    'note_external'               => $row->note_external,
                    'note'        => $row_detail->note,
                    'no_sj'          => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->code : '-',
                ];
            }

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
        ob_end_clean();
        ob_start();

        $search= null;
        $status = $request->status? $request->status : '';
        $type_sales = $request->type_sales ? $request->type_sales : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $type_deliv = $request->type_deliv? $request->type_deliv : '';
        $company = $request->company ? $request->company : '';
        $customer = $request->customer? $request->customer : '';
        $delivery = $request->delivery? $request->delivery : '';
        $sales = $request->sales ? $request->sales : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';

		return Excel::download(new ExportTransactionPageMarketingOrderDetail2('',$status,$type_sales,$type_pay,$type_deliv,$company,$customer,$delivery,$sales,$currency,$end_date,$start_date), 'report_sales_'.uniqid().'.xlsx');
    }
}
