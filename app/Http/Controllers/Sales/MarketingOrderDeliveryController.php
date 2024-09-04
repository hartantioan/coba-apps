<?php

namespace App\Http\Controllers\Sales;
use App\Exports\ExportTransactionPageOrderDelivery;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Helpers\TreeHelper;
use App\Models\IncomingPayment;
use App\Models\ItemStock;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDetail;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Illuminate\Support\Str;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\Menu;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MenuUser;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use App\Models\Place;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Item;
use App\Models\MarketingOrderDeliveryStock;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use App\Models\UsedData;
class MarketingOrderDeliveryController extends Controller
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
            'title'         => 'Marketing Order Delivery',
            'content'       => 'admin.sales.order_delivery',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = MarketingOrderDelivery::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'send_status',
            'code',
            'user_id',
            'user_update_id',
            'company_id',
            'account_id',
            'customer_id',
            'destination_address',
            'district_id',
            'city_id',
            'transportation_id',
            'cost_delivery_type',
            'type_delivery',
            'post_date',
            'delivery_date',
            'note_internal',
            'note_external'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDelivery::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderDelivery::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note_internal', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
                            ->orWhere('destination_address','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_id){
                    $query->whereIn('marketing_order_id',$request->marketing_order_id);
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

        $total_filtered = MarketingOrderDelivery::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note_internal', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
                            ->orWhere('destination_address','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_id){
                    $query->whereIn('marketing_order_id',$request->marketing_order_id);
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
                $dis = '';
                if($val->isOpenPeriod()){

                    $dis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                   
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    '
                        <select class="browser-default" onchange="updateSendStatus(`'.CustomHelper::encrypt($val->code).'`,this)" style="width:150px;">
                            <option value="" '.(!$val->send_status ? 'selected' : '').'>BELUM SIAP</option>
                            <option value="1" '.($val->send_status == '1' ? 'selected' : '').'>SIAP DIKIRIM</option>
                        </select>
                    ',
                    $val->code,
                    $val->user->name,
                    $val->userUpdate()->exists() ? $val->userUpdate->name : '-',
                    $val->company->name,
                    $val->account->name,
                    $val->customer->name ?? '-',
                    $val->destination_address,
                    $val->district->name,
                    $val->city->name,
                    $val->transportation->name,
                    $val->costDeliveryType(),
                    $val->deliveryType(),
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->delivery_date)),
                    $val->note_internal,
                    $val->note_external,
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

    public function getMarketingOrder(Request $request){
        $data = MarketingOrder::find($request->id);
        $data['sender_name'] = $data->sender->name;
        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Marketing Order No. '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($data->hasBalanceMod()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Marketing Order Delivery');

                $details = [];

                foreach($data->marketingOrderDetail as $row){
                    $details[] = [
                        'id'            => $row->id,
                        'item_id'       => $row->item_id,
                        'item_name'     => $row->item->code.' - '.$row->item->name,
                        'place_id'      => $row->place_id,
                        'place_name'    => $row->place->code,
                        'warehouse'     => $row->item->warehouseName(),
                        'qty_stock'     => CustomHelper::formatConditionalQty(round($row->item->getStockPlaceWithUnsentSales($row->place_id) / $row->qty_conversion,3)),
                        'qty'           => CustomHelper::formatConditionalQty($row->balanceQtyMod()),
                        'qty_uom'       => CustomHelper::formatConditionalQty(round($row->balanceQtyMod() * $row->qty_conversion,3)),
                        'unit'          => $row->itemUnit->unit->code,
                        'note'          => $row->note,
                        'qty_conversion'=> CustomHelper::formatConditionalQty($row->qty_conversion),
                        'code'          => $data->code,
                        'uom_unit'      => $row->item->uomUnit->code,
                    ];
                }

                $data['details'] = $details;
                $data['district_name'] = $data->district->name;
                $data['city_name'] = $data->city->name;
                $data['province_name'] = $data->province->name;
                $data['transportation_name'] = $data->transportation()->exists() ? $data->transportation->name : '';
                $data['cost_delivery_type'] = $data->transportation()->exists() ? $data->transportation->category_transportation : '';
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada Sales Order No. '.$data->code.' sudah menjadi MOD. Data tidak bisa ditambahkan.';
            }
        }

        return response()->json($data);
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $account_id = $request->account_id? $request->account_id : '';
        $company = $request->company ? $request->company : '';
        $marketing_order = $request->marketing_order ? $request->marketing_order:'';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportTransactionPageOrderDelivery($search,$status,$account_id,$company,$marketing_order,$end_date,$start_date), 'marketing_order_delivery_'.uniqid().'.xlsx');
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('marketing_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            /* 'code'			        => $request->temp ? ['required', Rule::unique('marketing_order_deliveries', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_deliveries,code',
             */'code_place_id'          => 'required',
            'account_id' 				=> 'required',
            'company_id'			    => 'required',
            'customer_id'		        => 'required',
            'post_date'		            => 'required',
            'delivery_date'		        => 'required',
            'cost_delivery_type'		=> 'required',
            'arr_modi'                  => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_qty'                   => 'required|array',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
           /*  'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'account_id.required' 				=> 'Vendor/Ekspedisi/Broker tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'customer_id.required' 	            => 'Customer tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'delivery_date.required' 			=> 'Tanggal kirim tidak boleh kosong.',
            'cost_delivery_type.required' 		=> 'Metode Hitung Ongkir tidak boleh kosong.',
            'arr_modi.required'                 => 'Data detail sales order tidak boleh kosong.',
            'arr_modi.array'                    => 'Data detail sales order harus array.',
            'arr_item.required'                 => 'Item baris tidak boleh kosong.',
            'arr_item.array'                    => 'item baris harus array.',
            'arr_qty.required'                  => 'Baris qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Baris qty harus array.',
            'arr_place.required'                => 'Baris plant tidak boleh kosong.',
            'arr_place.array'                   => 'Baris plant harus array.',
       ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedZero = true;
            $passedQty = true;
            $passedSentMore = true;
            $passedCreditLimit = true;
            $totalLimitCredit = 0;
            $totalSent = 0;

            $user = User::find($request->customer_id);

            $grandtotalUnsentModCredit = $user->grandtotalUnsentModCredit();
            $grandtotalUnsentModDp = $user->grandtotalUnsentModDp();
            $grandtotalUnsentDoCredit = $user->grandtotalUninvoiceDoCredit();
            $grandtotalUnsentDoDp = $user->grandtotalUninvoiceDoDp();
           
            $arrmo = array_unique($request->arr_mo);
            
            $total = 0;
            $tax = 0;
            $grandtotal = 0;
            $totalCredit = 0;
            $totalDp = 0;

            foreach($arrmo as $rowmo){
                $cekmo = MarketingOrder::find($rowmo);

                if($request->arr_qty){
                    foreach($request->arr_qty as $key => $row){
                        $datamodi = MarketingOrderDetail::find($request->arr_modi[$key]);
                        if($datamodi->marketing_order_id == $cekmo->id){
                            $rowtotal = $datamodi->realPriceAfterGlobalDiscount() * str_replace(',','.',str_replace('.','',$row));
                            $rowtax = 0;
                            if($datamodi->tax_id > 0){
                                if($datamodi->is_include_tax == '1'){
                                    $rowtotal = $rowtotal * (1 + ($datamodi->percent_tax / 100));
                                }
                                $rowtax += $rowtotal * ($datamodi->percent_tax / 100);
                            }
    
                            $total += $rowtotal;
                            $tax += $rowtax;   
                        }      
                    }

                    $grandtotal = $total + $tax;

                    $percent_credit = 100 - $cekmo->percent_dp;

                    $totalCredit += ($percent_credit / 100) * $grandtotal;
                    $totalDp += ($cekmo->percent_dp / 100) * $grandtotal;
                }
            }

            $balanceLimitCredit = $user->limit_credit - $user->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit - $totalCredit;
            $balanceLimitDp = $user->deposit - $grandtotalUnsentModDp - $grandtotalUnsentDoDp - $totalDp;
            $totalLimitCredit = $user->limit_credit - $user->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit;
            $totalLimitDp = $user->deposit - $grandtotalUnsentModDp - $grandtotalUnsentDoDp;
            
            if($balanceLimitCredit < 0){
                $passedCreditLimit = false;
            }

            if($balanceLimitDp < 0){
                $passedCreditLimit = false;
            }

            

            // if(!$passedCreditLimit){
            //     return response()->json([
            //         'status'  => 500,
            //         'message' => 'Mohon maaf, saat ini seluruh / salah satu item terkena limit kredit dimana perhitungannya adalah sebagai berikut, Sisa limit kredit '.number_format($totalLimitCredit,2,',','.').' sedangkan nominal Item Kredit terkirim : '.number_format($totalCredit,2,',','.').' maka terjadi selisih nominal kirim sebesar '.number_format($totalLimitCredit - $totalCredit,2,',','.').'. Dan sisa limit DP '.number_format($totalLimitDp,2,',','.').' sedangkan nominal Item DP terkirim : '.number_format($totalDp,2,',','.').' maka terjadi selisih nominal kirim sebesar '.number_format($totalLimitDp - $totalDp,2,',','.').'.',
            //     ]);
            // }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->temp))->first();

                    /* $approved = false;
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
                            'message' => 'Marketing Order Delivery telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    } */
                    if(in_array($query->status,['2'])){

                        $query->user_update_id = session('bo_id');
                        $query->update_time = date('Y-m-d H:i:s');
                        /* $query->code = $request->code; */
                        $query->account_id = $request->account_id;
                        $query->cost_delivery_type = $request->cost_delivery_type;
                        /* $query->company_id = $request->company_id;
                        $query->customer_id = $request->customer_id;
                        $query->post_date = $request->post_date;
                        $query->delivery_date = $request->delivery_date;
                        $query->destination_address = $request->destination_address;
                        $query->city_id = $request->tempCity;
                        $query->district_id = $request->tempDistrict;
                        $query->transportation_id = $request->tempTransport;
                        $query->note_internal = $request->note_internal;
                        $query->note_external = $request->note_external;
                        $query->send_status = NULL;
                        $query->status = '1'; */

                        $query->save();
            
                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Marketing Order Delivery hanya bisa diupdate untuk status dokumen PROSES.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
          
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=MarketingOrderDelivery::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = MarketingOrderDelivery::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'customer_id'	            => $request->customer_id,
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'destination_address'       => $request->destination_address,
                        'city_id'                   => $request->tempCity,
                        'district_id'               => $request->tempDistrict,
                        'transportation_id'         => $request->tempTransport,
                        'cost_delivery_type'        => $request->cost_delivery_type,
                        'type_delivery'             => $request->tempTypeDelivery,
                        'top_internal'              => $request->tempTopInternal,
                        'note_internal'             => $request->note_internal,
                        'note_external'             => $request->note_external,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                
			}
			
			if($query) {
                if(!$request->temp){
                    foreach($query->marketingOrderDeliveryDetail as $row){
                        $row->delete();
                    }
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_modi as $key => $row){
                            $querydetail = MarketingOrderDeliveryDetail::create([
                                'marketing_order_delivery_id'   => $query->id,
                                'marketing_order_detail_id'     => $row,
                                'item_id'                       => $request->arr_item[$key],
                                'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'note'                          => $request->arr_note[$key],
                                'place_id'                      => $request->arr_place[$key],
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                    $query->updateGrandtotal();
                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note_internal.' - '.$query->note_external);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Marketing Order Delivery No. '.$query->code.' Tahap 1',$query->note_internal.' - '.$query->note_external,session('bo_id'));
                    activity()
                        ->performedOn(new MarketingOrderDelivery())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add marketing order delivery.');
                }else{
                    CustomHelper::sendApprovalWithoutDelete($query->getTable(),$query->id,$query->note_internal.' - '.$query->note_external);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Marketing Order Delivery No. '.$query->code.' Tahap 2',$query->note_internal.' - '.$query->note_external,session('bo_id'));
                    activity()
                        ->performedOn(new MarketingOrderDelivery())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Edit marketing order delivery.');
                }

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
        $po = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        if($po->status !== '2'){
            return response()->json([
                'status'  => 500,
                'message' => 'Mohon maaf status dokumen sudah diluar perubahan. Anda tidak bisa melakukan perubahan.',
            ]);
        }
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->employee_no.' - '.$po->account->name;
        $po['customer_name'] = $po->customer->employee_no.' - '.$po->customer->name;
        $po['district_name'] = $po->district->name;
        $po['city_name'] = $po->city->name;
        $po['transportation_name'] = $po->transportation->name;

        $arr = [];
        
        foreach($po->marketingOrderDeliveryDetail as $row){
            $arrstock = [];

            $arr[] = [
                'marketing_order_id'    => $row->marketingOrderDetail->marketing_order_id,
                'so_no'                 => $row->marketingOrderDetail->marketingOrder->code,
                'mo'                    => $row->marketingOrderDetail->marketing_order_id,
                'id'                    => $row->marketing_order_detail_id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'warehouse'             => $row->item->warehouseName(),
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'qty_uom'               => CustomHelper::formatConditionalQty(round($row->qty * $row->marketingOrderDetail->qty_conversion,3)),
                'qty_original'          => CustomHelper::formatConditionalQty($row->marketingOrderDetail->qty),
                'unit'                  => $row->marketingOrderDetail->itemUnit->unit->code,
                'note'                  => $row->note,
                'place_id'              => $row->place_id,
                'qty_conversion'        => CustomHelper::formatConditionalQty($row->marketingOrderDetail->qty_conversion),
                'detail_stock'          => $arrstock,
                'stock'                 => CustomHelper::formatConditionalQty(round($row->item->getStockPlace($row->place_id) / $row->marketingOrderDetail->qty_conversion,3)),
                'district_name'         => $row->marketingOrderDetail->marketingOrder->district->name,
                'city_name'             => $row->marketingOrderDetail->marketingOrder->city->name,
                'province_name'         => $row->marketingOrderDetail->marketingOrder->province->name,
                'transportation_name'   => $row->marketingOrderDetail->marketingOrder->transportation->name,
                'payment_type'          => $row->marketingOrderDetail->marketingOrder->payment_type,
                'down_payment'          => $row->marketingOrderDetail->marketingOrder->percent_dp,
                'type_delivery'         => $row->marketingOrderDetail->marketingOrder->type_delivery,
                'uom_unit'              => $row->item->uomUnit->code,
            ];
            
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;" class="bordered">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="17">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;

        foreach($data->marketingOrderDeliveryDetail as $key => $row){
            $totalqty+=$row->qty;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->marketingOrderDetail->marketingOrder->code.'</td>
                <td class="center-align">'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->marketingOrderDetail->itemUnit->unit->code.'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . CustomHelper::formatConditionalQty($totalqty) . '</td>
                <td colspan="2"></td>
            </tr>  
        ';
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-2"><table style="min-width:100%;" class="bordered">
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
        
        $mod = MarketingOrderDelivery::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Print Marketing Order Delivery',
                'data'      => $mod
            ];

            return view('admin.approval.marketing_order_delivery', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = MarketingOrderDelivery::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Print Marketing Order Delivery','a4','portrait','admin.print.sales.order_delivery_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     
    
            return $document_po;
        }else{
            abort(404);
        }
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
                $pr = MarketingOrderDelivery::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Print Marketing Order Delivery','a4','portrait','admin.print.sales.order_delivery_individual');
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
                        $query = MarketingOrderDelivery::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Marketing Order Delivery','a4','portrait','admin.print.sales.order_delivery_individual');
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
                        $query = MarketingOrderDelivery::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Marketing Order Delivery','a4','portrait','admin.print.sales.order_delivery_individual');
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

    public function voidStatus(Request $request){
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new MarketingOrderDelivery())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the marketing order delivery data');
    
                CustomHelper::sendNotification('marketing_order_deliveries',$query->id,'Marketing Order Delivery No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('marketing_order_deliveries',$query->id);

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
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->marketingOrderDeliveryDetail()->delete();

            CustomHelper::removeApproval('marketing_order_deliveries',$query->id);

            activity()
                ->performedOn(new MarketingOrderDelivery())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the marketing order delivery data');

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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $data_mo_delivery = [
            "name"=>$query->code,
            "key" => $query->code,
            "color"=>"lightblue",
            'properties'=> [
                ['name'=> "Tanggal :".$query->post_date],
                ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
             ],
            'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),           
        ];
        $data_go_chart[]= $data_mo_delivery;
        if($query){
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_mo_delivery',$query->id);
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
                'link'    => $data_link,
            ];


        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function updateSendStatus(Request $request){
        $data = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->code))->first();
        if($data){
            if($data->status !== '3'){
                $response = [
                    'status'  => 500,
                    'message' => 'Maaf, data tidak bisa diupdate, karena dokumen SELESAI saja yang bisa dirubah.',
                    'value'   => '',
                ];
            }else{
                if(!$data->marketingOrderDeliveryProcess()->exists()){
                    $data->update([
                        'send_status'   => $request->status ? $request->status : NULL,
                    ]);
    
                    CustomHelper::sendNotification($data->getTable(),$data->id,'Status Pengiriman Surat Jalan No. '.$data->code.' telah diupdate','Status pengiriman dokumen anda telah dinyatakan '.$data->sendStatus().'.',session('bo_id'));
    
                    $response = [
                        'status'  => 200,
                        'message' => 'Status berhasil dirubah.',
                        'value'   => $data->send_status ? $data->send_status : '',
                    ];
                }else{
                    $response = [
                        'status'  => 422,
                        'message' => 'Maaf, data sudah dijadikan Surat Jalan.',
                        'value'   => $data->send_status ? $data->send_status : '',
                    ];
                }
            }
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Maaf, data tidak ditemukan.',
                'value'   => '',
            ];
        }

        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new MarketingOrderDelivery())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Order Delivery data');
    
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