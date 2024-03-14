<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\GoodIssueRequest;
use App\Models\Item;
use App\Models\Line;
use App\Models\CloseBill;
use App\Exports\ExportOutstandingDP;
use App\Models\LandedCost;
use App\Models\Machine;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;

use App\Models\Place;

use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrder;
use App\Models\Currency;
use App\Models\Menu;
use App\Models\PurchaseDownPaymentDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseDownPayment;
use App\Models\ChecklistDocumentList;
use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use App\Models\MenuUser;
use App\Models\User;
use App\Models\Tax;

class PurchaseDownPaymentController extends Controller
{
    protected $dataplaces, $dataplacecode, $url, $menu;

    public function __construct(){
        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->url = request()->segment(3);
        $this->menu = Menu::where('url', $this->url)->first();
    }

    public function index(Request $request)
    {
       
        $menu = $this->menu;
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $data = [
            'title'         => 'AP Down Payment',
            'content'       => 'admin.purchase.down_payment',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'menu'          => $menu,
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PurchaseDownPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('account_id',$request->supplier)->whereIn('status',['2'])->get();
        $data2 = FundRequest::where('account_id',$request->supplier)->whereIn('status',['2'])->where('document_status','3')->where('type','1')->get();

        $details = [];

        foreach($data as $row){
            $list_items = '<ol>';

            foreach($row->purchaseOrderDetail as $key => $rowdetail){
                $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.number_format($rowdetail->qty,3,',','.').' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
            }

            $list_items .= '</ol>';

            $details[] = [
                'id'            => $row->id,
                'po_code'       => CustomHelper::encrypt($row->code),
                'po_no'         => $row->code,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'delivery_date' => date('d/m/Y',strtotime($row->delivery_date)),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'list_items'    => $list_items,
                'note'          => $row->note ? $row->note : '',
                'type'          => $row->getTable(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
            ];
        }

        foreach($data2 as $row){
            $list_items = '<ol>';

            foreach($row->fundRequestDetail as $key => $rowdetail){
                $item_unit = $rowdetail->unit->code;
                $list_items .= '<li>'.$rowdetail->note.' Qty : '.number_format($rowdetail->qty,3,',','.').' '.$item_unit.' Total '.number_format($rowdetail->total,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').' 1231312321321312312312312312</li>';
            }

            $list_items .= '</ol>';

            $details[] = [
                'id'            => $row->id,
                'po_code'       => CustomHelper::encrypt($row->code),
                'po_no'         => $row->code,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'delivery_date' => date('d/m/Y',strtotime($row->required_date)),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'list_items'    => $list_items,
                'note'          => $row->note ? $row->note : '',
                'type'          => $row->getTable(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
            ];
        }

        return response()->json($details);
    }

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_type){
            foreach($request->arr_type as $key => $row){
                if($row == 'purchase_orders'){
                    $data = PurchaseOrder::find($request->arr_id[$key]);

                    $arrChecklist = [];

                    $list_items = '<ol>';

                    foreach($data->purchaseOrderDetail as $key => $rowdetail){
                        $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                        $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                        $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                        $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.number_format($rowdetail->qty,3,',','.').' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
                    }

                    $list_items .= '</ol>';

                    $details[] = [
                        'id'            => $data->id,
                        'po_code'       => CustomHelper::encrypt($data->code),
                        'po_no'         => $data->code,
                        'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                        'delivery_date' => date('d/m/Y',strtotime($data->delivery_date)),
                        'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                        'list_items'    => $list_items,
                        'note'          => $data->note ? $data->note : '',
                        'type'          => $data->getTable(),
                        'total'         => number_format($data->total,2,',','.'),
                        'tax'           => number_format($data->tax,2,',','.'),
                        'wtax'          => number_format($data->wtax,2,',','.'),
                        'checklist'     => $arrChecklist,
                        'payment_type'  => '',
                    ];
                }elseif($row == 'fund_requests'){
                    $data = FundRequest::find($request->arr_id[$key]);
                    $list_items = '<ol>';

                    $arrChecklist = [];

                    foreach($data->checklistDocumentList as $row){
                        $arrChecklist[] = [
                            'id'    => $row->checklist_document_id,
                            'title' => $row->checklistDocument->title,
                            'note'  => $row->note ? $row->note : '',
                        ];
                    }

                    foreach($data->fundRequestDetail as $key => $rowdetail){
                        $item_unit = $rowdetail->unit->code;
                        $details[] = [
                            'id'            => $rowdetail->id,
                            'po_code'       => CustomHelper::encrypt($data->code),
                            'po_no'         => $data->code,
                            'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                            'delivery_date' => date('d/m/Y',strtotime($data->required_date)),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'list_items'    => $rowdetail->note.' Qty : '.number_format($rowdetail->qty,3,',','.').' '.$item_unit,
                            'note'          => $rowdetail->note ? $rowdetail->note : '',
                            'type'          => $rowdetail->getTable(),
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'checklist'     => $arrChecklist,
                            'payment_type'  => $data->payment_type,
                        ];
                    }
                }
            }
        }
        
        $response['details'] = $details;

        return response()->json($response);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'type',
            'document',
            'post_date',
            'top',
            'currency_id',
            'currency_rate',
            'note',
            'subtotal',
            'discount',
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

        $total_data = PurchaseDownPayment::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
                                $query->whereHas('purchaseOrder',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
                                $query->whereHas('purchaseOrder',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
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
                    $val->supplier->name,
                    $val->company->name,
                    $val->type(),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->top,
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
           /*  'code'			            => $request->temp ? ['required', Rule::unique('purchase_down_payments', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:purchase_down_payments,code',
			 */'supplier_id' 				=> 'required',
			'type'                      => 'required',
            'top'                       => 'required',
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'subtotal'                  => 'required',
            'note'                      => 'required',
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            /* 'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.', */
            'code.unique'                       => 'Kode telah dipakai',
			'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
			'type.required'                     => 'Tipe tidak boleh kosong',
            'top.required'                      => 'TOP tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'subtotal.required'                 => 'Subtotal tidak boleh kosong.',
            'note.required'                     => 'Keterangan tidak boleh kosong.',
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

            if($request->arr_code){
                foreach($request->arr_code as $key => $row){
                    if($request->arr_type[$key] == 'purchase_orders'){
                        $po = PurchaseOrder::find($row);
                        $bobot = str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])) / $po->grandtotal;
                        $total += $bobot * $po->total;
                        $tax += $bobot * $po->tax;
                        $wtax += $bobot * $po->wtax;
                    }elseif($request->arr_type[$key] == 'fund_request_details'){
                        $fr = FundRequestDetail::find($row);
                        $bobot = str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])) / $fr->grandtotal;
                        $total += $bobot * $fr->total;
                        $tax += $bobot * $fr->tax;
                        $wtax += $bobot * $fr->wtax;
                    }
                }
            }

            $subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
            $discount = str_replace(',','.',str_replace('.','',$request->discount));

            $grandtotal = $subtotal - $discount;

            if($grandtotal <= 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Grandtotal tidak boleh kurang dari sama dengan 0.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Purchase Down Payment telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        CustomHelper::removeDeposit($query->account_id,$query->grandtotal);

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/purchase_down_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->supplier_id;
                        $query->type = $request->type;
                        $query->company_id = $request->company_id;
                        $query->document = $document;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->subtotal = round($subtotal,2);
                        $query->discount = $discount;
                        $query->total = round($total,2);
                        $query->tax = round($tax,2);
                        $query->wtax = round($wtax,2);
                        $query->grandtotal = round($grandtotal,2);
                        $query->status = '1';
                        $query->top = $request->top;

                        $query->save();

                        foreach($query->purchaseDownPaymentDetail as $row){
                            $row->delete();
                        }

                        $query->checklistDocumentList()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status AP Down Payment sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=PurchaseDownPayment::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = PurchaseDownPayment::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->supplier_id,
                        'type'	                    => $request->type,
                        'company_id'                => $request->company_id,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_down_payments') : NULL,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'note'                      => $request->note,
                        'subtotal'                  => round($subtotal,2),
                        'discount'                  => $discount,
                        'total'                     => round($total,2),
                        'tax'                       => round($tax,2),
                        'wtax'                      => round($wtax,2),
                        'grandtotal'                => round($grandtotal,2),
                        'status'                    => '1',
                        'top'                       => $request->top,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    if($request->arr_code){
                        foreach($request->arr_code as $key => $row){
                            PurchaseDownPaymentDetail::create([
                                'purchase_down_payment_id'      => $query->id,
                                'purchase_order_id'             => $request->arr_type[$key] == 'purchase_orders' ? $row : NULL,
                                'fund_request_detail_id'        => $request->arr_type[$key] == 'fund_request_details' ? $row : NULL,
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                          => $request->arr_note[$key]
                            ]);
                        }
                    }

                    if($request->arr_checklist_box){
                        foreach($request->arr_checklist_box as $key => $row){
                            ChecklistDocumentList::create([
                                'checklist_document_id'         => $row,
                                'lookable_type'                 => $query->getTable(),
                                'lookable_id'                   => $query->id,
                                'value'                         => '1',
                                'note'                          => $request->arr_checklist_note[$key],
                            ]);
                        }
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('purchase_down_payments',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'Pengajuan AP Down Payment No. '.$query->code,$query->note,session('bo_id'));                

                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase order down payment.');

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
        $menu = $this->menu;

        $data   = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">PO/FR No.</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Tgl.Kirim/Tgl.Dipakai</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Total DP</th>
                            </tr>
                        </thead><tbody>';
        $totals=0;
        $totalnominal=0;
        if(count($data->purchaseDownPaymentDetail) > 0){
            foreach($data->purchaseDownPaymentDetail as $key => $row){
                if($row->purchaseOrder()->exists()){
                    $totals+=$row->purchaseOrder->grandtotal;
                    $totalnominal+=$row->nominal;
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->purchaseOrder->code.'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->post_date)).'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)).'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="right-align">'.number_format($row->purchaseOrder->grandtotal,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }
                if($row->fundRequestDetail()->exists()){
                    $totals+=$row->fundRequestDetail->grandtotal;
                    $totalnominal+=$row->nominal;
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->fundRequestDetail->fundRequest->code.'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->post_date)).'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->required_date)).'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="right-align">'.number_format($row->fundRequestDetail->grandtotal,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }
            }
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="5"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalnominal, 2, ',', '.') . '</td>
            </tr>  
        ';
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="8">Data referensi purchase tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div><div class="col s12 mt-2"><h6>Daftar Lampiran</h6>';

        foreach($menu->checklistDocument as $row){
            $rowceklist = $row->checkDocument($data->getTable(),$data->id);
            $string .= '<label style="margin: 0 5px 0 0;">
            <input class="validate" required="" type="checkbox" value="{{ $row->id }}" '.($rowceklist ? 'checked' : '').'>
            <span>'.$row->title.' ('.$row->type().')'.'</span>
            '.($rowceklist ? $rowceklist->note : '').'
            </label>';
        }

        $string .= '</div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pdp){
            $data = [
                'title'     => 'Print Purchase Down Payment',
                'data'      => $pdp
            ];

            return view('admin.approval.purchase_down_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $pdp['code_place_id'] = substr($pdp->code,7,2);
        $pdp['supplier_name'] = $pdp->supplier->name;
        $pdp['subtotal'] = number_format($pdp->subtotal,2,',','.');
        $pdp['discount'] = number_format($pdp->discount,2,',','.');
        $pdp['total'] = number_format($pdp->total,2,',','.');
        $pdp['tax'] = number_format($pdp->tax,2,',','.');
        $pdp['wtax'] = number_format($pdp->wtax,2,',','.');
        $pdp['grandtotal'] = number_format($pdp->grandtotal,2,',','.');
        $pdp['is_include_tax'] = $pdp->is_include_tax ? $pdp->is_include_tax : '0';
        $pdp['currency_rate'] = number_format($pdp->currency_rate,2,',','.');

        $arr = [];
        $arrChecklist = [];

        foreach($pdp->checklistDocumentList as $row){
            $arrChecklist[] = [
                'id'    => $row->checklist_document_id,
                'note'  => $row->note ? $row->note : '',
            ];
        }

        foreach($pdp->purchaseDownPaymentDetail as $row){

            $list_items = '<ol>';

            if($row->purchaseOrder()->exists()){
                foreach($row->purchaseOrder->purchaseOrderDetail as $key => $rowdetail){
                    $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                    $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                    $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                    $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.number_format($rowdetail->qty,3,',','.').' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
                }
            }elseif($row->fundRequestDetail()->exists()){
                
            }

            $list_items .= '</ol>';

            $arr[] = [
                'id'                        => $row->purchase_order_id ?? $row->fund_request_detail_id,
                'type'                      => $row->purchase_order_id ? $row->purchaseOrder->getTable() : $row->fundRequestDetail->getTable(),
                'purchase_order_id'         => $row->purchase_order_id ?? $row->fund_request_detail_id,
                'purchase_order_code'       => $row->purchaseOrder()->exists() ? $row->purchaseOrder->code : $row->fundRequestDetail->fundRequest->code,
                'purchase_order_encrypt'    => $row->purchaseOrder()->exists() ? CustomHelper::encrypt($row->purchaseOrder->code) : CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                'post_date'                 => $row->purchaseOrder()->exists() ? date('d/m/Y',strtotime($row->purchaseOrder->post_date)) : date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->post_date)),
                'delivery_date'             => $row->purchaseOrder()->exists() ? date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)) : date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->required_date)),
                'note'                      => $row->note ? $row->note : '',
                'total'                     => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->grandtotal,2,',','.') : number_format($row->fundRequestDetail->grandtotal,2,',','.'),
                'tax'                       => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->tax,2,',','.') : number_format($row->fundRequestDetail->tax,2,',','.'),
                'wtax'                      => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->wtax,2,',','.') : number_format($row->fundRequestDetail->wtax,2,',','.'),
                'grandtotal'                => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->grandtotal,2,',','.') : number_format($row->fundRequestDetail->grandtotal,2,',','.'),
                'total_dp'                  => number_format($row->nominal,2,',','.'),
                'list_items'                => $list_items,
            ];
        }

        $pdp['details'] = $arr;
        $pdp['checklist'] = $arrChecklist;
        				
		return response()->json($pdp);
    }

    public function voidStatus(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada Payment Request / A/P Invoice sebagai DP.'
                ];
            }else{
                
                CustomHelper::removeDeposit($query->account_id,$query->total);
                CustomHelper::removeApproval('purchase_down_payments',$query->id);
                CustomHelper::removeJournal('purchase_down_payments',$query->id);

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase order down payment data');
    
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'AP Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
    
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
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

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

            CustomHelper::removeDeposit($query->account_id,$query->grandtotal);
            CustomHelper::removeApproval('purchase_down_payments',$query->id);

            $query->purchaseDownPaymentDetail()->delete();

            activity()
                ->performedOn(new PurchaseOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase order down payment data');

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
            'title' => 'AP DOWN PAYMENT REPORT',
            'data' => PurchaseDownPayment::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($request){
                                $query->whereHas('purchaseOrder',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%");
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier){
                    $query->whereIn('account_id',$request->supplier);
                }
                
                if($request->company){
                    $query->where('company_id',$request->company);
                }          
                
                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
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
         
        $pdf = PDF::loadView('admin.print.purchase.down_payment', $data)->setPaper('a5', 'landscape');

        $content = $pdf->download()->getOriginalContent();
        $randomString = Str::random(10); 


        $filePath = 'public/pdf/' . $randomString . '.pdf';
        

        Storage::put($filePath, $content);
        
        $document_po = asset(Storage::url($filePath));
        $var_link=$document_po;


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
                $pr = PurchaseDownPayment::where('code',$row)->first();
                
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
                    $pdf = Pdf::loadView('admin.print.purchase.down_payment_individual', $data)->setPaper('a5', 'landscape');
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

    public function printIndividual(Request $request,$id){
        
        $pr = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Downpayment',
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
             
            $pdf = Pdf::loadView('admin.print.purchase.down_payment_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
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
                        $query = PurchaseDownPayment::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Purchase DownPayment',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.down_payment_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = PurchaseDownPayment::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Purchase DownPayment',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.down_payment_individual', $data)->setPaper('a5', 'landscape');
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';

		return Excel::download(new ExportPurchaseDownPayment($post_date,$end_date,$mode), 'purchase_down_payment_'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        $data_id_good_scale = [];
        $data_id_good_issue = [];
        $data_id_mr = [];
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_inventory_transfer_out=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];
        $data_id_pyrcs=[];
        $data_id_gir = [];
        $data_id_cb  =[];
        $data_id_frs  =[];
        $data_id_op=[];

        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_hand_over_invoice = [];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_id_mo_delivery_process=[];
        $data_id_mo_receipt = [];
        $data_incoming_payment=[];
        $data_id_hand_over_receipt=[];
        
        $data_go_chart=[];
        $data_link=[];


        if($query) {
           
            $data_purchase_dp = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[]=$data_purchase_dp;
            $data_id_dp[]=$query->id;
            foreach($query->purchaseDownPaymentDetail as $row){
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
                        'to'=>$query->code,
                        'string_link'=>$row->purchaseOrder->code.$query->code,
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
                                'string_link'=>$po_detail->purchaseRequestDetail->purchaseRequest->code.$row->purchaseOrder->code,
                            ];
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
                                $data_id_gr[]=$good_receipt_detail->goodReceipt->id;
            
                            }
                        }
                    }
                     

                }
                
            }


            foreach($query->purchaseInvoiceDp as $purchase_invoicedp){
                $data_purchase_invoice = [
                    "name"=>$purchase_invoicedp->purchaseInvoice->code,
                    "key" => $purchase_invoicedp->purchaseInvoice->code,
                    'properties'=> [
                        ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                        ],
                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                ];
                $data_go_chart[]=$data_purchase_invoice;
                $data_link[]=[
                    'from'=>$query->code,
                    'to'=>$purchase_invoicedp->purchaseInvoice->code,
                    'string_link'=>$query->code.$purchase_invoicedp->purchaseInvoice->code,
                ];
                $data_id_invoice[]=$purchase_invoicedp->purchaseInvoice->id;
            }

            $finished_data_id_gr=[];
            $finished_data_id_gscale=[];
            $finished_data_id_greturns=[];
            $finished_data_id_invoice=[];
            $finished_data_id_pyrs=[];
            $finished_data_id_pyrcs=[];
            $finished_data_id_dp=[];
            $finished_data_id_memo=[];
            $finished_data_id_gissue=[];
            $finished_data_id_lc=[];
            $finished_data_id_invetory_to=[];
            $finished_data_id_po=[];
            $finished_data_id_pr=[];
            $finished_data_id_mr=[];
            $finished_data_id_gir=[];
            $finished_data_id_cb=[];
            $finished_data_id_frs=[];
            $added = true;
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    if(!in_array($gr_id, $finished_data_id_gr)){
                        $finished_data_id_gr[]= $gr_id; 
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
                            //$data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            if(!in_array($good_receipt_detail->purchaseOrderDetail->purchaseOrder->id, $data_id_po)){
                                $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                                $added = true; 
                            }
    
                            if($good_receipt_detail->goodReturnPODetail()->exists()){
                                foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                    $good_return_tempura =[
                                        "name"=> $goodReturnPODetail->goodReturnPO->code,
                                        "key" => $goodReturnPODetail->goodReturnPO->code,
                                        
                                        'properties'=> [
                                            ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                        ],
                                        'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
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
                                   
                                    if(!in_array($landed_cost_detail->landedCost->id, $data_id_lc)){
                                        $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                        $added = true; 
                                    }
                                   
                                    
                                    
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
    
                            if($good_receipt_detail->goodScaleDetail()->exists()){
                                $data_gscale = [
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$good_receipt_detail->goodScaleDetail->goodScale->post_date],
                                            ['name'=> "Vendor  : ".$good_receipt_detail->goodScaleDetail->goodScale->supplier->name],
                                            ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodScaleDetail->goodScale->grandtotal,2,',','.')]
                                        ],
                                        'key'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'name'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($good_receipt_detail->goodScaleDetail->goodScale->code),
                                    ];
                                    $data_go_chart[]=$data_gscale;
                                    $data_link[]=[
                                        'from'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'to'=>$query_gr->code,
                                        'string_link'=>$good_receipt_detail->goodScaleDetail->goodScale->code.$query_gr->code
                                    ];
                                    $data_id_good_scale[]= $good_receipt_detail->goodScaleDetail->goodScale->id; 
                                
                            }
    
                        }
                    }
                    
                }

                foreach($data_id_cb as $cb_id){
                    if(!in_array($cb_id,$finished_data_id_cb)){
                        $finished_data_id_cb[]= $cb_id; 
                        $query_cb = CloseBill::find($cb_id);
                        foreach($query_cb->closeBillDetail as $row_bill_detail){
                            $outgoingpaymnet = [
                                'key'   => $row_bill_detail->outgoingPayment->code,
                                "name"  => $row_bill_detail->outgoingPayment->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->outgoingPayment->post_date))],
                                    ['name'=> "Nominal: Rp".number_format($row_bill_detail->outgoingPayment->grandtotal,2,',','.')]
                                ],
                                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_bill_detail->outgoingPayment->code),
                                "title" => $row_bill_detail->outgoingPayment->code,
                            ];
                            $data_go_chart[]=$outgoingpaymnet;
                            $data_link[]=[
                                'from'=>$row_bill_detail->outgoingPayment->code,
                                'to'=>$query->code,
                                'string_link'=>$row_bill_detail->outgoingPayment->code.$query->code,
                            ];
                            if(!in_array($row_bill_detail->outgoingPayment->id, $data_id_op)){
                                $data_id_op[]= $row_bill_detail->outgoingPayment->id; 
                                $added = true; 
                            } 
                                
                        }

                    }
                }

                foreach($data_id_good_scale as $gs_id){
                    if(!in_array($gs_id, $finished_data_id_gscale)){
                        $finished_data_id_gscale[]=$gs_id;
                        $query_gs = GoodScale::where('id',$gs_id)->first();
                        
                        foreach($query_gs->goodScaleDetail as $data_gs){
                            if($data_gs->goodReceiptDetail->exists()){
                                $gr = [
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$data_gs->goodReceiptDetail->goodReceipt->post_date],
                                        ['name'=> "Vendor  : ".$data_gs->goodReceiptDetail->goodReceipt->supplier->name],
                                        // ['name'=> "Nominal : Rp.:".number_format($data_gs->goodReceiptDetail->goodReceipt->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'name'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($data_gs->goodReceiptDetail->goodReceipt->code),
                                ];
        
                                $data_go_chart[]=$gr;
                                $data_link[]=[
                                    'from'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'to'=>$query_gs->code,
                                    'string_link'=>$data_gs->goodReceiptDetail->goodReceipt->code.$query_gs->code
                                ];
                                if(!in_array($data_gs->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
                                    $added = true; 
                                }
                                // $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
        
                            }
                        }
                    }
                }

                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    if(!in_array($good_return_id, $finished_data_id_greturns)){
                        $finished_data_id_greturns[]=$good_return_id;
                        $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                        foreach($query_return->goodReturnPODetail as $good_return_detail){
                            $data_good_receipt = [
                                "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                    
                                'properties'=> [
                                    ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                                ],
                                'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
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
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    if(!in_array($invoice_id, $finished_data_id_invoice)){
                        $finished_data_id_invoice[]=$invoice_id;
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
                                    $data_id_po[]= $row_po->id;  
                                        
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
                                        // ['name'=> "Nominal : Rp.".number_format($row->lookable->goodReceipt->grandtotal,2,',','.')]
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
                            if($row->landedCostFeeDetail()){
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
                                    'from'=>$row->lookable->landedCost->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row->lookable->landedCost->code.$query_invoice->code,
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
                                                    ['name'=> "User :".$row_pyr_detail->lookable->account->name],
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

                                            if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                                $data_id_frs[] = $row_pyr_detail->lookable->id;
                                                $added = true; 
                                            } 

                                            
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
                                // $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                if(!in_array($row_pyr_detail->paymentRequest->id, $data_id_pyrs)){
                                    $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                    $added = true; 
                                
                                }    
                                
                                if($row_pyr_detail->fundRequest()){
                                    $data_fund_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                            ['name'=> "User :".$row_pyr_detail->lookable->account->name],
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
                                    if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                        $data_id_frs[] = $row_pyr_detail->lookable->id;
                                        $added = true; 
                                    }           
                                    
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
                }

                foreach($data_id_pyrs as $payment_request_id){
                    if(!in_array($payment_request_id, $finished_data_id_pyrs)){
                        $finished_data_id_pyrs[]=$payment_request_id;
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
                                        ['name'=> "User :".$row_pyr_detail->lookable->account->name],
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

                                if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                    $data_id_frs[] = $row_pyr_detail->lookable->id;
                                    $added = true; 
                                } 
                                
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
                                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                        'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                    ];
                        
                                    $data_go_chart[]=$data_pyrc_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_cross->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                    ];
                                    if(!in_array($row_pyr_cross->id, $data_id_pyrcs)){
                                        $data_id_pyrcs[] = $row_pyr_cross->id;
                                        
                                    }
                                }

                                
                            }
                        }
                    }
                    
                }

                foreach($data_id_pyrcs as $payment_request_cross_id){
                    
                    if(!in_array($payment_request_cross_id, $finished_data_id_pyrcs)){
                        $finished_data_id_pyrcs[]=$payment_request_cross_id;
                        $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                        if($query_pyrc->paymentRequest()->exists()){
                            $data_pyr_tempura = [
                                'key'   => $query_pyrc->paymentRequest->code,
                                "name"  => $query_pyrc->paymentRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->post_date))],
                                ],
                                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                                "title" =>$query_pyrc->paymentRequest->code,
                            ];
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_pyrc->lookable->code,
                                'to'=>$query_pyrc->paymentRequest->code,
                                'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                            ];
                            
                            if(!in_array($query_pyrc->paymentRequest->id, $data_id_pyrs)){
                                $data_id_pyrs[] = $query_pyrc->paymentRequest->id;
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
                                'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                            ];
        
                            $data_go_chart[]=$outgoing_tempura;
                            $data_link[]=[
                                'from'=>$query_pyrc->lookable->code,
                                'to'=>$query_pyrc->paymentRequest->code,
                                'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                            ];
                        }
                    }
                }
                
                foreach($data_id_dp as $downpayment_id){
                    
                    if(!in_array($downpayment_id, $finished_data_id_dp)){
                        $finished_data_id_dp[]=$downpayment_id;
                        
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
                            
                            if($row->fundRequest()->exists()){
                                $fr=[
                                    "name"=>$row->fundRequest->code,
                                    "key" => $row->fundRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->fundRequest->post_date],
                                        ['name'=> "User :".$row->fundRequest->account->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row->fundRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row->fundRequest->code),
                                ];
                            
                                $data_go_chart[]=$fr;
                                $data_link[]=[
                                    'from'=>$row->fundRequest->code,
                                    'to'=>$query_dp->code,
                                    'string_link'=>$row->fundRequest->code.$query_dp->code,
                                ];
                                if(!in_array($row->fundRequest->id, $data_id_frs)){
                                    $data_id_frs[] = $row->fundRequest->id;
                                    $added = true; 
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

                        if($query_dp->hasPaymentRequestDetail()->exists()){
                            
                            foreach($query_dp->hasPaymentRequestDetail as $row_pyr_detail){
                                $data_pyr_tempura=[
                                    "name"=>$row_pyr_detail->paymentRequest->code,
                                    "key" => $row_pyr_detail->paymentRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')],
                                        ],
                                    'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),           
                                ];
                                $data_go_chart[]=$data_pyr_tempura;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$query_dp->code.$row_pyr_detail->paymentRequest->code,
                                ];

                                if(!in_array($query_dp->id, $data_id_dp)){
                                    $data_id_dp[] = $query_dp->id;
                                    $added=true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_memo as $memo_id){
                    if(!in_array($memo_id, $finished_data_id_memo)){
                        $finished_data_id_memo []= $memo_id;
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
                }
                
                foreach($data_id_good_issue as $good_issue_id){
                    if(!in_array($good_issue_id, $finished_data_id_gissue)){
                        $finished_data_id_gissue[]=$good_issue_id;
                        $query_good_issue = GoodIssue::find($good_issue_id);
                        foreach($query_good_issue->goodIssueDetail as $data_detail_good_issue){
                            if($data_detail_good_issue->materialRequestDetail()){
                                $material_request_tempura = [
                                    "key" => $data_detail_good_issue->lookable->materialRequest->code,
                                    "name" => $data_detail_good_issue->lookable->materialRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_detail_good_issue->lookable->materialRequest->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($data_detail_good_issue->lookable->materialRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/material_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->materialRequest->code),
                                ];

                                $data_go_chart[]=$material_request_tempura;
                                $data_link[]=[
                                    'from'=>$data_detail_good_issue->lookable->materialRequest->code,
                                    'to'=>$query_good_issue->code,
                                    'string_link'=>$data_detail_good_issue->lookable->materialRequest->code.$query_good_issue->code,
                                ];
                                $data_id_mr[] = $data_detail_good_issue->lookable->materialRequest->id;
                            }

                            if($data_detail_good_issue->purchaseOrderDetail()->exists()){
                                foreach($data_detail_good_issue->purchaseOrderDetail as $data_purchase_order_detail){
                                    $po_tempura = [
                                        "key" => $data_purchase_order_detail->purchaseOrder->code,
                                        "name" => $data_purchase_order_detail->purchaseOrder->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$data_purchase_order_detail->purchaseOrder->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($data_purchase_order_detail->purchaseOrder->grandtotal,2,',','.')],
                                        ],
                                        'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($data_purchase_order_detail->purchaseOrder->code),
                                    ];
        
                                    $data_go_chart[]=$material_request_tempura;
                                    $data_link[]=[
                                        'from'=>$query_good_issue->code,
                                        'to'=>$data_purchase_order_detail->purchaseOrder->code,
                                        'string_link'=>$query_good_issue->code.$data_purchase_order_detail->purchaseOrder->code,
                                    ];
                                    $data_id_po[] = $data_purchase_order_detail->purchaseOrder->id;
                                }
                            }
                            
                            if($data_detail_good_issue->goodIssueRequestDetail()){
                                $good_issue_request_tempura = [
                                    "key" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                    "name" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_detail_good_issue->lookable->goodIssueRequest->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($data_detail_good_issue->lookable->goodIssueRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_issue_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->goodIssueRequest->code),
                                ];

                                $data_go_chart[]=$good_issue_request_tempura;
                                $data_link[]=[
                                    'from'=>$data_detail_good_issue->lookable->goodIssueRequest->code,
                                    'to'=>$query_good_issue->code,
                                    'string_link'=>$data_detail_good_issue->lookable->goodIssueRequest->code.$query_good_issue->code,
                                ];
                                $data_id_gir[] = $data_detail_good_issue->lookable->goodIssueRequest->id;  
                            }
                        }
                    }
                }

                foreach($data_id_lc as $landed_cost_id){
                    if(!in_array($landed_cost_id, $finished_data_id_lc)){
                        $finished_data_id_lc[]=$landed_cost_id;
                        $query= LandedCost::find($landed_cost_id);
                        foreach($query->landedCostDetail as $lc_detail ){
                            if($lc_detail->goodReceiptDetail()){
                                $data_good_receipt = [
                                    "key" => $lc_detail->lookable->goodReceipt->code,
                                    'name'=> $lc_detail->lookable->goodReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                        // ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->goodReceipt->grandtotal,2,',','.')],
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
                                if(!in_array($lc_detail->lookable->landedCost->id,$data_id_lc)){
                                    $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                    $added = true;
                                }
                            
                                                
                            }//??
                            if($lc_detail->inventoryTransferOutDetail()){
                                $inventory_transfer_out = [
                                    "key" => $lc_detail->lookable->inventoryTransferOut->code,
                                    "name" => $lc_detail->lookable->inventoryTransferOut->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$lc_detail->lookable->inventoryTransferOut->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->inventoryTransferOut->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($lc_detail->lookable->inventoryTransferOut->code),
                                ];

                                $data_go_chart[]=$inventory_transfer_out;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$lc_detail->lookable->inventoryTransferOut->code,
                                    'string_link'=>$query->code.$lc_detail->lookable->inventoryTransferOut->code,
                                ];
                                $data_id_inventory_transfer_out[] = $lc_detail->lookable->inventoryTransferOut->id;
                                                
                            }
                        } // inventory transferout detail apakah perlu
                        if($query->landedCostFeeDetail()->exists()){
                            foreach($query->landedCostFeeDetail as $row_landedfee_detail){
                                foreach($row_landedfee_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $row_invoice_detail->purchaseInvoice->code,
                                        "name"  => $row_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                        
                                        ],
                                        'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query->code,
                                        'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                        'string_link'=>$query->code.$row_invoice_detail->purchaseInvoice->code
                                    ];
                                    if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                }
                            
                            }
                        }
                    }
                }

                foreach($data_id_inventory_transfer_out as $id_transfer_out){
                    if(!in_array($id_transfer_out, $finished_data_id_invetory_to)){
                        $finished_data_id_invetory_to[]=$id_transfer_out;
                        $query_inventory_transfer_out = InventoryTransferOut::find($id_transfer_out);
                        foreach($query_inventory_transfer_out->inventoryTransferOutDetail as $row_transfer_out_detail){
                            if($row_transfer_out_detail->landedCostDetail->exists()){
                                $lc_tempura = [
                                    "key" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    "name" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_transfer_out_detail->landedCostDetail->landedCost->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_transfer_out_detail->landedCostDetail->landedCost->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($row_transfer_out_detail->landedCostDetail->landedCost->code),
                                ];

                                $data_go_chart[]=$lc_tempura;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    'string_link'=>$query->code.$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                ];
                                if(!in_array($row_transfer_out_detail->landedCostDetail->landedCost->id,$data_id_lc)){
                                    $data_id_lc[] = $row_transfer_out_detail->landedCostDetail->landedCost->id;
                                    $added = true;
                                }
                            
                                    
                            }
                        }
                    }
                }

                foreach($data_id_frs as $fr_id){
                    if(!in_array($fr_id, $finished_data_id_frs)){
                        $finished_data_id_frs[]=$fr_id;
                        $query_fr = FundRequest::find($fr_id);

                        foreach($query_fr->fundRequestDetail as $row_fr_detail){
                            if($row_fr_detail->hasPaymentRequestDetail()->exists()){
                                foreach($row_fr_detail->hasPaymentRequestDetail as $row_pyr_detail){
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
                                        'from'=>$query_fr->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pi->purchaseDownPayment->code.$row_pyr_detail->paymentRequest->code,
                                    ];
                                    if(!in_array($row_pyr_detail->paymentRequest->id,$data_id_pyrs)){
                                        $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                        $added = true;
                                    } 
                                   
                                }
                            }
                            
                            if($row_fr_detail->purchaseInvoiceDetail()->exists()){
                                foreach($row_fr_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $row_invoice_detail->purchaseInvoice->code,
                                        "name"  => $row_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                        
                                        ],
                                        'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query_fr->code,
                                        'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                        'string_link'=>$query_fr->code.$row_invoice_detail->purchaseInvoice->code
                                    ];
                                    if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                }
                            }
                        }

                        if($query_fr->purchaseDownPaymentDetail()->exists()){
                            foreach($query_fr->purchaseDownPaymentDetail as $row_dp_detail){
                                $data_apdp_tempura = [
                                    'key'   => $row_dp_detail->purchaseDownPayment->code,
                                    "name"  => $row_dp_detail->purchaseDownPayment->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                        ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                    ],
                                    'url'   =>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                                ];
                                $data_go_chart[]=$data_apdp_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_fr->code,
                                    'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                    'string_link'=>$query_fr->code.$row_dp_detail->purchaseDownPayment->code,
                                ];
                                if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                    $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                    $added = true;
                                } 
                            }
                        }

                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    if(!in_array($po_id, $finished_data_id_po)){
                        $finished_data_id_po[]=$po_id;
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
                                            // ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
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
                            if($purchase_order_detail->goodIssueDetail()->exists()){
                                $good_issue_tempura=[
                                    'key'   => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                    "name"  => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_order_detail->goodIssueDetail->goodIssue->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($purchase_order_detail->goodIssueDetail->goodIssue->code),
                                ];
                        
                                $data_go_chart[]=$good_issue_tempura;
                                $data_link[]=[
                                    'from'=>$query_po->code,
                                    'to'=>$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                    'string_link'=>$query_po->code.$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                ];
                                
                                if(!in_array($purchase_order_detail->goodIssueDetail->goodIssue->id,$data_id_good_issue)){
                                    $data_id_good_issue[]=$purchase_order_detail->goodIssueDetail->goodIssue->id;
                                    $added = true;
                                }
                                
                            }
                            if($purchase_order_detail->purchaseInvoiceDetail()->exists()){
                                foreach($purchase_order_detail->purchaseInvoiceDetail as $purchase_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $purchase_invoice_detail->purchaseInvoice->code,
                                        "name"  => $purchase_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$purchase_invoice_detail->purchaseInvoice->post_date],
                                        
                                        ],
                                        'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $purchase_invoice_detail->purchaseInvoice->code,
                                        'to'    =>  $query_po->code,
                                        'string_link'=>$purchase_invoice_detail->purchaseInvoice->code.$query_po->code,
                                    ];
                                    if(!in_array($purchase_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$purchase_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                
                                }
                            }
                            if($purchase_order_detail->marketingOrderDeliveryProcess()->exists()){
                                
                                $data_marketing_order_delivery_process = [
                                    'key'   => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                    "name"  => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_order_detail->marketingOrderDeliveryProcess->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_order_detail->marketingOrderDeliveryProcess->code),
                                ];
                                $data_go_chart[]=$data_marketing_order_delivery_process;
                                $data_link[]=[
                                    'from'  =>  $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                    'to'    =>  $query_po->code,
                                    'string_link'=>$purchase_order_detail->marketingOrderDeliveryProcess->code.$query_po->code,
                                ];
                                if(!in_array($purchase_order_detail->marketingOrderDeliveryProcess->id,$data_id_mo_delivery_process)){
                                    $data_id_mo_delivery_process[]=$purchase_order_detail->marketingOrderDeliveryProcess->id;
                                    $added = true;
                                }
                                
                                
                            }
                            
                        }

                        if($query_po->purchaseDownPaymentDetail()->exists()){
                            
                            foreach($query_po->purchaseDownPaymentDetail as $row_dp_detail){
                                $data_apdp_tempura = [
                                    'key'   => $row_dp_detail->purchaseDownPayment->code,
                                    "name"  => $row_dp_detail->purchaseDownPayment->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                        ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                    ],
                                    'url'   =>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                                ];
                                $data_go_chart[]=$data_apdp_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_po->code,
                                    'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                    'string_link'=>$query_po->code.$row_dp_detail->purchaseDownPayment->code,
                                ];
                                if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                    $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                    $added = true;
                                } 
                            }
                        }
                    }

                }

                foreach($data_id_pr as $pr_id){
                    if(!in_array($pr_id, $finished_data_id_pr)){
                        $finished_data_id_pr[]=$pr_id;
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
                            if($purchase_request_detail->materialRequestDetail()){
                                $mr=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_request_detail->lookable->materialRequest->post_date],
                                        ['name'=> "Vendor  : ".$purchase_request_detail->lookable->materialRequest->user->name],
                                    ],
                                    'key'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'name'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'url'=>request()->root()."/admin/inventory/material_request?code=".CustomHelper::encrypt($purchase_request_detail->lookable->materialRequest->code),
                                ];
                                
                                $data_go_chart[]=$mr;
                                $data_link[]=[
                                    'from'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'to'=>$query_pr->code,
                                    'string_link'=>$purchase_request_detail->lookable->materialRequest->code.$query_pr->code,
                                ];
                                if(!in_array($purchase_request_detail->lookable->materialRequest->id,$data_id_mr)){
                                    $data_id_mr[]= $purchase_request_detail->lookable->materialRequest->id;  
                                    $added = true;
                                }
                            
                                
                            }
                        }
                    }
                }

                foreach($data_id_gir as $gir_id){
                    if(!in_array($gir_id, $finished_data_id_gir)){
                        $finished_data_id_gir[]=$gir_id;
                        $query_good_issue_request = GoodIssueRequest::find($gir_id);
                        foreach($query_good_issue_request->goodIssueRequestDetail as $row_gird){
                            if($row_gird->goodIssueDetail()->exists()){
                                foreach($row_gird->goodIssueDetail as $good_issue_detail){
                                    $good_issue_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                        ],
                                        'key'=>$good_issue_detail->goodIssue->code,
                                        'name'=>$good_issue_detail->goodIssue->code,
                                        'url'=>request()->root()."/admin/inventory/good_issue_request?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                    ];
        
                                    $data_go_chart[]=$good_issue_tempura;
                                    $data_link[]=[
                                        'from'=>$query_good_issue_request->code,
                                        'to'=>$good_issue_detail->goodIssue->code,
                                        'string_link'=>$query_good_issue_request->code.$good_issue_detail->goodIssue->code,
                                    ];
                                    if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                        $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                        $added = true;
                                    }
                                }
                            }
                            
                        }

                    }
                }

                foreach($data_id_mr as $mr_id){
                    if(!in_array($mr_id, $finished_data_id_mr)){
                        $finished_data_id_mr[]=$mr_id;
                        $query_material_request = MaterialRequest::find($mr_id);
                        foreach($query_material_request->materialRequestDetail as $row_material_request_detail){
                            if($row_material_request_detail->purchaseRequestDetail()->exists()){
                            
                                foreach($row_material_request_detail->purchaseRequestDetail as $row_purchase_request_detail){
                                    $pr_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$row_purchase_request_detail->purchaseRequest->post_date],
                                            ['name'=> "Vendor  : ".$row_purchase_request_detail->purchaseRequest->user->name],
                                        ],
                                        'key'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'name'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_purchase_request_detail->purchaseRequest->code),
                                    ];
        
                                    $data_go_chart[]=$pr_tempura;
                                    $data_link[]=[
                                        'from'=>$query_material_request->code,
                                        'to'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'string_link'=>$query_material_request->code.$row_purchase_request_detail->purchaseRequest->code,
                                    ];
                                    if(!in_array($row_purchase_request_detail->purchaseRequest->id,$data_id_pr)){
                                        $data_id_pr[] = $row_purchase_request_detail->purchaseRequest->id;
                                        $added = true;
                                    }
                                }                     
                            
                            }
                            if($row_material_request_detail->goodIssueDetail()->exists()){
                            
                                foreach($row_material_request_detail->goodIssueDetail as $good_issue_detail){
                                    $good_issue_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                            ['name'=> "User  : ".$good_issue_detail->goodIssue->user->name],
                                        ],
                                        'key'=>$good_issue_detail->goodIssue->code,
                                        'name'=>$good_issue_detail->goodIssue->code,
                                        'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                    ];
        
                                    $data_go_chart[]=$good_issue_tempura;
                                    $data_link[]=[
                                        'from'=>$query_material_request->code,
                                        'to'=>$good_issue_detail->goodIssue->code,
                                        'string_link'=>$query_material_request->code.$good_issue_detail->goodIssue->code,
                                    ];
                                
                                    if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                        $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                        $added = true;
                                    }
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
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
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
		return Excel::download(new ExportOutstandingDP(), 'outstanding_purchase_down_payment'.uniqid().'.xlsx');
    }
}