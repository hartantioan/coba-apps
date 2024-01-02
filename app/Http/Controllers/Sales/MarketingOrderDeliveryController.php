<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\IncomingPayment;
use App\Models\ItemStock;
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

use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use App\Models\Place;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;

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
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = MarketingOrderDelivery::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'send_status',
            'code',
            'user_id',
            'customer_id',
            'company_id',
            'account_id',
            'marketing_order_no',
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
                    $val->marketingOrder->account->name,
                    $val->company->name,
                    $val->account->name,
                    $val->marketingOrder->code,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->delivery_date)),
                    $val->note_internal,
                    $val->note_external,
                    $val->status(),
                    '
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
                        'warehouse_id'  => $row->warehouse_id,
                        'warehouse_name'=> $row->warehouse->name,
                        'area_id'       => $row->area_id,
                        'area_name'     => $row->area->name,
                        'list_stock'    => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                        'qty'           => number_format($row->balanceQtyMod(),3,',','.'),
                        'unit'          => $row->item->sellUnit->code,
                        'note'          => $row->note,
                        'item_stock_id' => $row->item_stock_id ? $row->item_stock_id : '',
                    ];
                }

                $data['details'] = $details;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada Sales Order No. '.$data->code.' sudah menjadi MOD. Data tidak bisa ditambahkan.';
            }
        }

        return response()->json($data);
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
            'code'			            => $request->temp ? ['required', Rule::unique('marketing_order_deliveries', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_deliveries,code',
            'code_place_id'             => 'required',
            'account_id' 				=> 'required',
            'company_id'			    => 'required',
            'marketing_order_id'		=> 'required',
            'post_date'		            => 'required',
            'delivery_date'		        => 'required',
            'arr_modi'                  => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_item_stock'            => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_warehouse'             => 'required|array',
            'arr_area'                  => 'required|array',
            'arr_qty'                   => 'required|array',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai',
            'account_id.required' 				=> 'Vendor/Ekspedisi/Broker tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'marketing_order_id.required' 	    => 'Sales Order tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'delivery_date.required' 			=> 'Tanggal kirim tidak boleh kosong.',
            'arr_modi.required'                 => 'Data detail sales order tidak boleh kosong.',
            'arr_modi.array'                    => 'Data detail sales order harus array.',
            'arr_item.required'                 => 'Item baris tidak boleh kosong.',
            'arr_item.array'                    => 'item baris harus array.',
            'arr_item_stock.required'           => 'Stok item tidak boleh kosong.',
            'arr_item_stock.array'              => 'Stok item harus array.',
            'arr_qty.required'                  => 'Baris qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Baris qty harus array.',
            'arr_place.required'                => 'Baris plant tidak boleh kosong.',
            'arr_place.array'                   => 'Baris plant harus array.',
            'arr_warehouse.required'            => 'Baris gudang tidak boleh kosong.',
            'arr_warehouse.array'               => 'Baris gudang harus array.',
            'arr_area.required'                 => 'Baris area tidak boleh kosong.',
            'arr_area.array'                    => 'Baris area harus array.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedZero = true;
            $passedQty = true;
            $passedCreditLimit = true;
            $totalLimitCredit = 0;
            $totalSent = 0;

            $cekmo = MarketingOrder::find($request->marketing_order_id);

            $grandtotalUnsentModCredit = $cekmo->account->grandtotalUnsentModCredit();
            $grandtotalUnsentModDp = $cekmo->account->grandtotalUnsentModDp();
            $grandtotalUnsentDoCredit = $cekmo->account->grandtotalUninvoiceDoCredit();
            $grandtotalUnsentDoDp = $cekmo->account->grandtotalUninvoiceDoDp();

            $total = 0;
            $tax = 0;
            $grandtotal = 0;

            if($request->arr_qty){
                foreach($request->arr_qty as $key => $row){
                    if(floatval(str_replace(',','.',str_replace('.','',$row))) == 0){
                        $passedZero = false;
                    }
                    $item_stock = ItemStock::find($request->arr_item_stock[$key]);
                    $qtysell = round(($item_stock->qty / $item_stock->item->sell_convert) - $item_stock->totalQtyUnapproved(),3);

                    if(floatval(str_replace(',','.',str_replace('.','',$row))) > $qtysell){
                        $passedQty = false;
                    }

                    $datamodi = MarketingOrderDetail::find($request->arr_modi[$key]);

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

                $grandtotal = $total + $tax;

                $percent_credit = 100 - $cekmo->percent_dp;

                $totalCredit = ($percent_credit / 100) * $grandtotal;
                $totalDp = ($cekmo->percent_dp / 100) * $grandtotal;

                $balanceLimitCredit = $cekmo->account->limit_credit - $cekmo->account->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit - $totalCredit;
                $balanceLimitDp = $cekmo->account->deposit - $grandtotalUnsentModDp - $grandtotalUnsentDoDp - $totalDp;
                $totalLimitCredit = $cekmo->account->limit_credit - $cekmo->account->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit;
                $totalLimitDp = $cekmo->account->deposit - $grandtotalUnsentModDp - $grandtotalUnsentDoDp;
                
                if($balanceLimitCredit < 0){
                    $passedCreditLimit = false;
                }

                if($balanceLimitDp < 0){
                    $passedCreditLimit = false;
                }

                $errorMessage = [];

                if(!$passedZero){
                    $errorMessage[] = 'Qty tidak boleh 0.';
                }

                if(!$passedQty){
                    $errorMessage[] = 'Salah satu item memiliki qty kurang dari stok saat ini.';
                }

                if(count($errorMessage) > 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode(', ',$errorMessage)
                    ]);
                }
            }

            if(!$passedCreditLimit){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf, saat ini seluruh / salah satu item terkena limit kredit dimana perhitungannya adalah sebagai berikut, Sisa limit kredit '.number_format($totalLimitCredit,2,',','.').' sedangkan nominal Item Kredit terkirim : '.number_format($totalCredit,2,',','.').' maka terjadi selisih nominal kirim sebesar '.number_format($totalLimitCredit - $totalCredit,2,',','.').'. Dan sisa limit DP '.number_format($totalLimitDp,2,',','.').' sedangkan nominal Item DP terkirim : '.number_format($totalDp,2,',','.').' maka terjadi selisih nominal kirim sebesar '.number_format($totalLimitDp - $totalDp,2,',','.').'.',
                ]);
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Marketing Order Delivery telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->marketing_order_id = $request->marketing_order_id;
                        $query->post_date = $request->post_date;
                        $query->delivery_date = $request->delivery_date;
                        $query->note_internal = $request->note_internal;
                        $query->note_external = $request->note_external;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->marketingOrderDeliveryDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Marketing Order Delivery detail sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDelivery::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'marketing_order_id'	    => $request->marketing_order_id,
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'note_internal'             => $request->note_internal,
                        'note_external'             => $request->note_external,
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
                    
                    foreach($request->arr_modi as $key => $row){
                        $querydetail = MarketingOrderDeliveryDetail::create([
                            'marketing_order_delivery_id'   => $query->id,
                            'marketing_order_detail_id'     => $row,
                            'item_id'                       => $request->arr_item[$key],
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                          => $request->arr_note[$key],
                            'item_stock_id'                 => $request->arr_item_stock[$key],
                            'place_id'                      => $request->arr_place[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                            'area_id'                       => $request->arr_area[$key],
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                $query->updateGrandtotal();

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note_internal.' - '.$query->note_external);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Marketing Order Delivery No. '.$query->code,$query->note_internal.' - '.$query->note_external,session('bo_id'));

                activity()
                    ->performedOn(new MarketingOrderDelivery())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit marketing order delivery.');

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
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->name;
        $po['marketing_order_code'] = $po->marketingOrder->code;
        $po['outlet'] = $po->marketingOrder->outlet->name;
        $po['address'] = $po->marketingOrder->destination_address;
        $po['province'] = $po->marketingOrder->province->name;
        $po['city'] = $po->marketingOrder->city->name;
        $po['district'] = $po->marketingOrder->district->name;
        $po['subdistrict'] = $po->marketingOrder->subdistrict->name;

        $arr = [];
        
        foreach($po->marketingOrderDeliveryDetail as $row){
            $arr[] = [
                'id'                    => $row->marketing_order_detail_id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'qty'                   => number_format($row->qty,3,',','.'),
                'unit'                  => $row->item->sellUnit->code,
                'note'                  => $row->note,
                'item_stock_id'         => $row->item_stock_id,
                'item_stock_name'       => $row->itemStock->place->code.' - '.$row->itemStock->warehouse->code,
                'item_stock_qty'        => number_format($row->itemStock->qty,3,',','.'),
                'list_stock'            => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
                'area_id'               => $row->area_id,
                'place_name'            => $row->place->code,
                'warehouse_name'        => $row->warehouse->name,
                'area_name'             => $row->area->name,
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="17">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Ambil Dari</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->marketingOrderDeliveryDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->marketingOrderDetail->marketingOrder->code.'</td>
                <td class="center-align">'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="center-align">'.$row->itemStock->place->name.' - '.$row->itemStock->warehouse->name.' - '.$row->itemStock->area->name.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->item->sellUnit->code.'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }
        
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
        
        $pr = MarketingOrderDelivery::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Marketing Order Delivery',
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
             
            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
            // $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            
    
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
                    $data = [
                        'title'     => 'Print Marketing Order Delivery',
                        'data'      => $pr,
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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
                            $data = [
                                'title'     => 'Print Marketing Order Delivery',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = MarketingOrderDelivery::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Marketing Order Delivery',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            $data_id_mo_delivery[]=$query->id;

            if($query->marketingOrder()->exists()){
                $data_marketing_order = [
                    "name"=> $query->marketingOrder->code,
                    "key" => $query->marketingOrder->code,
                    
                    'properties'=> [
                        ['name'=> "Tanggal :".$query->marketingOrder->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($query->marketingOrder->grandtotal,2,',','.')]
                     ],
                    'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->marketingOrder->code),           
                ];
    
                $data_go_chart[]= $data_marketing_order;
                $data_id_mo[]=$query->marketingOrder->id;
                
                $data_link[]=[
                    'from'=>$query->marketingOrder->code,
                    'to'=>$query->code,
                    'string_link'=>$query->marketingOrder->code.$query->code
                ]; 
            }
            if($query->marketingOrderDeliveryProcess()->exists()){
                $data_mo_de_pro=[
                    "name"=>$query->marketingOrderDeliveryProcess->code,
                    "key" => $query->marketingOrderDeliveryProcess->code,
                    
                    'properties'=> [
                        ['name'=> "Tanggal :".$query->marketingOrderDeliveryProcess->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($query->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                     ],
                    'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->marketingOrderDeliveryProcess->code),                
                ];

                
                $data_go_chart[]= $data_mo_de_pro;
                $data_id_mo_[]=$query->marketingOrderDeliveryProcess->id;
                
                $data_link[]=[
                    'from'=>$query->code,
                    'to'=>$query->marketingOrderDeliveryProcess->code,
                    'string_link'=>$query->code.$query->marketingOrderDeliveryProcess->code
                ]; 
            }
            $added = true;
            while($added){
                $added=false;
                
                // mencaari incoming payment
                foreach($data_incoming_payment as $row_id_ip){
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
                // menacari down_payment
                foreach($data_id_mo_dp as $row_id_dp){
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
                //marketing mo receipt
                foreach($data_id_mo_receipt as $id_mo_receipt){
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

                foreach($data_id_mo_delivery_process as $id_mo_delivery_process){
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

                //marketing handover receipt
                foreach($data_id_hand_over_receipt as $row_handover_id){
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
                //marketing handover invoice
                foreach($data_id_hand_over_invoice as $row_handover_invoice_id){
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

                // menacari anakan invoice
                foreach($data_id_mo_invoice as $row_id_invoice){
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

                foreach($data_id_mo_memo as $row_id_memo){
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
               
                foreach($data_id_mo_return as $row_id_mo_return){
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
                // mencari delivery anakan
                foreach($data_id_mo_delivery as $row_id_mo_delivery){
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

                foreach($data_id_mo as $row_id_mo){
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
        
            // foreach($data_go_chart as $row_dg){
            //     info($row_dg);
            // }
            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
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
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Maaf, data tidak ditemukan.',
                'value'   => '',
            ];
        }

        return response()->json($response);
    }
}