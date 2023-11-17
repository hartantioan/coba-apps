<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Imports\ImportAttendance;
use App\Jobs\ProcessAttendanceJob;
use App\Models\AttendanceMachine;
use App\Models\Attendances;
use App\Models\Jobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        foreach(AttendanceMachine::where('status','1')->get() as $machine){
            $output = [];
            $exitCode = 0;
            $command = "node D:\absen_node\logCount.js " . $machine->ip_address.' '.$machine->id;
            exec($command, $output, $exitCode);            
        }

        $data = [
            'title'         => 'Jadwal Pegawai',
            'content'       => 'admin.hr.attendance',
            'machine'       => AttendanceMachine::where('status','1')->get(),
        ];
        $total_count = 0;
        foreach($data['machine'] as $row){
            $total_count+=$row->log_counts;
        }
        $data['total_count']=$total_count;
        return view('admin.layouts.index', ['data' => $data]); 
    }



    public function syncron(Request $request)
    {
        $startTime = microtime(true);
        $ipAddresses = $request->ip_address;
        $id_machine = $request->id_machines;
        $ipAddressesComma = implode(',', $ipAddresses);
        $id_machineComma = implode(',', $id_machine);
        ProcessAttendanceJob::dispatch($ipAddressesComma, $id_machineComma);
 
        $endTime = microtime(true);
        $executionTimeSec = ($endTime - $startTime); // Execution time in seconds
        $executionTimeMin = floor($executionTimeSec / 60); // Minutes
        $executionTimeSec %= 60;
        return response()->json([
          
            'controllerExecutionTimeMin' => $executionTimeMin, // Controller execution time in minutes
            'controllerExecutionTimeSec' => $executionTimeSec,
            'status'=>'200']);
        
    }

    public function checkJobStatus(Request $request,$jobId)
    {
        $job = Jobs::find($jobId); // Replace with the actual model representing your jobs table
        

        if ($job->hasBeenProcessed()) {
            return response()->json(['status' => 'done']);
        }

        return response()->json(['status' => 'processing']);
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
                    $val->user->name ?? $val->employee_no,
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

    public function import(Request $request){
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
            Excel::import(new ImportAttendance, $request->file('file'));

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
}
