<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportTemplateToleranceScale;
use App\Imports\ImportToleranceScale;
use App\Models\ToleranceScale;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exceptions\RowImportException;
use App\Exports\ExportItemWeight;
use App\Exports\ExportTemplateItemWeight;
use App\Imports\ImportItemWeight;

class ToleranceScaleController extends Controller
{
    public function index()
    {
        $data = [
            'title'             => 'Toleransi Timbang',
            'content'           => 'admin.master_data.tolerance_scale',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'item_id',
            'percentage',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $data = ToleranceScale::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->whereHas('item',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }
        });
        $total_data = ToleranceScale::count();
        $query_data = $data->offset($start)->limit($length)->orderBy($order, $dir)->get();
        $total_filtered = $data->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    $nomor,
                    $val->user->name,
                    $val->item->code.' - '.$val->item->name,
                    number_format($val->percentage,3,',','.'),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
            'item_id'               => 'required',
            'percentage'            => 'required',
        ], [
            'item_id.required'      => 'Item tidak boleh kosong.',
            'percentage.required'   => 'Prosentase tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            DB::beginTransaction();
            try {
                $query = ToleranceScale::where('item_id',$request->item_id)->first();
                if($query){
                    $query->user_id             = session('bo_id');
                    $query->item_id             = $request->item_id;
                    $query->percentage          = str_replace(',','.',str_replace('.','',$request->percentage));
                    $query->save();
                }else{
                    $query = ToleranceScale::create([
                        'user_id'                   => session('bo_id'),
                        'item_id'			        => $request->item_id,
                        'percentage'                => str_replace(',','.',str_replace('.','',$request->percentage)),
                    ]);
                }

                if($query) {

                    activity()
                        ->performedOn(new ToleranceScale())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit Tolerance Scale.');

                    $response = [
                        'status'  => 200,
                        'message' => 'Data successfully saved.'
                    ];
                } else {
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

    public function show(Request $request){
        $dc = ToleranceScale::find($request->id);
        $dc['item_name'] = $dc->item->name;
        $dc['item_code'] = $dc->item->code;
        $dc['percentage'] = number_format($dc->percentage,2,',','.');
		return response()->json($dc);
    }

    public function destroy(Request $request){
        $query = ToleranceScale::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new ToleranceScale())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the tolerance scale');

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

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportToleranceScale, $request->file('file'));
            return response()->json(['status' => 200, 'message' => 'Import successful']);
        } catch (RowImportException $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
                'row' => $e->getRowNumber(),
                'column' => $e->getColumn(),
                'sheet' => $e->getSheet(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateToleranceScale(), 'format_template_tolerance_scale_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';

		return Excel::download(new ExportItemWeight($search), 'item_weight_'.uniqid().'.xlsx');
    }
}
