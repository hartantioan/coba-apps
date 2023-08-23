<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceMachine;
use App\Models\Attendances;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        foreach(AttendanceMachine::where('status','1')->get() as $machine){
            $output = [];
            $exitCode = 0;
            $command = "node C:\Users\windy\absen2\logCount.js " . $machine->ip_address.' '.$machine->id;
            exec($command, $output, $exitCode);            
        }

        $data = [
            'title'         => 'Jadwal Pegawai',
            'content'       => 'admin.hr.attendance',
            'machine'       => AttendanceMachine::where('status','1')->get(),
        ];
        
        return view('admin.layouts.index', ['data' => $data]); 
    }

    public function syncron(Request $request)
    {
        $startTime = microtime(true);
        $ipAddresses = $request->ip_address;
        $id_machine = $request->id_machines;
        $ipAddressesComma = implode(',', $ipAddresses);
        $id_machineComma = implode(',', $id_machine);
       
        /* $command = "node D:\\\\absen_node\\\\testComma.js " . $ipAddressesComma.' '.$id_machineComma; */
        $command = "node C:\Users\windy\absen2\test_new.js " . $ipAddressesComma.' '.$id_machineComma;
        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);
           
        $endTime = microtime(true);
        $executionTimeSec = ($endTime - $startTime); // Execution time in seconds
        $executionTimeMin = floor($executionTimeSec / 60); // Minutes
        $executionTimeSec %= 60;
        return response()->json([
            'controllerExecutionTimeMin' => $executionTimeMin, // Controller execution time in minutes
            'controllerExecutionTimeSec' => $executionTimeSec,
            'status'=>'200']);
        
    }

    // public function syncron(Request $request)
    // {
    //     $ipAddresses = $request->ip_address;
    //     $id_machine = $request->id_machines;

    //     $success = [];
    //     $fail = [];
    //     foreach($ipAddresses as $key=> $row_ip){
    //         $command = "node D:\\\\absen_node\\\\testAb.js " . $row_ip.' '.$id_machine[$key];
    //         $output = [];
    //         $exitCode = 0;
    //         exec($command, $output, $exitCode);
    //         if ($exitCode === 0) {
    //             $success[] = $row_ip;
    //         } else {
    //             $fail[] = $row_ip;
    //         }
            
    //     }
    //     $ipAddressesSucc = implode(' ', $success);
    //     $ipAddressesFail = implode(' ', $fail);
    //     return response()->json(['success' => 'Berhasil mengambil data dari mesin dengan IP Address:'.$ipAddressesSucc, 'fail' => 'Gagal mengambil data dari mesin dengan ipAddress: '.$ipAddressesFail,'status'=>'200']);
        
    // }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'employee_no',
            'date',
            'verify_type',
            'location',
            'latitude',
            'longitude'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Attendances::count();
        
        $query_data = Attendances::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
                            ->orWhere('employee_no','like',"%$search%");
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

        $total_filtered = Attendances::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('date', 'like', "%$search%")
                        ->orWhere('employee_no','like',"%$search%");
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
                    $nomor,
                    $val->code,
                    $val->employee_no,
                    $val->employee_no,
                    $val->date,
                    $val->verifyType(),
                    $val->location,
                    $val->latitude,
                    $val->longitude,
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
