<?php

namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderPlan;
use App\Models\MarketingOrderPlanDetail;
use App\Models\Place;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\IncomingPayment;
use App\Models\Item;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\ProductionSchedule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportProductionOrderTransactionPage;
use App\Exports\ExportProductionOrder;
use App\Models\ProductionScheduleDetail;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use App\Models\UsedData;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\TreeHelper;
class ProductionOrderController extends Controller
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
            'title'         => 'Order Produksi',
            'content'       => 'admin.production.order',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'area'          => Area::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->whereNotNull('is_transit_warehouse')->first(),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = ProductionOrder::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'place_id',
            'post_date',
            'item_id',
            'start_date',
            'end_date',
            'type',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ProductionOrder::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('productionOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('productionScheduleDetail',function($query) use ($search, $request){
                                    $query->whereHas('item',function($query) use ($search, $request){
                                        $query->where('code','like',"%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    })->orWhereHas('productionSchedule',function($query) use ($search){
                                        $query->where('code','like',"%$search%");
                                    });
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

        $total_filtered = ProductionOrder::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('productionOrderDetail',function($query) use ($search, $request){
                                $query->whereHas('productionScheduleDetail',function($query) use ($search, $request){
                                    $query->whereHas('item',function($query) use ($search, $request){
                                        $query->where('code','like',"%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    })->orWhereHas('productionSchedule',function($query) use ($search){
                                        $query->where('code','like',"%$search%");
                                    });
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
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->listItemTarget(),
                    $val->note,
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
                        <button type="button" class="btn-floating mb-1 btn-flat blue accent-2 white-text btn-small" data-popup="tooltip" title="Tutup & Hitung Varian" onclick="calculate(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">developer_mode</i></button>
                        <!-- <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button> -->
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function getCloseData(Request $request){
        $data = ProductionOrder::where('code',CustomHelper::decrypt($request->id))->first();
        if($data){

            $details = [];

            foreach($data->productionOrderDetail as $row){
                $detailIssue = $row->htmlContentIssue();
                $detailReceive = $row->htmlHandover();
                $details[] = [
                    'item_code'             => $row->productionScheduleDetail->item->code,
                    'item_name'             => $row->productionScheduleDetail->item->name,
                    'bom_code'              => $row->productionScheduleDetail->bom->code,
                    'qty_planned'           => CustomHelper::formatConditionalQty($row->productionScheduleDetail->qty),
                    'qty_received'          => CustomHelper::formatConditionalQty($row->qtyReceive()),
                    'qty_reject'            => CustomHelper::formatConditionalQty($row->qtyReject()),
                    'unit'                  => $row->productionScheduleDetail->item->uomUnit->code,
                    'detail_issue'          => $detailIssue,
                    'detail_receive'        => $detailReceive,
                    /* 'total_issue_item'      => CustomHelper::formatConditionalQty($row->totalIssueItem()),
                    'total_issue_resource'  => CustomHelper::formatConditionalQty($row->totalIssueResource()),
                    'total_item'            => CustomHelper::formatConditionalQty($row->totalItem()), */
                ];
            }

            $query = [
                'code'                      => $data->code,
                'encrypt_code'              => CustomHelper::encrypt($data->code),
                'real_time_start'           => $data->real_time_start,
                'real_time_end'             => $data->real_time_end,
                'details'                   => $details,
            ];
            $response = [
                'status'    => 200,
                'message'   => 'Data berhasil dimuat!',
                'data'      => $query,
            ];
        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.'
            ];
        }

        return response()->json($response);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'                          => 'required',
            'code_place_id'                 => 'required',
            'company_id'			        => 'required',
            'post_date'		                => 'required',
            'production_schedule_id'        => 'required',
            'production_schedule_detail_id' => 'required',
        ], [
            'code_place_id.required'                    => 'Plant Tidak boleh kosong',
            'code.required' 	                        => 'Kode tidak boleh kosong.',
            'company_id.required' 			            => 'Perusahaan tidak boleh kosong.',
            'post_date.required' 			            => 'Tanggal posting tidak boleh kosong.',
            'production_schedule_id.required'           => 'Jadwal Produksi tidak boleh kosong.',
            'production_schedule_detail_id.required'    => 'Item target tidak boleh kosong.',
            'warehouse_id.required'                     => 'Gudang target tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $qtyPlanned = 0;
            $standardItemCost = 0;
            $standardResourceCost = 0;
            $standardProductCost = 0;
            $arrDetailCost = [];

            $pscd = ProductionScheduleDetail::find($request->production_schedule_detail_id);
            if($pscd){
                $qtyPlanned = $pscd->qty;

                foreach($request->arr_lookable_type as $key => $row){
                    if($row == 'items'){
                        $rownominal = Item::find($request->arr_lookable_id[$key])->priceNowProduction($pscd->productionSchedule->place_id,$request->post_date) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
                        $standardItemCost += $rownominal;
                        $arrDetailCost[] = $rownominal;
                    }elseif($row == 'coas'){
                        $standardResourceCost += str_replace(',','.',str_replace('.','',$request->arr_total[$key]));
                        $arrDetailCost[] = str_replace(',','.',str_replace('.','',$request->arr_total[$key]));
                    }
                }
            }

            $standardProductCost = $standardItemCost + $standardResourceCost;
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = ProductionOrder::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Order Produksi telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->production_schedule_id = $request->production_schedule_id;
                        $query->production_schedule_detail_id = $request->production_schedule_detail_id;
                        $query->warehouse_id = $request->warehouse_id;
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->standard_item_cost = $standardItemCost;
                        $query->standard_resource_cost = $standardResourceCost;
                        $query->standard_product_cost = $standardProductCost;
                        $query->planned_qty = $qtyPlanned;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionOrderDetail() as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Order Produksi sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=ProductionOrder::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionOrder::create([
                        'code'			                => $newCode,
                        'user_id'		                => session('bo_id'),
                        'company_id'                    => $request->company_id,
                        'production_schedule_id'	    => $request->production_schedule_id,
                        'production_schedule_detail_id'	=> $request->production_schedule_detail_id,
                        'warehouse_id'                  => $request->warehouse_id,
                        'post_date'                     => $request->post_date,
                        'note'                          => $request->note,
                        'standard_item_cost'            => $standardItemCost,
                        'standard_resource_cost'        => $standardResourceCost,
                        'standard_product_cost'         => $standardProductCost,
                        'planned_qty'                   => $qtyPlanned,
                        'status'                        => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Order Produksi No. '.$query->code,'Pengajuan Order Produksi No. '.$query->code,session('bo_id'));

                activity()
                    ->performedOn(new ProductionOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit production order plan.');

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

    public function approval(Request $request,$id){
        
        $pr = ProductionOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Order Produksi',
                'data'      => $pr
            ];

            return view('admin.approval.production_order', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = ProductionOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"> <div class="col s12">'.$data->code.'</div>
        <div class="col s12"></div><div class="col s12" style="overflow:auto;"><table style="min-width:100%;">
        <thead>
            <tr>
                <th class="center-align" colspan="9">Daftar Item Produksi</th>
            </tr>
            <tr>
                <th class="center-align">No.</th>
                <th class="center-align">Kode Item</th>
                <th class="center-align">Nama Item</th>
                <th class="center-align">Kode BOM</th>
                <th class="center-align">Qty</th>
                <th class="center-align">Satuan UoM</th>
                <th class="center-align">Line</th>
                <th class="center-align">Gudang</th>
                <th class="center-align">Tipe</th>
                <th class="center-align">Keterangan</th></tr></thead><tbody>';

        foreach($data->productionOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align" style="min-width:150px !important;">'.($key + 1).'</td>           
                <td style="min-width:150px !important;">'.$row->productionScheduleDetail->item->code.'</td>
                <td style="min-width:150px !important;">'.$row->productionScheduleDetail->item->name.'</td>
                <td style="min-width:150px !important;">'.$row->productionScheduleDetail->bom->code.' - '.$row->productionScheduleDetail->bom->name.'</td>
                <td style="min-width:150px !important;" class="right-align">'.CustomHelper::formatConditionalQty($row->productionScheduleDetail->qty).'</td>
                <td style="min-width:150px !important;" class="center-align">'.$row->productionScheduleDetail->item->uomUnit->code.'</td>
                <td style="min-width:150px !important;" class="center-align">'.$row->productionScheduleDetail->line->code.'</td>
                <td style="min-width:150px !important;" class="center-align">'.$row->productionScheduleDetail->warehouse->name.'</td>
                <td style="min-width:150px !important;" class="center-align">'.$row->productionScheduleDetail->type().'</td>
                <td style="min-width:150px !important;">'.$row->productionScheduleDetail->note.'</td>
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
        
        $pr = ProductionOrder::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Order Produksi','a5','landscape','admin.print.production.order_individual',$menuUser->mode);
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
        $query = ProductionOrder::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                foreach($query->productionOrderDetail as $row){
                    $row->productionScheduleDetail->update([
                        'status_process'    => NULL,
                    ]);
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new ProductionOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the production order plan data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Order Produksi No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
            $temp_pdf = [];
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key => $row){
                $pr = ProductionOrder::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Order Produksi','a5','landscape','admin.print.production.order_individual');
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

            $randomString = Str::random(10); 
         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $result);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

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
                        $query = ProductionOrder::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Order Produksi','a5','landscape','admin.print.production.order_individual');
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
                        $query = ProductionOrder::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Order Produksi','a5','landscape','admin.print.production.order_individual');
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


    public function viewStructureTree(Request $request){
        $query = ProductionOrder::where('code',CustomHelper::decrypt($request->id))->first();
    

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
                'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_marketing_order;
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_order',$query->id);
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

    public function done(Request $request){
        $query_done = ProductionOrder::where('code',CustomHelper::decrypt($request->tempClose))->first();

        if($query_done){

            if(in_array($query_done->status,['2'])){
                
                $query_done->update([
                    'real_time_start'   => $request->real_time_start,
                    'real_time_end'     => $request->real_time_end,
                    'status'            => '3',
                    'done_id'           => session('bo_id'),
                    'done_date'         => date('Y-m-d H:i:s'),
                ]);

                $query_done->updateProductionScheduleDone();
    
                activity()
                    ->performedOn(new ProductionOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query_done)
                    ->log('Done the Production Order data');
                
                CustomHelper::sendNotification($query_done->getTable(),$query_done->id,'Penutupan Production Order Produksi No. '.$query_done->code,'Penutupan Production Order No. '.$query_done->code.' oleh user '.session('bo_name'),$query_done->user_id);
    
                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan PROSES.'
                ];
            }

            return response()->json($response);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportProductionOrder($post_date,$end_date,$mode), 'production_order'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type = $request->type ? $request->type : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExportProductionOrderTransactionPage($search,$status,$type,$end_date,$start_date), 'marketing_order_production'.uniqid().'.xlsx');
    }

}