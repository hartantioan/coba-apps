<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\FundRequest;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Place;
use App\Models\ItemGroup;
use App\Models\ItemShading;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use App\Exports\ExportReportFundRequestOutgoing;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportFundRequestOutgoingController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
        $data = [
            'title'     => 'Report Fund Request To Outgoing',
            'content'   => 'admin.finance.report_freq_to_outgoing',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $data = FundRequest::where(function($query) use($request) {
            $query->where('post_date', '>=',$request->start_date)
            ->where('post_date', '<=', $request->end_date);
        })
        ->get();
        $arr = [];
        foreach($data as $key => $row){
            foreach($row->fundRequestDetail as $rowDetail){
                $arr[] = [
                    'no'            => ($key + 1),
                    'no_dokumen'    => $row->code,
                    'status'        => $row->statusRaw(),
                    'voider'        => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'     => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'     => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'       => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'   => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'   => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'name'          => $row->user->name,
                    'post_date'     => $row->post_date,
                    'required_date' => $row->required_date,
                    'bussiness_partner'  => $row->account->name,
                    'type'          => $row->type(),
                    'division'      => $row->division()->exists() ? $row->division->name : '',
                    'company_id'    => $row->company->name,
                    'note'          => $row->note,
                    'termin_note'   => $row->termin_note,
                    'payment_type'  => $row->paymentType(),
                    'no_account'    => $row->no_account,
                    'name_account'  => $row->name_account,
                    'bank_account'  => $row->bank_account,
                    'dekripsi'      => $rowDetail->note,
                    'qty'           => CustomHelper::formatConditionalQty($rowDetail->qty),
                    'unit'          => $rowDetail->unit->name,
                    'harga'         => $rowDetail->price,
                    'subtotal'      => $rowDetail->total,
                    'ppn'           => $rowDetail->tax,
                    'pph'           => $rowDetail->wtax,
                    'grandtotal'    => $rowDetail->grandtotal,
                    'no_preq'       => $row->getPaymentRequestAllCode(),
                    'no_opym'       => $row->getOPYMAllCode(),
                    'tgl_bayar'     => $row->getOPYMAllPayDate(),


                ];

            }

        }


        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$arr,
            'time'  => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }

    public function export(Request $request){
        $start_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		return Excel::download(new ExportReportFundRequestOutgoing($start_date,$end_date), 'export_freq_to_op_'.uniqid().'.xlsx');
    }
}
