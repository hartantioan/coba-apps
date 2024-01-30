<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\IncomingPayment;
use App\Models\LandedCost;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\InventoryTransferOut;
use App\Models\MaterialRequest;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Models\Menu;
use App\Helpers\CustomHelper;
use App\Exports\ExportOutgoingPayment;
use App\Models\Place;

class OutgoingPaymentController extends Controller
{

    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Outgoing Payment',
            'content'       => 'admin.finance.outgoing_payment',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'payment_request_id',
            'coa_source_id',
            'post_date',
            'pay_date',
            'currency_id',
            'currency_rate',
            'admin',
            'grandtotal',
            'document',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OutgoingPayment::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = OutgoingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequest',function($query) use($search, $request){
                                $query->where('code','like',"%$search%");
                            });
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
                    $query->whereIn('status', $request->status);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = OutgoingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequest',function($query) use($search, $request){
                                $query->where('code','like',"%$search%");
                            });
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
                    $query->whereIn('status', $request->status);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    $val->paymentRequest()->exists() ? $val->paymentRequest->code : '-',
                    $val->coaSource->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->pay_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->admin,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    $val->status(),
                    '
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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
        $data   = OutgoingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="7">Daftar Pembayaran</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->paymentRequest->paymentRequestDetail as $key => $row){
            
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="center-align">'.$row->type().'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.($row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-').'</td>
                <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Pembayaran dengan Piutang Karyawan</th>
                            </tr>
                            <tr>
                                <th class="center-align">Kode OP</th>
                                <th class="center-align">Kode PR</th>
                                <th class="center-align">Partner Bisnis</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Coa Kas/Bank</th>
                                <th class="center-align">Nominal</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->paymentRequest->paymentRequestCross()->exists()){
            foreach($data->paymentRequest->paymentRequestCross as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->lookable->code.'</td>
                    <td class="center-align">'.$row->lookable->paymentRequest->code.'</td>
                    <td class="center-align">'.$row->lookable->account->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->lookable->post_date)).'</td>
                    <td class="center-align">'.$row->lookable->coaSource->name.'</td>
                    <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="6">Data tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="4"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';
    
                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }
    
                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function sendUsedData(Request $request){
        $op = OutgoingPayment::find($request->id);
        if(!$op->used()->exists()){
            CustomHelper::sendUsedData('outgoing_payments',$request->id,'Form Outgoing Payment');
            return response()->json([
                'status'    => 200,
                'code'      => $op->code,
                'id'        => $op->id
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Outgoing Payment '.$op->used->lookable->code.' telah dipakai di '.$op->used->ref.', oleh '.$op->used->user->name.'.'
            ]);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('outgoing_payments',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function approval(Request $request,$id){
        
        $pr = OutgoingPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Kas Bank Keluar',
                'data'      => $pr
            ];

            return view('admin.approval.outgoing_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pr = OutgoingPayment::where('code',CustomHelper::decrypt($request->id))->first();

        if($pr->used()->exists()){
            $pr['status'] = 500;
            $pr['message'] = 'Outgoing Payment '.$pr->used->lookable->code.' telah dipakai di '.$pr->used->ref.', oleh '.$pr->used->user->name.'.';
        }else{
            CustomHelper::sendUsedData('outgoing_payments',$pr->id,'Form Outgoing Payment');
            $pr['status'] = 200;
            $pr['account_name'] = $pr->account->name;
            $pr['coa_source_name'] = $pr->coaSource->code.' - '.$pr->coaSource->name.' - '.$pr->coaSource->company->name;
            $pr['currency_rate'] = number_format($pr->currency_rate,3,',','.');
            $pr['admin'] = number_format($pr->admin,3,',','.');
            $pr['grandtotal'] = number_format($pr->grandtotal,3,',','.');
            $pr['payment_request_id'] = $pr->payment_request_id ? $pr->payment_request_id : '';
            $pr['payment_request_code'] = $pr->paymentRequest->code;
        }
        				
		return response()->json($pr);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                  => 'required',
            'code_place_id'         => 'required',
			'account_id' 			=> 'required',
            'company_id'            => 'required',
            'coa_source_id'         => 'required',
            'post_date'             => 'required',
            'pay_date'              => 'required',
            'currency_id'           => 'required',
            'currency_rate'         => 'required',
            'admin'                 => 'required',
            'grandtotal'            => 'required',
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_source_id.required'            => 'Kas/Bank tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'pay_date.required'                 => 'Tanggal bayar tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'admin.required'                    => 'Biaya admin tidak boleh kosong, minimal 0.',
            'grandtotal.required'               => 'Total bayar tidak boleh kosong.',
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
                    $query = OutgoingPayment::where('code',CustomHelper::decrypt($request->temp))->first();

                    $approved = false;
                    $revised = false;

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->approved){
                                $approved = true;
                            }

                            if($row->revised){
                                $revised = true;
                            }
                        }
                    }

                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Kas Keluar telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/outgoing_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->coa_source_id = $request->coa_source_id;
                        $query->payment_request_id = $request->payment_request_id ? $request->payment_request_id : NULL;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->pay_date = $request->pay_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->admin = str_replace(',','.',str_replace('.','',$request->admin));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();

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
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=OutgoingPayment::generateCode($menu->document_code.date('y').$request->code_place_id);
                    
                    $query = OutgoingPayment::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'coa_source_id'             => $request->coa_source_id,
                        'payment_request_id'        => $request->payment_request_id ? $request->payment_request_id : NULL,
                        'post_date'                 => $request->post_date,
                        'pay_date'                  => $request->pay_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'admin'                     => str_replace(',','.',str_replace('.','',$request->admin)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/outgoing_payments') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                CustomHelper::sendApproval('outgoing_payments',$query->id,$query->note);
                CustomHelper::sendNotification('outgoing_payments',$query->id,'Outgoing Payment No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new OutgoingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit cash bank out.');

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

    public function voidStatus(Request $request){
        $query = OutgoingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new OutgoingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the outgoing payment data');
    
                CustomHelper::sendNotification('outgoing_payments',$query->id,'Outgoing Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('outgoing_payments',$query->id);
                CustomHelper::removeJournal('outgoing_payments',$query->id);

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

    public function destroy(Request $request){
        $query = OutgoingPayment::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Outgoing Payment telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
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

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval('outgoing_payments',$query->id);

            activity()
                ->performedOn(new OutgoingPayment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the outgoing payment data');

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

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = OutgoingPayment::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Outgoing Payment',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.finance.outgoing_payment_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{   
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = OutgoingPayment::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Outgoing Payment',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.outgoing_payment_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = OutgoingPayment::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Outgoing Payment',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.outgoing_payment_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    
                    
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        
        $pr = OutgoingPayment::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $data = [
                'title'     => 'Outgoing Payment',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.finance.outgoing_payment_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';

		return Excel::download(new ExportOutgoingPayment($post_date,$end_date,$mode), 'outgoing_payment'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = OutgoingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $outgoing_payment = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                     ['name'=> "Nominal: Rp".number_format($query->grandtotal,2,',','.')]
                  ],
                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$outgoing_payment;

        $data_id_good_scale = [];
        $data_id_good_issue = [];
        $data_id_mr = [];
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_inventory_transfer_out=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];
        $data_id_pyrcs=[];

        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_hand_over_invoice = [];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_id_mo_delivery_process=[];
        $data_id_mo_receipt = [];
        $data_incoming_payment=[];
        $data_id_hand_over_receipt=[];


        if($query) {

            //Pengambilan Main Branch beserta id terkait
            if($query->paymentRequest()->exists()){
                foreach($query->paymentRequest->paymentRequestDetail as $row_pyr_detail){
                    $data_pyr_tempura=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->paymentRequest->code,
                        "name" => $row_pyr_detail->paymentRequest->code,
                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                    ];
    
                    $data_go_chart[]=$data_pyr_tempura;
                    $data_link[]=[
                        'from'=>$row_pyr_detail->paymentRequest->code,
                        'to'=>$query->code,
                        'string_link'=>$row_pyr_detail->paymentRequest->code.$query->code,
                    ]; 
                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                        
                    
                    if($row_pyr_detail->fundRequest()){
                       
                        $data_fund_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->lookable->code,
                            "name" => $row_pyr_detail->lookable->code,
                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                        ];
                        
                        $data_go_chart[]=$data_fund_tempura;
                        $data_link[]=[
                            'from'=>$row_pyr_detail->lookable->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                        ]; 
                        $data_id_frs[]= $row_pyr_detail->lookable->id;  
                            
                        
                        
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

                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyrd->lookable->code,
                                'to'=>$row_pyrd->paymentRequest->code,
                                'string_link'=>$row_pyrd->lookable->code.$row_pyrd->paymentRequest->code,
                            ]; 
                            $data_id_dp[]= $row_pyrd->lookable->id;  
 
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

                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'to'=>$query_gr->code,
                            'string_link'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code.$query_gr->code
                        ];
                        //$data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                        if(!in_array($good_receipt_detail->purchaseOrderDetail->purchaseOrder->id, $data_id_po)){
                            $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            $added = true; 
                        }
                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                $good_return_tempura =[
                                    "name"=> $goodReturnPODetail->goodReturnPO->code,
                                    "key" => $goodReturnPODetail->goodReturnPO->code,
                                    
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
                                ];
                                                    
                                $data_go_chart[] = $good_return_tempura;
                                $data_link[]=[
                                    'from'=> $query_gr->code,
                                    'to'=>$goodReturnPODetail->goodReturnPO->code,
                                    'string_link'=>$query_gr->code.$goodReturnPODetail->goodReturnPO->code,
                                ];
                                $data_id_greturns[]=  $goodReturnPODetail->goodReturnPO->id;

                            }
                             
                                
                            
                        }
                        //landed cost searching
                        if($good_receipt_detail->landedCostDetail()->exists()){
                            foreach($good_receipt_detail->landedCostDetail as $landed_cost_detail){
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$landed_cost_detail->landedCost->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$landed_cost_detail->landedCost->code,
                                    'name'=>$landed_cost_detail->landedCost->code,
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost_detail->landedCost->code),    
                                ];

                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost_detail->landedCost->code,
                                    'string_link'=>$query_gr->code.$landed_cost_detail->landedCost->code,
                                ];
                               
                                if(!in_array($landed_cost_detail->landedCost->id, $data_id_lc)){
                                    $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                    $added = true; 
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

                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_gr->code.$invoice_detail->purchaseInvoice->code
                                ];
                                
                                if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                    $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                    $added = true; 
                                }
                            }
                        }

                        if($good_receipt_detail->goodScaleDetail()->exists()){
                            $data_gscale = [
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$good_receipt_detail->goodScaleDetail->goodScale->post_date],
                                        ['name'=> "Vendor  : ".$good_receipt_detail->goodScaleDetail->goodScale->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodScaleDetail->goodScale->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'name'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($good_receipt_detail->goodScaleDetail->goodScale->code),
                                ];
                                $data_go_chart[]=$data_gscale;
                                $data_link[]=[
                                    'from'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'to'=>$query_gr->code,
                                    'string_link'=>$good_receipt_detail->goodScaleDetail->goodScale->code.$query_gr->code
                                ];
                                $data_id_good_scale[]= $good_receipt_detail->goodScaleDetail->goodScale->id; 
                            
                        }

                    }
                }

                foreach($data_id_good_scale as $gs_id){
                    $query_gs = GoodScale::where('id',$gs_id)->first();
                    
                    foreach($query_gs->goodScaleDetail as $data_gs){
                        if($data_gs->goodReceiptDetail->exists()){
                            $gr = [
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$data_gs->goodReceiptDetail->goodReceipt->post_date],
                                    ['name'=> "Vendor  : ".$data_gs->goodReceiptDetail->goodReceipt->supplier->name],
                                    // ['name'=> "Nominal : Rp.:".number_format($data_gs->goodReceiptDetail->goodReceipt->grandtotal,2,',','.')]
                                ],
                                'key'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'name'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($data_gs->goodReceiptDetail->goodReceipt->code),
                            ];
    
                            $data_go_chart[]=$gr;
                            $data_link[]=[
                                'from'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'to'=>$query_gs->code,
                                'string_link'=>$data_gs->goodReceiptDetail->goodReceipt->code.$query_gs->code
                            ];
                            if(!in_array($data_gs->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
                                $added = true; 
                            }
                            // $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
    
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
                   
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        
                        $data_good_receipt[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$data_good_receipt["key"],
                            'to'=>$query_return->code,
                            'string_link'=>$data_good_receipt["key"].$query_return->code,
                        ];
                        
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
                        if($row->purchaseOrderDetail()){
                            $row_po=$row->lookable->purchaseOrder;
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];

                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row_po->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_po->code.$query_invoice->code
                                ]; 
                                $data_id_po[]= $row_po->id;  
                                      
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
                                            
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row_po->code,
                                                'to'=>$data_good_receipt["key"],
                                                'string_link'=>$row_po->code.$data_good_receipt["key"]
                                            ];
                                            $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            
                                        }
                                    }
                                }
                            
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceiptDetail()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->goodReceipt->post_date],
                                    // ['name'=> "Nominal : Rp.".number_format($row->lookable->goodReceipt->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->goodReceipt->code,
                                "name" => $row->lookable->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->goodReceipt->code),
                            ];

                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query_invoice->code,
                                'string_link'=>$data_good_receipt["key"].$query_invoice->code,
                            ];
                            if(!in_array($row->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $row->lookable->goodReceipt->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCostFeeDetail()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->landedCost->code,
                                "name" => $row->lookable->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$data_lc;
                            $data_link[]=[
                                'from'=>$row->lookable->landedCost->code,
                                'to'=>$query_invoice->code,
                                'string_link'=>$row->lookable->landedCost->code.$query_invoice->code,
                            ];
                            $data_id_lc[] = $row->lookable->landedCost->id;
                            
                        }

                        if($row->purchaseMemoDetail()->exists()){
                            foreach($row->purchaseMemoDetail as $purchase_memodetail){
                                $data_memo = [
                                    "name"=>$purchase_memodetail->purchaseMemo->code,
                                    "key" => $purchase_memodetail->purchaseMemo->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                                ];
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$purchase_memodetail->purchaseMemo->code,
                                    'string_link'=>$query_invoice->code.$purchase_memodetail->purchaseMemo->code,
                                ];
                                $data_id_memo[]=$purchase_memodetail->purchaseMemo->id;
                                $data_go_chart[]=$data_memo;
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
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_pi->purchaseDownPayment->code.$query_invoice->code,
                                ];
            
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
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pi->purchaseDownPayment->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pi->purchaseDownPayment->code.$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        


                                    if($row_pyr_detail->fundRequest()){
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                       
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ];        
                                        
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
                                         
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        
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
                                   
                                               
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                        ];
                                        
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
                            
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_invoice->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            
                            if($row_pyr_detail->fundRequest()){
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                             
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                ];             
                                
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

                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                
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
                                
                                       
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                
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

                        $data_go_chart[]=$outgoing_payment;
                        $data_link[]=[
                            'from'=>$query_pyr->code,
                            'to'=>$query_pyr->outgoingPayment->code,
                            'string_link'=>$query_pyr->code.$query_pyr->outgoingPayment->code,
                        ]; 
                        
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
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ];
                           
                               
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
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
                            
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                               
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
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                          
                                   
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }
                        
                        if($row_pyr_detail->paymentRequest->paymentRequestCross()->exists()){
                          
                           
                            foreach($row_pyr_detail->paymentRequest->paymentRequestCross as $row_pyr_cross){
                                 
                                $data_pyrc_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_cross->lookable->code,
                                    "name" => $row_pyr_cross->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                ];
                       
                                $data_go_chart[]=$data_pyrc_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_cross->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                if(!in_array($row_pyr_cross->id, $data_id_pyrcs)){
                                    $data_id_pyrcs[] = $row_pyr_cross->id;
                                    
                                }
                            }

                            
                        }
                    }
                    
                }

                foreach($data_id_pyrcs as $payment_request_cross_id){
                     
                    $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                    if($query_pyrc->paymentRequest()->exists()){
                        $data_pyr_tempura = [
                            'key'   => $query_pyrc->paymentRequest->code,
                            "name"  => $query_pyrc->paymentRequest->code,
                            'properties'=> [
                                 ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->post_date))],
                              ],
                            'url'   =>request()->root()."/admin/finance/payment_request_cross?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                            "title" =>$query_pyrc->paymentRequest->code,
                        ];
                        $data_go_chart[]=$data_pyr_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                        ];
                        
                        if(!in_array($query_pyrc->paymentRequest->id, $data_id_pyrs)){
                            $data_id_pyrs[] = $query_pyrc->paymentRequest->id;
                            $added=true;
                        }
                    }
                    if($query_pyrc->outgoingPayment()){
                        $outgoing_tempura = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyrc->lookable->code,
                            "name" => $query_pyrc->lookable->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                        ];
    
                        $data_go_chart[]=$outgoing_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                        ];
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
                          
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$row->purchaseOrder->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$row->purchaseOrder->code.$query_dp->code,
                            ];
                            
                            $data_id_po []=$row->purchaseOrder->id; 
                                
                            
                           
                            
                            
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
                                    $data_go_chart[]=$pr;
                                    $data_link[]=[
                                        'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$row->purchaseOrder->code,
                                        'string_link'=>$po_detail->purchaseRequestDetail->purchaseRequest->code.$row->purchaseOrder->code
                                    ];
                                    $data_id_pr[]=$po_detail->purchaseRequestDetail->purchaseRequest->id;
                                        
                                    
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
                                               
                                        $data_go_chart[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$row->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                            'string_link'=>$row->purchaseOrder->code.$data_good_receipt["key"],
                                        ];
                                           
                                        
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
                        
                           
                        $data_go_chart[]=$invoice_tempura;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            'string_link'=>$query_dp->code.$purchase_invoicedp->purchaseInvoice->code,
                        ];
                        
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
                        }
                    }

                    foreach($query_dp->purchaseMemoDetail as $purchase_memodetail){
                        $data_memo=[
                            "name"=>$purchase_memodetail->purchaseMemo->code,
                            "key" => $purchase_memodetail->purchaseMemo->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                        ];
                        $data_go_chart[]=$data_memo;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_memodetail->purchaseMemo->code,
                            'string_link'=>$query_dp->code.$purchase_memodetail->purchaseMemo->code,
                        ];
                        

                    }

                }

                foreach($data_id_memo as $memo_id){
                    $query = PurchaseMemo::find($memo_id);
                    foreach($query->purchaseMemoDetail as $row){
                        if($row->lookable_type == 'purchase_invoice_details'){
                            $data_invoices_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->purchaseInvoice->code,
                                "name" => $row->lookable->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row->lookable->purchaseInvoice->code),
                            ];
        
                            $data_go_chart[]=$data_invoices_tempura;
                            $data_link[]=[
                                'from'=>$data_invoices_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_invoices_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $row->lookable->purchaseInvoice->id;
                                $added=true;
                            }
                        }elseif($row->lookable_type == 'purchase_down_payments'){
                            $data_downp_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$data_downp_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_downp_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row->lookable->id;
                                $added=true;
                            }
                        }
                        
                    }
                }
                
                foreach($data_id_good_issue as $good_issue_id){
                    $query_good_issue = GoodIssue::find($good_issue_id);
                    foreach($query_good_issue->goodIssueDetail as $data_detail_good_issue){
                        if($data_detail_good_issue->materialRequestDetail()){
                            $material_request_tempura = [
                                "key" => $data_detail_good_issue->materialRequestDetail->materialRequest->code,
                                "name" => $data_detail_good_issue->materialRequestDetail->materialRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$data_detail_good_issue->materialRequestDetail->materialRequest->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($data_detail_good_issue->materialRequestDetail->materialRequest->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/inventory/material_request?code=".CustomHelper::encrypt($data_detail_good_issue->materialRequestDetail->materialRequest->code),
                            ];

                            $data_go_chart[]=$material_request_tempura;
                            $data_link[]=[
                                'from'=>$data_detail_good_issue->materialRequestDetail->materialRequest->code,
                                'to'=>$query_good_issue->code,
                                'string_link'=>$data_detail_good_issue->materialRequestDetail->materialRequest->code.$query_good_issue->code,
                            ];
                            $data_id_mr[] = $data_detail_good_issue->materialRequestDetail->materialRequest->id;
                        }

                        if($data_detail_good_issue->purchaseOrderDetail->exists()){
                            foreach($data_detail_good_issue->purchaseOrderDetail as $data_purchase_order_detail){
                                $po_tempura = [
                                    "key" => $data_purchase_order_detail->purchaseOrder->code,
                                    "name" => $data_purchase_order_detail->purchaseOrder->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($data_purchase_order_detail->purchaseOrder->grandtotal,2,',','.')],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($data_purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$material_request_tempura;
                                $data_link[]=[
                                    'from'=>$query_good_issue->code,
                                    'to'=>$data_purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_good_issue->code.$data_purchase_order_detail->purchaseOrder->code,
                                ];
                                $data_id_po[] = $data_purchase_order_detail->purchaseOrder->id;
                            }
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
                                    // ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->goodReceipt->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/good_receipt?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
                            ];
                            
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_good_receipt["key"].$query->code,
                            ];
                               
                            
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

                            $data_go_chart[]=$lc_other;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->landedCost->code,
                                'string_link'=>$query->code.$lc_detail->lookable->landedCost->code,
                            ];
                            if(!in_array($lc_detail->lookable->landedCost->id,$data_id_lc)){
                                $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                $added = true;
                            }
                           
                                              
                        }//??
                        if($lc_detail->inventoryTransferOutDetail()){
                            $inventory_transfer_out = [
                                "key" => $lc_detail->lookable->inventoryTransferOut->code,
                                "name" => $lc_detail->lookable->inventoryTransferOut->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->inventoryTransferOut->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->inventoryTransferOut->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($lc_detail->lookable->inventoryTransferOut->code),
                            ];

                            $data_go_chart[]=$inventory_transfer_out;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->inventoryTransferOut->code,
                                'string_link'=>$query->code.$lc_detail->lookable->inventoryTransferOut->code,
                            ];
                            $data_id_inventory_transfer_out[] = $lc_detail->lookable->inventoryTransferOut->id;
                                              
                        }
                    } // inventory transferout detail apakah perlu
                    if($query->landedCostFeeDetail()->exists()){
                        foreach($query->landedCostFeeDetail as $row_landedfee_detail){
                            foreach($row_landedfee_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                $data_invoices_tempura = [
                                    'key'   => $row_invoice_detail->purchaseInvoice->code,
                                    "name"  => $row_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                ];
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'  =>  $query->code,
                                    'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query->code.$row_invoice_detail->purchaseInvoice->code
                                ];
                                if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                    $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                    $added = true;
                                }
                            }
                           
                        }
                    }
                }

                foreach($data_id_inventory_transfer_out as $id_transfer_out){
                    $query_inventory_transfer_out = InventoryTransferOut::find($id_transfer_out);
                    foreach($query_inventory_transfer_out->inventoryTransferOutDetail as $row_transfer_out_detail){
                        if($row_transfer_out_detail->landedCostDetail->exists()){
                            $lc_tempura = [
                                "key" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                "name" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_transfer_out_detail->landedCostDetail->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($row_transfer_out_detail->landedCostDetail->landedCost->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($row_transfer_out_detail->landedCostDetail->landedCost->code),
                            ];

                            $data_go_chart[]=$lc_tempura;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                'string_link'=>$query->code.$row_transfer_out_detail->landedCostDetail->landedCost->code,
                            ];
                            if(!in_array($row_transfer_out_detail->landedCostDetail->landedCost->id,$data_id_lc)){
                                $data_id_lc[] = $row_transfer_out_detail->landedCostDetail->landedCost->id;
                                $added = true;
                            }
                           
                                   
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
                    
                            $data_go_chart[]=$pr_tempura;
                            $data_link[]=[
                                'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                'to'=>$query_po->code,
                                'string_link'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code.$query_po->code,
                            ];
                            $data_id_pr[]=$purchase_order_detail->purchaseRequestDetail->purchaseRequest->id;
                            
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        // ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                    ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseOrder->code,
                                    'to'=>$data_good_receipt["key"],
                                    'string_link'=>$purchase_order_detail->purchaseOrder->code.$data_good_receipt["key"],
                                ];
                                
                                $data_go_chart[]=$data_good_receipt;  
                                
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        if($purchase_order_detail->goodIssueDetail()->exists()){
                            $good_issue_tempura=[
                                'key'   => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                "name"  => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->goodIssueDetail->goodIssue->post_date],
                                   
                                ],
                                'url'   =>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($purchase_order_detail->goodIssueDetail->goodIssue->code),
                            ];
                    
                            $data_go_chart[]=$good_issue_tempura;
                            $data_link[]=[
                                'from'=>$query_po->code,
                                'to'=>$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                'string_link'=>$query_po->code.$purchase_order_detail->goodIssueDetail->goodIssue->code,
                            ];
                            
                            if(!in_array($purchase_order_detail->goodIssueDetail->goodIssue->id,$data_id_good_issue)){
                                $data_id_good_issue[]=$purchase_order_detail->goodIssueDetail->goodIssue->id;
                                $added = true;
                            }
                            
                        }
                        if($purchase_order_detail->purchaseInvoiceDetail()->exists()){
                            foreach($purchase_order_detail->purchaseInvoiceDetail as $purchase_invoice_detail){
                                $data_invoices_tempura = [
                                    'key'   => $purchase_invoice_detail->purchaseInvoice->code,
                                    "name"  => $purchase_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_invoice_detail->purchaseInvoice->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoice_detail->purchaseInvoice->code),
                                ];
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'  =>  $purchase_invoice_detail->purchaseInvoice->code,
                                    'to'    =>  $query_po->code,
                                    'string_link'=>$purchase_invoice_detail->purchaseInvoice->code.$query_po->code,
                                ];
                                if(!in_array($purchase_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                    $data_id_invoice[]=$purchase_invoice_detail->purchaseInvoice->id;
                                    $added = true;
                                }
                              
                            }
                        }
                        if($purchase_order_detail->marketingOrderDeliveryProcess()->exists()){
                            
                            $data_marketing_order_delivery_process = [
                                'key'   => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                "name"  => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->marketingOrderDeliveryProcess->post_date],
                                
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_order_detail->marketingOrderDeliveryProcess->code),
                            ];
                            $data_go_chart[]=$data_marketing_order_delivery_process;
                            $data_link[]=[
                                'from'  =>  $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                'to'    =>  $query_po->code,
                                'string_link'=>$purchase_order_detail->marketingOrderDeliveryProcess->code.$query_po->code,
                            ];
                            if(!in_array($purchase_order_detail->marketingOrderDeliveryProcess->id,$data_id_mo_delivery_process)){
                                $data_id_mo_delivery_process[]=$purchase_order_detail->marketingOrderDeliveryProcess->id;
                                $added = true;
                            }
                              
                            
                        }

                    }

                }

                foreach($data_id_pr as $pr_id){
                    $query_pr = PurchaseRequest::find($pr_id);
                    foreach($query_pr->purchaseRequestDetail as $purchase_request_detail){
                        if($purchase_request_detail->purchaseOrderDetail()->exists()){
                        
                            foreach($purchase_request_detail->purchaseOrderDetail as $purchase_order_detail){
                                $po_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".$purchase_order_detail->purchaseOrder->supplier->name],
                                     ],
                                    'key'=>$purchase_order_detail->purchaseOrder->code,
                                    'name'=>$purchase_order_detail->purchaseOrder->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_pr->code,
                                    'to'=>$purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_pr->code.$purchase_order_detail->purchaseOrder->code,
                                ];
                                if(!in_array($purchase_order_detail->purchaseOrder->id,$data_id_po)){
                                    $data_id_po[] = $purchase_order_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }                     
                           
                        }
                        if($purchase_request_detail->materialRequestDetail()){
                            $mr=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$purchase_request_detail->lookable->materialRequest->post_date],
                                    ['name'=> "Vendor  : ".$purchase_request_detail->lookable->materialRequest->user->name],
                                 ],
                                'key'=>$purchase_request_detail->lookable->materialRequest->code,
                                'name'=>$purchase_request_detail->lookable->materialRequest->code,
                                'url'=>request()->root()."/admin/inventory/material_request?code=".CustomHelper::encrypt($purchase_request_detail->lookable->materialRequest->code),
                            ];
                            
                            $data_go_chart[]=$mr;
                            $data_link[]=[
                                'from'=>$purchase_request_detail->lookable->materialRequest->code,
                                'to'=>$query_pr->code,
                                'string_link'=>$purchase_request_detail->lookable->materialRequest->code.$query_pr->code,
                            ];
                            if(!in_array($purchase_request_detail->lookable->materialRequest->id,$data_id_mr)){
                                $data_id_mr[]= $purchase_request_detail->lookable->materialRequest->id;  
                                $added = true;
                            }
                           
                             
                        }
                    }
                }

                foreach($data_id_mr as $mr_id){
                    $query_material_request = MaterialRequest::find($mr_id);
                    foreach($query_material_request->materialRequestDetail as $row_material_request_detail){
                        if($row_material_request_detail->purchaseRequestDetail()->exists()){
                        
                            foreach($row_material_request_detail->purchaseRequestDetail as $row_purchase_request_detail){
                                $pr_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$row_purchase_request_detail->purchaseRequest->post_date],
                                        ['name'=> "Vendor  : ".$row_purchase_request_detail->purchaseRequest->user->name],
                                     ],
                                    'key'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'name'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_purchase_request_detail->purchaseRequest->code),
                                ];
    
                                $data_go_chart[]=$pr_tempura;
                                $data_link[]=[
                                    'from'=>$query_material_request->code,
                                    'to'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'string_link'=>$query_material_request->code.$row_purchase_request_detail->purchaseRequest->code,
                                ];
                                if(!in_array($row_purchase_request_detail->purchaseRequest->id,$data_id_pr)){
                                    $data_id_pr[] = $row_purchase_request_detail->purchaseRequest->id;
                                    $added = true;
                                }
                            }                     
                           
                        }
                        if($row_material_request_detail->goodIssueDetail()->exists()){
                        
                            foreach($row_material_request_detail->goodIssueDetail as $good_issue_detail){
                                $good_issue_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                        ['name'=> "Vendor  : ".$good_issue_detail->goodIssue->supplier->name],
                                     ],
                                    'key'=>$good_issue_detail->goodIssue->code,
                                    'name'=>$good_issue_detail->goodIssue->code,
                                    'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                ];
    
                                $data_go_chart[]=$good_issue_tempura;
                                $data_link[]=[
                                    'from'=>$query_material_request->code,
                                    'to'=>$good_issue_detail->goodIssue->code,
                                    'string_link'=>$query_material_request->code.$good_issue_detail->goodIssue->code,
                                ];
                               
                                if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                    $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                    $added = true;
                                }
                            }                     
                           
                        }
                    }
                }
            }       
            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){
                
                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }
                
                }
                $new_array = array_values($new_array);
                return $new_array;
            }

           
            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
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

    public function viewJournal(Request $request,$id){
        $query = OutgoingPayment::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td class="center-align">'.($row->note ? $row->note : '').'</td>
                    <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }
}