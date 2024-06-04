<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportGoodScale;
use App\Exports\ExportGoodScaleTransactionPage;
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
use App\Models\ItemUnit;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\QualityControl;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class QualityControlController extends Controller
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
            'content'   => 'admin.inventory.quality_control',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'post_date',
            'note',
            'driver',
            'vehicle_no',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodScale::whereNotNull('is_quality_check')->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = GoodScale::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
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
                    $query->whereIn('status', $request->status);
                }
            })
            ->whereNotNull('is_quality_check')
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
                    $query->whereIn('status', $request->status);
                }
            })
            ->whereNotNull('is_quality_check')
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->referencePO(),
                    $val->user->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->driver,
                    $val->vehicle_no,
                    $val->image_qc ? '<a href="'.$val->imageQc().'" target="_blank"><i class="material-icons">camera_rear</i></a>' : '<i class="material-icons">hourglass_empty</i>',
                    $val->status(),
                    $val->statusQc(),
                    $val->note_qc,
                    CustomHelper::formatConditionalQty($val->water_content),
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
                    $val->status_qc ? 'Telah di-cek QC' : '
                        <button type="button" class="btn-floating mb-1 btn-flat blue accent-2 white-text btn-small" data-popup="tooltip" title="Isi hasil pemeriksaan." onclick="inspect(`' . CustomHelper::encrypt($val->code) . '`);"><i class="material-icons dp48">done_all</i></button>
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

    public function inspect(Request $request){
        $data = GoodScale::where('code',CustomHelper::decrypt($request->id))->first();

        if($data->used()->exists()){
            return response()->json([
                'status'    => 500,
                'message'   => 'Ups. Maaf, Data sedang dipakai oleh pengguna lainnya.'
            ]);
        }else{
            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Quality Control');

            $data['account_name'] = $data->account->employee_no.' - '.$data->account->name;
            $data['post_date'] = date('d/m/Y',strtotime($data->post_date));

            $data['purchase_order']     = $data->purchaseOrderDetail->purchaseOrder->code;
            $data['item_name']          = $data->item->code.' - '.$data->item->name;
            $data['place_name']         = $data->place->code;
            $data['warehouse_name']     = $data->warehouse->name;
            $data['note']               = $data->note;
            $data['is_hide_supplier']   = $data->item->is_hide_supplier ?? '';

            return response()->json($data);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('good_scales',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'status_qc'                 => 'required',
            'note'                      => 'required',
            'water_content'             => 'required',
		], [
            'status_qc.required' 	            => 'Status tidak boleh kosong.',
            'note.required'                     => 'Keterangan tidak boleh kosong.',
            'water_content.required'            => 'Kadar air tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {
                
                $goodScale = GoodScale::where('code',CustomHelper::decrypt($request->temp))->first();
                
                if($goodScale){

                    $goodScale->update([
                        'water_content' => str_replace(',','.',str_replace('.','',$request->water_content)),
                        'status_qc'     => $request->status_qc,
                        'note_qc'       => $request->note,
                        'image_qc'      => $request->file('document') ? $request->file('document')->store('public/good_scales') : NULL,
                        'user_qc'       => session('bo_id'),
                        'time_scale_qc' => date('Y-m-d H:i:s'),
                        'status'        => $request->status_qc == '1' ? $goodScale->status : '5',
                        'note'          => $request->status_qc == '2' ? $goodScale->note.' - Ditutup oleh bagian QC.' : $goodScale->note,
                        /* 'done_id'       => $request->status_qc == '2' ? session('bo_id') : NULL,
                        'done_note'     => $request->status_qc == '2' ? 'Ditutup oleh bagian QC.' : NULL, */
                    ]);
    
                    CustomHelper::sendNotification('good_scales',$goodScale->id,'Pengajuan Timbangan Truk No. '.$goodScale->code.' telah di-cek kualitas (QC) oleh '.session('bo_name'),$request->note,session('bo_id'));

                    activity()
                        ->performedOn(new GoodScale())
                        ->causedBy(session('bo_id'))
                        ->withProperties($goodScale)
                        ->log('Add / edit timbangan truk quality control.');

                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                    ];
                    
                }else{
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
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">
                        <div class="col s12">'.$data->code.$x.'</div>
                        <div class="col s12">
                            <table class="bordered" style="min-width:100%;max-width:100%;">
                                <thead>
                                    <tr>
                                        <th class="center-align" colspan="13">Daftar Hasil Pemeriksaan</th>
                                    </tr>
                                    <tr>
                                        <th class="center-align">No.</th>
                                        <th class="center-align">Nama</th>
                                        <th class="center-align">Nominal</th>
                                        <th class="center-align">Satuan</th>
                                        <th class="center-align">Mempengaruhi Qty</th>
                                        <th class="center-align">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>';
        $totalqty=0;
        foreach($data->qualityControl as $key => $rowdetail){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$rowdetail->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($rowdetail->nominal).'</td>
                <td class="center-align">'.$rowdetail->unit.'</td>
                <td class="center-align">'.($rowdetail->is_affect_qty ? 'Ya' : 'Tidak').'</td>
                <td class="">'.$rowdetail->note.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table>';

        $string .= '</td></tr>';

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }
}