<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodIssueRequest;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\Item;
use App\Models\Menu;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\LandedCost;
use App\Models\Machine;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;

use App\Models\Place;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\UsedData;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
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
use App\Helpers\TreeHelper;
use App\Models\PurchaseMemoDetail;
use App\Exports\ExportPurchaseMemo;
use App\Exports\ExportPurchaseMemoTransactionPage;
use App\Models\Division;
use App\Models\MenuUser;
use App\Models\PurchaseInvoiceDetail;

class PurchaseMemoController extends Controller
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
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'AP Memo',
            'content'       => 'admin.purchase.memo',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Division::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PurchaseMemo::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getDetails(Request $request){

        if($request->type == 'pi'){
            $data = PurchaseInvoice::where('id',$request->id)->whereIn('status',['2','3'])->first();
        }elseif($request->type == 'podp'){
            $data = PurchaseDownPayment::find($request->id);
        }

        if($data->used()->exists()){
            if($request->type == 'pi'){
                $data['status'] = '500';
                $data['message'] = 'A/P Invoice '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }elseif($request->type == 'podp'){
                $data['status'] = '500';
                $data['message'] = 'Purchase Down Payment '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
            }
        }else{
            $passed = true;
            if(!$data->hasBalanceMemo()){
                $passed = false;
            }

            if($request->type == 'podp'){
                if($data->purchaseInvoiceDp()->exists()){
                    $data['status'] = '500';
                    $data['message'] = 'Purchase Down Payment '.$data->code.' telah digunakan sebagai down payment pada invoice, anda tidak bisa menggunakannya.';
                    return response()->json($data);
                }
            }
            
            if($passed){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Purchase Memo');

                if($request->type == 'pi'){
                    $details = [];
                    foreach($data->purchaseInvoiceDetail as $row){
                        $details[] = [
                            'id'            => $row->id,
                            'rawcode'       => $row->getCode(),
                            'tax_no'        => $data->tax_no,
                            'tax_cut_no'    => $data->tax_cut_no,
                            'cut_date'      => date('d/m/Y',strtotime($data->cut_date)),
                            'spk_no'        => $data->spk_no,
                            'invoice_no'    => $data->invoice_no,
                            'code'          => CustomHelper::encrypt($data->code),
                            'type'          => $row->getTable(),
                            'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                            'total'         => number_format($row->total,2,',','.'),
                            'tax'           => number_format($row->tax,2,',','.'),
                            'wtax'          => number_format($row->wtax,2,',','.'),
                            'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                            'note'          => $row->note ? $row->note : '',
                            'note2'         => $row->note2 ? $row->note2 : '',
                            'account_name'  => $data->account->name,
                            'is_include_tax'=> $row->is_include_tax,
                            'percent_tax'   => $row->percent_tax,
                            'percent_wtax'  => $row->percent_wtax,
                            'tax_id'        => $row->tax_id ? $row->tax_id : '',
                            'wtax_id'       => $row->wtax_id ? $row->wtax_id : '',
                            'balance'       => $row->balanceMemo(),
                            'balanceformat' => number_format($row->balanceMemo(),2,',','.'),
                            'qty'           => $row->goodReceiptDetail() ? number_format($row->lookable->getBalanceReturn(),2,',','.') : 1,
                        ];
                    }
                    $data['rawcode'] = $data->code;
                    $data['code'] = CustomHelper::encrypt($data->code);
                    $data['type'] = $data->getTable();
                    $data['account_id'] = $data->account_id;
                    $data['account_name'] = $data->account->name;
                    $data['details'] = $details;
                }elseif($request->type == 'podp'){
                    $data['rawcode'] = $data->code;
                    $data['tax_no'] = '-';
                    $data['tax_cut_no'] = '-';
                    $data['cut_date'] = '-';
                    $data['spk_no'] = '-';
                    $data['invoice_no'] = '-';
                    $data['code'] = CustomHelper::encrypt($data->code);
                    $data['type'] = $data->getTable();
                    $data['post_date'] = date('d/m/Y',strtotime($data->post_date));
                    $data['total'] = number_format($data->total,2,',','.');
                    $data['tax'] = number_format($data->tax,2,',','.');
                    $data['wtax'] = number_format($data->wtax,2,',','.');
                    $data['grandtotal'] = number_format($data->grandtotal,2,',','.');
                    $data['account_id'] = $data->account_id;
                    $data['account_name'] = $data->supplier->name;
                    $data['is_include_tax'] = $data->is_include_tax;
                    $data['percent_tax'] = $data->percent_tax;
                    $data['percent_wtax'] = 0;
                    $data['tax_id'] = $data->tax_id ? $data->tax_id : '';
                    $data['wtax_id'] = '';
                    $data['balance'] = $data->balanceMemo();
                    $data['balanceformat'] = number_format($data->balanceMemo(),2,',','.');
                    $data['qty'] = 1;
                    $data['note2'] = '';
                }

            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada A/P Invoice / purchase down payment '.$data->code.' telah digunakan pada purchase memo.';
            }
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
            'rounding',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseMemo::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = PurchaseMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('return_tax_no', 'like', "%$search%")
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseMemo::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('return_tax_no', 'like', "%$search%")
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }

                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
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
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->return_tax_no,
                    date('d/m/Y',strtotime($val->return_date)),
                    $val->note,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->rounding,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'sistem' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'sistem' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? null : null
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			        => $request->temp ? ['required', Rule::unique('purchase_memos', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:purchase_memos,code',
			 */'account_id' 			=> 'required',
            'company_id'            => 'required',
            'post_date'             => 'required',
            'return_date'             => 'required',
            'arr_type'                  => 'required|array',
            'arr_code'                  => 'required|array',
            'arr_description'           => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_wtax'                  => 'required|array',
            'arr_grandtotal'            => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            /* 'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'return_date.required'                => 'Tanggal retur tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty barang /jasa tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty barang / jasa harus dalam bentuk array.',
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
            $rounding = str_replace(',','.',str_replace('.','',$request->rounding));

            $passed = true;
            $passedQtyReturn = true;
            $arrErrorQtyReturn = [];

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
                $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                $wtax += str_replace(',','.',str_replace('.','',$request->arr_wtax[$key]));
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
                
                if($request->arr_type[$key] == 'purchase_invoice_details'){
                    $pid = PurchaseInvoiceDetail::find(intval($request->arr_code[$key]));
                    if($pid){
                        if($pid->lookable_type == 'good_receipt_details'){
                            $stock = $pid->lookable->item->getStockPlaceWarehouse($pid->lookable->place_id,$pid->lookable->warehouse_id);
                            $qtyReturn = str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) * $pid->lookable->qty_conversion;
                            if($qtyReturn > $stock){
                                $passedQtyReturn = false;
                                $arrErrorQtyReturn[] = $pid->lookable->item->name;
                            }
                        }
                    }
                }
            }

            if(!$passedQtyReturn){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Stok item tidak ada pada gudang. Daftarnya adalah sbb : '.implode(', ',$arrErrorQtyReturn),
                ]);
            }

            $grandtotal += $rounding;

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseMemo::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Purchase Memo telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/purchase_memos');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->return_tax_no = $request->return_tax_no;
                        $query->return_date = $request->return_date;
                        $query->total = round($total,2);
                        $query->tax = round($tax,2);
                        $query->wtax = round($wtax,2);
                        $query->rounding = round($rounding,2);
                        $query->grandtotal = round($grandtotal,2);
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

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
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=PurchaseMemo::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = PurchaseMemo::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'return_tax_no'             => $request->return_tax_no,
                        'return_date'               => $request->return_date,
                        'total'                     => round($total,2),
                        'tax'                       => round($tax,2),
                        'wtax'                      => round($wtax,2),
                        'rounding'                  => round($rounding,2),
                        'grandtotal'                => round($grandtotal,2),
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
                            PurchaseMemoDetail::create([
                                'purchase_memo_id'      => $query->id,
                                'lookable_type'         => $row,
                                'lookable_id'           => $request->arr_code[$key],
                                'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                                'description'           => $request->arr_description[$key],
                                'description2'          => $request->arr_description2[$key],
                                'is_include_tax'        => $request->arr_is_include_tax[$key],
                                'tax_id'                => $request->arr_id_tax[$key] ? $request->arr_id_tax[$key] : NULL,
                                'wtax_id'               => $request->arr_id_wtax[$key] ? $request->arr_id_wtax[$key] : NULL,
                                'percent_tax'           => $request->arr_percent_tax[$key],
                                'percent_wtax'          => $request->arr_percent_wtax[$key],
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
        $data   = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">AP Inv./AP DP</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Keterangan 1</th>
                                <th class="center-align">Keterangan 2</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPh</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        $totals=0;
        $totalppn=0;
        $totalpph=0;
        $totalgrandtotal=0;
        if(count($data->purchaseMemoDetail) > 0){
            foreach($data->purchaseMemoDetail as $key => $row){
                $totals+=$row->total;
                $totalppn+=$row->tax;
                $totalpph+=$row->wtax;
                $totalgrandtotal+=$row->grandtotal;
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->getCode().'</td>
                    <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                    <td class="center-align">'.$row->description.'</td>
                    <td class="center-align">'.$row->description2.'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                </tr>';
            }
            $string .= '<tr>
                    <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="4"> Total </td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalppn, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpph, 2, ',', '.') . '</td>
                    <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
                </tr>  
            ';
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="9">Data detail tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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

    public function show(Request $request){
        $data = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();
        $data['code_place_id'] = substr($data->code,7,2);
        $data['account_name'] = $data->account->name;
        $details = [];
        foreach($data->purchaseMemoDetail as $row){
            if($row->lookable_type == 'purchase_invoice_details'){
                $details[] = [
                    'id'            => $row->lookable_id,
                    'rawcode'       => $row->lookable->getCode(),
                    'tax_no'        => $row->lookable->purchaseInvoice->tax_no,
                    'tax_cut_no'    => $row->lookable->purchaseInvoice->tax_cut_no,
                    'cut_date'      => date('d/m/Y',strtotime($row->lookable->purchaseInvoice->cut_date)),
                    'spk_no'        => $row->lookable->purchaseInvoice->spk_no,
                    'invoice_no'    => $row->lookable->purchaseInvoice->invoice_no,
                    'code'          => CustomHelper::encrypt($row->lookable->getCode()),
                    'type'          => $row->lookable_type,
                    'post_date'     => date('d/m/Y',strtotime($row->lookable->purchaseInvoice->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'note'          => $row->description ? $row->description : '',
                    'note2'         => $row->description2 ? $row->description2 : '',
                    'account_name'  => $row->lookable->purchaseInvoice->account->name,
                    'is_include_tax'=> $row->is_include_tax,
                    'percent_tax'   => $row->percent_tax,
                    'percent_wtax'  => $row->percent_wtax,
                    'tax_id'        => $row->tax_id ? $row->tax_id : '',
                    'wtax_id'       => $row->wtax_id ? $row->wtax_id : '',
                    'balance'       => $row->total + $row->lookable->balanceMemo(),
                    'balanceformat' => number_format($row->total + $row->lookable->balanceMemo(),2,',','.'),
                    'qty_max'       => $row->lookable->goodReceiptDetail() ? number_format($row->lookable->lookable->getBalanceReturn(),2,',','.') : 1,
                    'qty'           => CustomHelper::formatConditionalQty($row->qty,2,',','.'),
                ];
            }elseif($row->lookable_type == 'purchase_down_payments'){
                $details[] = [
                    'id'            => $row->lookable_id,
                    'rawcode'       => $row->lookable->code,
                    'tax_no'        => '-',
                    'tax_cut_no'    => '-',
                    'cut_date'      => '-',
                    'spk_no'        => '-',
                    'invoice_no'    => '-',
                    'code'          => $row->lookable->code,
                    'type'          => $row->lookable_type,
                    'post_date'     => date('d/m/Y',strtotime($row->lookable->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'note'          => $row->description ? $row->description : '',
                    'note2'         => $row->description2 ? $row->description2 : '',
                    'account_name'  => $row->lookable->supplier->name,
                    'is_include_tax'=> $row->is_include_tax,
                    'percent_tax'   => $row->percent_tax,
                    'percent_wtax'  => $row->percent_wtax,
                    'tax_id'        => $row->tax_id ? $row->tax_id : '',
                    'wtax_id'       => $row->wtax_id ? $row->wtax_id : '',
                    'balance'       => $row->total + $row->lookable->balanceMemo(),
                    'balanceformat' => number_format($row->total + $row->lookable->balanceMemo(),2,',','.'),
                    'qty_max'       => 1,
                    'qty'           => CustomHelper::formatConditionalQty($row->qty,2,',','.'),
                ];
            }
        }
        $data['details'] = $details;

        return response()->json($data);
    }

    public function voidStatus(Request $request){
        $query = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                CustomHelper::removeApproval('purchase_memos',$query->id);
                CustomHelper::removeJournal('purchase_memos',$query->id);

                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseMemo())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase memo data');
    
                CustomHelper::sendNotification('purchase_memos',$query->id,'Purchase Memo No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
    
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
        $query = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval('purchase_memos',$query->id);

            $query->purchaseMemoDetail()->delete();

            activity()
                ->performedOn(new PurchaseMemo())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase memo data');

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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();

        $data_go_chart=[];
        
        $data_link=[];
        if($query) {
            $data_memo = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/finance/purchase_memo?code=".CustomHelper::encrypt($query->code),           
            ];
            
            $data_go_chart[]=$data_memo;
           
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_memo',$query->id);
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
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = PurchaseMemo::where('code',CustomHelper::decrypt($id))->first();
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
                $pr = PurchaseMemo::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Print A/P Invoice',
                        'data'      => $pr
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.purchase.memo_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
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
                        $query = PurchaseMemo::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Purchase Memo',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.memo_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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
                        $query = PurchaseMemo::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Purchase Memo',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.purchase.memo_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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

    public function approval(Request $request,$id){
        
        $pr = PurchaseMemo::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Memo',
                'data'      => $pr
            ];

            return view('admin.approval.purchase_memo', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = PurchaseMemo::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Order',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.purchase.memo_individual', $data)->setPaper('a5', 'landscape');
            // $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
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
		return Excel::download(new ExportPurchaseMemo($post_date,$end_date,$mode), 'purchase_memo'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = PurchaseMemo::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new PurchaseMemo())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Purchase Memo data');
    
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

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';;
        $company = $request->company ? $request->company : '';
        
        $supplier = $request->supplier? $request->supplier : '';
        
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';
      
		return Excel::download(new ExportPurchaseMemoTransactionPage($search,$status,$company,$supplier,$end_date,$start_date,$modedata), 'purchase_down_payment'.uniqid().'.xlsx');
    }
}