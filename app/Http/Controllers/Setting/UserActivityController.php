<?php

namespace App\Http\Controllers\Setting;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Approval;

class UserActivityController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Aktivitas User',
            'content'   => 'admin.setting.user_activity',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'approval_id',
            'level',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ApprovalStage::count();
        
        $query_data = ApprovalStage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('approvalStageDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ApprovalStage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('approvalStageDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->approval->name.' - '.$val->approval->document_text,
                    $val->level,
                    $val->min_approve,
                    $val->min_reject,
                    $val->status(),
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
}