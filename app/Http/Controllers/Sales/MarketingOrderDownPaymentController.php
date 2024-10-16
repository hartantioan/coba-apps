<?php

namespace App\Http\Controllers\Sales;


use App\Exports\ExporMarketingDownPaymentTransactionPage;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Helpers\TreeHelper;
use App\Models\IncomingPayment;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\CancelDocument;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use App\Models\Place;
use App\Models\TaxSeries;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Models\MarketingOrderDownPaymentDetail;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\User;
use App\Models\Tax;
use App\Models\Menu;
use App\Models\UsedData;
use App\Models\MenuUser;
class MarketingOrderDownPaymentController extends Controller
{
    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'AR Down Payment',
            'content'       => 'admin.sales.down_payment',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = MarketingOrderDownPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getTaxSeries(Request $request){
        return response()->json(TaxSeries::getTaxCode($request->company_id,$request->date,$request->prefix_tax));
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'type',
            'document',
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
            'tax_id',
            'percent_tax',
            'is_include_tax',
            'subtotal',
            'total',
            'tax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDownPayment::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';
                $nodis = '';
                if($val->isOpenPeriod()){
                    $dis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }else{
                    $nodis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }
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
                    $val->type(),
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->taxId()->exists() ? $val->taxId->name : '-',
                    number_format($val->percent_tax,2,',','.').'%',
                    $val->isIncludeTax(),
                    $val->tax_no,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'SYSTEM' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'SYSTEM' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? 'SYSTEM' : 'SYSTEM'
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1  btn-small btn-flat waves-effect waves-light purple darken-2 white-text" data-popup="tooltip" title="Cancel" onclick="cancelStatus(`' . CustomHelper::encrypt($val->code) . '`)" '.$nodis.'><i class="material-icons dp48">cancel</i></button>
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
           /*  'code'			            => $request->temp ? ['required', Rule::unique('marketing_order_down_payments', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_down_payments,code',
			 */'account_id' 				=> 'required',
			'type'                      => 'required',
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'subtotal'                  => 'required',
            'tax'                       => 'required',
            'grandtotal'                => 'required',
            'note'                      => $request->arr_id ? '' : 'required',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
			'account_id.required' 				=> 'Customer tidak boleh kosong.',
			'type.required'                     => 'Tipe tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'subtotal.required'                 => 'Subtotal tidak boleh kosong.',
            'tax.required'                      => 'PPN tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
            'note.required'                     => 'Keterangan tidak boleh kosong.'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            /* $passedCreditLimit = true;

            $account = User::find($request->account_id);

            $grandtotalUnsentModCredit = $account->grandtotalUnsentModCredit();
            $grandtotalUnsentDoCredit = $account->grandtotalUninvoiceDoCredit();

            $balanceLimitCredit = $account->limit_credit - $account->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit - str_replace(',','.',str_replace('.','',$request->grandtotal));
            $totalLimitCredit = $account->limit_credit - $account->count_limit_credit - $grandtotalUnsentModCredit - $grandtotalUnsentDoCredit;

            if($balanceLimitCredit < 0){
                $passedCreditLimit = false;
            }

            if(!$passedCreditLimit){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf, saat ini seluruh / salah satu item terkena limit kredit dimana perhitungannya adalah sebagai berikut, Sisa limit kredit '.number_format($totalLimitCredit,2,',','.').' sedangkan nominal down payment : '.$request->grandtotal.' maka terjadi selisih nominal sebesar '.number_format($balanceLimitCredit,2,',','.').'.',
                ]);
            } */

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->temp))->first();

                    $approved = false;
                    $revised = false;

                    if($query->approval()){
                        foreach ($query->approval() as $detail){
                            foreach($detail->approvalMatrix as $row){
                                if($row->approved){
                                    $approved = true;
                                }

                                if($row->revised){
                                    $revised = true;
                                }
                            }
                        }
                    }

                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'AR Down Payment telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        /* CustomHelper::removeDeposit($query->account_id,$query->grandtotal); */

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/sales_down_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->type = $request->type;
                        $query->company_id = $request->company_id;
                        $query->tax_id = $request->tax_id > 0 ? $request->tax_id : NULL;
                        $query->is_tax = $request->tax_id > 0 ? '1' : NULL;
                        $query->is_include_tax = $request->is_include_tax;
                        $query->percent_tax = $request->percent_tax;
                        $query->document = $document;
                        $query->tax_no = $request->tax_no;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->post_date;
                        $query->note = $request->note;
                        $query->subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
                        $query->discount = 0;
                        $query->total = str_replace(',','.',str_replace('.','',$request->subtotal));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                        $query->rounding = 0;
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->status = '1';

                        $query->save();

                        foreach($query->marketingOrderDownPaymentDetail as $row){
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
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=MarketingOrderDownPayment::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $taxno = '';
                    if($request->tax_id > 0){
                        $no = TaxSeries::getTaxCode($request->company_id,$request->post_date,$request->prefix_tax);
                        if($no['status'] == 200){
                            $taxno = $no['no'];
                        }else{
                            return response()->json([
                                'status'  => 500,
                                'message' => 'Nomor seri pajak sudah habis terpakai.'
                            ]);
                        }
                    }

                    $query = MarketingOrderDownPayment::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'type'	                    => $request->type,
                        'company_id'                => $request->company_id,
                        'tax_id'                    => $request->tax_id > 0 ? $request->tax_id : NULL,
                        'is_tax'                    => $request->tax_id > 0 ? '1' : NULL,
                        'is_include_tax'            => $request->is_include_tax,
                        'percent_tax'               => $request->percent_tax,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/sales_down_payments') : NULL,
                        'tax_no'                    => $request->tax_no ? $taxno : NULL,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->post_date,
                        'note'                      => $request->note,
                        'subtotal'                  => str_replace(',','.',str_replace('.','',$request->subtotal)),
                        'discount'                  => 0,
                        'total'                     => str_replace(',','.',str_replace('.','',$request->subtotal)),
                        'tax'                       => str_replace(',','.',str_replace('.','',$request->tax)),
                        'rounding'                  => 0,
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                if($request->arr_id){
                    $arr = array_unique($request->arr_id);
                    foreach($arr as $key => $row){
                        MarketingOrderDownPaymentDetail::create([
                            'marketing_order_down_payment_id'   => $query->id,
                            'marketing_order_id'                => intval($row),
                        ]);
                    }
                }

                CustomHelper::sendApproval('marketing_order_down_payments',$query->id,$query->note);
                CustomHelper::sendNotification('marketing_order_down_payments',$query->id,'Pengajuan AR Down Payment No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new MarketingOrderDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit sales order down payment.');

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

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12 mt-1"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Sales Order</th>
                            </tr>
                            <tr>
                                <th class="center-align">Nomor</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        $totalgrandtotal=0;
        if($data->marketingOrderDownPaymentDetail()->exists()){
            foreach($data->marketingOrderDownPaymentDetail as $row){
                $totalgrandtotal+=$row->marketingOrder->grandtotal;
                $string .= '<tr>
                    <td class="center-align">'.$row->marketingOrder->code.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->marketingOrder->post_date)).'</td>
                    <td class="">'.$row->marketingOrder->note_internal.' - '.$row->marketingOrder->note_external.'</td>
                    <td class="right-align">'.number_format($row->marketingOrder->grandtotal,2,',','.').'</td>
                </tr>';
            }
            $string .= '<tr>
                    <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
                </tr>  
            ';
        }else{
            $string .= '<tr>
                <td colspan="4" class="center-align">Data tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Tanggal</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
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
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->user->name.' - Tanggal Dipakai: '.$data->used->created_at.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $mod = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Print AR Down Payment',
                'data'      => $mod
            ];

            return view('admin.approval.marketing_order_down_payment', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
           
            $pdf = PrintHelper::print($pr,'Print AR Down Payment','a4','portrait','admin.print.sales.order_down_payment_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            /* $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0)); */
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
            return $document_po;
        }else{
            abort(404);
        }
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
            foreach($request->arr_id as $key => $row){
                $pr = MarketingOrderDownPayment::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Print AR Down Payment','a4','portrait','admin.print.sales.order_down_payment_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    /* $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0)); */
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
            }

            $merger = new Merger();

            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }

            $result = $merger->merge();

            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
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
                        $query = MarketingOrderDownPayment::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print AR Down Payment','a4','portrait','admin.print.sales.order_down_payment_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            /* $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0)); */
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }

                    $result = $merger->merge();

                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                        $query = MarketingOrderDownPayment::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Print AR Down Payment','a4','portrait','admin.print.sales.order_down_payment_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            /* $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0)); */
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
                    $result = $merger->merge();

                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function show(Request $request){
        $modp = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $modp['code_place_id'] = substr($modp->code,7,2);
        $modp['account_name'] = $modp->account->employee_no.' - '.$modp->account->name;
        $modp['subtotal'] = number_format($modp->subtotal,2,',','.');
        $modp['discount'] = number_format($modp->discount,2,',','.');
        $modp['total'] = number_format($modp->total,2,',','.');
        $modp['tax'] = number_format($modp->tax,2,',','.');
        $modp['grandtotal'] = number_format($modp->grandtotal,2,',','.');
        $modp['currency_rate'] = number_format($modp->currency_rate,2,',','.');

        $arr = [];

        foreach($modp->marketingOrderDownPaymentDetail as $row){
            $details = [];
            foreach($row->marketingOrder->marketingOrderDetail as $rowdetail){
                $details[] = [
                    'item_name'     => $rowdetail->item->code.' - '.$rowdetail->item->name,
                    'total'         => CustomHelper::formatConditionalQty($rowdetail->total),
                ];
            }
            $arr[] = [
                'id'   			=> $row->marketingOrder->id,
                'type'          => $row->marketingOrder->getTable(),
                'post_date'     => date('d/m/Y',strtotime($row->marketingOrder->post_date)),
                'note'          => $row->marketingOrder->note_internal.' - '.$row->marketingOrder->note_external,
                'code'          => $row->marketingOrder->code,
                'total'         => CustomHelper::formatConditionalQty($row->marketingOrder->total),
                'tax'           => CustomHelper::formatConditionalQty($row->marketingOrder->tax),
                'grandtotal'    => CustomHelper::formatConditionalQty($row->marketingOrder->grandtotal),
                'details'       => $details,
            ];
        }

        $modp['details'] = $arr;

		return response()->json($modp);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($id))->first();
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
                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += $row->nominal;
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += $row->nominal;
                }
                
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
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval('marketing_order_down_payments',$query->id);

            activity()
                ->performedOn(new MarketingOrderDownPayment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the AP Down Payment data');

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

    public function voidStatus(Request $request){
        $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya.'
                ];
            }else{
                $newtaxno = '';
                if($query->tax_no){
                    $array = explode('.',$query->tax_no);
                    $newarray = array_slice($array, 1);
                    $newtaxno = '011.'.implode('.',$newarray);
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s'),
                    'tax_no'    => $newtaxno ?? NULL,
                ]);

                activity()
                    ->performedOn(new MarketingOrderDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the  data');
    
                CustomHelper::sendNotification('marketing_order_down_payments',$query->id,'AR Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('marketing_order_down_payments',$query->id);
                /* CustomHelper::removeJournal('marketing_order_down_payments',$query->id); */

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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
       

        $data_go_chart=[];
        $data_link=[];

        if($query){
            $data_mo_dp = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_mo_dp;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_mo_dp',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2;   


           

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
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function sendUsedData(Request $request){
        $mo = MarketingOrder::find($request->id);

        if(!$mo->used()->exists()){
            CustomHelper::sendUsedData($request->type,$request->id,'Form AR Down Payment');
            return response()->json([
                'status'    => 200,
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Dokumen no. '.$mo->used->lookable->code.' telah dipakai di '.$mo->used->ref.', oleh '.$mo->used->user->name.'.'
            ]);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function done(Request $request){
        $query_done = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new MarketingOrderDownPayment())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Order Down Payment data');
    
                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }
    public function cancelStatus(Request $request){
        $query = MarketingOrderDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($request->cancel_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada tanggal cancel void telah ditutup oleh Akunting.'
                ]);
            }

            if(in_array($query->status,['4','5','8'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                
                /* CustomHelper::removeDeposit($query->account_id,$query->grandtotal); */
                CustomHelper::removeApproval($query->getTable(),$query->id);
               
                $query->update([
                    'status'    => '8',
                    'done_id'   => session('bo_id'),
                ]);

                $cd = CancelDocument::create([
                    'code'          => CancelDocument::generateCode('CAPN',substr($query->code,7,2),$request->cancel_date),
                    'user_id'       => session('bo_id'),
                    'post_date'     => $request->cancel_date,
                    'lookable_type' => $query->getTable(),
                    'lookable_id'   => $query->id,
                ]);

                CustomHelper::cancelJournal($cd,$request->cancel_date);
    
                activity()
                    ->performedOn(new MarketingOrderDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void cancel the marketing Order Downpayment data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'marketing Order Downpayment No. '.$query->code.' telah ditutup dengan tombol cancel void.','marketing Order Downpayment No. '.$query->code.' telah ditutup dengan tombol cancel void.',$query->user_id);
    
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

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type = $request->type ? $request->type : '';
        $account = $request->account ? $request->account : '';
        $company = $request->company ? $request->company : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExporMarketingDownPaymentTransactionPage($search,$status,$type,$account,$company,$currency,$end_date,$start_date), 'marketing_order_down_payment'.uniqid().'.xlsx');
    }
}