<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Exports\ExportReportProcurement;
use App\Exports\ExportTransportService;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\GoodReceiptDetail;
use App\Models\GoodScale;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\RuleBpScale;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use iio\libmergepdf\Merger;
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
            'title'     => 'Report Procurement RM SM',
            'content'   => 'admin.purchase.report_procurement',
            'shading'      => ItemShading::get(),
            'item'      => Item::where('status','1')
            ->whereHas('itemGroup',function($query){
                $query->whereHas('itemGroupWarehouse',function($query){
                    $query->whereIn('warehouse_id',['2','3']);
                });
            })
            ->get(),
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

    public function exportTransportService(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->end_date ? $request->end_date : '';
        $item_id = $request->item_id ? $request->item_id : '';

		return Excel::download(new ExportTransportService($start_date,$finish_date,$item_id), 'report_jasa_angkut_'.uniqid().'.xlsx');
    }

    public function printIndividual(Request $request){
        $type='';
        $item = Item::find($request->item_id);
        $itemGroup = $item->itemGroup;
        foreach($itemGroup->itemGroupWarehouse as $row){
            if($type == ''){
                $type = $row->warehouse_id;
            }
        }
        if($type == 2){
            $query_data = GoodReceiptDetail::whereHas('goodScale', function ($querys) use($request) {
                $querys->where('post_date', '>=',$request->start_date)
                ->where('post_date', '<=', $request->finish_date)
                ->where('item_id',$request->item_id)
                ->whereIn('status',["2","3"]);
            })->whereHas('goodReceipt', function ($querysd) {
                $querysd
                ->whereIn('status',["2","3","9"]);
            })->get();
            $queryWithoutGoodScale = GoodReceiptDetail::doesntHave('goodScale')
            ->whereHas('goodReceipt', function ($querysd) use($request) {
                $querysd->where('post_date', '>=',$request->start_date)
                ->where('post_date', '<=', $request->finish_date)
                ->whereIn('status',["2","3","9"]);
            })
            ->where('item_id',$request->item_id)->get();
            $query_data = $query_data->merge($queryWithoutGoodScale);
        }
        if($type == 3){
            $query_data = GoodReceiptDetail::whereHas('goodReceipt', function ($querysda) use($request) {
                $querysda->where('post_date', '>=', $request->start_date)
                    ->where('post_date', '<=', $request->finish_date)
                    ->whereIn('status',["2","3","9"]);
            })
            ->where('item_id',$request->item_id)->get();

        }

        $grouped_data = $query_data->groupBy(function($detail) {
            return $detail->goodReceipt->account_id;
        });

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
                    $all_bayar = 0;
                    $all_finance_price = 0;
                    $all_netto= 0;
                    $account = '';
                    $satuan = '';
                    if($type == 2){
                        foreach($row as $detail_gs){
                            if($account== ''){
                                $account = $detail_gs->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $detail_gs->itemUnit->unit->code;
                            }
                            if($detail_gs->goodScale()->exists()){

                                $take_item_rule_percent = RuleBpScale::where('item_id',$detail_gs->goodScale->item_id)
                                ->whereDate('start_effective_date','<=',$detail_gs->goodScale->post_date)
                                ->whereDate('effective_date','>=',$detail_gs->goodScale->post_date)
                                ->where('account_id',$detail_gs->goodScale->account_id)->first();
                            }else{
                                $take_item_rule_percent = RuleBpScale::where('item_id',$request->item_id)
                                ->whereDate('start_effective_date','<=',$detail_gs->goodReceipt->post_date)
                                ->whereDate('effective_date','>=',$detail_gs->goodReceipt->post_date)
                                ->where('account_id',$detail_gs->goodReceipt->account_id)->first();
                            }
                            $percentage_level = 0;
                            $percentage_netto_limit = 0;
                            $finance_kadar_air = 0;
                            $finance_kg = 0;
                            $real_balance=0;
                            if($take_item_rule_percent){
                                $percentage_level = round($take_item_rule_percent->percentage_level,2);
                                $percentage_netto_limit = round($take_item_rule_percent->percentage_netto_limit,2);
                            }
                            if (Carbon::parse($request->start_date)->greaterThan(Carbon::create(2025, 1, 1))) {
                                if($detail_gs->goodScale()->exists()){
                                    if($detail_gs->goodScale->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    $real_balance = (($detail_gs->qty/$detail_gs->goodReceipt->getTotalQty())*$detail_gs->goodScale->qty_balance);
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 )*$real_balance;
                                    }
                                    $total_bayar = $detail_gs->qty_balance;

                                    $total_penerimaan = $real_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->goodScale->purchaseOrderDetail->price;
                                    $all_netto += $real_balance;
                                    $finance_price = $price*$total_bayar;
                                }else{

                                    $real_balance=$detail_gs->qty_balance;
                                    if($detail_gs->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->qty);
                                    }
                                    $total_bayar = $detail_gs->qty_balance;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $detail_gs->qty_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->purchaseOrderDetail->price;
                                    $finance_price = $price*$total_bayar;
                                    $all_netto += $detail_gs->qty;
                                }
                            }else{
                                if($detail_gs->goodScale()->exists()){
                                    $real_balance=$detail_gs->goodScale->qty_balance;
                                    if($detail_gs->goodScale->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->goodScale->qty_balance);
                                    }
                                    $total_bayar = $detail_gs->goodScale->qty_balance;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $detail_gs->goodScale->qty_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->goodScale->purchaseOrderDetail->price;
                                    $all_netto += $detail_gs->goodScale->qty_balance;

                                    $finance_price = $price*$total_bayar;
                                }else{
                                    $real_balance=$detail_gs->qty_balance;
                                    if($detail_gs->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->qty);
                                    }
                                    $total_bayar = $detail_gs->qty_balance;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $detail_gs->qty_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->purchaseOrderDetail->price;
                                    $finance_price = $price*$total_bayar;
                                    $all_netto += $detail_gs->qty;
                                }



                            }

                            $all_penerimaan += $total_penerimaan;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $detail_gs->place->code,
                                'NO PO'=> $detail_gs->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $detail_gs->item->name,
                                'NO SJ'=> $detail_gs->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($detail_gs->goodScale->post_date?? $detail_gs->goodReceipt->post_date)),
                                'NO. KENDARAAN' =>$detail_gs->goodScale->vehicle_no ?? $detail_gs->goodReceipt->vehicle_no,
                                'NETTO JEMBATAN TIMBANG' =>number_format($real_balance ?? $detail_gs->qty_balance,2,',','.'),
                                'HASIL QC' =>number_format($detail_gs->water_content,2,',','.'),
                                'STD POTONGAN QC' =>number_format($percentage_level,2,',','.'),
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement','all');
                        $content = $pdf->download()->getOriginalContent();
                        $randomString = Str::random(10);
                    }else{
                        foreach($row as $row_2){
                            $take_item_rule_percent = RuleBpScale::where('item_id',$request->item_id)
                            ->whereDate('start_effective_date','<=',$row_2->goodReceipt->post_date)
                            ->whereDate('effective_date','>=',$row_2->goodReceipt->post_date)
                            ->where('account_id',$row_2->goodReceipt->account_id)->first();
                            $netto_sj = 0;
                            $selisih = 0;
                            if($account== ''){
                                $account = $row_2->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $row_2->itemUnit->unit->code;
                            }
                            if($row_2->goodScale()->exists()){
                                $netto_sj = $row_2->goodScale->qty_sj;

                            }else{
                                $netto_sj = $row_2->qty_sj;
                            }
                            if($netto_sj > 0){
                                $selisih = $row_2->qty - $netto_sj;
                            }
                            if($take_item_rule_percent->rule_procurement_id == 3){
                                if($row_2->qty<$row_2->qty_sj){
                                    $total_bayar = $row_2->qty;
                                }else{
                                    $total_bayar = $row_2->qty_sj;
                                }
                            }else{
                                $total_bayar = $row_2->qty_balance;
                            }
                            $price = $row_2->purchaseOrderDetail->price;
                            $finance_price = $price*$total_bayar;


                            $all_penerimaan += $total_bayar;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;
                            $all_netto +=$total_bayar;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $row_2->place->name,
                                'NO PO'=> $row_2->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $row_2->item->name,
                                'NO SJ'=> $row_2->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($row_2->goodReceipt->post_date)),
                                'NO. KENDARAAN' =>$row_2?->goodScale->vehicle_no ?? '-',
                                'NETTO SJ'=>number_format($netto_sj,2,',','.'),
                                'NETTO SPS'=>number_format($row_2->qty,2,',','.'),
                                'SELISIH'=>number_format($selisih,2,',','.'),
                                'TOTAL BAYAR'=>number_format($total_bayar,2,',','.'),
                                'TOTAL PENERIMAAN'=>$total_bayar,
                                'HARGA PO'=>$price,
                                'HARGA FINANCE'=>$finance_price,
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement_sm','all');
                        $content = $pdf->download()->getOriginalContent();

                        $randomString = Str::random(10);
                    }


                    $filePath = 'public/pdf/' . $randomString . '.pdf';

                    Storage::put($filePath, $content);

                    $absoluteFilePath = storage_path('app/' . $filePath);
                    $document_po = asset(Storage::url($filePath));
                    $pdfFileName = "Report_Procurement_Account_$k.pdf";
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

    public function printIndividualSupplier(Request $request){
        $type='';
        $supplier = User::find($request->account_id);
        // $itemGroup = $item->itemGroup;
        // foreach($itemGroup->itemGroupWarehouse as $row){
        //     if($type == ''){
        //         $type = $row->warehouse_id;
        //     }
        // }
        $query_data = GoodReceiptDetail::whereHas('goodScale', function ($querys) use($request) {
            $querys->where('post_date', '>=',$request->start_date)
            ->where('post_date', '<=', $request->finish_date)
            ->where('account_id',$request->account_id)
            ->whereIn('status',["2","3"]);
        })->whereHas('goodReceipt', function ($querysd) {
            $querysd
            ->whereIn('status',["2","3","9"]);
        })->get();
        $queryWithoutGoodScale = GoodReceiptDetail::doesntHave('goodScale')
        ->whereHas('goodReceipt', function ($querysd) use($request) {
            $querysd->where('post_date', '>=',$request->start_date)
            ->where('post_date', '<=', $request->finish_date)
            ->where('account_id',$request->account_id)
            ->whereIn('status',["2","3","9"]);
        })->get();
        $query_data = $query_data->merge($queryWithoutGoodScale);

        $grouped_data = $query_data->groupBy(function($detail) {
            return $detail->goodReceipt->account_id;
        });

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
                    $all_bayar = 0;
                    $all_finance_price = 0;
                    $all_netto= 0;
                    $account = '';
                    $satuan = '';
                    if($type == 2){
                        foreach($row as $detail_gs){
                            if($account== ''){
                                $account = $detail_gs->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $detail_gs->itemUnit->unit->code;
                            }

                            $take_item_rule_percent = RuleBpScale::where('item_id',$detail_gs->goodScale->item_id)
                            ->whereDate('start_effective_date','<=',$detail_gs->goodScale->post_date)
                            ->whereDate('effective_date','>=',$detail_gs->goodScale->post_date)
                            ->where('account_id',$detail_gs->goodScale->account_id)->first();
                            $percentage_level = 0;
                            $percentage_netto_limit = 0;
                            $finance_kadar_air = 0;
                            $finance_kg = 0;
                            if($take_item_rule_percent){
                                $percentage_level = round($take_item_rule_percent->percentage_level,2);
                                $percentage_netto_limit = round($take_item_rule_percent->percentage_netto_limit,2);
                            }
                            if($detail_gs->goodScale->water_content > $percentage_level && $percentage_level != 0){
                                $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                            }
                            if($finance_kadar_air > 0){
                                $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->goodScale->qty_balance);
                            }
                            $total_bayar = $detail_gs->goodScale->qty_balance;
                            if($finance_kadar_air > 0){
                                $total_bayar = $total_bayar-$finance_kg;
                            }
                            $total_penerimaan = $detail_gs->goodScale->qty_balance * (1 - ($detail_gs->water_content/100));
                            $price = $detail_gs->goodScale->purchaseOrderDetail->price;
                            $finance_price = $price*$total_bayar;


                            $all_penerimaan += $total_penerimaan;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;
                            $all_netto += $detail_gs->goodScale->qty_balance;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $detail_gs->place->code,
                                'NO PO'=> $detail_gs->goodScale->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $detail_gs->goodScale->item->name,
                                'NO SJ'=> $detail_gs->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($detail_gs->goodScale->post_date)),
                                'NO. KENDARAAN' =>$detail_gs->goodScale->vehicle_no,
                                'NETTO JEMBATAN TIMBANG' =>number_format($detail_gs->goodScale->qty_balance,2,',','.'),
                                'HASIL QC' =>number_format($detail_gs->water_content,2,',','.'),
                                'STD POTONGAN QC' =>number_format($percentage_level,2,',','.'),
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement','all');
                        $content = $pdf->download()->getOriginalContent();
                        $randomString = Str::random(10);
                    }else{
                        foreach($row as $row_2){
                            $netto_sj = 0;
                            $selisih = 0;
                            if($account== ''){
                                $account = $row_2->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $row_2->itemUnit->unit->code;
                            }
                            if($row_2->goodScale()->exists()){
                                $netto_sj = $row_2->goodScale->qty_balance;

                            }
                            if($netto_sj > 0){
                                $selisih = $row_2->qty - $netto_sj;
                            }
                            $total_bayar = $row_2->qty;
                            $price = $row_2->purchaseOrderDetail->price;
                            $finance_price = $price*$total_bayar;


                            $all_penerimaan += $total_bayar;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;
                            $all_netto +=$total_bayar;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $row_2->place->name,
                                'NO PO'=> $row_2->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $row_2->item->name,
                                'NO SJ'=> $row_2->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($row_2->goodReceipt->post_date)),
                                'NO. KENDARAAN' =>$row_2?->goodScale->vehicle_no ?? '-',
                                'NETTO SJ'=>number_format($netto_sj,2,',','.'),
                                'NETTO SPS'=>number_format($total_bayar,2,',','.'),
                                'SELISIH'=>number_format($selisih,2,',','.'),
                                'TOTAL BAYAR'=>number_format($total_bayar,2,',','.'),
                                'TOTAL PENERIMAAN'=>$total_bayar,
                                'HARGA PO'=>$price,
                                'HARGA FINANCE'=>$finance_price,
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement_sm','all');
                        $content = $pdf->download()->getOriginalContent();

                        $randomString = Str::random(10);
                    }


                    $filePath = 'public/pdf/' . $randomString . '.pdf';

                    Storage::put($filePath, $content);

                    $absoluteFilePath = storage_path('app/' . $filePath);
                    $document_po = asset(Storage::url($filePath));
                    $pdfFileName = "Report_Procurement_Account_$k.pdf";
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

    public function printMultiItemPDF(Request $request){
        $array_item = [];
        $pdfFileNameArray = [];
        $contentArray = [];
        if($request->item_multi){
            $array_item = explode(',', $request->item_multi);
        }
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d-m-Y_H-i-s');
        $zipFileName = "Production_Receives_$formattedDate.zip";
        $zipFilePath = storage_path("app/public/temp/$zipFileName");
        $zip = new \ZipArchive;

        if (!is_dir(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0777, true);
        }
        $temp_pdf = [];
        foreach($array_item as $row_item){
            $type='';
            $item = Item::find($row_item);
            $itemGroup = $item->itemGroup;
            foreach($itemGroup->itemGroupWarehouse as $row){
                if($type == ''){
                    $type = $row->warehouse_id;
                }
            }
            if($type == 2){
                $query_data = GoodReceiptDetail::whereHas('goodScale', function ($querys) use($request,$row_item) {
                    $querys->where('post_date', '>=',$request->start_date)
                    ->where('post_date', '<=', $request->finish_date)
                    ->where('item_id',$row_item)
                    ->whereIn('status',["2","3"]);
                })->whereHas('goodReceipt', function ($querysd) {
                    $querysd
                    ->whereIn('status',["2","3","9"]);
                })->get();
                $queryWithoutGoodScale = GoodReceiptDetail::doesntHave('goodScale')
                ->whereHas('goodReceipt', function ($querysd) use($request) {
                    $querysd->where('post_date', '>=',$request->start_date)
                    ->where('post_date', '<=', $request->finish_date)
                    ->whereIn('status',["2","3","9"]);
                })
                ->where('item_id',$row_item)->get();
                $query_data = $query_data->merge($queryWithoutGoodScale);
            }
            if($type == 3){
                $query_data = GoodReceiptDetail::whereHas('goodReceipt', function ($querysda) use($request) {
                    $querysda->where('post_date', '>=', $request->start_date)
                        ->where('post_date', '<=', $request->finish_date)
                        ->whereIn('status',["2","3","9"]);
                })
                ->where('item_id',$row_item)->get();

            }

            $grouped_data = $query_data->groupBy(function($detail) {
                return $detail->goodReceipt->account_id;
            });

            $limited_data = $grouped_data->map(function ($group) {
                return $group;
            });


            if($query_data){
                foreach ($limited_data as $k=>$row) {

                    if(!isset($temp_pdf[$k])){
                        $temp_pdf[$k] = [];
                    }
                    $arr = [];
                    $no = 1;
                    $all_penerimaan = 0;
                    $all_bayar = 0;
                    $all_finance_price = 0;
                    $all_netto= 0;
                    $account = '';
                    $satuan = '';
                    if($type == 2){
                        foreach($row as $detail_gs){
                            if($account== ''){
                                $account = $detail_gs->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $detail_gs->itemUnit->unit->code;
                            }
                            if($detail_gs->goodScale()->exists()){

                                $take_item_rule_percent = RuleBpScale::where('item_id',$detail_gs->goodScale->item_id)
                                ->whereDate('start_effective_date','<=',$detail_gs->goodScale->post_date)
                                ->whereDate('effective_date','>=',$detail_gs->goodScale->post_date)
                                ->where('account_id',$detail_gs->goodScale->account_id)->first();
                            }else{
                                $take_item_rule_percent = RuleBpScale::where('item_id',$row_item)
                                ->whereDate('start_effective_date','<=',$detail_gs->goodReceipt->post_date)
                                ->whereDate('effective_date','>=',$detail_gs->goodReceipt->post_date)
                                ->where('account_id',$detail_gs->goodReceipt->account_id)->first();
                            }
                            $percentage_level = 0;
                            $percentage_netto_limit = 0;
                            $finance_kadar_air = 0;
                            $finance_kg = 0;
                            $real_balance=0;
                            if($take_item_rule_percent){
                                $percentage_level = round($take_item_rule_percent->percentage_level,2);
                                $percentage_netto_limit = round($take_item_rule_percent->percentage_netto_limit,2);
                            }
                            if (Carbon::parse($request->start_date)->greaterThan(Carbon::create(2025, 1, 1))) {
                                if($detail_gs->goodScale()->exists()){
                                    if($detail_gs->goodScale->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    $real_balance = (($detail_gs->qty/$detail_gs->goodReceipt->getTotalQty())*$detail_gs->goodScale->qty_balance);
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 )*$real_balance;
                                    }
                                    $total_bayar = $detail_gs->qty_balance;

                                    $total_penerimaan = $real_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->goodScale->purchaseOrderDetail->price;
                                    $all_netto += $real_balance;
                                    $finance_price = $price*$total_bayar;
                                }else{
                                    $real_balance=$detail_gs->qty;
                                    if($detail_gs->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->qty);
                                    }
                                    $total_bayar = $real_balance;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $real_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->purchaseOrderDetail->price;
                                    $finance_price = $price*$total_bayar;
                                    $all_netto += $detail_gs->qty;
                                }
                            }else{
                                if($detail_gs->goodScale()->exists()){
                                    $real_balance = $detail_gs->goodScale->qty_balance;
                                    if($detail_gs->goodScale->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->goodScale->qty_balance);
                                    }
                                    $total_bayar = $detail_gs->goodScale->qty_balance;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $detail_gs->goodScale->qty_balance * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->goodScale->purchaseOrderDetail->price;
                                    $all_netto += $detail_gs->goodScale->qty_balance;
                                    $finance_price = $price*$total_bayar;

                                }else{
                                    $real_balance = $detail_gs->qty;
                                    if($detail_gs->water_content > $percentage_level && $percentage_level != 0){
                                        $finance_kadar_air = $detail_gs->water_content - $percentage_level;
                                    }
                                    if($finance_kadar_air > 0){
                                        $finance_kg = ($finance_kadar_air/100 *$percentage_netto_limit/100 *$detail_gs->qty);
                                    }
                                    $total_bayar = $detail_gs->qty;
                                    if($finance_kadar_air > 0){
                                        $total_bayar = $total_bayar-$finance_kg;
                                    }
                                    $total_penerimaan = $detail_gs->qty * (1 - ($detail_gs->water_content/100));
                                    $price = $detail_gs->purchaseOrderDetail->price;
                                    $finance_price = $price*$total_bayar;
                                    $all_netto += $detail_gs->qty;
                                }
                            }


                            $all_penerimaan += $total_penerimaan;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $detail_gs->place->code,
                                'NO PO'=> $detail_gs->goodScale->purchaseOrderDetail->purchaseOrder->code??$detail_gs->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $detail_gs->item->name,
                                'NO SJ'=> $detail_gs->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($detail_gs->goodScale->post_date?? $detail_gs->goodReceipt->post_date)),
                                'NO. KENDARAAN' =>$detail_gs->goodScale->vehicle_no ?? $detail_gs->goodReceipt->vehicle_no,
                                'NETTO JEMBATAN TIMBANG' =>number_format($real_balance ?? $detail_gs->qty,2,',','.'),
                                'HASIL QC' =>number_format($detail_gs->water_content,2,',','.'),
                                'STD POTONGAN QC' =>number_format($percentage_level,2,',','.'),
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement','all');
                        $content = $pdf->download()->getOriginalContent();
                        $randomString = Str::random(10);
                    }else{
                        foreach($row as $row_2){
                            $netto_sj = 0;
                            $selisih = 0;
                            if($account== ''){
                                $account = $row_2->goodReceipt->account->name;
                            }
                            if($satuan == ''){
                                $satuan = $row_2->itemUnit->unit->code;
                            }
                            if($row_2->goodScale()->exists()){
                                $netto_sj = $row_2->goodScale->qty_balance;

                            }
                            if($netto_sj > 0){
                                $selisih = $row_2->qty - $netto_sj;
                            }
                            $total_bayar = $row_2->qty;
                            $price = $row_2->purchaseOrderDetail->price;
                            $finance_price = $price*$total_bayar;


                            $all_penerimaan += $total_bayar;
                            $all_finance_price += $finance_price;
                            $all_bayar += $total_bayar;
                            $all_netto +=$total_bayar;



                            $arr[] = [
                                'no'                => $no,
                                'PLANT'=> $row_2->place->name,
                                'NO PO'=> $row_2->purchaseOrderDetail->purchaseOrder->code,
                                'NAMA ITEM'=> $row_2->item->name,
                                'NO SJ'=> $row_2->goodReceipt->delivery_no,
                                'TGL MASUK'=> date('d/m/Y',strtotime($row_2->goodReceipt->post_date)),
                                'NO. KENDARAAN' =>$row_2?->goodScale->vehicle_no ?? '-',
                                'NETTO SJ'=>number_format($netto_sj,2,',','.'),
                                'NETTO SPS'=>number_format($total_bayar,2,',','.'),
                                'SELISIH'=>number_format($selisih,2,',','.'),
                                'TOTAL BAYAR'=>number_format($total_bayar,2,',','.'),
                                'TOTAL PENERIMAAN'=>$total_bayar,
                                'HARGA PO'=>$price,
                                'HARGA FINANCE'=>$finance_price,
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
                            'total_netto'=>number_format($all_netto,2,',','.'),
                            'total_all_bayar'=>number_format($all_bayar,2,',','.'),
                            'total_all_penerimaan'=>number_format($all_penerimaan,2,',','.'),
                            'supplier'  => $account,
                            'satuan'  => $satuan,
                        ];
                        $pdf = PrintHelper::print($data,'Report Procurement','a4','landscape','admin.print.purchase.report_procurement_sm','all');
                        $content = $pdf->download()->getOriginalContent();

                        $randomString = Str::random(10);
                    }

                    $filePath = 'public/pdf/' . $randomString . '.pdf';

                    Storage::put($filePath, $content);

                    $absoluteFilePath = storage_path('app/' . $filePath);
                    $document_po = asset(Storage::url($filePath));
                    $temp_pdf[$k][] = $content;

                }


            }else{
                $error = $zip->getStatusString();
                dd("Zip file could not be opened: " . $error);
            }

        }
        foreach($temp_pdf as $k=>$row_pdf){

            $merger = new Merger();
            foreach ($row_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();

            $randomStringss = Str::random(4);
            $pdfFileName = "Report_Procurement_Account_{$k}_{$randomStringss}.pdf";
            $contentArray[]=$result;
            $pdfFileNameArray[] = $pdfFileName;
        }
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {

            foreach($contentArray as $index=>$row_content){
                $zip->addFromString($pdfFileNameArray[$index],$row_content);
                if ($zip->addFromString($pdfFileNameArray[$index], $row_content) === false) {
                    dd("Failed to add PDF file to ZIP: " . $pdfFileNameArray[$index]);
                } else {
                    info("Added $pdfFileNameArray[$index] to ZIP.");
                }

                $numFiles = $zip->numFiles;
                info("Number of files added to ZIP: $numFiles");
            }
            $numFiles = $zip->numFiles;
            info("Number of files added to ZIP: $numFiles");
            $zip->close();

        }

        $response =[
            'status'=>200,
            'message'  =>url('/storage/temp/'.$zipFileName)
        ];
        return response()->json($response);
    }
}
