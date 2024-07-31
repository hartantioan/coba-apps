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
use App\Models\Area;
use App\Models\Bom;
use App\Models\BomAlternative;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Line;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchUsage;
use App\Models\ProductionIssue;
use App\Models\ProductionReceive;
use App\Models\ProductionReceiveDetail;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\ProductionReceiveIssue;
use App\Models\Shift;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UsedData;
use App\Models\MenuUser;
use App\Exports\ExportProductionReceive;
class ProductionReceiveController extends Controller
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
            'title'         => 'Receive',
            'content'       => 'admin.production.receive',
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
        $code = ProductionReceive::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getBatchCode(Request $request){
        $pod = ProductionOrderDetail::find($request->pod_id);
        $type = $pod->productionScheduleDetail->bom->is_powder ? 'powder' : 'normal';
        $shift = Shift::find($request->shift_id);
        $code = ProductionBatch::generateCodeWithNumber($type,$shift->code,$request->group,$request->number);
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

        $total_data = ProductionReceive::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionReceive::where(function($query) use ($search, $request) {
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

        $total_filtered = ProductionReceive::where(function($query) use ($search, $request) {
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
                    $val->productionOrderDetail->productionScheduleDetail->productionSchedule->code,
                    $val->shift->code.' - '.$val->shift->name,
                    date('d/m/Y H:i',strtotime($val->start_process_time)),
                    date('d/m/Y H:i',strtotime($val->end_process_time)),
                    $val->line->code,
                    $val->group,
                    $val->place->code,
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
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
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
        DB::beginTransaction();
        try {
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
                'start_process_time'        => 'required',
                'end_process_time'          => 'required',
            ], [
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'place_id'                          => 'Plant tidak boleh kosong.',
                'shift_id'                          => 'Shift tidak boleh kosong.',
                'group'                             => 'Grup tidak boleh kosong.',
                'line_id'                           => 'Line tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
                'production_order_detail_id.required'=> 'Production Order tidak boleh kosong.',
                'start_process_time.required'       => 'Waktu mulai produksi tidak boleh kosong.',
                'end_process_time.required'         => 'Waktu selesai produksi tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                
                $arrItemReject = [];
                if($request->arr_qty_reject){
                    $passedReject = true;
                    foreach($request->arr_qty_reject as $key => $row){
                        $bom = Bom::find($request->arr_bom_id[$key]);
                        if($bom){
                            if(str_replace(',','.',str_replace('.','',$row)) > 0){
                                if(!$bom->itemReject()->exists()){
                                    $passedReject = false;
                                    $arrItemReject[] = NULL;
                                }else{
                                    $arrItemReject[] = $bom->item_reject_id;
                                }
                            }else{
                                $arrItemReject[] = NULL;
                            }
                        }
                    }
                    if(!$passedReject){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Terdapat qty item reject diatas 0 yang belum memiliki item reject pada bom.'
                        ]);
                    }
                }

                $passedIssue = true;

                $datapod = ProductionOrder::find($request->production_order_id);
                
                if($datapod){
                    if(!$datapod->productionIssue()->exists()){
                        $passedIssue = false;
                    }

                    if(!$passedIssue){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Production order no. '.$datapod->code.' belum memiliki data Issue.'
                        ]);
                    }
                }

                $arrItemError = [];

                foreach($request->arr_item_id as $key => $row){
                    $bomAlternative = BomAlternative::whereHas('bom',function($query)use($row){
                        $query->where('item_id',$row)->orderByDesc('created_at');
                    })->whereNotNull('is_default')->first();

                    if($bomAlternative){
                        foreach($bomAlternative->bomDetail()->where('issue_method','2')->get() as $rowbom){
                            if($rowbom->lookable_type == 'items'){
                                $item = Item::find($rowbom->lookable_id);
                                $qty = round($rowbom->qty * (str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) / $rowbom->bom->qty_output),3);
                                if($item){
                                    if($item->productionBatchMoreThanZero()->exists()){
                                        $totalstock = 0;
                                        foreach($item->productionBatchMoreThanZero()->orderBy('created_at')->get() as $rowbatch){
                                            $totalstock += $rowbatch->qty;
                                        }
                                        if($qty > $totalstock){
                                            $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' stok '.CustomHelper::formatConditionalQty($totalstock).' sedangkan kebutuhan '.CustomHelper::formatConditionalQty($qty);
                                        }
                                    }else{
                                        $itemstock = NULL;
                                        $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$request->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                                        if($itemstock){
                                            if($itemstock->qty < $qty){
                                                $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' stok '.CustomHelper::formatConditionalQty($itemstock->qty).' sedangkan kebutuhan '.CustomHelper::formatConditionalQty($qty);
                                            }
                                        }else{
                                            $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' data stok tidak ditemukan.';
                                        }
                                    }
                                }
                            }
                        }

                        if($bomAlternative->bom->bomStandard()->exists()){
                            foreach($bomAlternative->bom->bomStandard->bomStandardDetail as $rowbom){
                                if($rowbom->lookable_type == 'items'){
                                    $item = Item::find($rowbom->lookable_id);
                                    $qty = round($rowbom->qty * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),3);
                                    if($item){
                                        if($item->productionBatchMoreThanZero()->exists()){
                                            $totalstock = 0;
                                            foreach($item->productionBatchMoreThanZero()->orderBy('created_at')->get() as $rowbatch){
                                                $totalstock += $rowbatch->qty;
                                            }
                                            if($qty > $totalstock){
                                                $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' stok '.CustomHelper::formatConditionalQty($totalstock).' sedangkan kebutuhan '.CustomHelper::formatConditionalQty($qty);
                                            }
                                        }else{
                                            $itemstock = NULL;
                                            $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$request->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                                            if($itemstock){
                                                if($itemstock->qty < $qty){
                                                    $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' stok '.CustomHelper::formatConditionalQty($itemstock->qty).' sedangkan kebutuhan '.CustomHelper::formatConditionalQty($qty);
                                                }
                                            }else{
                                                $arrItemError[] = 'Item : '.$item->code.' - '.$item->name.' data stok tidak ditemukan.';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if(count($arrItemError)){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode(', ',$arrItemError),
                    ]);
                }
                
                if($request->temp){
                    $query = ProductionReceive::where('code',CustomHelper::decrypt($request->temp))->first();

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

                    if(in_array($query->status,['1','2','5','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_receives');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->production_order_detail_id = $request->production_order_detail_id;
                        $query->place_id = $request->place_id;
                        $query->shift_id = $request->shift_id;
                        $query->group = $request->group;
                        $query->line_id = $request->line_id;
                        $query->post_date = $request->post_date;
                        $query->start_process_time = $request->start_process_time;
                        $query->end_process_time = $request->end_process_time;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionReceiveDetail as $row){
                            if($row->productionBatch()->exists()){
                                $row->productionBatch()->delete();
                            }
                            $row->delete();
                        }

                        $query->productionReceiveIssue()->delete();
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Production Receive sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ProductionReceive::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionReceive::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'production_order_detail_id'=> $request->production_order_detail_id,
                        'place_id'                  => $request->place_id,
                        'shift_id'                  => $request->shift_id,
                        'group'                     => $request->group,
                        'line_id'                   => $request->line_id,
                        'post_date'                 => $request->post_date,
                        'start_process_time'        => $request->start_process_time,
                        'end_process_time'          => $request->end_process_time,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_issues') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {

                    if($request->arr_production_issue_id){
                        foreach($request->arr_production_issue_id as $key => $row){
                            ProductionReceiveIssue::create([
                                'production_receive_id' => $query->id,
                                'production_issue_id'   => $row,
                            ]);
                        }
                    }

                    $totalQty = 0;
                    foreach($request->arr_qty as $key => $row){
                        $totalQty += str_replace(',','.',str_replace('.','',$row));
                    }

                    foreach($request->arr_qty as $key => $row){
                        $querydetail = ProductionReceiveDetail::create([
                            'production_receive_id'         => $query->id,
                            'production_order_detail_id'    => $request->arr_production_order_detail_id[$key] ?? NULL,
                            'item_id'                       => $request->arr_item_id[$key],
                            'bom_id'                        => $request->arr_bom_id[$key],
                            'item_reject_id'                => $arrItemReject[$key], 
                            'is_powder'                     => $request->arr_is_powder[$key] == '0' ? NULL : $request->arr_is_powder[$key],
                            'qty'                           => str_replace(',','.',str_replace('.','',$row)),
                            'qty_planned'                   => str_replace(',','.',str_replace('.','',$request->arr_qty_bom[$key])),
                            'qty_reject'                    => str_replace(',','.',str_replace('.','',$request->arr_qty_reject[$key])),
                            'place_id'                      => $request->arr_place[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                            'total'                         => 0,
                        ]);
                        $type = $querydetail->is_powder ? 'powder' : 'normal';
                        if($request->arr_count_detail){
                            foreach($request->arr_count_detail as $keydetail => $rowdetail){
                                if($request->arr_count_header[$key] == $rowdetail){
                                    $cekBatch = ProductionBatch::where('code',$request->arr_batch_no[$keydetail])->first();
                                    $code_batch = $cekBatch ? ProductionBatch::generateCode($type,$query->shift->code,$query->group) : $request->arr_batch_no[$keydetail];
                                    ProductionBatch::create([
                                        'code'          => $code_batch,
                                        'item_id'       => $querydetail->item_id,
                                        'place_id'      => $request->arr_place[$key],
                                        'warehouse_id'  => $request->arr_warehouse[$key],
                                        'lookable_type' => $querydetail->getTable(),
                                        'lookable_id'   => $querydetail->id,
                                        'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$keydetail])),
                                        'qty_real'      => str_replace(',','.',str_replace('.','',$request->arr_qty_batch[$keydetail])),
                                        'total'         => 0
                                    ]);
                                }
                            }
                        }
                    }

                    $queryupdate = ProductionReceive::find($query->id);
                    
                    $queryupdate->createProductionIssue();

                    $totalIssue = 0;
                    foreach($queryupdate->productionReceiveIssue as $key => $row){
                        $totalIssue += $row->productionIssue->total();
                    }

                    foreach($queryupdate->productionReceiveDetail as $row){
                        $rowtotal = round(($row->qty / $totalQty) * $totalIssue,2);
                        foreach($row->productionBatch as $rowbatch){
                            $totalbatch = round(($rowbatch->qty_real / $row->qty) * $rowtotal,2);
                            $rowbatch->update([
                                'total' => $totalbatch,
                            ]);
                        }
                        $row->update([
                            'total' => $rowtotal,
                        ]);
                    }
                    
                    CustomHelper::sendApproval($query->getTable(),$query->id,'Production Receive No. '.$query->code);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Receive No. '.$query->code,'Pengajuan Production Receive No. '.$query->code,session('bo_id'));

                    activity()
                        ->performedOn(new ProductionReceive())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit receive production.');

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
        $detail_receive = [];

        $po = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();

        $issue = [];

        foreach($po->productionReceiveIssue as $rowissue){
            $issue[] = [
                'production_issue_id'   => $rowissue->production_issue_id,
                'production_issue_name' => $rowissue->productionIssue->code.' Tgl. '.date('d/m/Y',strtotime($rowissue->productionIssue->post_date)).' Shift '.$rowissue->productionIssue->shift->code.' Group '.$rowissue->productionIssue->group
            ];
        }

        foreach($po->productionReceiveDetail()->orderBy('id')->get() as $key => $row){
            $batch = [];

            foreach($row->productionBatch as $rowbatch){
                $batch[] = [
                    'batch_no'  => $rowbatch->code,
                    'qty'       => CustomHelper::formatConditionalQty($rowbatch->qty_real),
                ];
            }

            $detail_receive[] = [
                'id'                    => $row->production_order_detail_id,
                'bom_id'                => $row->bom_id ?? '',
                'group_bom'             => $row->bom()->exists() ? $row->bom->group : '',
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'unit'                  => $row->item->uomUnit->code,
                'qty_planned'           => CustomHelper::formatConditionalQty($row->qty_planned),
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'qty_reject'            => CustomHelper::formatConditionalQty($row->qty_reject),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
                'list_warehouse'        => $row->item->warehouseList(),
                'is_powder'             => $row->is_powder ?? '0',
                'list_batch'            => $batch,
            ];
        }

        $po['code_place_id']                    = substr($po->code,7,2);
        $po['production_order_detail_code']     = $po->productionOrderDetail->productionOrder->code.' Tgl.Post '.date('d/m/Y',strtotime($po->productionOrderDetail->productionOrder->post_date)).' - Plant : '.$po->productionOrderDetail->productionScheduleDetail->productionSchedule->place->code;
        $po['table']                            = $po->productionOrderDetail->getTable();
        $po['po_code']                          = $po->productionOrderDetail->productionOrder->code;
        $po['details']                          = $detail_receive;
        $po['shift_name']                       = $po->shift->code.' - '.$po->shift->name;
        $po['issues']                           = $issue;
        $po['bom_group']                        = strtoupper($po->productionOrderDetail->productionScheduleDetail->bom->group());
        $po['group_bom']                        = $po->productionOrderDetail->productionScheduleDetail->bom->group;
        
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Production Issue',
                'data'      => $pr
            ];

            return view('admin.approval.production_receive', $data);
        }else{
            abort(404);
        }
    }


    public function rowDetail(Request $request)
    {
        $data   = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9" style="font-size:20px !important;">Daftar Item Receive</th>
                            </tr>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Item</th>
                                <th class="center">Qty Planned</th>
                                <th class="center">Qty Real</th>
                                <th class="center">Qty Reject</th>
                                <th class="center">Satuan Produksi</th>
                                <th class="center">Plant</th>
                                <th class="center">Gudang</th>
                                <th class="center">No.Batch</th>
                            </tr>
                        </thead><tbody>';
        $totalqtyplanned=0;
        $totalqtyreal=0;
        $totalqtyreject=0;
        foreach($data->productionReceiveDetail()->orderBy('id')->get() as $key => $row){
            $totalqtyplanned+=$row->qty_planned;
            $totalqtyreal+=$row->qty;
            $totalqtyreject+=$row->qty_reject;
            $batch = '<ol>';

            foreach($row->productionBatch as $rowbatch){
                $batch .= '<li>No. '.$rowbatch->code.' Qty : '.CustomHelper::formatConditionalQty($rowbatch->qty_real).'</li>';
            }

            $batch .= '</ol>';
            $string .= '<tr>
                <td class="center-align">'.($key+1).'.</td>
                <td>'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty_planned).'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty_reject).'</td>
                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                <td class="">'.$row->place->code.'</td>
                <td class="">'.$row->warehouse->name.'</td>
                <td class="">'.$batch.'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqtyplanned, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqtyreal, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqtyreject, 3, ',', '.') . '</td>
                <td colspan="4"></td>
            </tr>  
        ';

        $string .= '</tbody></table></div><div class="col s12 mt-2"><table style="width:350px !important;" class="bordered" id="table-detail-row">
        <thead>
            <tr>
                <th class="center-align" colspan="2" style="font-size:20px !important;">Daftar Production Issue Terpakai</th>
            </tr>
            <tr>
                <th class="center">No.</th>
                <th class="center">No. Production Issue</th>
            </tr>
        </thead><tbody>';

        foreach($data->productionReceiveIssue as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'.</td>
                <td>'.$row->productionIssue->code.'</td>
            </tr>';
        }

        $string .= '</tbody></table></div><div class="col s12 mt-1"><table style="min-width:100%;">
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
        
        $pr = ProductionReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Production Receive',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $options = PDF::getDomPDF()->getOptions();
            $options->set('isFontSubsettingEnabled', true);
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
          
            $pdf = Pdf::loadView('admin.print.production.receive_individual', $data)->setPaper('a4','portrait');
            $pdf->render();
    
   
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}",'', 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }
            foreach($query->productionReceiveDetail as $row){
                foreach($row->productionBatch as $rowbatch){
                    if($rowbatch->productionBatchUsage()->exists()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Mohon maaf, nomor batch telah digunakan pada dokumen lainnya.'
                        ]);
                    }
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
                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                    foreach($query->productionReceiveIssue as $row){
                        if($row->productionIssue->status == '3'){
                            $row->productionIssue->update([
                                'status'	=> '2'
                            ]);
                        }
                    }
                }

                foreach($query->productionReceiveDetail as $row){
                    if($row->productionBatch()->exists()){
                        $row->productionBatch()->delete();
                    }
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                $query->voidProductionIssue();

                activity()
                    ->performedOn(new ProductionReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the production receive data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Production Receive No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
        $query = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->productionReceiveDetail as $row){
                if($row->productionBatch()->exists()){
                    $row->productionBatch()->delete();
                }
                $row->delete();
            }

            $query->productionReceiveIssue()->delete();

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionReceive())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the production receive data');

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
        $query = ProductionReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                'url'=>request()->root()."/admin/production/production_receive?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_core;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_receive',$query->id);
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
        $mop = ProductionOrder::find($request->id);
       
        if(!$mop->used()->exists()){
            CustomHelper::sendUsedData($request->type,$request->id,'Form Production Receive');
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
        $query = ProductionReceive::where('code',CustomHelper::decrypt($id))->first();
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
		return Excel::download(new ExportProductionReceive($post_date,$end_date,$mode), 'production_receive'.uniqid().'.xlsx');
    }
}