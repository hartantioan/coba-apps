<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Jobs\ResetCogs;
use App\Jobs\ResetStock;
use App\Models\Coa;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\GoodIssueRequest;
use App\Models\InventoryTransferOut;
use App\Models\CancelDocument;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\Item;
use App\Models\Line;
use App\Models\LandedCost;
use App\Models\Machine;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;

use App\Models\InventoryTransferIn;
use App\Models\LandedCostFee;
use App\Exports\ExportOutstandingLC;
use App\Exports\ExportLandedCostTransactionPage;
use App\Models\Place;
;
use App\Models\Tax;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Menu;
use App\Models\LandedCostDetail;
use App\Models\LandedCostFeeDetail;
use App\Models\PurchaseOrder;
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportLandedCost;
use App\Models\MenuUser;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\UsedData;
class LandedCostController extends Controller
{
    protected $dataplaces, $datawarehouses, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Landed Cost',
            'content'       => 'admin.purchase.landed_cost',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'landedcostfee' => LandedCostFee::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       =>  $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = LandedCost::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getAccountData(Request $request){

        $account = NULL;

        if($request->id){
            
            $account = User::find($request->id);

            $goods_receipt = [];
            $landed_cost = [];

            $datagr = GoodReceipt::whereIn('status',['2','3'])->where('account_id',$request->id)->get();
            
            foreach($datagr as $row){
                if(!$row->used()->exists()){
                    $goods_receipt[] = [
                        'id'            => $row->id,
                        'code'          => $row->code,
                        'delivery_no'   => $row->delivery_no,
                        'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'note'          => $row->note ? $row->note : '',
                        'landed_cost'   => $row->getLandedCostList()
                    ];
                }
            }
        
            $datalc = LandedCost::where('supplier_id',$request->id)->whereIn('status',['2','3'])->get();

            foreach($datalc as $row){
                if(!$row->used()->exists() && !$row->hasChildDocument()){
                    $landed_cost[] = [
                        'id'            => $row->id,
                        'code'          => $row->code,
                        'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'note'          => $row->note ? $row->note : '',
                        'landed_cost'   => $row->getLandedCostList()
                    ];
                }
            }

            $account['goods_receipt'] = $goods_receipt;
            $account['landed_cost'] = $landed_cost;

        }else{
            $inventory_transfer_in = [];

            $dataiti = InventoryTransferIn::whereHas('inventoryTransferOut',function($query){
                    $query->whereIn('place_to',$this->dataplaces)
                        ->whereIn('warehouse_to',$this->datawarehouses)
                        ->whereRaw('place_from <> place_to');
                })
                ->whereIn('status',['2','3'])
                ->get();

            foreach($dataiti as $row){
                if(!$row->used()->exists()){
                    $inventory_transfer_in[] = [
                        'id'                => $row->id,
                        'code_iti'          => $row->code,
                        'code_ito'          => $row->inventoryTransferOut->code,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'note'              => $row->note ? $row->note : '',
                    ];
                }
            }

            $account['inventory_transfer_in'] = $inventory_transfer_in;
        }

        return response()->json($account);
    }

    public function getGoodReceipt(Request $request){
        $arr_main = [];

        if($request->arr_gr_id){
            foreach($request->arr_gr_id as $row){
                $data = GoodReceipt::find(intval($row));
                $data['lookable_type'] = $data->getTable();
                $data['from_address'] = $data->account->city()->exists() ? $data->account->city->name.' - '.$data->account->subdistrict->name : '';
                $data['subdistrict_from_id'] = $data->account->subdistrict_id;
                $data['code'] = $data->code.' - SJ : '.$data->delivery_no;
            
                if($data->used()->exists()){
                    $data['status'] = '500';
                    $data['message'] = 'Goods Receipt '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
                }else{
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Landed Cost');
        
                    $details = [];
                    
                    foreach($data->goodReceiptDetail as $row){
                        $coa = Coa::where('code','500.02.01.13.01')->where('company_id',$row->place->company_id)->where('status','1')->first();
                        $details[] = [
                            'item_id'                   => $row->item_id,
                            'item_name'                 => $row->item->code.' - '.$row->item->name,
                            'qty'                       => CustomHelper::formatConditionalQty($row->qtyConvert()),
                            'totalrow'                  => $row->getRowTotal(),
                            'qtyRaw'                    => $row->qtyConvert(),
                            'unit'                      => $row->item->uomUnit->code,
                            'place_name'                => $row->place->code,
                            'department_name'           => $row->department_id ? $row->department->name : '-',
                            'warehouse_name'            => $row->warehouse->name,
                            'place_id'                  => $row->place_id,
                            'department_id'             => $row->department_id ? $row->department_id : '',
                            'warehouse_id'              => $row->warehouse_id,
                            'line_name'                 => $row->line_id ? $row->line->name : '-',
                            'line_id'                   => $row->line_id ? $row->line_id : '',
                            'machine_name'              => $row->machine_id ? $row->machine->name : '-',
                            'machine_id'                => $row->machine_id ? $row->machine_id : '',
                            'project_name'              => $row->purchaseOrderDetail->purchaseRequestDetail()->exists() ? ($row->purchaseOrderDetail->purchaseRequestDetail->project()->exists() ? $row->purchaseOrderDetail->purchaseRequestDetail->project->name : '') : '-',
                            'project_id'              => $row->purchaseOrderDetail->purchaseRequestDetail()->exists() ? ($row->purchaseOrderDetail->purchaseRequestDetail->project()->exists() ? $row->purchaseOrderDetail->purchaseRequestDetail->project_id : '') : '',
                            'lookable_id'               => $row->id,
                            'lookable_type'             => $row->getTable(),
                            'stock'                     => $row->item->getStockPlace($row->place_id),
                            'coa_id'                    => $coa ? $coa->id : '',
                            'coa_name'                  => $coa ? $coa->name : '',
                        ];

                        $data['to_address'] = $row->place->city->name.' - '.$row->place->subdistrict->name;
                        $data['subdistrict_to_id'] = $row->place->subdistrict_id;
                    }
        
                    $data['details'] = $details;
                    $data['fees'] = [];
                }

                $arr_main[] = $data;
            }
        }

        if($request->arr_lc_id){
            foreach($request->arr_lc_id as $row){
                $data = LandedCost::find(intval($row));
                $data['lookable_type'] = $data->getTable();
                $data['account_name'] = $data->vendor->name;
                $data['from_address'] = ($data->supplier->city()->exists() ? $data->supplier->city->name : '');
                $data['subdistrict_from_id'] = $data->supplier->subdistrict_id;
            
                if($data->used()->exists()){
                    $data['status'] = '500';
                    $data['message'] = 'Landed Cost '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
                }else{
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Landed Cost');
        
                    $details = [];
                    $fees = [];

                    foreach($data->landedCostFeeDetail as $row){
                        $fees[] = [
                            'id'                => $row->landed_cost_fee_id,
                            'total'             => number_format($row->total,2,',','.'),
                            'is_include_tax'    => $row->is_include_tax,
                            'percent_tax'       => $row->percent_tax,
                            'percent_wtax'      => $row->percent_wtax,
                            'tax'               => number_format($row->tax,2,',','.'),
                            'wtax'              => number_format($row->wtax,2,',','.'),
                            'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        ];
                    }
                    
                    foreach($data->landedCostDetail as $row){
                        $coa = Coa::where('code','500.02.01.13.01')->where('company_id',$row->place->company_id)->where('status','1')->first();
                        $details[] = [
                            'item_id'                   => $row->item_id,
                            'item_name'                 => $row->item->code.' - '.$row->item->name,
                            'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                            'totalrow'                  => $row->lookable->total,
                            'qtyRaw'                    => $row->qty,
                            'unit'                      => $row->item->uomUnit->code,
                            'place_name'                => $row->place->code,
                            'department_name'           => $row->department_id ? $row->department->name : '',
                            'warehouse_name'            => $row->warehouse->name,
                            'place_id'                  => $row->place_id,
                            'line_name'                 => $row->line_id ? $row->line->name : '-',
                            'line_id'                   => $row->line_id ? $row->line_id : '',
                            'machine_name'              => $row->machine_id ? $row->machine->name : '-',
                            'machine_id'                => $row->machine_id ? $row->machine_id : '',
                            'department_id'             => $row->department_id ? $row->department_id : '',
                            'warehouse_id'              => $row->warehouse_id,
                            'project_name'              => $row->project()->exists() ? $row->project->name : '-',
                            'project_id'                => $row->project()->exists() ? $row->project_id : '',
                            'lookable_id'               => $row->id,
                            'lookable_type'             => $row->getTable(),
                            'stock'                     => $row->item->getStockPlace($row->place_id),
                            'coa_id'                    => $coa ? $coa->id : '',
                            'coa_name'                  => $coa ? $coa->name : '',
                        ];

                        $data['to_address'] = $row->place->city->name.' - '.$row->place->subdistrict->name;
                        $data['subdistrict_to_id'] = $row->place->subdistrict_id;
                    }
        
                    $data['details'] = $details;
                    $data['fees'] = $fees;
                }

                $arr_main[] = $data;
            }
        }

        if($request->arr_iti){
            foreach($request->arr_iti as $row){
                $data = InventoryTransferIn::find(intval($row));
                $data['lookable_type'] = $data->getTable();
                $data['account_name'] = '-';
                $data['from_address'] = $data->inventoryTransferOut->placeFrom->city->name.' - '.$data->inventoryTransferOut->placeFrom->subdistrict->name;
                $data['to_address'] = $data->inventoryTransferOut->placeTo->city->name.' - '.$data->inventoryTransferOut->placeTo->subdistrict->name;
                $data['subdistrict_from_id'] = $data->inventoryTransferOut->placeFrom->subdistrict_id;
                $data['subdistrict_to_id'] = $data->inventoryTransferOut->placeTo->subdistrict_id;
            
                if($data->used()->exists()){
                    $data['status'] = '500';
                    $data['message'] = 'Landed Cost '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
                }else{
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Landed Cost');
        
                    $details = [];
                    
                    foreach($data->inventoryTransferOut->inventoryTransferOutDetail as $row){
                        $coa = Coa::where('code','500.02.01.13.01')->where('company_id',$row->itemStock->place->company_id)->where('status','1')->first();
                        $details[] = [
                            'item_id'                   => $row->itemStock->item_id,
                            'item_name'                 => $row->itemStock->item->code.' - '.$row->itemStock->item->name,
                            'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                            'totalrow'                  => $row->total,
                            'qtyRaw'                    => $row->qty,
                            'unit'                      => $row->itemStock->item->uomUnit->code,
                            'place_name'                => $row->inventoryTransferOut->placeTo->name,
                            'department_name'           => '',
                            'warehouse_name'            => $row->inventoryTransferOut->warehouseTo->name,
                            'place_id'                  => $row->inventoryTransferOut->place_to,
                            'line_name'                 => '-',
                            'line_id'                   => '',
                            'machine_name'              => '-',
                            'machine_id'                => '',
                            'department_id'             => '',
                            'warehouse_id'              => $row->inventoryTransferOut->warehouse_to,
                            'project_name'              => '-',
                            'project_id'                => '',
                            'lookable_id'               => $row->id,
                            'lookable_type'             => $row->getTable(),
                            'stock'                     => $row->qty,
                            'coa_id'                    => $coa ? $coa->id : '',
                            'coa_name'                  => $coa ? $coa->name : '',
                        ];
                    }
        
                    $data['details'] = $details;
                    $data['fees'] = [];
                }

                $arr_main[] = $data;
            }
        }

        return response()->json($arr_main);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'supplier_id',
            'account_id',
            'company_id',
            'post_date',
            'reference',
            'currency_id',
            'currency_rate',
            'note',
            'document',
            'total',
            'tax',
            'wtax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = LandedCost::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = LandedCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('reference', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('landedCostDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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

                if($request->vendor_id){
                    $query->whereIn('account_id',$request->vendor_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = LandedCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('reference', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('landedCostDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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

                if($request->vendor_id){
                    $query->whereIn('account_id',$request->vendor_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $val->user->name,
                    $val->supplier_id ? $val->supplier->name : '-',
                    $val->vendor->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->reference,
                    $val->currency()->exists() ? $val->currency->code : '',
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
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
                    <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1  btn-small btn-flat waves-effect waves-light purple darken-2 white-text" data-popup="tooltip" title="Cancel" onclick="cancelStatus(`' . CustomHelper::encrypt($val->code) . '`)" '.$nodis.'><i class="material-icons dp48">cancel</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			            => $request->temp ? ['required', Rule::unique('landed_costs', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:landed_costs,code',
             */'company_id' 			    => 'required',
			'account_id'                => 'required',
            'post_date'                 => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'total'                     => 'required',
            'tax'                       => 'required',
            'grandtotal'                => 'required',
            'arr_item'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_qty'                   => 'required|array'
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 	                => 'Kode tidak boleh kosong.',
            /* 'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
			'account_id.required'               => 'Broker / Ekspeditor tidak boleh kosong',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'total.required'                    => 'Total tagihan tidak boleh kosong.',
            'tax.required'                      => 'Total pajak tidak boleh kosong.',
            'grandtotal.required'               => 'Total tagihan tidak boleh kosong.',
            'arr_item.required'                 => 'Item tidak boleh kosong.',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'arr_price.required'                => 'Harga per item tidak boleh kosong.',
            'arr_price.array'                   => 'Harga per item harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $total = str_replace(',','.',str_replace('.','',$request->total));
            $tax = str_replace(',','.',str_replace('.','',$request->tax));
            $wtax = str_replace(',','.',str_replace('.','',$request->wtax));
            $grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));

            if($grandtotal <= 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Nominal grandtotal tidak boleh dibawah sama dengan 0.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = LandedCost::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->hasChildDocument()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi telah dipakai di dokumen lainnya.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/landed_costs');
                        } else {
                            $document = $query->document;
                        }

                        if($query->status == '2'){
                            CustomHelper::removeJournal($query->getTable(),$query->id);
                            CustomHelper::removeCogs($query->getTable(),$query->id);
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->supplier_id = $request->supplier_id ? $request->supplier_id : NULL;
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->reference = $request->reference;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->document = $document;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->wtax = round($wtax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->status = '1';

                        $query->save();

                        $query->landedCostDetail()->delete();

                        $query->landedCostFeeDetail()->delete();

                        CustomHelper::removeApproval($query->getTable(),$query->id);

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status landed cost sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=LandedCost::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = LandedCost::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'supplier_id'               => $request->supplier_id ? $request->supplier_id : NULL,
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'reference'                 => $request->reference,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/landed_costs') : NULL,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'wtax'                      => round($wtax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_item){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_item as $key => $row){
                            LandedCostDetail::create([
                                'landed_cost_id'        => $query->id,
                                'item_id'               => $row,
                                'coa_id'                => $request->arr_coa[$key] ? $request->arr_coa[$key] : NULL,
                                'qty'                   => floatval($request->arr_qty[$key]),
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                                'place_id'              => $request->arr_place[$key],
                                'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                                'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                                'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                                'warehouse_id'          => $request->arr_warehouse[$key],
                                'project_id'            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                                'lookable_type'         => $request->arr_lookable_type[$key],
                                'lookable_id'           => $request->arr_lookable_id[$key],
                            ]);
                        }

                        foreach($request->arr_fee_id as $key => $row){
                            if(str_replace(',','.',str_replace('.','',$request->arr_fee_grandtotal[$key])) > 0){
                                LandedCostFeeDetail::create([
                                    'landed_cost_id'        => $query->id,
                                    'landed_cost_fee_id'    => $row,
                                    'total'                 => str_replace(',','.',str_replace('.','',$request->arr_fee_nominal[$key])),
                                    'is_include_tax'        => $request->arr_fee_include_tax[$key],
                                    'percent_tax'           => $request->arr_fee_tax[$key],
                                    'percent_wtax'          => $request->arr_fee_wtax[$key],
                                    'tax'                   => str_replace(',','.',str_replace('.','',$request->arr_fee_tax_rp[$key])),
                                    'wtax'                  => str_replace(',','.',str_replace('.','',$request->arr_fee_wtax_rp[$key])),
                                    'grandtotal'            => str_replace(',','.',str_replace('.','',$request->arr_fee_grandtotal[$key])),
                                ]);
                            }
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('landed_costs',$query->id,$query->note);
                CustomHelper::sendNotification('landed_costs',$query->id,'Pengajuan Landed Cost No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit landed cost.');

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

    public function rowDetail(Request $request)
    {
        $data   = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="12">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Harga Total</th>
                                <th class="center-align">Harga Satuan</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Proyek</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totalnominal=0;
        if(count($data->landedCostDetail) > 0){
            foreach($data->landedCostDetail as $key => $row){
                $totalqty+=$row->qty;
                $totalnominal+=$row->nominal;
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->item->code.' - '.$row->item->name.'</td>
                    <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                    <td class="center-align">'.$row->item->uomUnit->code.'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    <td class="right-align">'.number_format(round($row->nominal / $row->qty,3),2,',','.').'</td>
                    <td class="center-align">'.$row->place->name.'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.$row->warehouse->name.'</td>
                    <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                </tr>';
            }
            $string .= '<tr>
                    <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;"></td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalnominal, 2, ',', '.') . '</td>
                </tr>  
            ';
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="12">Data item tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Rincian Biaya</th>
                            </tr>
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">Deskripsi</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPh</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->landedCostFeeDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->landedCostFee->name.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
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
        
        $lc = LandedCost::where('code',CustomHelper::decrypt($id))->first();
                
        if($lc){
            $data = [
                'title'     => 'Print Landed Cost',
                'data'      => $lc
            ];

            return view('admin.approval.landed_cost', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $lc = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        if(!CustomHelper::checkLockAcc($lc->post_date)){
            return response()->json([
                'status'  => 500,
                'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
            ]);
        }
        $lc['code_place_id'] = substr($lc->code,7,2);
        $lc['supplier_name'] = $lc->supplier()->exists() ? $lc->supplier->name : '';
        $lc['account_name'] = $lc->vendor()->exists() ? $lc->vendor->name : '';
        $lc['total'] = number_format($lc->total,2,',','.');
        $lc['tax'] = number_format($lc->tax,2,',','.');
        $lc['wtax'] = number_format($lc->wtax,2,',','.');
        $lc['grandtotal'] = number_format($lc->grandtotal,2,',','.');
        $lc['currency_rate'] = number_format($lc->currency_rate,2,',','.');

        $arr = [];
        $fees = [];

        foreach($lc->landedCostDetail as $row){
            $arr[] = [
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->name.' - '.$row->item->name,
                'qtyRaw'                    => $row->qty,
                'totalrow'                  => $row->lookable->total,
                'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                'nominal'                   => number_format($row->nominal,2,',','.'),
                'unit'                      => $row->item->uomUnit->code,
                'place_name'                => $row->place->name,
                'department_name'           => $row->department_id ? $row->department->name : '-',
                'warehouse_name'            => $row->warehouse->name,
                'project_name'              => $row->project()->exists() ? $row->project->name : '-',
                'place_id'                  => $row->place_id,
                'line_id'                   => $row->line_id ? $row->line_id : '',
                'line_name'                 => $row->line_id ? $row->line->name : '-',
                'machine_id'                => $row->machine_id ? $row->machine_id : '',
                'machine_name'              => $row->machine_id ? $row->machine->name : '-',
                'department_id'             => $row->department_id ? $row->department_id : '',
                'warehouse_id'              => $row->warehouse_id,
                'project_id'                => $row->project()->exists() ? $row->project_id : '',
                'lookable_type'             => $row->lookable_type,
                'lookable_id'               => $row->lookable_id,
                'coa_id'                    => $row->coa_id ? $row->coa_id : '',
                'coa_name'                  => $row->coa_id ? $row->coa->code.' - '.$row->coa->name : '',
                'stock'                     => $row->item->getStockPlace($row->place_id),
            ];

            $lc['to_address'] = $row->place->city->name.' - '.$row->place->subdistrict->name;
            $lc['subdistrict_to_id'] = $row->place->subdistrict_id;
        }

        foreach($lc->landedCostFeeDetail as $row){
            $fees[] = [
                'id'                => $row->landed_cost_fee_id,
                'total'             => number_format($row->total,2,',','.'),
                'is_include_tax'    => $row->is_include_tax,
                'percent_tax'       => $row->percent_tax,
                'percent_wtax'      => $row->percent_wtax,
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
            ];
        }

        $lc['details'] = $arr;
        $lc['fees'] = $fees;
        				
		return response()->json($lc);
    }

    public function voidStatus(Request $request){
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada A/P Invoice.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                CustomHelper::removeJournal('landed_costs',$query->id);
                CustomHelper::removeCogs('landed_costs',$query->id);
    
                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the landed cost data');
    
                CustomHelper::sendNotification('landed_costs',$query->id,'Landed Cost No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('landed_costs',$query->id);
                CustomHelper::removeJournal('landed_costs',$query->id);

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
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();

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

            CustomHelper::removeApproval('landed_costs',$query->id);
            
            foreach($query->landedCostDetail as $rowdetail){
                ItemCogs::where('lookable_type','landed_costs')->where('lookable_id',$query->id)->delete();
                ResetCogs::dispatch($query->post_date,$rowdetail->place_id,$rowdetail->item_id);
            }

            $query->landedCostDetail()->delete();
            
            CustomHelper::removeJournal('landed_costs',$query->id);

            activity()
                ->performedOn(new LandedCost())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase order data');

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

    public function printALL(Request $request){

        $data = [
            'title' => 'LANDED COST REPORT',
            'data' => LandedCost::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('reference', 'like', "%$request->search%")
                            ->orWhere('total', 'like', "%$request->search%")
                            ->orWhere('tax', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('landedCostDetail',function($query) use($request){
                                $query->whereHas('item',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%")
                                        ->orWhere('name','like',"%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->vendor_id){
                    $query->whereIn('account_id',$request->vendor_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->get()
		];
		
		return view('admin.print.purchase.landed_cost', $data);
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
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = LandedCost::where('code',$row)->first();
                
                if($pr){
                    
                    $pdf = PrintHelper::print($pr,'Landed Cost','a4','portrait','admin.print.purchase.landed_cost_individual');
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
                        $query = LandedCost::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Landed Cost','a4','portrait','admin.print.purchase.landed_cost_individual');
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
                        $query = LandedCost::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Landed Cost','a4','portrait','admin.print.purchase.landed_cost_individual');
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

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = LandedCost::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Landed Cost','a4','portrait','admin.print.purchase.landed_cost_individual',$menuUser->mode);
    
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		
		return Excel::download(new ExportLandedCost($post_date,$end_date,$mode), 'landed_cost'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $currency = $request->currency? $request->currency : '';
        $supplier = $request->supplier? $request->supplier : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportLandedCostTransactionPage($search,$post_date,$end_date,$currency,$supplier,$status,$modedata), 'purchase_request_'.uniqid().'.xlsx');
    }
    
    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();

        $data_link = [];
        $data_go_chart = [];
        if($query) {
            $lc = [
                "key" => $query->code,
                "name" => $query->code,
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')],
                 ],
                'color'=>"lightblue",
                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($query->code),
            ];
            $data_go_chart[]=$lc;
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_lc',$query->id);
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
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data not Found.'
            ];
        }
        return response()->json($response);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = LandedCost::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->journal->note,
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
                    <td>'.($row->note ? $row->note : '').'</td>
                    <td>'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
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

    public function getOutstanding(Request $request){
		return Excel::download(new ExportOutstandingLC(), 'outstanding_landed_cost'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new LandedCost())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Landed Cost data');
    
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
    public function cancelStatus(Request $request){
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada A/P Invoice.'
                ];
            }else{
                
                CustomHelper::removeApproval($query->getTable(),$query->id);

                $query->update([
                    'status'    => '8',
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
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void cancel the Landed cost data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Landed Cost No. '.$query->code.' telah ditutup dengan tombol cancel void.','Landed Cost No. '.$query->code.' telah ditutup dengan tombol cancel void.',$query->user_id);
    
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
}