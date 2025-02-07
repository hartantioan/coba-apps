<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingOrderRecap;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrder;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class MarketingOrderRecapController extends Controller
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
        $data = [
            'title'         => 'Rekapitulasi SO',
            'content'       => 'admin.sales.recap_marketing_order',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

  
    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportMarketingOrderRecap($request->start_date,$request->end_date), 'so_recap_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }

    public function downloadAttachment(Request $request){
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data = MarketingOrder::whereIn('status',['1','2','3'])->whereNotNull('document')->where('post_date','>=',$start_date)->where('post_date','<=',$end_date)->get();
        $arrPath = [];
        foreach($data as $row){
            if(Storage::exists($row->document)){
                $arrPath[] = storage_path('app/'.$row->document);
            }
        }
        $zipFileName = 'app/public/temp/attachment.zip';
        $zipFilePath = storage_path($zipFileName);
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach($arrPath as $file) {
                if (File::exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        } else {
            return response()->json(['error' => 'Unable to create ZIP file'], 500);
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}