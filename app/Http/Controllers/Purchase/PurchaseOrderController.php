<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodIssueRequest;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\Item;
use App\Models\Line;
use App\Models\CloseBill;
use App\Models\FundRequest;
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
use App\Exports\ExportOutstandingPO;
use App\Models\MarketingOrderDeliveryProcess;

use App\Models\Place;
use App\Models\Menu;
use App\Models\UsedData;
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
use App\Models\Currency;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseOrder;
use App\Exports\ExportPurchaseOrderTransactionPage;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\Tax;
use App\Models\Coa;
use App\Models\Division;
use App\Models\ItemUnit;
use App\Models\MenuUser;
use App\Models\Unit;
use Milon\Barcode\DNS2D;
use Milon\Barcode\Facades\DNS2DFacade;

class PurchaseOrderController extends Controller
{
    protected $dataplaces, $dataplacecode, $subordinate, $array_subordinate_id;

    public function __construct(){
        $user = User::find(session('bo_id'));
        if($user){
            $this->subordinate = $user ? $user->getAllSubordinates() : []; 
            $this->array_subordinate_id=[];
            foreach($this->subordinate as $row){
                $this->array_subordinate_id[]= $row->id;
            }
            $this->array_subordinate_id[]=$user->id;
            $this->dataplaces = $user ? $user->userPlaceArray() : [];
            $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        }
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Purchase Order',
            'content'       => 'admin.purchase.order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Division::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PurchaseOrder::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'inventory_type',
            'shipping_type',
            'company_id',
            'document_no',
            'document_po',
            'payment_type',
            'payment_term',
            'currency_id',
            'currency_rate',
            'post_date',
            'delivery_date',
            'receiver_name',
            'receiver_address',
            'receiver_phone',
            'received_date',
            'due_date',
            'document_date',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'note',
            'subtotal',
            'discount',
            'total',
            'tax',
            'rounding',
            'grandtotal',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseOrder::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
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
        })->count();
        
        $query_data = PurchaseOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('supplier',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere('note','like',"%$search%")
                                ->orWhere('note2','like',"%$search%");
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

                if($request->inventory_type){
                    $query->where('inventory_type',$request->inventory_type);
                }

                if($request->shipping_type){
                    $query->where('shipping_type',$request->shipping_type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }

                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }

                if($request->payment_type){
                    $query->where('payment_type',$request->payment_type);
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
                // else{
                //     $query->whereIn('user_id',$this->array_subordinate_id);
                // }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('supplier',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })->orWhereHas('purchaseOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere('note','like',"%$search%")
                                ->orWhere('note2','like',"%$search%");
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

                if($request->inventory_type){
                    $query->where('inventory_type',$request->inventory_type);
                }

                if($request->shipping_type){
                    $query->where('shipping_type',$request->shipping_type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }

                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }

                if($request->payment_type){
                    $query->where('payment_type',$request->payment_type);
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
                // else{
                //     $query->whereIn('user_id',$this->array_subordinate_id);
                // }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $btn_close = /* $val->inventory_type == '1' ? '<button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>' :  */'';
                $btn_print = in_array($val->status,['2','3']) ? ' <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) .'`,`'.$val->code.'`)"><i class="material-icons dp48">local_printshop</i></button>' : ' ';
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    $val->supplier->name ?? '',
                    $val->inventoryType(),
                    $val->shippingType(),
                    $val->company->name,
                    $val->document_no,
                    $val->attachments(),
                    $val->paymentType(),
                    $val->payment_term,
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->delivery_date)),
                    $val->receiver_name,
                    $val->receiver_address,
                    $val->receiver_phone,
                    $val->received_date ? date('d/m/Y',strtotime($val->received_date)) : '-',
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
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
                    $btn_close.$btn_print.'
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function getDetails(Request $request){

        if($request->type == 'po'){
            $data = PurchaseRequest::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }elseif($request->type == 'gi'){
            $data = GoodIssue::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }elseif($request->type == 'sj'){
            $data = MarketingOrderDeliveryProcess::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }

        if($data->used()->exists()){
            if($request->type == 'po'){
                $data['status'] = '500';
                $data['message'] = 'Purchase Request '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }elseif($request->type == 'gi'){
                $data['status'] = '500';
                $data['message'] = 'Goods Issue / Barang Keluar '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }elseif($request->type == 'sj'){
                $data['status'] = '500';
                $data['message'] = 'Surat Jalan Penjualan '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }
        }else{
            $passed = true;
            if(!$data->hasBalance()){
                $passed = false;
            }
            
            if($passed){
                $data['account_name'] = $request->type == 'sj' ? $data->account->name : '';
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Purchase Order');
                $details = [];

                if($request->type == 'po'){
                    foreach($data->purchaseRequestDetail()->whereNull('status')->get() as $row){
                        if($row->qtyBalance() > 0){
                            $details[] = [
                                'reference_id'                  => $row->id,
                                'item_id'                       => $row->item_id,
                                'item_name'                     => $row->item->code.' - '.$row->item->name,
                                'old_prices'                    => $row->item->oldPrices($this->dataplaces),
                                'item_unit_id'                  => $row->item_unit_id,
                                'qty'                           => CustomHelper::formatConditionalQty($row->qtyBalance()),
                                'note'                          => $row->note ? $row->note : '',
                                'note2'                         => $row->note2 ? $row->note2 : '',
                                'warehouse_name'                => $row->warehouse->code.' - '.$row->warehouse->name,
                                'warehouse_id'                  => $row->warehouse_id,
                                'place_id'                      => $row->place_id,
                                'line_id'                       => $row->line_id,
                                'machine_id'                    => $row->machine_id,
                                'department_id'                 => $row->department_id,
                                'requester'                     => $row->requester ? $row->requester : '',
                                'project_id'                    => $row->project()->exists() ? $row->project->id : '',
                                'project_name'                  => $row->project()->exists() ? $row->project->name : '-',
                                'buy_units'                     => $row->item->arrBuyUnits(),
                                'uom'                           => $row->item->uomUnit->code,
                            ];
                        }
                    }
                }elseif($request->type == 'gi'){
                    foreach($data->goodIssueDetail as $row){
                        $details[] = [
                            'reference_id'                  => $row->id,
                            'item_id'                       => $row->itemStock->item_id,
                            'item_name'                     => $row->itemStock->item->code.' - '.$row->itemStock->item->name,
                            'old_prices'                    => $row->itemStock->item->oldPrices($this->dataplaces),
                            'item_unit_id'                  => '',
                            'qty'                           => CustomHelper::formatConditionalQty($row->qty),
                            'note'                          => $row->note ? $row->note : '',
                            'note2'                         => '',
                            'warehouse_name'                => $row->itemStock->warehouse->code.' - '.$row->itemStock->warehouse->name,
                            'warehouse_id'                  => $row->itemStock->warehouse_id,
                            'place_id'                      => $row->itemStock->place_id,
                            'line_id'                       => $row->line_id ? $row->line_id : '',
                            'machine_id'                    => $row->machine_id ? $row->machine_id : '',
                            'department_id'                 => $row->department_id ? $row->department_id : '',
                            'requester'                     => $row->requester ? $row->requester : '',
                            'project_id'                    => $row->project()->exists() ? $row->project->id : '',
                            'project_name'                  => $row->project()->exists() ? $row->project->name : '-',
                            'buy_units'                     => $row->itemStock->item->arrBuyUnits(),
                            'uom'                           => $row->itemStock->item->uomUnit->code,
                        ];
                    }
                }elseif($request->type == 'sj'){
                    $details[] = [
                        'reference_id'                  => $data->id,
                    ];
                }

                $data['details'] = $details;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada purchase request / good issue '.$data->code.' telah digunakan pada purchase order.';
            }
        }

        return response()->json($data);
    }

    public function create(Request $request){
        if($request->inventory_type == '1'){
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'supplier_id' 				=> 'required',
                'inventory_type'			=> 'required',
                'shipping_type'		        => 'required',
                'payment_type'		        => 'required',
                'payment_term'		        => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'post_date'                 => 'required',
                'delivery_date'             => 'required',
                'arr_item'                  => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'arr_place'                 => 'required|array',
                'arr_warehouse'             => 'required|array',
                'discount'                  => 'required',
                'rounding'                  => 'required',
            ], [
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
                'inventory_type.required' 			=> 'Tipe persediaan/jasa tidak boleh kosong.',
                'shipping_type.required' 			=> 'Tipe pengiriman tidak boleh kosong.',
                'payment_type.required' 			=> 'Tipe pembayaran tidak boleh kosong.',
                'payment_term.required'				=> 'Termin pembayaran tidak boleh kosong.',
                'currency_id.required'              => 'Mata uang tidak boleh kosong.',
                'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
                'post_date.required'                => 'Tanggal post tidak boleh kosong.',
                'delivery_date.required'            => 'Tanggal kirim tidak boleh kosong.',
                'arr_item.required'                 => 'Item tidak boleh kosong.',
                'arr_item.array'                    => 'Item harus array.',
                'arr_qty.required'                  => 'Qty tidak boleh kosong.',
                'arr_qty.array'                     => 'Qty harus array.',
                'arr_price.required'                => 'Harga tidak boleh kosong.',
                'arr_price.array'                   => 'Harga harus array.',
                'arr_disc1.required'                => 'Diskon 1 tidak boleh kosong.',
                'arr_disc1.array'                   => 'Diskon 1 harus array.',
                'arr_disc2.required'                => 'Diskon 2 tidak boleh kosong.',
                'arr_disc2.array'                   => 'Diskon 2 harus array.',
                'arr_disc3.required'                => 'Diskon 3 tidak boleh kosong.',
                'arr_disc3.array'                   => 'Diskon 3 harus array.',
                'arr_place.required'                => 'Plant tidak boleh kosong.',
                'arr_place.array'                   => 'Plant harus array.',
                'arr_warehouse.required'            => 'Gudang tidak boleh kosong.',
                'arr_warehouse.array'               => 'Gudang harus array.',
                'discount.required'                 => 'Diskon akhir tidak boleh kosong.',
                'rounding.required'                 => 'Pembulatan tidak boleh kosong.'
            ]);
        }elseif($request->inventory_type == '2'){
            $validation = Validator::make($request->all(), [
                'code'			            => 'required',
                'code_place_id'             => 'required',
                'supplier_id' 				=> 'required',
                'inventory_type'			=> 'required',
                'shipping_type'		        => 'required',
                'payment_type'		        => 'required',
                'payment_term'		        => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'post_date'                 => 'required',
                'delivery_date'             => 'required',
                'arr_coa'                   => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'discount'                  => 'required',
                'rounding'                  => 'required',
            ], [
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
                'inventory_type.required' 			=> 'Tipe persediaan/jasa tidak boleh kosong.',
                'shipping_type.required' 			=> 'Tipe pengiriman tidak boleh kosong.',
                'payment_type.required' 			=> 'Tipe pembayaran tidak boleh kosong.',
                'payment_term.required'				=> 'Termin pembayaran tidak boleh kosong.',
                'currency_id.required'              => 'Mata uang tidak boleh kosong.',
                'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
                'post_date.required'                => 'Tanggal post tidak boleh kosong.',
                'delivery_date.required'            => 'Tanggal kirim tidak boleh kosong.',
                'arr_coa.required'                  => 'Coa tidak boleh kosong.',
                'arr_coa.array'                     => 'Coa harus array.',
                'arr_qty.required'                  => 'Qty tidak boleh kosong.',
                'arr_qty.array'                     => 'Qty harus array.',
                'arr_price.required'                => 'Harga tidak boleh kosong.',
                'arr_price.array'                   => 'Harga harus array.',
                'arr_disc1.required'                => 'Diskon 1 tidak boleh kosong.',
                'arr_disc1.array'                   => 'Diskon 1 harus array.',
                'arr_disc2.required'                => 'Diskon 2 tidak boleh kosong.',
                'arr_disc2.array'                   => 'Diskon 2 harus array.',
                'arr_disc3.required'                => 'Diskon 3 tidak boleh kosong.',
                'arr_disc3.array'                   => 'Diskon 3 harus array.',
                'discount.required'                 => 'Diskon akhir tidak boleh kosong.',
                'rounding.required'                 => 'Pembulatan tidak boleh kosong.'
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedZero = true;
            $passedMustPr = true;
            if($request->arr_price){
                foreach($request->arr_price as $row){
                    if(floatval(str_replace(',','.',str_replace('.','',$row))) == 0){
                        $passedZero = false;
                    }
                }

                if(!$passedZero){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Harga item tidak boleh 0.'
                    ]);
                }
            }

            if($request->inventory_type == '1'){
                $arrGroupItem = [];
                foreach($request->arr_item as $key => $row){
                    $item = Item::find(intval($row));
                    $topGroupId = $item->itemGroup->getTopParent($item->itemGroup);
                    $topGroupName = $item->itemGroup->getTopParentName($item->itemGroup);
                    $index = -1;
                    foreach($arrGroupItem as $keyitem => $row){
                        if($topGroupId == $row['group_id']){
                            $index = $keyitem;
                        }
                    }
                    if($index >= 0){

                    }else{
                        $arrGroupItem[] = [
                            'item_name'     => $item->code.' - '.$item->name,
                            'group_name'    => $topGroupName,
                            'group_id'      => $topGroupId,
                        ];
                    }
                }
                if(count($arrGroupItem) > 1){
                    $arrError = [];
                    foreach($arrGroupItem as $row){
                        $arrError[] = $row['item_name'].' Grup : '.$row['group_name'];
                    }
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf PO tidak bisa memiliki lebih dari 1 macam group item. Daftarnya : '.implode(', ',$arrError),
                    ]);
                }
                foreach($request->arr_type as $key => $row){
                    if(!$row){
                        $passedMustPr = false;
                    }
                }
            }

            if(!$passedMustPr){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf PO tipe Persediaan harus menarik data Purchase Request.',
                ]);
            }

            if($request->inventory_type == '2'){
                $passedProfitLoss = true;
                if($request->arr_coa){
                    foreach($request->arr_coa as $key => $row){
                        $coa = Coa::find(intval($row));
                        if(in_array(substr($coa->code,0,1),['4','5','6','7','8'])){
                            if(!isset($request->arr_department[$key])){
                                $passedProfitLoss = false;
                            }
                        }
                    }
                }
                if(!$passedProfitLoss){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Untuk Coa terpilih harus memiliki Divisi. Silahkan pilih divisi.'
                    ]);
                }
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->temp))->first();
            
                    if($query->hasChildDocument()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Purchase Order telah digunakan di dokumen lain, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','2','6'])){

                        if($request->has('file')) {

                            if($query->document_po){
                                $arrFile = explode(',',$query->document_po);
                                foreach($arrFile as $row){
                                    if(Storage::exists($row)){
                                        Storage::delete($row);
                                    }
                                }
                            }

                            $arrFile = [];

                            foreach($request->file('file') as $key => $file)
                            {
                                $arrFile[] = $file->store('public/purchase_orders');
                            }

                            $document = implode(',',$arrFile);
                        } else {
                            $document = $query->document_po;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $request->supplier_id;
                        $query->inventory_type = $request->inventory_type;
                        $query->shipping_type = $request->shipping_type;
                        $query->company_id = $request->company_id;
                        $query->document_no = $request->document_no;
                        $query->document_po = $document;
                        $query->payment_type = $request->payment_type;
                        $query->payment_term = $request->payment_term;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->delivery_date = $request->delivery_date;
                        $query->received_date = $request->received_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->tax_no = $request->tax_no;
                        $query->tax_cut_no = $request->tax_cut_no;
                        $query->cut_date = $request->cut_date;
                        $query->spk_no = $request->spk_no;
                        $query->invoice_no = $request->invoice_no;
                        $query->note = $request->note;
                        $query->note_external = $request->note_external;
                        $query->subtotal = str_replace(',','.',str_replace('.','',$request->savesubtotal));
                        $query->discount = str_replace(',','.',str_replace('.','',$request->discount));
                        $query->total = str_replace(',','.',str_replace('.','',$request->savetotal));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->savetax));
                        $query->wtax = str_replace(',','.',str_replace('.','',$request->savewtax));
                        $query->rounding = str_replace(',','.',str_replace('.','',$request->rounding));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->savegrandtotal));
                        $query->receiver_name = $request->receiver_name;
                        $query->receiver_address = $request->receiver_address;
                        $query->receiver_phone = $request->receiver_phone;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->PurchaseOrderDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase order sudah SELESAI, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $fileUpload = '';

                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/purchase_orders');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }

                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=PurchaseOrder::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = PurchaseOrder::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->supplier_id,
                        'inventory_type'	        => $request->inventory_type,
                        'shipping_type'             => $request->shipping_type,
                        'company_id'                => $request->company_id,
                        'document_no'               => $request->document_no,
                        'document_po'               => $fileUpload ? $fileUpload : NULL,
                        'payment_type'              => $request->payment_type,
                        'payment_term'              => $request->payment_term,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'received_date'             => $request->received_date,
                        'due_date'                  => $request->due_date,
                        'document_date'             => $request->document_date,
                        'tax_no'                    => $request->tax_no,
                        'tax_cut_no'                => $request->tax_cut_no,
                        'cut_date'                  => $request->cut_date,
                        'spk_no'                    => $request->spk_no,
                        'invoice_no'                => $request->invoice_no,
                        'note'                      => $request->note,
                        'note_external'             => $request->note_external,
                        'subtotal'                  => str_replace(',','.',str_replace('.','',$request->savesubtotal)),
                        'discount'                  => str_replace(',','.',str_replace('.','',$request->discount)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->savetotal)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->savetax)),
                        'wtax'                      => str_replace(',','.',str_replace('.','',$request->savewtax)),
                        'rounding'                  => str_replace(',','.',str_replace('.','',$request->rounding)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->savegrandtotal)),
                        'status'                    => '1',
                        'receiver_name'             => $request->receiver_name,
                        'receiver_address'          => $request->receiver_address,
                        'receiver_phone'            => $request->receiver_phone
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                DB::beginTransaction();
                try {

                    if($request->inventory_type == '1'){
                        foreach($request->arr_item as $key => $row){
            
                            $qty = str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                            if($qty > 0){
                                $price = str_replace(',','.',str_replace('.','',$request->arr_price[$key]));
                                $disc1 = str_replace(',','.',str_replace('.','',$request->arr_disc1[$key]));
                                $disc2 = str_replace(',','.',str_replace('.','',$request->arr_disc2[$key]));
                                $disc3 = str_replace(',','.',str_replace('.','',$request->arr_disc3[$key]));
                
                                $finalpricedisc1 = $price - ($price * ($disc1 / 100));
                                $finalpricedisc2 = $finalpricedisc1 - ($finalpricedisc1 * ($disc2 / 100));
                                $finalpricedisc3 = $finalpricedisc2 - $disc3;
                
                                $rowsubtotal = round($finalpricedisc3 * $qty,2);
                                
                                $itemUnit = ItemUnit::find(intval($request->arr_unit[$key]));
                                $querydetail = PurchaseOrderDetail::create([
                                    'purchase_order_id'             => $query->id,
                                    'purchase_request_detail_id'    => $request->arr_data ? ($request->arr_type[$key] == 'po' ? $request->arr_data[$key] : NULL) : NULL,
                                    'good_issue_detail_id'          => $request->arr_data ? ($request->arr_type[$key] == 'gi' ? $request->arr_data[$key] : NULL) : NULL,
                                    'item_id'                       => $request->arr_type[$key] == 'gi' ? CustomHelper::addNewItemService(intval($row)) : $row,
                                    'qty'                           => $qty,
                                    'item_unit_id'                  => $itemUnit->id,
                                    'qty_conversion'                => $itemUnit->conversion,
                                    'price'                         => $price,
                                    'percent_discount_1'            => $disc1,
                                    'percent_discount_2'            => $disc2,
                                    'discount_3'                    => $disc3,
                                    'subtotal'                      => $rowsubtotal,
                                    'tax'                           => str_replace(',','.',str_replace('.','',$request->arr_nominal_tax[$key])),
                                    'wtax'                          => str_replace(',','.',str_replace('.','',$request->arr_nominal_wtax[$key])),
                                    'grandtotal'                    => str_replace(',','.',str_replace('.','',$request->arr_nominal_grandtotal[$key])),
                                    'note'                          => $request->arr_note[$key] ? $request->arr_note[$key] : NULL,
                                    'note2'                         => $request->arr_note2[$key] ? $request->arr_note2[$key] : NULL,
                                    'is_tax'                        => $request->arr_tax[$key] > 0 ? '1' : NULL,
                                    'is_include_tax'                => $request->arr_is_include_tax[$key] == '1' ? '1' : '0',
                                    'percent_tax'                   => $request->arr_tax[$key],
                                    'is_wtax'                       => $request->arr_wtax[$key] > 0 ? '1' : NULL,
                                    'percent_wtax'                  => $request->arr_wtax[$key],
                                    'tax_id'                        => $request->arr_tax_id[$key],
                                    'wtax_id'                       => $request->arr_wtax_id[$key],
                                    'place_id'                      => $request->arr_place[$key],
                                    'line_id'                       => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                                    'machine_id'                    => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                                    'department_id'                 => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                                    'warehouse_id'                  => $request->arr_warehouse[$key] ? $request->arr_warehouse[$key] : NULL,
                                    'requester'                     => $request->arr_requester[$key] ? $request->arr_requester[$key] : NULL,
                                    'project_id'                    => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                                ]);
                                
                                if($querydetail->purchaseRequestDetail()->exists()){
                                    CustomHelper::removeUsedData('purchase_requests',$querydetail->purchaseRequestDetail->purchase_request_id);
                                }

                                if($querydetail->goodReceiptDetail()->exists()){
                                    CustomHelper::removeUsedData('good_issues',$querydetail->goodReceiptDetail->good_issue_id);
                                }
                            }
                        }
                    }elseif($request->inventory_type == '2'){
                        foreach($request->arr_coa as $key => $row){
            
                            $qty = str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                            if($qty > 0){
                                $price = str_replace(',','.',str_replace('.','',$request->arr_price[$key]));
                                $disc1 = str_replace(',','.',str_replace('.','',$request->arr_disc1[$key]));
                                $disc2 = str_replace(',','.',str_replace('.','',$request->arr_disc2[$key]));
                                $disc3 = str_replace(',','.',str_replace('.','',$request->arr_disc3[$key]));
                
                                $finalpricedisc1 = $price - ($price * ($disc1 / 100));
                                $finalpricedisc2 = $finalpricedisc1 - ($finalpricedisc1 * ($disc2 / 100));
                                $finalpricedisc3 = $finalpricedisc2 - $disc3;
                
                                $rowsubtotal = round($finalpricedisc3 * $qty,2);

                                $bobot = $rowsubtotal / str_replace(',','.',str_replace('.','',$request->savesubtotal));

                                $tax = ($request->arr_tax[$key] / 100) * $rowsubtotal;
                                $wtax = $bobot * str_replace(',','.',str_replace('.','',$request->wtax));
        
                                $querydetail = PurchaseOrderDetail::create([
                                    'purchase_order_id'                     => $query->id,
                                    'marketing_order_delivery_process_id'   => $request->arr_type[$key] == 'sj' ? $request->arr_data[$key] : NULL,
                                    'coa_id'                                => $row,
                                    'qty'                                   => $qty,
                                    'coa_unit_id'                           => $request->arr_unit[$key],
                                    'price'                                 => $price,
                                    'percent_discount_1'                    => $disc1,
                                    'percent_discount_2'                    => $disc2,
                                    'discount_3'                            => $disc3,
                                    'subtotal'                              => $rowsubtotal,
                                    'tax'                                   => str_replace(',','.',str_replace('.','',$request->arr_nominal_tax[$key])),
                                    'wtax'                                  => str_replace(',','.',str_replace('.','',$request->arr_nominal_wtax[$key])),
                                    'grandtotal'                            => str_replace(',','.',str_replace('.','',$request->arr_nominal_grandtotal[$key])),
                                    'note'                                  => $request->arr_note[$key] ? $request->arr_note[$key] : NULL,
                                    'note2'                                 => $request->arr_note2[$key] ? $request->arr_note2[$key] : NULL,
                                    'is_tax'                                => $request->arr_tax[$key] > 0 ? '1' : NULL,
                                    'is_include_tax'                        => $request->arr_is_include_tax[$key] == '1' ? '1' : '0',
                                    'percent_tax'                           => $request->arr_tax[$key],
                                    'is_wtax'                               => $request->arr_wtax[$key] > 0 ? '1' : NULL,
                                    'percent_wtax'                          => $request->arr_wtax[$key],
                                    'tax_id'                                => $request->arr_tax_id[$key],
                                    'wtax_id'                               => $request->arr_wtax_id[$key],
                                    'place_id'                              => $request->arr_place[$key],
                                    'line_id'                               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                                    'machine_id'                            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                                    'department_id'                         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                                    'requester'                             => $request->arr_requester[$key] ? $request->arr_requester[$key] : NULL,
                                    'project_id'                            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                                ]);
                            }
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                if($request->temp){
                    $query->updateRootDocumentStatusProcess();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Purchase Order No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PurchaseOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase order.');

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
        $data   = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.' - '.$data->account->name.$x.'</div><div class="col s12" style="overflow:auto;"><table style="min-width:2500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="22">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item/Coa Biaya</th>
                                <th class="center-align">Grup Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Discount 1 (%)</th>
                                <th class="center-align">Discount 2 (%)</th>
                                <th class="center-align">Discount 3 (Rp)</th>
                                <th class="center-align">Subtotal</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPh</th>
                                <th class="center-align">Grandtotal</th>
                                <th class="center-align">Keterangan 1</th>
                                <th class="center-align">Keterangan 2</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Requester</th>
                                <th class="center-align">Proyek</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        
        $totaldiskon1=0;
        $totaldiskon2=0;
        $totaldiskon3=0;
        $totalsubtotal=0;
        $totaltax=0;
        $totalwtax=0;
        $totalgrandtotal=0;
        foreach($data->purchaseOrderDetail as $key => $row){
            $totalqty+=$row->qty;
            $totaldiskon1+=$row->percent_discount_1;
            $totaldiskon1+=$row->percent_discount_2;
            $totaldiskon3+=$row->discount_3;
            $totalsubtotal+=$row->subtotal;
            $totaltax+=$row->tax;
            $totalwtax+=$row->wtax;
            $totalgrandtotal+=$row->grandtotal;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.($row->item_id ? $row->item->code.' - '.$row->item->name : $row->coa->name).'</td>
                <td class="center-align">'.($row->item_id ? $row->item->itemGroup->name : '-').'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.($row->itemUnit()->exists() ? $row->itemUnit->unit->code : ($row->coaUnit()->exists() ? $row->coaUnit->code : '-')).'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="center-align">'.$row->place->code.'</td>
                <td class="center-align">'.($row->line()->exists() ? $row->line->name : '-').'</td>
                <td class="center-align">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                <td class="center-align">'.($row->purchaseRequestDetail()->exists() ? $row->purchaseRequestDetail->purchaseRequest->code : ($row->goodIssueDetail()->exists() ? $row->goodIssueDetail->goodIssue->code : ' - ')).'</td>
                <td class="center-align">'.($row->requester ? $row->requester : '-').'</td>
                <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon1, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon2, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon3, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalsubtotal, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaltax, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalwtax, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
            </tr>  
        ';
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $po = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);
        $po['supplier_name'] = $po->supplier->name;
        $po['top_master'] = $po->supplier->top;
        $subtotal_convert = $po->subtotal * $po->currency_rate;
        $discount_convert = $po->discount * $po->currency_rate;
        $total_convert = $po->total * $po->currency_rate;
        $tax_convert = $po->tax * $po->currency_rate;
        $wtax_convert = $po->wtax * $po->currency_rate;
        $grandtotal_convert = $po->grandtotal * $po->currency_rate;
        $rounding = $po->rounding * $po->currency_rate;
        $po['currency_rate'] = number_format($po->currency_rate,2,',','.');
        $po['subtotal'] = number_format($po->subtotal,2,',','.');
        $po['discount'] = number_format($po->discount,2,',','.');
        $po['total'] = number_format($po->total,2,',','.');
        $po['tax'] = number_format($po->tax,2,',','.');
        $po['wtax'] = number_format($po->wtax,2,',','.');
        $po['grandtotal'] = number_format($po->grandtotal,2,',','.');
        $po['rounding'] = number_format($po->rounding,2,',','.');

        $po['subtotal_convert'] = number_format($subtotal_convert,2,',','.');
        $po['discount_convert'] = number_format($discount_convert,2,',','.');
        $po['total_convert'] = number_format($total_convert,2,',','.');
        $po['tax_convert'] = number_format($tax_convert,2,',','.');
        $po['wtax_convert'] = number_format($wtax_convert,2,',','.');
        $po['grandtotal_convert'] = number_format($grandtotal_convert,2,',','.');
        $po['rounding_convert'] = number_format($rounding,2,',','.');

        $arr = [];
        
        foreach($po->purchaseOrderDetail as $row){
            $arr[] = [
                'id'                                => $row->purchaseRequestDetail()->exists() ? $row->purchaseRequestDetail->purchase_request_id : ($row->goodIssueDetail()->exists() ? $row->goodIssueDetail->good_issue_id : '0'),
                'reference_id'                      => $row->purchase_request_detail_id ? $row->purchase_request_detail_id : ($row->good_issue_detail_id ? $row->good_issue_detail_id : '0' ),
                'item_id'                           => $row->item_id,
                'coa_id'                            => $row->coa_id,
                'item_name'                         => $row->item_id ? $row->item->code.' - '.$row->item->name : '',
                'coa_name'                          => $row->coa_id ? $row->coa->name : '',
                'qty'                               => CustomHelper::formatConditionalQty($row->qty),
                'qty_stock'                         => $row->item_id ? CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion) : '-',
                'unit_stock'                        => $row->item_id ? $row->item->uomUnit->code : '-',
                'item_unit_id'                      => $row->itemUnit()->exists() ? $row->item_unit_id : '',
                'item_unit_name'                    => $row->itemUnit()->exists() ? $row->itemUnit->unit->code.' - '.$row->itemUnit->unit->name : '',
                'coa_unit_id'                       => $row->coaUnit()->exists() ? $row->coa_unit_id : '',
                'coa_unit_name'                     => $row->coaUnit()->exists() ? $row->coaUnit->code.' - '.$row->coaUnit->name : '',
                'note'                              => $row->note ? $row->note : '',
                'note2'                             => $row->note2 ? $row->note2 : '',
                'price'                             => CustomHelper::formatConditionalQty($row->price),
                'disc1'                             => number_format($row->percent_discount_1,2,',','.'),
                'disc2'                             => number_format($row->percent_discount_2,2,',','.'),
                'disc3'                             => number_format($row->discount_3,2,',','.'),
                'subtotal'                          => CustomHelper::formatConditionalQty($row->subtotal),
                'total'                             => CustomHelper::formatConditionalQty($row->subtotal),
                'tax'                               => CustomHelper::formatConditionalQty($row->tax),
                'wtax'                              => CustomHelper::formatConditionalQty($row->wtax),
                'grandtotal'                        => CustomHelper::formatConditionalQty($row->grandtotal),
                'is_tax'                            => $row->is_tax ? $row->is_tax : '',
                'is_include_tax'                    => $row->is_include_tax ? $row->is_include_tax : '',
                'percent_tax'                       => number_format($row->percent_tax,2,',','.'),
                'is_wtax'                           => $row->is_wtax ? $row->is_wtax : '',
                'percent_wtax'                      => number_format($row->percent_wtax,2,',','.'),
                'warehouse_id'                      => $row->warehouse_id,
                'warehouse_name'                    => $row->warehouse_id ? $row->warehouse->name : '',
                'place_id'                          => $row->place_id,
                'line_id'                           => $row->line_id ? $row->line_id : '',
                'machine_id'                        => $row->machine_id ? $row->machine_id : '',
                'department_id'                     => $row->department_id ? $row->department_id : '',
                'tax_id'                            => $row->tax_id,
                'wtax_id'                           => $row->wtax_id,
                'type'                              => $row->purchase_request_detail_id ? 'po' : ($row->good_issue_detail_id ? 'gi' : ''),
                'requester'                         => $row->requester ? $row->requester : '',
                'project_id'                        => $row->purchaseRequestDetail()->exists() ? ($row->purchaseRequestDetail->project()->exists() ? $row->purchaseRequestDetail->project->id : '') : '',
                'project_name'                      => $row->purchaseRequestDetail()->exists() ? ($row->purchaseRequestDetail->project()->exists() ? $row->purchaseRequestDetail->project->name : '-') : '-',
                'buy_units'                         => $row->item_id ? $row->item->arrBuyUnits() : [],
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = PurchaseOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Order',
                'data'      => $pr
            ];

            return view('admin.approval.purchase_order', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = PurchaseOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Order',
                'data'      => $pr
            ];
          
            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
            // $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
                'year_range'                 => 'required'
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
                'year_range'                 => 'Harap Isi kolom tahun'
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
                        
                        $query = PurchaseOrder::where('Code', 'LIKE', '%'.$x)->first();

                        if($query){
                            $data = [
                                'title'     => 'Print Purchase Order',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
                            $pdf->render();
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


                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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
                        $query = PurchaseOrder::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Purchase Order',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
                            $pdf->render();
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
    
    
                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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

    public function voidStatus(Request $request){
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada Goods Receipt PO.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                $query->updateRootDocumentStatusProcess();
    
                activity()
                    ->performedOn(new PurchaseOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase order data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Purchase Order No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);

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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        

        $data_go_chart=[];
        
        $data_link=[];
        if($query) {
            $name = $query->supplier->name ?? null;
            $data_po = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Vendor  : ". ($name !== null ? $name : ' ')],
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($query->code),           
            ];
            
            $data_go_chart[]=$data_po;
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_po',$query->id);
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
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            $query->purchaseOrderDetail()->delete();

            CustomHelper::removeApproval('purchase_orders',$query->id);

            activity()
                ->performedOn(new PurchaseOrder())
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
            'title' => 'PURCHASE ORDER REPORT',
            'data' => PurchaseOrder::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('document_no', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhere('subtotal', 'like', "%$request->search%")
                            ->orWhere('discount', 'like', "%$request->search%")
                            ->orWhere('total', 'like', "%$request->search%")
                            ->orWhere('tax', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use ($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->inventory){
                    $query->where('inventory_type',$request->inventory);
                }

                if($request->shipping){
                    $query->where('shipping_type',$request->shipping);
                }

                if($request->supplier){
                    $query->whereIn('account_id',$request->supplier);
                }
                
                if($request->company){
                    $query->where('company_id',$request->company);
                }

                if($request->payment){
                    $query->where('payment_type',$request->payment);
                }                
                
                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
                }
            })
            ->get()
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
         
        $pdf = Pdf::loadView('admin.print.purchase.order', $data)->setPaper('a4', 'portrait');
        $pdf->render();

        $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
        $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
        
        $content = $pdf->download()->getOriginalContent();
        
        $randomString = Str::random(10); 

         
        $filePath = 'public/pdf/' . $randomString . '.pdf';
        

        Storage::put($filePath, $content);
        
        $document_po = asset(Storage::url($filePath));
 


        return $document_po;
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
                $pr = PurchaseOrder::where('code',$row)->first();
               
                
                if($pr){
                    $data = [
                        'title'     => 'Print Purchase Order',
                        'data'      => $pr,
                      
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
                    $pdf->render();
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


            $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		
		return Excel::download(new ExportPurchaseOrder($post_date,$end_date,$mode), 'purchase_order_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type_buy = $request->type_buy ? $request->type_buy : '';
        $type_deliv = $request->type_deliv? $request->type_deliv : '';
        $company = $request->company ? $request->company : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $supplier = $request->supplier? $request->supplier : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';
      
		return Excel::download(new ExportPurchaseOrderTransactionPage($search,$status,$type_buy,$type_deliv,$company,$type_pay,$supplier,$currency,$end_date,$start_date,$modedata), 'purchase_order_'.uniqid().'.xlsx');
    }

    public function removeUsedData(Request $request){
        if($request->type == 'po'){
            CustomHelper::removeUsedData('purchase_requests',$request->id);
        }elseif($request->type == 'gi'){
            CustomHelper::removeUsedData('good_issues',$request->id);
        }elseif($request->type == 'sj'){
            CustomHelper::removeUsedData('marketing_order_delivery_processes',$request->id);
        }
        
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function getItems(Request $request){
        $pr = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();

        $arr = [];

        foreach($pr->purchaseOrderDetail as $row){
            $arr[] = [
                'id'                => $row->id,
                'item_id'           => $row->item_id,
                'item_name'         => $row->item->code.' - '.$row->item->name,
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'unit'              => $row->item_id ? $row->itemUnit->unit->code : '-',
                'qty_balance'       => CustomHelper::formatConditionalQty($row->getBalanceReceipt()),
                'qty_gr'            => CustomHelper::formatConditionalQty($row->qtyGR()),
                'closed'            => $row->status ? $row->status : '',
            ];
        }

        $pr['details'] = $arr;
        				
		return response()->json($pr);
    }

    public function createDone(Request $request){
        $validation = Validator::make($request->all(), [
            'arr_id'            => 'required|array',
            'arr_value'         => 'required|array',
		], [
            'arr_id.required'       => 'Item tidak boleh kosong',
            'arr_id.array'          => 'Item harus dalam bentuk array.',
            'arr_value.required'    => 'Nilai tidak boleh kosong',
            'arr_value.array'       => 'Nilai harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->tempDone))->first();

            if($query){
                $arr = [];

                foreach($request->arr_id as $key => $row){
                    $prd = PurchaseOrderDetail::find(intval($row));
                    if($prd){
                        $prd->update([
                            'status'    => $request->arr_value[$key] ? $request->arr_value[$key] : NULL,
                        ]);
                        if($request->arr_value[$key]){
                            $arr[] = $prd->item->name;
                        }
                    }
                }

                $items = implode(', ',$arr);

                CustomHelper::sendNotification($query->getTable(),$query->id,'Purchase Order No. '.$query->code.' telah ditutup per item','List yang ditutup adalah sebagai berikut : '.$items.'.',session('bo_id'));

                activity()
                    ->performedOn(new PurchaseOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase order.');

                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];
            }else{
                $response = [
                    'status'    => 500,
                    'message'   => 'Data tidak ditemukan.',
                ];
            }
		}
		
		return response()->json($response);
    }

    // public function getOutstanding(Request $request)
    // {
    //     $start_date = $request->startDate;
    //     $end_date = $request->endDate;

    //     $data = PurchaseOrderDetail::whereHas('purchaseOrder',function($query)use($start_date,$end_date){
    //                 $query->whereIn('status',['2','3']);
    //             })->whereNull('status')->get();
        
    //     $string = '<div class="row pt-1 pb-1"><div class="col s12"><table style="min-width:100%;max-width:100%;">
    //                     <thead>
    //                         <tr>
    //                             <th class="center-align" colspan="10">Daftar Item Order Pembelian</th>
    //                         </tr>
    //                         <tr>
    //                             <th class="center-align">No</th>
    //                             <th class="center-align">Dokumen</th>
    //                             <th class="center-align">Tgl.Post</th>
    //                             <th class="center-align">Keterangan</th>
    //                             <th class="center-align">Status</th>
    //                             <th class="center-align">Item</th>
    //                             <th class="center-align">Satuan</th>
    //                             <th class="center-align">Qty Order.</th>
    //                             <th class="center-align">Qty Diterima</th>
    //                             <th class="center-align">Tunggakan</th>
    //                         </tr>
    //                     </thead><tbody>';
        
    //     foreach($data as $key => $row){
    //         if($row->getBalanceReceipt() > 0){
    //             $string .= '<tr>
    //                 <td class="center-align">'.($key + 1).'</td>
    //                 <td class="center-align">'.$row->purchaseOrder->code.'</td>
    //                 <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->post_date)).'</td>
    //                 <td class="">'.$row->purchaseOrder->note.'</td>
    //                 <td class="center-align">'.$row->purchaseOrder->status().'</td>
    //                 <td class="">'.$row->item->code.' - '.$row->item->name.'</td>
    //                 <td class="center-align">'.$row->itemUnit->unit->code.'</td>
    //                 <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty,3,',','.').'</td>
    //                 <td class="right-align">'.CustomHelper::formatConditionalQty($row->qtyGR(),3,',','.').'</td>
    //                 <td class="right-align">'.number_format($row->getBalanceReceipt(),3,',','.').'</td>
    //             </tr>';
    //         }
    //     }
        
    //     $string .= '</tbody></table></div></div>';

    //     $response = [
    //         'status'    => 200,
    //         'content'   => $string,
    //         'message'   => 'Data tidak ditemukan.',
    //     ];
		
    //     return response()->json($response);
    // }

    public function getOutstanding(Request $request){
		return Excel::download(new ExportOutstandingPO(), 'outstanding_purchase_order_'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new PurchaseOrder())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Purchase Order data');
    
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
}