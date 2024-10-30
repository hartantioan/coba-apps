<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\GoodReturnPO;
use App\Models\Department;
use App\Models\Line;
use App\Models\Machine;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodIssueRequest;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\Item;
use App\Models\CloseBill;
use App\Exports\ExportOutstandingInvoice;
use App\Models\LandedCost;

use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\Menu;
use App\Models\Place;
use App\Models\PurchaseInvoiceDp;

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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\PurchaseInvoiceDetail;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportPurchaseInvoice;
use App\Exports\ExportPurchaseInvoiceTransactionPage;
use App\Exports\ExportTemplatePurchaseInvoice;
use App\Models\Currency;
use App\Models\Division;
use App\Models\FundRequest;
use App\Models\LandedCostFeeDetail;
use App\Models\MenuUser;
use App\Models\Project;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\CancelDocument;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder;
use App\Models\UsedData;
class PurchaseInvoiceController extends Controller
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
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'A/P Invoice',
            'content'       => 'admin.purchase.invoice',
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Division::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
            'currency'      => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = PurchaseInvoice::generateCode($request->val);

		return response()->json($code);
    }

    public function getScanBarcode(Request $request){
        /* $code = $request->code;
        $precode = explode('-',$code)[0];

        $details = [];

        $menu = Menu::where('document_code','like',"$precode%")->first();

        if($menu){
            if($menu->table_name == 'good_receipts'){
                $datagr = GoodReceipt::where('code',$code)->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->first();
                if($datagr){
                    $top = 0;
                    $info = '';
                    foreach($datagr->goodReceiptDetail as $rowdetail){
                        if($top < $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term){
                            $top = $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term;
                        }
                        $info .= 'Diterima '.$rowdetail->qty.' '.$rowdetail->itemUnit->unit->code.' dari '.$rowdetail->purchaseOrderDetail->qty.' '.$rowdetail->itemUnit->unit->code;
                    }
                    foreach($datagr->goodReceiptDetail as $rowdetail){
                        if($rowdetail->balanceInvoice() > 0){
                            $details[] = [
                                'type'          => 'good_receipt_details',
                                'id'            => $rowdetail->id,
                                'name'          => $rowdetail->item->code.' - '.$rowdetail->item->name,
                                'qty_received'  => CustomHelper::formatConditionalQty($rowdetail->qty),
                                'qty_returned'  => CustomHelper::formatConditionalQty($rowdetail->qtyReturn()),
                                'qty_balance'   => CustomHelper::formatConditionalQty(($rowdetail->qty - $rowdetail->qtyReturn())),
                                'price'         => number_format($rowdetail->purchaseOrderDetail->price,2,',','.'),
                                'buy_unit'      => $rowdetail->itemUnit->unit->code,
                                'rawcode'       => $datagr->code,
                                'post_date'     => date('d/m/Y',strtotime($datagr->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($datagr->due_date)),
                                'total'         => number_format($rowdetail->total,2,',','.'),
                                'tax'           => number_format($rowdetail->tax,2,',','.'),
                                'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                                'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                                'info'          => $info,
                                'note'          => $rowdetail->note ? $rowdetail->note : '',
                                'note2'         => $rowdetail->note2 ? $rowdetail->note2 : '',
                                'top'           => $top,
                                'delivery_no'   => $datagr->delivery_no,
                                'purchase_no'   => $rowdetail->purchaseOrderDetail->purchaseOrder->code,
                                'percent_tax'   => $rowdetail->purchaseOrderDetail->percent_tax,
                                'percent_wtax'  => $rowdetail->purchaseOrderDetail->percent_wtax,
                                'include_tax'   => $rowdetail->purchaseOrderDetail->is_include_tax,
                                'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                                'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                                'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                                'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                                'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                                'project_id'    => $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : '',
                                'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                                'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                                'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                                'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                                'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                                'project_name'  => $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project->name : '-',
                                'qty_stock'     => CustomHelper::formatConditionalQty(($rowdetail->qty - $rowdetail->qtyReturn()) * $rowdetail->qty_conversion),
                                'unit_stock'    => $rowdetail->item->uomUnit->code,
                                'qty_conversion'=> $rowdetail->qty_conversion,
                                'received_date' => '',
                                'document_date' => '',
                                'tax_no'        => '',
                                'tax_cut_no'    => '',
                                'cut_date'      => '',
                                'spk_no'        => '',
                                'invoice_no'    => '',
                            ];
                        }
                    }
                }
            }elseif($menu->table_name == 'landed_costs'){
                $datalc = LandedCost::where('code',$code)->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->first();
                if($datalc){
                    if($datalc->balanceInvoice() > 0){
                        foreach($datalc->landedCostFeeDetail as $rowdetail){
                            $details[] = [
                                'type'          => $rowdetail->getTable(),
                                'id'            => $rowdetail->id,
                                'name'          => $rowdetail->landedCostFee->name,
                                'qty_received'  => 1,
                                'qty_returned'  => 0,
                                'qty_balance'   => 1,
                                'price'         => number_format($rowdetail->total,2,',','.'),
                                'buy_unit'      => '-',
                                'rawcode'       => $datalc->code,
                                'post_date'     => date('d/m/Y',strtotime($datalc->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($datalc->post_date)),
                                'total'         => number_format($rowdetail->total,2,',','.'),
                                'tax'           => number_format($rowdetail->tax,2,',','.'),
                                'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                                'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                                'info'          => $datalc->code,
                                'note'          => $datalc->note ? $datalc->note : '',
                                'note2'         => '',
                                'top'           => 0,
                                'delivery_no'   => $datalc->getListDeliveryNo(),
                                'purchase_no'   => $datalc->getGoodReceiptNo(),
                                'percent_tax'   => $rowdetail->percent_tax,
                                'percent_wtax'  => $rowdetail->percent_wtax,
                                'include_tax'   => $rowdetail->is_include_tax,
                                'place_id'      => '',
                                'line_id'       => '',
                                'machine_id'    => '',
                                'department_id' => '',
                                'warehouse_id'  => '',
                                'project_id'    => '',
                                'place_name'    => '-',
                                'line_name'     => '-',
                                'machine_name'  => '-',
                                'department_name' => '-',
                                'warehouse_name'=> '-',
                                'project_name'  => '-',
                                'qty_stock'     => 1,
                                'unit_stock'    => '-',
                                'qty_conversion'=> 1,
                                'received_date' => '',
                                'document_date' => '',
                                'tax_no'        => '',
                                'tax_cut_no'    => '',
                                'cut_date'      => '',
                                'spk_no'        => '',
                                'invoice_no'    => '',
                            ];
                        }
                    }
                }
            }elseif($menu->table_name == 'purchase_orders'){
                $datapo = PurchaseOrder::where('code',$code)->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->first();
                if($datapo){
                    foreach($datapo->purchaseOrderDetail as $rowdetail){
                        if($rowdetail->balanceInvoice() > 0){
                            $arrTotal = $rowdetail->getArrayTotal();
                            $details[] = [
                                'type'          => 'purchase_order_details',
                                'id'            => $rowdetail->id,
                                'name'          => $rowdetail->item_id ? $rowdetail->item->code.' - '.$rowdetail->item->name : $rowdetail->coa->code.' - '.$rowdetail->coa->name,
                                'qty_received'  => CustomHelper::formatConditionalQty($rowdetail->qty),
                                'qty_returned'  => 0,
                                'qty_balance'   => CustomHelper::formatConditionalQty($rowdetail->qty),
                                'price'         => number_format($arrTotal['total'] / $rowdetail->qty,2,',','.'),
                                'buy_unit'      => $rowdetail->item_id ? $rowdetail->itemUnit->unit->code : '-',
                                'rawcode'       => $datapo->code,
                                'post_date'     => date('d/m/Y',strtotime($datapo->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($datapo->post_date)),
                                'total'         => number_format($arrTotal['total'],2,',','.'),
                                'tax'           => number_format($arrTotal['tax'],2,',','.'),
                                'wtax'          => number_format($arrTotal['wtax'],2,',','.'),
                                'grandtotal'    => number_format($arrTotal['grandtotal'],2,',','.'),
                                'info'          => $rowdetail->note ? $rowdetail->note : '',
                                'note'          => $rowdetail->note ? $rowdetail->note : '',
                                'note2'         => $rowdetail->note2 ? $rowdetail->note2 : '',
                                'top'           => $datapo->payment_term,
                                'delivery_no'   => '-',
                                'purchase_no'   => $datapo->code,
                                'percent_tax'   => $rowdetail->percent_tax,
                                'percent_wtax'  => $rowdetail->percent_wtax,
                                'include_tax'   => $rowdetail->is_include_tax,
                                'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                                'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                                'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                                'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                                'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                                'project_id'    => $rowdetail->project_id ? $rowdetail->project_id : '',
                                'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                                'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                                'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                                'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                                'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                                'project_name'  => $rowdetail->project_id ? $rowdetail->project->name : '-',
                                'qty_stock'     => CustomHelper::formatConditionalQty($rowdetail->qty),
                                'unit_stock'    => '-',
                                'qty_conversion'=> 1,
                                'received_date' => $datapo->received_date ?? '',
                                'document_date' => $datapo->document_date ?? '',
                                'tax_no'        => $datapo->tax_no ?? '',
                                'tax_cut_no'    => $datapo->tax_cut_no ?? '',
                                'cut_date'      => $datapo->cut_date ?? '',
                                'spk_no'        => $datapo->spk_no ?? '',
                                'invoice_no'    => $datapo->invoice_no ?? '',
                            ];
                        }
                    }
                }
            }
        }

        $result['details'] = $details;
        $result['status'] = count($details) > 0 ? 200 : 500;

		return response()->json($result); */
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
                    'pyr_code'      => $row->listPaymentRequest(),
                    'note'          => $row->note,
                    'code'          => CustomHelper::encrypt($row->code),
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->balanceInvoice(),2,',','.'),
                ];
            }
        }

        $datafr = FundRequest::where('account_id',$request->id)->whereIn('status',['2','3'])->where('document_status','2')->where('type','1')->get();

        foreach($datafr as $row){
            $balanceInvoice = $row->balanceInvoice();
            if($balanceInvoice > 0){
                $details[] = [
                    'type'          => 'fund_requests',
                    'id'            => $row->id,
                    'code'          => $row->code,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($row->totalInvoice(),2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($balanceInvoice,2,',','.'),
                    'info'          => $row->note,
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $datapo = PurchaseOrder::whereIn('status',['2','3'])->where('inventory_type','2')->where('account_id',$request->id)->get();

        foreach($datapo as $row){
            $invoice = $row->totalInvoice();
            $code_sj = '';
            if($row->goodscale()->exists()){
                $code_sj = $row->goodScale->getSalesSuratJalan();
            }
            if(($row->grandtotal - $invoice) > 0){
                $details[] = [
                    'type'          => 'purchase_orders',
                    'id'            => $row->id,
                    'purchase_order'=> $code_sj,
                    'code'          => $row->code,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $datapo = PurchaseOrder::whereIn('status',['2','3'])->where('inventory_type','3')->where('account_id',$request->id)->get();

        foreach($datapo as $row){
            $code_sj = '';
            if($row->goodscale()->exists()){
                $code_sj = $row->goodScale->getSalesSuratJalan();
            }
            $invoice = $row->totalInvoice();
            if(($row->grandtotal - $invoice) > 0 && $row->goodScale->sjHasReturnDocument()){
                $details[] = [
                    'type'          => 'purchase_orders',
                    'id'            => $row->id,
                    'code'          => $row->code,
                    'purchase_order'=> $code_sj,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $datagr = GoodReceipt::whereIn('status',['2','3'])->where('account_id',$request->id)->get();

        foreach($datagr as $row){
            $invoice = $row->totalInvoice();
            if(round($row->total - $invoice,2) > 0){
                $details[] = [
                    'type'          => 'good_receipts',
                    'id'            => $row->id,
                    'code'          => $row->code.' - No. SJ : '.$row->delivery_no,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'purchase_order'=> $row->getPurchaseCode(),
                    'grandtotal'    => number_format($row->total,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currencyReference()->symbol.' '.number_format($row->total - $invoice,2,',','.'),
                    'info'          => $row->note,
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $datalc = LandedCost::where('account_id',$request->id)->whereIn('status',['2','3'])->get();

        foreach($datalc as $row){
            $invoice = $row->totalInvoice();
            if(($row->grandtotal - $invoice) > 0 && !$row->hasLandedCost()){
                $details[] = [
                    'type'          => 'landed_costs',
                    'id'            => $row->id,
                    'code'          => $row->code.' - No. SJ : '.$row->getListDeliveryNo(),
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'invoice'       => number_format($invoice,2,',','.'),
                    'balance'       => $row->currency->symbol.' '.number_format($row->grandtotal - $invoice,2,',','.'),
                    'info'          => $row->note,
                    'list_item'     => $row->getListItem(),
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
                        'rawcode'       => $datadp->note,
                        'pyr_code'      => $datadp->listPaymentRequest(),
                        'code'          => CustomHelper::encrypt($datadp->code),
                        'post_date'     => date('d/m/Y',strtotime($datadp->post_date)),
                        'total'         => number_format($datadp->total,2,',','.'),
                        'grandtotal'    => number_format($datadp->grandtotal,2,',','.'),
                        'balance'       => number_format($datadp->balanceInvoice(),2,',','.'),
                    ];
                }
            }elseif($row == 'purchase_orders'){
                $datapo = PurchaseOrder::find(intval($request->arr_id[$key]));
                foreach($datapo->purchaseOrderDetail as $rowdetail){
                    if($rowdetail->balanceInvoice() > 0 || $rowdetail->balanceInvoice() < 0){
                        $arrTotal = $rowdetail->getArrayTotal();
                        $details[] = [
                            'type'          => 'purchase_order_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->item_id ? $rowdetail->item->code.' - '.$rowdetail->item->name : $rowdetail->coa->code.' - '.$rowdetail->coa->name,
                            'qty_received'  => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'qty_returned'  => 0,
                            'qty_balance'   => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'price'         => number_format($arrTotal['total'] / $rowdetail->qty,2,',','.'),
                            'raw_price'     => number_format($arrTotal['total'] / $rowdetail->qty,5,',','.'),
                            'buy_unit'      => $rowdetail->item_id ? $rowdetail->itemUnit->unit->code : '-',
                            'rawcode'       => $datapo->code,
                            'post_date'     => date('d/m/Y',strtotime($datapo->post_date)),
                            'total'         => number_format($arrTotal['total'],2,',','.'),
                            'tax'           => number_format($arrTotal['tax'],2,',','.'),
                            'wtax'          => number_format($arrTotal['wtax'],2,',','.'),
                            'grandtotal'    => number_format($arrTotal['grandtotal'],2,',','.'),
                            'info'          => $rowdetail->note ? $rowdetail->note : '',
                            'note'          => $rowdetail->note ? $rowdetail->note : '',
                            'note2'         => $rowdetail->note2 ? $rowdetail->note2 : '',
                            'top'           => $datapo->payment_term,
                            'delivery_no'   => '-',
                            'purchase_no'   => $datapo->code,
                            'percent_tax'   => $rowdetail->percent_tax,
                            'percent_wtax'  => $rowdetail->percent_wtax,
                            'include_tax'   => $rowdetail->is_include_tax,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                            'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                            'project_id'    => $rowdetail->project_id ? $rowdetail->project_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                            'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                            'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                            'project_name'  => $rowdetail->project_id ? $rowdetail->project->name : '-',
                            'qty_stock'     => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'unit_stock'    => '-',
                            'qty_conversion'=> 1,
                            'received_date' => $datapo->received_date ?? '',
                            'due_date'      => $datapo->due_date ?? '',
                            'document_date' => $datapo->document_date ?? '',
                            'document_no'   => $datapo->document_no ?? '',
                            'tax_no'        => $datapo->tax_no ?? '',
                            'tax_cut_no'    => $datapo->tax_cut_no ?? '',
                            'cut_date'      => $datapo->cut_date ?? '',
                            'spk_no'        => $datapo->spk_no ?? '',
                            'invoice_no'    => $datapo->invoice_no ?? '',
                            'header_note'   => $datapo->note,
                            'currency_rate' => number_format($datapo->currency_rate,2,',','.'),
                            'currency_id'   => $datapo->currency_id,
                            'rounding'      => number_format($datapo->rounding,2,',','.'),
                            'is_expedition' => $datapo->goodScale()->exists() ? '1' : '',
                        ];
                    }
                }
            }elseif($row == 'fund_requests'){
                $datafr = FundRequest::find(intval($request->arr_id[$key]));
                foreach($datafr->fundRequestDetail as $rowdetail){
                    if($rowdetail->balanceInvoice() > 0 || $rowdetail->balanceInvoice() < 0){
                        $details[] = [
                            'type'          => 'fund_request_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->note,
                            'qty_received'  => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'qty_returned'  => 0,
                            'qty_balance'   => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'price'         => number_format($rowdetail->price,2,',','.'),
                            'raw_price'     => number_format($rowdetail->price,5,',','.'),
                            'buy_unit'      => '-',
                            'rawcode'       => $datafr->code,
                            'post_date'     => date('d/m/Y',strtotime($datafr->post_date)),
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'info'          => $rowdetail->note ? $rowdetail->note : '',
                            'note'          => $rowdetail->note ? $rowdetail->note : '',
                            'note2'         => $rowdetail->note ? $rowdetail->note : '',
                            'top'           => 0,
                            'delivery_no'   => '-',
                            'purchase_no'   => $datafr->code,
                            'percent_tax'   => $rowdetail->percent_tax,
                            'percent_wtax'  => $rowdetail->percent_wtax,
                            'include_tax'   => $rowdetail->is_include_tax,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->division_id ? $rowdetail->division_id : '',
                            'warehouse_id'  => '',
                            'project_id'    => $rowdetail->project_id ? $rowdetail->project_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                            'department_name' => $rowdetail->division_id ? $rowdetail->division->name : '-',
                            'warehouse_name'=> '-',
                            'project_name'  => $rowdetail->project_id ? $rowdetail->project->name : '-',
                            'qty_stock'     => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'unit_stock'    => '-',
                            'qty_conversion'=> 1,
                            'received_date' => '',
                            'due_date'      => $datafr->required_date ?? '',
                            'document_date' => $datafr->document_date ?? '',
                            'document_no'   => $datafr->document_no ?? '',
                            'tax_no'        => $datafr->tax_no ?? '',
                            'tax_cut_no'    => $datafr->tax_cut_no ?? '',
                            'cut_date'      => $datafr->cut_date ?? '',
                            'spk_no'        => $datafr->spk_no ?? '',
                            'invoice_no'    => $datafr->invoice_no ?? '',
                            'header_note'   => $datafr->note,
                            'currency_rate' => number_format($datafr->currency_rate,2,',','.'),
                            'currency_id'   => $datafr->currency_id,
                            'rounding'      => 0,
                            'is_expedition' => '',
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
                    $info .= 'Diterima '.$rowdetail->qty.' '.$rowdetail->itemUnit->unit->code.' dari '.$rowdetail->purchaseOrderDetail->qty.' '.$rowdetail->itemUnit->unit->code;
                }
                $currency_rate = $datagr->latestCurrencyRate();
                foreach($datagr->goodReceiptDetail as $rowdetail){
                    if($rowdetail->balanceTotalInvoice() > 0){
                        $price = round($rowdetail->total / $rowdetail->qty,5);
                        $details[] = [
                            'type'          => 'good_receipt_details',
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->item->code.' - '.$rowdetail->item->name,
                            'qty_received'  => CustomHelper::formatConditionalQty($rowdetail->qty),
                            'qty_returned'  => CustomHelper::formatConditionalQty($rowdetail->qtyReturn()),
                            'qty_balance'   => CustomHelper::formatConditionalQty($rowdetail->balanceQtyInvoice() - $rowdetail->qtyReturn()),
                            'price'         => number_format($price,2,',','.'),
                            'raw_price'     => number_format($price,5,',','.'),
                            'buy_unit'      => $rowdetail->itemUnit->unit->code,
                            'rawcode'       => $datagr->code,
                            'post_date'     => date('d/m/Y',strtotime($datagr->post_date)),
                            'due_date'      => '',
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'info'          => $info,
                            'note'          => $rowdetail->note ? $rowdetail->note : '',
                            'note2'         => $rowdetail->note2 ? $rowdetail->note2 : '',
                            'top'           => $top,
                            'delivery_no'   => $datagr->delivery_no,
                            'purchase_no'   => $rowdetail->purchaseOrderDetail->purchaseOrder->code,
                            'percent_tax'   => $rowdetail->purchaseOrderDetail->percent_tax,
                            'percent_wtax'  => $rowdetail->purchaseOrderDetail->percent_wtax,
                            'include_tax'   => $rowdetail->purchaseOrderDetail->is_include_tax,
                            'place_id'      => $rowdetail->place_id ? $rowdetail->place_id : '',
                            'line_id'       => $rowdetail->line_id ? $rowdetail->line_id : '',
                            'machine_id'    => $rowdetail->machine_id ? $rowdetail->machine_id : '',
                            'department_id' => $rowdetail->department_id ? $rowdetail->department_id : '',
                            'warehouse_id'  => $rowdetail->warehouse_id ? $rowdetail->warehouse_id : '',
                            'project_id'    => $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : '',
                            'place_name'    => $rowdetail->place_id ? $rowdetail->place->code : '-',
                            'line_name'     => $rowdetail->line_id ? $rowdetail->line->name : '-',
                            'machine_name'  => $rowdetail->machine_id ? $rowdetail->machine->name : '-',
                            'department_name' => $rowdetail->department_id ? $rowdetail->department->name : '-',
                            'warehouse_name'=> $rowdetail->warehouse_id ? $rowdetail->warehouse->name : '-',
                            'project_name'  => $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project->name : '-',
                            'qty_stock'     => number_format(($rowdetail->qty - $rowdetail->qtyReturn()) * $rowdetail->qty_conversion),
                            'unit_stock'    => $rowdetail->item->uomUnit->code,
                            'qty_conversion'=> $rowdetail->qty_conversion,
                            'received_date' => '',
                            'document_date' => '',
                            'document_no'   => '',
                            'tax_no'        => '',
                            'tax_cut_no'    => '',
                            'cut_date'      => '',
                            'spk_no'        => '',
                            'invoice_no'    => '',
                            'header_note'   => $rowdetail->purchaseOrderDetail->purchaseOrder->note,
                            'currency_rate' => number_format($currency_rate,2,',','.'),
                            'currency_id'   => $rowdetail->purchaseOrderDetail->purchaseOrder->currency_id,
                            'rounding'      => number_format($rowdetail->purchaseOrderDetail->purchaseOrder->rounding,2,',','.'),
                            'is_expedition' => '',
                        ];
                    }
                }
            }elseif($row == 'landed_costs'){
                $datalc = LandedCost::find(intval($request->arr_id[$key]));

                if($datalc->balanceInvoice() > 0){
                    foreach($datalc->landedCostFeeDetail as $rowdetail){
                        $details[] = [
                            'type'          => $rowdetail->getTable(),
                            'id'            => $rowdetail->id,
                            'name'          => $rowdetail->landedCostFee->name,
                            'qty_received'  => 1,
                            'qty_returned'  => 0,
                            'qty_balance'   => 1,
                            'price'         => number_format($rowdetail->total,2,',','.'),
                            'raw_price'     => number_format($rowdetail->total,5,',','.'),
                            'buy_unit'      => '-',
                            'rawcode'       => $datalc->code,
                            'post_date'     => date('d/m/Y',strtotime($datalc->post_date)),
                            'due_date'      => date('d/m/Y',strtotime($datalc->post_date)),
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'info'          => $datalc->code,
                            'note'          => $datalc->note ? $datalc->note : '',
                            'note2'         => '',
                            'top'           => 0,
                            'delivery_no'   => $datalc->getListDeliveryNo(),
                            'purchase_no'   => $datalc->getGoodReceiptNo(),
                            'percent_tax'   => $rowdetail->percent_tax,
                            'percent_wtax'  => $rowdetail->percent_wtax,
                            'include_tax'   => $rowdetail->is_include_tax,
                            'place_id'      => '',
                            'line_id'       => '',
                            'machine_id'    => '',
                            'department_id' => '',
                            'warehouse_id'  => '',
                            'project_id'    => '',
                            'place_name'    => '-',
                            'line_name'     => '-',
                            'machine_name'  => '-',
                            'department_name' => '-',
                            'warehouse_name'=> '-',
                            'project_name'  => '-',
                            'qty_stock'     => 1,
                            'unit_stock'    => '-',
                            'qty_conversion'=> 1,
                            'received_date' => '',
                            'document_date' => '',
                            'document_no'   => '',
                            'tax_no'        => '',
                            'tax_cut_no'    => '',
                            'cut_date'      => '',
                            'spk_no'        => '',
                            'invoice_no'    => '',
                            'header_note'   => $datalc->note,
                            'currency_rate' => number_format($datalc->currency_rate,2,',','.'),
                            'currency_id'   => $datalc->currency_id,
                            'rounding'      => 0,
                            'is_expedition' => '',
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
            'currency_id',
            'currency_rate',
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
            'wtax',
            'rounding',
            'grandtotal',
            'downpayment',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseInvoice::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();

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

                if(!$request->modedata){

                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));

                }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
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

                if(!$request->modedata){

                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));

                }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';
                $nodis = '';
                if($val->isOpenPeriod()){
                    $dis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }else{
                    $nodis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    $val->account->name ?? '',
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->received_date)),
                    date('d/m/Y',strtotime($val->due_date)),
                    date('d/m/Y',strtotime($val->document_date)),
                    $val->currency->name??'-',
                    number_format($val->currency_rate,2,',','.'),
                    $val->type(),
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->note,
                    $val->tax_no,
                    $val->tax_cut_no,
                    date('d/m/Y',strtotime($val->cut_date)),
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->percent_discount,2,',','.'),
                    number_format($val->nominal_discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->downpayment,2,',','.'),
                    number_format($val->balance,2,',','.'),
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'SYSTEM' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'SYSTEM' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? 'SYSTEM' : 'SYSTEM'
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1  btn-small btn-flat waves-effect waves-light purple darken-2 white-text" data-popup="tooltip" title="Cancel" onclick="cancelStatus(`' . CustomHelper::encrypt($val->code) . '`)" '.$nodis.'><i class="material-icons dp48">cancel</i></button>
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
        if($request->type_detail == '1'){
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
               /*  'code'			            => $request->temp ? ['required', Rule::unique('purchase_invoices', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:purchase_invoices,code',
                 */'account_id' 			    => 'required',
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
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                /* 'code.string'                       => 'Kode harus dalam bentuk string.',
                'code.min'                          => 'Kode harus minimal 18 karakter.',
                'code.unique'                       => 'Kode telah dipakai', */
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
        }elseif($request->type_detail == '2'){
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'account_id' 			    => 'required',
                'type'                      => 'required',
                'company_id'                => 'required',
                'post_date'                 => 'required',
                'received_date'             => 'required',
                'due_date'                  => 'required',
                'document_date'             => 'required',
                'arr_multi_coa'             => 'required|array',
            ], [
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
                'type.required'                     => 'Tipe invoice tidak boleh kosong',
                'company_id.required'               => 'Perusahaan tidak boleh kosong.',
                'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
                'received_date.required'            => 'Tanggal terima tidak boleh kosong.',
                'due_date.required'                 => 'Tanggal tenggat tidak boleh kosong.',
                'document_date.required'            => 'Tanggal dokumen tidak boleh kosong.',
                'arr_multi_coa.required'            => 'Coa tidak boleh kosong.',
                'arr_multi_coa.array'               => 'Coa harus dalam bentuk array.',
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if(!CustomHelper::checkLockAcc($request->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

            $total = 0;
            $tax = 0;
            $wtax = str_replace(',','.',str_replace('.','',$request->wtax));
            $grandtotal = 0;
            $balance = 0;
            $downpayment = str_replace(',','.',str_replace('.','',$request->downpayment));
            $rounding = str_replace(',','.',str_replace('.','',$request->rounding));

            if ($wtax>0 && strlen($request->tax_cut_no)<5)
            {
                return response()->json([
                    'status'  => 500,
                    'message' => 'No Bukti Potong Belum Diisi'
                ]);
            }



            if($request->arr_total){
                foreach($request->arr_total as $key => $row){
                    $total += str_replace(',','.',str_replace('.','',$row));
                    $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                    $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
                }
            }

            if ($tax>0 && strlen($request->tax_no)<5)
            {
                return response()->json([
                    'status'  => 500,
                    'message' => 'No Faktur Pajak Belum Diisi'
                ]);
            }

            if($request->arr_multi_total){
                foreach($request->arr_multi_total as $key => $row){
                    $total += floatval($row);
                    $tax += floatval($request->arr_multi_ppn[$key]);
                }
                $grandtotal = $total + $tax - $wtax;
            }

            $grandtotal += $rounding;

            $balance = $grandtotal - $downpayment;

            if($request->temp){
                /* DB::beginTransaction();
                try { */
                    $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->temp))->first();

                    $approved = false;
                    $revised = false;

                    if(!CustomHelper::checkLockAcc($query->post_date) && !$query->cancelDocument()->exists()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan. Atau silahkan buat cancel dokumen.'
                        ]);
                    }

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

                    if(in_array($query->status,['1','2','6'])){

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
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->total = round($total,2);
                        $query->tax = round($tax,2);
                        $query->wtax = round($wtax,2);
                        $query->rounding = round($rounding,2);
                        $query->grandtotal = round($grandtotal,2);
                        $query->downpayment = round($downpayment,2);
                        $query->balance = round($balance,2);
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->document_no = $request->document_no;
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

                        CustomHelper::removeApproval($query->getTable(),$query->id);
                        if(!$query->cancelDocument()->exists()){
                            if($query->journal()->exists()){
                                CustomHelper::removeJournal($query->getTable(),$query->id);
                            }
                        }

                        /* DB::commit(); */
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                /* }catch(\Exception $e){
                    DB::rollback();
                } */
            }else{
                /* DB::beginTransaction();
                try { */
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=PurchaseInvoice::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = PurchaseInvoice::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'received_date'             => $request->received_date,
                        'due_date'                  => $request->due_date,
                        'document_date'             => $request->document_date,
                        'type'                      => $request->type,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'total'                     => round($total,2),
                        'tax'                       => round($tax,2),
                        'wtax'                      => round($wtax,2),
                        'rounding'                  => round($rounding,2),
                        'grandtotal'                => round($grandtotal,2),
                        'downpayment'               => round($downpayment,2),
                        'balance'                   => round($balance,2),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_invoices') : NULL,
                        'status'                    => '1',
                        'document_no'               => $request->document_no,
                        'tax_no'                    => $request->tax_no,
                        'tax_cut_no'                => $request->tax_cut_no,
                        'cut_date'                  => $request->cut_date,
                        'spk_no'                    => $request->spk_no,
                        'invoice_no'                => $request->invoice_no
                    ]);

                    /* DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                } */
            }

            if($query) {
                /* DB::beginTransaction();
                try { */
                    if($request->type_detail == '1'){
                        if($request->arr_type){

                            foreach($request->arr_type as $key => $row){
                                PurchaseInvoiceDetail::create([
                                    'purchase_invoice_id'   => $query->id,
                                    'lookable_type'         => $row,
                                    'lookable_id'           => $request->arr_code[$key],
                                    'fund_request_detail_id'=> $request->arr_frd_id[$key] ?? NULL,
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
                                    'project_id'            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                                ]);
                            }

                        }
                    }elseif($request->type_detail == '2'){
                        foreach($request->arr_multi_coa as $key => $row){
                            $coa = Coa::where('code',explode('-',$row)[0])->where('company_id',$request->company_id)->first();
                            $tax = $request->arr_multi_tax_id[$key] ? Tax::where('code',explode('-',$request->arr_multi_tax_id[$key])[0])->first() : '';
                            $wtax = $request->arr_multi_wtax_id[$key] ? Tax::where('code',explode('|',$request->arr_multi_wtax_id[$key])[0])->first() : '';
                            $place = $request->arr_multi_place[$key] ? Place::where('code',explode('-',$request->arr_multi_place[$key])[0])->first() : '';
                            $line = $request->arr_multi_line[$key] ? Line::where('code',$request->arr_multi_line[$key])->first() : '';
                            $machine = $request->arr_multi_machine[$key] ? Machine::where('code',explode('|',$request->arr_multi_machine[$key])[1])->first() : '';
                            $department = $request->arr_multi_department[$key] ? Department::where('code',explode('|',$request->arr_multi_department[$key])[0])->first() : '';
                            $warehouse = $request->arr_multi_warehouse[$key] ? Warehouse::where('code',explode('|',$request->arr_multi_warehouse[$key])[0])->first() : '';
                            $project = $request->arr_multi_project[$key] ? Project::where('code',explode('|',$request->arr_multi_project[$key])[0])->first() : '';
                            PurchaseInvoiceDetail::create([
                                'purchase_invoice_id'   => $query->id,
                                'lookable_type'         => 'coas',
                                'lookable_id'           => $coa->id,
                                'qty'                   => $request->arr_multi_qty[$key],
                                'price'                 => $request->arr_multi_price[$key],
                                'total'                 => $request->arr_multi_total[$key],
                                'tax'                   => $request->arr_multi_ppn[$key],
                                'tax_id'                => $tax ? $tax->id : NULL,
                                'is_include_tax'        => '0',
                                'wtax_id'               => $wtax ? $wtax->id : NULL,
                                'wtax'                  => $request->arr_multi_pph[$key],
                                'grandtotal'            => $request->arr_multi_grandtotal[$key],
                                'note'                  => $request->arr_multi_note_1[$key],
                                'note2'                 => $request->arr_multi_note_2[$key],
                                'place_id'              => $place ? $place->id : NULL,
                                'line_id'               => $line ? $line->id : NULL,
                                'machine_id'            => $machine ? $machine->id : NULL,
                                'department_id'         => $department ? $department->id : NULL,
                                'warehouse_id'          => $warehouse ? $warehouse->id : NULL,
                                'project_id'            => $project ? $project->id : NULL,
                            ]);
                        }
                    }

                    if($request->arr_dp_code){
                        foreach($request->arr_dp_code as $key => $row){
                            $apdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($row))->first();
                            PurchaseInvoiceDp::create([
                                'purchase_invoice_id'       => $query->id,
                                'purchase_down_payment_id'  => $apdp->id,
                                'nominal'                   => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                            ]);
                            if($apdp->balanceInvoice() <= 0){
                                $apdp->update([
                                    'balance_status'	=> '1',
                                ]);
                            }
                        }
                    }

                    /* DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                } */

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
        $x="";
        $canceled = "";
        if ($data->cancelDocument()->exists()) {
            $canceled .= '<span style="color: red;">|| Tanggal Cancel: ' . $data->cancelDocument->post_date .  ' || Void User: ' . $data->cancelDocument->user->name.' || Note:' . $data->void_note.'</span>' ;
        }
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">
                    <div class="col s12">'.$canceled.'</div>
                    <div class="col s12">'.$data->code.$x.'</div>
                    <div class="col s12"><table style="min-width:100%;max-width:100%;">
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
        $totals=0;
        $totalppn=0;
        $totalpph=0;
        $totalgrandtotal=0;
        if(count($data->purchaseInvoiceDetail) > 0){
            foreach($data->purchaseInvoiceDetail as $key => $row){
                $totals+=$row->total;
                $totalppn+=$row->tax;
                $totalpph+=$row->wtax;
                $totalgrandtotal+=$row->grandtotal;
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
            $string .= '<tr>
                    <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="4"> Total </td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalppn, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpph, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
                </tr>
            ';
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
                                <th class="center-align" colspan="5">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Tanggal</th>
                            </tr>
                        </thead><tbody>';

        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
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
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->user->name.' - Tanggal Dipakai: '.$data->used->created_at.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';

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
        $lastSegment = request()->segment(count(request()->segments())-2);

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $pr = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();

        if($pr){

            $pdf = PrintHelper::print($pr,'Print A/P Invoice','a4','portrait','admin.print.purchase.invoice_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));


            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;


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
        $pi['currency_rate'] = number_format($pi->currency_rate,2,',','.');
        $pi['top'] = $pi->top();
        // if(!CustomHelper::checkLockAcc($pi->post_date)){
        //     return response()->json([
        //         'status'  => 500,
        //         'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
        //     ]);
        // }
        $downpayments = [];

        foreach($pi->purchaseInvoiceDp as $row){
            $downpayments[] = [
                'rawcode'       => $row->purchaseDownPayment->code,
                'pyr_code'      => $row->purchaseDownPayment->listPaymentRequest(),
                'code'          => CustomHelper::encrypt($row->purchaseDownPayment->code),
                'post_date'     => date('d/m/Y',strtotime($row->purchaseDownPayment->post_date)),
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
                'qty_balance'   => CustomHelper::formatConditionalQty($row->qty),
                'price'         => number_format($row->price,2,',','.'),
                'price_raw'     => number_format($row->price,5,',','.'),
                'buy_unit'      => $row->getUnitCode(),
                'rawcode'       => $row->getHeaderCode(),
                'post_date'     => $row->getPostDate(),
                'due_date'      => $row->getDueDate(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'info'          => $row->note ? $row->note : '',
                'note'          => $row->note ? $row->note : '',
                'note2'         => $row->note2 ? $row->note2 : '',
                'top'           => $row->getTop(),
                'delivery_no'   => $row->getDeliveryCode(),
                'purchase_no'   => $row->getPurchaseCode(),
                'percent_tax'   => $row->percent_tax,
                'percent_wtax'  => $row->percent_wtax,
                'include_tax'   => $row->is_include_tax,
                'place_id'      => $row->place_id ? $row->place_id : '',
                'line_id'       => $row->line_id ? $row->line_id : '',
                'machine_id'    => $row->machine_id ? $row->machine_id : '',
                'department_id' => $row->department_id ? $row->department_id : '',
                'warehouse_id'  => $row->warehouse_id ? $row->warehouse_id : '',
                'project_id'    => $row->project_id ? $row->project_id : '',
                'place_name'    => $row->place_id ? $row->place->code : '-',
                'line_name'     => $row->line_id ? $row->line->name : '-',
                'machine_name'  => $row->machine_id ? $row->machine->name : '-',
                'department_name'=> $row->department_id ? $row->department->name : '-',
                'warehouse_name'=> $row->warehouse_id ? $row->warehouse->name : '-',
                'project_name'  => $row->project_id ? $row->project->name : '-',
                'qty_stock'     => number_format($row->getQtyStock()),
                'unit_stock'    => $row->getUnitStock(),
                'qty_conversion'=> $row->getQtyConversion(),
                'frd_id'        => $row->fund_request_detail_id ?? '',
            ];
        }

        $pi['details'] = $arr;
        $pi['downpayments'] = $downpayments;

		return response()->json($pi);
    }

    public function voidStatus(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

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

                $query->updateRootDocumentStatusProcess();

                if($query->downpayment > 0){
                    foreach($query->purchaseInvoiceDp as $row){
                        if($row->purchaseDownPayment->balanceInvoice() > 0){
                            $row->purchaseDownPayment->update([
                                'balance_status'	=> NULL,
                            ]);
                        }
                    }
                }

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

    public function cancelStatus(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            if(!CustomHelper::checkLockAcc($request->cancel_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada tanggal cancel void telah ditutup oleh Akunting.'
                ]);
            }

            if(in_array($query->status,['4','5','8'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocumentExceptAdjustRate()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Payment Request.'
                ];
            }else{

                CustomHelper::removeApproval($query->getTable(),$query->id);
                CustomHelper::addDeposit($query->account_id,$query->downpayment);

                $query->update([
                    'status'    => '8',
                    'done_id'   => session('bo_id'),
                ]);

                $cd = CancelDocument::create([
                    'code'          => CancelDocument::generateCode('CAPN',substr($query->code,7,2),$request->cancel_date),
                    'user_id'       => session('bo_id'),
                    'post_date'     => $request->cancel_date,
                    'lookable_type' => $query->getTable(),
                    'lookable_id'   => $query->id,
                ]);

                CustomHelper::cancelJournal($cd,$request->cancel_date);

                activity()
                    ->performedOn(new PurchaseInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void cancel the purchase invoice data');

                CustomHelper::sendNotification($query->getTable(),$query->id,'AP Invoice No. '.$query->code.' telah ditutup dengan tombol cancel void.','AP Invoice No. '.$query->code.' telah ditutup dengan tombol cancel void.',$query->user_id);

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

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            if($query->downpayment > 0){
                foreach($query->purchaseInvoiceDp as $row){
                    if($row->purchaseDownPayment->balanceInvoice() > 0){
                        $row->purchaseDownPayment->update([
                            'balance_status'	=> NULL,
                        ]);
                    }
                }
            }

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
                    $pdf = PrintHelper::print($pr,'Print A/P Invoice','a4','portrait','admin.print.purchase.invoice_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }

            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
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
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);

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
                    $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
                    $content = $pdf->download()->getOriginalContent();

                    $randomString = Str::random(10);


                    $filePath = 'public/pdf/' . $randomString . '.pdf';


                    Storage::put($filePath, $content);

                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;

                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
                $content = $pdf->download()->getOriginalContent();

                $randomString = Str::random(10);


                $filePath = 'public/pdf/' . $randomString . '.pdf';


                Storage::put($filePath, $content);

                $document_po = asset(Storage::url($filePath));
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
                        $lastSegment = $request->lastsegment;

                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);

                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded;
                        $query = PurchaseInvoice::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print A/P Invoice','a4','portrait','admin.print.purchase.invoice_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;

                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $document_po = PrintHelper::savePrint($result);

                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                            $pdf = PrintHelper::print($query,'Print A/P Invoice','a4','portrait','admin.print.purchase.invoice_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;

                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $document_po = PrintHelper::savePrint($result);

                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCostFeeDetail::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($request) {
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
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportPurchaseInvoice($post_date,$end_date,$mode), 'purchase_invoice'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();



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
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($query->code),
            ];
            $data_go_chart[] = $data_invoice;


            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_invoice',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2;
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
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += round($row->nominal,2);
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += round($row->nominal,2);
                }

                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td class="center-align">'.($row->note ? $row->note : '').'</td>
                    <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }

            if($query->cancelDocument()->exists()){
                foreach($query->cancelDocument->journal->journalDetail()->where(function($query){
                    $query->whereHas('coa',function($query){
                        $query->orderBy('code');
                    })
                    ->orderBy('type');
                })->get() as $key => $row){
                    if($row->type == '1'){
                        $total_debit_asli += $row->nominal_fc;
                        $total_debit_konversi += $row->nominal;
                    }
                    if($row->type == '2'){
                        $total_kredit_asli += $row->nominal_fc;
                        $total_kredit_konversi += $row->nominal;
                    }
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                        <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                        <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                        <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                        <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                        <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                        <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                        <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                        <td class="center-align">'.($row->note ? $row->note : '').'</td>
                        <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                        <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                    </tr>';
                }
            }

            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string;
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ];
        }
        return response()->json($response);
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplatePurchaseInvoice(), 'format_copas_multi_ap_invoice'.uniqid().'.xlsx');
    }

    public function getOutstanding(Request $request){
		return Excel::download(new ExportOutstandingInvoice(), 'outstanding_purchase_invoice_'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);

                activity()
                        ->performedOn(new PurchaseInvoice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Purchase Invoice data');

                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';;
        $company = $request->company ? $request->company : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $supplier = $request->supplier? $request->supplier : '';

        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';

		return Excel::download(new ExportPurchaseInvoiceTransactionPage($search,$status,$company,$type_pay,$supplier,$end_date,$start_date,$modedata), 'purchase_invoice'.uniqid().'.xlsx');
    }
}
