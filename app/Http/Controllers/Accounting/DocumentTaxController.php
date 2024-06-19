<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportDocumentTax;
use App\Exports\ExportDocumentTaxTable;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\DocumentTax;
use App\Models\DocumentTaxDetail;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DocumentTaxController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Faktur',
            'content' => 'admin.accounting.document_tax'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'date',
            'code',
            'npwp_number',
            'npwp_name',
            'npwp_address',
            'replace',
            'transaction_code',
            'npwp_target',
            'npwp_target_name',
            'npwp_target_address',
            'total',
            'tax',
            'wtax',
            'approval_status',
            'tax_status',
            'reference',
            'url',
        ];
        $conditions = [];
        if($request->multiple){
            $codes = explode(',', $request->multiple);

            // Initialize an array to store the conditions
            

            foreach ($codes as $code) {
                // Extract parts of the code
                $transactionCode = substr($code, 0, 2);
                $replace = substr($code, 2, 1);
                $documentCode = substr($code, 3);

                // Add conditions to the array
                $conditions[] = [
                    'transaction_code' => $transactionCode,
                    'replace' => $replace,
                    'code' => $documentCode,
                ];
            }
        }

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = DocumentTax::count();
        
        $query_data = DocumentTax::where(function($query) use ($search, $request , $conditions) {
                        $query->where(function($query) use ($search) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('date', 'like', "%$search%")
                                ->orWhere('npwp_number', 'like', "%$search%")
                                ->orWhere('npwp_name', 'like', "%$search%")
                                ->orWhere('npwp_target', 'like', "%$search%")
                                ->orWhere('npwp_target_name', 'like', "%$search%")
                                ->orWhere('total', 'like', "%$search%")
                                ->orWhere('tax', 'like', "%$search%");
                        });
                        if($request->multiple){
                            $query->where(function($innerQuery) use ($conditions) {
                                foreach ($conditions as $condition) {
                                    $innerQuery->orWhere(function($subInnerQuery) use ($condition) {
                                        $subInnerQuery->where('transaction_code', $condition['transaction_code'])
                                                      ->where('replace', $condition['replace'])
                                                      ->where('code', $condition['code']);
                                    });
                                }
                            });
                        }
                    
                        if($request->start_date && $request->finish_date) {
                            $query->whereDate('date', '>=', $request->start_date)
                                ->whereDate('date', '<=', $request->finish_date);
                        } else if($request->start_date) {
                            $query->whereDate('date','>=', $request->start_date);
                        } else if($request->finish_date) {
                            $query->whereDate('date','<=', $request->finish_date);
                        }
                    })
                    
                    ->offset($start)
                    ->limit($length)
                    ->orderBy($order, $dir)
                    ->get();
        

        $total_filtered = DocumentTax::where(function($query) use ($search, $request , $conditions) {
                $query->where(function($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('date', 'like', "%$search%")
                        ->orWhere('npwp_number', 'like', "%$search%")
                        ->orWhere('npwp_name', 'like', "%$search%")
                        ->orWhere('npwp_target', 'like', "%$search%")
                        ->orWhere('npwp_target_name', 'like', "%$search%")
                        ->orWhere('total', 'like', "%$search%")
                        ->orWhere('tax', 'like', "%$search%");
                });

                if($request->multiple){
                    $query->where(function($innerQuery) use ($conditions) {
                        foreach ($conditions as $condition) {
                            $innerQuery->orWhere(function($subInnerQuery) use ($condition) {
                                $subInnerQuery->where('transaction_code', $condition['transaction_code'])
                                              ->where('replace', $condition['replace'])
                                              ->where('code', $condition['code']);
                            });
                        }
                    });
                }
            
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('date', '>=', $request->start_date)
                        ->whereDate('date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            foreach($query_data as $val) {
                $refrence = '';
                $pdate_dtax ="";
				if($val->documentTaxHandoverDetail()->exists()){
                 
                    $refrence = $val->documentTaxHandoverDetail->documentTaxHandover->code;
                    $pdate_dtax =  date('d/m/Y',strtotime($val->documentTaxHandoverDetail->documentTaxHandover->post_date));
                }else{
                    $refrence = "-";
                    $pdate_dtax ="-";
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $pdate_dtax,
                    $refrence,
                    $val->user->name ?? '-',
                    $val->status(),
                    $val->date,
                    $val->transaction_code.$val->replace.$val->code,
                    $val->npwp_number,
                    $val->npwp_name,
                    $val->npwp_address,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    $val->documentTaxDetail->first()->item ?? '-',
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
                    
                ];

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

    public function rowDetail(Request $request)
    {
        $data   = DocumentTax::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                        <tr>
                            <th align="center" rowspan="2"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
                            <th align="center" colspan="2" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak</th>
                            <th align="center" colspan="3" style="background-color: navy; color: white;border: 1px solid white;">Supplier</th>
                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">PPN</th>
                            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
                        </tr>
                        <tr>
                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor</th>
                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
                            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
                        </tr>
                        </thead><tbody>';
        
        foreach($data->documentTaxDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align" style="border: 1px solid black;">'.($key + 1).'</td>
                <td class="center-align" style="border: 1px solid black;">'.$row->documentTax->date.'</td>
                <td class="center-align" style="border: 1px solid black;">'.$row->documentTax->transaction_code.$row->documentTax->replace.$row->documentTax->code.'</td>
                <td class="center-align" style="border: 1px solid black;">'.$row->documentTax->npwp_number.'</td>
                <td class="center-align" style="border: 1px solid black;">'.$row->documentTax->npwp_name.'</td>              
                <td class="center-align" style="border: 1px solid black;">'.$row->documentTax->npwp_address.'</td>
                <td class="center-align" style="border: 1px solid black;">'.number_format(round($row->total - 0.5, 0, PHP_ROUND_HALF_UP),2,',','.').'</td>
                <td class="center-align" style="border: 1px solid black;">'.number_format(round($row->tax - 0.5, 0, PHP_ROUND_HALF_UP),2,',','.').'</td>
                <td class="center-align" style="border: 1px solid black;">'.$row->item.'</td>
            </tr>';
        } 

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $country = DocumentTax::find($request->id);
        				
		return response()->json($country);
    }

    public function destroy(Request $request){
        $query = DocumentTax::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new DocumentTax())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Tax data');

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

    public function print(Request $request){

        $data = [
            'title' => 'Tax Report',
            'data' => DocumentTax::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where('code', 'like', "%$request->search%")
                        ->orWhere('date', 'like', "%$request->search%")
                        ->orWhere('npwp_number', 'like', "%$request->search%")
                        ->orWhere('npwp_name', 'like', "%$request->search%")
                        ->orWhere('npwp_target', 'like', "%$request->search%")
                        ->orWhere('npwp_target_name', 'like', "%$request->search%")
                        ->orWhere('total', 'like', "%$request->search%")
                        ->orWhere('tax', 'like', "%$request->search%");
                        
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('date', '>=', $request->start_date)
                        ->whereDate('date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }
            })->get()
		];
		
		return view('admin.print.accounting.document_tax', $data);
    }

    public function hasNestedArrays($array) {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false;
    }

    public function export(Request $request){
		$no_faktur = $request->no_faktur ? $request->no_faktur : ''   ;
        info($request);
		return Excel::download(new ExportDocumentTax($no_faktur),'faktur_pajak'.uniqid().'.xlsx');
    }

    public function exportDataTable(Request $request){
		$start_date = $request->start_date ? $request->start_date : ''   ;
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $search = $request->search ? $request->search : '';
        $multiple = $request->multiple ? $request->multiple : '';
        
		return Excel::download(new ExportDocumentTaxTable($start_date,$finish_date,$search,$multiple),'faktur_pajak'.uniqid().'.xlsx');
    }

    public function store_w_barcode(Request $request){
        $barcode = $request->input('barcode');
        $xmlDataString = @file_get_contents($barcode);
        $xmlObject = simplexml_load_string($xmlDataString);
        if ($xmlDataString === false) {
           
            $response = [
                'status' => 422,
                'error'  => 'Bukan merupakan barcode yang dimaksud'
            ];
            return response()->json($response);
        }

        $existingRecord = DB::table('document_taxes')
        ->where('code', $xmlObject->nomorFaktur)
        ->where('replace', $xmlObject->fgPengganti)
        ->where('transaction_code', $xmlObject->kdJenisTransaksi)
        ->whereNull('deleted_at')
        ->whereIn('status',['1','2']) 
        ->exists();
        
        
        if ($existingRecord) {
            $response = [
                'status' => 422,
                'error'  => 'kode sudah pernah diinput'
            ];
            return response()->json($response);
        }else{

            $json = json_encode($xmlObject);
            $phpDataArray = json_decode($json, true);
            DB::beginTransaction();
            if (count($phpDataArray) > 0) {
                
                $dataArray = array();
                $detail_transaksi_faktur = $phpDataArray['detailTransaksi'];
                $date = Carbon::createFromFormat('d/m/Y', $phpDataArray['tanggalFaktur'])->format('Y-m-d');
                try{
                    $query = DocumentTax::create([
                                'transaction_code'=> $phpDataArray['kdJenisTransaksi'],
                                'replace' => $phpDataArray['fgPengganti'],
                                'code'=> $phpDataArray['nomorFaktur'],
                                'date'=> $date,
                                'npwp_number'=> $phpDataArray['npwpPenjual'],
                                'npwp_name'=> $phpDataArray['namaPenjual'],
                                'npwp_address'=> $phpDataArray['alamatPenjual'],
                                'npwp_target'=> $phpDataArray['npwpLawanTransaksi'],
                                'npwp_target_name'=> $phpDataArray['namaLawanTransaksi'],
                                'npwp_target_address'=> $phpDataArray['alamatLawanTransaksi'],
                                'total'=> $phpDataArray['jumlahDpp'],
                                'tax'=> $phpDataArray['jumlahPpn'],
                                'wtax'=> $phpDataArray['jumlahPpnBm'],
                                'approval_status'=> $phpDataArray['statusApproval'],
                                'tax_status'=> $phpDataArray['statusFaktur'],
                                'reference'=> isset($phpDataArray['referensi'])? $phpDataArray['referensi'] : null,
                                'url'=> $barcode,
                                'user_id'=> session('bo_id'),
                                'status'=> '1',
                            ]);
    
                    
    
                    if ($this->hasNestedArrays($detail_transaksi_faktur)) {
                        foreach ($detail_transaksi_faktur as $data) {
                            
                            $dataArray[] = [
                                'document_tax_id'=>$query->id,
                                'item'=>$data['nama'],
                                'price'=>$data['hargaSatuan'],
                                'qty'=>$data['jumlahBarang'],
                                'subtotal'=>$data['hargaTotal'],
                                'discount'=>$data['diskon'],
                                'total'=>$data['dpp'],
                                'tax'=>$data['ppn'],
                                'nominal_ppnbm'=>$data['tarifPpnbm'],
                                'ppnbm'=>$data['ppnbm'],
                            ];
                        }
                        DocumentTaxDetail::insert($dataArray);

                    } else {
                        DocumentTaxDetail::create([
                        'document_tax_id'=>$query->id,
                        'item'=>$detail_transaksi_faktur['nama'],
                        'price'=>$detail_transaksi_faktur['hargaSatuan'],
                        'qty'=>$detail_transaksi_faktur['jumlahBarang'],
                        'subtotal'=>$detail_transaksi_faktur['hargaTotal'],
                        'discount'=>$detail_transaksi_faktur['diskon'],
                        'total'=>$detail_transaksi_faktur['dpp'],
                        'tax'=>$detail_transaksi_faktur['ppn'],
                        'nominal_ppnbm'=>$detail_transaksi_faktur['tarifPpnbm'],
                        'ppnbm'=>$detail_transaksi_faktur['ppnbm'],
                        ]);
                    }
                    
                    
                    
                    DB::commit();
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully saved.',
                    ];
    
                }catch(\Exception $e){
                    DB::rollback();
                    $response = [
                        'status'    => 500,
                        'message'   => 'Data failed to save cause'+ $e -> getMessage(),
                    ];
                }
            }
        }
        
       
        return response()->json($response);
    }
}
