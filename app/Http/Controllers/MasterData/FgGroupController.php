<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportGroupFG;
use App\Http\Controllers\Controller;
use App\Imports\ImportFgGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\FgGroup;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class FgGroupController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Grup Item FG',
            'content'   => 'admin.master_data.fg_group',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'parent_id',
            'item_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = FgGroup::count();

        $query_data = FgGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('parent',function($query)use($search,$request){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        })->orWhereHas('item',function($query)use($search,$request){
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

        $total_filtered = FgGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('parent',function($query)use($search,$request){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        })->orWhereHas('item',function($query)use($search,$request){
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
                    $val->id,
                    $val->parent->code.' - '.$val->parent->name,
                    $val->item->code.' - '.$val->item->name,
                    '
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

    public function destroy(Request $request){
        $query = FgGroup::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new FgGroup())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the fg group data');

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
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportFgGroup, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);

        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
            ];
            return response()->json($response);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';

		return Excel::download(new ExportGroupFG($search), 'bom_map_'.uniqid().'.xlsx');
    }
}
