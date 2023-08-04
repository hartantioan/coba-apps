<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportGoodScale;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodScale;
use App\Models\GoodScaleDetail;
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
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
        $data = [
            'title'     => 'Timbangan Truk',
            'content'   => 'admin.inventory.good_scale',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => 'TMBG-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = GoodScale::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
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
                            ->orWhereHas('goodScaleDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
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
                    $query->where('status', $request->status);
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
                            ->orWhereHas('goodScaleDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
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
                    $query->where('status', $request->status);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $updateBtn = '';
                if(!$val->alreadyHome()){
                    $updateBtn = '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small disable" data-popup="tooltip" title="Update Timbangan" onclick="update(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">add_box</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->referencePO(),
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d M Y',strtotime($val->post_date)),
                    $val->delivery_no,
                    $val->vehicle_no,
                    $val->driver,
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->image_in ? '<a href="'.$val->imageIn().'" target="_blank"><i class="material-icons">camera_front</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->image_out ? '<a href="'.$val->imageOut().'" target="_blank"><i class="material-icons">camera_rear</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        '.$updateBtn.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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
                    'post_date'         => date('d/m/y',strtotime($row->post_date)),
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
                            'qty'                       => number_format($row->getBalanceReceipt(),3,',','.'),
                            'unit'                      => $row->item->buyUnit->code,
                            'place_id'                  => $row->place_id,
                            'place_name'                => $row->place->name,
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
            'code'			            => $request->temp ? ['required', Rule::unique('good_scales', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:good_scales,code',
            'account_id'                => 'required',
            'company_id'                => 'required',
            'vehicle_no'                => 'required',
            'driver'                    => 'required',
            'place_id'                  => 'required',
			'post_date'		            => 'required',
            'arr_item'                  => 'required|array',
            /* 'document'                  => 'required|mimes:jpg,jpeg,png,pdf', */
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai.',
            'account_id.required'               => 'Supplier/vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'vehicle_no.required'               => 'Nomor kendaraan tidak boleh kosong.',
            'driver.required'                   => 'Nama supir tidak boleh kosong.',
            'place_id.required'                 => 'Plant tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            /* 'document.required'                 => 'Bukti tidak boleh kosong.',
            'document.mimes'                    => 'Bukti harus dalam bentuk gambar JPG, JPEG, PNG atau dokumen PDF.', */
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {

                $imageName = '';
                $newFile = '';
                if($request->image_in){
                    $image = $request->image_in;  // your base64 encoded
                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(35).'.png';
                    $newFile = 'public/good_scales/'.$imageName;
                    Storage::put($newFile,base64_decode($image));
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

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/good_scales');
                        } else {
                            $document = $query->document;
                        }

                        if(Storage::exists($query->image_in)){
                            Storage::delete($query->image_in);
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->place_id = $request->place_id;
                        $query->post_date = $request->post_date;
                        $query->delivery_no = $request->delivery_no;
                        $query->vehicle_no = $request->vehicle_no;
                        $query->driver = $request->driver;
                        $query->document = $document;
                        $query->image_in = $newFile ? $newFile : NULL;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();

                        foreach($query->goodScaleDetail as $row){
                            $row->delete();
                        }
                        
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Timbangan Truk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    
                }else{
                    
                    $query = GoodScale::create([
                        'code'			        => $request->code,
                        'user_id'		        => session('bo_id'),
                        'account_id'            => $request->account_id,
                        'company_id'            => $request->company_id,
                        'place_id'              => $request->place_id,
                        'post_date'             => $request->post_date,
                        'delivery_no'           => $request->delivery_no,
                        'vehicle_no'            => $request->vehicle_no,
                        'driver'                => $request->driver,
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_scales') : NULL,
                        'image_in'              => $newFile ? $newFile : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                    ]);
                        
                }
                
                if($query) {

                    foreach($request->arr_purchase as $key => $row){
                        $balance = str_replace(',','.',str_replace('.','',$request->arr_qty_in[$key])) - str_replace(',','.',str_replace('.','',$request->arr_qty_out[$key]));
                        GoodScaleDetail::create([
                            'good_scale_id'             => $query->id,
                            'purchase_order_detail_id'  => $row ? $row : NULL,
                            'item_id'                   => $request->arr_item[$key],
                            'qty_in'                    => str_replace(',','.',str_replace('.','',$request->arr_qty_in[$key])),
                            'qty_out'                   => str_replace(',','.',str_replace('.','',$request->arr_qty_out[$key])),
                            'qty_balance'               => $balance,
                            'note'                      => $request->arr_note[$key],
                            'note2'                     => $request->arr_note2[$key],
                            'place_id'                  => $request->arr_place[$key],
                            'warehouse_id'              => $request->arr_warehouse[$key]
                        ]);
                    }

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
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function rowDetail(Request $request)
    {
        $data   = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4">
                        <div class="col s12">
                            <table class="bordered" style="min-width:100%;max-width:100%;">
                                <thead>
                                    <tr>
                                        <th class="center-align" colspan="12">Daftar Item</th>
                                    </tr>
                                    <tr>
                                        <th class="center-align">No.</th>
                                        <th class="center-align">Ref.PO</th>
                                        <th class="center-align">Item</th>
                                        <th class="center-align">Timbang Masuk</th>
                                        <th class="center-align">Timbang Keluar</th>
                                        <th class="center-align">Qty Netto</th>
                                        <th class="center-align">Satuan</th>
                                        <th class="center-align">Keterangan 1</th>
                                        <th class="center-align">Keterangan 2</th>
                                        <th class="center-align">Plant</th>
                                        <th class="center-align">Gudang</th>
                                    </tr>
                                </thead>
                                <tbody>';
        
        foreach($data->goodScaleDetail as $key => $rowdetail){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.($rowdetail->purchase_order_detail_id ? $rowdetail->purchaseOrderDetail->purchaseOrder->code : '-').'</td>
                <td class="center-align">'.$rowdetail->item->name.'</td>
                <td class="center-align">'.number_format($rowdetail->qty_in,3,',','.').'</td>
                <td class="center-align">'.number_format($rowdetail->qty_out,3,',','.').'</td>
                <td class="center-align">'.number_format($rowdetail->qty_balance,3,',','.').'</td>
                <td class="center-align">'.$rowdetail->item->buyUnit->code.'</td>
                <td class="center-align">'.$rowdetail->note.'</td>
                <td class="center-align">'.$rowdetail->note2.'</td>
                <td class="center-align">'.$rowdetail->place->name.'</td>
                <td class="center-align">'.$rowdetail->warehouse->name.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table>';

        $string .= '</td></tr>';

        $string .= '</tbody></table></div><div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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

    public function getWeight(Request $request)
    {
        $weight = Weight::where('place_id',$request->place_id)->first();

        if($weight){
            $nominal = number_format($weight->nominal,3,',','.');
        }else{
            $nominal = '0,000';
        }

        return response()->json($nominal);
    }

    public function update(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $data['supplier_name'] = $data->account->employee_no.' - '.$data->account->name;
        $data['image_in'] = $data->imageIn();

        $details = [];
        foreach($data->goodScaleDetail as $row){
            $details[] = [
                'id'                        => $row->id,
                'purchase_order_detail_id'  => $row->purchase_order_detail_id ? $row->purchase_order_detail_id : '',
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->code.' - '.$row->item->name,
                'qty_po'                    => $row->purchase_order_detail_id ? number_format($row->purchaseOrderDetail->getBalanceReceipt(),3,',','.') : '-',
                'qty_in'                    => number_format($row->qty_in,3,',','.'),
                'qty_out'                   => number_format($row->qty_out,3,',','.'),
                'unit'                      => $row->item->buyUnit->code,
                'place_id'                  => $row->place_id,
                'place_name'                => $row->place->code.' - '.$row->place->name,
                'warehouse_id'              => $row->warehouse_id,
                'warehouse_name'            => $row->warehouse->name,
                'note'                      => $row->note,
                'note2'                     => $row->note2,
            ];
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function saveUpdate(Request $request){

        $overtolerance = false;

        foreach($request->arr_good_scale_detail as $key => $row){
            if($row){
                $pod = GoodScaleDetail::find($row);
                if($pod){
                    if($pod->purchase_order_detail_id){
                        $balanceweight = $pod->qty_in - str_replace(',','.',str_replace('.','',$request->arr_qty_out[$key]));

                        $tolerance_gr = $pod->item->tolerance_gr ? $pod->item->tolerance_gr : 0;

                        $balance = $balanceweight - $pod->purchaseOrderDetail->getBalanceReceipt();

                        $percent_balance = round(($balance / $pod->qty_balance) * 100,2);

                        if($percent_balance > $tolerance_gr){
                            $overtolerance = true;
                        }
                    }
                }
            }
        }

        if($overtolerance){
            return response()->json([
                'status'  => 500,
                'message' => 'Prosentase qty diterima melebihi prosentase toleransi yang telah diatur.'
            ]);
        }

        $adapo = false;
        $idgs = 0;

        $gs = GoodScale::find($request->tempGoodScale);

        if($gs){
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
            $gs->image_out = $newFile ? $newFile : NULL;
            $gs->save();
        }

        foreach($request->arr_good_scale_detail as $key => $row){
            $query = GoodScaleDetail::find($row);
            if($query->qty_out == 0){
                $query->qty_out = str_replace(',','.',str_replace('.','',$request->arr_qty_out[$key]));
                $query->qty_balance = $query->qty_in - str_replace(',','.',str_replace('.','',$request->arr_qty_out[$key]));
                $query->purchase_order_detail_id = $request->arr_pod[$key] ? $request->arr_pod[$key] : NULL;
                $query->save();
            }
            if($request->arr_pod[$key]){
                $adapo = true;
                $idgs = $query->good_scale_id;
            }
        }

        if($adapo){
            if($idgs > 0){
                GoodScale::find($idgs)->createGoodReceipt();
            }
        }

        $response = [
            'status'    => 200,
            'message'   => 'Data successfully updated.',
        ];

        return response()->json($response);
    }

    public function show(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
        $data['code_place_id'] = substr($data->code,7,2);

        $details = [];
        foreach($data->goodScaleDetail as $row){
            $details[] = [
                'id'                        => $row->id,
                'purchase_order_detail_id'  => $row->purchase_order_detail_id,
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->code.' - '.$row->item->name,
                'qty_po'                    => $row->purchase_order_detail_id ? number_format($row->purchaseOrderDetail->qty,3,',','.') : '-',
                'qty_in'                    => number_format($row->qty_in,3,',','.'),
                'qty_out'                   => number_format($row->qty_out,3,',','.'),
                'qty_balance'               => number_format($row->qty_balance,3,',','.'),
                'unit'                      => $row->item->buyUnit->code,
                'place_id'                  => $row->place_id,
                'place_name'                => $row->place->name,
                'warehouse_id'              => $row->warehouse_id,
                'warehouse_name'            => $row->warehouse->name,
                'list_warehouse'            => $row->item->warehouseList(),
                'note'                      => $row->note,
                'note2'                     => $row->note2,
            ];
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function voidStatus(Request $request){
        $query = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();
        
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

            $query->goodScaleDetail()->delete();

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
        
        $pr = GoodScale::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $data = [
                'title'     => 'Good Scale',
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
             
            $pdf = Pdf::loadView('admin.print.inventory.good_scale_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
    
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
                    $data = [
                        'title'     => 'Timbangan Truk',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.inventory.good_scale_individual', $data)->setPaper('a5', 'landscape');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = GoodScale::where('Code', 'LIKE', '%'.$etNumbersArray[$nomor-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Timbangan Truk',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.inventory.good_scale_individual', $data)->setPaper('a5', 'landscape');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = GoodScale::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Timbangan Truk',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.inventory.good_scale_individual', $data)->setPaper('a5', 'landscape');
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		return Excel::download(new ExportGoodScale($post_date,$end_date), 'good_scale_'.uniqid().'.xlsx');
    }
}