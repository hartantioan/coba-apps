<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
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
use App\Models\PurchaseOrderDetailComposition;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseOrder;
use App\Models\Place;
use App\Models\User;
use App\Models\Department;

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
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'supplier_id',
            'purchasing_type',
            'shipping_type',
            'place_id',
            'department_id',
            'is_tax',
            'is_include_tax',
            'percent_tax',
            'document_no',
            'document_po',
            'payment_type',
            'payment_term',
            'currency_id',
            'currency_rate',
            'post_date',
            'delivery_date',
            'document_date',
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

        $total_data = PurchaseOrder::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = PurchaseOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('percent_tax', 'like', "%$search%")
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

                if($request->purchasing_type){
                    $query->where('purchasing_type',$request->type);
                }

                if($request->shipping_type){
                    $query->where('shipping_type',$request->shipping_type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
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
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('percent_tax', 'like', "%$search%")
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

                if($request->purchasing_type){
                    $query->where('purchasing_type',$request->type);
                }

                if($request->shipping_type){
                    $query->where('shipping_type',$request->shipping_type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
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
            ->whereIn('place_id',$this->dataplaces)
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
                    $val->purchasingType(),
                    $val->shippingType(),
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->isTax(),
                    $val->isIncludeTax(),
                    number_format($val->percent_tax,3,',','.'),
                    $val->document_no,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->paymentType(),
                    $val->payment_term,
                    $val->currency->name,
                    number_format($val->currency_rate,3,',','.'),
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->delivery_date)),
                    date('d/m/y',strtotime($val->document_date)),
                    $val->receiver_name,
                    $val->receiver_address,
                    $val->receiver_phone,
                    $val->note,
                    number_format($val->subtotal,3,',','.'),
                    number_format($val->discount,3,',','.'),
                    number_format($val->total,3,',','.'),
                    number_format($val->tax,3,',','.'),
                    number_format($val->grandtotal,3,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
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

    public function getPurchaseRequest(Request $request){
        $data = PurchaseRequest::where('id',$request->id)->where('status','2')->first();
        $data['ecode'] = CustomHelper::encrypt($data->code);

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Request '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            
            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Purchase Order');

            $details = [];
            foreach($data->purchaseRequestDetail as $row){
                $details[] = [
                    'item_id'       => $row->item_id,
                    'item_name'     => $row->item->code.' - '.$row->item->name,
                    'unit'          => $row->item->buyUnit->code,
                    'qty'           => $row->qtyBalance(),
                    'note'          => $row->note,
                ];
            }

            $data['details'] = $details;
        }

        return response()->json($data);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'supplier_id' 				=> 'required',
			'purchasing_type'			=> 'required',
			'shipping_type'		        => 'required',
			'payment_type'		        => 'required',
            'payment_term'		        => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'post_date'                 => 'required',
            'delivery_date'             => 'required',
            'document_date'             => 'required',
            'receiver_name'             => 'required',
            'receiver_address'          => 'required',
            'receiver_phone'            => 'required',
            'percent_tax'               => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_disc1'                 => 'required|array',
            'arr_disc2'                 => 'required|array',
            'arr_disc3'                 => 'required|array',
            'discount'                  => 'required',
		], [
			'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
			'purchasing_type.required' 			=> 'Tipe PO tidak boleh kosong.',
            'shipping_type.required' 			=> 'Tipe pengiriman tidak boleh kosong.',
			'payment_type.required' 			=> 'Tipe pembayaran tidak boleh kosong.',
			'payment_term.required'				=> 'Termin pembayaran tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'post_date.required'                => 'Tanggal post tidak boleh kosong.',
            'delivery_date.required'            => 'Tanggal kirim tidak boleh kosong.',
            'document_date.required'            => 'Tanggal dokumen tidak boleh kosong.',
            'percent_tax.required'              => 'Prosentase tax tidak boleh kosong, minimal harus diisi 0.',
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
            'discount.required'                 => 'Diskon akhir tidak boleh kosong.'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            #start_count
            $subtotal = 0;
            $discount = str_replace(',','.',str_replace('.','',$request->discount));
            $total = 0;
            $tax = 0;
            $grandtotal = 0;
            $arr_subtotal = [];
            $percent_tax = str_replace(',','.',str_replace('.','',$request->percent_tax));
            
            $arrDetail = [];

            foreach($request->arr_item as $key => $row){
                $index = -1;
                $detail_pr = [];

                foreach($arrDetail as $keycek => $rowcek){
                    if($row == $rowcek['item_id']){
                        $index = $keycek;
                    }
                }

                $qty = str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                $price = str_replace(',','.',str_replace('.','',$request->arr_price[$key]));
                $disc1 = str_replace(',','.',str_replace('.','',$request->arr_disc1[$key]));
                $disc2 = str_replace(',','.',str_replace('.','',$request->arr_disc2[$key]));
                $disc3 = str_replace(',','.',str_replace('.','',$request->arr_disc3[$key]));

                $finalpricedisc1 = $price - ($price * ($disc1 / 100));
                $finalpricedisc2 = $finalpricedisc1 - ($finalpricedisc1 * ($disc2 / 100));
                $finalpricedisc3 = $finalpricedisc2 - $disc3;

                $rowsubtotal = round($finalpricedisc3 * $qty,3);

                $subtotal += $rowsubtotal;

                if($index >= 0){
                    $arrDetail[$index]['detail_pr'][] = [
                        'pr_code'       => $request->arr_purchase[$key],
                        'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key]))
                    ];
                    $arrDetail[$index] = [
                        'item_id'               => $arrDetail[$index]['item_id'],
                        'qty'                   => $arrDetail[$index]['qty'] + str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                        'percent_discount_1'    => str_replace(',','.',str_replace('.','',$request->arr_disc1[$key])),
                        'percent_discount_2'    => str_replace(',','.',str_replace('.','',$request->arr_disc2[$key])),
                        'discount_3'            => str_replace(',','.',str_replace('.','',$request->arr_disc3[$key])),
                        'subtotal'              => $arrDetail[$index]['subtotal'] + $rowsubtotal,
                        'note'                  => $arrDetail[$index]['note'].', '.$request->arr_note[$key],
                        'detail_pr'             => $arrDetail[$index]['detail_pr']
                    ];
                }else{
                    $detail_pr[] = [
                        'pr_code'       => $request->arr_purchase[$key],
                        'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key]))
                    ];
                    $arrDetail[] = [
                        'item_id'               => $row,
                        'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                        'percent_discount_1'    => str_replace(',','.',str_replace('.','',$request->arr_disc1[$key])),
                        'percent_discount_2'    => str_replace(',','.',str_replace('.','',$request->arr_disc2[$key])),
                        'discount_3'            => str_replace(',','.',str_replace('.','',$request->arr_disc3[$key])),
                        'subtotal'              => $rowsubtotal,
                        'note'                  => $request->arr_note[$key],
                        'detail_pr'             => $detail_pr,
                    ];
                }
            }

            $total = $subtotal - $discount;

            if($request->is_tax){
                if($request->is_include_tax){
                    $total = $total / (1 + ($percent_tax / 100));
                }
                $tax = $total * ($percent_tax / 100);
            }

            $grandtotal = round($total + $tax,3);

            #end_count

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
                        $query->purchasing_type = $request->purchasing_type;
                        $query->shipping_type = $request->shipping_type;
                        $query->place_id = $request->place_id;
                        $query->department_id = $request->department_id;
                        $query->is_tax = $request->is_tax ? $request->is_tax : NULL;
                        $query->is_include_tax = $request->is_include_tax ? $request->is_include_tax : '0';
                        $query->document_no = $request->document_no;
                        $query->document_po = $document;
                        $query->percent_tax = str_replace(',','.',str_replace('.','',$request->percent_tax));
                        $query->payment_type = $request->payment_type;
                        $query->payment_term = $request->payment_term;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->delivery_date = $request->delivery_date;
                        $query->document_date = $request->document_date;
                        $query->note = $request->note;
                        $query->subtotal = round($subtotal,3);
                        $query->discount = $discount;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->receiver_name = $request->receiver_name;
                        $query->receiver_address = $request->receiver_address;
                        $query->receiver_phone = $request->receiver_phone;

                        $query->save();

                        foreach($query->PurchaseOrderDetail as $row){
                            foreach($row->purchaseOrderDetailComposition as $composition){
                                $composition->delete();
                            }
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
                        'purchasing_type'	        => $request->purchasing_type,
                        'shipping_type'             => $request->shipping_type,
                        'place_id'                  => $request->place_id,
                        'department_id'             => $request->department_id,
                        'is_tax'                    => $request->is_tax ? $request->is_tax : NULL,
                        'is_include_tax'            => $request->is_include_tax ? $request->is_include_tax : '0',
                        'document_no'               => $request->document_no,
                        'document_po'               => $request->file('document_po') ? $request->file('document_po')->store('public/purchase_orders') : NULL,
                        'percent_tax'               => str_replace(',','.',str_replace('.','',$request->percent_tax)),
                        'payment_type'              => $request->payment_type,
                        'payment_term'              => $request->payment_term,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'document_date'             => $request->document_date,
                        'note'                      => $request->note,
                        'subtotal'                  => round($subtotal,3),
                        'discount'                  => $discount,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'grandtotal'                => round($grandtotal,3),
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
                
                    foreach($arrDetail as $key => $row){
                        
                        $querydetail = PurchaseOrderDetail::create([
                            'purchase_order_id'     => $query->id,
                            'item_id'               => $row['item_id'],
                            'qty'                   => $row['qty'],
                            'price'                 => $row['price'],
                            'percent_discount_1'    => $row['percent_discount_1'],
                            'percent_discount_2'    => $row['percent_discount_2'],
                            'discount_3'            => $row['discount_3'],
                            'subtotal'              => $row['subtotal'],
                            'note'                  => $row['note'],
                        ]);

                        foreach($row['detail_pr'] as $rowpr){
                            if($rowpr['pr_code'] !== '0'){
                                $pr = PurchaseRequest::where('code',CustomHelper::decrypt($rowpr['pr_code']))->first();
                                if($pr){
                                    PurchaseOrderDetailComposition::create([
                                        'pod_id'    => $querydetail->id,
                                        'pr_id'     => $pr->id,
                                        'qty'       => $rowpr['qty']
                                    ]);
                                    CustomHelper::removeUsedData($pr->getTable(),$pr->id);
                                }
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
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Price</th>
                                <th class="center-align">Discount 1 (%)</th>
                                <th class="center-align">Discount 2 (%)</th>
                                <th class="center-align">Discount 3 (Rp)</th>
                                <th class="center-align">Subtotal</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->purchaseOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->item->buyUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,3,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,3,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,3,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,3,',','.').'</td>
                <td class="center-align">'.$row->note.'</td>
            </tr>
            <tr>
                <td class="center-align" colspan="10">
                    '.$row->purchaseRequestList().'
                </td>
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

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Landed Cost</th>
                            </tr>
                            <tr>
                                <th class="center-align">Nomor/Kode</th>
                                <th class="center-align">Vendor</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Pajak</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';

        if($data->landedCost()->exists()){
            foreach($data->landedCost as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->code.'</td>
                    <td class="center-align">'.$row->vendor->name.'</td>
                    <td class="center-align">'.$row->note.'</td>
                    <td class="center-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="center-align">'.number_format($row->tax,2,',','.').'</td>
                    <td class="center-align">'.number_format($row->grandtotal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="6">Landed cost tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $po = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['supplier_name'] = $po->supplier->name;
        $po['percent_tax'] = number_format($po->percent_tax,3,',','.');
        $po['subtotal'] = number_format($po->subtotal,3,',','.');
        $po['discount'] = number_format($po->discount,3,',','.');
        $po['total'] = number_format($po->total,3,',','.');
        $po['tax'] = number_format($po->tax,3,',','.');
        $po['grandtotal'] = number_format($po->grandtotal,3,',','.');

        $arr = [];

        foreach($po->purchaseOrderDetail as $row){
            if($row->purchaseOrderDetailComposition()->exists()){
                foreach($row->purchaseOrderDetailComposition as $rowcompos){
                    $arr[] = [
                        'id'        => $rowcompos->pr_id,
                        'ecode'     => CustomHelper::encrypt($rowcompos->purchaseRequest->code),
                        'code'      => $rowcompos->purchaseRequest->code,
                        'item_id'   => $row->item_id,
                        'item_name' => $row->item->name,
                        'qty'       => $rowcompos->qty,
                        'unit'      => $row->item->buyUnit->code,
                        'note'      => $row->note,
                        'price'     => number_format($row->price,3,',','.'),
                        'disc1'     => number_format($row->percent_discount_1,3,',','.'),
                        'disc2'     => number_format($row->percent_discount_2,3,',','.'),
                        'disc3'     => number_format($row->discount_3,3,',','.'),
                        'subtotal'  => number_format($rowcompos->qty * $row->price,3,',','.'),
                    ];
                }
            }else{
                $arr[] = [
                    'id'        => 0,
                    'ecode'     => 0,
                    'item_id'   => $row->item_id,
                    'item_name' => $row->item->name,
                    'qty'       => $row->qty,
                    'unit'      => $row->item->buyUnit->code,
                    'note'      => $row->note,
                    'price'     => number_format($row->price,3,',','.'),
                    'disc1'     => number_format($row->percent_discount_1,3,',','.'),
                    'disc2'     => number_format($row->percent_discount_2,3,',','.'),
                    'disc3'     => number_format($row->discount_3,3,',','.'),
                    'subtotal'  => number_format($row->subtotal,3,',','.'),
                ];
            }
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
            if($query->status == '5'){
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

    public function destroy(Request $request){
        $query = PurchaseOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval() || in_array($query->status,['2','3'])){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }
        
        if($query->delete()) {

            $query->purchaseOrderDetail()->purchaseOrderDetailComposition()->delete();
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
                            ->orWhere('percent_tax', 'like', "%$request->search%")
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

                if($request->type){
                    $query->where('purchasing_type',$request->type);
                }

                if($request->shipping){
                    $query->where('shipping_type',$request->shipping);
                }

                if($request->supplier){
                    $query->whereIn('account_id',$request->supplier);
                }
                
                if($request->place){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department){
                    $query->where('department_id',$request->department);
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

                if($request->payment){
                    $query->where('payment_type',$request->payment);
                }                
                
                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
		];
		
		return view('admin.print.purchase.order', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPurchaseOrder($request->search,$request->status,$request->type,$request->shipping,$request->place,$request->department,$request->is_tax,$request->is_include_tax,$request->payment,$request->supplier,$request->currency,$this->dataplaces), 'purchase_order_'.uniqid().'.xlsx');
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_requests',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}