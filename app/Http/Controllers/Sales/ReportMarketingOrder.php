<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\Machine;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\UserData;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Area;
use App\Models\Company;
use App\Exports\ExportMarketingOrderTransactionPage;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportTransactionPageMarketingOrderDetail1;

use App\Exports\ExportTransactionPageMarketingOrderDetail2;
use App\Models\MarketingOrder;
use App\Models\User;
use App\Models\Tax;
class ReportMarketingOrder extends Controller
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
            'title'     => 'Report Marketing Invoice',
            'content'   => 'admin.sales.report_marketing_invoice',
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
        $search = $request->search;
        
        $query_data = MarketingOrder::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('document_no', 'like', "%$search%")
                        ->orWhere('note_internal', 'like', "%$search%")
                        ->orWhere('note_external', 'like', "%$search%")
                        ->orWhere('discount', 'like', "%$search%")
                        ->orWhere('total', 'like', "%$search%")
                        ->orWhere('tax', 'like', "%$search%")
                        ->orWhere('grandtotal', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        })
                        ->orWhereHas('marketingOrderDetail',function($query) use ($search, $request){
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

            if($request->type){
                $query->where('type',$request->type);
            }

            if($request->delivery_type){
                $query->where('type_delivery',$request->delivery_type);
            }

            if($request->payment_type){
                $query->where('payment_type',$request->payment_type);
            }

            if($request->account_id){
                $query->whereIn('account_id',$request->account_id);
            }

            if($request->sender_id){
                $query->whereIn('sender_id',$request->sender_id);
            }

            if($request->sales_id){
                $query->whereIn('sales_id',$request->sales_id);
            }
            
            if($request->company_id){
                $query->where('company_id',$request->company_id);
            }          
            
            if($request->currency_id){
                $query->whereIn('currency_id',$request->currency_id);
            }

        })
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->get();
        $newData = [];

        foreach($query_data as $key => $row){
            $subtotal = $row->subtotal * $row->currency_rate;
            $discount = $row->discount * $row->currency_rate;
            $total = $subtotal - $discount;
            foreach($row->marketingOrderDetail as $rowdetail){
                $newData[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->code,
                    'babi'              => $row->document_no,
                    'status'            => $row->statusRaw(),
                    'nik'               => $row->user->employee_no,
                    'user'              => $row->user->name,
                    'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'done_date'         => $row->doneUser()->exists() ? $row->done_date : '',
                    'done_note'         => $row->doneUser()->exists() ? $row->done_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'valid_date'        => date('d/m/Y',strtotime($row->valid_date)),
                    'customer'          => $row->account->name,
                    'company'           => $row->company->name,
                    'type'              => $row->type(),
                    'project'           => $row->project->name ?? '-',
                    'document'          => '',
                    'deliv_type'        => $row->deliveryType(),
                    'sender'            => $row->sender()->exists() ? $row->sender->name : '-',
                    'transport_type'    => $row->transportation->name,
                    'delivery_date'     => date('d/m/Y',strtotime($row->delivery_date)),
                    'payment_type'      => $row->paymentType(),
                    'TOP_IN'            => $row->top_internal,
                    'TOP_Customer'      => $row->top_customer,
                    'is_guarantee'      => $row->is_guarantee,
                    'billing_address'   => $row->billing_address,
                    'outlet'            => $row->outlet->name ?? '-',
                    'destination_address'=> $row->destination_address,
                    'province'          => $row->province->name  ?? '-',
                    'city'              => $row->com_print_typeinfo->name  ?? '-',
                    'district_id'       => $row->district->name  ?? '-',
                    'subdistrict_id'    => $row->subdistrict->name  ?? '-',
                    'sales'             => $row->sales->name  ?? '-',
                    'currency_id'       => $row->currency->name  ?? '-',
                    'currency_rate'     => number_format($row->currency_rate,2,',','.'),
                    'dp'                => $row->percent_dp,
                    'department'        => $row->note_internal,
                    'warehouse'         => $row->note_external,
                    'item'              => $rowdetail->item->code.' - '.$rowdetail->item->name,
                    'qty'               => round($rowdetail->qty,3),
                    'unit'              => $rowdetail->itemUnit->unit->code,
                    'qty_uom'           => round($rowdetail->qty_uom,3),
                    'unit_uom'          => $rowdetail->item->uomUnit->code,
                    'total'             => $rowdetail->total,
                    'note'              => $rowdetail->note,
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
      
		return Excel::download(new ExportMarketingOrderTransactionPage($search,$status,$type_sales,$type_pay,$type_deliv,$type_pay,$company,$customer,$delivery,$sales,$currency,$end_date,$start_date), 'marketing_order_'.uniqid().'.xlsx');
    }
}
