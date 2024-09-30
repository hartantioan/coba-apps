<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderDelivery;
use App\Models\Menu;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportTransactionPageMarketingOrderInvoice;
class ReportMarketingOrderDeliveryController extends Controller
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
        $menu = Menu::where('url', $parentSegment)->first();
        $data = [
            'title'     => 'Report Marketing Order',
            'content'   => 'admin.sales.report_mod',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search = $request->search;
        $query_data = MarketingOrderDelivery::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('note_internal', 'like', "%$search%")
                        ->orWhere('note_external', 'like', "%$search%")
                        ->orWhere('destination_address','like',"%$search%")
                        ->orWhereHas('user',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })
                        ->orWhereHas('account',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })
                        ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
                            $query->whereHas('item',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                        });
                });
            }

            if($request->status){
                $query->whereIn('status', $request->status);
            }

            if($request->start_date && $request->finish_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('post_date','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('post_date','<=', $request->finish_date);
            }

            if($request->account_id){
                $query->whereIn('account_id',$request->account_id);
            }

            if($request->marketing_order_id){
                $query->whereIn('marketing_order_id',$request->marketing_order_id);
            }
            
            if($request->company_id){
                $query->where('company_id',$request->company_id);
            }

        })
        ->get();
        
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
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type = $request->type? $request->type :'';
        $account_id = $request->account? $request->account : '';
        $company = $request->company ? $request->company : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportTransactionPageMarketingOrderInvoice($search,$status,$account_id,$type,$company,$end_date,$start_date), 'marketing_order_invoices_'.uniqid().'.xlsx');
    }


}
