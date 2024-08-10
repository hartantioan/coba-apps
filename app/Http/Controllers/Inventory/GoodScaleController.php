<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportGoodScale;
use App\Exports\ExportGoodScaleTransactionPage;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodScale;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Warehouse;
use App\Models\Weight;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Menu;
use App\Models\MenuUser;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use App\Models\UsedData;
class GoodScaleController extends Controller
{
    protected $dataplaces, $datawarehouses, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'     => 'Timbangan Truk',
            'content'   => 'admin.inventory.good_scale',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = GoodScale::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'delivery_no',
            'vehicle_no',
            'driver',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodScale::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = GoodScale::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('delivery_no', 'like', "%$search%")
                            ->orWhere('vehicle_no', 'like', "%$search%")
                            ->orWhere('driver', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->status_qc){
                    $query->where('status_qc', $request->status_qc);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodScale::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('delivery_no', 'like', "%$search%")
                            ->orWhere('vehicle_no', 'like', "%$search%")
                            ->orWhere('driver', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->status_qc){
                    $query->where('status_qc', $request->status_qc);
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
                $updateBtn = '';
                if($val->alreadyChecked()){
                    $updateBtn = '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small disable" data-popup="tooltip" title="Update Timbangan" onclick="update(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">add_box</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->referencePO(),
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->delivery_no,
                    $val->vehicle_no,
                    $val->driver,
                    $val->note,
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->image_in ? '<a href="'.$val->imageIn().'" target="_blank"><i class="material-icons">camera_front</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    date('d/m/Y H:i:s',strtotime($val->time_scale_in)),
                    $val->qualityCheck(),
                    $val->image_qc ? '<a href="'.$val->imageQc().'" target="_blank"><i class="material-icons">camera_rear</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->time_scale_qc ? date('d/m/Y H:i:s',strtotime($val->time_scale_qc)) : '',
                    $val->image_out ? '<a href="'.$val->imageOut().'" target="_blank"><i class="material-icons">camera_rear</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->time_scale_out ? date('d/m/Y H:i:s',strtotime($val->time_scale_out)) : '',
                    $val->status(),
                    $val->statusQc(),
                    $val->note_qc,
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
                    $val->place->code,
                    $val->warehouse->name,
                    $val->item->code.' - '.$val->item->name,
                    CustomHelper::formatConditionalQty($val->purchaseOrderDetail->qty),
                    CustomHelper::formatConditionalQty($val->qty_in),
                    CustomHelper::formatConditionalQty($val->qty_out),
                    CustomHelper::formatConditionalQty($val->qty_balance),
                    CustomHelper::formatConditionalQty($val->qty_qc),
                    CustomHelper::formatConditionalQty($val->qty_final),
                    CustomHelper::formatConditionalQty($val->water_content),
                    CustomHelper::formatConditionalQty($val->viscosity),
                    CustomHelper::formatConditionalQty($val->residue),
                    $val->itemUnit->unit->code,
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        '.$updateBtn.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light purple accent-2 white-text btn-small" data-popup="tooltip" title="Update Informasi Timbangan" onclick="updateInformation(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">contact_phone</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					',
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

    public function getPurchaseOrderAi(Request $request){
        $result = [];

        $datapo = PurchaseOrder::whereIn('status',['2','3'])
                    ->where('account_id',$request->account_id)
                    ->whereHas('purchaseOrderDetail',function($query)use($request){
                        $query->where('place_id',$request->place_id);
                    })
                    ->whereDoesntHave('used')
                    ->where('inventory_type','1')
                    ->get();

        foreach($datapo as $row){
            if($row->hasBalance()){
                $result[] = [
                    'id'                => $row->id,
                    'code'              => $row->code,
                    'post_date_raw'     => $row->post_date,
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'receiver_name'     => $row->receiver_name,
                    'receiver_address'  => $row->receiver_address,
                    'receiver_phone'    => $row->receiver_phone,
                    'description'       => '',
                    'percent_balance'   => $row->percentBalance(),
                ];
            }
        }

        $collection = collect($result)->sortBy([
            ['percent_balance','desc'],
            ['code','asc'],
        ])->values()->all();

        return response()->json($collection);
    }

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->whereIn('status',['2','3'])->first();
        $data['account_name'] = $data->supplier->employee_no.' - '.$data->supplier->name;

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Order '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($data->hasBalance()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Timbangan Truk');
                $details = [];
                foreach($data->purchaseOrderDetail as $row){
                    if($row->getBalanceReceipt() > 0){
                        $details[] = [
                            'purchase_order_detail_id'  => $row->id,
                            'item_id'                   => $row->item_id,
                            'item_name'                 => $row->item->code.' - '.$row->item->name,
                            'qty'                       => CustomHelper::formatConditionalQty($row->getBalanceReceipt()),
                            'unit'                      => $row->itemUnit->unit->code,
                            'place_id'                  => $row->place_id,
                            'place_name'                => $row->place->code,
                            'warehouse_id'              => $row->warehouse_id,
                            'warehouse_name'            => $row->warehouse->name,
                            'note'                      => $row->note,
                            'note2'                     => $row->note2,
                        ];
                    }
                }

                $data['details'] = $details;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada purchase order '.$data->code.' telah diterima di gudang.';
            }
        }

        return response()->json($data);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            'company_id'                => 'required',
            'vehicle_no'                => 'required',
            'driver'                    => 'required',
            'place_id'                  => 'required',
			'post_date'		            => 'required',
            'item_id'                   => 'required',
            'warehouse_id'              => 'required',
            'purchase_order_detail_id'  => 'required',
            'qty_po'                    => 'required',
            'qty_in'                    => 'required',
            'qty_out'                   => 'required',
            'item_unit_id'              => 'required',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'vehicle_no.required'               => 'Nomor kendaraan tidak boleh kosong.',
            'driver.required'                   => 'Nama supir tidak boleh kosong.',
            'place_id.required'                 => 'Plant tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'item_id.required'                  => 'Item tidak boleh kosong.',
            'warehouse_id.required'             => 'Gudang tidak boleh kosong.',
            'purchase_order_detail_id.required' => 'PO tidak boleh kosong.',
            'qty_po.required'                   => 'Qty PO tidak boleh kosong.',
            'qty_in.required'                   => 'Qty timbang masuk tidak boleh kosong.',
            'qty_out.required'                  => 'Qty timbang keluar tidak boleh kosong.',
            'item_unit_id.required'             => 'Satuan item tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
           

                $imageName = '';
                $newFile = '';
                if($request->image_in){
                    // $image = $request->image_in;  // your base64 encoded
                    // $image = str_replace('data:image/png;base64,', '', $image);
                    // $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(35).'.png';
                    $path=storage_path('app/public/good_scales/'.$imageName);
                    $newFile = CustomHelper::compress($request->image_in,$path,30);
                    $basePath = storage_path('app');
                    $desiredPath = explode($basePath.'/', $newFile)[1];

                }

                $itemUnit = ItemUnit::find($request->item_unit_id);
                $pod = PurchaseOrderDetail::find($request->purchase_order_detail_id);
                $item = Item::find($request->item_id);

                if($item->is_quality_check && !$request->is_quality_check){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Item terpilih masuk ke dalam kategori wajib dicek QC, silahkan centang PENGECEKAN QC.'
                    ]);
                }
                if($request->temp){
                
                    $query = GoodScale::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Goods Scale (Timbangan Truk) PO telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){
                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $extension = $request->document->getClientOriginalExtension();
                            if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                                $document_name = Str::random(35).'.png';
                                $path_document=storage_path('app/public/good_scales/'.$document_name);
                                $newFile_document = CustomHelper::compress($request->document,$path_document,30);
                                $basePath = storage_path('app');
                            
                                $document = explode($basePath.'/', $newFile_document)[1];
                            } else {
                                $document = $request->file('document') ? $request->file('document')->store('public/good_scales') : NULL;
                            }
                        } else {
                            $document = $query->document;
                        }

                        if(Storage::exists($query->image_in)){
                            Storage::delete($query->image_in);
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id ?? $pod->purchaseOrder->account_id;
                        $query->company_id = $request->company_id;
                        $query->place_id = $request->place_id;
                        $query->warehouse_id = $request->warehouse_id;
                        $query->post_date = $request->post_date;
                        $query->delivery_no = $request->delivery_no;
                        $query->vehicle_no = $request->vehicle_no;
                        $query->driver = $request->driver;
                        $query->document = $document;
                        $query->image_in = $desiredPath ? $desiredPath : NULL;
                        $query->time_scale_in = date('Y-m-d H:i:s');
                        $query->note = $request->note;
                        $query->purchase_order_detail_id = $request->purchase_order_detail_id;
                        $query->item_id = $request->item_id;
                        $query->qty_in = str_replace(',','.',str_replace('.','',$request->qty_in));
                        $query->qty_out = str_replace(',','.',str_replace('.','',$request->qty_out));
                        $query->qty_balance = 0;
                        $query->qty_qc = 0;
                        $query->qty_final = 0;
                        $query->item_unit_id = $request->item_unit_id;
                        $query->qty_conversion = $itemUnit->conversion;
                        $query->is_quality_check = $request->is_quality_check ?? NULL;
                        $query->water_content = 0;
                        $query->status = '1';

                        $query->save();
                        
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Timbangan Truk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=GoodScale::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    if($request->has('document')) {
                        $extension = $request->document->getClientOriginalExtension();
                        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                            $document_name = Str::random(35).'.png';
                            $path_document=storage_path('app/public/good_scales/'.$document_name);
                            $newFile_document = CustomHelper::compress($request->document,$path_document,30);
                            $basePath = storage_path('app');
                        
                            $document = explode($basePath.'/', $newFile_document)[1];
                        } else {
                            $document = $request->file('document') ? $request->file('document')->store('public/good_scales') : NULL;
                        }
                        
                    }else{
                        $document = null;
                    }
                    $query = GoodScale::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id ?? $pod->purchaseOrder->account_id,
                        'company_id'                => $request->company_id,
                        'place_id'                  => $request->place_id,
                        'warehouse_id'              => $request->warehouse_id,
                        'post_date'                 => $request->post_date,
                        'delivery_no'               => $request->delivery_no,
                        'vehicle_no'                => $request->vehicle_no,
                        'driver'                    => $request->driver,
                        'document'                  => $document,
                        'image_in'                  => $desiredPath ? $desiredPath : NULL,
                        'time_scale_in'             => date('Y-m-d H:i:s'),
                        'note'                      => $request->note,
                        'purchase_order_detail_id'  => $request->purchase_order_detail_id,
                        'item_id'                   => $request->item_id,
                        'qty_in'                    => str_replace(',','.',str_replace('.','',$request->qty_in)),
                        'qty_out'                   => str_replace(',','.',str_replace('.','',$request->qty_out)),
                        'qty_balance'               => 0,
                        'qty_qc'                    => 0,
                        'qty_final'                 => 0,
                        'item_unit_id'              => $request->item_unit_id,
                        'qty_conversion'            => $itemUnit->conversion,
                        'is_quality_check'          => $request->is_quality_check ?? NULL,
                        'water_content'             => 0,
                        'status'                    => '1',
                    ]);
                        
                }
                
                if($query) {

                    CustomHelper::sendApproval('good_scales',$query->id,$query->note);
                    CustomHelper::sendNotification('good_scales',$query->id,'Pengajuan Timbangan Truk No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new GoodScale())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit timbangan truk.');

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
                DB::commit();
            
		}
		
		return response()->json($response);
    }

    public function rowDetail(Request $request)
    {
        $data   = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }

        $string = '<div class="row pt-1 pb-1 lighten-4">
                    <div class="col s12">'.$data->code.$x.'</div>';
                    
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

    public function getWeight(Request $request)
    {
        $weight = Weight::where('place_id',$request->place_id)->first();

        if($weight){
            $nominal = number_format($weight->nominal,3,',','.');
        }else{
            $nominal = '0';
        }

        return response()->json($nominal);
    }

    public function update(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
        $data['image_in'] = $data->imageIn();
        $data['item_name'] = $data->item->code.' - '.$data->item->name;
        $data['purchase_code'] = $data->purchaseOrderDetail->purchaseOrder->code;
        $data['qty_po'] = CustomHelper::formatConditionalQty($data->purchaseOrderDetail->qty);
        $data['qty_in'] = CustomHelper::formatConditionalQty($data->qty_in);
        $data['place_code'] = $data->place->code;
        $data['warehouse_name'] = $data->warehouse->name;
        $data['buy_units'] = $data->item->arrBuyUnits();
        $data['is_hide'] = $data->item->is_hide_supplier ?? '';
        $data['unit'] = $data->itemUnit->unit->code;

        return response()->json($data);
    }

    public function updateInformation(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        if($data->goodReceiptDetail()->exists()){
            return response()->json([
                'status'  => 500,
                'message' => 'Data Timbangan telah ditarik menjadi GRPO, anda tidak bisa merubah data.'
            ]);
        }else{
            $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
            $data['purchase_code'] = $data->purchaseOrderDetail->purchaseOrder->code;
            $data['place_code'] = $data->place->code;
            $data['warehouse_name'] = $data->warehouse->name;
            $response = [
                'status'    => 200,  
                'data'      => $data
            ];
        }

        return response()->json($response);
    }

    public function saveUpdate(Request $request){

        $overtolerance = false;

        $arrweight = explode('.',str_replace(',','.',str_replace('.','',$request->qtyOutUpdate)));

        if(strlen($arrweight[0]) <= 3){
            return response()->json([
                'status'  => 500,
                'message' => 'Berat timbang dibawah 1000, silahkan cek berat atau kalibrasi ulang koneksi dengan timbangan.'
            ]);
        }

        $gs = GoodScale::find($request->tempGoodScale);

        if($gs){
            $balanceweight = $gs->qty_in - str_replace(',','.',str_replace('.','',$request->qtyOutUpdate));
            $tolerance_gr = $gs->item->tolerance_gr ? $gs->item->tolerance_gr : 0;
            $balancegrpo = $gs->purchaseOrderDetail->qtyGR();
            $balance = ($balanceweight + $balancegrpo) - $gs->purchaseOrderDetail->qty;
            $percent_balance = round(($balance / $gs->purchaseOrderDetail->qty) * 100,2);
            if($percent_balance > $tolerance_gr){
                $overtolerance = true;
            }

            if($overtolerance){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Prosentase qty diterima melebihi prosentase toleransi yang telah diatur.'
                ]);
            }

            $qty_qc = 0;
            $qty_final = $balanceweight;

            $qty_qc = round((($gs->water_content / 100) * $balanceweight),3);

            $qty_final -= $qty_qc;
    
            $adapo = false;
            $idgs = 0;
    
            $imageName = '';
            $newFile = '';
            if($request->image_out){
                $image = $request->image_out;  // your base64 encoded
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(35).'.'.'png';
                $newFile = 'public/good_scales/'.$imageName;
                Storage::put($newFile,base64_decode($image));
            }
            $gs->qty_out = str_replace(',','.',str_replace('.','',$request->qtyOutUpdate));
            $gs->qty_balance = $balanceweight;
            $gs->qty_qc = $qty_qc;
            $gs->qty_final = $qty_final;
            $gs->time_scale_out = date('Y-m-d H:i:s');
            $gs->image_out = $newFile ? $newFile : NULL;
            $gs->note = $request->noteUpdate;
            $gs->save();
    
            /* if($adapo){
                if($idgs > 0){
                    GoodScale::find($idgs)->createGoodReceipt();
                }
            } */
    
            $response = [
                'status'    => 200,
                'message'   => 'Data successfully updated.',
            ];
        }

        return response()->json($response);
    }

    public function createUpdateInformation(Request $request){

        $gs = GoodScale::find($request->tempGoodScaleLast);

        if($gs){
            if($gs->goodReceiptDetail()->exists()){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Data Timbangan telah ditarik menjadi GRPO, anda tidak bisa merubah data.'
                ]);
            }

            $gs->delivery_no = $request->deliveryNoLast;
            $gs->vehicle_no = $request->vehicleNoLast;
            $gs->driver = $request->driverLast;
            $gs->note = $request->noteUpdateLast;
            $gs->save();

            activity()
                    ->performedOn(new GoodScale())
                    ->causedBy(session('bo_id'))
                    ->withProperties($gs)
                    ->log('Change information good scale data');
    
            $response = [
                'status'    => 200,
                'message'   => 'Data successfully updated.',
            ];
        }

        return response()->json($response);
    }

    public function show(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
        $data['code_place_id'] = substr($data->code,7,2);
        $data['item_name'] = $data->item->code.' - '.$data->item->name;
        $data['purchase_code'] = $data->purchaseOrderDetail->purchaseOrder->code.' - '.$data->purchaseOrderDetail->place->code.' - '.$data->purchaseOrderDetail->warehouse->name.' Qty. '.CustomHelper::formatConditionalQty($data->purchaseOrderDetail->getBalanceReceipt()).' '.$data->purchaseOrderDetail->itemUnit->unit->code;
        $data['qty_po'] = CustomHelper::formatConditionalQty($data->purchaseOrderDetail->qty);
        $data['qty_in'] = CustomHelper::formatConditionalQty($data->qty_in);
        $data['qty_out'] = CustomHelper::formatConditionalQty($data->qty_out);
        $data['list_warehouse'] = $data->item->warehouseList();
        $data['buy_units'] = $data->item->arrBuyUnits();
        $data['is_hide'] = $data->item->is_hide_supplier ?? '';

        return response()->json($data);
    }

    public function voidStatus(Request $request){
        $query = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }elseif($query->alreadyChecked()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada dokumen lain.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new GoodScale())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good scale data');
    
                CustomHelper::sendNotification('good_scales',$query->id,'Timbangan Truk No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_scales',$query->id);
                
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
        $query = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Goods Scale (Timbangan Truk) PO sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval('good_scales',$query->id);

            activity()
                ->performedOn(new GoodScale())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good scale data');

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

    public function approval(Request $request,$id){
        
        $gs = GoodScale::where('code',CustomHelper::decrypt($id))->first();
                
        if($gs){
            $data = [
                'title'     => 'Print Timbangan Masuk',
                'data'      => $gs
            ];

            return view('admin.approval.good_scale', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = GoodScale::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $pdf = PrintHelper::print($pr,'Good Scale','a5','landscape','admin.print.inventory.good_scale_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
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
            foreach($request->arr_id as $key =>$row){
                $pr = GoodScale::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Good Scale','a5','landscape','admin.print.inventory.good_scale_individual');
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
                        $query = GoodScale::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Scale','a5','landscape','admin.print.inventory.good_scale_individual');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = GoodScale::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Scale','a5','landscape','admin.print.inventory.good_scale_individual');
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportGoodScale($post_date,$end_date,$mode), 'good_scale_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $status_qc = $request->status_qc ? $request->status_qc : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportGoodScaleTransactionPage($search,$post_date,$end_date,$status,$modedata,$status_qc), 'good_scale'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new GoodScale())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Good Scale data');
    
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