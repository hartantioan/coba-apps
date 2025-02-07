<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use App\Models\MitraPriceList;
use App\Models\User;

use App\Exports\ExportTemplateMasterMitraPriceList;
use App\Imports\ImportMitraPriceList;
use App\Exports\ExportMitraPriceList;

use function PHPSTORM_META\map;

class MitraPriceListController extends Controller
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
            'title'   => 'Price List Mitra',
            'content' => 'admin.master_data.mitra_price_list',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'price_group_code',
            'sales_area_code',
            'variety',
            'type',
            'package',
            'effective_date',
            'uom',
            'min_qty',
            'price_exclude',
            'price_include',
            'mitra',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MitraPriceList::count();

        $query_builder = MitraPriceList::where(function($query) use ($search, $request){
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
                    $val->price_group_code,
                    $val->sales_area_code,
                    $val->variety->name,
                    $val->type->name,
                    $val->package->prefix_code,
                    $val->effective_date,
                    $val->uom->name,
                    $val->min_qty,
                    $val->price_exclude,
                    $val->price_include,
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

    public function create(Request $request){
        //
    }

    public function store(Request $request){
        //
    }

    public function show(string $id){
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

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        // $type   = $request->type ? $request->type : '';
        $broker = $request->broker ? $request->broker : '';

		return Excel::download(new ExportMitraPriceList($search, $status, $broker), 'mitra_price_list_'.uniqid().'.xlsx');
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterMitraPriceList(), 'format_master_mitra_price_list'.uniqid().'.xlsx');
    }

    public function import(Request $request){
        Excel::import(new ImportMitraPriceList, $request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
    }
}
