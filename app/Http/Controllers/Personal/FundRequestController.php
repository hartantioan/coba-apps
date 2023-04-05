<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use App\Models\User;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseRequest;

class FundRequestController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function userIndex()
    {
        $data = [
            'title'         => 'Pengajuan Permohonan Dana - Pengguna',
            'content'       => 'admin.personal.fund_request',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function userDatatable(Request $request){
        $column = [
            'id',
            'code',
            'place_id',
            'department_id',
            'account_id',
            'post_date',
            'due_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'termin_note',
            'payment_type',
            'name_account',
            'no_account'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = FundRequest::where('user_id',session('bo_id'))->count();
        
        $query_data = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('user_id',session('bo_id'))
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('user_id',session('bo_id'))
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->account->name,
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->due_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,3,',','.'),
                    $val->note,
                    $val->termin_note,
                    $val->paymentType(),
                    $val->name_account,
                    $val->no_account,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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

    public function getAccountInfo(Request $request){
        $data = User::find($request->id);

        $banks = [];

        if($data){
            foreach($data->userBank()->orderByDesc('is_default')->get() as $row){
                $banks[] = [
                    'bank_id'   => $row->bank_id,
                    'name'      => $row->name,
                    'bank_name' => $row->bank->name,
                    'no'        => $row->no,
                ];
            }
        }

        $data['banks'] = $banks;

        return response()->json($data);
    }

    public function userCreate(Request $request){
        $validation = Validator::make($request->all(), [
            'account_id'                => 'required',
			'post_date' 				=> 'required',
			'due_date'			        => 'required',
			'required_date'		        => 'required',
            'place_id'                  => 'required',
            'department_id'             => 'required',
            'note'		                => 'required',
            'payment_type'		        => 'required',
            'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_unit'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_total'                 => 'required|array',
		], [
            'account_id.required'               => 'Target Partner Bisnis tidak boleh kosong',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'due_date.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
			'required_date.required' 			=> 'Tanggal dipakai tidak boleh kosong.',
            'place_id.required'                 => 'Penempatan lokasi tidak boleh kosong.',
            'department_id.required'            => 'Departemen tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'payment_type.required'				=> 'Tipe pembayaran tidak boleh kosong',
            'currency_id.required'				=> 'Mata uang tidak boleh kosong',
            'currency_rate.required'			=> 'Konversi tidak boleh kosong',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty harus dalam bentuk array.',
            'arr_unit.required'                 => 'Satuan tidak boleh kosong.',
            'arr_unit.array'                    => 'Satuan harus dalam bentuk array.',
            'arr_price.required'                => 'Harga tidak boleh kosong.',
            'arr_price.array'                   => 'Harga harus dalam bentuk array.',
            'arr_total.required'                => 'Harga total tidak boleh kosong.',
            'arr_total.array'                   => 'Harga total harus dalam bentuk array.',
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
                    $query = FundRequest::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            $document = $request->file('file')->store('public/fund_requests');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->user_id = session('bo_id');
                        $query->place_id = $request->place_id;
                        $query->department_id = $request->department_id;
                        $query->account_id = $request->account_id;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->required_date = $request->required_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->termin_note = $request->termin_note;
                        $query->payment_type = $request->payment_type;
                        $query->name_account = $request->name_account;
                        
                        $query->document = $document;
                        $query->save();

                        foreach($query->purchaseRequestDetail as $row){
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
                    $query = PurchaseRequest::create([
                        'code'			=> PurchaseRequest::generateCode(),
                        'user_id'		=> session('bo_id'),
                        'place_id'      => $request->place_id,
                        'department_id'	=> session('bo_department_id'),
                        'status'        => '1',
                        'post_date'     => $request->post_date,
                        'due_date'      => $request->due_date,
                        'required_date' => $request->required_date,
                        'note'          => $request->note,
                        'project_id'    => $request->project_id ? $request->project_id : NULL,
                        'document'      => $request->file('file') ? $request->file('file')->store('public/purchase_requests') : NULL,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                foreach($request->arr_item as $key => $row){
                    DB::beginTransaction();
                    try {
                        PurchaseRequestDetail::create([
                            'purchase_request_id'   => $query->id,
                            'item_id'               => $row,
                            'qty'                   => $request->arr_qty[$key],
                            'note'                  => $request->arr_note[$key],
                            'required_date'         => $request->arr_required_date[$key],
                            'place_id'              => session('bo_place_id'),
                            'department_id'         => session('bo_department_id'),
                            'warehouse_id'          => $request->arr_warehouse[$key]
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('purchase_requests',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_requests',$query->id,'Pengajuan Purchase Request No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PurchaseRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase request.');

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