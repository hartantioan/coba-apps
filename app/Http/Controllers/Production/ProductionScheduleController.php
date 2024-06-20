<?php

namespace App\Http\Controllers\Production;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderPlan;
use App\Models\IncomingPayment;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderPlanDetail;
use Illuminate\Support\Str;
use App\Models\ProductionSchedule;
use App\Models\ProductionScheduleDetail;
use App\Models\Place;
use App\Models\ProductionScheduleTarget;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\TreeHelper;
use App\Models\Line;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportProductionScheduleTransactionPage;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\Date;

class ProductionScheduleController extends Controller
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
        $places = Place::where('status','1')->whereIn('id',$this->dataplaces)->get();
        $lines = Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get();
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();

        $data = [
            'title'         => 'Jadwal Produksi',
            'content'       => 'admin.production.schedule',
            'company'       => Company::where('status','1')->get(),
            'place'         => $places,
            'line'          => $lines,
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = ProductionSchedule::generateCode($request->val);
        				
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
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ProductionSchedule::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ProductionSchedule::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('productionScheduleDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('place',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
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

        $total_filtered = ProductionSchedule::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('productionScheduleDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('place',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
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
                    $val->place->code,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
            'company_id'			    => 'required',
            'place_id'		            => 'required',
            'post_date'		            => 'required',
            'arr_id'                    => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_detail_qty'            => 'required|array',
            'arr_item_detail_id'        => 'required|array',
            'arr_bom'                   => 'required|array',
            'arr_start_date'            => 'required|array',
            'arr_end_date'              => 'required|array',
            'arr_detail_id'             => 'required|array',
        ], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'place_id.required' 			    => 'Plant tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'arr_id.required'                   => 'Marketing Order Plan item target tidak boleh kosong.',
            'arr_id.array'                      => 'Marketing Order Plan item target harus array.',
            'arr_qty.required'                  => 'Qty target tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty target harus array.',
            'arr_detail_qty.required'           => 'Qty detail tidak boleh kosong.',
            'arr_detail_qty.array'              => 'Qty detail harus array.',
            'arr_bom.required'                  => 'Bom tidak boleh kosong.',
            'arr_bom.array'                     => 'Bom harus array.',
            'arr_start_date.required'           => 'Tgl mulai tidak boleh kosong.',
            'arr_start_date.array'              => 'Tgl mulai harus array.',
            'arr_end_date.required'             => 'Tgl selesai tidak boleh kosong.',
            'arr_end_date.array'                => 'Tgl selesai harus array.',
            'arr_detail_id.required'            => 'Detail id tidak boleh kosong.',
            'arr_detail_id.array'               => 'Detail id harus array.',
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
                    $query = ProductionSchedule::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Jadwal Produksi telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/production_schedules');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->place_id = $request->place_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->productionScheduleDetail as $row){
                            $row->delete();
                        }

                        foreach($query->productionScheduleTarget as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Jadwal Produksi sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode = ProductionSchedule::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = ProductionSchedule::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'place_id'	                => $request->place_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/production_schedules') : NULL,
                        'note'                      => $request->note,
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
                    
                    foreach($request->arr_id as $key => $row){
                        ProductionScheduleTarget::create([
                            'production_schedule_id'            => $query->id,
                            'marketing_order_plan_detail_id'    => $row,
                            'qty'                               => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        ]);
                    }

                    foreach($request->arr_detail_id as $key => $row){
                        ProductionScheduleDetail::create([
                            'production_schedule_id'        => $query->id,
                            'marketing_order_plan_detail_id'=> $row,
                            'item_id'                       => $request->arr_item_detail_id[$key],
                            'bom_id'                        => $request->arr_bom[$key],
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_detail_qty[$key])),
                            'line_id'                       => $request->arr_line[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                            'start_date'                    => $request->arr_start_date[$key],
                            'end_date'                      => $request->arr_end_date[$key],
                            'note'                          => $request->arr_note[$key],
                            'type'                          => $request->arr_type[$key] == 'normal' ? '1' : '2',
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Jadwal Produksi No. '.$query->code,'Pengajuan Jadwal Produksi No. '.$query->code,session('bo_id'));

                activity()
                    ->performedOn(new ProductionSchedule())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit jadwal produksi plan.');

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
        $po = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);

        $arr = [];

        $composition = [];

        foreach($po->productionScheduleTarget as $row){
            $cekBom = $row->marketingOrderPlanDetail->item->bomPlace($po->place_id);

            $arrDetail = [];

            foreach($po->productionScheduleDetail()->where('marketing_order_plan_detail_id',$row->marketing_order_plan_detail_id)->orderBy('id')->get() as $rowdetail){
                $arrDetail[] = [
                    'mopd_id'           => $rowdetail->marketing_order_plan_detail_id,
                    'start_date'        => $rowdetail->start_date,
                    'end_date'          => $rowdetail->end_date,
                    'bom_id'            => $rowdetail->bom_id ?? '',
                    'bom_code'          => $rowdetail->bom->code.' - '.$rowdetail->bom->name,
                    'item_id'           => $rowdetail->item_id,
                    'item_code'         => $rowdetail->item->code.' - '.$rowdetail->item->name,
                    'qty'               => CustomHelper::formatConditionalQty($rowdetail->qty),
                    'uom'               => $rowdetail->item->uomUnit->code,
                    'warehouse_id'      => $rowdetail->warehouse_id,
                    'warehouse_name'    => $rowdetail->warehouse->name,
                    'note'              => $rowdetail->note ?? '',
                    'list_warehouse'    => $rowdetail->item->warehouseList(),
                    'line_id'           => $rowdetail->line_id,
                    'type'              => $rowdetail->type == '1' ? 'normal' : 'powder',
                ];
            }

            $arr[] = [
                'id'                => $row->marketingOrderPlanDetail->marketing_order_plan_id,
                'mop_code'          => $row->marketingOrderPlanDetail->marketingOrderPlan->code,
                'mopd_id'           => $row->marketing_order_plan_detail_id,
                'item_id'           => $row->marketingOrderPlanDetail->item_id,
                'item_code'         => $row->marketingOrderPlanDetail->item->code,
                'item_name'         => $row->marketingOrderPlanDetail->item->name,
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'uom'               => $row->marketingOrderPlanDetail->item->uomUnit->code,
                'request_date'      => date('d/m/Y',strtotime($row->marketingOrderPlanDetail->request_date)),
                'note'              => $row->marketingOrderPlanDetail->note ? $row->marketingOrderPlanDetail->note : '',
                'has_bom'           => $cekBom->exists() ? '1' : '',
                'place_id'          => $po->place_id,
                'line'              => $row->marketingOrderPlanDetail->marketingOrderPlan->line->code,
                'details'           => $arrDetail,
            ];
        }

        $po['targets'] = $arr;
        $po['compositions'] = $composition;
        				
		return response()->json($po);
    }

    public function approval(Request $request,$id){
        
        $pr = ProductionSchedule::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Jadwal Produksi',
                'data'      => $pr
            ];

            return view('admin.approval.production_schedule', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();

        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12" style="overflow:auto;"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Target Produksi</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">MOP</th>
                                <th class="center-align">Kode Item</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan UoM</th>
                                <th class="center-align">Tgl.Request</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';

        $arrMop = [];
        
        foreach($data->productionScheduleTarget as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->marketingOrderPlanDetail->marketingOrderPlan->code.'</td>
                <td class="center-align">'.$row->marketingOrderPlanDetail->item->code.'</td>
                <td class="center-align">'.$row->marketingOrderPlanDetail->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->marketingOrderPlanDetail->item->uomUnit->code.'</td>
                <td class="center-align">'.date('d/m/Y',strtotime($row->marketingOrderPlanDetail->request_date)).'</td>
                <td class="">'.$row->marketingOrderPlanDetail->note.'</td>
            </tr>';
            $arrMop[] = [
                'item_name' => $row->marketingOrderPlanDetail->item->code.' - '.$row->marketingOrderPlanDetail->item->name,
                'mopd_id'   => $row->marketing_order_plan_detail_id,
            ];
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1" style="overflow:auto;width:100% !important;"><table style="min-width:1800px;">
                        <thead>
                            <tr>
                                <th colspan="3">Daftar BOM & Target Produksi</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th style="min-width:300px !important;">Item Target</th>
                                <th style="min-width:150px !important;">Informasi Jadwal & BOM</th>
                            </tr>
                        </thead><tbody>';

        foreach($arrMop as $key => $row){
            $htmlDetail = '<table class="bordered"><thead><tr>';
            
            foreach($data->productionScheduleDetail()->where('marketing_order_plan_detail_id',$row['mopd_id'])->get() as $keydetail => $rowdetail){
                $htmlDetail .= '<th class="center-align">Status Proses</th>
                    <th class="center-align">Status Approval</th>
                    <th class="center-align">No. PDO</th>
                    <th class="center-align">Kode Item</th>
                    <th class="center-align">Nama Item</th>
                    <th class="center-align">Kode BOM</th>
                    <th class="center-align">Qty</th>
                    <th class="center-align">Satuan UoM</th>
                    <th class="center-align">Line</th>
                    <th class="center-align">Gudang</th>
                    <th class="center-align">Tgl.Mulai</th>
                    <th class="center-align">Tgl.Selesai</th>
                    <th class="center-align">Tipe</th>
                    <th class="center-align">Keterangan</th>
                ';
            }
            
            $htmlDetail .= '</tr></thead><tbody><tr>';

            $start = $data->productionScheduleDetail()->where('marketing_order_plan_detail_id',$row['mopd_id'])->count(); 

            foreach($data->productionScheduleDetail()->where('marketing_order_plan_detail_id',$row['mopd_id'])->orderBy('id')->get() as $keydetail => $rowdetail){
                $option = '-';
                if($rowdetail->status == '1'){
                    $option = '<select class="browser-default" onfocus="updatePrevious(this);" onchange="updateDocumentStatus(`'.CustomHelper::encrypt($rowdetail->id).'`,this,'.$start.')" style="width:150px;">
                    <option value="" '.($rowdetail->status_process == NULL || $rowdetail->status_process == '' ? 'selected' : '').'>MENUNGGU</option>
                    <option value="1" '.($rowdetail->status_process == '1' ? 'selected' : '').'>PROSES</option>
                    <option value="2" '.($rowdetail->status_process == '2' ? 'selected' : '').' disabled>SELESAI</option>
                    <option value="3" '.($rowdetail->status_process == '3' ? 'selected' : '').' disabled>DITUNDA</option>
                </select>';
                }

                $randomColor = CustomHelper::randomColor(150,255);
                
                $htmlDetail .= '
                    <td class="center-align" style="min-width:150px !important;background-color:'.$randomColor.';">
                        '.$option.'
                    </td>
                    <td class="center-align" style="min-width:150px !important;background-color:'.$randomColor.';">'.$rowdetail->status().'</td>           
                    <td class="center-align" style="min-width:150px !important;background-color:'.$randomColor.';" id="pod-'.CustomHelper::encrypt($rowdetail->id).'">'.($rowdetail->productionOrder()->exists() ? $rowdetail->productionOrder->code : '-').'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';">'.$rowdetail->item->code.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';">'.$rowdetail->item->name.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';">'.$rowdetail->bom->code.' - '.$rowdetail->bom->name.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="right-align">'.CustomHelper::formatConditionalQty($rowdetail->qty).'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.$rowdetail->item->uomUnit->code.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.$rowdetail->line->code.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.$rowdetail->warehouse->code.'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.date('d/m/Y H:i:s',strtotime($rowdetail->start_date)).'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.date('d/m/Y H:i:s',strtotime($rowdetail->end_date)).'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';" class="center-align">'.$rowdetail->type().'</td>
                    <td style="min-width:150px !important;background-color:'.$randomColor.';">'.$rowdetail->note.'</td>';
                $start -= 1;
            }

            $htmlDetail .= '</tr></tbody></table>';

            $string .= '<tr>
                <td class="center-align" rowspan="2">'.($key + 1).'</td>
                <td class="">
                    '.$row['item_name'].'
                </td>
                <td>'.$htmlDetail.'</td>
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
        
        $pr = ProductionSchedule::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            
            $pdf = PrintHelper::print($pr,'Jadwal Produksi','a4','landscape','admin.print.production.schedule_individual');
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new ProductionSchedule())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the jadwal produksi data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Jadwal Produksi No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
        $query = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->productionScheduleDetail()->delete();
            $query->productionScheduleTarget()->delete();

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ProductionSchedule())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the jadwal produksi data');

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
            $temp_pdf = [];
            foreach($request->arr_id as $key => $row){
                $pr = ProductionSchedule::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Jadwal Produksi','a5','landscape','admin.print.production.schedule_individual');
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
                        $query = ProductionSchedule::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Jadwal Produksi','a5','landscape','admin.print.production.schedule_individual');
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
                        $query = ProductionSchedule::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Jadwal Produksi','a5','landscape','admin.print.production.schedule_individual');
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
        $query = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();
    
        $data_go_chart=[];
        $data_link=[];

        if($query){
            $data_core = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                  
                 ],
                'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_core;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_schedule',$query->id);
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
        $mop = MarketingOrderPlan::find($request->id);
       
        if(!$mop->used()->exists()){
            CustomHelper::sendUsedData($mop->getTable(),$request->id,'Form Jadwal Produksi');
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

    public function done(Request $request){
        $query_done = ProductionSchedule::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new ProductionSchedule())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Production Schedule data');
    
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
      
		return Excel::download(new ExportProductionScheduleTransactionPage($search,$status,$end_date,$start_date), 'production_schedule'.uniqid().'.xlsx');
    }

    public function updateDocumentStatus(Request $request){
        $data = ProductionScheduleDetail::find(CustomHelper::decrypt($request->code));
        if($data){
            $lastSegment = 'production_order';
            $menu = Menu::where('url', $lastSegment)->first();
            $order = $request->order;
            $newCode = ProductionOrder::generateCode($menu->document_code.date('y').substr($data->productionSchedule->code,7,2),$order);
            
            $query = ProductionOrder::create([
                'code'			                => $newCode,
                'user_id'		                => session('bo_id'),
                'company_id'                    => $data->productionSchedule->company_id,
                'production_schedule_id'	    => $data->production_schedule_id,
                'production_schedule_detail_id'	=> $data->id,
                'warehouse_id'                  => $data->warehouse_id,
                'post_date'                     => date('Y-m-d'),
                'note'                          => $data->note,
                'standard_item_cost'            => 0,
                'standard_resource_cost'        => 0,
                'standard_product_cost'         => 0,
                'actual_item_cost'              => 0,
                'actual_resource_cost'          => 0,
                'total_product_cost'            => 0,
                'planned_qty'                   => $data->qty,
                'completed_qty'                 => 0,
                'rejected_qty'                  => 0,
                'total_production_time'         => 0,
                'total_additional_time'         => 0,
                'total_run_time'                => 0,
                'status'                        => '2',
            ]);

            $data->update([
                'status_process'    => $request->status,
            ]);

            CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Order Produksi No. '.$query->code,'Pengajuan Order Produksi No. '.$query->code,$data->productionSchedule->user_id);
            CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Order Produksi No. '.$query->code,'Pengajuan Order Produksi No. '.$query->code,session('bo_id'));

            activity()
                    ->performedOn(new ProductionOrder())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit production order from production schedule.');

            $response = [
                'status'  => 200,
                'message' => 'Data berhasil diupdate',
                'value'   => $query->code,
            ];
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