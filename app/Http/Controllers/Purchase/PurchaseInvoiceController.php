<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\GoodReturnPO;
use App\Models\Department;
use App\Models\Line;
use App\Models\Machine;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\Place;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoiceDp;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LandedCost;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodReceipt;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseInvoice;
use App\Models\User;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder;

class PurchaseInvoiceController extends Controller
{

    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    public function index(Request $request)
    {
        $data = [
            'title'         => 'A/P Invoice',
            'content'       => 'admin.purchase.invoice',
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'PINV-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PurchaseInvoice::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getAccountData(Request $request){
        $account = User::find($request->id);

        $details = [];
        $downpayments = [];
        
        $datadp = PurchaseDownPayment::where('account_id',$request->id)->whereIn('status',['2','3'])->get();

        foreach($datadp as $row){
            if($row->balanceInvoice() > 0){
                $downpayments[] = [
                    'type'          => 'purchase_down_payments',
                    'id'            => $row->id,
                    'rawcode'       => $row->code,
                    'code'          => CustomHelper::encrypt($row->code),
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->balanceInvoice(),2,',','.'),
                ];
            }
        }
        
        $datapo = PurchaseOrder::whereIn('status',['2','3'])->where('inventory_type','2')->where('account_id',$request->id)->get();

        foreach($datapo as $row){
            $invoice = $row->totalInvoice();
            if(($row->grandtotal - $invoice) > 0){
                $details[] = [
                    'type'          => 'purchase_orders',
                    'id'            => $row->id,
                    'code'          => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                ];
            }
        }

        $datagr = GoodReceipt::whereIn('status',['2','3'])->where('account_id',$request->id)->get();
        
        foreach($datagr as $row){
            $invoice = $row->totalInvoice();
            if(($row->grandtotal - $invoice) > 0){
                $details[] = [
                    'type'          => 'good_receipts',
                    'id'            => $row->id,
                    'code'          => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency()->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                ];
            }
        }
    
        $datalc = LandedCost::where('account_id',$request->id)->whereIn('status',['2','3'])->get();

        foreach($datalc as $row){
            $invoice = $row->totalInvoice();
            if(($row->grandtotal - $invoice) > 0){
                $details[] = [
                    'type'          => 'landed_costs',
                    'id'            => $row->id,
                    'code'          => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                ];
            }
        }

        $account['details'] = $details;
        $account['downpayments'] = $downpayments;

        return response()->json($account);
    }
    public function getGoodReceiptLandedCost(Request $request){

        $details = [];
        $downpayments = [];

        foreach($request->arr_type as $key => $row){
            if($row == 'purchase_down_payments'){
                $datadp = PurchaseDownPayment::find(intval($request->arr_id[$key]));
                if($datadp->balanceInvoice() > 0){
                    $downpayments[] = [
                        'rawcode'       => $datadp->code,
                        'code'          => CustomHelper::encrypt($datadp->code),
                        'post_date'     => date('d/m/y',strtotime($datadp->post_date)),
                        'total'         => number_format($datadp->total,2,',','.'),
                        'grandtotal'    => number_format($datadp->grandtotal,2,',','.'),
                        'balance'       => number_format($datadp->balanceInvoice(),2,',','.'),
                    ];
                }
            }elseif($row == 'purchase_orders'){
                $datapo = PurchaseOrder::find(intval($request->arr_id[$key]));
                foreach($datapo->purchaseOrderDetail as $rowdetail){
                    if($rowdetail->balanceInvoice() > 0){
                        $arrTotal = $rowdetail->getArrayTotal();
                        $details[] = [
                            'type'          => 'purchase_order_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->item_id ? $rowdetail->item->code.' - '.$rowdetail->item->name : $rowdetail->coa->code.' - '.$rowdetail->coa->name,
                            'qty_received'  => number_format($rowdetail->qty,3,',','.'),
                            'qty_returned'  => 0,
                            'qty_balance'   => number_format($rowdetail->qty,3,',','.'),
                            'price'         => number_format($arrTotal['total'] / $rowdetail->qty,2,',','.'),
                            'buy_unit'      => $rowdetail->item_id ? $rowdetail->item->buyUnit->code : '-',
                            'rawcode'       => $datapo->code,
                            'post_date'     => date('d/m/y',strtotime($datapo->post_date)),
                            'due_date'      => date('d/m/y',strtotime($datapo->post_date)),
                            'total'         => number_format($arrTotal['total'],2,',','.'),
                            'tax'           => number_format($arrTotal['tax'],2,',','.'),
                            'wtax'          => number_format($arrTotal['wtax'],2,',','.'),
                            'grandtotal'    => number_format($arrTotal['grandtotal'],2,',','.'),
                            'info'          => $rowdetail->note,
                            'note'          => $rowdetail->note,
                            'note2'         => $rowdetail->note2,
                            'top'           => $datapo->payment_term,
                            'delivery_no'   => '-',
                            'purchase_no'   => 'NO PO - '.$datapo->code,
                            'percent_tax'   => $rowdetail->percent_tax,
                            'percent_wtax'  => $rowdetail->percent_wtax,
                            'include_tax'   => $rowdetail->is_include_tax,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                            'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                            'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                            'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                        ];
                    }
                }
            }elseif($row == 'good_receipts'){
                $datagr = GoodReceipt::find(intval($request->arr_id[$key]));
                $top = 0;
                $info = '';
                foreach($datagr->goodReceiptDetail as $rowdetail){
                    if($top < $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term){
                        $top = $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term;
                    }
                    $info .= 'Diterima '.$rowdetail->qty.' '.$rowdetail->item->buyUnit->code.' dari '.$rowdetail->purchaseOrderDetail->qty.' '.$rowdetail->item->buyUnit->code;
                }
                foreach($datagr->goodReceiptDetail as $rowdetail){
                    if($rowdetail->balanceInvoice() > 0){
                        $details[] = [
                            'type'          => 'good_receipt_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->item->code.' - '.$rowdetail->item->name,
                            'qty_received'  => number_format($rowdetail->qty,3,',','.'),
                            'qty_returned'  => number_format($rowdetail->qtyReturn(),3,',','.'),
                            'qty_balance'   => number_format(($rowdetail->qty - $rowdetail->qtyReturn()),3,',','.'),
                            'price'         => number_format($rowdetail->purchaseOrderDetail->price,2,',','.'),
                            'buy_unit'      => $rowdetail->item->buyUnit->code,
                            'rawcode'       => $datagr->code,
                            'post_date'     => date('d/m/y',strtotime($datagr->post_date)),
                            'due_date'      => date('d/m/y',strtotime($datagr->due_date)),
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'info'          => $info,
                            'note'          => $rowdetail->note,
                            'note2'         => $rowdetail->note2,
                            'top'           => $top,
                            'delivery_no'   => 'NO SJ - '.$datagr->delivery_no,
                            'purchase_no'   => 'NO PO - '.$rowdetail->purchaseOrderDetail->purchaseOrder->code,
                            'percent_tax'   => $rowdetail->purchaseOrderDetail->percent_tax,
                            'percent_wtax'  => $rowdetail->purchaseOrderDetail->percent_wtax,
                            'include_tax'   => $rowdetail->purchaseOrderDetail->is_include_tax,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                            'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                            'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                            'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                        ];
                    }
                }
            }elseif($row == 'landed_costs'){
                $datalc = LandedCost::find(intval($request->arr_id[$key]));

                if($datalc->balanceInvoice() > 0){
                    foreach($datalc->landedCostDetail as $rowdetail){
                        $details[] = [
                            'type'          => 'landed_cost_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->item->code,
                            'qty_received'  => 1,
                            'qty_returned'  => 0,
                            'qty_balance'   => 1,
                            'price'         => number_format($rowdetail->nominal,2,',','.'),
                            'buy_unit'      => $rowdetail->item->buyUnit->code,
                            'rawcode'       => $rowdetail->item->code,
                            'post_date'     => date('d/m/y',strtotime($datalc->post_date)),
                            'due_date'      => date('d/m/y',strtotime($datalc->post_date)),
                            'total'         => number_format($rowdetail->nominal,2,',','.'),
                            'tax'           => number_format($rowdetail->getTax(),2,',','.'),
                            'wtax'          => number_format($rowdetail->getWtax(),2,',','.'),
                            'grandtotal'    => number_format($rowdetail->getGrandtotal(),2,',','.'),
                            'info'          => $datalc->code,
                            'note'          => $datalc->note,
                            'note2'         => '',
                            'top'           => 0,
                            'delivery_no'   => '-',
                            'purchase_no'   => '-',
                            'percent_tax'   => 0,
                            'percent_wtax'  => 0,
                            'include_tax'   => 0,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                            'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->name : '',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '',
                            'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '',
                            'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '',
                        ];
                    }
                }
            }
        }

        $result['details'] = $details;
        $result['downpayments'] = $downpayments;

        return response()->json($result);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'received_date',
            'due_date',
            'document_date',
            'type',
            'document',
            'note',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'subtotal',
            'percent_discount',
            'nominal_discount',
            'total',
            'tax',
            'grandtotal',
            'downpayment',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseInvoice::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($search) {
                                    $query->where('code','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($search) {
                                    $query->where('code','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->received_date)),
                    date('d/m/y',strtotime($val->due_date)),
                    date('d/m/y',strtotime($val->document_date)),
                    $val->type(),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    $val->tax_no,
                    $val->tax_cut_no,
                    date('d/m/y',strtotime($val->cut_date)),
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->percent_discount,2,',','.'),
                    number_format($val->nominal_discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->downpayment,2,',','.'),
                    number_format($val->balance,2,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'			            => $request->temp ? ['required', Rule::unique('purchase_invoices', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:purchase_invoices,code',
			'account_id' 			    => 'required',
			'type'                      => 'required',
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'received_date'             => 'required',
            'due_date'                  => 'required',
            'document_date'             => 'required',
            'arr_type'                  => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_grandtotal'            => 'required|array'
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai',
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
			'type.required'                     => 'Tipe invoice tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'received_date.required'            => 'Tanggal terima tidak boleh kosong.',
            'due_date.required'                 => 'Tanggal tenggat tidak boleh kosong.',
            'document_date.required'            => 'Tanggal dokumen tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_total.required'                => 'Nominal total tidak boleh kosong.',
            'arr_total.array'                   => 'Nominal harus dalam bentuk array.',
            'arr_tax.required'                  => 'Nominal pajak tidak boleh kosong.',
            'arr_tax.array'                     => 'Nominal pajak harus dalam bentuk array.',
            'arr_grandtotal.required'           => 'Grandtotal tidak boleh kosong.',
            'arr_grandtotal.array'              => 'Grandtotal harus dalam bentuk array.'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $total = 0;
            $tax = 0;
            $wtax = 0;
            $grandtotal = 0;
            $balance = 0;
            $downpayment = str_replace(',','.',str_replace('.','',$request->downpayment));
            $rounding = str_replace(',','.',str_replace('.','',$request->rounding));

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
                $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                $wtax += str_replace(',','.',str_replace('.','',$request->arr_wtax[$key]));
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
            }

            $balance = $grandtotal - $downpayment + $rounding;
            if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->temp))->first();

                    $approved = false;
                    $revised = false;

                    if($query->approval()){
                        foreach ($query->approval() as $detail){
                            foreach($detail->approvalMatrix as $row){
                                if($row->approved){
                                    $approved = true;
                                }

                                if($row->revised){
                                    $revised = true;
                                }
                            }
                        }
                    }

                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'A/P Invoice telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/purchase_invoices');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->received_date = $request->received_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->type = $request->type;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->wtax = round($wtax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->downpayment = round($downpayment,3);
                        $query->rounding = round($rounding,3);
                        $query->balance = round($balance,3);
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->tax_no = $request->tax_no;
                        $query->tax_cut_no = $request->tax_cut_no;
                        $query->cut_date = $request->cut_date;
                        $query->spk_no = $request->spk_no;
                        $query->invoice_no = $request->invoice_no;
                        $query->status = '1';

                        $query->save();

                        foreach($query->purchaseInvoiceDetail as $row){
                            $row->delete();
                        }

                        foreach($query->purchaseInvoiceDp as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
            }else{
                DB::beginTransaction();
                try {
                    $query = PurchaseInvoice::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'received_date'             => $request->received_date,
                        'due_date'                  => $request->due_date,
                        'document_date'             => $request->document_date,
                        'type'                      => $request->type,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'wtax'                      => round($wtax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'downpayment'               => round($downpayment,3),
                        'rounding'                  => round($rounding,3),
                        'balance'                   => round($balance,3),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_invoices') : NULL,
                        'status'                    => '1',
                        'tax_no'                    => $request->tax_no,
                        'tax_cut_no'                => $request->tax_cut_no,
                        'cut_date'                  => $request->cut_date,
                        'spk_no'                    => $request->spk_no,
                        'invoice_no'                => $request->invoice_no
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {
                DB::beginTransaction();
                try {
                    if($request->arr_type){
                        
                        foreach($request->arr_type as $key => $row){
                            PurchaseInvoiceDetail::create([
                                'purchase_invoice_id'   => $query->id,
                                'lookable_type'         => $row,
                                'lookable_id'           => $request->arr_code[$key],
                                'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                                'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                                'tax_id'                => $request->arr_tax_id[$key] ? $request->arr_tax_id[$key] : NULL,
                                'wtax_id'               => $request->arr_wtax_id[$key] ? $request->arr_wtax_id[$key] : NULL,
                                'is_include_tax'        => $request->arr_include_tax[$key],
                                'percent_tax'           => $request->arr_percent_tax[$key],
                                'tax'                   => str_replace(',','.',str_replace('.','',$request->arr_tax[$key])),
                                'percent_wtax'          => $request->arr_percent_wtax[$key],
                                'wtax'                  => str_replace(',','.',str_replace('.','',$request->arr_wtax[$key])),
                                'grandtotal'            => str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key])),
                                'note'                  => $request->arr_note[$key],
                                'note2'                 => $request->arr_note2[$key],
                                'place_id'              => $request->arr_place[$key] ? $request->arr_place[$key] : NULL,
                                'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                                'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                                'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                                'warehouse_id'          => $request->arr_warehouse[$key] ? $request->arr_warehouse[$key] : NULL,
                            ]);
                        }
                            
                    }

                    if($request->arr_dp_code){
                        foreach($request->arr_dp_code as $key => $row){
                            PurchaseInvoiceDp::create([
                                'purchase_invoice_id'       => $query->id,
                                'purchase_down_payment_id'  => PurchaseDownPayment::where('code',CustomHelper::decrypt($row))->first()->id,
                                'nominal'                   => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                            ]);
                        }
                    }
                
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('purchase_invoices',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_invoices',$query->id,'Pengajuan A/P Invoice No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::removeDeposit($query->account_id,$query->downpayment);

                activity()
                    ->performedOn(new PurchaseInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit A/P Invoice.');

                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];
            } else {
                $response = [
                    'status'  => 500,
                    'message' => 'Data failed to save.'
                ];
            }
		}
		
		return response()->json($response);
    }

    public function createMulti(Request $request){
        $validation = Validator::make($request->all(), [
            'arr_multi_code'                          => 'required|array',
		], [
            'arr_multi_code.required'                 => 'Kode multi tidak boleh kosong.',
            'arr_multi_code.array'                    => 'Kode multi harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $cekSameCode = PurchaseInvoice::whereIn('code',$request->arr_multi_code)->count();

            if($cekSameCode > 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Kode AP INvoice telah terpakai, silahkan gunakan yang lainnya.'
                ]);
            }
        
            DB::beginTransaction();
            try {

                $temp = '';
                foreach($request->arr_multi_code as $key => $row){

                    if($temp !== $row){
                        $query = PurchaseInvoice::create([
                            'code'			            => $row,
                            'user_id'		            => session('bo_id'),
                            'account_id'                => $request->arr_multi_supplier[$key],
                            'company_id'                => $request->arr_multi_company[$key],
                            'post_date'                 => date('Y-m-d',strtotime($request->arr_multi_post_date[$key])),
                            'received_date'             => date('Y-m-d',strtotime($request->arr_multi_received_date[$key])),
                            'due_date'                  => date('Y-m-d',strtotime($request->arr_multi_due_date[$key])),
                            'document_date'             => date('Y-m-d',strtotime($request->arr_multi_document_date[$key])),
                            'type'                      => $request->arr_multi_type[$key],
                            'total'                     => 0,
                            'tax'                       => 0,
                            'wtax'                      => 0,
                            'grandtotal'                => 0,
                            'downpayment'               => 0,
                            'rounding'                  => 0,
                            'balance'                   => 0,
                            'note'                      => $request->arr_multi_note[$key],
                            'document'                  => NULL,
                            'status'                    => '1',
                            'tax_no'                    => $request->arr_multi_tax_no[$key],
                            'tax_cut_no'                => $request->arr_multi_tax_cut_no[$key],
                            'cut_date'                  => date('Y-m-d',strtotime($request->arr_multi_cut_date[$key])),
                            'spk_no'                    => $request->arr_multi_spk_no[$key],
                            'invoice_no'                => $request->arr_multi_invoice_no[$key],
                        ]);

                        activity()
                            ->performedOn(new PurchaseInvoice())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add / edit AP Invoice multi.');
                    }

                    if($query) {

                        if(floatval($request->arr_multi_total[$key]) > 0){
                            PurchaseInvoiceDetail::create([
                                'purchase_invoice_id'   => $query->id,
                                'lookable_type'         => 'coas',
                                'lookable_id'           => $request->arr_multi_coa[$key],
                                'qty'                   => $request->arr_multi_qty[$key],
                                'price'                 => $request->arr_multi_price[$key],
                                'total'                 => $request->arr_multi_total[$key],
                                'tax'                   => $request->arr_multi_ppn[$key],
                                'tax_id'                => $request->arr_multi_tax_id[$key] ? $request->arr_multi_tax_id[$key] : NULL,
                                'is_include_tax'        => '0',
                                'wtax_id'               => $request->arr_multi_wtax_id[$key] ? $request->arr_multi_wtax_id[$key] : NULL,
                                'wtax'                  => $request->arr_multi_pph[$key],
                                'grandtotal'            => $request->arr_multi_grandtotal[$key],
                                'note'                  => $request->arr_multi_note_1[$key],
                                'note2'                 => $request->arr_multi_note_2[$key],
                                'place_id'              => $request->arr_multi_place[$key] ? $request->arr_multi_place[$key] : NULL,
                                'line_id'               => $request->arr_multi_line[$key] ? $request->arr_multi_line[$key] : NULL,
                                'machine_id'            => $request->arr_multi_machine[$key] ? $request->arr_multi_machine[$key] : NULL,
                                'department_id'         => $request->arr_multi_department[$key] ? $request->arr_multi_department[$key] : NULL,
                                'warehouse_id'          => $request->arr_multi_warehouse[$key] ? $request->arr_multi_warehouse[$key] : NULL,
                            ]);
                        }
                    }
                    
                    $temp = $row;
                }

                $temp = '';
                foreach($request->arr_multi_code as $key => $row){

                    if($temp !== $row){
                        $pi = null;
                        $pi = PurchaseInvoice::where('code',$row)->first();
                        if($pi){
                            $pi->updateTotal();
                            CustomHelper::sendApproval('purchase_invoices',$pi->id,$pi->note);
                            CustomHelper::sendNotification('purchase_invoices',$pi->id,'Pengajuan AP Invoice No. '.$pi->code,$pi->note,session('bo_id'));
                        }
                    }

                    $temp = $row;
                }

                $response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json($response);
    }

    public function rowDetail(Request $request)
    {
        $data   = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item / Biaya</th>
                                <th class="center-align">Keterangan 1</th>
                                <th class="center-align">Keterangan 2</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPh</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->purchaseInvoiceDetail) > 0){
            foreach($data->purchaseInvoiceDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->getCode().'</td>
                    <td class="">'.$row->note.'</td>
                    <td class="">'.$row->note2.'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="6">Data detail tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
        <thead>
            <tr>
                <th class="center-align" colspan="4">Daftar Down Payment Dipakai</th>
            </tr>
            <tr>
                <th class="center-align">No.</th>
                <th class="center-align">No Down Payment</th>
                <th class="center-align">Total</th>
                <th class="center-align">Dipakai</th>
            </tr>
        </thead><tbody>';

        if(count($data->purchaseInvoiceDp) > 0){
            foreach($data->purchaseInvoiceDp as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->purchaseDownPayment->code.'</td>
                    <td class="right-align">'.number_format($row->purchaseDownPayment->grandtotal,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Data down payment tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="4"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';
    
                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }
    
                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pi = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();
                
        if($pi){
            $data = [
                'title'     => 'Print A/P Invoice',
                'data'      => $pi
            ];

            return view('admin.approval.purchase_invoice', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print A/P Invoice',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.purchase.invoice_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pi = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        $pi['code_place_id'] = substr($pi->code,7,2);
        $pi['account_name'] = $pi->account->name;
        $pi['total'] = number_format($pi->total,2,',','.');
        $pi['tax'] = number_format($pi->tax,2,',','.');
        $pi['wtax'] = number_format($pi->wtax,2,',','.');
        $pi['grandtotal'] = number_format($pi->grandtotal,2,',','.');
        $pi['downpayment'] = number_format($pi->downpayment,2,',','.');
        $pi['rounding'] = number_format($pi->rounding,2,',','.');

        $downpayments = [];
        
        foreach($pi->purchaseInvoiceDp as $row){
            $downpayments[] = [
                'rawcode'       => $row->purchaseDownPayment->code,
                'code'          => CustomHelper::encrypt($row->purchaseDownPayment->code),
                'post_date'     => date('d/m/y',strtotime($row->purchaseDownPayment->post_date)),
                'nominal'       => number_format($row->nominal,2,',','.'),
                'grandtotal'    => number_format($row->purchaseDownPayment->grandtotal,2,',','.'),
            ];
        }

        $arr = [];

        foreach($pi->purchaseInvoiceDetail as $row){
            $arr[] = [
                'type'          => $row->lookable_type,
                'id'            => $row->lookable_id,
                'name'          => $row->getCode(),
                'qty_received'  => 0,
                'qty_returned'  => 0,
                'qty_balance'   => number_format($row->qty,3,',','.'),
                'price'         => number_format($row->price,2,',','.'),
                'buy_unit'      => $row->getUnitCode(),
                'rawcode'       => $row->getHeaderCode(),
                'post_date'     => $row->getPostDate(),
                'due_date'      => $row->getDueDate(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'info'          => $row->note,
                'note'          => $row->note,
                'note2'         => $row->note2,
                'top'           => $row->getTop(),
                'delivery_no'   => 'NO SJ - '.$row->getDeliveryCode(),
                'purchase_no'   => 'NO PO - '.$row->getPurchaseCode(),
                'percent_tax'   => $row->percent_tax,
                'percent_wtax'  => $row->percent_wtax,
                'include_tax'   => $row->is_include_tax,
                'place_id'      => $row->place_id ? $row->place_id : '',
                'line_id'       => $row->line_id ? $row->line_id : '',
                'machine_id'    => $row->machine_id ? $row->machine_id : '',
                'department_id' => $row->department_id ? $row->department_id : '',
                'warehouse_id'  => $row->warehouse_id ? $row->warehouse_id : '',
                'place_name'    => $row->place_id ? $row->place->name : '-',
                'line_name'     => $row->line_id ? $row->line->name : '-',
                'machine_name'  => $row->machine_id ? $row->machine->name : '-',
                'department_name'=> $row->department_id ? $row->department->name : '-',
                'warehouse_name'=> $row->warehouse_id ? $row->warehouse->name : '-',
            ];
        }

        $pi['details'] = $arr;
        $pi['downpayments'] = $downpayments;
        				
		return response()->json($pi);
    }

    public function voidStatus(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Payment Request.'
                ];
            }else{

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                activity()
                    ->performedOn(new PurchaseInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the A/P Invoice data');
    
                CustomHelper::sendNotification('purchase_invoices',$query->id,'A/P Invoice No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('purchase_invoices',$query->id);
                CustomHelper::addDeposit($query->account_id,$query->downpayment);
                CustomHelper::removeJournal('purchase_invoices',$query->id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('purchase_requests',$query->id);
            CustomHelper::addDeposit($query->account_id,$query->downpayment);
            
            $query->purchaseInvoiceDetail()->delete();
            $query->PurchaseInvoiceDp()->delete();

            activity()
                ->performedOn(new PurchaseInvoice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the A/P Invoice data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function print(Request $request){
        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $var_link=[];
            $pdf = new Dompdf();
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = PurchaseInvoice::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Print A/P Invoice',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.purchase.invoice_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            Storage::put('public/pdf/bubla.pdf',$result);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);
    }
    public function printByRangeTemp(Request $request){
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{

                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }else{
                    $pdf = new Dompdf();
         
                    $html = '';
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PurchaseInvoice::where('Code', 'LIKE', '%'.$etNumbersArray[$nomor-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print A/P Invoice',
                                'data'      => $query
                            ];
            
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
        
                            $additionalView = View::make('admin.print.purchase.invoice_individual', $data);
                            $html .= $additionalView->render();
                        }
                    }
                    $pdf = PDF::loadHTML($html)->setPaper('a5', 'landscape');
                    $content = $pdf->download()->getOriginalContent();
                    
                    Storage::put('public/pdf/bubla.pdf',$content);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));
                $pdf = new Dompdf();
        
                $html = '';

                foreach($merged as $code){
                    $etNumbersArray = explode(',', $request->tabledata);
                    $query = PurchaseInvoice::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                    if($query){
                        $data = [
                            'title'     => 'Print A/P Invoice',
                            'data'      => $query
                        ];
        
                        $img_path = 'website/logo_web_fix.png';
                        $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                        $image_temp = file_get_contents($img_path);
                        $img_base_64 = base64_encode($image_temp);
                        $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                        $data["image"]=$path_img;
    
                        $additionalView = View::make('admin.print.purchase.invoice_individual', $data);
                        $html .= $additionalView->render();
                    }
                }
                $pdf = PDF::loadHTML($html)->setPaper('a5', 'landscape');
                $content = $pdf->download()->getOriginalContent();
                
                Storage::put('public/pdf/bubla.pdf',$content);
                $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                $var_link=$document_po;
    
                $response =[
                    'status'=>200,
                    'message'  =>$merged
                ];
                

            }
        }
        return response()->json($response);
    }
    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{   
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PurchaseInvoice::where('Code', 'LIKE', '%'.$etNumbersArray[$nomor-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print A/P Invoice',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.invoice_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PurchaseInvoice::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print A/P Invoice',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.invoice_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
    }
    public function printALL(Request $request){

        $data = [
            'title' => 'A/P Invoice REPORT',
            'data' => PurchaseInvoice::where(function($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('total', 'like', "%$request->search%")
                            ->orWhere('tax', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('downpayment', 'like', "%$request->search%")
                            ->orWhere('balance', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhere('tax_no', 'like', "%$request->search%")
                            ->orWhere('tax_cut_no', 'like', "%$request->search%")
                            ->orWhere('spk_no', 'like', "%$request->search%")
                            ->orWhere('invoice_no', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('account',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($request){
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($request) {
                                    $query->where('code','like',"%$request->search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company){
                    $query->where('company_id',$request->company);
                }
            })
            ->get()
		];
		
		return view('admin.print.purchase.invoice', $data);
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		return Excel::download(new ExportPurchaseInvoice($post_date,$end_date), 'purchase_invoice'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];    
        $data_id_pyrcs=[];
        
        $data_id_lc=[];

        $data_go_chart=[];
        $data_link=[];
        if($query) {
            /*mengambil invoice*/
            $data_invoice = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[] = $data_invoice;
            $data_id_invoice[]=$query->id;
            foreach($query->purchaseInvoiceDetail as $row){

                if($row->purchaseOrderDetail()){
                    $row_po=$row->lookable->purchaseOrder;
                        $po =[
                            "name"=>$row_po->code,
                            "key" => $row_po->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_po->post_date],
                                ['name'=> "Vendor  : ".$row_po->supplier->name],
                                ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                             ],
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                        ];

                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$row_po->code,
                            'to'=>$query->code,
                            'string_link'=>$row_po->code.$query->code
                        ]; 
                        $data_id_po[]= $row_po->code->id;  
                              
                        foreach($row_po->purchaseOrderDetail as $po_detail){
                            if($po_detail->goodReceiptDetail()->exists()){
                                foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                    $data_good_receipt=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                            ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                         ],
                                        "key" => $good_receipt_detail->goodReceipt->code,
                                        "name" => $good_receipt_detail->goodReceipt->code,
                                        'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    ];
                                    
                                    $data_go_chart[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$row_po->code,
                                        'to'=>$data_good_receipt["key"],
                                        'string_link'=>$row_po->code.$data_good_receipt["key"]
                                    ];
                                    $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                    
                                }
                            }
                        }
                    
                }
                /*  melihat apakah ada hubungan grpo tanpa po */
                if($row->goodReceiptDetail()){

                    $data_good_receipt=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->lookable->goodReceipt->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row->lookable->goodReceipt->grandtotal,2,',','.')]
                        ],
                        "key" => $row->lookable->goodReceipt->code,
                        "name" => $row->lookable->goodReceipt->code,
                        'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->goodReceipt->code),
                    ];

                    $data_go_chart[]=$data_good_receipt;
                    $data_link[]=[
                        'from'=>$data_good_receipt["key"],
                        'to'=>$query->code,
                        'string_link'=>$data_good_receipt["key"].$query->code,
                    ];
                    if(!in_array($row->lookable->goodReceipt->id, $data_id_gr)){
                        $data_id_gr[] = $row->lookable->goodReceipt->id; 
                        $added = true;
                    } 
                }
                
                
                /* melihat apakah ada hubungan lc */
                if($row->landedCostDetail()){
                    $data_lc=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                        ],
                        "key" => $row->lookable->landedCost->code,
                        "name" => $row->lookable->landedCost->code,
                        'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                    ];

                    $data_go_chart[]=$data_lc;
                    $data_link[]=[
                        'from'=>$query->code,
                        'to'=>$row->lookable->landedCost->code,
                        'string_link'=>$query->code.$row->lookable->landedCost->code,
                    ];
                    $data_id_lc[] = $row->lookable->landedCost->id;
                    
                }
                
            }
            
            if($query->purchaseInvoiceDp()->exists()){
                foreach($query->purchaseInvoiceDp as $row_pi){
                    $data_down_payment=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                            ['name'=> "Nominal : Rp.:".number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')],
                        ],
                        "key" => $row_pi->purchaseDownPayment->code,
                        "name" => $row_pi->purchaseDownPayment->code,
                        'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                    ];
                    $data_go_chart[]=$data_down_payment;
                    $data_link[]=[
                        'from'=>$row_pi->purchaseDownPayment->code,
                        'to'=>$query->code,
                        'string_link'=>$row_pi->purchaseDownPayment->code.$query->code,
                    ];
                    $data_id_dp[]=$row_pi->purchaseDownPayment->id;
                    
                }
            }
            
            $added = true;
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
                            ],
                            'key'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'name'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                        ];

                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'to'=>$query_gr->code,
                            'string_link'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code.$query_gr->code
                        ];
                        $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 

                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                $good_return_tempura =[
                                    "name"=> $goodReturnPODetail->goodReturnPO->code,
                                    "key" => $goodReturnPODetail->goodReturnPO->code,
                                    
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
                                ];
                                                    
                                $data_go_chart[] = $good_return_tempura;
                                $data_link[]=[
                                    'from'=> $query_gr->code,
                                    'to'=>$goodReturnPODetail->goodReturnPO->code,
                                    'string_link'=>$query_gr->code.$goodReturnPODetail->goodReturnPO->code,
                                ];
                                $data_id_greturns[]=  $goodReturnPODetail->goodReturnPO->id;

                            }
                             
                                
                            
                        }
                        //landed cost searching
                        if($good_receipt_detail->landedCostDetail()->exists()){
                            foreach($good_receipt_detail->landedCostDetail as $landed_cost_detail){
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$landed_cost_detail->landedCost->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$landed_cost_detail->landedCost->code,
                                    'name'=>$landed_cost_detail->landedCost->code,
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost_detail->landedCost->code),    
                                ];

                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost_detail->landedCost->code,
                                    'string_link'=>$query_gr->code.$landed_cost_detail->landedCost->code,
                                ];
                                $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                
                                
                            }
                        }
                        //invoice searching
                        if($good_receipt_detail->purchaseInvoiceDetail()->exists()){
                            foreach($good_receipt_detail->purchaseInvoiceDetail as $invoice_detail){
                                $invoice_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        
                                    ],
                                    'key'=>$invoice_detail->purchaseInvoice->code,
                                    'name'=>$invoice_detail->purchaseInvoice->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                                ];

                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_gr->code.$invoice_detail->purchaseInvoice->code
                                ];
                                
                                if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                    $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                    $added = true; 
                                }
                            }
                        }

                    }
                }




                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                    foreach($query_return->goodReturnPODetail as $good_return_detail){
                        $data_good_receipt = [
                            "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "color"=>"lightblue",
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        
                        $data_good_receipt[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$data_good_receipt["key"],
                            'to'=>$query_return->code,
                            'string_link'=>$data_good_receipt["key"].$query_return->code,
                        ];
                        
                        if(!in_array($good_return_detail->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                            $data_id_gr[] = $good_return_detail->goodReceiptDetail->goodReceipt->id;
                            $added = true;
                        }
                    }
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrderDetail()){
                            $row_po=$row->lookable->purchaseOrder;
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];

                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row_po->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_po->code.$query_invoice->code
                                ]; 
                                $data_id_po[]= $purchase_order_detail->purchaseOrder->id;  
                                      
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail()->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                                 ],
                                                "key" => $good_receipt_detail->goodReceipt->code,
                                                "name" => $good_receipt_detail->goodReceipt->code,
                                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                            ];
                                            
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row_po->code,
                                                'to'=>$data_good_receipt["key"],
                                                'string_link'=>$row_po->code.$data_good_receipt["key"]
                                            ];
                                            $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            
                                        }
                                    }
                                }
                            
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceiptDetail()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->goodReceipt->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->goodReceipt->code,
                                "name" => $row->lookable->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->goodReceipt->code),
                            ];

                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query_invoice->code,
                                'string_link'=>$data_good_receipt["key"].$query_invoice->code,
                            ];
                            if(!in_array($row->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $row->lookable->goodReceipt->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCostDetail()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->landedCost->code,
                                "name" => $row->lookable->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$data_lc;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row->lookable->landedCost->code,
                                'string_link'=>$query_invoice->code.$row->lookable->landedCost->code,
                            ];
                            $data_id_lc[] = $row->lookable->landedCost->id;
                            
                        }

                        if($row->purchaseMemoDetail()->exists()){
                            foreach($row->purchaseMemoDetail as $purchase_memodetail){
                                $data_memo = [
                                    "name"=>$purchase_memodetail->purchaseMemo->code,
                                    "key" => $purchase_memodetail->purchaseMemo->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                                ];
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$purchase_memodetail->purchaseMemo->code,
                                    'string_link'=>$query_invoice->code.$purchase_memodetail->purchaseMemo->code,
                                ];
                                $data_id_memo[]=$purchase_memodetail->purchaseMemo->id;
                                $data_go_chart[]=$data_memo;
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_pi->purchaseDownPayment->code.$query_invoice->code,
                                ];
            
                            if($row_pi->purchaseDownPayment->hasPaymentRequestDetail()->exists()){
                                foreach($row_pi->purchaseDownPayment->hasPaymentRequestDetail as $row_pyr_detail){
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pi->purchaseDownPayment->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pi->purchaseDownPayment->code.$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        


                                    if($row_pyr_detail->fundRequest()){
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                       
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ];        
                                        
                                    }
                                    if($row_pyr_detail->purchaseDownPayment()){
                                        $data_downp_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                         
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        
                                    }
                                    if($row_pyr_detail->purchaseInvoice()){
                                        $data_invoices_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                   
                                               
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                        ];
                                        
                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                            $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                            $added=true;
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if($query_invoice->hasPaymentRequestDetail()->exists()){
                        foreach($query_invoice->hasPaymentRequestDetail as $row_pyr_detail){
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_invoice->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            
                            if($row_pyr_detail->fundRequest()){
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                             
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                ];             
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];

                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                
                            }
                            if($row_pyr_detail->purchaseInvoice()){
                                $data_invoices_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                
                                       
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                    $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                    $added=true;
                                }
                            }
                        }
                    }
                }

                foreach($data_id_pyrs as $payment_request_id){
                    $query_pyr = PaymentRequest::find($payment_request_id);
                    
                    if($query_pyr->outgoingPayment()->exists()){
                        $outgoing_payment = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyr->outgoingPayment->code,
                            "name" => $query_pyr->outgoingPayment->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyr->outgoingPayment->code),  
                        ];

                        $data_go_chart[]=$outgoing_payment;
                        $data_link[]=[
                            'from'=>$query_pyr->code,
                            'to'=>$query_pyr->outgoingPayment->code,
                            'string_link'=>$query_pyr->code.$query_pyr->outgoingPayment->code,
                        ]; 
                        
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        
                        $data_pyr_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                    
                        if($row_pyr_detail->fundRequest()){
                            
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ];
                           
                               
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                        }
                        if($row_pyr_detail->purchaseDownPayment()){
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];       
                            
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                               
                            }
                        }
                        if($row_pyr_detail->purchaseInvoice()){
                            $data_invoices_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                          
                                   
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }
                        
                        if($row_pyr_detail->paymentRequest->paymentRequestCross()->exists()){
           
                           
                            foreach($row_pyr_detail->paymentRequest->paymentRequestCross as $row_pyr_cross){
                                
                                $data_pyrc_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_cross->lookable->code,
                                    "name" => $row_pyr_cross->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                ];
                       
                                $data_go_chart[]=$data_pyrc_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_cross->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                if(!in_array($row_pyr_cross->lookable->id, $data_id_pyrcs)){
                                    $data_id_pyrcs[] = $row_pyr_cross->lookable->id;
                                }
                            }

                            
                        }
                    }
                    
                }
                foreach($data_id_pyrcs as $payment_request_cross_id){
                    $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                    if($query_pyrc->paymentRequest->exists()){
                        $data_pyr_tempura = [
                            'key'   => $query_pyrc->paymentRequest->code,
                            "name"  => $query_pyrc->paymentRequest->code,
                            'properties'=> [
                                 ['name'=> "Tanggal: ".date('d/m/y',strtotime($query_pyrc->paymentRequest->post_date))],
                              ],
                            'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                            "title" =>$query_pyrc->paymentRequest->code,
                        ];
                        $data_go_chart[]=$data_pyr_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                        ];
                        
                        if(!in_array($query_pyrc->id, $data_id_pyrs)){
                            $data_id_pyrs[] = $query_pyrc->id;
                            $added=true;
                        }
                    }
                    if($query_pyrc->outgoingPayment()){
                        $outgoing_tempura = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyrc->lookable->code,
                            "name" => $query_pyrc->lookable->code,
                            'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                        ];
    
                        $data_go_chart[]=$outgoing_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                        ];
                    }
                }
                foreach($data_id_dp as $downpayment_id){
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder->exists()){
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal : Rp.:".number_format($row->purchaseOrder->grandtotal,2,',','.')],
                                ],
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                            ];
                          
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$row->purchaseOrder->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$row->purchaseOrder->code.$query_dp->code,
                            ];
                            
                            $data_id_po []=$row->purchaseOrder->id; 
                                
                            
                           
                            
                            
                            /* mendapatkan request po */
                            foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){

                                if($po_detail->purchaseRequestDetail()->exists()){
                                   
                                    $pr = [
                                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                           
                                         ],
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                                    ];
                                    $data_go_chart[]=$pr;
                                    $data_link[]=[
                                        'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$row->purchaseOrder->code,
                                        'string_link'=>$po_detail->purchaseRequestDetail->purchaseRequest->code.$row->purchaseOrder->code
                                    ];
                                    $data_id_pr[]=$po_detail->purchaseRequestDetail->purchaseRequest->id;
                                        
                                    
                                }
                                /* mendapatkan gr po */
                                if($po_detail->goodReceiptDetail()->exists()){
                                    foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                            
                                        $data_good_receipt = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                             ],
                                            "key" => $good_receipt_detail->goodReceipt->code,
                                            "name" => $good_receipt_detail->goodReceipt->code,
                                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                                        ];
                                               
                                        $data_go_chart[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$row->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                            'string_link'=>$row->purchaseOrder->code.$data_good_receipt["key"],
                                        ];
                                           
                                        
                                        if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                            $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                            $added = true;
                                        }
                    
                                    }
                                }
                            }
                             
        
                        }
                        
                    }

                    foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                        
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                        ];
                        
                           
                        $data_go_chart[]=$invoice_tempura;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            'string_link'=>$query_dp->code.$purchase_invoicedp->purchaseInvoice->code,
                        ];
                        
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
                        }
                    }

                    foreach($query_dp->purchaseMemoDetail as $purchase_memodetail){
                        $data_memo=[
                            "name"=>$purchase_memodetail->purchaseMemo->code,
                            "key" => $purchase_memodetail->purchaseMemo->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                        ];
                        $data_go_chart[]=$data_memo;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_memodetail->purchaseMemo->code,
                            'string_link'=>$query_dp->code.$purchase_memodetail->purchaseMemo->code,
                        ];
                        

                    }

                }

                foreach($data_id_memo as $memo_id){
                    $query = PurchaseMemo::find($memo_id);
                    foreach($query->purchaseMemoDetail as $row){
                        if($row->lookable_type == 'purchase_invoice_details'){
                            $data_invoices_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->purchaseInvoice->code,
                                "name" => $row->lookable->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row->lookable->purchaseInvoice->code),
                            ];
        
                            $data_go_chart[]=$data_invoices_tempura;
                            $data_link[]=[
                                'from'=>$data_invoices_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_invoices_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $row->lookable->purchaseInvoice->id;
                                $added=true;
                            }
                        }elseif($row->lookable_type == 'purchase_down_payments'){
                            $data_downp_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$data_downp_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_downp_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row->lookable->id;
                                $added=true;
                            }
                        }
                        
                    }
                }
                
                foreach($data_id_lc as $landed_cost_id){
                    $query= LandedCost::find($landed_cost_id);
                    foreach($query->landedCostDetail as $lc_detail ){
                        if($lc_detail->goodReceiptDetail()){
                            $data_good_receipt = [
                                "key" => $lc_detail->lookable->goodReceipt->code,
                                'name'=> $lc_detail->lookable->goodReceipt->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->goodReceipt->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/good_receipt?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
                            ];
                            
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_good_receipt["key"].$query->code,
                            ];
                               
                            
                            if(!in_array($lc_detail->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $lc_detail->lookable->goodReceipt->id;
                                $added = true;
                            }

                        }
                        if($lc_detail->landedCostDetail()){
                            $lc_other = [
                                "key" => $lc_detail->lookable->landedCost->code,
                                "name" => $lc_detail->lookable->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($lc_detail->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$lc_other;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->landedCost->code,
                                'string_link'=>$query->code.$lc_detail->lookable->landedCost->code,
                            ];
                            $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                              
                        }
                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    $query_po = PurchaseOrder::find($po_id);
                   
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                       
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                        
                            $pr_tempura=[
                                'key'   => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                "name"  => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                   
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_order_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                    
                            $data_go_chart[]=$pr_tempura;
                            $data_link[]=[
                                'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                'to'=>$query_po->code,
                                'string_link'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code.$query_po->code,
                            ];
                            $data_id_pr[]=$purchase_order_detail->purchaseRequestDetail->purchaseRequest->id;
                            
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                    ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseOrder->code,
                                    'to'=>$data_good_receipt["key"],
                                    'string_link'=>$purchase_order_detail->purchaseOrder->code.$data_good_receipt["key"],
                                ];
                                
                                $data_go_chart[]=$data_good_receipt;  
                                
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_pr as $pr_id){
                    $query_pr = PurchaseRequest::find($pr_id);
                    foreach($query_pr->purchaseRequestDetail as $purchase_request_detail){
                        if($purchase_request_detail->purchaseOrderDetail()->exists()){
                        
                            foreach($purchase_request_detail->purchaseOrderDetail as $purchase_order_detail){
                                $po_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".$purchase_order_detail->purchaseOrder->supplier->name],
                                     ],
                                    'key'=>$purchase_order_detail->purchaseOrder->code,
                                    'name'=>$purchase_order_detail->purchaseOrder->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_pr->code,
                                    'to'=>$purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_pr->code.$purchase_order_detail->purchaseOrder->code,
                                ];
                                if(!in_array($purchase_order_detail->purchaseOrder->id,$data_id_po)){
                                    $data_id_po[] = $purchase_order_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }                     
                           
                        }
                    }
                }
            }  
            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){
                
                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }
                
                }
                $new_array = array_values($new_array);
                return $new_array;
            }

           
            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
            ];
            
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function viewJournal(Request $request,$id){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' =>  $query->lookable_id ? $query->lookable->code : '-',
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.$row->coa->company->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->name : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }
    
}