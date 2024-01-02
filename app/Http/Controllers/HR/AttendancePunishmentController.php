<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendancePunishment;
use App\Models\User;
use Illuminate\Http\Request;

class AttendancePunishmentController extends Controller
{
    public function index(Request $request){
        $data = [
            'title'         => 'Laporan Keterlambatan',
            'user'          =>  User::where('type','1')->where('status',1)->get(),
            'content'       => 'admin.hr.attendance_punishment',
        ];

        return view('admin.layouts.index', ['data' => $data]); 
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'employee_id',
            'period_id',
            'punishment_id',
            'type',
            'frequent',
            'total',
            'dates',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = AttendancePunishment::count();
        
        $query_data = AttendancePunishment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('dates', 'like', "%$search%")
                        ->orWhereHas('employee', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('period', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });;
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = AttendancePunishment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('dates', 'like', "%$search%")
                        ->orWhereHas('employee', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('period', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('punishment', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                ' <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    $nomor,
                    $val->employee->name ?? '-',
                    $val->period->name ?? '-',
                    $val->punishment->name,
                    $val->frequent,
                    $val->dates,
                    $val->total,
                    $btn
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
