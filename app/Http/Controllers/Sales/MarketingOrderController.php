<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\IncomingPayment;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use Illuminate\Support\Str;
use App\Models\Line;

use App\Models\MarketingOrderDetail;

use App\Models\Place;
use App\Models\Machine;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\UserData;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\Tax;
use App\Models\Menu;
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
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Sales Order',
            'content'       => 'admin.sales.order',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'transportation'=> Transportation::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
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
            'post_date',
            'valid_date',
            'project_id',
            'document',
            'document_no',
            'delivery_type',
            'sender_id',
            'transportation_id',
            'delivery_date',
            'payment_type',
            'top_internal',
            'top_customer',
            'is_guarantee',
            'billing_address',
            'outlet_id',
            'destination_address',
            'province_id',
            'city_id',
            'district_id',
            'sales_id',
            'currency_id',
            'currency_rate',
            'note_internal',
            'note_external',
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
                            ->orWhere('note_internal', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
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
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('note_internal', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
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
                    $val->type(),
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->valid_date)),
                    $val->project()->exists() ? $val->project->name : '-',
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->document_no,
                    $val->deliveryType(),
                    $val->sender->name,
                    $val->transportation->name,
                    date('d/m/Y',strtotime($val->delivery_date)),
                    $val->paymentType(),
                    $val->top_internal,
                    $val->top_customer,
                    $val->isGuarantee(),
                    $val->billing_address,
                    $val->outlet->name,
                    $val->destination_address,
                    $val->province->name,
                    $val->city->name,
                    $val->district->name,
                    $val->subdistrict->name,
                    $val->sales->name,
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    $val->percent_dp,
                    $val->note_internal,
                    $val->note_external,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->total_after_tax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'sistem' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'sistem' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? null : null
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat indigo accent-2 white-text btn-small" data-popup="tooltip" title="Salin" onclick="duplicate(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">content_copy</i></button>
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
           /*  'code'			            => $request->temp ? ['required', Rule::unique('marketing_orders', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_orders,code',
             */'code_place_id'             => 'required',
            'account_id' 				=> 'required',
            'company_id'			    => 'required',
            'type'			            => 'required',
            'post_date'		            => 'required',
            'valid_date'		        => 'required',
            'type_delivery'             => 'required',
            'sender_id'                 => 'required',
            'delivery_date'             => 'required',
            'transportation_id'         => $request->type_delivery == '2' ? 'required' : '',
            'outlet_id'                 => 'required',
            'billing_address'           => 'required',
            'destination_address'       => 'required',
            'province_id'               => 'required',
            'city_id'                   => 'required',
            'district_id'               => 'required',
            'subdistrict_id'            => 'required',
            'payment_type'              => 'required',
            'top_internal'              => 'required',
            'top_customer'              => 'required',
            'is_guarantee'              => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'percent_dp'                => 'required',
            'sales_id'                  => 'required',
            'arr_place'                 => 'required|array',
            'arr_area'                  => 'required|array',
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
            /* 'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'account_id.required' 				=> 'Customer tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'type.required' 			        => 'Tipe Penjualan tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'valid_date.required' 			    => 'Tanggal valid SO tidak boleh kosong.',
            'type_delivery.required'		    => 'Tipe pengiriman tidak boleh kosong.',
            'sender_id.required'                => 'Pihak pengirim tidak boleh kosong.',
            'delivery_date.required'            => 'Tanggal pengiriman estimasi tidak boleh kosong.',
            'transportation_id.required'        => 'Tipe transportasi tidak boleh kosong.',
            'outlet_id.required'                => 'Outlet tidak boleh kosong.',
            'billing_address.required'          => 'Alamat penagihan tidak boleh kosong.',
            'destination_address.required'      => 'Alamat tujuan tidak boleh kosong.',
            'province_id.required'              => 'Provinsi tujuan tidak boleh kosong.',
            'city_id.required'                  => 'Kota tujuan tidak boleh kosong.',
            'district_id.required'              => 'Kecamatan tujuan tidak boleh kosong.',
            'subdistrict_id.required'           => 'Kelurahan tidak boleh kosong',
            'payment_type.required'             => 'Tipe pembayaran tidak boleh kosong.',
            'top_internal.required'             => 'TOP internal tidak boleh kosong.',
            'top_customer.required'             => 'TOP customer tidak boleh kosong',
            'is_guarantee.required'             => 'Garansi atau tidaknya barang tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'percent_dp.required'               => 'Prosentase DP tidak boleh kosong. Silahkan isi 0 jika memang tidak ada.',
            'sales_id.required'                 => 'Sales tidak boleh kosong',
            'arr_place.required'                => 'Plant tidak boleh kosong.',
            'arr_place.array'                   => 'Plant harus array.',
            'arr_area.required'                 => 'Area tidak boleh kosong.',
            'arr_area.array'                    => 'Area harus array.',
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

            $userData = UserData::find($request->billing_address);
            
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
                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
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
                        $query->type = $request->type;
                        $query->post_date = $request->post_date;
                        $query->valid_date = $request->valid_date;
                        $query->document_no = $request->document_no;
                        $query->document = $document;
                        $query->project_id = $request->project_id;
                        $query->type_delivery = $request->type_delivery;
                        $query->sender_id = $request->sender_id;
                        $query->delivery_date = $request->delivery_date;
                        $query->payment_type = $request->payment_type;
                        $query->top_internal = $request->top_internal;
                        $query->top_customer = $request->top_customer;
                        $query->is_guarantee = $request->is_guarantee;
                        $query->transportation_id = $request->transportation_id;
                        $query->outlet_id = $request->outlet_id;
                        $query->user_data_id = $request->billing_address;
                        $query->billing_address = $userData->title.' '.$userData->content;
                        $query->destination_address = $request->destination_address;
                        $query->province_id = $request->province_id;
                        $query->city_id = $request->city_id;
                        $query->district_id = $request->district_id;
                        $query->subdistrict_id = $request->subdistrict_id;
                        $query->sales_id = $request->sales_id;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->percent_dp = str_replace(',','.',str_replace('.','',$request->percent_dp));
                        $query->note_internal = $request->note_internal;
                        $query->note_external = $request->note_external;
                        $query->subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
                        $query->discount = str_replace(',','.',str_replace('.','',$request->discount));
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                        $query->total_after_tax = str_replace(',','.',str_replace('.','',$request->total_after_tax));
                        /* $query->rounding = str_replace(',','.',str_replace('.','',$request->rounding)); */
                        $query->rounding = 0;
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
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=MarketingOrder::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = MarketingOrder::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'type'                      => $request->type,
                        'post_date'                 => $request->post_date,
                        'valid_date'                => $request->valid_date,
                        'project_id'                => $request->project_id,
                        'document_no'               => $request->document_no,
                        'document'                  => $request->file('document_so') ? $request->file('document_so')->store('public/marketing_orders') : NULL,
                        'type_delivery'             => $request->type_delivery,
                        'sender_id'                 => $request->sender_id,
                        'delivery_date'             => $request->delivery_date,
                        'payment_type'              => $request->payment_type,
                        'top_internal'              => $request->top_internal,
                        'top_customer'              => $request->top_customer,
                        'is_guarantee'              => $request->is_guarantee,
                        'transportation_id'         => $request->transportation_id,
                        'outlet_id'                 => $request->outlet_id,
                        'user_data_id'              => $request->billing_address,
                        'billing_address'           => $userData->title.' '.$userData->content,
                        'destination_address'       => $request->destination_address,
                        'province_id'               => $request->province_id,
                        'city_id'                   => $request->city_id,
                        'district_id'               => $request->district_id,
                        'subdistrict_id'            => $request->subdistrict_id,
                        'sales_id'                  => $request->sales_id,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'percent_dp'                => str_replace(',','.',str_replace('.','',$request->percent_dp)),
                        'note_internal'             => $request->note_internal,
                        'note_external'             => $request->note_external,
                        'subtotal'                  => str_replace(',','.',str_replace('.','',$request->subtotal)),
                        'discount'                  => str_replace(',','.',str_replace('.','',$request->discount)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->total)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->tax)),
                        'total_after_tax'           => str_replace(',','.',str_replace('.','',$request->total_after_tax)),
                        /* 'rounding'                  => str_replace(',','.',str_replace('.','',$request->rounding)), */
                        'rounding'                  => 0,
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
                            'item_stock_id'                 => $request->arr_item_stock[$key] ? $request->arr_item_stock[$key] : NULL,
                            'place_id'                      => $request->arr_place[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                            'area_id'                       => $request->arr_area[$key],
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note_internal.' - '.$query->note_external);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Sales Order No. '.$query->code,$query->note_internal.' - '.$query->note_external,session('bo_id'));

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
        $po['sales_name'] = $po->sales->name.' - '.$po->sales->phone.' Pos. '.$po->sales->position->name.' Dep. '.$po->sales->position->division->department->name;
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
        $po['project_name'] = $po->project()->exists() ? $po->project->code.' - '.$po->project->name : '';
        $po['percent_dp'] = number_format($po->percent_dp,2,',','.');
        $po['user_data'] = $po->account->getBillingAddress();
        $po['transportation_name'] = $po->transportation->code.' - '.$po->transportation->name;
        $po['outlet_name'] = $po->outlet->code.' - '.$po->outlet->name;

        $arr = [];
        
        foreach($po->marketingOrderDetail as $row){
            $arr[] = [
                'id'                    => $row->id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
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
                'item_stock_qty'        => CustomHelper::formatConditionalQty($row->itemStock->qty / $row->item->sell_convert),
                'list_stock'            => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
                'area_id'               => $row->area_id,
                'area_name'             => $row->area->name,
                'list_warehouse'        => $row->item->warehouseList(),
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
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
            $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser;
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;">
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
        $totalqty=0;
        $totalmargin=0;
        $totaldiskon1=0;
        $totaldiskon2=0;
        $totaldiskon3=0;
        $totalother=0;
        $totalpriceafterdiscount=0;
        $totals=0;
        
        foreach($data->marketingOrderDetail as $key => $row){
            $totalqty+=$row->qty;
            $totalmargin+=$row->margin;
            $totaldiskon1+=$row->percent_discount_1;
            $totaldiskon2+=$row->percent_discount_2;
            $totaldiskon3+=$row->discount3;
            $totalother+=$row->other_fee;
            $totalpriceafterdiscount+=$row->price_after_discount;
            $totals+=$row->total;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->item->sellUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.number_format($row->margin,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
                <td class="center-align">'.$row->place->code.' - '.$row->warehouse->name.' - '.$row->area->name.'</td>
                <td class="right-align">'.number_format($row->other_fee,2,',','.').'</td>
                <td class="right-align">'.number_format($row->price_after_discount,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalmargin, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon1, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon2, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon3, 2, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalother, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpriceafterdiscount, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
            </tr>  
        ';

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
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
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

    public function voidStatus(Request $request){
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
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

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

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
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Sales Order',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Sales Order',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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


    public function viewStructureTree(Request $request){
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
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


        if($query){
            $data_marketing_order = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_marketing_order;
            $data_id_mo[]=$query->id;

            $finished_data_id_ip = [];
            $finished_data_id_dp = [];
            $finished_data_id_mo_receipt = [];
            $finished_data_id_mo_delivery_process = [];
            $finished_data_id_handover= [];
            $finished_data_id_handover_invoice = [];
            $finished_data_id_invoice = [];
            $finished_data_id_memo = [];
            $finished_data_id_mo_return = [];
            $finished_data_id_mo_delivery = [];
            $finished_data_id_mo = [];

            $added = true;
            while($added){//beda tree
                $added=false;
                
                // mencaari incoming payment
                foreach($data_incoming_payment as $row_id_ip){
                    if(!in_array($row_id_ip, $finished_data_id_ip)){
                        $finished_data_id_ip[]=$row_id_ip;
                        $query_ip = IncomingPayment::find($row_id_ip);
                        foreach($query_ip->incomingPaymentDetail as $row_ip_detail){
                            if($row_ip_detail->marketingOrderDownPayment()->exists()){
                                $mo_downpayment=[
                                    "name"=>$row_ip_detail->marketingOrderDownPayment->code,
                                    "key" => $row_ip_detail->marketingOrderDownPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->marketingOrderDownPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderDownPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/finance/incoming_payment?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderDownPayment->code),
                                ];
                                $data_go_chart[]=$mo_downpayment;
                                $data_link[]=[
                                    'from'=>$row_ip_detail->marketingOrderDownPayment->code,
                                    'to'=>$query_ip->code,
                                    'string_link'=>$row_ip_detail->marketingOrderDownPayment->code.$query_ip->code,
                                ];
                                $data_id_mo_dp[] = $row_ip_detail->marketingOrderDownPayment->id;
                                
                            }
                            if($row_ip_detail->marketingOrderInvoice()->exists()){
                                $mo_invoice=[
                                    "name"=>$row_ip_detail->marketingOrderInvoice->code,
                                    "key" => $row_ip_detail->marketingOrderInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->marketingOrderInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderInvoice->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderInvoice->code),
                                ];
                                $data_go_chart[]=$mo_invoice;
                                $data_link[]=[
                                    'from'=>$row_ip_detail->marketingOrderInvoice->code,
                                    'to'=>$query_ip->code,
                                    'string_link'=>$row_ip_detail->marketingOrderInvoice->code.$query_ip->code,
                                ];
                                $data_id_mo_invoice[] = $row_ip_detail->marketingOrderInvoice->id;
                                
                            }
                        }
                    }
                }
                // menacari down_payment
                foreach($data_id_mo_dp as $row_id_dp){
                    if(!in_array($row_id_dp, $finished_data_id_dp)){
                        $finished_data_id_dp[]=$row_id_dp;
                        $query_dp= MarketingOrderDownPayment::find($row_id_dp);
                        
                        if($query_dp->incomingPaymentDetail()->exists()){
                            foreach($query_dp->incomingPaymentDetail as $row_incoming_payment){
                                $mo_incoming_payment=[
                                    "name"=>$row_incoming_payment->incomingPayment->code,
                                    "key" => $row_incoming_payment->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_incoming_payment->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_incoming_payment->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_incoming_payment->incomingPayment->code),
                                ];
                                $data_go_chart[]=$mo_incoming_payment;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$row_incoming_payment->incomingPayment->code,
                                    'string_link'=>$query_dp->code.$row_incoming_payment->incomingPayment->code,
                                ];
                                if(!in_array($row_incoming_payment->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[] = $row_incoming_payment->incomingPayment->id;
                                    $added = true;
                                }
                            }
                        }
                        
                        if($query_dp->marketingOrderInvoiceDetail()->exists()){
                            $arr = [];
                            foreach($query_dp->marketingOrderInvoiceDetail as $row_invoice_detail){
                                if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                                    foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                                        $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                                    }
                                }
                                
                                $newArray = array_unique($arr);
                                $string = implode(', ', $newArray);
                                $data_invoice = [
                                    "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                                    "key" => $row_invoice_detail->marketingOrderInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')],
                                        ['name'=> "No Surat Jalan  :".$string.""]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                                ];
                                
                                $data_go_chart[]=$data_invoice;
                                $data_link[]=[
                                    'from'=>$row_invoice_detail->marketingOrderInvoice->code,
                                    'to'=>$query_dp->code,
                                    'string_link'=>$query_dp->code.$row_invoice_detail->marketingOrderInvoice->code,
                                ];
                                
                                if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                                    $added = true;
                                }
                            }
                        }
                    }


                }
                //marketing mo receipt
                foreach($data_id_mo_receipt as $id_mo_receipt){
                    if(!in_array($id_mo_receipt, $finished_data_id_mo_receipt)){
                        $finished_data_id_mo_receipt[]=$id_mo_receipt;
                        $query_mo_receipt = MarketingOrderReceipt::find($id_mo_receipt);

                        if($query_mo_receipt->marketingOrderHandoverReceiptDetail->exists()){
                            foreach($query_mo_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_rd){
                                $mohr=[
                                    "name"=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    "key" =>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_rd->marketingOrderHandoverReceipt->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_rd->marketingOrderHandoverReceipt->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_handover_receipt?code=".CustomHelper::encrypt($row_mo_h_rd->marketingOrderHandoverReceipt->code),
                                ];
                                $data_go_chart[]=$mohr;
                                $data_link[]=[
                                    'from'=>$query_mo_receipt->code,
                                    'to'=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    'string_link'=>$query_mo_receipt->code.$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                ];
                                
                                if(!in_array($row_mo_h_rd->marketingOrderHandoverReceipt->id, $data_id_hand_over_receipt)){
                                    $data_id_hand_over_receipt[] =$row_mo_h_rd->marketingOrderHandoverReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        
                        foreach($query_mo_receipt->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                            if($row_mo_receipt_detail->marketingOrderInvoice()){
                                $mo_invoice_tempura = [
                                    "name"=>$row_mo_receipt_detail->lookable->code,
                                    "key" => $row_mo_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_receipt->code,
                                    'to'=>$row_mo_receipt_detail->lookable->code,
                                    'string_link'=>$query_mo_receipt->code.$row_mo_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_mo_delivery_process as $id_mo_delivery_process){
                    if(!in_array($id_mo_delivery_process, $finished_data_id_mo_delivery_process)){
                        $finished_data_id_mo_delivery_process[]=$id_mo_delivery_process;
                        $query_mo_delivery_process = MarketingOrderDeliveryProcess::find($id_mo_delivery_process);

                        if($query_mo_delivery_process->purchaseOrderDetail()->exists()){
                            foreach($query_mo_delivery_process->purchaseOrderDetail as $row_po_detail){
                                $po_tempura=[
                                    "name"=>$row_po_detail->purchaseOrder->code,
                                    "key" =>$row_po_detail->purchaseOrder->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po_detail->purchaseOrder->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po_detail->purchaseOrder->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po_detail->purchaseOrder->code),
                                ];
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_delivery_process->code,
                                    'to'=>$row_po_detail->purchaseOrder->code,
                                    'string_link'=>$query_mo_delivery_process->code.$row_po_detail->purchaseOrder->code,
                                ];
                                
                                if(!in_array($row_po_detail->purchaseOrder->id, $data_id_po)){
                                    $data_id_po[] =$row_po_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }
                        
                        }
                    }
                }

                //marketing handover receipt
                foreach($data_id_hand_over_receipt as $row_handover_id){
                    if(!in_array($row_handover_id, $finished_data_id_handover)){
                        $finished_data_id_handover[]=$row_handover_id;
                        $query_handover_receipt = MarketingOrderHandoverReceipt::find($row_handover_id);
                        foreach($query_handover_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_receipt_detail){
                            if($row_mo_h_receipt_detail->marketingOrderInvoice()){
                                $mo_invoice_tempura=[
                                    "name"=>$row_mo_h_receipt_detail->lookable->code,
                                    "key" => $row_mo_h_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_handover_receipt->code,
                                    'to'=>$row_mo_h_receipt_detail->lookable->code,
                                    'string_link'=>$query_handover_receipt->code.$row_mo_h_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }
                }
                //marketing handover invoice
                foreach($data_id_hand_over_invoice as $row_handover_invoice_id){
                    if(!in_array($row_handover_invoice_id, $finished_data_id_handover_invoice)){
                        $finished_data_id_handover_invoice[]=$row_handover_invoice_id;
                        $query_handover_invoice = MarketingOrderHandoverInvoice::find($row_handover_invoice_id);
                        foreach($query_handover_invoice->marketingOrderHandoverInvoiceDetail as $row_mo_h_invoice_detail){
                            if($row_mo_h_invoice_detail->marketingOrderInvoice->exists()){
                                $mo_invoice_tempura=[
                                    "name"=>$row_mo_h_receipt_detail->lookable->code,
                                    "key" => $row_mo_h_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_handover_invoice->code,
                                    'to'=>$row_mo_h_receipt_detail->lookable->code,
                                    'string_link'=>$query_handover_invoice->code.$row_mo_h_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }
                }

                // menacari anakan invoice
                foreach($data_id_mo_invoice as $row_id_invoice){
                    if(!in_array($row_id_invoice, $finished_data_id_invoice)){
                        $finished_data_id_invoice[]=$row_id_invoice;
                        $query_invoice = MarketingOrderInvoice::find($row_id_invoice);
                        if($query_invoice->incomingPaymentDetail()->exists()){
                            foreach($query_invoice->incomingPaymentDetail as $row_ip_detail){
                                $mo_incoming_payment=[
                                    "name"=>$row_ip_detail->incomingPayment->code,
                                    "key" => $row_ip_detail->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_ip_detail->incomingPayment->code),
                                ];
                                $data_go_chart[]=$mo_incoming_payment;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_ip_detail->incomingPayment->code,
                                    'string_link'=>$query_invoice->code.$row_ip_detail->incomingPayment->code,
                                ];
                                if(!in_array($row_ip_detail->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[] = $row_ip_detail->incomingPayment->id;
                                    $added = true;
                                }
                            }
                        }
                        if($query_invoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                            foreach($query_invoice->marketingOrderInvoiceDeliveryProcess as $row_delivery_detail){
                                
                                $mo_delivery=[
                                    "name"=> $row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    "key" => $row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_delivery_detail->lookable->marketingOrderDelivery->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_delivery_detail->lookable->marketingOrderDelivery->grandtotal,2,',','.')],
                                        
                                    ],
                                    'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_delivery_detail->lookable->marketingOrderDelivery->code),
                                ];
                                $data_go_chart[]=$mo_delivery;
                                $data_link[]=[
                                    'from'=>$row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_delivery_detail->lookable->marketingOrderDelivery->code.$query_invoice->code,
                                ];
                                $data_id_mo_delivery[]=$row_delivery_detail->lookable->marketingOrderDelivery->id;
                            }    
                            
                        }
                        if($query_invoice->marketingOrderInvoiceDownPayment()->exists()){
                            foreach($query_invoice->marketingOrderInvoiceDownPayment as $row_dp){
                                $mo_downpayment=[
                                    "name"=>$row_dp->lookable->code,
                                    "key" =>$row_dp->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_dp->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_dp->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_dp->lookable->code),
                                ];
                                $data_go_chart[]=$mo_downpayment;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_dp->lookable->code,
                                    'string_link'=>$query_invoice->code.$row_dp->lookable->code,
                                ];
                                
                                if(!in_array($row_dp->lookable->id, $data_id_mo_dp)){
                                    $data_id_mo_dp[] =$row_dp->lookable->id;
                                    $added = true;
                                }
                            }
                            
                        }
                        if($query_invoice->marketingOrderHandoverInvoiceDetail()->exists()){
                            foreach($query_invoice->marketingOrderHandoverInvoiceDetail as $row_handover_detail){
                                $mo_handover_tempura=[
                                    "name"=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    "key" =>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_handover_detail->marketingOrderHandoverInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_handover_detail->marketingOrderHandoverInvoice->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_handover_invoice?code=".CustomHelper::encrypt($row_handover_detail->marketingOrderHandoverInvoice->code),
                                ];
                                $data_go_chart[]=$mo_handover_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    'string_link'=>$query_invoice->code.$row_handover_detail->marketingOrderHandoverInvoice->code,
                                ];
                                
                                if(!in_array($row_handover_detail->marketingOrderHandoverInvoice->id, $data_id_hand_over_invoice)){
                                    $data_id_hand_over_invoice[] =$row_handover_detail->marketingOrderHandoverInvoice->id;
                                    $added = true;
                                }
                            }
                        }
                        if($query_invoice->marketingOrderReceiptDetail()->exists()){
                            foreach($query_invoice->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                                $mo_receipt_tempura=[
                                    "name"=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    "key" =>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_receipt_detail->marketingOrderReceipt->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->marketingOrderReceipt->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_receipt?code=".CustomHelper::encrypt($row_mo_receipt_detail->marketingOrderReceipt->code),
                                ];
                                $data_go_chart[]=$mo_receipt_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    'string_link'=>$query_invoice->code.$row_mo_receipt_detail->marketingOrderReceipt->code,
                                ];
                                
                                if(!in_array($row_mo_receipt_detail->marketingOrderReceipt->id, $data_id_mo_receipt)){
                                    $data_id_mo_receipt[] =$row_mo_receipt_detail->marketingOrderReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        foreach($query_invoice->marketingOrderInvoiceDetail as $row_invoice_detail){
                            if($row_invoice_detail->marketingOrderMemoDetail()->exists()){
                                foreach($row_invoice_detail->marketingOrderMemoDetail as $row_memo){
                                    $mo_memo=[
                                        "name"=>$row_memo->marketingOrderMemo->code,
                                        "key" => $row_memo->marketingOrderMemo->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_memo->marketingOrderMemo->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_memo->marketingOrderMemo->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_memo?code=".CustomHelper::encrypt($row_memo->marketingOrderMemo->code),
                                    ];
                                    $data_go_chart[]=$mo_memo;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_memo->marketingOrderMemo->code,
                                        'string_link'=>$query_invoice->code.$row_memo->marketingOrderMemo->code,
                                    ];
                                    $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                    // if(!in_array($row_memo->marketingOrderMemo->id, $data_id_mo_memo)){
                                    //     $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                    //     $added = true;
                                    // }
                                }
                            }
                            
                        }
                    }

                }

                foreach($data_id_mo_memo as $row_id_memo){
                    if(!in_array($row_id_memo, $finished_data_id_memo)){
                        $finished_data_id_memo[]=$row_id_memo;
                        $query_mo_memo = MarketingOrderMemo::find($row_id_memo);
                        if($query_mo_memo->incomingPaymentDetail()->exists()){
                            foreach($query_mo_memo->incomingPaymentDetail as $ip_detail){
                                $ip_tempura = [
                                    "name"=>$ip_detail->incomingPayment->code,
                                    "key" => $ip_detail->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$ip_detail->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($ip_detail->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($ip_detail->incomingPayment->code),
                                ];
                                
                                $data_go_chart[]=$ip_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_memo->code,
                                    'to'=>$ip_detail->incomingPayment->code,
                                    'string_link'=>$query_mo_memo->code.$ip_detail->incomingPayment->code,
                                ];
                                if(!in_array($ip_detail->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[]=$ip_detail->incomingPayment->id;
                                    $added = true;
                                }  
                            }    
                        }
                        foreach($query_mo_memo->marketingOrderMemoDetail as $row_mo_memo_detail){
                                if($row_mo_memo_detail->marketingOrderDownPayment()){
                                    $mo_downpayment=[
                                        "name"=>$row_mo_memo_detail->lookable->code,
                                        "key" => $row_mo_memo_detail->lookable->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                    ];
                                    $data_go_chart[]=$mo_downpayment;
                                    $data_link[]=[
                                        'from'=>$row_mo_memo_detail->lookable->code,
                                        'to'=>$query_mo_memo->code,
                                        'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                    ];
                                    $data_id_mo_dp[] = $row_mo_memo_detail->lookable->id;
                                    
                                    
                                }
                                if($row_mo_memo_detail->marketingOrderInvoiceDetail()){
                                    $mo_invoice_tempura=[
                                        "name"=>$row_mo_memo_detail->lookable->code,
                                        "key" => $row_mo_memo_detail->lookable->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                    ];
                                    $data_go_chart[]=$mo_invoice_tempura;
                                    $data_link[]=[
                                        'from'=>$row_mo_memo_detail->lookable->code,
                                        'to'=>$query_mo_memo->code,
                                        'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                    ];
                                    $data_id_mo_invoice[] = $row_mo_memo_detail->lookable->id;
                                    
                                }
                        }
                    }

                }
               
                foreach($data_id_mo_return as $row_id_mo_return){
                    if(!in_array($row_id_mo_return, $finished_data_id_mo_return)){
                        $finished_data_id_mo_return[]=$row_id_mo_return;
                        $query_mo_return = MarketingOrderReturn::find($row_id_mo_return);
                        foreach($query_mo_return->marketingOrderReturnDetail as $row_mo_return_detail){
                            if($row_id_mo_return->marketingOrderDeliveryDetail()->exists()){
                                $data_mo_delivery_tempura = [
                                    "name"=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    "key" => $row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_delivery/?code=".CustomHelper::encrypt($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code),
                                ];
                                $data_go_chart[]=$data_mo_delivery_tempura;
                                $data_link[]=[
                                    'from'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    'to'=>$query_mo_return->code,
                                    'string_link'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code.$query_mo_return->code,
                                ];
                                if(!in_array($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id, $data_id_mo_delivery)){
                                    $data_id_mo_delivery[]=$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id;
                                    $added = true;
                                }
                            }
                            
                            
                            
                        }
                    }
                }
                // mencari delivery anakan
                foreach($data_id_mo_delivery as $row_id_mo_delivery){
                    if(!in_array($row_id_mo_delivery, $finished_data_id_mo_delivery)){
                        $finished_data_id_mo_delivery[]=$row_id_mo_delivery;
                        $query_mo_delivery = MarketingOrderDelivery::find($row_id_mo_delivery);
                        if($query_mo_delivery->marketingOrderDeliveryProcess()->exists()){
                            $data_mo_delivery_process = [
                                "name"=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                                "key" => $query_mo_delivery->marketingOrderDeliveryProcess->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_mo_delivery->marketingOrderDeliveryProcess->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrderDeliveryProcess->code),
                            ];
                            
                            $data_go_chart[]=$data_mo_delivery_process;
                            $data_link[]=[
                                'from'=>$query_mo_delivery->code,
                                'to'=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                                'string_link'=>$query_mo_delivery->code.$query_mo_delivery->marketingOrderDeliveryProcess->code,
                            ];
                            if(!in_array($query_mo_delivery->marketingOrderDeliveryProcess->id, $data_id_mo_delivery_process)){
                                $data_id_mo_delivery_process[]=$query_mo_delivery->marketingOrderDeliveryProcess->id;
                                $added = true;
                            }
                            
                            
                        }//mencari process dari delivery
                        foreach($query_mo_delivery->marketingOrderDeliveryDetail as $row_delivery_detail){
                            if($row_delivery_detail->marketingOrderInvoiceDetail()->exists()){
                                $arr = [];
                                foreach($row_delivery_detail->marketingOrderInvoiceDetail as $row_invoice_detail){
                                    if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                                        foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                                            $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                                        }
                                    }
                                    
                                    $newArray = array_unique($arr);
                                    $string = implode(', ', $newArray);
                                    $data_invoice = [
                                        "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                                        "key" => $row_invoice_detail->marketingOrderInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')],
                                            ['name'=> "No Surat Jalan  :".$string.""]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                                    ];
                                    
                                    $data_go_chart[]=$data_invoice;
                                    $data_link[]=[
                                        'from'=>$query_mo_delivery->code,
                                        'to'=>$row_invoice_detail->marketingOrderInvoice->code,
                                        'string_link'=>$query_mo_delivery->code.$row_invoice_detail->marketingOrderInvoice->code,
                                    ];
                                    
                                    if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                                        $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                                        $added = true;
                                    }
                                }
                            }//mencari marketing order invoice

                            if($row_delivery_detail->marketingOrderReturnDetail()->exists()){
                                foreach($row_delivery_detail->marketingOrderReturnDetail as $row_return_detail){
                                    $data_return = [
                                        "name"=>$row_return_detail->marketingOrderReturn->code,
                                        "key" => $row_return_detail->marketingOrderReturn->code,
                                        
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_return_detail->marketingOrderReturn->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_return_detail->marketingOrderReturn->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_return_detail->marketingOrderReturn->code),
                                    ];
                                    
                                    $data_go_chart[]=$data_return;
                                    $data_link[]=[
                                        'from'=>$query_mo_delivery->code,
                                        'to'=>$row_return_detail->marketingOrderReturn->code,
                                        'string_link'=>$query_mo_delivery->code.$row_return_detail->marketingOrderReturn->code,
                                    ];
                                    
                                    $data_id_mo_return[]=$row_return_detail->marketingOrderReturn->id;
                                }
                            }//mencari marketing order return
                        }
                        if($query_mo_delivery->marketingOrder()->exists()){
                            $data_marketing_order = [
                                "name"=> $query_mo_delivery->marketingOrder->code,
                                "key" => $query_mo_delivery->marketingOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_mo_delivery->marketingOrder->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrder->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/marketing_order_delivery?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrder->code),           
                            ];
                
                            $data_go_chart[]= $data_marketing_order;
                            $data_id_mo[]=$query_mo_delivery->marketingOrder->id;
                        }
                    }
                }

                foreach($data_id_mo as $row_id_mo){
                    if(!in_array($row_id_mo, $finished_data_id_mo)){
                        $finished_data_id_mo[]=$row_id_mo;
                        $query_mo= MarketingOrder::find($row_id_mo);

                        foreach($query_mo->marketingOrderDelivery as $row_mod_del){
                            $modelvery=[
                                "name"=>$row_mod_del->code,
                                "key" => $row_mod_del->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_mod_del->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($row_mod_del->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_mod_del->code),  
                            ];
        
                            $data_go_chart[]=$modelvery;
                            $data_link[]=[
                                'from'=>$query_mo->code,
                                'to'=>$row_mod_del->code,
                                'string_link'=>$query_mo->code.$row_mod_del->code
                            ]; 

                            if(!in_array($row_mod_del->id, $data_id_mo_delivery)){
                                $data_id_mo_delivery[] = $row_mod_del->id; 
                                $added = true;
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
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new MarketingOrder())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Order data');
    
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

    // public function exportFromTransactionPage(Request $request){
    //     $search= $request->search? $request->search : '';
    //     $status = $request->status? $request->status : '';
    //     $type_buy = $request->type_buy ? $request->type_buy : '';
    //     $type_deliv = $request->type_deliv? $request->type_deliv : '';
    //     $company = $request->company ? $request->company : '';
    //     $type_pay = $request->type_pay ? $request->type_pay : '';
    //     $sender = $request->sender? $request->sender : '';
    //     $sales = $request->sales ? $request->sales : '';
    //     $supplier = $request->supplier? $request->supplier : '';
    //     $currency = $request->currency ? $request->currency : '';
    //     $end_date = $request->end_date ? $request->end_date : '';
    //     $start_date = $request->start_date? $request->start_date : '';
	// 	$modedata = $request->modedata? $request->modedata : '';
      
	// 	return Excel::download(new ExportPurchaseOrderTransactionPage($search,$status,$type_buy,$type_deliv,$company,$type_pay,$supplier,$currency,$end_date,$start_date,$modedata), 'purchase_order_'.uniqid().'.xlsx');
    // }

}