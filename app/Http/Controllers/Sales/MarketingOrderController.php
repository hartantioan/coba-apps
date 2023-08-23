<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Line;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDetail;
use App\Models\Place;
use App\Models\Machine;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\Tax;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;

class MarketingOrderController extends Controller
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
        $data = [
            'title'         => 'Sales Order',
            'content'       => 'admin.sales.order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'SORD-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = MarketingOrder::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'sales_type',
            'post_date',
            'valid_date',
            'document',
            'document_no',
            'delivery_type',
            'sender_id',
            'delivery_date',
            'payment_type',
            'top_internal',
            'top_customer',
            'is_guarantee',
            'shipment_address',
            'billing_address',
            'destination_address',
            'province_id',
            'city_id',
            'district_id',
            'sales_id',
            'currency_id',
            'currency_rate',
            'note',
            'subtotal',
            'discount',
            'total',
            'tax',
            'grandtotal',
            'rounding',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrder::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrder::where(function($query) use ($search, $request) {
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
                            ->orWhereHas('marketingOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->sales_type){
                    $query->where('type_sales',$request->sales_type);
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
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrder::where(function($query) use ($search, $request) {
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
                            ->orWhereHas('marketingOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->sales_type){
                    $query->where('type_sales',$request->sales_type);
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
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    $val->typeSales(),
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->valid_date)),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->document_no,
                    $val->deliveryType(),
                    $val->sender->name,
                    date('d/m/y',strtotime($val->delivery_date)),
                    $val->paymentType(),
                    $val->top_internal,
                    $val->top_customer,
                    $val->isGuarantee(),
                    $val->shipment_address,
                    $val->billing_address,
                    $val->destination_address,
                    $val->province->name,
                    $val->city->name,
                    $val->subdistrict->name,
                    $val->sales->name,
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->total_after_tax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
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

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			            => $request->temp ? ['required', Rule::unique('marketing_orders', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_orders,code',
            'code_place_id'             => 'required',
            'account_id' 				=> 'required',
            'company_id'			    => 'required',
            'type_sales'		        => 'required',
            'post_date'		            => 'required',
            'valid_date'		        => 'required',
            'type_delivery'             => 'required',
            'sender_id'                 => 'required',
            'delivery_date'             => 'required',
            'shipment_address'          => 'required',
            'billing_address'           => 'required',
            'destination_address'       => 'required',
            'province_id'               => 'required',
            'city_id'                   => 'required',
            'subdistrict_id'            => 'required',
            'payment_type'              => 'required',
            'top_internal'              => 'required',
            'top_customer'              => 'required',
            'is_guarantee'              => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'sales_id'                  => 'required',
            'arr_place'                 => 'required|array',
            'arr_warehouse'             => 'required|array',
            'arr_tax_nominal'           => 'required|array',
            'arr_grandtotal'            => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_item_stock'            => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_margin'                => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_is_include_tax'        => 'required|array',
            'arr_disc1'                 => 'required|array',
            'arr_disc2'                 => 'required|array',
            'arr_disc3'                 => 'required|array',
            'arr_other_fee'             => 'required|array',
            'arr_final_price'           => 'required|array',
            'arr_total'                 => 'required|array',
            'subtotal'                  => 'required',
            'discount'                  => 'required',
            'total'                     => 'required',
            'tax'                       => 'required',
            'grandtotal'                => 'required',
            'rounding'                  => 'required',
            'total_after_tax'           => 'required',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai',
            'account_id.required' 				=> 'Customer tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'type_sales.required' 			    => 'Tipe SO tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'valid_date.required' 			    => 'Tanggal valid SO tidak boleh kosong.',
            'type_delivery.required'		    => 'Tipe pengiriman tidak boleh kosong.',
            'sender_id.required'                => 'Pihak pengirim tidak boleh kosong.',
            'delivery_date.required'            => 'Tanggal pengiriman estimasi tidak boleh kosong.',
            'shipment_address.required'         => 'Alamat pengirim ekspedisi tidak boleh kosong.',
            'billing_address.required'          => 'Alamat penagihan tidak boleh kosong.',
            'destination_address.required'      => 'Alamat tujuan tidak boleh kosong.',
            'province_id.required'              => 'Provinsi tujuan tidak boleh kosong.',
            'city_id.required'                  => 'Kota tujuan tidak boleh kosong.',
            'subdistrict_id.required'           => 'Kecamatan tidak boleh kosong',
            'payment_type.required'             => 'Tipe pembayaran tidak boleh kosong.',
            'top_internal.required'             => 'TOP internal tidak boleh kosong.',
            'top_customer.required'             => 'TOP customer tidak boleh kosong',
            'is_guarantee.required'             => 'Garansi atau tidaknya barang tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'sales_id.required'                 => 'Sales tidak boleh kosong',
            'arr_place.required'                => 'Plant tidak boleh kosong.',
            'arr_place.array'                   => 'Plant harus array.',
            'arr_warehouse.required'            => 'Gudang tidak boleh kosong.',
            'arr_warehouse.array'               => 'Gudang harus array.',
            'arr_tax_nominal.required'          => 'Tax nominal tidak boleh kosong.',
            'arr_tax_nominal.array'             => 'Tax nominal harus array.',
            'arr_grandtotal.required'           => 'Grantotal baris tidak boleh kosong.',
            'arr_grandtotal.array'              => 'Grandtotal baris harus array.',
            'arr_item.required'                 => 'Item baris tidak boleh kosong.',
            'arr_item.array'                    => 'item baris harus array.',
            'arr_item_stock.required'           => 'Stok item tidak boleh kosong.',
            'arr_item_stock.array'              => 'Stok item harus array.',
            'arr_qty.required'                  => 'Baris qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Baris qty harus array.',
            'arr_price.required'                => 'Baris harga tidak boleh kosong.',
            'arr_price.array'                   => 'Baris harga harus array.',
            'arr_margin.required'               => 'Harga margin tidak boleh kosong.',
            'arr_margin.array'                  => 'Harga margin baris harus array.',
            'arr_tax.required'                  => 'Baris pajak tidak boleh kosong.',
            'arr_tax.array'                     => 'Baris pajak harus array.',
            'arr_is_include_tax.required'       => 'Baris termasuk pajak tidak boleh kosong.',
            'arr_is_include_tax.array'          => 'Baris termasuk pajak harus array.',
            'arr_disc1.required'                => 'Baris diskon 1 tidak boleh kosong.',
            'arr_disc1.array'                   => 'Baris diskon 1 harus array.',
            'arr_disc2.required'                => 'Baris diskon 2 tidak boleh kosong.',
            'arr_disc2.array'                   => 'Baris diskon 2 harus array.',
            'arr_disc3.required'                => 'Baris diskon 3 tidak boleh kosong.',
            'arr_disc3.array'                   => 'Baris diskon 3 harus array.',
            'arr_other_fee.required'            => 'Baris biaya lain tidak boleh kosong.',
            'arr_other_fee.array'               => 'Baris biaya lain harus array.',
            'arr_final_price.required'          => 'Baris harga akhir tidak boleh kosong.',
            'arr_final_price.array'             => 'Baris harga akhir harus array.',
            'arr_total.required'                => 'Baris total tidak boleh kosong.',
            'arr_total.array'                   => 'Baris total harus array.',
            'discount.required'                 => 'Diskon akhir tidak boleh kosong.',
            'subtotal.required'                 => 'Subtotal tidak boleh kosong.',
            'total.required'                    => 'Total tidak boleh kosong.',
            'tax.required'                      => 'PPN tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
            'rounding.required'                 => 'Rounding tidak boleh kosong.',
            'total_after_tax.required'          => 'Total setelah pajak tidak boleh kosong.'
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedZero = true;
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
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrder::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Sales Order telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){
                        if($request->has('document_so')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document_so')->store('public/marketing_orders');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->type_sales = $request->type_sales;
                        $query->post_date = $request->post_date;
                        $query->valid_date = $request->valid_date;
                        $query->document_no = $request->document_no;
                        $query->document = $document;
                        $query->type_delivery = $request->type_delivery;
                        $query->sender_id = $request->sender_id;
                        $query->delivery_date = $request->delivery_date;
                        $query->payment_type = $request->payment_type;
                        $query->top_internal = $request->top_internal;
                        $query->top_customer = $request->top_customer;
                        $query->is_guarantee = $request->is_guarantee;
                        $query->shipment_address = $request->shipment_address;
                        $query->billing_address = $request->billing_address;
                        $query->destination_address = $request->destination_address;
                        $query->province_id = $request->province_id;
                        $query->city_id = $request->city_id;
                        $query->subdistrict_id = $request->subdistrict_id;
                        $query->sales_id = $request->sales_id;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
                        $query->discount = str_replace(',','.',str_replace('.','',$request->discount));
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                        $query->total_after_tax = str_replace(',','.',str_replace('.','',$request->total_after_tax));
                        $query->rounding = str_replace(',','.',str_replace('.','',$request->rounding));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->marketingOrderDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status sales order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = MarketingOrder::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'type_sales'	            => $request->type_sales,
                        'post_date'                 => $request->post_date,
                        'valid_date'                => $request->valid_date,
                        'document_no'               => $request->document_no,
                        'document'                  => $request->file('document_so') ? $request->file('document_so')->store('public/marketing_orders') : NULL,
                        'type_delivery'             => $request->type_delivery,
                        'sender_id'                 => $request->sender_id,
                        'delivery_date'             => $request->delivery_date,
                        'payment_type'              => $request->payment_type,
                        'top_internal'              => $request->top_internal,
                        'top_customer'              => $request->top_customer,
                        'is_guarantee'              => $request->is_guarantee,
                        'shipment_address'          => $request->shipment_address,
                        'billing_address'           => $request->billing_address,
                        'destination_address'       => $request->destination_address,
                        'province_id'               => $request->province_id,
                        'city_id'                   => $request->city_id,
                        'subdistrict_id'            => $request->subdistrict_id,
                        'sales_id'                  => $request->sales_id,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'note'                      => $request->note,
                        'subtotal'                  => str_replace(',','.',str_replace('.','',$request->subtotal)),
                        'discount'                  => str_replace(',','.',str_replace('.','',$request->discount)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->total)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->tax)),
                        'total_after_tax'           => str_replace(',','.',str_replace('.','',$request->total_after_tax)),
                        'rounding'                  => str_replace(',','.',str_replace('.','',$request->rounding)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                DB::beginTransaction();
                try {
                    
                    foreach($request->arr_item as $key => $row){
                        MarketingOrderDetail::create([
                            'marketing_order_id'            => $query->id,
                            'item_id'                       => $row,
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'price'                         => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'margin'                        => str_replace(',','.',str_replace('.','',$request->arr_margin[$key])),
                            'is_include_tax'                => $request->arr_is_include_tax[$key],
                            'percent_tax'                   => $request->arr_tax[$key],
                            'tax_id'                        => $request->arr_tax_id[$key],
                            'percent_discount_1'            => str_replace(',','.',str_replace('.','',$request->arr_disc1[$key])),
                            'percent_discount_2'            => str_replace(',','.',str_replace('.','',$request->arr_disc2[$key])),
                            'discount_3'                    => str_replace(',','.',str_replace('.','',$request->arr_disc3[$key])),
                            'other_fee'                     => str_replace(',','.',str_replace('.','',$request->arr_other_fee[$key])),
                            'price_after_discount'          => str_replace(',','.',str_replace('.','',$request->arr_final_price[$key])),
                            'total'                         => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'tax'                           => $request->arr_tax_nominal[$key],
                            'grandtotal'                    => $request->arr_grandtotal[$key],
                            'note'                          => $request->arr_note[$key] ? $request->arr_note[$key] : NULL,
                            'item_stock_id'                 => $request->arr_item_stock[$key],
                            'place_id'                      => $request->arr_place[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('marketing_orders',$query->id,$query->note);
                CustomHelper::sendNotification('marketing_orders',$query->id,'Pengajuan Sales Order No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new MarketingOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit sales order.');

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

    public function show(Request $request){
        $po = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->name;
        $po['sender_name'] = $po->sender->name;
        $po['sales_name'] = $po->sales->name.' - '.$po->sales->phone.' Pos. '.$po->sales->position->name.' Dep. '.$po->sales->department->name;
        $po['province_name'] = $po->province->name;
        $po['cities'] = $po->province->getCity();
        $po['subtotal'] = number_format($po->subtotal,2,',','.');
        $po['discount'] = number_format($po->discount,2,',','.');
        $po['total'] = number_format($po->total,2,',','.');
        $po['tax'] = number_format($po->tax,2,',','.');
        $po['total_after_tax'] = number_format($po->total_after_tax,2,',','.');
        $po['rounding'] = number_format($po->rounding,2,',','.');
        $po['grandtotal'] = number_format($po->grandtotal,2,',','.');
        $po['currency_rate'] = number_format($po->currency_rate,2,',','.');

        $arr = [];
        
        foreach($po->marketingOrderDetail as $row){
            $arr[] = [
                'id'                    => $row->id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'qty'                   => number_format($row->qty,3,',','.'),
                'unit'                  => $row->item->sellUnit->code,
                'price'                 => number_format($row->price,2,',','.'),
                'margin'                => number_format($row->margin,2,',','.'),
                'is_include_tax'        => $row->is_include_tax ? $row->is_include_tax : '',
                'percent_tax'           => number_format($row->percent_tax,2,',','.'),
                'tax_id'                => $row->tax_id,
                'disc1'                 => number_format($row->percent_discount_1,2,',','.'),
                'disc2'                 => number_format($row->percent_discount_2,2,',','.'),
                'disc3'                 => number_format($row->discount_3,2,',','.'),
                'other_fee'             => number_format($row->other_fee,2,',','.'),
                'final_price'           => number_format($row->price_after_discount,2,',','.'),
                'total'                 => number_format($row->total,2,',','.'),
                'tax'                   => $row->tax,
                'grandtotal'            => $row->grandtotal,
                'note'                  => $row->note,
                'item_stock_id'         => $row->item_stock_id,
                'item_stock_name'       => $row->itemStock->place->code.' - '.$row->itemStock->warehouse->code,
                'item_stock_qty'        => number_format($row->itemStock->qty / $row->item->sell_convert,3,',','.'),
                'list_stock'            => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = MarketingOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Sales Order',
                'data'      => $pr
            ];

            return view('admin.approval.marketing_order', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="17">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Margin</th>
                                <th class="center-align">Discount 1 (%)</th>
                                <th class="center-align">Discount 2 (%)</th>
                                <th class="center-align">Discount 3 (Rp)</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Ambil dari</th>
                                <th class="center-align">Biaya lain2</th>
                                <th class="center-align">Harga Final</th>
                                <th class="center-align">Total</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->marketingOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->item->sellUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.number_format($row->margin,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
                <td class="center-align">'.$row->place->name.' - '.$row->warehouse->name.'</td>
                <td class="right-align">'.number_format($row->other_fee,2,',','.').'</td>
                <td class="right-align">'.number_format($row->price_after_discount,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
            </tr>';
        }

        $string .= '<tr>
                        <td class="right-align" colspan="13">Subtotal</td>
                        <td class="right-align">'.number_format($data->subtotal,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13">Diskon</td>
                        <td class="right-align">'.number_format($data->discount,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13">Total</td>
                        <td class="right-align">'.number_format($data->total,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13">PPN</td>
                        <td class="right-align">'.number_format($data->tax,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13">Total Setelah PPN</td>
                        <td class="right-align">'.number_format($data->total_after_tax,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13">Rounding</td>
                        <td class="right-align">'.number_format($data->rounding,2,',','.').'</td>
                    </tr>
                    <tr>
                        <td class="right-align" colspan="13" style="font-size:20px !important;"><b>Grandtotal</b></td>
                        <td class="right-align" style="font-size:20px !important;"><b>'.number_format($data->grandtotal,2,',','.').'</b></td>
                    </tr>';
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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

    public function printIndividual(Request $request,$id){
        
        $pr = MarketingOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Sales Order',
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
             
            $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
            // $pdf->render();
    
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

    public function voidStatus(Request $request){
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new MarketingOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the sales order data');
    
                CustomHelper::sendNotification('marketing_orders',$query->id,'Sales Order No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('marketing_orders',$query->id);

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
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->marketingOrderDetail()->delete();

            CustomHelper::removeApproval('marketing_orders',$query->id);

            activity()
                ->performedOn(new MarketingOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the sales order data');

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
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key => $row){
                $pr = MarketingOrder::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Print Sales Order',
                        'data'      => $pr,
                      
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Sales Order',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Sales Order',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
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
}