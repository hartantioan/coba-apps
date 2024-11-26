<?php

namespace App\Http\Controllers\Finance;

use App\Exports\ExportBalanceBsEmployee;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\FundRequest;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportEmployeeReceivable;
use Maatwebsite\Excel\Facades\Excel;

class BalanceBsEmployeeController extends Controller
{
    protected $dataplaces, $datawarehouses, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Sisa Piutang Karyawan',
            'content'   => 'admin.finance.balance_bs_employee',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){
        
        $start_time = microtime(true);

        $data = User::where('type','1')->where('status','1')->orderBy('employee_no')->get();

        $results = [];

        foreach($data as $row){
            $results[] = [
                'code'          => $row->employee_no,
                'name'          => $row->name,
                'limit'         => number_format($row->limit_credit,2,',','.'),
                'usage'         => number_format($row->count_limit_credit,2,',','.'),
                'balance'       => number_format($row->limit_credit - $row->count_limit_credit,2,',','.'),
            ];
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $results,
            'execution_time'    => $execution_time,
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportBalanceBsEmployee(), 'balance_bs_employee'.uniqid().'.xlsx');
    }
}