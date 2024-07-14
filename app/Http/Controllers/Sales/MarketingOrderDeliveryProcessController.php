<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
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
use App\Models\ItemStock;
use Illuminate\Support\Str;
use App\Models\MarketingOrderDeliveryProcessTrack;
use App\Models\Menu;
use App\Models\Place;
use App\Models\Tax;
use App\Models\TaxSeries;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Process;
use Illuminate\Contracts\Process\ProcessResult;
use App\Models\UsedData;
class MarketingOrderDeliveryProcessController extends Controller
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
            'title'         => 'Surat Jalan',
            'content'       => 'admin.sales.order_delivery_process',
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
        $code = MarketingOrderDeliveryProcess::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'customer_id',
            'company_id',
            'account_id',
            'marketing_order_no',
            'marketing_order_delivery_no',
            'post_date',
            'driver_name',
            'driver_no',
            'vehicle_name',
            'vehicle_no',
            'note_internal',
            'note_external'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDeliveryProcess::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderDeliveryProcess::where(function($query) use ($search, $request) {
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
                            ->orWhereHas('marketingOrderDelivery',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
                                        $query->whereHas('item',function($query) use ($search, $request){
                                            $query->where('code','like',"%$search%")
                                                ->orWhere('name','like',"%$search%");
                                            });
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_delivery_id){
                    $query->whereIn('marketing_order_delivery_id',$request->marketing_order_delivery_id);
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

        $total_filtered = MarketingOrderDeliveryProcess::where(function($query) use ($search, $request) {
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
                            ->orWhereHas('marketingOrderDelivery',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
                                        $query->whereHas('item',function($query) use ($search, $request){
                                            $query->where('code','like',"%$search%")
                                                ->orWhere('name','like',"%$search%");
                                            });
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_delivery_id){
                    $query->whereIn('marketing_order_delivery_id',$request->marketing_order_delivery_id);
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
				if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->marketingOrderDelivery->marketingOrder->account->name,
                    $val->company->name,
                    $val->account->name,
                    $val->marketingOrderDelivery->marketingOrder->code,
                    $val->marketingOrderDelivery->code,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->driver_name,
                    $val->driver_hp,
                    $val->vehicle_name,
                    $val->vehicle_no,
                    $val->note_internal,
                    $val->note_external,
                    $val->return_date ? date('d/m/Y',strtotime($val->return_date)) : '-',
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->marketingOrderDelivery->marketingOrder->destination_address
                    .' - '.ucwords(strtolower($val->marketingOrderDelivery->marketingOrder->subdistrict->name
                    .' - '.$val->marketingOrderDelivery->marketingOrder->city->name
                    .' '.$val->marketingOrderDelivery->marketingOrder->province->name)),
                    $val->statusTracking(),
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text btn-small" data-popup="tooltip" title="Switch ke MOD lain" onclick="switchDocument(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">call_split</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light purple accent-2 white-text btn-small" data-popup="tooltip" title="Update Tracking" onclick="getTracking(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_shipping</i></button>
                        <a href="delivery_order/driver/'.CustomHelper::encrypt($val->code).'?d='.CustomHelper::encrypt($val->driver_name).'&p='.CustomHelper::encrypt($val->driver_hp).'" class="btn-floating btn-small mb-1 btn-flat waves-effect waves-light indigo accent-1 white-text" data-popup="tooltip" title="Driver Update Tracking" target="_blank"><i class="material-icons dp48">streetview</i></a>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
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

    public function getMarketingOrderDelivery(Request $request){
        $data = MarketingOrderDelivery::find($request->id);
        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'MOD No. '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if(!$data->marketingOrderDeliveryProcess()->exists()){
                $data['outlet'] = $data->marketingOrder->outlet->name;
                $data['sender'] = $data->account->name;
                $data['address'] = $data->marketingOrder->destination_address;
                $data['province'] = $data->marketingOrder->province->name;
                $data['city'] = $data->marketingOrder->city->name;
                $data['district'] = $data->marketingOrder->district->name;
                $data['subdistrict'] = $data->marketingOrder->subdistrict->name;

                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Surat Jalan');

                $details = [];
                $drivers = [];

                foreach($data->marketingOrderDeliveryDetail as $row){
                    $stocks = [];
                    foreach($row->marketingOrderDeliveryStock as $rowdetail){
                        $stocks[] = [
                            'stock_name'    => $rowdetail->itemStock->place->code.' - '.$rowdetail->itemStock->warehouse->name.' - '.($rowdetail->itemStock->area()->exists() ? $rowdetail->itemStock->area->name : '').' - '.($rowdetail->itemStock->itemShading()->exists() ? $rowdetail->itemStock->itemShading->code : ''),
                            'qty'           => CustomHelper::formatConditionalQty($rowdetail->qty),
                        ];
                    }
                    $details[] = [
                        'item_name'     => $row->item->code.' - '.$row->item->name,
                        'qty'           => CustomHelper::formatConditionalQty($row->qty),
                        'unit'          => $row->marketingOrderDetail->itemUnit->unit->code,
                        'note'          => $row->note,
                        'stocks'        => $stocks,
                    ];
                }

                foreach($data->account->userDriver as $row){
                    $drivers[] = [
                        'id'    => $row->id,
                        'name'  => $row->name,
                        'hp'    => $row->hp,
                    ];
                }

                $data['details'] = $details;
                $data['drivers'] = $drivers;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada MOD No. '.$data->code.' sudah dikirimkan. Data tidak bisa ditambahkan.';
            }
        }

        return response()->json($data);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('marketing_order_deliveries',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			                => $request->temp ? ['required', Rule::unique('marketing_order_delivery_processes', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_delivery_processes,code',
            'code_place_id'                 => 'required', */
            'company_id'			        => 'required',
            'marketing_order_delivery_id'	=> 'required',
            'post_date'		                => 'required',
            'driver_name'		            => 'required',
            'driver_hp'                     => 'required',
            'vehicle_name'                  => 'required',
            'vehicle_no'                    => 'required',
        ], [
            'code.required' 	                    => 'Kode tidak boleh kosong.',
            /* 'code.string'                           => 'Kode harus dalam bentuk string.',
            'code.min'                              => 'Kode harus minimal 18 karakter.',
            'code.unique'                           => 'Kode telah dipakai.', */
            'code_place_id.required'                => 'No plant dokumen tidak boleh kosong.',
            'marketing_order_delivery_id.required' 	=> 'MOD tidak boleh kosong.',
            'company_id.required' 			        => 'Perusahaan tidak boleh kosong.',
            'post_date.required' 			        => 'Tanggal posting tidak boleh kosong.',
            'driver_name.required' 			        => 'Nama supir tidak boleh kosong.',
            'driver_hp.required'                    => 'No HP/WA supir tidak boleh kosong.',
            'vehicle_name.required'                 => 'Nama/Tipe kendaraan tidak boleh kosong.',
            'vehicle_no.required'                   => 'Nopol kendaraan tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $mod = MarketingOrderDelivery::find($request->marketing_order_delivery_id);

            if($request->user_driver_id){
                $user_driver = $request->user_driver_id;
            }else{
                $user_driver = UserDriver::create([
                    'user_id'   => $mod->account_id,
                    'name'      => $request->driver_name,
                    'hp'        => $request->driver_hp,
                ])->id;
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->temp))->first();

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

                    if($approved && !$revised && !$request->tempSwitch){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Surat jalan telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if($request->tempSwitch){
                        $error = [];
                        if($mod->account_id !== $query->marketingOrderDelivery->account_id){
                            $error[] = 'Ekspeditor MOD baru dan ekspeditor MOD lama tidak sama.';
                        }
                        foreach($mod->marketingOrderDeliveryDetail as $key => $detail){
                            $cekquery = null;
                            $cekquery = $query->marketingOrderDelivery->marketingOrderDeliveryDetail()->where('item_id',$detail->item_id)->get();
                            if($cekquery->count() > 0){
                                foreach($cekquery as $key => $rowcek){
                                    if($detail->qty !== $rowcek->qty){
                                        $error[] = 'Produk '.$detail->item->name.' memiliki selisih qty. Jumlah qty harus sama untuk MOD lama dan baru.';
                                    }   
                                }
                            }else{
                                $error[] = 'Produk '.$detail->item->name.' tidak ditemukan pada MOD lama.';
                            }
                        }
                        if(count($error) > 0){
                            return response()->json([
                                'status'  => 500,
                                'message' => implode(', ',$error)
                            ]);
                        }else{
                            $query->marketingOrderDelivery->marketingOrder->update([
                                'status'        => '5',
                                'void_id'       => session('bo_id'),
                                'void_note'     => 'Ditutup oleh switch dari MOD nomor '.$query->marketingOrderDelivery->code.'.',
                                'void_date'     => date('Y-m-d H:i:s'),
                            ]);

                            $query->marketingOrderDelivery->update([
                                'status'        => '5',
                                'void_id'       => session('bo_id'),
                                'void_note'     => 'Ditutup oleh switch dari MOD nomor '.$query->marketingOrderDelivery->code.'.',
                                'void_date'     => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6']) || $request->tempSwitch){

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $mod->account_id;
                        $query->company_id = $request->company_id;
                        $query->marketing_order_delivery_id = $request->marketing_order_delivery_id;
                        $query->post_date = $request->post_date;
                        $query->user_driver_id = $user_driver;
                        $query->driver_name = $request->driver_name;
                        $query->driver_hp = $request->driver_hp;
                        $query->vehicle_name = $request->vehicle_name;
                        $query->vehicle_no = $request->vehicle_no;
                        $query->note_internal = $request->note_internal;
                        $query->note_external = $request->note_external;
                        $query->status = $request->tempSwitch ? $query->status : '1';
                        $query->total = $mod->getTotal();
                        $query->tax = $mod->getTax();
                        $query->rounding = $mod->getRounding();
                        $query->grandtotal = $mod->getGrandtotal();

                        $query->save();

                        if(!$request->tempSwitch){
                            foreach($query->marketingOrderDeliveryProcessTrack as $row){
                                $row->delete();
                            }
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status surat jalan detail sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=MarketingOrderDeliveryProcess::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = MarketingOrderDeliveryProcess::create([
                        'code'			                => $newCode,
                        'user_id'		                => session('bo_id'),
                        'account_id'                    => $mod->account_id,
                        'company_id'                    => $request->company_id,
                        'marketing_order_delivery_id'   => $request->marketing_order_delivery_id,
                        'post_date'                     => $request->post_date,
                        'user_driver_id'                => $user_driver,
                        'driver_name'                   => $request->driver_name,
                        'driver_hp'                     => $request->driver_hp,
                        'vehicle_name'                  => $request->vehicle_name,
                        'vehicle_no'                    => $request->vehicle_no,
                        'note_internal'                 => $request->note_internal,
                        'note_external'                 => $request->note_external,
                        'status'                        => '1',
                        'total'                         => $mod->getTotal(),
                        'tax'                           => $mod->getTax(),
                        'rounding'                      => $mod->getRounding(),
                        'grandtotal'                    => $mod->getGrandtotal(),
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                if(!$request->tempSwitch){
                    MarketingOrderDeliveryProcessTrack::create([
                        'user_id'                               => session('bo_id'),
                        'marketing_order_delivery_process_id'   => $query->id,
                        'status'                                => '1',
                    ]);
                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note_internal.' - '.$query->note_external);
                }
                
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Surat Jalan No. '.$query->code,$query->note_internal.' - '.$query->note_external,session('bo_id'));

                if($request->tempSwitch){
                    $mod = MarketingOrderDelivery::find(intval($request->marketing_order_delivery_id));
                    if($query->journal()->exists()){
                        foreach($query->journal as $row){
                            foreach($row->journalDetail as $rowdetail){ 
                                $rowdetail->update([
                                    'account_id'    => $mod->marketingOrder->account_id,
                                ]);
                            }
                        }
                    }
                }

                activity()
                    ->performedOn(new MarketingOrderDeliveryProcess())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit surat jalan.');

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
        $data   = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table class="bordered" style="min-width:100%;">
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
        foreach($data->marketingOrderDelivery->marketingOrderDeliveryDetail as $key => $row){
            $totalqty+=$row->qty;
            $string .= '<tr>
                <td class="center-align" rowspan="2">'.($key + 1).'</td>
                <td class="center-align">'.$row->marketingOrderDetail->marketingOrder->code.'</td>
                <td class="center-align">'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->marketingOrderDetail->itemUnit->unit->code.'</td>
                <td class="">'.$row->note.'</td>
            </tr>';

            $string .= '
                <tr>
                    <td class="center-align">Ambil Item dari : </td>
                    <td colspan="5"><table class="bordered" id="table-detail-source">
                    <thead>
                        <tr>
                            <th class="center-align">Asal Plant - Gudang - Area - Shading</th>
                            <th class="center-align">Qty Kirim</th>
                        </tr>
                    </thead>
                    <tbody>';
                        
            foreach($row->marketingOrderDeliveryStock as $rowdetail){
                $string .= '<tr>
                    <td>'.$rowdetail->itemStock->place->code.' - '.$rowdetail->itemStock->warehouse->name.' - '.($rowdetail->itemStock->area()->exists() ? $rowdetail->itemStock->area->name : '').' - '.($rowdetail->itemStock->itemShading()->exists() ? $rowdetail->itemStock->itemShading->code : '').'</td>
                    <td class="right-align">'.CustomHelper::formatConditionalQty($rowdetail->qty).'</td>

                </tr>';
            }
                    
            $string .= '</tbody>
                    </table>
                    </td>
                </tr>
            ';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
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

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $mod = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Print Marketing Order Delivery Process',
                'data'      => $mod
            ];

            return view('admin.approval.marketing_order_delivery_process', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Print Surat Jalan','a4','portrait','admin.print.sales.order_delivery_process_individual');
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
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
                $pr = MarketingOrderDeliveryProcess::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Print Surat Jalan','a4','portrait','admin.print.sales.order_delivery_process_individual');
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
                        $query = MarketingOrderDeliveryProcess::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Surat Jalan','a4','portrait','admin.print.sales.order_delivery_process_individual');
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
                        $query = MarketingOrderDeliveryProcess::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print Surat Jalan','a4','portrait','admin.print.sales.order_delivery_process_individual');
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

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $main = [];
            foreach($query->journal as $rowmain){

                $string='<div class="row">
                            <div class="col" id="user_jurnal">
                            '.$query->user->name.'
                            </div>
                            <div class="col" id="post_date_jurnal">
                            '.date('d/m/Y',strtotime($query->post_date)).'
                            </div>
                            <div class="col" id="note_jurnal">
                            '.$query->note.'
                            </div>
                            <div class="col" id="ref_jurnal">
                            '.$query->code.'
                            </div>
                            <div class="col" id="company_jurnal">
                            '.$query->company->name.'
                            </div>
                        </div>
                        <div class="row mt-2">
                            <table class="bordered Highlight striped" style="zoom:0.7;">
                                <thead>
                                        <tr>
                                            <th class="center-align" rowspan="2">No</th>
                                            <th class="center-align" rowspan="2">Coa</th>
                                            <th class="center-align" rowspan="2">Partner Bisnis</th>
                                            <th class="center-align" rowspan="2">Plant</th>
                                            <th class="center-align" rowspan="2">Line</th>
                                            <th class="center-align" rowspan="2">Mesin</th>
                                            <th class="center-align" rowspan="2">Divisi</th>
                                            <th class="center-align" rowspan="2">Gudang</th>
                                            <th class="center-align" rowspan="2">Proyek</th>
                                            <th class="center-align" rowspan="2">Ket.1</th>
                                            <th class="center-align" rowspan="2">Ket.2</th>
                                            <th class="center-align" colspan="2">Mata Uang Asli</th>
                                            <th class="center-align" colspan="2">Mata Uang Konversi</th>
                                        </tr>
                                        <tr>
                                            <th class="center-align">Debit</th>
                                            <th class="center-align">Kredit</th>
                                            <th class="center-align">Debit</th>
                                            <th class="center-align">Kredit</th>
                                        </tr>
                                    
                                </thead>
                                <tbody>';

                foreach($rowmain->journalDetail()->where(function($query){
                    $query->whereHas('coa',function($query){
                        $query->orderBy('code');
                    })
                    ->orderBy('type');
                })->get() as $key => $row){
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

                $string .= '</tbody>
                        </table>
                    </div>';

                $main[] = $string;
            }

            $response = [
                'status'    => 200,
                'message'   => 'Data berhasil dimuat.',
                'data'      => $main
            ]; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

    public function show(Request $request){
        $po = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();
        if($request->type){
            if(in_array($po->statusTrackingRaw(),['1','3','4','5'])){
                return response()->json([
                    'responseStatus'    => 500,
                    'message'           => 'Fitur switch hanya untuk status *Barang telah dikirimkan*.'
                ]);
            }
        }
        $po['responseStatus'] = 200;
        $po['code_place_id'] = substr($po->code,7,2);
        $po['marketing_order_delivery_code'] = $po->marketingOrderDelivery->code;
        $po['outlet'] = $po->marketingOrderDelivery->marketingOrder->outlet->name;
        $po['sender'] = $po->marketingOrderDelivery->account->name;
        $po['address'] = $po->marketingOrderDelivery->marketingOrder->destination_address;
        $po['province'] = $po->marketingOrderDelivery->marketingOrder->province->name;
        $po['city'] = $po->marketingOrderDelivery->marketingOrder->city->name;
        $po['district'] = $po->marketingOrderDelivery->marketingOrder->district->name;
        $po['subdistrict'] = $po->marketingOrderDelivery->marketingOrder->subdistrict->name;

        $arr = [];
        $drivers = [];

        foreach($po->account->userDriver as $row){
            $drivers[] = [
                'id'    => $row->id,
                'name'  => $row->name,
                'hp'    => $row->hp,
            ];
        }

        $po['drivers'] = $drivers;
        
        foreach($po->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            $stocks = [];
            foreach($row->marketingOrderDeliveryStock as $rowdetail){
                $stocks[] = [
                    'stock_name'    => $rowdetail->itemStock->place->code.' - '.$rowdetail->itemStock->warehouse->name.' - '.($rowdetail->itemStock->area()->exists() ? $rowdetail->itemStock->area->name : '').' - '.($rowdetail->itemStock->itemShading()->exists() ? $rowdetail->itemStock->itemShading->code : ''),
                    'qty'           => CustomHelper::formatConditionalQty($rowdetail->qty),
                ];
            }
            $arr[] = [
                'item_name'     => $row->item->code.' - '.$row->item->name,
                'place_name'    => $row->place->code,
                'qty'           => CustomHelper::formatConditionalQty($row->qty),
                'unit'          => $row->marketingOrderDetail->itemUnit->unit->code,
                'note'          => $row->note,
                'stocks'        => $stocks,
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function getTracking(Request $request)
    {
        $data   = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->code))->first();
        
        $string = '<h6>Status Tracking : Surat Jalan No. '.$data->code.'</h6><div class="row pt-1 pb-1 lighten-4">';
        
        $arrTracking = [];

        foreach($data->marketingOrderDeliveryProcessTrack as $key => $row){
            $arrTracking[] = [
                'status'    => $row->status,
                'date'      => date('d/m/Y H:i:s',strtotime($row->updated_at)),
            ];
        }

        $response =[
            'status'    => 200,
            'tracking'  => $arrTracking,
        ];
		
        return response()->json($response);
    }

    public function updateTracking(Request $request){

        $validation = Validator::make($request->all(), [
            'tempTracking'                  => 'required',
            'status_tracking'			    => 'required',
        ], [
            'tempTracking.required' 	    => 'Tracking tidak boleh kosong.',
            'status_tracking.required'      => 'Status tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            if($request->status_tracking == '5'){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Status kembali hanya bisa diisi melalui form disamping kanan / Surat Jalan Kembali.'
                ]);
            }
            $data   = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->tempTracking))->first();
            $cek = MarketingOrderDeliveryProcessTrack::where('marketing_order_delivery_process_id',$data->id)->where('status',$request->status_tracking)->first();
            if($cek){
                $cek->update([
                    'user_id' => session('bo_id'),
                ]);
            }else{
                $cek = MarketingOrderDeliveryProcessTrack::create([
                    'user_id'                               => session('bo_id'),
                    'marketing_order_delivery_process_id'   => $data->id,
                    'status'                                => $request->status_tracking,
                ]);
            }

            if($request->status_tracking == '2'){
                $data->createJournalSentDocument();
            }

            CustomHelper::sendNotification($data->getTable(),$data->id,'Status Pengiriman Surat Jalan No. '.$data->code.' telah diupdate','Status Pengiriman Surat Jalan No. '.$data->code.' telah diupdate.',$data->user_id);

            $response = [
                'status'    => 200,
                'param'     => $cek->status,
                'date'      => date('d/m/Y H:i:s',strtotime($cek->updated_at)),
                'message'   => 'Tracking succesfully updated.'
            ];
        }

        return response()->json($response); 
    }

    public function updateReturn(Request $request){

        $validation = Validator::make($request->all(), [
            'tempTracking'                  => 'required',
            'post_date_return'			    => 'required',
            'document'                      => 'required',
        ], [
            'tempTracking.required' 	    => 'Tracking tidak boleh kosong.',
            'post_date_return.required'     => 'Tgl. Kembali tidak boleh kosong.',
            'document.required'             => 'Dokumen bukti tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $data   = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->tempTracking))->first();

            $cek = $data->marketingOrderDeliveryProcessTrack()->where('status','2')->count();

            if($cek == 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf Surat Jalan nomor '.$data->code.' status tracking belum dirubah ke BARANG TELAH DIKIRIMKAN.',
                ]);
            }

            if($data){
                if($request->has('document')) {
                    if($data->document){
                        if(Storage::exists($data->document)){
                            Storage::delete($data->document);
                        }
                    }
                    $document = $request->file('document')->store('public/delivery_orders');
                } else {
                    $document = $data->document;
                }

                $data->update([
                    'return_date'   => $request->post_date_return,
                    'document'      => $document,
                ]);

                $cek = MarketingOrderDeliveryProcessTrack::where('marketing_order_delivery_process_id',$data->id)->where('status','5')->first();
                
                if($cek){
                    $cek->update([
                        'user_id' => session('bo_id'),
                    ]);
                }else{
                    $cek = MarketingOrderDeliveryProcessTrack::create([
                        'user_id'                               => session('bo_id'),
                        'marketing_order_delivery_process_id'   => $data->id,
                        'status'                                => '5',
                    ]);
                }

                $datakuy = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->tempTracking))->first();

                if($datakuy){
                    $datakuy->createJournalReceiveDocument();
                    $datakuy->createInvoice();
                }

                CustomHelper::sendNotification($datakuy->getTable(),$datakuy->id,'Dokumen Surat Jalan No. '.$datakuy->code.' telah kembali','Dokumen Surat Jalan No. '.$datakuy->code.' telah kembali.',session('bo_id'));

                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                    'param'     => $cek->status,
                    'date'      => date('d/m/Y H:i:s',strtotime($cek->updated_at)),
                ];
            }else{
                $response = [
                    'status'    => 500,
                    'message'   => 'Data tidak ditemukan.'
                ];
            }
        }

        return response()->json($response); 
    }

    public function voidStatus(Request $request){
        $query = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                }
                
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new MarketingOrderDeliveryProcess())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the  data');
    
                CustomHelper::sendNotification('marketing_order_delivery_processes',$query->id,'Surat Jalan No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('marketing_order_delivery_processes',$query->id);

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
        $query = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->marketingOrderDeliveryProcessTrack()->delete();

            CustomHelper::removeApproval('marketing_order_delivery_processes',$query->id);

            activity()
                ->performedOn(new MarketingOrderDeliveryProcess())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the marketing order delivery process data');

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

    public function driverIndex(Request $request, $id){
        $modp = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($id))->whereIn('status',['2','3'])->first();
        
        if($modp){
            $data = [
                'title'         => 'Driver Update Tracking',
                'content'       => 'admin.sales.driver_tracking',
                'data'          => $modp,
                'code'          => $id,
                'driver'        => $request->d,
                'phone'         => $request->p,
                'arrTracking'   => $modp->getArrStatusTracking(),
            ];

            return view('admin.layouts.no_header_sidebar', ['data' => $data]);
        }else{
            abort(404);
        }
    }

    public function driverUpdate(Request $request){
        $modp = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->code))->whereIn('status',['2','3'])->first();

        if($modp){
            $cek = MarketingOrderDeliveryProcessTrack::where('marketing_order_delivery_process_id',$modp->id)->where('status',$request->status)->first();

            if($cek){
                $cek->update([
                    'user_id' => session('bo_id') ? session('bo_id') : NULL,
                ]);
            }else{
                MarketingOrderDeliveryProcessTrack::create([
                    'user_id'                               => session('bo_id') ? session('bo_id') : NULL,
                    'marketing_order_delivery_process_id'   => $modp->id,
                    'status'                                => $request->status,
                ]);
            }

            return response()->json([
                'status'    => 200,
                'message'   => 'Status tracking telah diupdate, halaman akan direfresh.'
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data tracking surat jalan tidak ditemukan.'
            ]);
        }        
    }

    public function viewStructureTree(Request $request){
        $query = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            $data_marketing_order_process = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_marketing_order_process;

            $data_mo_delivery = [
                "name"=>$query->marketingOrderDelivery->code,
                "key" => $query->marketingOrderDelivery->code,
                'properties'=> [
                    ['name'=> "Tanggal :".$query->marketingOrderDelivery->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->marketingOrderDelivery->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->marketingOrderDelivery->code),           
            ];

            $data_go_chart[]= $data_mo_delivery;
            $data_id_mo_delivery[]=$query->id;
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
        $query_done = MarketingOrderDeliveryProcess::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new MarketingOrderDeliveryProcess())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Order Delivery Process data');
    
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