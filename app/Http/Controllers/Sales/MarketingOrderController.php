<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Helpers\TreeHelper;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Line;
use App\Exports\ExportMarketingOrderTransactionPage;
use App\Models\MarketingOrderDetail;

use App\Models\Place;
use App\Models\Machine;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\UserData;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Area;
use App\Models\CustomerDiscount;
use App\Models\DeliveryCostStandard;
use App\Models\Item;
use App\Models\ItemPricelist;
use App\Models\ItemUnit;
use App\Models\User;
use App\Models\Tax;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\StandardCustomerPrice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use App\Models\UsedData;
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
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
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
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = MarketingOrder::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getSalesItemInformation(Request $request){
        $item = Item::find($request->item_id);
        $account_id   = $request->account_id;
        $date   = $request->date;
        $city   = $request->city;
        $district = $request->district;
        $payment_type   = $request->payment_type;
        $user = User::find($account_id);
        $transportation = Transportation::find($request->transportation_id);
        $cek_price = ItemPricelist::where('group_id',$user->group_id)
            ->where('item_id',$item->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('status','1')
            ->first() ?? 0;
            
            $cek_delivery = DeliveryCostStandard::where('transportation_id',$transportation->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('city_id',$city)
            ->where('district_id',$district)
            ->where('status','1')
            ->first() ?? 0;

            $cek_type = StandardCustomerPrice::where('group_id',$user->group_id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('status','1')
            ->first() ?? 0;

            $cek_discount = CustomerDiscount::where('account_id',$user->id)
            ->where('brand_id',$item->brand_id)
            ->where('city_id',$city)
            ->where('payment_type',$payment_type)
            ->where('status','1')
            ->first() ?? 0;

        
        $response = [
            'old_prices'        => $item->oldSalePrices($this->dataplaces),
            'list_warehouse'    => $item->warehouseList(),
            'list_outletprice'  => $item->listOutletPrice(),
            'price'             => $cek_price->price ?? 0,
            'price_delivery'    => $cek_delivery->price ?? 0,
            'price_bp'          => $cek_type->price ?? 0,
            'disc1'             => $cek_discount->disc1 ?? 0,
            'disc2'             => $cek_discount->disc2 ?? 0,
            'disc3'             => $cek_discount->disc3 ?? 0,
            'stock_now'         => CustomHelper::formatConditionalQty($item->getStockArrayPlace($this->dataplaces)),
            'stock_com'         => CustomHelper::formatConditionalQty($item->getQtySalesNotSent($this->dataplaces)),
            'sell_units'        => $item->arrSellUnits(),
            'list_area'         => Area::where('status','1')->get(),
        ];
        
		return response()->json($response);
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
            'delivery_schedule',
            'payment_type',
            'dp_type',
            'top_internal',
            'top_customer',
            'billing_address',
            'outlet_id',
            'destination_address',
            'province_id',
            'city_id',
            'district_id',
            'phone',
            'sales_id',
            'currency_id',
            'currency_rate',
            'note_internal',
            'note_external',
           
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
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				$dis = '';
                // if($val->isOpenPeriod()){

                //     $dis = 'style="cursor: default;
                //     pointer-events: none;
                //     color: #9f9f9f !important;
                //     background-color: #dfdfdf !important;
                //     box-shadow: none;"';
                   
                // }
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
                    $val->sender()->exists() ? $val->sender->name : '-',
                    $val->transportation->name,
                    date('d/m/Y',strtotime($val->delivery_date)),
                    $val->deliverySchedule(),
                    $val->paymentType(),
                    $val->dpType(),
                    $val->top_internal,
                    $val->top_customer,
                    $val->billing_address,
                    $val->outlet()->exists() ? $val->outlet->name : '-',
                    $val->destination_address,
                    $val->province->name,
                    $val->city->name,
                    $val->district->name,
                    $val->phone,
                    $val->sales->name,
                    $val->broker()->exists() ? $val->broker->name : '-',
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->percent_dp,2,',','.'),
                    $val->note_internal,
                    $val->note_external,
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->total_after_tax,2,',','.'),
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
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
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
        /* DB::beginTransaction();
        try { */
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
                /* 'sender_id'                 => 'required', */
                'delivery_date'             => 'required',
                'delivery_schedule'         => 'required',
                'transportation_id'         => $request->type_delivery == '2' ? 'required' : '',
                
                'billing_address'           => 'required',
                'destination_address'       => 'required',
                'province_id'               => 'required',
                'city_id'                   => 'required',
                'district_id'               => 'required',
                'phone'                     => 'required',
                'payment_type'              => 'required',
                'dp_type'                   => $request->payment_type == '1' ? 'required' : '',
                'top_internal'              => 'required',
                'top_customer'              => 'required',
            
                'currency_id'               => 'required',
                'currency_rate'             => 'required',
                'percent_dp'                => 'required',
                'sales_id'                  => 'required',
                'arr_place'                 => 'required|array',
                'arr_tax_nominal'           => 'required|array',
                'arr_grandtotal'            => 'required|array',
                'arr_item'                  => 'required|array',
                'arr_unit'                  => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_qty_uom'               => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_tax'                   => 'required|array',
                'arr_is_include_tax'        => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'arr_final_price'           => 'required|array',
                'arr_total'                 => 'required|array',
            
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
                /* 'sender_id.required'                => 'Pihak pengirim tidak boleh kosong.', */
                'delivery_date.required'            => 'Tanggal pengiriman estimasi tidak boleh kosong.',
                'delivery_schedule.required'        => 'Jadwal kirim tidak boleh kosong.',
                'transportation_id.required'        => 'Tipe transportasi tidak boleh kosong.',
            
                'billing_address.required'          => 'Alamat penagihan tidak boleh kosong.',
                'destination_address.required'      => 'Alamat tujuan tidak boleh kosong.',
                'province_id.required'              => 'Provinsi tujuan tidak boleh kosong.',
                'city_id.required'                  => 'Kota tujuan tidak boleh kosong.',
                'district_id.required'              => 'Kecamatan tujuan tidak boleh kosong.',
                'phone.required'                    => 'Telepon customer tidak boleh kosong.',
                'payment_type.required'             => 'Tipe pembayaran tidak boleh kosong.',
                'dp_type.required'                  => 'Tipe DP tidak boleh kosong untuk tipe pembayaran DP.',
                'top_internal.required'             => 'TOP internal tidak boleh kosong.',
                'top_customer.required'             => 'TOP customer tidak boleh kosong',
                
                'currency_id.required'              => 'Mata uang tidak boleh kosong.',
                'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
                'percent_dp.required'               => 'Prosentase DP tidak boleh kosong. Silahkan isi 0 jika memang tidak ada.',
                'sales_id.required'                 => 'Sales tidak boleh kosong',
                'arr_place.required'                => 'Plant tidak boleh kosong.',
                'arr_place.array'                   => 'Plant harus array.',
                'arr_tax_nominal.required'          => 'Tax nominal tidak boleh kosong.',
                'arr_tax_nominal.array'             => 'Tax nominal harus array.',
                'arr_grandtotal.required'           => 'Grantotal baris tidak boleh kosong.',
                'arr_grandtotal.array'              => 'Grandtotal baris harus array.',
                'arr_item.required'                 => 'Item baris tidak boleh kosong.',
                'arr_item.array'                    => 'item baris harus array.',
                'arr_unit.required'                 => 'Satuan tidak boleh kosong.',
                'arr_unit.array'                    => 'Satuan harus array.',
                'arr_qty.required'                  => 'Baris qty tidak boleh kosong.',
                'arr_qty.array'                     => 'Baris qty harus array.',
                'arr_qty_uom.required'              => 'Baris qty tidak boleh kosong.',
                'arr_qty_uom.array'                 => 'Baris qty harus array.',
                'arr_price.required'                => 'Baris harga tidak boleh kosong.',
                'arr_price.array'                   => 'Baris harga harus array.',
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
                'arr_final_price.required'          => 'Baris harga akhir tidak boleh kosong.',
                'arr_final_price.array'             => 'Baris harga akhir harus array.',
                'arr_total.required'                => 'Baris total tidak boleh kosong.',
                'arr_total.array'                   => 'Baris total harus array.',
                'discount.required'                 => 'Diskon akhir tidak boleh kosong.',
            
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

                $passedNettPrice = true;
                $arrMessage = [];
                foreach($request->arr_item as $key => $row){
                    $item = Item::find($row);
                    $codePlace = Place::where('code',$request->arr_place[$key])->where('status','1')->first();
                    if($item){
                        $priceNett = $item->cogsSales($codePlace->id,$request->post_date);
                        if($priceNett <= 0){
                            $passedNettPrice = false;
                            $arrMessage[] = 'Item '.$item->code.' - '.$item->name.' belum memiliki harga nett price dari hpp atau kalkulator BOM.';
                        }
                    }
                }

                if(!$passedNettPrice){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode(', ',$arrMessage),
                    ]);
                }

                $userData = UserData::find($request->billing_address);
                
                if($request->temp){
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
                    // if(!CustomHelper::checkLockAcc($request->post_date)){
                    //     return response()->json([
                    //         'status'  => 500,
                    //         'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                    //     ]);
                    // }
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
                        $query->sender_id = $request->sender_id ?? NULL;
                        $query->delivery_date = $request->delivery_date;
                        $query->delivery_schedule = $request->delivery_schedule;
                        $query->payment_type = $request->payment_type;
                        $query->dp_type = $request->payment_type == '1' ? $request->dp_type : NULL;
                        $query->top_internal = $request->top_internal;
                        $query->top_customer = $request->top_customer;
                        $query->transportation_id = $request->transportation_id;
                        $query->outlet_id = $request->outlet_id;
                        $query->user_data_id = $request->billing_address;
                        $query->billing_address = $userData->title.' '.$userData->npwp.' '.$userData->address;
                        $query->destination_address = $request->destination_address;
                        $query->province_id = $request->province_id;
                        $query->city_id = $request->city_id;
                        $query->district_id = $request->district_id;
                        $query->phone = $request->phone;
                        $query->sales_id = $request->sales_id;
                        $query->broker_id = $request->broker_id ?? NULL;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->percent_dp = str_replace(',','.',str_replace('.','',$request->percent_dp));
                        $query->note_internal = $request->note_internal;
                        $query->note_external = $request->note_external;
                    
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
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status sales order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
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
                        'sender_id'                 => $request->sender_id ?? NULL,
                        'delivery_date'             => $request->delivery_date,
                        'delivery_schedule'         => $request->delivery_schedule,
                        'payment_type'              => $request->payment_type,
                        'dp_type'                   => $request->payment_type == '1' ? $request->dp_type : NULL,
                        'top_internal'              => $request->top_internal,
                        'top_customer'              => $request->top_customer,
                        'transportation_id'         => $request->transportation_id,
                        'outlet_id'                 => $request->outlet_id,
                        'user_data_id'              => $request->billing_address,
                        'billing_address'           => $userData->title.' '.$userData->npwp.' '.$userData->address,
                        'destination_address'       => $request->destination_address,
                        'province_id'               => $request->province_id,
                        'city_id'                   => $request->city_id,
                        'district_id'               => $request->district_id,
                        'phone'                     => $request->phone,
                        'sales_id'                  => $request->sales_id,
                        'broker_id'                 => $request->broker_id ?? NULL,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'percent_dp'                => str_replace(',','.',str_replace('.','',$request->percent_dp)),
                        'note_internal'             => $request->note_internal,
                        'note_external'             => $request->note_external,

                        'discount'                  => str_replace(',','.',str_replace('.','',$request->discount)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->total)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->tax)),
                        'total_after_tax'           => str_replace(',','.',str_replace('.','',$request->total_after_tax)),
                        /* 'rounding'                  => str_replace(',','.',str_replace('.','',$request->rounding)), */
                        'rounding'                  => 0,
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {
                    $marginprice = $query->account->getStandarPrice($request->post_date);
                    foreach($request->arr_item as $key => $row){
                        $itemUnit = ItemUnit::find(intval($request->arr_unit[$key]));
                        $codePlace = Place::where('code',$request->arr_place[$key])->where('status','1')->first();
                        $item = Item::find($row);
                        MarketingOrderDetail::create([
                            'marketing_order_id'            => $query->id,
                            'item_id'                       => $row,
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'item_unit_id'                  => $itemUnit->id,
                            'qty_conversion'                => $itemUnit->conversion,
                            'qty_uom'                       => str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])),
                            'price'                         => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'price_list'                    => str_replace(',','.',str_replace('.','',$request->arr_price_list[$key])),
                            'price_delivery'                => str_replace(',','.',str_replace('.','',$request->arr_price_delivery[$key])),
                            'price_type_bp'                 => str_replace(',','.',str_replace('.','',$request->arr_price_type_bp[$key])),
                            'price_nett'                    => $item->cogsSales($codePlace->id,$request->post_date) + $marginprice,
                            'is_include_tax'                => $request->arr_is_include_tax[$key],
                            'percent_tax'                   => $request->arr_tax[$key],
                            'tax_id'                        => $request->arr_tax_id[$key],
                            'percent_discount_1'            => str_replace(',','.',str_replace('.','',$request->arr_disc1[$key])),
                            'percent_discount_2'            => str_replace(',','.',str_replace('.','',$request->arr_disc2[$key])),
                            'discount_3'                    => str_replace(',','.',str_replace('.','',$request->arr_disc3[$key])),
                            'price_after_discount'          => str_replace(',','.',str_replace('.','',$request->arr_final_price[$key])),
                            'total'                         => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'tax'                           => $request->arr_tax_nominal[$key],
                            'grandtotal'                    => $request->arr_grandtotal[$key],
                            'note'                          => $request->arr_note[$key] ?? NULL,
                            'place_id'                      => $codePlace->id,
                        ]);
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

            /* DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        } */

		return response()->json($response);
    }

    public function show(Request $request){
        $po = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->name;
        $po['sender_name'] = $po->sender->name;
        $po['sales_name'] = $po->sales->name.' - '.$po->sales->phone.' Pos. '.($po->sales->position()->exists() ? $po->sales->position->name : '-').' Dep. '.($po->sales->position()->exists() ? $po->sales->position->division->department->name : '-');
        $po['province_name'] = $po->province->name;
        $po['cities'] = $po->province->getCity();
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
        $po['outlet_name'] = $po->outlet()->exists() ? $po->outlet->code.' - '.$po->outlet->name : '';
        $po['broker_name'] = $po->broker()->exists() ? $po->broker->employee_no.' - '.$po->broker->name : '';
        $po['deposit'] = number_format($po->account->deposit,2,',','.');
        $arr = [];
        
        foreach($po->marketingOrderDetail()->orderBy('id')->get() as $row){
            $arr[] = [
                'id'                    => $row->id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'qty_uom'               => CustomHelper::formatConditionalQty($row->qty_uom),
                'unit'                  => $row->itemUnit->unit->code,
                'price'                 => number_format($row->price,2,',','.'),
                'price_delivery'        => number_format($row->price_delivery,2,',','.'),
                'price_list'            => number_format($row->price_list,2,',','.'),
                'price_type_bp'         => number_format($row->price_type_bp,2,',','.'),
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
                'place_id'              => $row->place_id,
                'place_code'            => $row->place->code,
                'item_unit_id'          => $row->item_unit_id,
                'sell_units'            => $row->item->arrSellUnits(),
                'uom'                   => $row->item->uomUnit->code,
                'qty_now'               => CustomHelper::formatConditionalQty($row->item->getStockPlace($row->place_id)),
                'qty_commited'          => CustomHelper::formatConditionalQty($row->item->getQtySalesNotSentByPlace($row->place_id)),
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
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="17">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty Jual</th>
                                <th class="center-align">Satuan Jual</th>
                                <th class="center-align">Qty Stock</th>
                                <th class="center-align">Satuan Stock</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Discount 1 (%)</th>
                                <th class="center-align">Discount 2 (%)</th>
                                <th class="center-align">Discount 3 (Rp)</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Harga Final</th>
                                <th class="center-align">Total</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totalqtyuom=0;
        $totaldiskon1=0;
        $totaldiskon2=0;
        $totaldiskon3=0;
        $totalpriceafterdiscount=0;
        $totals=0;
        
        foreach($data->marketingOrderDetail as $key => $row){
            $totalqty+=$row->qty;
            $totalqtyuom+=$row->qty_uom;
            $totaldiskon1+=$row->percent_discount_1;
            $totaldiskon2+=$row->percent_discount_2;
            $totaldiskon3+=$row->discount3;
            $totalpriceafterdiscount+=$row->price_after_discount;
            $totals+=$row->total;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->itemUnit->unit->code.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_uom).'</td>
                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
                <td class="center-align">'.$row->place->code.'</td>
                <td class="right-align">'.number_format($row->price_after_discount,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqtyuom, 3, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon1, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon2, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldiskon3, 2, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpriceafterdiscount, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
            </tr>  
        ';

        $string .= '
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

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = MarketingOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Print Sales Order','a4','portrait','admin.print.sales.order_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            // if(!CustomHelper::checkLockAcc($query->post_date)){
            //     return response()->json([
            //         'status'  => 500,
            //         'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
            //     ]);
            // }

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
                    $pdf = PrintHelper::print($pr,'Print Sales Order','a4','portrait','admin.print.sales.order_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Sales Order','a4','portrait','admin.print.sales.order_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                        $query = MarketingOrder::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Sales Order','a4','portrait','admin.print.sales.order_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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


    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MarketingOrder::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        
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

        if($query){
            $data_go_chart[]= $data_marketing_order;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_mo',$query->id);
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

    public function exportFromTransactionPage(Request $request){
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