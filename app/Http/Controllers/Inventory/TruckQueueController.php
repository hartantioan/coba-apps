<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\CustomHelper;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use App\Models\InventoryCoa;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\TruckQueue;
use App\Models\TruckQueueDetail;
use App\Models\User;
use Illuminate\Http\Request;

class TruckQueueController extends Controller
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
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $data = [
            'title'     => 'Antrian Truk',
            'content'   => 'admin.inventory.truck_queue',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'line'      => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'machine'   => Machine::where('status','1')->orderBy('name')->get(),
            'coa_cost'  => InventoryCoa::where('status','1')->where('type','1')->get(),
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'name',
            'no_pol',
            'expedition',
            'truck',
            'date',
            'type',
            'code_barcode',
            'document_status',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = TruckQueue::count();

        $query_data = TruckQueue::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhere('no_pol', 'like', "%$search%")
                            ->orWhere('truck', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('code_barcode', 'like', "%$search%");
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('date', '>=', $request->start_date)
                        ->whereDate('date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = TruckQueue::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhere('no_pol', 'like', "%$search%")
                            ->orWhere('truck', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('code_barcode', 'like', "%$search%");
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('date', '>=', $request->start_date)
                        ->whereDate('date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->name,
                    $val->no_pol,
                    $val->expedition,
                    $val->truck,
                    date('d/m/Y',strtotime($val->date)),
                    $val->type(),
                    $val->code_barcode,
                    $val->documentStatus(),
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat lime darken-3 accent-2 white-text btn-small" data-popup="tooltip" title="Ganti Status" onclick="showStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">low_priority</i></button>
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

    public function show(Request $request){
        $po = TruckQueue::where('code',CustomHelper::decrypt($request->id))->first();

		return response()->json($po);
    }

    public function updateStatusDocument(Request $request){
        $iti = TruckQueue::where('code',CustomHelper::decrypt($request->id))->latest()->first();

        if($iti->status == '6'){
            $response = [
                'status'    => 500,
                'message'   => 'Antrian sudah di status berikutnya',
            ];
        }else{
            $iti->update([
                'document_status'=> $request->document_status_edit,
                'change_status'  => Date::now(),
            ]);
            if($iti){
                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];
            }
        }

		return response()->json($response);
    }


    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'name'             => 'required',
            'no_pol'                => 'required',
			'truck'		            => 'required',
            'code_barcode'		                => 'required',
		], [
            'name.required'                     => 'Nama SUpir Tidak boleh kosong',
            'no_pol.required'                   => 'No Polisi tidak boleh kosong.',
			'code_barcode.required' 			=> 'Kode Barcode  tidak boleh kosong.',

		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            if($request->temp){
                $query = TruckQueue::where('code',CustomHelper::decrypt($request->temp))->first();
                if($query->status == '6'){
                    $response = [
                        'status'    => 500,
                        'message'   => 'Antrian sudah keluar',
                    ];
                    return response()->json($response);
                }else{
                    $query->user_id = session('bo_id');
                    $query->no_pol = $request->no_pol;
                    $query->truck = $request->truck;
                    $query->type = $request->type;
                    $query->expedition = $request->expedition;
                    $query->code_barcode = $request->code_barcode;
                    $query->no_container = $request->no_container;
                    $query->note = $request->note;
                    $query->save();
                }

            }else{
                $newCode=TruckQueue::generateCode('ATR'.date('y',strtotime($request->date)).'P');

                $query = TruckQueue::create([
                    'code'			    => $newCode,
                    'name'		        => $request->name,
                    'no_pol'		    => $request->no_pol,
                    'truck'             => $request->truck,
                    'document_status'   => $request->document_status,
                    'code_barcode'      => $request->code_barcode,
                    'date'              => Date::now(),
                    'type'              => $request->type,
                    'expedition'        => $request->expedition,
                    'no_container'      => $request->no_container,
                    'note'              => $request->note,
                    'status'            => '1',
                    'user_id'           => session('bo_id'),
                ]);
                $query_detail = TruckQueueDetail::create([
                    'truck_queue_id'			            => $query->id,
                    'good_scale_id'		                => null,
                    'marketing_delivery_oder_process_id'	=> null,
                    'time_in'	=> null,
                ]);
            }

            if($query) {

                activity()
                    ->performedOn(new TruckQueue())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Antrian Truck.');

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

    public function rowDetail(Request $request)
    {
        $data   = TruckQueue::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        $string = '<div class="row pt-1 pb-1 lighten-4">
            <div class="col s12">
                <h5>'.$data->code.' - '.$data->user->name.'</h5>
            </div>
            <div class="col s12" style="overflow:auto;">';

        $gs_code = "-";
        $gs_time_out = "-";
        $sj_code = "-";
        $gs_time_out = "-";
        $sj_keluar = '-';

        if ($data->truckQueueDetail->goodScale()->exists()) {
            $gs_code = $data->truckQueueDetail->goodScale->code;
            $gs_time_out=$data->truckQueueDetail->goodScale->time_scale_out;
            $sj_code = $data->truckQueueDetail->goodScale->getSalesSuratJalan();
            $gs_time_out=$data->truckQueueDetail->goodScale->time_scale_out;
            $sj_keluar=$data->truckQueueDetail->goodScale->getSuratJalanKeluarPabrik();
        }

        $string .= '<div class="card-panel">
                <div class="row">
                    <div class="col s12 m6 l4">
                        <p><strong>Status Dokumen</strong><br>'.$data->status().'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Kode Barcode</strong><br>'.$data->code_barcode.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Antri</strong><br>'.$data->date.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>No Timbangan</strong><br>'.$gs_code.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Timbang Masuk</strong><br>'.$data->truckQueueDetail->time_in.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Muat FG</strong><br>'.$data->time_load_fg.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Selesai Muat FG</strong><br>'.$data->time_done_load_fg.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Timbang Keluar</strong><br>'.$gs_time_out.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Kode SJ</strong><br>'.$sj_code.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Keluar Pabrik</strong><br>'.$sj_keluar.'</p>
                    </div>
                    <div class="col s12 m6 l4">
                        <p><strong>Ganti Status Dokumen</strong><br>'.$data->change_status.'</p>
                    </div>
                </div>
            </div>';

        $string .= '</div></div>';


        return response()->json($string);
    }
}
