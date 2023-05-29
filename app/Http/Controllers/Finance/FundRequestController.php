<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\Currency;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\LandedCost;
use App\Models\PaymentRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Tax;
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
use App\Exports\ExportFundRequest;

class FundRequestController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'     => 'Permohonan Dana',
            'content'   => 'admin.finance.fund_request',
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'place_id',
            'department_id',
            'account_id',
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'termin_note',
            'payment_type',
            'name_account',
            'no_account',
            'total',
            'tax',
            'wtax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = FundRequest::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
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

        $total_filtered = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
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
                    $val->user->name,
                    $val->code,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->account->name,
                    $val->type(),
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,3,',','.'),
                    $val->note,
                    $val->termin_note,
                    $val->paymentType(),
                    $val->name_account,
                    $val->no_account,
                    number_format($val->total,3,',','.'),
                    number_format($val->tax,3,',','.'),
                    number_format($val->wtax,3,',','.'),
                    number_format($val->grandtotal,3,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function rowDetail(Request $request){
        $data   = FundRequest::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12">
                    <table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Harga Total</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->fundRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="center-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.number_format($row->total,3,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function voidStatus(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Payment Request.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new FundRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the fund request data');
    
                CustomHelper::sendNotification('fund_requests',$query->id,'Permohonan Dana No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('fund_requests',$query->id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
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
            'title' => 'FUND
             REQUEST REPORT',
            'data' => FundRequest::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('required_date', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->get()
		];
		
		return view('admin.print.finance.fund_request', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportFundRequest($search,$status,$this->dataplaces), 'fund_request_'.uniqid().'.xlsx');
    }

    public function userIndex()
    {
        $data = [
            'title'         => 'Pengajuan Permohonan Dana - Pengguna',
            'content'       => 'admin.personal.fund_request',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
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
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'termin_note',
            'payment_type',
            'name_account',
            'no_account',
            'total',
            'tax',
            'wtax',
            'grandtotal'
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
                    $val->type(),
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,3,',','.'),
                    $val->note,
                    $val->termin_note,
                    $val->paymentType(),
                    $val->name_account,
                    $val->no_account,
                    number_format($val->total,3,',','.'),
                    number_format($val->tax,3,',','.'),
                    number_format($val->wtax,3,',','.'),
                    number_format($val->grandtotal,3,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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
            'type'                      => 'required',
			'post_date' 				=> 'required',
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
            'type.required'                     => 'Tipe tidak boleh kosong',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'required_date.required' 			=> 'Tanggal request pembayaran tidak boleh kosong.',
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


            $total = 0; 
            $grandtotal = 0;
            $tax = str_replace(',','.',str_replace('.','',$request->tax));
            $wtax = str_replace(',','.',str_replace('.','',$request->wtax));

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
            }

            $grandtotal = $total + $tax - $wtax;
                    
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
                        $query->type = $request->type;
                        $query->post_date = $request->post_date;
                        $query->required_date = $request->required_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->termin_note = $request->termin_note;
                        $query->payment_type = $request->payment_type;
                        $query->name_account = $request->name_account;
                        $query->no_account = $request->no_account;
                        $query->document = $document;
                        $query->total = $total;
                        $query->tax = $tax;
                        $query->wtax = $wtax;
                        $query->grandtotal = $grandtotal;
                        $query->save();

                        foreach($query->fundRequestDetail as $row){
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
                    $query = FundRequest::create([
                        'code'			=> FundRequest::generateCode(),
                        'user_id'		=> session('bo_id'),
                        'place_id'      => $request->place_id,
                        'department_id'	=> $request->department_id,
                        'account_id'    => $request->account_id,
                        'type'          => $request->type,
                        'post_date'     => $request->post_date,
                        'required_date' => $request->required_date,
                        'currency_id'   => $request->currency_id,
                        'currency_rate' => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'note'          => $request->note,
                        'termin_note'   => $request->termin_note,
                        'payment_type'  => $request->payment_type,
                        'name_account'  => $request->name_account,
                        'no_account'    => $request->no_account,
                        'document'      => $request->file('file') ? $request->file('file')->store('public/fund_requests') : NULL,
                        'total'         => $total,
                        'tax'           => $tax,
                        'wtax'          => $wtax,
                        'grandtotal'    => $grandtotal,
                        'status'        => '1',
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
                        FundRequestDetail::create([
                            'fund_request_id'       => $query->id,
                            'note'                  => $row,
                            'qty'                   => $request->arr_qty[$key],
                            'unit_id'               => $request->arr_unit[$key],
                            'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                        ]);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('fund_requests',$query->id,$query->note);
                CustomHelper::sendNotification('fund_requests',$query->id,'Pengajuan Permohonan Dana No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new FundRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit fund request.');

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

    public function userRowDetail(Request $request){
        $data   = FundRequest::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12">
                    <table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Harga Total</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->fundRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="center-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.number_format($row->total,3,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function userShow(Request $request){
        $fr = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $fr['account_name'] = $fr->account->name;
        $fr['currency_rate'] = number_format($fr->currency_rate,3,',','.');
        $fr['tax'] = number_format($fr->tax,3,',','.');
        $fr['wtax'] = number_format($fr->wtax,3,',','.');

        $arr = [];

        foreach($fr->fundRequestDetail as $row){
            $arr[] = [
                'item'              => $row->note,
                'qty'               => number_format($row->qty,3,',','.'),
                'unit_id'           => $row->unit_id,
                'unit_name'         => $row->unit->code.' - '.$row->unit->name,
                'price'             => number_format($row->price,3,',','.'),
                'total'             => number_format($row->total,3,',','.'),
            ];
        }

        $fr['details'] = $arr;
        				
		return response()->json($fr);
    }

    public function userDestroy(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Permohonan Dana telah diapprove, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal / dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {
            
            $query->fundRequestDetail()->delete();
            CustomHelper::removeApproval('fund_requests',$query->id);

            activity()
                ->performedOn(new FundRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the fund request data');

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

    public function approval(Request $request,$id){
        
        $pr = FundRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Permohonan Dana',
                'data'      => $pr
            ];

            return view('admin.approval.fund_request', $data);
        }else{
            abort(404);
        }
    }

    public function viewStructureTree(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$fr;
        $data_good_receipts = [];
        $data_purchase_requests = [];
        
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_purchase_downpayment=[];
        $data_id_pyrs=[];
        $data_id_frs=[];
        $data_id_greturns=[];
        $data_frs=[];
        $data_pyrs = [];
        $data_good_returns = [];
        $data_outgoingpayments = [];
        $data_lcs=[];
        $data_id_lc=[];
        $data_invoices=[];

        $data_frs[]=$fr;

        $data_pos=[];
        if($query) {

            //Pengambilan Main Branch beserta id terkait
            if($query->hasPaymentRequestDetail()->exists()){
                foreach($query->hasPaymentRequestDetail as $row_pyr_detail){
                    $data_pyr_tempura=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->paymentRequest->code,
                        "name" => $row_pyr_detail->paymentRequest->code,
                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                    ];
                    if(count($data_pyrs)<1){
                        $data_pyrs[]=$data_pyr_tempura;
                        $data_go_chart[]=$data_pyr_tempura;
                        $data_link[]=[
                            'from'=>$query->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                        ]; 
                        $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                        
                    }else{
                        $found = false;
                        foreach ($data_pyrs as $key => $row_pyr) {
                            if ($row_pyr["key"] == $data_pyr_tempura["key"]) {
                                $found = true;
                                break;
                            }
                        }
                     
                        if($found){
                            $data_links=[
                                'from'=>$query->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $found_inlink = false;
                            foreach($data_link as $key=>$row_link){
                                if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                    $found_inlink = true;
                                    break;
                                }
                            }
                            if(!$found_inlink){
                                $data_link[] = $data_links;
                            }
                            
                        }
                        if (!$found) {
                            $data_pyrs[]=$data_pyr_tempura;
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;   
                        }
                    }
                    if($row_pyr_detail->fundRequest()){
                       
                        $data_fund_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->lookable->code,
                            "name" => $row_pyr_detail->lookable->code,
                            'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                        ];
                        
                        if(count($data_frs)<1){
                            $data_frs[]=$data_fund_tempura;
                            $data_go_chart[]=$data_fund_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_frs[]= $row_pyr_detail->lookable->id;  
                            
                        }else{
                            $found = false;
                            foreach ($data_frs as $key => $row_fundreq) {
                                if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            
                            if($found){
                                $data_links=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $found_inlink = false;
                                foreach($data_link as $key=>$row_link){
                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                        $found_inlink = true;
                                        break;
                                    }
                                }
                                if(!$found_inlink){
                                    $data_link[] = $data_links;
                                }
                                
                            }
                            if (!$found) {
                                $data_frs[]=$data_fund_tempura;
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_frs[]= $row_pyr_detail->lookable->id;   
                            }
                        }
                        
                    }
                    foreach($row_pyr_detail->paymentRequest->paymentRequestDetail as $row_pyrd){
                        if($row_pyrd->purchaseDownPayment()){
                        
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyrd->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyrd->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyrd->lookable->code,
                                "name" => $row_pyrd->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyrd->lookable->code),  
                            ];
                            if(count($data_purchase_downpayment)<1){
                                $data_purchase_downpayment[]=$data_downp_tempura;
                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyrd->lookable->code,
                                    'to'=>$row_pyrd->paymentRequest->code,
                                ]; 
                                $data_id_dp[]= $row_pyrd->lookable->id;  
                                
                            }else{
                                $found = false;
                                foreach ($data_purchase_downpayment as $key => $row_dp) {
                                    if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if($found){
                                    $data_links=[
                                        'from'=>$row_pyrd->lookable->code,
                                        'to'=>$row_pyrd->paymentRequest->code,
                                    ]; 
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_purchase_downpayment[]=$data_downp_tempura;
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyrd->lookable->code,
                                        'to'=>$row_pyrd->paymentRequest->code,
                                    ]; 
                                    $data_id_dp[]= $row_pyrd->lookable->id;    
                                }
                            }
                        }  
                    }
                }
            }

            $added = true;
           
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
                            ],
                            'key'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'name'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                        ];
                        if(count($data_pos)<1){
                            $data_pos[]=$po;
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                                'to'=>$query_gr->code,
                            ];
                            $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            
                        }else{
                            $found = false;
                            foreach ($data_pos as $key => $row_pos) {
                                if ($row_pos["key"] == $po["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $data_pos[] = $po;
                                $data_link[]=[
                                    'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                                    'to'=>$query_gr->code,
                                ];  
                                $data_go_chart[]=$po;
                                $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id;
                            }
                        }

                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            $good_return_tempura =[
                                "name"=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                "key" =>  $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal :". $good_receipt_detail->goodReturnPODetail->goodReturnPO->post_date],
                                ],
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt( $good_receipt_detail->goodReturnPODetail->goodReturnPO->code),
                            ];
                            if(count($data_good_returns)<1){
                                $data_good_returns[]=$good_return_tempura;
                                $data_go_chart[] = $good_return_tempura;;
                                $data_link[]=[
                                    'from'=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                    'to'=>$query_gr->code,
                                ];
                                $data_id_greturns[]=  $good_receipt_detail->goodReturnPODetail->goodReturnPO->id; 
                                
                            }else{
                                $found = false;
                                foreach ($data_good_returns as $key => $row_return) {
                                    if ($row_return["key"] == $good_return_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_good_returns[]=$good_return_tempura;
                                    $data_go_chart[] = $good_return_tempura;;
                                    $data_link[]=[
                                        'from'=> $good_receipt_detail->goodReturnPODetail->goodReturnPO->code,
                                        'to'=>$query_gr->code,
                                    ];
                                    $data_id_greturns[]=  $good_receipt_detail->goodReturnPODetail->goodReturnPO->id; 
                                }
                            }
                        }
                        //landed cost searching
                        if($good_receipt_detail->landedCostDetail()->exists()){
                            foreach($good_receipt_detail->landedCostDetail->landedCost as $landed_cost){
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$landed_cost->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($landed_cost->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$landed_cost->code,
                                    'name'=>$landed_cost->code,
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost->code),    
                                ];
                                if(count($data_lcs)<1){
                                    $data_lcs[]=$data_lc;
                                    $data_go_chart[]=$data_lc;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$landed_cost->code,
                                    ];
                                    $data_id_lc = $landed_cost->id;
                                }else{
                                    $found = false;
                                    foreach ($data_lcs as $key => $row_lc) {
                                        if ($row_lc["key"] == $data_lc["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        $data_lcs[]=$data_lc;
                                        $data_go_chart[]=$data_lc;
                                        $data_link[]=[
                                            'from'=>$query_gr->code,
                                            'to'=>$landed_cost->code,
                                        ];
                                        $data_id_lc = $landed_cost->id;
                                    }
                                }
                                
                            }
                        }
                        //invoice searching
                        if($good_receipt_detail->purchaseInvoiceDetail()->exists()){
                            foreach($good_receipt_detail->purchaseInvoiceDetail as $invoice_detail){
                                $invoice_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        
                                    ],
                                    'key'=>$invoice_detail->purchaseInvoice->code,
                                    'name'=>$invoice_detail->purchaseInvoice->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                                ];
                                if(count($data_invoices)<1){
                                    $data_invoices[]=$invoice_tempura;
                                    $data_go_chart[]=$invoice_tempura;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$invoice_detail->purchaseInvoice->code,
                                    ];
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_invoices as $key => $row_invoice) {
                                        if ($row_invoice["key"] == $invoice_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        $data_invoices[]=$invoice_tempura;
                                        $data_go_chart[]=$invoice_tempura;
                                        $data_link[]=[
                                            'from'=>$query_gr->code,
                                            'to'=>$invoice_detail->purchaseInvoice->code,
                                        ];
                                        
                                    }
                                    
                                }
                                if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                    $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                    $added = true; 
                                }
                            }
                        }

                    }

                
                    
                    if($query_gr->landedCost()->exists()){
                        foreach($query_gr->landedCost as $landed_cost){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$landed_cost->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($landed_cost->grandtotal,2,',','.')]
                                ],
                                'key'=>$landed_cost->code,
                                'name'=>$landed_cost->code,
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost->code),    
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost->code,
                                ];
                                $data_id_lc = $landed_cost->id;
                            }else{
                                $found = false;
                                foreach ($data_lcs as $key => $row_lc) {
                                    if ($row_lc["key"] == $data_lc["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_lcs[]=$data_lc;
                                    $data_go_chart[]=$data_lc;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$landed_cost->code,
                                    ];
                                    $data_id_lc = $landed_cost->id;
                                }
                            }
                            
                        }
                    }
                    
                    if($query_gr->purchaseInvoiceDetail()->exists()){
                        foreach($query_gr->purchaseInvoiceDetail as $invoice_detail){
                            $invoice_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                    
                                ],
                                'key'=>$invoice_detail->purchaseInvoice->code,
                                'name'=>$invoice_detail->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                            ];
                            if(count($data_invoices)<1){
                                $data_invoices[]=$invoice_tempura;
                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                ];
                                
                            }else{
                                $found = false;
                                foreach ($data_invoices as $key => $row_invoice) {
                                    if ($row_invoice["key"] == $invoice_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_invoices[]=$invoice_tempura;
                                    $data_go_chart[]=$invoice_tempura;
                                    $data_link[]=[
                                        'from'=>$query_gr->code,
                                        'to'=>$invoice_detail->purchaseInvoice->code,
                                    ];
                                    
                                }
                                
                            }
                            if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                $added = true; 
                            }
                        }
                    }

                }




                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                    foreach($query_return->goodReturnPODetail as $good_return_detail){
                        $data_good_receipt = [
                            "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "color"=>"lightblue",
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        if(count($data_good_receipts)<1){
                            $data_good_receipt[]=$data_good_receipt;
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                'to'=>$data_good_receipt["key"],
                            ];
                        }else{
                            $found = false;
                            foreach ($data_good_receipts as $key => $row_gr) {
                                if ($row_gr["key"] == $data_good_receipt["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if($found){
                                $data_links=[
                                    'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                    'to'=>$data_good_receipt["key"],
                                ];  
                                $found_inlink = false;
                                foreach($data_link as $key=>$row_link){
                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                        $found_inlink = true;
                                        break;
                                    }
                                }
                                if(!$found_inlink){
                                    $data_link[] = $data_links;
                                }
                                
                            }
                            if (!$found) {
                                $data_good_receipt[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                    'to'=>$data_good_receipt["key"],
                                ];
                                  
                            }
                        }
                        if(!in_array($good_return_detail->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                            $data_id_gr[] = $good_return_detail->goodReceiptDetail->goodReceipt->id;
                            $added = true;
                        }
                    }
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrder()){
                            $row_po=$row->lookable;
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    "color"=>"lightblue",
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];
                                /*memasukkan ke node data dan linknya*/
                                if(count($data_pos)<1){
                                    $data_pos[]=$po;
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$row_po->code,
                                        'to'=>$query_invoice->code,
                                    ]; 
                                    $data_id_po[]= $purchase_order_detail->purchaseOrder->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_pos as $key => $row_pos) {
                                        if ($row_pos["key"] == $po["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    //po yang memiliki request yang sama
                                    if($found){
                                        $data_links=[
                                            'from'=>$row_po->code,
                                            'to'=>$query_invoice->code,
                                        ]; 
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_pos[] = $po;
                                        $data_link[]=[
                                            'from'=>$row_po->code,
                                            'to'=>$query_invoice->code,
                                        ];  
                                        $data_go_chart[]=$po;
                                        $data_id_po[]= $purchase_order_detail->purchaseOrder->id; 
                                    }
                                }
                                //memasukkan dengan yang sama atau tidak
                                
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail()->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                                 ],
                                                "key" => $good_receipt_detail->goodReceipt->code,
                                                "name" => $good_receipt_detail->goodReceipt->code,
                                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                            ];
                                            if(count($data_good_receipts)<1){
                                                $data_good_receipts[]=$data_good_receipt;
                                                $data_go_chart[]=$data_good_receipt;
                                                $data_link[]=[
                                                    'from'=>$row_po->code,
                                                    'to'=>$data_good_receipt["key"],
                                                ];
                                                $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            }else{
                                                $found = false;
                                                foreach ($data_good_receipts as $key => $row_pos) {
                                                    if ($row_pos["key"] == $data_good_receipt["key"]) {
                                                        $found = true;
                                                        break;
                                                    }
                                                }
                                                if (!$found) {
                                                    $data_good_receipts[]=$data_good_receipt;
                                                    $data_go_chart[]=$data_good_receipt;
                                                    $data_link[]=[
                                                        'from'=>$row_po->code,
                                                        'to'=>$data_good_receipt["key"],
                                                    ]; 
                                                    $data_id_gr[]=$good_receipt_detail->goodReceipt->id; 
                                                }
                                            }
                                        }
                                    }
                                }
                            
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceipt()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            if(count($data_good_receipts)<1){
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$data_good_receipt["key"],
                                    'to'=>$query_invoice->code,
                                ];
                               
                            }else{
                                $found = false;
                                foreach ($data_good_receipts as $key => $row_pos) {
                                    if ($row_pos["key"] == $data_good_receipt["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_go_chart[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$data_good_receipt["key"],
                                        'to'=>$query_invoice->code,
                                    ]; 
                                   
                                }
                            }
                            if(!in_array($row->lookable->id, $data_id_gr)){
                                $data_id_gr[] = $row->lookable->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCost()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row->lookable->code,
                                ];
                                $data_id_lc = $row->lookable->id;
                            }else{
                                $found = false;
                                foreach ($data_lcs as $key => $row_lc) {
                                    if ($row_lc["key"] == $data_lc["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_lcs[]=$data_lc;
                                    $data_go_chart[]=$data_lc;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row->lookable->code,
                                    ];
                                    $data_id_lc = $row->lookable->id;
                                }
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                            $found = false;
                            foreach($data_purchase_downpayment as $data_dp){
                                if($data_dp["key"]==$data_down_payment["key"]){
                                    $found= true;
                                    break;
                                }

                            }
                            if($found){
                                $data_links=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                ];
                                $found_inlink = false;
                                foreach($data_link as $key=>$row_link){
                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                        $found_inlink = true;
                                        break;
                                    }
                                }
                                if(!$found_inlink){
                                    $data_link[] = $data_links;
                                }
                                
                            }
                            if(!$found){
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                ];
                                $data_purchase_downpayment[]=$data_down_payment;
                            }
                            if($row_pi->purchaseDownPayment->hasPaymentRequestDetail()->exists()){
                                foreach($row_pi->purchaseDownPayment->hasPaymentRequestDetail as $row_pyr_detail){
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];

                                    if(count($data_pyrs)<1){
                                        $data_pyrs[]=$data_pyr_tempura;
                                        $data_go_chart[]=$data_pyr_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pi->purchaseDownPayment->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        
                                    }else{
                                        $found = false;
                                        foreach ($data_pyrs as $key => $row_pyr) {
                                            if ($row_pyr["key"] == $data_pyr_tempura["key"]) {
                                                $found = true;
                                                break;
                                            }
                                        }
                                     
                                        if($found){
                                            $data_links=[
                                                'from'=>$row_pi->purchaseDownPayment->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $found_inlink = false;
                                            foreach($data_link as $key=>$row_link){
                                                if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                    $found_inlink = true;
                                                    break;
                                                }
                                            }
                                            if(!$found_inlink){
                                                $data_link[] = $data_links;
                                            }
                                            
                                        }
                                        if (!$found) {
                                            $data_pyrs[]=$data_pyr_tempura;
                                            $data_go_chart[]=$data_pyr_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pi->purchaseDownPayment->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;   
                                        }
                                    }

                                    if($row_pyr_detail->fundRequest()){
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                        if(count($data_frs)<1){
                                            $data_frs[]=$data_fund_tempura;
                                            $data_go_chart[]=$data_fund_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                            
                                        }else{
                                            $found = false;
                                            foreach ($data_frs as $key => $row_fundreq) {
                                                if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            
                                            if($found){
                                                $data_links=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $found_inlink = false;
                                                foreach($data_link as $key=>$row_link){
                                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                        $found_inlink = true;
                                                        break;
                                                    }
                                                }
                                                if(!$found_inlink){
                                                    $data_link[] = $data_links;
                                                }
                                                
                                            }
                                            if (!$found) {
                                                $data_frs[]=$data_fund_tempura;
                                                $data_go_chart[]=$data_fund_tempura;
                                                $data_link[]=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                            }
                                        }
                                        
                                    }
                                    if($row_pyr_detail->purchaseDownPayment()){
                                        $data_downp_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                        if(count($data_purchase_downpayment)<1){
                                            $data_purchase_downpayment[]=$data_downp_tempura;
                                            $data_go_chart[]=$data_downp_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ]; 
                                            $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        }else{
                                            $found = false;
                                            foreach ($data_purchase_downpayment as $key => $row_dp) {
                                                if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            
                                            if($found){
                                                $data_links=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $found_inlink = false;
                                                foreach($data_link as $key=>$row_link){
                                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                        $found_inlink = true;
                                                        break;
                                                    }
                                                }
                                                if(!$found_inlink){
                                                    $data_link[] = $data_links;
                                                }
                                                
                                            }
                                            if (!$found) {
                                                $data_purchase_downpayment[]=$data_downp_tempura;
                                                $data_go_chart[]=$data_downp_tempura;
                                                $data_link[]=[
                                                    'from'=>$row_pyr_detail->lookable->code,
                                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                                ]; 
                                                $data_id_dp[]= $row_pyr_detail->lookable->id;    
                                            }
                                        }
                                    }
                                    if($row_pyr_detail->purchaseInvoice()){
                                        $data_invoices_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                        if(count($data_invoices)<1){
                                               
                                            $data_go_chart[]=$data_invoices_tempura;
                                            $data_link[]=[
                                                'from'=>$row_pyr_detail->lookable->code,
                                                'to'=>$row_pyr_detail->paymentRequest->code,
                                            ];
                                            
                                            $data_invoices[]=$data_invoices_tempura;
                                        }else{
                                            $found = false;
                                            foreach ($data_invoices as $key => $row_invoice) {
                                                if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                               
                                                $data_go_chart[]=$data_invoices_tempura;
                                                $data_link[]=[
                                                   'from'=>$row_pyr_detail->lookable->code,
                                                   'to'=>$row_pyr_detail->paymentRequest->code,
                                                ];
                                            
                                                $data_invoices[]=$data_invoices_tempura;
                                            }
                                        }
                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                            $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                            $added=true;
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if($query_invoice->hasPaymentRequestDetail()->exists()){
                        foreach($query_invoice->hasPaymentRequestDetail as $row_pyr_detail){
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            if(count($data_pyrs)<1){
                                $data_pyrs[]=$data_pyr_tempura;
                                $data_go_chart[]=$data_pyr_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            }else{
                                $found = false;
                                foreach ($data_pyrs as $key => $row_pyr) {
                                    if ($row_pyr["key"] == $data_pyr_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                             
                                if($found){
                                    $data_links=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_pyrs[]=$data_pyr_tempura;
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;   
                                }
                            }
                            if($row_pyr_detail->fundRequest()){
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                                if(count($data_frs)<1){
                                    $data_frs[]=$data_fund_tempura;
                                    $data_go_chart[]=$data_fund_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_frs as $key => $row_fundreq) {
                                        if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    
                                    if($found){
                                        $data_links=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_frs[]=$data_fund_tempura;
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                    }
                                }
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                if(count($data_purchase_downpayment)<1){
                                    $data_purchase_downpayment[]=$data_downp_tempura;
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                }else{
                                    $found = false;
                                    foreach ($data_purchase_downpayment as $key => $row_dp) {
                                        if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    
                                    if($found){
                                        $data_links=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_purchase_downpayment[]=$data_downp_tempura;
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;    
                                    }
                                }
                            }
                            if($row_pyr_detail->purchaseInvoice()){
                                $data_invoices_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                if(count($data_invoices)<1){
                                       
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ];
                                    
                                    $data_invoices[]=$data_invoices_tempura;
                                }else{
                                    $found = false;
                                    foreach ($data_invoices as $key => $row_invoice) {
                                        if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                       
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                           'from'=>$row_pyr_detail->lookable->code,
                                           'to'=>$row_pyr_detail->paymentRequest->code,
                                        ];
                                    
                                        $data_invoices[]=$data_invoices_tempura;
                                    }
                                }
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                    $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                    $added=true;
                                }
                            }
                        }
                    }
                }

                foreach($data_id_pyrs as $payment_request_id){
                    $query_pyr = PaymentRequest::find($payment_request_id);

                    if($query_pyr->outgoingPayment()->exists()){
                        $outgoing_payment = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyr->outgoingPayment->code,
                            "name" => $query_pyr->outgoingPayment->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyr->outgoingPayment->code),  
                        ];
                        if(count($data_outgoingpayments) < 1){
                            $data_outgoingpayments[]=$outgoing_payment;
                            $data_go_chart[]=$outgoing_payment;
                            $data_link[]=[
                                'from'=>$query_pyr->code,
                                'to'=>$query_pyr->outgoingPayment->code,
                            ]; 
                        }else{
                            $found = false;
                            foreach($data_outgoingpayments as $row_op){
                                if($outgoing_payment["key"]== $row_op["key"]){
                                    $found = true;
                                    break;
                                }
                            }
                            if($found){
                                $data_links=[
                                    'from'=>$query_pyr->code,
                                    'to'=>$query_pyr->outgoingPayment->code, 
                                ];
                                foreach($data_link as $key=>$row_link){
                                    if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                        $found_inlink = true;
                                        break;
                                    }
                                }
                                if(!$found_inlink){
                                    $data_link[] = $data_links;
                                }
                            }
                            if(!$found){
                                $data_outgoingpayments[]=$outgoing_payment;
                                $data_go_chart[]=$outgoing_payment;
                                $data_link[]=[
                                    'from'=>$query_pyr->code,
                                    'to'=>$query_pyr->outgoingPayment->code,
                                ]; 
                            }
                        }
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        
                        $data_pyr_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                        if($row_pyr_detail->fundRequest()){
                            
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finace/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ];
                            if(count($data_frs)<1){
                                $data_frs[]=$data_fund_tempura;
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                
                            }else{
                                $found = false;
                                foreach ($data_frs as $key => $row_fundreq) {
                                    if ($row_fundreq["key"] == $data_fund_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if($found){
                                    $data_links=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_frs[]=$data_fund_tempura;
                                    $data_go_chart[]=$data_fund_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_frs[]= $row_pyr_detail->lookable->id;   
                                }
                            }
                            
                        }
                        if($row_pyr_detail->purchaseDownPayment()){
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                            if(count($data_purchase_downpayment)<1){
                                
                                $data_purchase_downpayment[]=$data_downp_tempura;
                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ]; 
                                
                                
                            }else{
                                $found = false;
                                foreach ($data_purchase_downpayment as $key => $row_dp) {
                                    if ($row_dp["key"] == $data_downp_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if($found){
                                    
                                    $data_links=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_purchase_downpayment[]=$data_downp_tempura;
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_detail->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    
                                }
                                if(count($data_id_dp)<1){
                                    $data_id_dp[] = $row_pyr_detail->lookable->id;
                                    $added = true;
                                    
                                }
                                
                                
                            }
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                               
                            }else{
                            }
                        }
                        if($row_pyr_detail->purchaseInvoice()){
                            $data_invoices_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                            if(count($data_invoices)<1){
                                   
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                ];
                                
                                $data_invoices[]=$data_invoices_tempura;
                            }else{
                                $found = false;
                                foreach ($data_invoices as $key => $row_invoice) {
                                    if ($row_invoice["key"] == $data_invoices_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                   
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                       'from'=>$row_pyr_detail->lookable->code,
                                       'to'=>$row_pyr_detail->paymentRequest->code,
                                    ];
                                
                                    $data_invoices[]=$data_invoices_tempura;
                                }
                            }
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }
                    }
                    
                }
                foreach($data_id_dp as $downpayment_id){
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder->exists()){
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal : Rp.:".number_format($row->purchaseOrder->grandtotal,2,',','.')],
                                ],
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                            ];
                            if(count($data_pos)<1){
                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row->purchaseOrder->code,
                                    'to'=>$query_dp->code,
                                ];
                                $data_pos[]=$po;
                                
                                $data_id_po []=$row->purchaseOrder->id; 
                                
                            }else{
                                $found = false;
                                foreach ($data_pos as $key => $row_pos) {
                                    if ($row_pos["key"] == $po["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$row->purchaseOrder->code,
                                        'to'=>$query_dp->code,
                                    ];
                                    $data_pos[]=$po;
                                
                                    $data_id_po []=$row->purchaseOrder->id;
                                }
                            }
                           
                            
                            
                            /* mendapatkan request po */
                            foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){

                                if($po_detail->purchaseRequestDetail()->exists()){
                                   
                                    $pr = [
                                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                           
                                         ],
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                                    ];
                                    if(count($data_purchase_requests)<1){
                                        $data_purchase_requests[]=$pr;
                                        $data_go_chart[]=$pr;
                                        $data_link[]=[
                                            'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                            'to'=>$row->purchaseOrder->code,
                                        ]; 
                                        
                                    }else{
                                        $found = false;
                                        foreach ($data_purchase_requests as $key => $row_pos) {
                                            if ($row_pos["key"] == $pr["key"]) {
                                                $found = true;
                                                break;
                                            }
                                        }
                                        if (!$found) {
                                            $data_purchase_requests[]=$pr;
                                            $data_go_chart[]=$pr;
                                            $data_link[]=[
                                                'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                                'to'=>$row->purchaseOrder->code,
                                            ]; 
                                        }
                                    }
                                }
                                /* mendapatkan gr po */
                                if($po_detail->goodReceiptDetail()->exists()){
                                    foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                            
                                        $data_good_receipt = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                             ],
                                            "key" => $good_receipt_detail->goodReceipt->code,
                                            "name" => $good_receipt_detail->goodReceipt->code,
                                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                                        ];
                    
                                        if(count($data_good_receipts)<1){
                                            
                                            $data_good_receipts[]=$data_good_receipt;
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row->purchaseOrder->code,
                                                'to'=>$data_good_receipt["key"],
                                            ];
                                           
                                        }else{
                                            $found = false;
                                            foreach ($data_good_receipts as $key => $row_pos) {
                                                if ($row_pos["key"] == $data_good_receipt["key"]) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                                $data_good_receipts[]=$data_good_receipt;
                                                $data_go_chart[]=$data_good_receipt;
                                                $data_link[]=[
                                                    'from'=>$row->purchaseOrder->code,
                                                    'to'=>$data_good_receipt["key"],
                                                ];
                                                
                                               
                                            }
                                        }
                                        if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                            $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                            $added = true;
                                        }
                    
                                    }
                                }
                            }
                             
        
                        }
                        
                    }

                    foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                        
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                        ];
                        if(count($data_invoices)<1){
                           
                            $data_go_chart[]=$invoice_tempura;
                            $data_link[]=[
                                'from'=>$query_dp->code,
                                'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            ];
                            
                            $data_invoices[]=$invoice_tempura;
                        }else{
                            $found = false;
                            foreach ($data_invoices as $key => $row_invoice) {
                                if ($row_invoice["key"] == $invoice_tempura["key"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                               
                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$purchase_invoicedp->purchaseInvoice->code,
                                ];
                                
                                $data_invoices[]=$invoice_tempura;
                            }
                        }
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
                        }
                    }

                }
                
                foreach($data_id_lc as $landed_cost_id){
                    $query= LandedCost::find($landed_cost_id);
                    foreach($query->landedCostDetail as $lc_detail ){
                        if($lc_detail->goodReceiptDetail()){
                            $data_good_receipt = [
                                "key" => $lc_detail->lookable->goodReceipt->code,
                                'name'=> $lc_detail->lookable->goodReceipt->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->goodReceipt->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/good_receipt?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
                            ];
                            if(count($data_good_receipts)<1){
                                            
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$data_good_receipt["key"],
                                    'to'=>$query->code,
                                ];
                               
                            }else{
                                $found = false;
                                foreach ($data_good_receipts as $key => $row_pos) {
                                    if ($row_pos["key"] == $data_good_receipt["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_go_chart[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$data_good_receipt["key"],
                                        'to'=>$query->code,
                                    ];
                                    
                                   
                                }
                            }
                            if(!in_array($lc_detail->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $lc_detail->lookable->goodReceipt->id;
                                $added = true;
                            }

                        }
                        if($lc_detail->landedCostDetail()){
                            $lc_other = [
                                "key" => $lc_detail->lookable->landedCost->code,
                                "name" => $lc_detail->lookable->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($lc_detail->lookable->landedCost->code),
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$lc_other;
                                $data_go_chart[]=$lc_other;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$lc_detail->lookable->landedCost->code,
                                ];
                                $data_id_lc = $lc_detail->lookable->landedCost->id;
                            }else{
                                $found = false;
                                foreach ($data_lcs as $key => $lc_other) {
                                    if ($lc_other["key"] == $data_lc["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $data_lcs[]=$lc_other;
                                    $data_go_chart[]=$lc_other;
                                    $data_link[]=[
                                        'from'=>$query->code,
                                        'to'=>$lc_detail->lookable->landedCost->code,
                                    ];
                                    $data_id_lc = $row->lookable->id;
                                }elseif($found){
                                    $data_links=[
                                        'from'=>$query->code,
                                        'to'=>$lc_detail->lookable->landedCost->code,
                                    ];  
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                }
                            }
                            $data_go_chart[]=$lc_other;
                            $data_lcs[]=$lc_other;
                            $data_link[]=[
                                'from'=>$lc_detail->lookable->landedCost->code,
                                'to'=>$query->code,
                            ];
                            $data_id_lc[]=$lc_detail->lookable->landedCost->id;
                        }
                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    $query_po = PurchaseOrder::find($po_id);
                   
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                       
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                        
                            $pr_tempura=[
                                'key'   => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                "name"  => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                   
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_order_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                            if($data_purchase_requests < 1){
                                $data_purchase_requests[]=$pr_tempura;
                                $data_go_chart[]=$pr_tempura;
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                    'to'=>$query_po->code,
                                ];
                            }else{
                                $found = false;
                                foreach ($data_purchase_requests as $key => $row_pr) {
                                    if ($row_pr["key"] == $pr_tempura["key"]) {
                                        $found = true;
                                        break;
                                    }
                                }
                                //pr yang memiliki request yang sama
                                if($found){
                                    $data_links=[
                                        'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$query_po->code,
                                    ];  
                                    $found_inlink = false;
                                    foreach($data_link as $key=>$row_link){
                                        if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                            $found_inlink = true;
                                            break;
                                        }
                                    }
                                    if(!$found_inlink){
                                        $data_link[] = $data_links;
                                    }
                                    
                                }
                                if (!$found) {
                                    $data_purchase_requests[]=$pr_tempura;
                                    $data_go_chart[]=$pr_tempura;
                                    $data_link[]=[
                                        'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$query_po->code,
                                    ];
                                }
                            }
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                    ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                if(count($data_good_receipts)<1){
                                    $data_good_receipts[]=$data_good_receipt;
                                    $data_link[]=[
                                        'from'=>$purchase_order_detail->purchaseOrder->code,
                                        'to'=>$data_good_receipt["key"],
                                    ];
                                   
                                    $data_go_chart[]=$data_good_receipt;  
                                }else{
                                    $found = false;
                                    foreach($data_good_receipts as $tempdg){
                                        if ($tempdg["key"] == $data_good_receipt["key"]) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if($found){
                                        $data_links=[
                                            'from'=>$purchase_order_detail->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                        ];  
                                        $found_inlink = false;
                                        foreach($data_link as $key=>$row_link){
                                            if ($row_link["from"] == $data_links["from"]&&$row_link["to"] == $data_links["to"]) {
                                                $found_inlink = true;
                                                break;
                                            }
                                        }
                                        if(!$found_inlink){
                                            $data_link[] = $data_links;
                                        }
                                        
                                    }
                                    if (!$found) {
                                        $data_good_receipts[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$purchase_order_detail->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                        ];  
                                       
                                        $data_go_chart[]=$data_good_receipt; 
                                    }
                                }
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }
            }  

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link,
            ];
            
        } else {
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }
}