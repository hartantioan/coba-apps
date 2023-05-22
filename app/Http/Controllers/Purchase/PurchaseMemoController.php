<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Place;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\UsedData;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\PurchaseMemo;
use App\Models\PurchaseMemoDetail;

class PurchaseMemoController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Purchase Memo',
            'content'       => 'admin.purchase.memo',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getDetails(Request $request){

        if($request->type == 'pi'){
            $data = PurchaseInvoice::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }elseif($request->type == 'podp'){
            $data = PurchaseDownPayment::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }

        if($data->used()->exists()){
            if($request->type == 'pi'){
                $data['status'] = '500';
                $data['message'] = 'Purchase Invoice '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }elseif($request->type == 'podp'){
                $data['status'] = '500';
                $data['message'] = 'Purchase Down Payment '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }
        }else{
           /*  $passed = true;
            if(!$data->hasBalance()){
                $passed = false;
            }
            
            if($passed){ */
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Purchase Memo');

                if($request->type == 'pi'){
                    $data['rawcode'] = $data->code;
                    $data['code'] = CustomHelper::encrypt($data->code);
                    $data['type'] = $data->getTable();
                    $data['post_date'] = date('d/m/y',strtotime($data->post_date));
                    $data['total'] = number_format($data->total,2,',','.');
                    $data['tax'] = number_format($data->tax,2,',','.');
                    $data['wtax'] = number_format($data->wtax,2,',','.');
                    $data['grandtotal'] = number_format($data->balance,2,',','.');
                    $data['account_id'] = $data->account_id;
                    $data['account_name'] = $data->account->name;
                }elseif($request->type == 'podp'){
                    $data['rawcode'] = $data->code;
                    $data['code'] = CustomHelper::encrypt($data->code);
                    $data['type'] = $data->getTable();
                    $data['post_date'] = date('d/m/y',strtotime($data->post_date));
                    $data['total'] = number_format($data->total,2,',','.');
                    $data['tax'] = number_format($data->tax,2,',','.');
                    $data['wtax'] = number_format($data->wtax,2,',','.');
                    $data['grandtotal'] = number_format($data->grandtotal,2,',','.');
                    $data['account_id'] = $data->account_id;
                    $data['account_name'] = $data->supplier->name;
                }

            /* }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada purchase request / good issue '.$data->code.' telah digunakan pada purchase order.';
            } */
        }

        return response()->json($data);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'note',
            'total',
            'tax',
            'wtax',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseMemo::count();
        
        $query_data = PurchaseMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d M Y',strtotime($val->post_date)),
                    $val->note,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
			'account_id' 			=> 'required',
            'company_id'            => 'required',
            'post_date'             => 'required',
            'arr_type'                  => 'required|array',
            'arr_code'                  => 'required|array',
            'arr_description'           => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_wtax'                  => 'required|array',
            'arr_grandtotal'            => 'required|array',
		], [
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_code.required'                 => 'Kode dokumen tidak boleh kosong.',
            'arr_code.array'                    => 'Kode dokumen harus dalam bentuk array.',
            'arr_description.required'          => 'Keterangan tidak boleh kosong.',
            'arr_description.array'             => 'Keterangan harus dalam bentuk array.',
            'arr_total.required'                => 'Nominal total tidak boleh kosong.',
            'arr_total.array'                   => 'Nominal harus dalam bentuk array.',
            'arr_tax.required'                  => 'Nominal ppn tidak boleh kosong.',
            'arr_tax.array'                     => 'Nominal ppn harus dalam bentuk array.',
            'arr_wtax.required'                 => 'Nominal pph tidak boleh kosong.',
            'arr_wtax.array'                    => 'Nominal pph harus dalam bentuk array.',
            'arr_grandtotal.required'           => 'Grandtotal tidak boleh kosong.',
            'arr_grandtotal.array'              => 'Grandtotal harus dalam bentuk array.'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $total = 0;
            $tax = 0;
            $wtax = 0;
            $grandtotal = 0;

            $passed = true;

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
                $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                $wtax += str_replace(',','.',str_replace('.','',$request->arr_wtax[$key]));
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseMemo::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Memo telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){

                        if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/purchase_memos');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->wtax = round($wtax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->document = $document;
                        $query->note = $request->note;

                        $query->save();

                        foreach($query->purchaseMemoDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = PurchaseMemo::create([
                        'code'			            => PurchaseMemo::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'wtax'                      => round($wtax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_memos') : NULL,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_type){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_type as $key => $row){
                            $id = match ($row) {
                                'purchase_invoices'         => PurchaseInvoice::where('code',CustomHelper::decrypt($request->arr_code[$key]))->first()->id,
                                'purchase_down_payments'    => PurchaseDownPayment::where('code',CustomHelper::decrypt($request->arr_code[$key]))->first()->id,
                                default                     => NULL,
                            };

                            PurchaseMemoDetail::create([
                                'purchase_memo_id'      => $query->id,
                                'lookable_type'         => $row,
                                'lookable_id'           => $id,
                                'description'           => $request->arr_description[$key],
                                'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                                'tax'                   => str_replace(',','.',str_replace('.','',$request->arr_tax[$key])),
                                'wtax'                  => str_replace(',','.',str_replace('.','',$request->arr_wtax[$key])),
                                'grandtotal'            => str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key])),
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('purchase_memos',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_memos',$query->id,'Pengajuan Purchase Memo No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PurchaseMemo())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase memo.');

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

    public function rowDetail(Request $request){
        $data   = PurchaseMemo::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="7">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">PO Inv./PO DP</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPH</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->purchaseMemoDetail) > 0){
            foreach($data->purchaseMemoDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->lookable->code.'</td>
                    <td class="center-align">'.$row->description.'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="7">Data detail tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
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
        
        if($data->approval() && $data->approval()->approvalMatrix()->exists()){    
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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}