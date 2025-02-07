<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use App\Models\MitraApiSyncData;
use App\Models\MitraApiEndpoint;
use App\Models\User;

class MitraApiSyncDataController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->dataplaces     = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode  = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    
    public function index(){
        $data = [
            'title'   => 'API Sync Data',
            'content' => 'admin.master_data.mitra_api_sync_data',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'mitra',
            'lookable_type',
            'lookable_id',
            'payload',
            'status',
            'api_response',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MitraApiSyncData::count();    

        $query_builder = MitraApiSyncData::where(function($query) use ($search, $request){
            if($search){
                $query->where(function($query) use ($search, $request){
                    $query->where('code', 'like', "%$search%")
                    // ->orWhere('name', 'like', "%$search%")
                    // ->orWhere('type', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
                });
            }

            if($request->status){
                $query->where('status', $request->status);
            }
        });

        $total_filtered = $query_builder->count();
        $query_data     = $query_builder->offset($start)->limit($length)
                                    ->orderBy($order, $dir)
                                    ->get();

        $response['data']=[];
        if($query_data <> FALSE){
            $nomor = $start + 1;
            foreach($query_data as $val){
                $lookable = $val->lookable->model_name;

                if(in_array($lookable, (["Item", "Unit", "Sales Area Mitra", "Price List Mitra"]))){
                    $keterangan = '['.$val->lookable->code.'] '.$val->lookable->name;
                }
                else{
                    $keterangan = $val->lookable->code;
                }

                $response['data'][] = [
                    $val->mitra->name,
                    $lookable,
                    $keterangan,
                    $val->status(),
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Sync Data" onclick="syncData(`'.CustomHelper::encrypt($val->id).'`)"><i class="material-icons">sync</i></button>',
                    // '
                    //     <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-text btn-small" data-popup="tooltip" title="Document Relasi" onclick="documentRelation(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">device_hub</i></button>
                    //     <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                    //     <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                    // '
                ];
                $nomor++;
            }
        }

        $response['recordsTotal']    = ($total_data <> FALSE) ? $total_data : 0;
        $response['recordsFiltered'] = ($total_filtered <> FALSE) ? $total_filtered : 0;
        return response()->json($response); 
    }
    
    public function create(){
        //
    }

    public function syncData(Request $request){
        $id = CustomHelper::decrypt($request->id);

        $data = MitraApiSyncData::where('id', $id)->first();
        if($data->status == 1){
            Log::info('Data already sync');
        }

        if($data->operation == 'index'){
            Log::info('operation: index');
        }
        else if($data->operation == 'show'){
            Log::info('operation: show');
        }
        else if($data->operation == 'store'){
            $endpoint = MitraApiEndpoint::where('mitra_id', $data->mitra_id)
                            ->where('lookable_type', $data->lookable_type)
                            ->where('operation', $data->operation)->first();
            $mitra = User::where('id',$data->mitra_id)->first();
            
            // Log::info($id."-".$data->payload);
            //Call API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$mitra->token_mitra,
                'Accept'        => 'application/json',
            ])->post($endpoint->base_url.$endpoint->endpoint, json_decode($data->payload)); //ini yang benar
            Log::info($response->body());
            
            //Cek API berhasil / tidak
            if ($response->successful()){
                MitraApiSyncData::find($id)->increment('attempts');
                MitraApiSyncData::find($id)
                    ->update([
                        "status" => '1',
                        "api_response"=> json_encode(["status"=> $response->status(), "body" => $response->body()]),
                    ]);
                return $response->json();
            } else {
                MitraApiSyncData::find($id)->increment('attempts');
                MitraApiSyncData::find($id)
                    ->update([
                        "api_response"=> json_encode(["status"=> $response->status(), "body" => $response->body()]),
                    ]);
                return response()->json(['error' => $response->body()], $response->status());
            }
        }
        else if($data->operation == 'update'){
            $endpoint = MitraApiEndpoint::where('mitra_id', $data->mitra_id)
                            ->where('lookable_type', $data->lookable_type)
                            ->where('operation', $data->operation)->first();
            $mitra = User::where('id',$data->mitra_id)->first();

            //cek dulu apakah ada yang mitra_id, lookable_type, operation, idnya sama dengan status masih pending, kalau ada update payloadnya
            //salah, di atas harusnya dicek di model

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$mitra->token_mitra,
                'Accept'        => 'application/json',
            ])->put($endpoint->base_url.$endpoint->endpoint.base64_encode($data->code), json_decode($data->payload)); //not tested yet
        }
        else if($data->operation == 'delete'){
            Log::info('operation: delete');
        }
        else{
            Log::info('Operation not registered');
            return "Operation not registered";
        }
    }

    public function rowDetail(Request $request){
        $data   = MitraApiSyncData::where('id', CustomHelper::decrypt($request->id))->first();

        $string = '<table style="min-width:50%;max-width:50%;">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>'.$data->code.'</th>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <th>'.$data->name.'</th>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <th>'.$data->type.'</th>
                            </tr>
                            <tr>
                                <th>Broker</th>
                                <th>'.($data->mitra ? ($data->mitra->employee_no." - ".$data->mitra->name) : '' ).'</th>
                            </tr>
                        </thead>
                    </table>';

        return response()->json($string);
    }
    
    public function show(){
        //
    }

    public function edit(string $id){
        //
    }

    public function update(Request $request, string $id){
        //
    }
    
    public function destroy(string $id){
        //
    }
}
