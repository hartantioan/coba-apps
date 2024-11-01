<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\ItemWeightFg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exceptions\RowImportException;
use App\Exports\ExportItemWeight;
use App\Exports\ExportTemplateItemWeight;
use App\Imports\ImportItemWeight;

class ItemWeightController extends Controller
{
    public function index()
    {
        $data = [
            'title'             => 'Berat Item FG',
            'content'           => 'admin.master_data.item_weight',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'item_id',
            'gross_weight',
            'netto_weight',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ItemWeightFg::count();

        $query_data = ItemWeightFg::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->orWhere('code','like',"%$search%")
                    ->orWhereHas('item',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }
        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();

        $total_filtered = ItemWeightFg::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->orWhere('code','like',"%$search%")
                    ->orWhereHas('item',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }
        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->item->name,
                    number_format($val->gross_weight,3,',','.'),
                    number_format($val->netto_weight,2,',','.'),
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
        info($request);
        $validation = Validator::make($request->all(), [
            'item_id'               => 'required',
            'gross_weight'            => 'required',
            'netto_weight'              => 'required',
        ], [
            'item_id.required'                 => 'Nama tidak boleh kosong.',
            'gross_weight.required'           => 'Gross Weight Tidak boleh kosong.',
            'netto_weight.required'             => 'Berat Netto Tidak Boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            DB::beginTransaction();
            try {

                if($request->temp){
                    $query = ItemWeightFg::find($request->temp);
                    $query->user_id             = session('bo_id');
                    $query->item_id             = $request->item_id;
                    $query->netto_weight        = $request->netto_weight;
                    $query->gross_weight	    = $request->gross_weight;
                    $query->save();
                }else{
                    $query = ItemWeightFg::create([
                        'code'                      => strtoupper(Str::random(15)),
                        'user_id'                   => session('bo_id'),
                        'netto_weight'              => $request->netto_weight,
                        'gross_weight'              => $request->gross_weight,
                        'item_id'			        => $request->item_id,

                    ]);
                }

                if($query) {

                    activity()
                        ->performedOn(new ItemWeightFg())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit Item Weight.');

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
        $dc = ItemWeightFg::find($request->id);
        $dc['item'] = $dc->item;

		return response()->json($dc);
    }

    public function destroy(Request $request){
        $query = ItemWeightFg::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new ItemWeightFg())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the delivery cost');

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
            Excel::import(new ImportItemWeight, $request->file('file'));
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
        return Excel::download(new ExportTemplateItemWeight(), 'format_template_item_weight_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';

		return Excel::download(new ExportItemWeight($search), 'item_weight_'.uniqid().'.xlsx');
    }
}
