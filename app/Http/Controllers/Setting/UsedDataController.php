<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\UsedData;
use Illuminate\Http\Request;

class UsedDataController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Approval',
            'content'   => 'admin.setting.used_data',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'lookable_type',
            'lookable_id',
            'ref',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UsedData::count();
        
        $query_data = UsedData::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('lookable', function ($query) use ($search) {
                        $query->where('code','like', "%$search%");
                    });
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = UsedData::where(function($query) use ($search, $request) {
                if($search) {
                    $query->whereHas('lookable', function ($query) use ($search) {
                        $query->where('code','like', "%$search%");
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
                    $val->user->name,
                    $val->lookable->code,
                    $val->lookable_type,
                    $val->ref,
                    $val->updated_at,
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
        $query = UsedData::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new UsedData())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Used Data');

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
}
