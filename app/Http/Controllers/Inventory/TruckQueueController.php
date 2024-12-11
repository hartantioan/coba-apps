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
            'code',
            'name',
            'no_pol',
            'truck',
            'document_status',
            'code_barcode',
            'date',
            'status',
            'user_id',
            'date'];

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
                            });
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
                            });
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
                $query->user_id = session('bo_id');
                $query->no_pol = $request->no_pol;
                $query->truck = $request->truck;
                $query->document_status = $request->document_status;
                $query->type = $request->type;
                $query->expedition = $request->expedition;
                $query->code_barcode = $request->code_barcode;
                $query->save();
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
                    'status'            => '1',
                    'user_id'           => session('bo_id'),
                ]);
            }

            if($query) {
                $query_detail = TruckQueueDetail::create([
                    'truck_queue_id'			            => $query->id,
                    'good_scale_id'		                => null,
                    'marketing_delivery_oder_process_id'	=> null,
                    'time_in'	=> null,
                ]);
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
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.' - '.$data->user->name.'</div><div class="col s12" style="overflow:auto;"><table style="min-width:2500px;">
                        <thead>
                            <tr>
                                <th class="center-align">Status Dokumen</th>
                                <th class="center-align">Kode Barcode</th>
                                <th class="center-align">Antri</th>
                                <th class="center-align">No Timbangan</th>
                                <th class="center-align">Timbang Masuk</th>
                                <th class="center-align">Muat FG</th>
                                <th class="center-align">Selesai Muat FG</th>
                                <th class="center-align">Timbang Keluar</th>
                                <th class="center-align">Kode SJ</th>
                                <th class="center-align">Keluar Pabrik</th>
                            </tr>
                        </thead><tbody>';
                        $gs_code="-";
                        $gs_time_out="-";
                        $sj_code="-";
                        $gs_time_out="-";
        if($data->truckQueueDetail->goodScale()->exists()){
            $gs_code = $data->truckQueueDetail->goodScale->code;
            $gs_time_out=$data->truckQueueDetail->goodScale->time_scale_out;
        }
        if($data->truckQueueDetail->marketingOrderDeliveryProcess()->exists()){
            $sj_code = $data->truckQueueDetail->marketingOrderDeliveryProcess->code;
            $gs_time_out=$data->truckQueueDetail->goodScale->time_scale_out;
        }

        $string .= '<tr>
            <td class="center-align">'.$data->status().'</td>
            <td class="center-align">'.$data->code_barcode.'</td>
            <td class="center-align">'.$data->date.'</td>
            <td class="center-align">'.$gs_code.'</td>
            <td class="center-align">'.$data->truckQueueDetail->time_in.'</td>
            <td class="center-align">'.$data->time_load_fg.'</td>
            <td class="center-align">'.$data->time_done_load_fg.'</td>
            <td class="center-align">'.$gs_time_out.'</td>
            <td class="center-align">'.$sj_code.'</td>
            <td class="center-align">'.$data->truckQueueDetail->marketingOrderDeliveryProcess?->deliveryScan?->created_at ?? '-'.'</td>
        </tr>';

        $string .= '</tbody></table></div>';

        return response()->json($string);
    }
}
