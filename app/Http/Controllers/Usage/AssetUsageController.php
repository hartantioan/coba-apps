<?php

namespace App\Http\Controllers\Usage;

use App\Http\Controllers\Controller;
use App\Models\AssetUsage;
use App\Models\DocumentTax;
use App\Models\DocumentTaxDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class AssetUsageController extends Controller
{    public function index()
    {
        $data = [
            'title' => 'Usages Hardware',
            'content' => 'admin.usage.asset_usage'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'user',
            'location',
            'ip_address',
            'info',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = AssetUsage::count();
        
        $query_data = AssetUsage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%")
                            ->orWhere('ip_address', 'like', "%$search%")
                            ->orWhere('location', 'like', "%$search%");
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

        $total_filtered = AssetUsage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhere('name', 'like', "%$search%")
                        ->orWhere('ip_address', 'like', "%$search%")
                        ->orWhere('location', 'like', "%$search%");
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
                    $val->id,
                    $val->name,
                    $val->user->name,
                    $val->location,
                    $val->ip_address,
                    $val->info,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'name'              => 'required',
            'type'              => 'required'
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
            'type.required'         => 'Tipe tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = AssetUsage::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->note	        = $request->note;
                    $query->type	        = $request->type;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = AssetUsage::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'note'			=> $request->note,
                        'type'			=> $request->type,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new AssetUsage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Asset Usage.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function store_w_barcode(Request $request){
        $barcode = $request->input('barcode');
        $xmlDataString = file_get_contents($barcode);
        $xmlObject = simplexml_load_string($xmlDataString);
       

        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'type'              => 'required'
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
            'type.required'         => 'Tipe tidak boleh kosong.',
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
                                'tax_id'=>$query->id,
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
                        'tax_id'=>$query->id,
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
