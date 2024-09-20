<?php

namespace App\Http\Controllers\Production;

use App\Exports\ExportProductionWorkingHourTransactionPage;
use iio\libmergepdf\Merger;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use App\Helpers\TreeHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Line;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\Area;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\WaBlas;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchUsage;
use App\Models\ProductionRepack;
use App\Models\ProductionRepackDetail;
use App\Models\Shift;
use App\Models\UsedData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionRepackController extends Controller
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
            'title'         => 'Production Repack',
            'content'       => 'admin.production.production_repack',
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
        $code = ProductionRepack::generateCode($request->val);
		return response()->json($code);
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

        $total_data = ProductionRepack::count();
        
        $query_data = ProductionRepack::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ProductionRepack::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Cetak Barcode" onclick="barcode(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">style</i></button>
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

    public function getItemData(Request $request){
        $type = $request->type;
        $item = $request->id;
        if($type == 'source'){
            $data = Item::find($item);
            $data['list_stock'] = $data->currentStockMoreThanZero($this->dataplaces,$this->datawarehouses);
            $data['uom_unit_code'] = $data->uomUnit->code;
            $data['sell_units'] = $data->arrSellUnits();
        }else{
            $data = Item::find($item);
            $data['list_stock'] = $data->currentStockMoreThanZero($this->dataplaces,$this->datawarehouses);
            $data['uom_unit'] = $data->uomUnit->code;
            $data['sell_units'] = $data->arrSellUnits();
        }
        return response()->json($data);
    }

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'company_id'			    => 'required',
                'post_date'		            => 'required',
                'note'                      => 'required',
                'arr_item_source'           => 'required|array',
                'arr_item_stock'            => 'required|array',
                'arr_qty'                   => 'required|array',
                'arr_qty_conversion_source' => 'required|array',
                'arr_unit_conversion'       => 'required|array',
                'arr_item_target'           => 'required|array',
                'arr_qty_conversion_target' => 'required|array',
                'arr_unit_target_conversion'=> 'required|array',
                'arr_batch_no'              => 'required|array',
            ], [
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
                'note.required'                     => 'Keterangan harus dalam bentuk array.',
                'arr_item_source.required'          => 'Item sumber tidak boleh kosong.',
                'arr_item_source.array'             => 'Item sumber harus array.',
                'arr_item_stock.required'           => 'Stok asal tidak boleh kosong.',
                'arr_item_stock.array'              => 'Stok asal harus array.',
                'arr_qty.required'                  => 'Qty tidak boleh kosong.',
                'arr_qty.array'                     => 'Qty harus array.',
                'arr_qty_conversion_source.required'=> 'Qty konversi boleh kosong.',
                'arr_qty_conversion_source.array'   => 'Qty konversi harus array.',
                'arr_unit_conversion.required'      => 'Satuan konversi boleh kosong.',
                'arr_unit_conversion.array'         => 'Satuan konversi harus array.',
                'arr_item_target.required'          => 'Item target boleh kosong.',
                'arr_item_target.array'             => 'Item target harus array.',
                'arr_qty_conversion_target.required'=> 'Qty konversi target boleh kosong.',
                'arr_qty_conversion_target.array'   => 'Qty konversi target harus array.',
                'arr_unit_target_conversion.required'=> 'Satuan konversi target boleh kosong.',
                'arr_unit_target_conversion.array'   => 'Satuan konversi target harus array.',
                'arr_batch_no.required'             => 'Nomor batch boleh kosong.',
                'arr_batch_no.array'                => 'Nomor batch harus array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $arrItemStock = [];
                $arrItemQty = [];
                $arrErrorItem = [];

                if($request->arr_item_stock){
                    foreach($request->arr_item_stock as $key => $row){
                        if(!in_array($row,$arrItemStock)){
                            $arrItemStock[] = $row;
                            $arrItemQty[] = str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                        }else{
                            $index = array_search($row,$arrItemStock);
                            $arrItemQty[$index] += str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                        }
                    }

                    foreach($arrItemStock as $key => $row){
                        $itemstock = NULL;
                        $itemstock = ItemStock::find($row);
                        if($itemstock){
                            $qtyStock = $itemstock->stockByDate($request->post_date);
                            if($qtyStock < $arrItemQty[$key]){
                                $arrErrorItem[] = 'Item '.$itemstock->item->name.' qty stock tidak mencukupi pada tanggal terpilih. Stok : '.CustomHelper::formatConditionalQty($qtyStock).' - kebutuhan '.CustomHelper::formatConditionalQty($arrItemQty[$key]);
                            }
                        }else{
                            $arrErrorItem[] = 'Data item stock tidak ditemukan.';
                        }
                    }
                }

                if(count($arrErrorItem) > 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf, '.implode(', ',$arrErrorItem).'.',
                    ]);
                }

                if($request->temp){
                    $query = ProductionRepack::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Production repack telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','2','5','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_repack');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionRepackDetail as $row){
                            $row->productionBatchUsage()->delete();
                            $row->productionBatch()->delete();
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Production Working Hour sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ProductionRepack::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionRepack::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_repack') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {
                    
                    $yearno = date('ym',strtotime($request->post_date));
                    foreach($request->arr_item_source as $key => $row){
                        $itemStock = NULL;
                        $itemStock = ItemStock::find($request->arr_item_stock[$key]);
                        if($itemStock){
                            $total = round($itemStock->priceFgNow($request->post_date) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),2);
                            $prd = ProductionRepackDetail::create([
                                'production_repack_id'      => $query->id,
                                'item_source_id'            => $row,
                                'item_stock_id'             => $request->arr_item_stock[$key],
                                'qty'                       => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'item_unit_source_id'       => $request->arr_unit_conversion[$key],
                                'item_target_id'            => $request->arr_item_target[$key],
                                'item_unit_target_id'       => $request->arr_unit_target_conversion[$key],
                                'place_id'                  => $itemStock->place_id,
                                'warehouse_id'              => $itemStock->warehouse_id,
                                'total'                     => $total,
                            ]);

                            $item_shading_id = NULL;
                            $shading = ItemShading::where('item_id',$request->arr_item_target[$key])->where('code',$itemStock->itemShading->code)->first();
                            if(!$shading){
                                $shading = ItemShading::create([
                                    'item_id'   => $request->arr_item_target[$key],
                                    'code'      => $itemStock->itemShading->code,
                                ]);   
                            }
                            $item_shading_id = $shading->id;
                            
                            $runningno = ProductionBatch::getLatestCodeFg($yearno);
                            $newbatch = $request->arr_batch_no[$key].'/'.$runningno;

                            $lineshiftgroup = explode('/',$request->arr_batch_no[$key])[1];

                            $linecode = explode('-',$lineshiftgroup)[0];
                            $shiftcode = substr(explode('-',$lineshiftgroup)[1],0,1);
                            $group = substr(explode('-',$lineshiftgroup)[1],1,1);

                            $line = Line::where('code',$linecode)->first();
                            $shift = Shift::where('production_code',$shiftcode)->first();

                            $batch = ProductionBatch::create([
                                'code'              => $newbatch,
                                'item_id'           => $prd->item_target_id,
                                'place_id'          => $prd->place_id,
                                'warehouse_id'      => $prd->warehouse_id,
                                'area_id'           => $itemStock->area_id,
                                'item_shading_id'   => $item_shading_id,
                                'lookable_type'     => $prd->getTable(),
                                'lookable_id'       => $prd->id,
                                'qty'               => $prd->qty,
                                'qty_real'          => $prd->qty,
                                'total'             => $total,
                            ]);

                            $prd->update([
                                'item_shading_id'       => $item_shading_id,
                                'production_batch_id'   => $batch->id,
                                'area_id'               => $itemStock->area_id,
                                'line_id'               => $line ? $line->id : NULL,
                                'shift_id'              => $shift ? $shift->id : NULL,
                                'group'                 => $group,
                                'batch_no'              => $batch->code,
                            ]);

                            ProductionBatchUsage::create([
                                'production_batch_id'   => $itemStock->production_batch_id,
                                'lookable_type'         => $prd->getTable(),
                                'lookable_id'           => $prd->id,
                                'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            ]);
                        }
                    }
                    
                    CustomHelper::sendApproval($query->getTable(),$query->id,'Production Repack No. '.$query->code);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Repack No. '.$query->code,'Pengajuan Production Repack No. '.$query->code,session('bo_id'));

                    activity()
                        ->performedOn(new ProductionRepack())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit production repack.');

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
        
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }

		return response()->json($response);
    }

    public function show(Request $request){
        $detail = [];

        $po = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();

        foreach($po->productionRepackDetail as $row){
            $arr = explode('/',$row->batch_no);
            $detail[] = [
                'item_source_id'                => $row->item_source_id,
                'item_source_info'              => $row->itemSource->code.' - '.$row->itemSource->name,
                'item_unit_source'              => $row->itemSource->uomUnit->code,
                'qty'                           => CustomHelper::formatConditionalQty($row->qty),
                'qty_conversion_source'         => CustomHelper::formatConditionalQty(round($row->qty / $row->itemUnitSource->conversion,3)),
                'list_stock'                    => $row->itemSource->currentStock($this->dataplaces,$this->datawarehouses),
                'unit_source_conversion'        => $row->itemSource->arrSellUnits(),
                'item_unit_source_id'           => $row->item_unit_source_id,
                'source_batch'                  => $row->itemStock->productionBatch->code,
                'item_target_id'                => $row->item_target_id,
                'item_target_info'              => $row->itemTarget->code.' - '.$row->itemTarget->name,
                'qty_conversion_target'         => CustomHelper::formatConditionalQty(round($row->qty / $row->itemUnitTarget->conversion,3)),
                'unit_target_conversion'        => $row->itemTarget->arrSellUnits(),
                'item_unit_target_id'           => $row->item_unit_target_id,
                'batch_no'                      => $arr[0].'/'.$arr[1],
                'item_stock_id'                 => $row->item_stock_id,
            ];
        }

        $po['code_place_id']                    = substr($po->code,7,2);
        $po['details']                          = $detail;
        
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionRepack::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Production Repack',
                'data'      => $pr
            ];

            return view('admin.approval.production_repack', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="12" style="font-size:20px !important;">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center">'.__('translations.no').'</th>
                                <th class="center">Item Sumber</th>
                                <th class="center">Ambil dari Stok</th>
                                <th class="center">Jumlah (Stock)</th>
                                <th class="center">Satuan (Stock)</th>
                                <th class="center">Qty (Konversi)</th>
                                <th class="center">Satuan (Konversi)</th>
                                <th class="center">Batch Lama</th>
                                <th class="center">Item Target</th>
                                <th class="center">Qty (Konversi)</th>
                                <th class="center">Satuan (Konversi)</th>
                                <th class="center">Batch Baru</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->productionRepackDetail()->orderBy('id')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'</td>
                <td>'.$row->itemSource->name.'</td>
                <td>'.$row->itemStock->place->code.' - '.$row->itemStock->warehouse->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->itemSource->uomUnit->code.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty(round($row->qty / $row->itemUnitSource->conversion,3)).'</td>
                <td class="center-align">'.$row->itemUnitSource->unit->code.'</td>
                <td class="center-align">'.$row->itemStock->productionBatch->code.'</td>
                <td>'.$row->itemTarget->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty(round($row->qty / $row->itemUnitSource->conversion,3)).'</td>
                <td class="center-align">'.$row->itemUnitTarget->unit->code.'</td>
                <td class="center-align">'.$row->batch_no.'</td>
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
        
        $pr = ProductionRepack::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Production Working Hour','a4','portrait','admin.print.production.production_repack_individual',$menuUser->mode);
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
        $query = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }else{
                $tempStatus = $query->status;

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                if(in_array($tempStatus,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                }

                foreach($query->productionRepackDetail as $rowdetail){
                    $rowdetail->productionBatchUsage()->delete();
                    $rowdetail->productionBatch()->delete();
                }

                activity()
                    ->performedOn(new ProductionRepack())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Production Repack data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Production Repack No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
        $query = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->productionRepackDetail as $row){
                $row->productionBatchUsage()->delete();
                $row->productionBatch()->delete();
                $row->delete();
            }

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionRepack())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Production Repack data');

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
                $pr = ProductionRepack::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Production Receive','a4','portrait','admin.print.production.production_repack_individual');
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
                        $query = ProductionRepack::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.production_repack_individual');
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
                        $query = ProductionRepack::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.production_repack_individual');
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
        $query = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                'url'=>request()->root()."/admin/production/production_fg_receive?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_core;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_fg_receive',$query->id);
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

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = ProductionRepack::where('code',CustomHelper::decrypt($id))->first();
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
        $query_done = ProductionRepack::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new ProductionRepack())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Production Repack data');
    
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

    public function printBarcode(Request $request,$id){
        
        $pr = ProductionRepack::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Production Receive FG',array(0,0,264.57,188.98),'portrait','admin.print.production.repack_barcode');
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);$var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportProductionWorkingHourTransactionPage($search,$status,$end_date,$start_date), 'production_schedule'.uniqid().'.xlsx');
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportProductionWorkingHour($post_date,$end_date,$mode), 'production_fg_receive'.uniqid().'.xlsx');
    }
}
