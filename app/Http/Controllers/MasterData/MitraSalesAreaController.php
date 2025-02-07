<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use App\Models\MitraSalesArea;
use App\Models\User;

use App\Exports\ExportTemplateMasterMitraSalesArea;
use App\Imports\ImportMitraSalesArea;
use App\Exports\ExportMitraSalesArea;


class MitraSalesAreaController extends Controller
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
            'title'   => 'Sales Area',
            'content' => 'admin.master_data.mitra_sales_area',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'type',
            'mitra',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MitraSalesArea::count();

        $query_builder = MitraSalesArea::where(function($query) use ($search, $request){
            if($search){
                $query->where(function($query) use ($search, $request){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('type', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
                });
            }

            if($request->status){
                $query->where('status', $request->status);
            }
        });

        $total_filtered = $query_builder->count();
        $query_data = $query_builder->offset($start)->limit($length)
                                    ->orderBy($order, $dir)
                                    ->get();

        $response['data']=[];
        if($query_data <> FALSE){
            $nomor = $start + 1;
            foreach($query_data as $val){
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->name,
                    $val->type,
                    $val->mitra->name,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-text btn-small" data-popup="tooltip" title="Document Relasi" onclick="documentRelation(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">device_hub</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                    '
                ];
                $nomor++;
            }
        }

        $response['recordsTotal']    = ($total_data <> FALSE) ? $total_data : 0;
        $response['recordsFiltered'] = ($total_filtered <> FALSE) ? $total_filtered : 0;
        return response()->json($response); 
    }
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'     => 'required',
            'name'     => 'required',
            'type'     => 'required',
            'mitra_id' => 'required',
            'status'   => 'required',
        ], [
            'code.required'   => 'Kode tidak boleh kosong',
            'name.required'   => 'Nama tidak boleh kosong',
            'type.required'   => 'Type tidak boleh kosong',
            'status.required' => 'Status tidak boleh kosong',
        ]);

    }

    public function rowDetail(Request $request){
        $data   = MitraSalesArea::where('code', CustomHelper::decrypt($request->id))->first();

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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $type   = $request->type ? $request->type : '';
        $broker = $request->broker ? $request->broker : '';

		return Excel::download(new ExportMitraSalesArea($search, $status, $type, $broker), 'mitra_sales_area_'.uniqid().'.xlsx');
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterMitraSalesArea(), 'format_master_mitra_sales_area'.uniqid().'.xlsx');
    }

    public function import(Request $request){
        Excel::import(new ImportMitraSalesArea, $request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
    }
}
