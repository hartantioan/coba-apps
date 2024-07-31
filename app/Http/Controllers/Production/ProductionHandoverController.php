<?php

namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Menu;
use App\Helpers\TreeHelper;
use App\Models\Place;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Bom;
use App\Models\BomAlternative;
use App\Models\BomDetail;
use App\Models\Grade;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Line;
use App\Models\Pallet;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchUsage;
use App\Models\ProductionFgReceive;
use App\Models\ProductionFgReceiveDetail;
use App\Models\ProductionFgReceiveMaterial;
use App\Models\ProductionHandover;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionReceive;
use App\Models\ProductionReceiveDetail;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\Shift;
use App\Models\Tank;
use App\Models\User;
use App\Models\MenuUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportProductionHandover;
class ProductionHandoverController extends Controller
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
            'title'         => 'Serah Terima',
            'content'       => 'admin.production.handover',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = ProductionHandover::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getScanBarcode(Request $request){
        $code = $request->code;
        $data = ProductionFgReceiveDetail::where('production_fg_receive_id',$request->fgr_id)
        ->where('pallet_no', $code)
        ->first();

        if($data){
            if($data->balanceHandover() > 0){
                $arr = [
                    'id'            => $data->id,
                    'pallet_no'     => $data->pallet_no,
                    'item_id'       => $data->item_id,
                    'item_code'     => $data->item->code,
                    'item_name'     => $data->item->name,
                    'shading'       => $data->shading,
                    'qty'           => CustomHelper::formatConditionalQty($data->qty_sell),
                    'unit'          => $data->itemUnit->unit->code,
                    'list_warehouse'=> $data->item->warehouseList(),
                    'place_id'      => $data->productionFgReceive->place_id,
                    'place_code'    => $data->productionFgReceive->place->code,
                ];
                
                $response = [
                    'status'    => 200,
                    'data'      => $arr,
                    'message'   => 'Data berhasil ditarik.'
                ];
            }else{
                $response = [
                    'status'    => 500,
                    'message'   => 'Outstanding qty yang bisa diterima adalah 0. Data tidak bisa ditarik.'
                ];
            }
        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.',
            ];
        }
        				
		return response()->json($response);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ProductionHandover::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionHandover::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })->orWhereHas('productionFgReceive',function($query)use($search){
                                $query->whereHas('productionOrderDetail',function($query) use ($search){
                                    $query->whereHas('productionOrder',function($query) use ($search){
                                        $query->where('code', 'like', "%$search%");
                                    });
                                })->orWhere('code', 'like', "%$search%");
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

            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ProductionHandover::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })->orWhereHas('productionFgReceive',function($query)use($search){
                                $query->whereHas('productionOrderDetail',function($query) use ($search){
                                    $query->whereHas('productionOrder',function($query) use ($search){
                                        $query->where('code', 'like', "%$search%");
                                    });
                                })->orWhere('code', 'like', "%$search%");;
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
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->productionFgReceive->code,
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
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
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function create(Request $request){
        /* DB::beginTransaction();
        try { */
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'company_id'			    => 'required',
                'post_date'		            => 'required',
                'production_fg_receive_id'  => 'required',
                'arr_prfd_id'               => 'required|array',
                'arr_item_id'               => 'required|array',
                'arr_area_id'               => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_place'                 => 'required|array',
                'arr_warehouse'             => 'required|array',
            ], [
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
                'production_fg_receive_id.required' => 'Serah Terima Produksi tidak boleh kosong.',
                'arr_prfd_id.required'              => 'Receive FG detail tidak boleh kosong.',
                'arr_prfd_id.array'                 => 'Receive FG detail harus array.',
                'arr_item_id.required'              => 'Item tidak boleh kosong.',
                'arr_item_id.array'                 => 'Item harus array.',
                'arr_area_id.required'              => 'Area tidak boleh kosong.',
                'arr_area_id.array'                 => 'Area harus array.',
                'arr_qty.required'                  => 'Qty input tidak boleh kosong.',
                'arr_qty.array'                     => 'Qty input harus array.',
                'arr_place.required'                => 'Plant tidak boleh kosong.',
                'arr_place.array'                   => 'Plant harus array.',
                'arr_warehouse.required'            => 'Gudang tidak boleh kosong.',
                'arr_warehouse.array'               => 'Gudang harus array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                
                $passedQty = true;
                
                $arrQty = [];
                foreach($request->arr_qty as $key => $row){
                    $index = -1;
                    foreach($arrQty as $z => $rowqty){
                        if($rowqty['prfd_id'] == $request->arr_prfd_id[$key]){
                            $index = $z;
                        }
                    }
                    if($index >= 0){
                        $arrQty[$index]['qty'] += str_replace(',','.',str_replace('.','',$row));
                    }else{
                        $arrQty[] = [
                            'prfd_id'   => $request->arr_prfd_id[$key],
                            'qty'       => str_replace(',','.',str_replace('.','',$row)),
                        ];
                    }
                }

                $arrMessageQty = [];
                
                foreach($arrQty as $row){
                    $data = ProductionFgReceiveDetail::find($row['prfd_id']);
                    if($data){
                        $max = $data->balanceHandover();
                        if($row['qty'] > $max){
                            $balance = CustomHelper::formatConditionalQty($row['qty'] - $max);
                            $arrMessageQty[] = 'Item '.$data->item->code.' - '.$data->item->name.' nomor batch '.$data->pallet_no.' kelebihan penerimaan sebesar '.$balance.' dari sisa penerimaan seharusnya '.CustomHelper::formatConditionalQty($max);
                        }
                    }
                }

                if(count($arrMessageQty) > 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode('. ',$arrMessageQty),
                    ]);
                }

                if($request->temp){
                    $query = ProductionHandover::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Serah Terima Produksi telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','2','5','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_handovers');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->production_fg_receive_id = $request->production_fg_receive_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionHandoverDetail as $row){
                            if($row->productionBatchUsage()->exists()){
                                CustomHelper::updateProductionBatch($row->productionBatchUsage->production_batch_id,$row->productionBatchUsage->qty,'IN');
                            }
                            $row->productionBatchUsage()->delete();
                            $row->productionBatch()->delete();
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Serah Terima Produksi sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ProductionHandover::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionHandover::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'production_fg_receive_id'  => $request->production_fg_receive_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_handovers') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {
                    
                    foreach($request->arr_qty as $key => $row){
                        $prfgd = ProductionFgReceiveDetail::find($request->arr_prfd_id[$key]);
                        if($prfgd){
                            $qtyuom = str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) * $prfgd->conversion;
                            $rowcost = round($prfgd->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->priceNowProduction($prfgd->productionFgReceive->place_id,$request->post_date) * $qtyuom,2);
                            $itemShading = ItemShading::where('item_id',$request->arr_item_id[$key])->where('code',$prfgd->shading)->first();

                            if(!$itemShading){
                                $itemShading = ItemShading::create([
                                    'item_id'   => $request->arr_item_id[$key],
                                    'code'      => $prfgd->shading,
                                ]);
                            }

                            $querydetail = ProductionHandoverDetail::create([
                                'production_handover_id'            => $query->id,
                                'production_fg_receive_detail_id'   => $prfgd->id,
                                'item_id'                           => $request->arr_item_id[$key],
                                'qty'                               => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'shading'                           => $prfgd->shading,
                                'place_id'                          => $request->arr_place[$key],
                                'warehouse_id'                      => $request->arr_warehouse[$key],
                                'area_id'                           => $request->arr_area_id[$key],
                                'total'                             => $rowcost,
                                'item_shading_id'                   => $itemShading->id,
                            ]);
                            
                            ProductionBatchUsage::create([
                                'production_batch_id'   => $prfgd->productionBatch->id,
                                'lookable_type'         => $querydetail->getTable(),
                                'lookable_id'           => $querydetail->id,
                                'qty'                   => $qtyuom,
                            ]);

                            CustomHelper::updateProductionBatch($prfgd->productionBatch->id,$qtyuom,'OUT');

                            ProductionBatch::create([
                                'code'          => $querydetail->productionFgReceiveDetail->pallet_no,
                                'item_id'       => $querydetail->item_id,
                                'place_id'      => $request->arr_place[$key],
                                'warehouse_id'  => $request->arr_warehouse[$key],
                                'area_id'       => $request->arr_area_id[$key],
                                'item_shading_id'=> $itemShading->id,
                                'lookable_type' => $querydetail->getTable(),
                                'lookable_id'   => $querydetail->id,
                                'qty'           => $qtyuom,
                                'qty_real'      => $qtyuom,
                                'total'         => $rowcost,
                            ]);
                        }
                    }
                    
                    CustomHelper::sendApproval($query->getTable(),$query->id,'Serah Terima Produksi No. '.$query->code);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Serah Terima Produksi No. '.$query->code,'Pengajuan Production Receive No. '.$query->code,session('bo_id'));

                    activity()
                        ->performedOn(new ProductionHandover())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit handover fg.');

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
        $detail_receive = [];

        $po = ProductionHandover::where('code',CustomHelper::decrypt($request->id))->first();

        foreach($po->productionHandoverDetail()->orderBy('id')->get() as $key => $row){
            $detail_receive[] = [
                'prfd_id'               => $row->production_fg_receive_detail_id,
                'item_id'               => $row->item_id,
                'item_code'             => $row->item->code,
                'item_name'             => $row->item->name,
                'unit'                  => $row->productionFgReceiveDetail->itemUnit->unit->code,
                'area_id'               => $row->area_id,
                'area_code'             => $row->area->code,
                'pallet_no'             => $row->productionFgReceiveDetail->pallet_no,
                'shading'               => $row->shading,
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'qty_reject'            => CustomHelper::formatConditionalQty($row->qty_reject),
                'qty_received'          => CustomHelper::formatConditionalQty($row->qty_received),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
                'list_warehouse'        => $row->item->warehouseList(),
            ];
        }

        $po['code_place_id']                    = substr($po->code,7,2);
        $po['production_fg_receive_code']       = $po->productionFgReceive->code.' Tgl.Post '.date('d/m/Y',strtotime($po->productionFgReceive->post_date)).' - Plant : '.$po->productionFgReceive->place->code.' - Line : '.$po->productionFgReceive->line->code.' - '.$po->productionFgReceive->item->code.' - '.$po->productionFgReceive->item->name;
        $po['details']                          = $detail_receive;
        
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionHandover::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Serah Terima Produksi',
                'data'      => $pr
            ];

            return view('admin.approval.production_handover', $data);
        }else{
            abort(404);
        }
    }


    public function rowDetail(Request $request)
    {
        $data   = ProductionHandover::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10" style="font-size:20px !important;">Daftar Item Receive</th>
                            </tr>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">No.Batch Palet/Curah</th>
                                <th class="center">Item</th>
                                <th class="center">'.__('translations.shading').'</th>
                                <th class="center">Qty Diterima</th>
                                <th class="center">Satuan</th>
                                <th class="center">Konversi</th>
                                <th class="center">'.__('translations.plant').'</th>
                                <th class="center">'.__('translations.warehouse').'</th>
                                <th class="center">'.__('translations.area').'</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->productionHandoverDetail()->orderBy('id')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'</td>
                <td>'.$row->productionFgReceiveDetail->pallet_no.'</td>
                <td>'.$row->item->code.' - '.$row->item->name.'</td>
                <td>'.$row->shading.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->productionFgReceiveDetail->itemUnit->unit->code.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->productionFgReceiveDetail->conversion).'</td>
                <td class="">'.$row->place->code.'</td>
                <td class="">'.$row->warehouse->name.'</td>
                <td class="">'.$row->area->code.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col m12 s12 mt-1"><table style="min-width:100%;"><thead>
                    <tr>
                        <th colspan="5" class="center-align">Daftar Batch Terpakai</th>
                    </tr>
                    <tr>
                        <th class="center">'.__('translations.no').'.</th>
                        <th class="center">No.Batch</th>
                        <th class="center">Item Parent</th>
                        <th class="center">Item Child</th>
                        <th class="center">Qty</th>
                        <th class="center">Satuan</th>
                    </tr>
                </thead><tbody>';

        foreach($data->productionHandoverDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'</td>
                <td>'.$row->productionBatchUsage->productionBatch->code.'</td>
                <td>'.$data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->code.' - '.$data->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->name.'</td>
                <td>'.$row->productionBatchUsage->productionBatch->item->code.' - '.$row->productionBatchUsage->productionBatch->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->productionBatchUsage->qty).'</td>
                <td class="center-align">'.$row->productionBatchUsage->productionBatch->item->uomUnit->code.'</td>
            </tr>';
        }

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

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = ProductionHandover::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Serah Terima Produksi','a4','portrait','admin.print.production.handover_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);$var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = ProductionHandover::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    if($query->productionFgReceive()->exists()){
                        $query->productionFgReceive->update([
                            'status'    => '2'
                        ]);
                    }
                }

                foreach($query->productionHandoverDetail as $row){
                    if($row->productionBatchUsage()->exists()){
                        CustomHelper::updateProductionBatch($row->productionBatchUsage->production_batch_id,$row->productionBatchUsage->qty,'IN');
                    }
                    $row->productionBatchUsage()->delete();
                    $row->productionBatch()->delete();
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                activity()
                    ->performedOn(new ProductionHandover())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Serah Terima Produksi data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Serah Terima Produksi No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);

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
        $query = ProductionHandover::where('code',CustomHelper::decrypt($request->id))->first();

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
        
        if($query->delete()){
            
            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            foreach($query->productionHandoverDetail as $row){
                if($row->productionBatchUsage()->exists()){
                    CustomHelper::updateProductionBatch($row->productionBatchUsage->production_batch_id,$row->productionBatchUsage->qty,'IN');
                }
                $row->productionBatchUsage()->delete();
                $row->productionBatch()->delete();
                $row->delete();
            }

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionHandover())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Serah Terima Produksi data');

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
                $pr = ProductionReceive::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Production Receive','a4','portrait','admin.print.production.handover_individual');
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
                        $query = ProductionReceive::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.handover_individual');
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
                        $query = ProductionReceive::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.handover_individual');
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
        $query = ProductionHandover::where('code',CustomHelper::decrypt($request->id))->first();
        
        $data_go_chart=[];
        $data_link=[];


        if($query){
            $data_core = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/production/production_handover?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_core;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_handover',$query->id);
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

    public function sendUsedData(Request $request){
        $mop = ProductionBatch::find($request->id);
       
        if(!$mop->used()->exists()){
            CustomHelper::sendUsedData($request->type,$request->id,'Form Serah Terima Produksi');
            return response()->json([
                'status'    => 200,
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Dokumen no. '.$mop->used->lookable->code.' telah dipakai di '.$mop->used->ref.', oleh '.$mop->used->user->name.'.'
            ]);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = ProductionHandover::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += $row->nominal;
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += $row->nominal;
                }
                
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
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string;
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new ProductionReceive())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Production Receive data');
    
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
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportProductionIssueReceiveTransactionPage($search,$status,$end_date,$start_date), 'production_schedule'.uniqid().'.xlsx');
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportProductionHandover($post_date,$end_date,$mode), 'production_handover'.uniqid().'.xlsx');
    }
}