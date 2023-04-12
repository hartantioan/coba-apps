<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\PurchaseOrder;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\User;
use App\Models\Place;
use App\Models\Department;
use App\Models\GoodReceiptDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportGoodReceipt;

class GoodReceiveController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'     => 'Barang Masuk',
            'content'   => 'admin.inventory.good_receive',
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'currency'  => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'place_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodReceive::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = GoodReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiveDetail', function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiveDetail', function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->place->name,
                    date('d M Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,3,',','.'),
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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
            'place_id'                  => 'required',
			'post_date'		            => 'required',
			'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'arr_price'                 => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_coa'                   => 'required|array',
            'arr_warehouse'             => 'required|array',
		], [
            'place_id.required'                 => 'Penempatan tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'currency_id.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
            'Currency_rate.required' 			=> 'Tanggal dokumen tidak boleh kosong.',
			'warehouse_id.required'				=> 'Gudang tujuan tidak boleh kosong',
            'arr_price.required'                => 'Harga satuan tidak boleh kosong',
            'arr_price.array'                   => 'Harga satuan harus dalam bentuk array',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array',
            'arr_coa.required'                  => 'Coa tidak boleh kosong',
            'arr_coa.array'                     => 'Coa harus dalam bentuk array',
            'arr_warehouse.required'            => 'Gudang tidak boleh kosong',
            'arr_warehouse.array'               => 'Gudang harus dalam bentuk array',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $grandtotal = 0;

            foreach($request->arr_item as $key => $row){
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_price[$key])) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = GoodReceive::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Request telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/good_receives');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->user_id = session('bo_id');
                        $query->place_id = $request->place_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->grandtotal = $grandtotal;
                        $query->save();

                        foreach($query->goodReceiveDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase request sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = GoodReceive::create([
                        'code'			        => GoodReceive::generateCode(),
                        'user_id'		        => session('bo_id'),
                        'place_id'		        => $request->place_id,
                        'post_date'             => $request->post_date,
                        'currency_id'           => $request->currency_id,
                        'currency_rate'         => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receives') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'grandtotal'            => $grandtotal
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_item as $key => $row){
                        
                        GoodReceiveDetail::create([
                            'good_receive_id'       => $query->id,
                            'item_id'               => $row,
                            'qty'                   => $request->arr_qty[$key],
                            'price'                 => $request->arr_price[$key],
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                  => $request->arr_note[$key],
                            'coa_id'                => $request->arr_coa[$key],
                            'warehouse_id'          => $request->arr_warehouse[$key]
                        ]);

                    }

                    CustomHelper::sendApproval('good_receives',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receives',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                activity()
                    ->performedOn(new GoodReceiptMain())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
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
}