<?php

namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\IncomingPayment;
use App\Models\MarketingOrderPlan;
use App\Models\ProductionSchedule;
use App\Models\ProductionIssueReceive;
use App\Models\ProductionIssueReceiveDetail;
use App\Models\Menu;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;

use App\Models\MarketingOrderPlanDetail;
use App\Models\Place;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\BomDetail;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
class ProductionIssueReceiveController extends Controller
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
            'title'         => 'Issue Receive',
            'content'       => 'admin.production.issue_receive',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       =>  $menu->document_code.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = ProductionIssueReceive::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ProductionIssueReceive::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionIssueReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ProductionIssueReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
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
                    date('d/m/y',strtotime($val->post_date)),
                    $val->productionOrder->code,
                    $val->productionOrder->productionSchedule->code,
                    date('d/m/y',strtotime($val->productionOrder->productionScheduleDetail->production_date)).' - '.$val->productionOrder->productionScheduleDetail->shift->code.' - '.$val->productionOrder->productionScheduleDetail->shift->name,
                    $val->productionOrder->productionScheduleDetail->line->code,
                    $val->productionOrder->productionScheduleDetail->group,
                    $val->productionOrder->productionSchedule->place->code,
                    $val->productionOrder->warehouse->name,
                    $val->productionOrder->area()->exists() ? $val->productionOrder->area->name : '-',
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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
        
        $validation = Validator::make($request->all(), [
            'code'			            => $request->temp ? ['required', Rule::unique('production_issue_receives', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:production_issue_receives,code',
            'code_place_id'             => 'required',
            'company_id'			    => 'required',
            'post_date'		            => 'required',
            'production_order_id'       => 'required',
            'arr_type'                  => 'required|array',
            'arr_lookable_type'         => 'required|array',
            'arr_lookable_id'           => 'required|array',
            'arr_production_detail_id'  => 'required|array',
            'arr_bom_detail_id'         => 'required|array',
            'arr_nominal'               => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_shading'               => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_batch'                 => 'required|array',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai',
            'code_place_id.required' 			=> 'Kode plant tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'production_order_id.required'      => 'Order Produksi tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe harus array.',
            'arr_lookable_type.required'        => 'Coa/item tidak boleh kosong.',
            'arr_lookable_type.array'           => 'Coa/item harus array.',
            'arr_lookable_id.required'          => 'Coa/item tidak boleh kosong.',
            'arr_lookable_id.array'             => 'Coa/item harus array.',
            'arr_production_detail_id.required' => 'Detail produk tidak boleh kosong.',
            'arr_production_detail_id.array'    => 'Detail produk harus array.',
            'arr_bom_detail_id.required'        => 'Bom tidak boleh kosong.',
            'arr_bom_detail_id.array'           => 'Bom harus array.',
            'arr_nominal.required'              => 'Qty/harga tidak boleh kosong.',
            'arr_nominal.array'                 => 'Qty/harga harus array.',
            'arr_total.required'                => 'Total tidak boleh kosong.',
            'arr_total.array'                   => 'Total harus array.',
            'arr_shading.required'              => 'Shading tidak boleh kosong.',
            'arr_shading.array'                 => 'Shading harus array.',
            'arr_batch.required'                => 'No batch tidak boleh kosong.',
            'arr_batch.array'                   => 'No batch harus array.',
            'arr_qty.required'                  => 'Qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty harus array.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = ProductionIssueReceive::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Issue Receive telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_issue_receives');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->production_order_id = $request->production_order_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionIssueReceiveDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Issue Receive sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ProductionIssueReceive::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'production_order_id'       => $request->production_order_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_issue_receives') : NULL,
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
                    $totalActualItemCost = 0;
                    $totalActualResourceCost = 0;
                    $totalProductCost = 0;
                    $totalCompletedQty = 0;
                    foreach($request->arr_type as $key => $row){
                        if($row == '2'){
                            $totalCompletedQty += str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                        }
                        $bomId = 0;
                        if($request->arr_bom_detail_id[$key]){
                            $bomDetail = BomDetail::find(intval($request->arr_bom_detail_id[$key]));
                            if($bomDetail){
                                $bomId = $bomDetail->bom_id;
                            }
                        }
                        $priceStockItem = 0;
                        $totalStockItem = 0;
                        if($row == '1' && $request->arr_lookable_type[$key] == 'items'){
                            $item = Item::find(intval($request->arr_lookable_id[$key]));
                            if($item){
                                $priceStockItem = $item->priceNowProduction($query->productionOrder->productionSchedule->place_id,$query->post_date);
                                $totalStockItem = $priceStockItem * str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                                $totalActualItemCost += $totalStockItem;
                            }
                        }
                        if($row == '1' && $request->arr_lookable_type[$key] == 'coas'){
                            $totalActualResourceCost += str_replace(',','.',str_replace('.','',$request->arr_total[$key]));
                        }
                        if($request->arr_production_detail_id[$key]){
                            $pod = ProductionOrderDetail::find(intval($request->arr_production_detail_id[$key]));
                            if($pod){
                                $pod->update([
                                    'qty_real'      => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                    'nominal_real'  => $priceStockItem > 0 ? $totalStockItem : str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                    'total_real'    => $priceStockItem > 0 ? $totalStockItem : str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                                ]);
                            }
                        }
                        ProductionIssueReceiveDetail::create([
                            'production_issue_receive_id'   => $query->id,
                            'production_order_detail_id'    => $request->arr_production_detail_id[$key] ? $request->arr_production_detail_id[$key] : NULL,
                            'lookable_type'                 => $request->arr_lookable_type[$key],
                            'lookable_id'                   => $request->arr_lookable_id[$key],
                            'shading'                       => $request->arr_shading[$key] ? $request->arr_shading[$key] : NULL,
                            'bom_id'                        => $bomId > 0 ? $bomId : NULL,
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'nominal'                       => $priceStockItem > 0 ? round($priceStockItem,2) : str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                            'total'                         => $totalStockItem > 0 ? round($totalStockItem,2) : str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'type'                          => $row,
                            'from_item_stock_id'            => $request->arr_item_stock_id[$key] ? $request->arr_item_stock_id[$key] : NULL,
                            'batch_no'                      => $request->arr_batch[$key],
                        ]);
                    }

                    $totalProductCost = $totalActualItemCost + $totalActualResourceCost;

                    $query->productionOrder->update([
                        'actual_item_cost'      => $totalActualItemCost,
                        'actual_resource_cost'  => $totalActualResourceCost,
                        'total_product_cost'    => $totalProductCost,
                        'completed_qty'         => $totalCompletedQty,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,'Issue Receive No. '.$query->code);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Issue Receive No. '.$query->code,'Pengajuan Issue Receive No. '.$query->code,session('bo_id'));

                activity()
                    ->performedOn(new ProductionIssueReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit issue receive production.');

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
        $detail_issue = [];

        $po = ProductionIssueReceive::where('code',CustomHelper::decrypt($request->id))->first();

        foreach($po->productionIssueReceiveDetail()->where('type','1')->get() as $row){
            $detail_issue[] = [
                'id'                    => $row->production_order_detail_id,
                'bom_detail_id'         => $row->productionOrderDetail->bom_detail_id,
                'lookable_type'         => $row->lookable_type,
                'lookable_id'           => $row->lookable_id,
                'lookable_code'         => $row->lookable->code,
                'lookable_name'         => $row->lookable->name,
                'lookable_unit'         => $row->item()->exists() ? $row->item->productionUnit->code : '-',
                'list_stock'            => $row->item()->exists() ? $row->item->currentStockPerPlace($po->productionOrder->productionSchedule->place_id) : [],
                'qty'                   => number_format($row->qty,3,',','.'),
                'nominal'               => number_format($row->nominal,2,',','.'),
                'total'                 => number_format($row->total,2,',','.'),
                'qty_standard'          => number_format($row->productionOrderDetail->qty,3,',','.'),
                'nominal_standard'      => number_format($row->productionOrderDetail->nominal,3,',','.'),
                'total_standard'        => number_format($row->productionOrderDetail->total,3,',','.'),
                'item_stock_id'         => $row->from_item_stock_id,
                'shading'               => $row->shading,
                'batch_no'              => $row->batch_no,
                'type'                  => $row->type,
            ];
        }

        $detailReceive = $po->productionIssueReceiveDetail()->where('type','2')->first();

        $po['code_place_id'] = substr($po->code,7,2);
        $po['production_order_code'] = $po->productionOrder->code.' Tgl.Post '.date('d/m/y',strtotime($po->productionOrder->post_date)).' - Plant : '.$po->productionOrder->productionSchedule->place->code;
        $po['item_receive_id']                  = $po->productionOrder->productionScheduleDetail->item_id;
        $po['item_receive_code']                = $po->productionOrder->productionScheduleDetail->item->code;
        $po['item_receive_name']                = $po->productionOrder->productionScheduleDetail->item->name;
        $po['item_receive_unit_production']     = $po->productionOrder->productionScheduleDetail->item->productionUnit->code;
        $po['item_receive_unit_uom']            = $po->productionOrder->productionScheduleDetail->item->uomUnit->code;
        $po['item_receive_unit_sell']           = $po->productionOrder->productionScheduleDetail->item->sellUnit->code;
        $po['item_receive_unit_pallet']         = $po->productionOrder->productionScheduleDetail->item->palletUnit->code;
        $po['item_receive_qty']                 = number_format($po->productionOrder->productionScheduleDetail->qty,3,',','.');
        $po['qty']                              = number_format($detailReceive->qty,3,',','.');
        $po['production_convert']               = $po->productionOrder->productionScheduleDetail->item->production_convert;
        $po['sell_convert']                     = $po->productionOrder->productionScheduleDetail->item->sell_convert;
        $po['pallet_convert']                   = $po->productionOrder->productionScheduleDetail->item->pallet_convert;
        $po['detail_issue']                     = $detail_issue;
        $po['shift']                            = date('d/m/y',strtotime($po->productionOrder->productionScheduleDetail->production_date)).' - '.$po->productionOrder->productionScheduleDetail->shift->code.' - '.$po->productionOrder->productionScheduleDetail->shift->name;
        $po['group']                            = $po->productionOrder->productionScheduleDetail->group;
        $po['line']                             = $po->productionOrder->productionScheduleDetail->line->code;
        $po['shading']                          = $detailReceive->shading;
        $po['batch_no']                         = $detailReceive->batch_no;
        
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionIssueReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Issue Receive',
                'data'      => $pr
            ];

            return view('admin.approval.issue_receive', $data);
        }else{
            abort(404);
        }
    }


    public function rowDetail(Request $request)
    {
        $data   = ProductionIssueReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="12" style="font-size:20px !important;">Daftar Item/Coa Issue (Terpakai)</th>
                            </tr>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Item/Coa</th>
                                <th class="center">Qty Planned</th>
                                <th class="center">Qty Real</th>
                                <th class="center">Satuan Produksi</th>
                                <th class="center">Plant & Gudang</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->productionIssueReceiveDetail()->where('type','1')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'.</td>
                <td>'.($row->item()->exists() ? $row->item->code.' - '.$row->item->name : $row->coa->code.' - '.$row->coa->name).'</td>
                <td class="right-align">'.($row->item()->exists() ? number_format($row->productionOrderDetail->qty,3,',','.') : '-').'</td>
                <td class="right-align">'.($row->item()->exists() ? number_format($row->qty,3,',','.') : '-').'</td>
                <td class="center-align">'.($row->item()->exists() ? $row->item->productionUnit->code : '-').'</td>
                <td>'.($row->item()->exists() ? $row->itemStock->fullName() : '-').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div><div class="col s12 mt-3"><table style="min-width:100%;" class="bordered" id="table-detail-row">
            <thead>
                <tr>
                    <th class="center-align" colspan="9" style="font-size:20px !important;">Item Receive (Diterima)</th>
                </tr>
                <tr>
                    <th class="center" width="5%">No.</th>
                    <th class="center" width="15%">Item/Coa</th>
                    <th class="center" width="10%">Qty Planned (Prod.)</th>
                    <th class="center" width="10%">Qty Real (Prod.)</th>
                    <th class="center" width="10%">Qty UoM</th>
                    <th class="center" width="10%">Qty Jual</th>
                    <th class="center" width="10%">Qty Pallet</th>
                    <th class="center" width="15%">Shading</th>
                    <th class="center" width="15%">Batch</th>
                </tr>
            </thead><tbody>';

        foreach($data->productionIssueReceiveDetail()->where('type','2')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'.</td>
                <td>'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="right-align">'.number_format($data->productionOrder->productionScheduleDetail->qty,3,',','.').' '.$row->item->productionUnit->code.'</td>
                <td class="right-align">'.number_format($row->qty,3,',','.').' '.$row->item->productionUnit->code.'</td>
                <td class="right-align">'.number_format($row->qty * $row->item->production_convert,3,',','.').' '.$row->item->uomUnit->code.'</td>
                <td class="right-align">'.number_format(($row->qty * $row->item->production_convert) / $row->item->sell_convert,3,',','.').' '.$row->item->sellUnit->code.'</td>
                <td class="right-align">'.number_format((($row->qty * $row->item->production_convert) / $row->item->sell_convert) / $row->item->pallet_convert,3,',','.').' '.$row->item->palletUnit->code.'</td>
                <td class="center-align">'.$row->shading.'</td>
                <td class="center-align">'.$row->batch_no.'</td>
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

    public function printIndividual(Request $request,$id){
        
        $pr = ProductionIssueReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Issue Receive',
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
             
            $pdf = Pdf::loadView('admin.print.production.issue_receive_individual', $data)->setPaper('a5', 'landscape');
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = ProductionIssueReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
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

                foreach($query->productionIssueReceiveDetail()->whereNotNull('production_order_detail_id')->get() as $row){
                    $row->productionOrderDetail->update([
                        'qty_real'      => NULL,
                        'nominal_real'  => NULL,
                        'total_real'    => NULL,
                    ]);
                }

                $query->productionOrder->update([
                    'actual_item_cost'      => NULL,
                    'actual_resource_cost'  => NULL,
                    'total_product_cost'    => NULL,
                    'completed_qty'         => NULL,
                ]);
    
                activity()
                    ->performedOn(new ProductionIssueReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the issue receive data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Issue Receive No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);
                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                }

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
        $query = ProductionIssueReceive::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->productionIssueReceiveDetail()->whereNotNull('production_order_detail_id')->get() as $row){
                $row->productionOrderDetail->update([
                    'qty_real'      => NULL,
                    'nominal_real'  => NULL,
                    'total_real'    => NULL,
                ]);
            }

            $query->productionIssueReceiveDetail()->delete();

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionIssueReceive())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the issue receive data');

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
                $pr = ProductionIssueReceive::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Issue Receive',
                        'data'      => $pr,
                      
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.production.issue_receive_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = ProductionIssueReceive::where('Code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Issue Receive',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.production.issue_receive_individual', $data)->setPaper('a5', 'landscape');
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
                        $query = ProductionIssueReceive::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Issue Receive',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.production.issue_receive_individual', $data)->setPaper('a5', 'landscape');
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


    public function viewStructureTree(Request $request){
        $query = MarketingOrderPlan::where('code',CustomHelper::decrypt($request->id))->first();
        
        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_incoming_payment=[];

        $data_go_chart=[];
        $data_link=[];


        if($query){
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

            $data_go_chart[]= $data_marketing_order;
            $data_id_mo[]=$query->id;

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
                    foreach($query_invoice->marketingOrderInvoiceDetail as $row_invoice_detail){
                        if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDownPayment()->exists()){
                            
                        }
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
                // mencari delivery anakan
                $data_deliv_process=[];
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
            CustomHelper::sendUsedData($request->type,$request->id,'Form Issue Receive');
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
        $query = ProductionIssueReceive::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' =>  $query->lookable_id ? $query->lookable->code : '-',
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.$row->coa->company->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->name : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }
}