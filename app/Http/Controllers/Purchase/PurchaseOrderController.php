<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Place;
use App\Models\UsedData;
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
use App\Models\PurchaseOrderDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseOrder;
use App\Models\User;
use App\Models\Tax;

class PurchaseOrderController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'         => 'Purchase Order',
            'content'       => 'admin.purchase.order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'supplier_id',
            'inventory_type',
            'purchasing_type',
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
            'note',
            'subtotal',
            'discount',
            'total',
            'tax',
            'grandtotal',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseOrder::count();
        
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
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->inventory_type){
                    $query->where('inventory_type',$request->inventory_type);
                }

                if($request->purchasing_type){
                    $query->where('purchasing_type',$request->type);
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

            })
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
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->inventory_type){
                    $query->where('inventory_type',$request->inventory_type);
                }

                if($request->purchasing_type){
                    $query->where('purchasing_type',$request->type);
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
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->supplier->name,
                    $val->inventoryType(),
                    $val->purchasingType(),
                    $val->shippingType(),
                    $val->company->name,
                    $val->document_no,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->paymentType(),
                    $val->payment_term,
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->delivery_date)),
                    $val->receiver_name,
                    $val->receiver_address,
                    $val->receiver_phone,
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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

    public function getPurchaseRequest(Request $request){
        $data = PurchaseRequest::where('id',$request->id)->where('status','2')->first();

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Request '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($data->hasBalance()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Purchase Order');
                $details = [];
                foreach($data->purchaseRequestDetail as $row){
                    $details[] = [
                        'purchase_request_detail_id'    => $row->id,
                        'item_id'                       => $row->item_id,
                        'item_name'                     => $row->item->code.' - '.$row->item->name,
                        'old_prices'                    => $row->item->oldPrices($this->dataplaces),
                        'unit'                          => $row->item->buyUnit->code,
                        'qty'                           => number_format($row->qtyBalance(),3,',','.'),
                        'note'                          => $row->note,
                        'warehouse_name'                => $row->warehouse->code.' - '.$row->warehouse->name,
                        'warehouse_id'                  => $row->warehouse_id,
                        'place_id'                      => $row->place_id,
                        'department_id'                 => $row->department_id,
                    ];
                }

                $data['details'] = $details;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada purchase request '.$data->code.' telah digunakan pada purchase order.';
            }
        }

        return response()->json($data);
    }

    public function create(Request $request){
        if($request->inventory_type == '1'){
            $validation = Validator::make($request->all(), [
                'supplier_id' 				=> 'required',
                'inventory_type'			=> 'required',
                'purchasing_type'			=> 'required',
                'shipping_type'		        => 'required',
                'payment_type'		        => 'required',
                'payment_term'		        => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'post_date'                 => 'required',
                'delivery_date'             => 'required',
                'receiver_name'             => 'required',
                'receiver_address'          => 'required',
                'receiver_phone'            => 'required',
                'arr_item'                  => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'arr_place'                 => 'required|array',
                'arr_warehouse'             => 'required|array',
                'discount'                  => 'required',
            ], [
                'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
                'inventory_type.required' 			=> 'Tipe persediaan/jasa tidak boleh kosong.',
                'purchasing_type.required' 			=> 'Tipe PO tidak boleh kosong.',
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
                'arr_place.required'                => 'Site tidak boleh kosong.',
                'arr_place.array'                   => 'Site harus array.',
                'arr_warehouse.required'            => 'Gudang tidak boleh kosong.',
                'arr_warehouse.array'               => 'Gudang harus array.',
                'discount.required'                 => 'Diskon akhir tidak boleh kosong.'
            ]);
        }elseif($request->inventory_type == '2'){
            $validation = Validator::make($request->all(), [
                'supplier_id' 				=> 'required',
                'inventory_type'			=> 'required',
                'purchasing_type'			=> 'required',
                'shipping_type'		        => 'required',
                'payment_type'		        => 'required',
                'payment_term'		        => 'required',
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'post_date'                 => 'required',
                'delivery_date'             => 'required',
                'receiver_name'             => 'required',
                'receiver_address'          => 'required',
                'receiver_phone'            => 'required',
                'arr_coa'                   => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'discount'                  => 'required',
            ], [
                'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
                'inventory_type.required' 			=> 'Tipe persediaan/jasa tidak boleh kosong.',
                'purchasing_type.required' 			=> 'Tipe PO tidak boleh kosong.',
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
                'discount.required'                 => 'Diskon akhir tidak boleh kosong.'
            ]);
        }

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Order telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){
                        if($request->has('document_po')) {
                            if(Storage::exists($query->document_po)){
                                Storage::delete($query->document_po);
                            }
                            $document = $request->file('document_po')->store('public/purchase_orders');
                        } else {
                            $document = $query->document_po;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->supplier_id;
                        $query->inventory_type = $request->inventory_type;
                        $query->purchasing_type = $request->purchasing_type;
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
                        $query->note = $request->note;
                        $query->subtotal = str_replace(',','.',str_replace('.','',$request->savesubtotal));
                        $query->discount = str_replace(',','.',str_replace('.','',$request->discount));
                        $query->total = str_replace(',','.',str_replace('.','',$request->savetotal));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->savetax));
                        $query->wtax = str_replace(',','.',str_replace('.','',$request->savewtax));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->savegrandtotal));
                        $query->receiver_name = $request->receiver_name;
                        $query->receiver_address = $request->receiver_address;
                        $query->receiver_phone = $request->receiver_phone;

                        $query->save();
                        
                        foreach($query->PurchaseOrderDetail as $row){
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
                    $query = PurchaseOrder::create([
                        'code'			            => PurchaseOrder::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->supplier_id,
                        'inventory_type'	        => $request->inventory_type,
                        'purchasing_type'	        => $request->purchasing_type,
                        'shipping_type'             => $request->shipping_type,
                        'company_id'                => $request->company_id,
                        'document_no'               => $request->document_no,
                        'document_po'               => $request->file('document_po') ? $request->file('document_po')->store('public/purchase_orders') : NULL,
                        'payment_type'              => $request->payment_type,
                        'payment_term'              => $request->payment_term,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'note'                      => $request->note,
                        'subtotal'                  => str_replace(',','.',str_replace('.','',$request->savesubtotal)),
                        'discount'                  => str_replace(',','.',str_replace('.','',$request->discount)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->savetotal)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->savetax)),
                        'wtax'                      => str_replace(',','.',str_replace('.','',$request->savewtax)),
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
                
                                $rowsubtotal = round($finalpricedisc3 * $qty,3);
        
                                $querydetail = PurchaseOrderDetail::create([
                                    'purchase_order_id'             => $query->id,
                                    'purchase_request_detail_id'    => $request->arr_purchase ? $request->arr_purchase[$key] : NULL,
                                    'item_id'                       => $row,
                                    'qty'                           => $qty,
                                    'price'                         => $price,
                                    'percent_discount_1'            => $disc1,
                                    'percent_discount_2'            => $disc2,
                                    'discount_3'                    => $disc3,
                                    'subtotal'                      => $rowsubtotal,
                                    'note'                          => $request->arr_note[$key] ? $request->arr_note[$key] : NULL,
                                    'is_tax'                        => $request->arr_tax[$key] > 0 ? '1' : NULL,
                                    'is_include_tax'                => $request->arr_is_include_tax[$key] == '1' ? '1' : '0',
                                    'percent_tax'                   => $request->arr_tax[$key],
                                    'is_wtax'                       => $request->arr_wtax[$key] > 0 ? '1' : NULL,
                                    'percent_wtax'                  => $request->arr_wtax[$key],
                                    'tax_id'                        => $request->arr_tax_id[$key],
                                    'wtax_id'                       => $request->arr_wtax_id[$key],
                                    'place_id'                      => $request->arr_place[$key],
                                    'department_id'                 => $request->arr_department[$key],
                                    'warehouse_id'                  => $request->arr_warehouse[$key] ? $request->arr_warehouse[$key] : NULL,
                                ]);
                                
                                if($querydetail->purchaseRequestDetail()->exists()){
                                    CustomHelper::removeUsedData('purchase_requests',$querydetail->purchaseRequestDetail->purchase_request_id);
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
                
                                $rowsubtotal = round($finalpricedisc3 * $qty,3);
        
                                $querydetail = PurchaseOrderDetail::create([
                                    'purchase_order_id'             => $query->id,
                                    'coa_id'                        => $row,
                                    'qty'                           => $qty,
                                    'price'                         => $price,
                                    'percent_discount_1'            => $disc1,
                                    'percent_discount_2'            => $disc2,
                                    'discount_3'                    => $disc3,
                                    'subtotal'                      => $rowsubtotal,
                                    'note'                          => $request->arr_note[$key],
                                    'is_tax'                        => $request->arr_tax[$key] > 0 ? '1' : NULL,
                                    'is_include_tax'                => $request->arr_is_include_tax[$key] == '1' ? '1' : '0',
                                    'percent_tax'                   => $request->arr_tax[$key],
                                    'is_wtax'                       => $request->arr_wtax[$key] > 0 ? '1' : NULL,
                                    'percent_wtax'                  => $request->arr_wtax[$key],
                                    'tax_id'                        => $request->arr_tax_id[$key],
                                    'wtax_id'                       => $request->arr_wtax_id[$key],
                                    'place_id'                      => $request->arr_place[$key],
                                    'department_id'                 => $request->arr_department[$key]
                                ]);
                            }
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('purchase_orders',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_orders',$query->id,'Pengajuan Purchase Order No. '.$query->code,$query->note,session('bo_id'));

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
        $data   = PurchaseOrder::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="14">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item/Coa Biaya</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Price</th>
                                <th class="center-align">Discount 1 (%)</th>
                                <th class="center-align">Discount 2 (%)</th>
                                <th class="center-align">Discount 3 (Rp)</th>
                                <th class="center-align">Subtotal</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Site</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Referensi</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->purchaseOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.($row->item_id ? $row->item->name : $row->coa->name).'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.($row->item_id ? $row->item->buyUnit->code : '-').'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,2,',','.').'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.$row->place->name.'</td>
                <td class="center-align">'.$row->department->name.'</td>
                <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                <td class="center-align">'.($row->purchaseRequestDetail()->exists() ? $row->purchaseRequestDetail->purchaseRequest->code : ' - ').'</td>
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
        
        if($data->approval() && $data->approval()->approvalMatrix()->exists()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTable->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $po = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['supplier_name'] = $po->supplier->name;
        $po['subtotal'] = number_format($po->subtotal,2,',','.');
        $po['discount'] = number_format($po->discount,2,',','.');
        $po['total'] = number_format($po->total,2,',','.');
        $po['tax'] = number_format($po->tax,2,',','.');
        $po['wtax'] = number_format($po->wtax,2,',','.');
        $po['grandtotal'] = number_format($po->grandtotal,2,',','.');

        $arr = [];

        foreach($po->purchaseOrderDetail as $row){
            $arr[] = [
                'id'                                => $row->purchaseRequestDetail()->exists() ? $row->purchaseRequestDetail->purchase_request_id : '0',
                'purchase_request_detail_id'        => $row->purchase_request_detail_id ? $row->purchase_request_detail_id : '0',
                'item_id'                           => $row->item_id,
                'coa_id'                            => $row->coa_id,
                'item_name'                         => $row->item_id ? $row->item->name : '',
                'coa_name'                          => $row->coa_id ? $row->coa->name : '',
                'qty'                               => number_format($row->qty,3,',','.'),
                'unit'                              => $row->item_id ? $row->item->buyUnit->code : '-',
                'note'                              => $row->note,
                'price'                             => number_format($row->price,2,',','.'),
                'disc1'                             => number_format($row->percent_discount_1,2,',','.'),
                'disc2'                             => number_format($row->percent_discount_2,2,',','.'),
                'disc3'                             => number_format($row->discount_3,2,',','.'),
                'subtotal'                          => number_format($row->subtotal,2,',','.'),
                'is_tax'                            => $row->is_tax ? $row->is_tax : '',
                'is_include_tax'                    => $row->is_include_tax ? $row->is_include_tax : '',
                'percent_tax'                       => number_format($row->percent_tax,2,',','.'),
                'is_wtax'                           => $row->is_wtax ? $row->is_wtax : '',
                'percent_wtax'                      => number_format($row->percent_wtax,2,',','.'),
                'warehouse_id'                      => $row->warehouse_id,
                'warehouse_name'                    => $row->warehouse_id ? $row->warehouse->name : '',
                'place_id'                          => $row->place_id,
                'department_id'                     => $row->department_id,
                'tax_id'                            => $row->tax_id,
                'wtax_id'                           => $row->wtax_id
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

    public function voidStatus(Request $request){
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase order data');
    
                CustomHelper::sendNotification('purchase_orders',$query->id,'Purchase Order No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('purchase_orders',$query->id);

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
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $data_good_receipts=[];
        $data_purchase_requests=[];
        $data_go_chart=[];
        $data_link=[];
        if($query) {
            $data_po = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "url", 'type'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($query->code)],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[]=$data_po;
            foreach($query->purchaseOrderDetail as $po_detail){
                
                foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                    
                    $data_good_receipt = [
                        'properties'=> [
                            ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                            ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
                         ],
                        "key" => $good_receipt_detail->goodReceipt->code,
                        "name" => $good_receipt_detail->goodReceipt->code,
                        'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                    ];

                    if(count($data_good_receipts)<1){
                        $data_good_receipts[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$query->code,
                            'to'=>$data_good_receipt["key"],
                        ];  
                    }else{
                        $found = false;
                        foreach ($data_good_receipts as $key => $row_pos) {
                            if ($row_pos["key"] == $data_good_receipt["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $data_good_receipts[]=$data_good_receipt;
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$data_good_receipt["key"],
                            ]; 
                        }
                    }

                }
                if($po_detail->purchaseRequestDetail()->exists()){
                    $pr = [
                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                        'properties'=> [
                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                            ['name'=> "url", 'type'=> request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code)],
                         ],
                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                    ];
                    if(count($data_purchase_requests)<1){
                        $data_purchase_requests[]=$pr;
                        $data_go_chart[]=$pr;
                        $data_link[]=[
                            'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                            'to'=>$query->code,
                        ]; 
                        
                    }else{
                        $found = false;
                        foreach ($data_purchase_requests as $key => $row_pos) {
                            if ($row_pos["key"] == $pr["key"]) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $data_purchase_requests[]=$pr;
                            $data_go_chart[]=$pr;
                            $data_link[]=[
                                'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                'to'=>$query->code,
                            ]; 
                        }
                    }
                }
            }
            
            if(count($data_good_receipts)>0){
                $response = [
                    'status'  => 200,
                    'message' => $data_go_chart,
                    'link'    => $data_link
                ];
            }else{
                $response = [
                    'status'  => 200,
                    'message' => $data_go_chart,
                    'link'    => $data_link
                ];
            }
            
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

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

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

    public function print(Request $request){

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

                if($request->type){
                    $query->where('purchasing_type',$request->type);
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
		
		return view('admin.print.purchase.order', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPurchaseOrder($request->search,$request->status,$request->inventory,$request->type,$request->shipping,$request->company,$request->is_tax,$request->is_include_tax,$request->payment,$request->supplier,$request->currency,$this->dataplaces), 'purchase_order_'.uniqid().'.xlsx');
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_requests',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}