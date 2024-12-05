<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Exports\ExportReportProcurement;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\GoodScale;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\RuleBpScale;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;

class ReportProcurementController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Report Summary Stock Accounting',
            'content'   => 'admin.purchase.report_procurement',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->end_date ? $request->end_date : '';
        $item_id = $request->item_id ? $request->item_id : '';

		return Excel::download(new ExportReportProcurement($start_date,$finish_date,$item_id), 'report_procurement_'.uniqid().'.xlsx');
    }

    public function printIndividual(Request $request){

        $query_data = GoodScale::where('post_date', '>=',$request->start_date)
        ->where('post_date', '<=', $request->finish_date)
        ->where('item_id',$request->item_id)
        ->whereIn('status',["2","3"])
        ->get();

        $grouped_data = $query_data->groupBy('account_id');

        $limited_data = $grouped_data->map(function ($group) {
            return $group;
        });

        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d-m-Y_H-i-s');
        $zipFileName = "Production_Receives_$formattedDate.zip";
        $zipFilePath = storage_path("app/public/temp/$zipFileName");

        if (!is_dir(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0777, true);
        }

        if($query_data){
            $zip = new \ZipArchive;
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($limited_data as $k=>$row) {
                    $arr = [];
                    $no = 1;
                    $all_penerimaan = 0;
                    $all_finance_price = 0;
                    $account = '';
                    foreach($row as $detail_gs){
                        if($account== ''){
                            $account = $detail_gs->account->name;
                        }

                        $take_item_rule_percent = RuleBpScale::where('item_id',$request->item_id)->where('account_id',$detail_gs->purchaseOrderDetail->purchaseOrder->account_id)->first()->water_percent ?? 0;

                        $finance_kadar_air = 0;
                        $finance_kg = 0;
                        if($detail_gs->water_content > $take_item_rule_percent && $take_item_rule_percent != 0){
                            $finance_kadar_air = $detail_gs->water_content - $take_item_rule_percent;
                        }
                        if($finance_kadar_air > 0){
                            $finance_kg = ($finance_kadar_air*$detail_gs->qty_in) / 100;
                        }
                        $total_bayar = $detail_gs->qty_in;
                        if($finance_kadar_air > 0){
                            $total_bayar = $total_bayar-$finance_kadar_air;
                        }
                        $total_penerimaan = $detail_gs->qty_in - (1 - ($detail_gs->water_content/100));
                        $price = $detail_gs->purchaseOrderDetail->price;
                        $finance_price = $price*$total_bayar;


                        $all_penerimaan += $total_penerimaan;
                        $all_finance_price += $finance_price;



                        $arr[] = [
                            'no'                => $no,
                            'PLANT'=> $detail_gs->place->name,
                            'NO PO'=> $detail_gs->note,
                            'NAMA ITEM'=> $detail_gs->item->name,
                            'NO SJ'=> $detail_gs->delivery_no,
                            'TGL MASUK'=> date('d/m/Y',strtotime($detail_gs->post_date)),
                            'NO. KENDARAAN' =>$detail_gs->vehicle_no,
                            'NETTO JEMBATAN TIMBANG' =>number_format($detail_gs->qty_in,2,',','.'),
                            'HASIL QC' =>number_format($detail_gs->water_content,2,',','.'),
                            'STD POTONGAN QC' =>number_format($take_item_rule_percent,2,',','.'),
                            'FINANCE Kadar air' =>number_format($finance_kadar_air,2,',','.'),
                            'FINANCE Kg' =>number_format($finance_kg,2,',','.'),
                            'TOTAL BAYAR KG'=>number_format($total_bayar,2,',','.'),
                            'TOTAL PENERIMAAN'=>$total_penerimaan,
                            'HARGA PO'=>number_format($price,2,',','.'),
                            'HARGA FINANCE'=>number_format($finance_price,2,',','.'),
                            'HARGA OP/BBM'=>0,
                        ];
                    }

                    $avg = $all_finance_price / (($all_penerimaan != 0) ? $all_penerimaan : 1);

                    foreach ($arr as &$row_arr) {
                        $row_arr['HARGA OP/BBM'] = number_format($row_arr['TOTAL PENERIMAAN'] * $avg,2,',','.');
                        $row_arr['TOTAL PENERIMAAN'] = number_format($row_arr['TOTAL PENERIMAAN'],2,',','.');
                    }



                    $data = [
                        'title' => 'Report Procurement',
                        'data'  => $arr,
                        'supplier'  => $account,
                    ];
                    $pdf = PrintHelper::print($data,'Report Procurement','a4','portrait','admin.print.purchase.report_procurement','all');
                    $content = $pdf->download()->getOriginalContent();
                    $randomString = Str::random(10);


                    $filePath = 'public/pdf/' . $randomString . '.pdf';

                    Storage::put($filePath, $content);

                    $absoluteFilePath = storage_path('app/' . $filePath);
                    $document_po = asset(Storage::url($filePath));
                    $pdfFileName = "Report_Procurement_Account_$k.pdf";
                    info($absoluteFilePath);
                    $zip->addFromString($pdfFileName,$content);
                }
                $zip->close();
                $response =[
                    'status'=>200,
                    'message'  =>url('/storage/temp/'.$zipFileName)
                ];
                return response()->json($response);
            }
        }else{
            abort(404);
        }
    }
}
