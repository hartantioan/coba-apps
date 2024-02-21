<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportDocumentTax;
use App\Helpers\CustomHelper;
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
            'transaction_code',
            'replace',
            'code',
            'date',
            'npwp_number',
            'npwp_name',
            'npwp_address',
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

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = DocumentTax::count();
        
        $query_data = DocumentTax::where(function($query) use ($search, $request) {
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
        

        $total_filtered = DocumentTax::where(function($query) use ($search, $request) {
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
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->transaction_code,
                    $val->replace,
                    $val->code,
                    $val->date,
                    $val->npwp_number,
                    $val->npwp_name,
                    $val->npwp_address,
                    $val->npwp_target,
                    $val->npwp_target_name,
                    $val->npwp_target_address,
                    $val->total,
                    $val->tax,
                    $val->wtax,
                    $val->approval_status,
                    $val->tax_status,
                    $val->reference,
                    $val->url,
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
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Price</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Sub Total</th>
                                <th class="center-align">Discount (%)</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Tax</th>
                                <th class="center-align">Nominal Ppnbm</th>
                                <th class="center-align">Ppnbm</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->documentTaxDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item.'</td>
                <td class="right-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.number_format($row->subtotal,3,',','.').'</td>              
                <td class="center-align">'.number_format($row->discount,3,',','.').'</td>
                <td class="center-align">'.number_format($row->total,3,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,3,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_ppnbm,3,',','.').'</td>
                <td class="right-align">'.number_format($row->ppnbm,3,',','.').'</td>
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
        
		return Excel::download(new ExportDocumentTax($no_faktur),'faktur_pajak'.uniqid().'.xlsx');
    }

    public function store_w_barcode(Request $request){
        $barcode = $request->input('barcode');
        $xmlDataString = file_get_contents($barcode);
        $xmlObject = simplexml_load_string($xmlDataString);
       

        $validator = Validator::make(['code' => $xmlObject->nomorFaktur], [
            'code' => ['required', Rule::unique('document_taxes','code')->whereNull('deleted_at')],
        ],[
            'code.required'=> 'Kode tidak boleh kosong.',
            'code.unique'  => 'Kode telah dipakai',
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validator->errors()
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
