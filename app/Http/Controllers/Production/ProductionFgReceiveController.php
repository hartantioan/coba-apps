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
use App\Models\ItemStock;
use App\Models\Line;
use App\Models\Pallet;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchUsage;
use App\Models\ProductionFgReceive;
use App\Models\ProductionFgReceiveDetail;
use App\Models\ProductionFgReceiveMaterial;
use App\Models\ProductionReceive;
use App\Models\ProductionReceiveDetail;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UsedData;
use App\Models\MenuUser;
use App\Exports\ExportProductionFgReceive;
use App\Models\ItemCogs;
use App\Models\ProductionBarcodeDetail;

class ProductionFgReceiveController extends Controller
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
            'title'         => 'Receive FG',
            'content'       => 'admin.production.receive_fg',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'line'          => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = ProductionFgReceive::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getPalletBarcode(Request $request){

        $barcode = ProductionBarcodeDetail::find($request->production_barcode_detail_id);

        if($barcode){
            if(!$barcode->productionFgReceiveDetail()->exists()){
                $result[] = [
                    'item_id'       => $barcode->item_id,
                    'item_code'     => $barcode->item->code,
                    'item_name'     => $barcode->item->name,
                    'item_unit_id'  => $barcode->item_unit_id,
                    'code'          => $barcode->pallet_no,
                    'shading'       => $barcode->shading,
                    'qty_convert'   => CustomHelper::formatConditionalQty($barcode->conversion),
                    'qty_sell'      => CustomHelper::formatConditionalQty($barcode->qty_sell),
                    'qty_uom'       => CustomHelper::formatConditionalQty($barcode->qty),
                    'sell_unit'     => $barcode->item->sellUnit(),
                    'uom_unit'      => $barcode->item->uomUnit->code,
                    'plant'         => $barcode->productionBarcode->place->code,
                    'plant_id'      => $barcode->productionBarcode->place_id,
                    'shift'         => $barcode->productionBarcode->shift->production_code,
                    'shift_id'      => $barcode->productionBarcode->shift_id,
                    'group'         => $barcode->productionBarcode->group,
                    'pallet_id'     => $barcode->pallet_id,
                    'grade_id'      => $barcode->grade_id,
                ];
    
                return response()->json($result);
            }else{
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Data telah dipakai pada dokumen lainnya.'
                ]);
            }
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.'
            ]);
        }
    }

    public function getChildFg(Request $request){

        $pod = ProductionOrderDetail::find($request->pod_id);
        $pallet = Pallet::find($request->pallet_id);
        $grade = Grade::find($request->grade_id);

        $itemChild = Item::whereHas('parentFg',function($query)use($pod){
            $query->where('parent_id',$pod->productionScheduleDetail->item_id);
        })
        ->where('pallet_id',$pallet->id)
        ->where('grade_id',$grade->id)
        ->where('status','1')
        ->first();

        if($itemChild){
            return response()->json([
                'status'    => 200,
                'name'      => $itemChild->code.' - '.$itemChild->name,
                'conversion'=> CustomHelper::formatConditionalQty($itemChild->sellConversion()),
                'unit'      => $itemChild->sellUnit(),
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data item child dengan palet dan grade terpilih tidak ditemukan.'
            ]);
        }
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

        $total_data = ProductionFgReceive::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionFgReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })->orWhereHas('productionOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('productionOrder',function($query) use ($search){
                                    $query->where('code', 'like', "%$search%");
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

            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ProductionFgReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })->orWhereHas('productionOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('productionOrder',function($query) use ($search){
                                    $query->where('code', 'like', "%$search%");
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
                    $val->productionOrderDetail->productionOrder->code,
                    $val->productionIssue()->exists() ? $val->productionIssueList() : '-',
                    $val->item->code.' - '.$val->item->name,
                    $val->place->code,
                    $val->line->code,
                    $val->shift->code.' - '.$val->shift->name,
                    $val->group,
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

    public function create(Request $request){
        /* DB::beginTransaction();
        try { */
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'company_id'			    => 'required',
                'place_id'                  => 'required',
                'shift_id'                  => 'required',
                'group'                     => 'required',
                'line_id'                   => 'required',
                'post_date'		            => 'required',
                'production_order_detail_id'=> 'required',
                'arr_pallet_no'             => 'required|array',
            ], [
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'place_id'                          => 'Plant tidak boleh kosong.',
                'shift_id'                          => 'Shift tidak boleh kosong.',
                'group'                             => 'Group tidak boleh kosong.',
                'line_id'                           => 'Line tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
                'production_order_detail_id.required'=> 'Production Order Detail tidak boleh kosong.',
                'arr_pallet_no.required'            => 'Batch tidak boleh kosong.',
                'arr_pallet_no.array'               => 'Batch harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $passedStockMaterial = true;
                $passedBatchUsed = true;
                $arrBatch = [];
                $arrBatchQty = [];
                $arrBatchError = [];
                $arrItemMore = [];
                $arrItem = [];
                $arrQty = [];
                $passedBatchCode = true;

                if($request->arr_production_batch_id){
                    foreach($request->arr_production_batch_id as $key => $row){
                        if(!in_array($row,$arrBatch)){
                            $arrBatch[] = $row;
                            $arrBatchQty[] = str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$key]));
                        }else{
                            $index = array_search($row,$arrBatch);
                            $arrBatchQty[$index] += str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$key]));
                        }
                    }
                }
                
                foreach($arrBatch as $key => $row){
                    $pb = ProductionBatch::find($row);
                    if($pb){
                        if($request->post_date >= $pb->lookable->parent->post_date){
                            if($arrBatchQty[$key] > $pb->qty){
                                $arrBatchError[] = 'Terdapat batch melebihi pemakaian stock : '.CustomHelper::formatConditionalQty($pb->qty).' sedangkan pemakaian : '.CustomHelper::formatConditionalQty($arrBatchQty[$key]).'.';
                                $passedBatchUsed = false;
                            }
                        }else{
                            $arrBatchError[] = 'Batch No. '.$pb->code.' berada di luar tanggal post date.';
                            $passedBatchUsed = false;
                        }
                    }
                }

                if($request->arr_item_id){
                    foreach($request->arr_item_id as $key => $row){
                        $batch = ProductionBatch::where('code',$request->arr_pallet_no[$key])->first();
                        if(!$batch){
                            $passedBatchCode = false;
                        }

                        $bomAlternative = BomAlternative::whereHas('bom',function($query)use($row){
                            $query->where('item_id',$row)->orderByDesc('created_at');
                        })->whereNotNull('is_default')->first();

                        if($bomAlternative){
                            foreach($bomAlternative->bomDetail()->where('lookable_type','items')->get() as $rowbom){
                                if(!in_array($rowbom->lookable_id,$arrItem)){
                                    $arrItem[] = $rowbom->lookable_id;
                                    $arrQty[] = round($rowbom->qty * (str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])) / $rowbom->bom->qty_output),3);
                                }else{
                                    $index = array_search($rowbom->lookable_id,$arrItem);
                                    $arrQty[$index] += round($rowbom->qty * (str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])) / $rowbom->bom->qty_output),3);
                                }
                            }

                            if($bomAlternative->bom->bomStandard()->exists()){
                                foreach($bomAlternative->bom->bomStandard->bomStandardDetail()->where('lookable_type','items')->get() as $rowbom){
                                    if(!in_array($rowbom->lookable_id,$arrItem)){
                                        $arrItem[] = $rowbom->lookable_id;
                                        $arrQty[] = round($rowbom->qty * str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])),3);
                                    }else{
                                        $index = array_search($rowbom->lookable_id,$arrItem);
                                        $arrQty[$index] += round($rowbom->qty * str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])),3);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach($arrItem as $key => $row){
                    $item = Item::find($row);
                    $itemstock = ItemCogs::where('item_id',$row)->where('place_id',$request->place_id)->whereDate('date','<=',$request->post_date)->orderByDesc('date')->orderByDesc('id')->first();
                    if($itemstock){
                        if($itemstock->qty_final < $arrQty[$key]){
                            $arrItemMore[] = $itemstock->item->code.' - '.$itemstock->item->name.' Stok : '.CustomHelper::formatConditionalQty($itemstock->qty_final).' Kebutuhan : '.CustomHelper::formatConditionalQty($arrQty[$key]);
                            $passedStockMaterial = false;
                        }
                    }else{
                        $arrItemMore[] = $item->code.' - '.$item->name.' Kebutuhan : '.CustomHelper::formatConditionalQty($arrQty[$key]);
                        $passedStockMaterial = false;
                    }
                }

                if(!$passedStockMaterial){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf terdapat permintaan item : '.implode(', ',$arrItemMore).' melebihi stok yang ada.'
                    ]);
                }

                if(!$passedBatchUsed){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf! '.implode(', ',$arrBatchError),
                    ]);
                }

                if(!$passedBatchCode){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf! batch tidak ditemukan di transaksi Produksi Barcode.',
                    ]);
                }
                
                $pod = ProductionOrderDetail::find($request->production_order_detail_id);

                if($request->temp){
                    $query = ProductionFgReceive::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Production Issue telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_fg_receives');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->production_order_detail_id = $request->production_order_detail_id;
                        $query->item_id = $pod->productionScheduleDetail->item_id;
                        $query->place_id = $request->place_id;
                        $query->shift_id = $request->shift_id;
                        $query->group = $request->group;
                        $query->line_id = $request->line_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->qty_reject = 0;
                        $query->status = '1';

                        $query->save();

                        foreach($query->productionBatchUsage as $rowdetail){
                            CustomHelper::updateProductionBatch($rowdetail->production_batch_id,$rowdetail->qty,'IN');
                            $rowdetail->delete();
                        }
                        
                        foreach($query->productionFgReceiveDetail as $row){
                            if($row->productionBatch()->exists()){
                                $row->productionBatch()->delete();
                            }
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Production Receive FG sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ProductionFgReceive::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionFgReceive::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'production_order_detail_id'=> $request->production_order_detail_id,
                        'item_id'                   => $pod->productionScheduleDetail->item_id,
                        'place_id'                  => $request->place_id,
                        'shift_id'                  => $request->shift_id,
                        'group'                     => $request->group,
                        'line_id'                   => $request->line_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_fg_receives') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                        'qty_reject'                => 0,
                    ]);
                }
                
                if($query) {
                    
                    $totalCost = 0;
                    $totalQty = 0;
                    $totalBatch = 0;
                    foreach($request->arr_qty_uom as $key => $row){
                        $totalQty += str_replace(',','.',str_replace('.','',$row));
                    }

                    foreach($request->arr_production_batch_id as $key => $row){
                        $pb = ProductionBatch::find($row);
                        if($pb){
                            $pbu = ProductionBatchUsage::create([
                                'production_batch_id'   => $pb->id,
                                'lookable_type'         => $query->getTable(),
                                'lookable_id'           => $query->id,
                                'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$key])),
                            ]);
                            CustomHelper::updateProductionBatch($pb->id,str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$key])),'OUT');
                            $totalCost += $pbu->productionBatch->totalById($pbu->id);
                            $totalBatch += str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$key]));
                        }
                    }

                    $qtyReject = $totalBatch - $totalQty;

                    $totalCostAll = $totalCost;
                    
                    foreach($request->arr_qty_uom as $key => $row){
                        $rowtotalbatch = round((str_replace(',','.',str_replace('.','',$row)) / $totalQty) * $totalCost,2);
                        $rowtotalbatch = $totalCostAll >= $rowtotalbatch ? $rowtotalbatch : $totalCostAll;
                        $rowtotalmaterial = 0;
                        $bom_id = NULL;

                        $bomAlternative = BomAlternative::whereHas('bom',function($query)use($request,$key){
                            $query->where('item_id',$request->arr_item_id[$key])->orderByDesc('created_at');
                        })->whereNotNull('is_default')->first();

                        if($bomAlternative){
                            foreach($bomAlternative->bomDetail as $rowbom){
                                if($rowbom->lookable_type == 'items'){
                                    $item = Item::find($rowbom->lookable_id);
                                    if($item){
                                        $price = $item->priceNowProduction($request->place_id,$request->post_date);
                                        $rowtotalmaterial += round(round($rowbom->qty * (str_replace(',','.',str_replace('.','',$row)) / $rowbom->bom->qty_output),3) * $price,2);
                                    }
                                }elseif($rowbom->lookable_type == 'resources'){
                                    $rowtotalmaterial += round(round($rowbom->qty * (str_replace(',','.',str_replace('.','',$row)) / $rowbom->bom->qty_output),3) * $rowbom->nominal,2);
                                }
                            }
                            $bom_id = $bomAlternative->bom_id;
                            if($bomAlternative->bom->bomStandard()->exists()){
                                foreach($bomAlternative->bom->bomStandard->bomStandardDetail as $rowbom){
                                    if($rowbom->lookable_type == 'items'){
                                        $item = Item::find($rowbom->lookable_id);
                                        if($item){
                                            $price = $item->priceNowProduction($request->place_id,$request->post_date);
                                            $rowtotalmaterial += round(round($rowbom->qty * str_replace(',','.',str_replace('.','',$row)),3) * $price,2);
                                        }
                                    }elseif($rowbom->lookable_type == 'resources'){
                                        $rowtotalmaterial += round(round($rowbom->qty * str_replace(',','.',str_replace('.','',$row)),3) * $rowbom->nominal,2);
                                    }
                                }
                            }
                        }

                        $rowtotal = $rowtotalbatch + $rowtotalmaterial;

                        $pfrd = ProductionFgReceiveDetail::create([
                            'production_fg_receive_id'  => $query->id,
                            'item_id'                   => $request->arr_item_id[$key],
                            'bom_id'                    => $bom_id ?? NULL,
                            'item_unit_id'              => $request->arr_item_unit_id[$key],
                            'pallet_no'                 => $request->arr_pallet_no[$key],
                            'shading'                   => $request->arr_shading[$key],
                            'qty_sell'                  => str_replace(',','.',str_replace('.','',$request->arr_qty_sell[$key])),
                            'qty'                       => str_replace(',','.',str_replace('.','',$request->arr_qty_uom[$key])),
                            'conversion'                => str_replace(',','.',str_replace('.','',$request->arr_qty_convert[$key])),
                            'pallet_id'                 => $request->arr_pallet_id[$key],
                            'grade_id'                  => $request->arr_grade_id[$key],
                            'total_batch'               => $rowtotalbatch,
                            'total_material'            => $rowtotalmaterial,
                            'total'                     => $rowtotal,
                        ]);

                        $batch = ProductionBatch::where('code',$request->arr_pallet_no[$key])->whereNull('lookable_type')->first();
                        if($batch){
                            $batch->update([
                                'lookable_type' => $pfrd->getTable(),
                                'lookable_id'   => $pfrd->id,
                                'total'         => $pfrd->total,
                                'post_date'     => $query->post_date,
                            ]);
                        }

                        $totalCostAll -= $rowtotalbatch;
                    }

                    if($qtyReject > 0){
                        $updateReject = ProductionFgReceive::find($query->id);
                        $updateReject->update([
                            'qty_reject'    => $qtyReject,
                        ]);
                    }
                    
                    CustomHelper::sendApproval($query->getTable(),$query->id,'Production Receive FG No. '.$query->code);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Receive FG No. '.$query->code,'Pengajuan Production Receive No. '.$query->code,session('bo_id'));

                    activity()
                        ->performedOn(new ProductionFgReceive())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit receive production fg.');

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

        $po = ProductionFgReceive::where('code',CustomHelper::decrypt($request->id))->first();

        $detail_batch = [];

        if($po->status == '5'){
            foreach($po->productionIssueWithVoid()->whereHas('productionIssueDetail',function($query){
                /* $query->whereHas('productionBatchUsage'); */
            })->get() as $row){
                foreach($row->productionIssueDetail()->where('lookable_type','items')->orderBy('id')->get() as $key => $rowdetail){
                    foreach($rowdetail->productionBatchUsage()->withTrashed()->get() as $rowbatch){
                        $detail_batch[] = [
                            'production_batch_id'   => $rowbatch->production_batch_id,
                            'production_batch_info' => $rowbatch->productionBatch->code.' - Qty : '.CustomHelper::formatConditionalQty($rowbatch->productionBatch->qtyByIdWithVoid($rowbatch->id)).' '.$rowbatch->productionBatch->item->uomUnit->code.' - Item : '.$rowbatch->productionBatch->lookable->item->code.' - '.$rowbatch->productionBatch->lookable->item->name,
                            'qty'                   => CustomHelper::formatConditionalQty($rowbatch->qty),
                            'qty_max'               => CustomHelper::formatConditionalQty($rowbatch->productionBatch->qty + $rowbatch->qty),
                            'unit'                  => $rowbatch->productionBatch->item->uomUnit->code,
                        ];
                    }
                }
            }
        }else{
            foreach($po->productionIssue()->whereHas('productionIssueDetail',function($query){
                $query->whereHas('productionBatchUsage');
            })->get() as $row){
                foreach($row->productionIssueDetail()->where('lookable_type','items')->orderBy('id')->get() as $key => $rowdetail){
                    foreach($rowdetail->productionBatchUsage()->get() as $rowbatch){
                        $qtyBeforeTransaction = $rowbatch->productionBatch->qtyById($rowbatch->id) + $rowbatch->qty;
                        $qtyGas = $qtyBeforeTransaction <= 0 ? $rowbatch->qty : $qtyBeforeTransaction;
                        $detail_batch[] = [
                            'production_batch_id'   => $rowbatch->production_batch_id,
                            'production_batch_info' => $rowbatch->productionBatch->code.' - Qty : '.CustomHelper::formatConditionalQty($qtyGas).' '.$rowbatch->productionBatch->item->uomUnit->code.' - Item : '.$rowbatch->productionBatch->lookable->item->code.' - '.$rowbatch->productionBatch->lookable->item->name,
                            'qty'                   => CustomHelper::formatConditionalQty($rowbatch->qty),
                            'qty_max'               => CustomHelper::formatConditionalQty($rowbatch->productionBatch->qty + $rowbatch->qty),
                            'unit'                  => $rowbatch->productionBatch->item->uomUnit->code,
                        ];
                    }
                }
            }
        }
        

        foreach($po->productionFgReceiveDetail()->orderBy('id')->get() as $key => $row){
            $detail_receive[] = [
                'item_id'               => $row->item_id,
                'item_code'             => $row->item->code,
                'item_name'             => $row->item->name,
                'unit'                  => $row->item->uomUnit->code,
                'item_unit_id'          => $row->item_unit_id,
                'sell_unit'             => $row->itemUnit->unit->code,
                'code'                  => $row->pallet_no,
                'pallet_id'             => $row->pallet_id,
                'pallet_code'           => $row->pallet->code,
                'grade_id'              => $row->grade_id,
                'grade_code'            => $row->grade->code,
                'shading'               => $row->shading,
                'qty_sell'              => CustomHelper::formatConditionalQty($row->qty_sell),
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'conversion'            => CustomHelper::formatConditionalQty($row->conversion),
                'place'                 => $row->productionFgReceive->place->code,
                'shift'                 => $row->productionFgReceive->shift->code,
                'group'                 => $row->productionFgReceive->group,
            ];
        }

        $po['code_place_id']                    = substr($po->code,7,2);
        $po['item_parent_name']                 = $po->item->code.' - '.$po->item->name;
        $po['unit']                             = $po->item->uomUnit->code;
        $po['production_order_detail_code']     = $po->productionOrderDetail->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($po->productionOrderDetail->productionOrder->post_date)).' - Plant : '.$po->productionOrderDetail->productionScheduleDetail->productionSchedule->place->code;
        $po['table']                            = $po->productionOrderDetail->getTable();
        $po['po_code']                          = $po->productionOrderDetail->code;
        $po['details']                          = $detail_receive;
        $po['batches']                          = $detail_batch;
        $po['shift_name']                       = $po->shift->code.' - '.$po->shift->name;
        
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionFGReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Production Receive FG',
                'data'      => $pr
            ];

            return view('admin.approval.production_fg_receive', $data);
        }else{
            abort(404);
        }
    }

    public function getAccountData(Request $request){
        $account = User::find($request->id);
        $response = [];
        $data = ProductionOrderDetail::where(function($query){  
            
        })
        ->whereHas('productionOrder',function($query){
            $query->whereDoesntHave('used')
                ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
                ->whereIn('status',['2']);
        })
        ->whereHas('productionScheduleDetail',function($query){
            $query->whereHas('item',function($query){
                $query->whereNotNull('is_sales_item');
            });
        })
        ->get();

        foreach($data as $d) {
            if($d->productionScheduleDetail->item->sellUnit()){
                $response[] = [
                    'id'        => $d->id,
                    'code'      => $d->productionOrder->code,
                    'user'      => $d->productionOrder->user->name,
                    'post_date' => $d->productionOrder->post_date,
                    'item_receive_name'=> $d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name,
                    'text' 	    => $d->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($d->productionOrder->post_date)).' - Plant : '.$d->productionScheduleDetail->productionSchedule->place->code.' ( '.$d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name.' )',
                    'item_name' => $d->productionScheduleDetail->item->code.' - '.$d->productionScheduleDetail->item->name,
                    'qty'       => CustomHelper::formatConditionalQty($d->qtyReceiveFg()),
                    'uom_unit'  => $d->productionScheduleDetail->item->uomUnit->code, 
                    'sell_unit' => $d->productionScheduleDetail->item->sellUnit(),
                    'note1'      => $d->productionOrder->note,
                    'status'    => $d->productionOrder->statusRaw(),
                    'conversion'=> CustomHelper::formatConditionalQty($d->productionScheduleDetail->item->sellConversion()),
                ];
            }
        }
       

        $account['details'] = $response;

        return response()->json($account);
    }


    public function rowDetail(Request $request)
    {
        $data   = ProductionFgReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="11" style="font-size:20px !important;">Daftar Item Receive</th>
                            </tr>
                            <tr>
                                <th class="center">'.__('translations.no').'</th>
                                <th class="center">'.__('translations.item').'</th>
                                <th class="center">No.Batch/Palet</th>
                                <th class="center">Shading</th>
                                <th class="center">Qty Diterima</th>
                                <th class="center">Satuan</th>
                                <th class="center">Konversi</th>
                                <th class="center">Qty Produksi</th>
                                <th class="center">Satuan</th>
                                <th class="center">Palet</th>
                                <th class="center">Grade</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->productionFgReceiveDetail()->orderBy('id')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'</td>
                <td>'.$row->item->code.' - '.$row->item->name.'</td>
                <td>'.$row->pallet_no.'</td>
                <td>'.$row->shading.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty_sell).'</td>
                <td class="center-align">'.$row->itemUnit->unit->code.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->conversion).'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                <td class="">'.$row->pallet->code.'</td>
                <td class="">'.$row->grade->code.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col m6 s12 mt-1"><table style="min-width:100%;"><thead>
                    <tr>
                        <th colspan="5" class="center-align">Daftar Batch Terpakai</th>
                    </tr>
                    <tr>
                        <th class="center">'.__('translations.no').'.</th>
                        <th class="center">No.Batch</th>
                        <th class="center">Item</th>
                        <th class="center">Qty</th>
                        <th class="center">Satuan</th>
                    </tr>
                </thead><tbody>';

        foreach($data->productionBatchUsage as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'</td>
                <td>'.$row->productionBatch->code.'</td>
                <td>'.$row->productionBatch->item->code.' - '.$row->productionBatch->item->name.'</td>
                <td>'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->productionBatch->item->uomUnit->code.'</td>
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
        
        $pr = ProductionFgReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Production Receive FG','a4','portrait','admin.print.production.receive_fg_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);$var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function printBarcode(Request $request,$id){
        
        $pr = ProductionFgReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Production Receive FG',array(0,0,264.57,188.98),'portrait','admin.print.production.receive_fg_barcode');
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);$var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = ProductionFgReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }
            foreach($query->productionFgReceiveDetail as $row){
                if($row->productionBatch->productionBatchUsage()->exists()){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf, nomor batch telah digunakan pada dokumen lainnya.'
                    ]);
                }
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
                $tempStatus = $query->status;

                foreach($query->productionBatchUsage as $rowdetail){
                    CustomHelper::updateProductionBatch($rowdetail->production_batch_id,$rowdetail->qty,'IN');
                    $rowdetail->delete();
                }
                
                foreach($query->productionFgReceiveDetail as $row){
                    if(!$row->productionBarcodeDetail()->exists()){
                        if($row->productionBatch()->exists()){
                            $row->productionBatch()->delete();
                        }
                    }else{
                        $row->productionBatch->update([
                            'lookable_type' => NULL,
                            'lookable_id'   => NULL,
                            'total'         => 0,
                            'post_date'     => NULL,
                        ]);
                    }
                    if($row->productionBarcodeDetail()->exists()){
                        if($row->productionBarcodeDetail->productionBarcode->alreadyReceived()){
                            $row->productionBarcodeDetail->productionBarcode->update([
                                'status'	=> '2'
                            ]);
                        }
                    }
                    /* $row->delete(); */
                }

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

                $query->voidProductionIssue();

                activity()
                    ->performedOn(new ProductionFgReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the production receive fg data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Production Receive FG No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
        $query = ProductionFgReceive::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->productionFgReceiveDetail as $row){
                if($row->productionBatch()->exists()){
                    $row->productionBatch()->delete();
                }
            }
            if($query->productionBatchUsage()->exists()){
                foreach($query->productionBatchUsage as $rowdetail){
                    CustomHelper::updateProductionBatch($rowdetail->production_batch_id,$rowdetail->qty,'IN');
                    $rowdetail->delete();
                }
            }

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionFgReceive())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the production receive fg data');

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
                    $pdf = PrintHelper::print($pr,'Production Receive','a4','portrait','admin.print.production.receive_individual');
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
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.receive_individual');
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
                            $pdf = PrintHelper::print($query,'Production Receive','a4','portrait','admin.print.production.receive_individual');
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
        $query = ProductionFgReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
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

    public function sendUsedData(Request $request){
        $mop = ProductionBatch::find($request->id);
       
        if(!$mop->used()->exists()){
            CustomHelper::sendUsedData($request->type,$request->id,'Form Production Receive FG');
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
        $query = ProductionFgReceive::where('code',CustomHelper::decrypt($id))->first();
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
        $menu = Menu::where('url','production_fg_receive')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $modedata = $menuUser->mode ?? '';
        $nominal = $menuUser->show_nominal ?? '';
		return Excel::download(new ExportProductionFgReceive($post_date,$end_date,$mode,$modedata,$nominal), 'production_fg_receive'.uniqid().'.xlsx');
    }

}