<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoiceDp;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LandedCost;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodReceipt;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseInvoice;
use App\Models\User;
use App\Models\Tax;

class PurchaseInvoiceController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'         => 'Invoice Pembelian',
            'content'       => 'admin.purchase.invoice',
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getGoodReceiptLandedCost(Request $request){
        $account = User::find($request->id);
        $account['deposit'] = number_format($account->deposit,2,',','.');

        $details = [];
        $downpayments = [];
        
        $datadp = PurchaseDownPayment::where('account_id',$request->id)->whereIn('status',['2','3'])->get();

        foreach($datadp as $row){
            if($row->balanceInvoice() > 0){
                $downpayments[] = [
                    'rawcode'       => $row->code,
                    'code'          => CustomHelper::encrypt($row->code),
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'balance'       => number_format($row->balanceInvoice(),2,',','.'),
                ];
            }
        }
        
        $datapo = PurchaseOrder::whereIn('status',['2','3'])->where('inventory_type','2')->where('account_id',$request->id)->get();

        foreach($datapo as $row){
            if($row->balanceInvoice() > 0){
                $details[] = [
                    'type'          => 'purchase_order',
                    'code'          => CustomHelper::encrypt($row->code),
                    'rawcode'       => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/y',strtotime($row->post_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'info'          => '',
                    'top'           => $row->payment_term,
                    'delivery_no'   => '-',
                    'purchase_no'   => 'NO PO - '.$row->code,
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $datagr = GoodReceipt::where('status','2')->where('account_id',$request->id)->get();
        
        $top = 0;
        
        foreach($datagr as $row){
            if($row->balanceInvoice() > 0){
                $info = '';

                $tax_id = 0;
                $wtax_id = 0;
                $is_include_tax = '0';
                $percent_tax = 0;
                $percent_wtax = 0;
                

                foreach($row->goodReceiptDetail as $rowdetail){
                    $tax_id = $rowdetail->purchaseOrderDetail->tax_id;
                    $wtax_id = $rowdetail->purchaseOrderDetail->wtax_id;
                    $is_include_tax = $rowdetail->purchaseOrderDetail->is_include_tax;
                    $percent_tax = $rowdetail->purchaseOrderDetail->percent_tax;
                    $percent_wtax = $rowdetail->purchaseOrderDetail->percent_wtax;
                    if($top < $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term){
                        $top = $rowdetail->purchaseOrderDetail->purchaseOrder->payment_term;
                    }
                    $info .= 'Diterima '.$rowdetail->qty.' '.$rowdetail->item->buyUnit->code.' dari '.$rowdetail->purchaseOrderDetail->qty.' '.$rowdetail->item->buyUnit->code.'<br>';
                }

                $details[] = [
                    'type'          => 'good_receipt',
                    'code'          => CustomHelper::encrypt($row->code),
                    'rawcode'       => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/y',strtotime($row->due_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'info'          => $info,
                    'top'           => $top,
                    'delivery_no'   => 'NO SJ - '.$row->delivery_no,
                    'purchase_no'   => 'NO PO - '.$row->getPurchaseCode(),
                    'list_item'     => $row->getListItem(),
                ];
            }
        }
    
        $datalc = LandedCost::where('account_id',$request->id)->where('status','2')->get();

        foreach($datalc as $row){
            if($row->balanceInvoice() > 0){
                $details[] = [
                    'type'          => 'landed_cost',
                    'code'          => CustomHelper::encrypt($row->code),
                    'rawcode'       => $row->code,
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/y',strtotime($row->due_date)),
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'info'          => '',
                    'top'           => $top,
                    'delivery_no'   => 'No SJ GRPO - '.$row->goodReceipt->delivery_no,
                    'purchase_no'   => 'NO PO - '.$row->goodReceipt->getPurchaseCode(),
                    'list_item'     => $row->getListItem(),
                ];
            }
        }

        $account['details'] = $details;
        $account['downpayments'] = $downpayments;

        return response()->json($account);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'received_date',
            'due_date',
            'document_date',
            'type',
            'document',
            'note',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'subtotal',
            'percent_discount',
            'nominal_discount',
            'total',
            'tax',
            'grandtotal',
            'downpayment',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseInvoice::count();
        
        $query_data = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHas('landedCost',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%");
                                })->orWhereHas('goodReceipt',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
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
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHas('landedCost',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%");
                                })->orWhereHas('goodReceipt',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
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
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->received_date)),
                    date('d/m/y',strtotime($val->due_date)),
                    date('d/m/y',strtotime($val->document_date)),
                    $val->type(),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    $val->tax_no,
                    $val->tax_cut_no,
                    date('d/m/y',strtotime($val->cut_date)),
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->percent_discount,2,',','.'),
                    number_format($val->nominal_discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->downpayment,2,',','.'),
                    number_format($val->balance,2,',','.'),
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
			'type'                  => 'required',
            'company_id'            => 'required',
            'post_date'             => 'required',
            'received_date'         => 'required',
            'due_date'              => 'required',
            'document_date'         => 'required',
            'arr_type'                  => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_grandtotal'            => 'required|array'
		], [
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
			'type.required'                     => 'Tipe invoice tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'received_date.required'            => 'Tanggal terima tidak boleh kosong.',
            'due_date.required'                 => 'Tanggal tenggat tidak boleh kosong.',
            'document_date.required'            => 'Tanggal dokumen tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_total.required'                => 'Nominal total tidak boleh kosong.',
            'arr_total.array'                   => 'Nominal harus dalam bentuk array.',
            'arr_tax.required'                  => 'Nominal pajak tidak boleh kosong.',
            'arr_tax.array'                     => 'Nominal pajak harus dalam bentuk array.',
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
            $balance = 0;
            $downpayment = str_replace(',','.',str_replace('.','',$request->downpayment));
            $rounding = str_replace(',','.',str_replace('.','',$request->rounding));

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
                $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                $wtax += str_replace(',','.',str_replace('.','',$request->arr_wtax[$key]));
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
            }

            $balance = $grandtotal - $downpayment + $rounding;

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Order Down Payment telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){

                        if($request->has('document')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('document')->store('public/purchase_invoices');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->received_date = $request->received_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->type = $request->type;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->wtax = round($wtax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->downpayment = round($downpayment,3);
                        $query->rounding = round($rounding,3);
                        $query->balance = round($balance,3);
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->tax_no = $request->tax_no;
                        $query->tax_cut_no = $request->tax_cut_no;
                        $query->cut_date = $request->cut_date;
                        $query->spk_no = $request->spk_no;
                        $query->invoice_no = $request->invoice_no;

                        $query->save();

                        foreach($query->purchaseInvoiceDetail as $row){
                            $row->delete();
                        }

                        foreach($query->purchaseInvoiceDp as $row){
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
                    $query = PurchaseInvoice::create([
                        'code'			            => PurchaseInvoice::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'received_date'             => $request->received_date,
                        'due_date'                  => $request->due_date,
                        'document_date'             => $request->document_date,
                        'type'                      => $request->type,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'wtax'                      => round($wtax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'downpayment'               => round($downpayment,3),
                        'rounding'                  => round($rounding,3),
                        'balance'                   => round($balance,3),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_invoices') : NULL,
                        'status'                    => '1',
                        'tax_no'                    => $request->tax_no,
                        'tax_cut_no'                => $request->tax_cut_no,
                        'cut_date'                  => $request->cut_date,
                        'spk_no'                    => $request->spk_no,
                        'invoice_no'                => $request->invoice_no
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
                            PurchaseInvoiceDetail::create([
                                'purchase_invoice_id'   => $query->id,
                                'good_receipt_id'       => $row == 'good_receipt' ? GoodReceipt::where('code',CustomHelper::decrypt($request->arr_code[$key]))->first()->id : NULL,
                                'landed_cost_id'        => $row == 'landed_cost' ? LandedCost::where('code',CustomHelper::decrypt($request->arr_code[$key]))->first()->id : NULL,
                                'purchase_order_id'     => $row == 'purchase_order' ? PurchaseOrder::where('code',CustomHelper::decrypt($request->arr_code[$key]))->first()->id : NULL,
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

                if($request->arr_dp_code){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_dp_code as $key => $row){
                            PurchaseInvoiceDp::create([
                                'purchase_invoice_id'       => $query->id,
                                'purchase_down_payment_id'  => PurchaseDownPayment::where('code',CustomHelper::decrypt($row))->first()->id,
                                'nominal'                   => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('purchase_invoices',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_invoices',$query->id,'Pengajuan Purchase Invoice No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::removeDeposit($query->account_id,$query->downpayment);

                activity()
                    ->performedOn(new PurchaseInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase invoice.');

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
        $data   = PurchaseInvoice::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">GR. PO / Landed Cost / Purchase Order</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">PPH</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->purchaseInvoiceDetail) > 0){
            foreach($data->purchaseInvoiceDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.($row->getCode()).'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="6">Data detail tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
        <thead>
            <tr>
                <th class="center-align" colspan="4">Daftar Down Payment Dipakai</th>
            </tr>
            <tr>
                <th class="center-align">No.</th>
                <th class="center-align">No Down Payment</th>
                <th class="center-align">Total</th>
                <th class="center-align">Dipakai</th>
            </tr>
        </thead><tbody>';

        if(count($data->purchaseInvoiceDp) > 0){
            foreach($data->purchaseInvoiceDp as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->purchaseDownPayment->code.'</td>
                    <td class="right-align">'.number_format($row->purchaseDownPayment->grandtotal,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Data down payment tidak ditemukan.</td>
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

    public function approval(Request $request,$id){
        
        $pi = PurchaseInvoice::where('code',CustomHelper::decrypt($id))->first();
                
        if($pi){
            $data = [
                'title'     => 'Print Purchase Invoice',
                'data'      => $pi
            ];

            return view('admin.approval.purchase_invoice', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pi = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        $pi['account_name'] = $pi->account->name;
        $pi['total'] = number_format($pi->total,2,',','.');
        $pi['tax'] = number_format($pi->tax,2,',','.');
        $pi['wtax'] = number_format($pi->wtax,2,',','.');
        $pi['grandtotal'] = number_format($pi->grandtotal,2,',','.');
        $pi['downpayment'] = number_format($pi->downpayment,2,',','.');

        $downpayments = [];
        
        foreach($pi->purchaseInvoiceDp as $row){
            $downpayments[] = [
                'rawcode'       => $row->purchaseDownPayment->code,
                'code'          => CustomHelper::encrypt($row->purchaseDownPayment->code),
                'post_date'     => date('d/m/y',strtotime($row->purchaseDownPayment->post_date)),
                'nominal'       => number_format($row->nominal,2,',','.'),
                'grandtotal'    => number_format($row->purchaseDownPayment->grandtotal,2,',','.'),
            ];
        }

        $arr = [];

        foreach($pi->purchaseInvoiceDetail as $row){
            $arr[] = [
                'code'                      => $row->good_receipt_id ? CustomHelper::encrypt($row->goodReceipt->code) : CustomHelper::encrypt($row->landedCost->code),
                'rawcode'                   => $row->good_receipt_id ? $row->goodReceipt->code : $row->landedCost->code,
                'post_date'                 => $row->good_receipt_id ? date('d/m/y',strtotime($row->goodReceipt->post_date)) : date('d/m/y',strtotime($row->landedCost->post_date)),
                'due_date'                  => $row->good_receipt_id ? date('d/m/y',strtotime($row->goodReceipt->due_date)) : date('d/m/y',strtotime($row->landedCost->due_date)),
                'type'                      => $row->good_receipt_id ? 'good_receipt' : 'landed_cost',
                'total'                     => number_format($row->total,2,',','.'),
                'tax'                       => number_format($row->tax,2,',','.'),
                'wtax'                      => number_format($row->wtax,2,',','.'),
                'grandtotal'                => number_format($row->grandtotal,2,',','.'),
                'info'                      => '-',
                'delivery_no'               => 'No SJ GRPO - '.($row->good_receipt_id ? $row->goodReceipt->delivery_no : $row->landedCost->goodReceipt->delivery_no),
                'purchase_no'               => 'NO PO - '.($row->good_receipt_id ? $row->goodReceipt->getPurchaseCode() : $row->landedCost->goodReceipt->getPurchase()),
                'list_item'                 => $row->good_receipt_id ? $row->goodReceipt->getListItem() : $row->landedCost->getListItem(),
            ];
        }

        $pi['details'] = $arr;
        $pi['downpayments'] = $downpayments;
        				
		return response()->json($pi);
    }

    public function voidStatus(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new PurchaseInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase invoice data');
    
                CustomHelper::sendNotification('purchase_invoices',$query->id,'Purchase Invoice No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('purchase_invoices',$query->id);
                CustomHelper::addDeposit($query->account_id,$query->downpayment);

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
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Landed coast telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('purchase_requests',$query->id);
            CustomHelper::addDeposit($query->account_id,$query->downpayment);
            
            $query->purchaseInvoiceDetail()->delete();
            $query->PurchaseInvoiceDp()->delete();

            activity()
                ->performedOn(new PurchaseInvoice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase invoice data');

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
            'title' => 'PURCHASE INVOICE REPORT',
            'data' => PurchaseInvoice::where(function($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('total', 'like', "%$request->search%")
                            ->orWhere('tax', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('downpayment', 'like', "%$request->search%")
                            ->orWhere('balance', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhere('tax_no', 'like', "%$request->search%")
                            ->orWhere('tax_cut_no', 'like', "%$request->search%")
                            ->orWhere('spk_no', 'like', "%$request->search%")
                            ->orWhere('invoice_no', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('account',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($request){
                                $query->whereHas('landedCost',function($query) use($request){
                                    $query->where('code','like',"%$request->search%");
                                })->orWhereHas('goodReceipt',function($query) use($request){
                                    $query->where('code','like',"%$request->search%");
                                });
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company){
                    $query->where('company_id',$request->company);
                }
            })
            ->get()
		];
		
		return view('admin.print.purchase.invoice', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportPurchaseInvoice($request->search,$request->status,$request->type,$request->company,$request->account,$this->dataplaces), 'purchase_invoice'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        $query = PurchaseInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        $data_good_receipts=[];
        $data_purchase_requests=[];

        $data_id_po = [];
        $data_id_gr = [];
        $data_pos = [];

        $data_purchase_downpayment = [];
        $data_invoices=[];
        $data_go_chart=[];
        $data_link=[];
        if($query) {
            /*mengambil invoice*/
            $data_invoice = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "url", 'type'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($query->code)],
                 ],
                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[] = $data_invoice;
            $data_invoices[]=$data_invoice;
            $data_id_invoice[]=$query->id;
            foreach($query->purchaseInvoiceDetail as $row){
                if($row->purchaseOrder()->exists()){
                    foreach($row->purchaseOrder as $row_po){
                        $po =[
                            "name"=>$row_po->code,
                            "key" => $row_po->code,
                            "color"=>"lightblue",
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_po->post_date],
                             ],
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                        ];
                        /*memasukkan ke node data dan linknya*/
                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$query->code,
                            'to'=>$row_po->code,
                        ];
                        $data_id_po[]= $row_po->id;  
                        foreach($row_po->purchaseOrderDetail as $po_detail){
                            if($po_detail->goodReceiptDetail->exists()){
                                foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                    $data_good_receipt=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                            ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
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
                }
                /*  melihat apakah ada hubungan grpo tanpa po */
                if($row->goodReceipt()->exists()){

                    $data_good_receipt=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->goodReceipt->post_date],
                        ],
                        "key" => $row->goodReceipt->code,
                        "name" => $row->goodReceipt->code,
                        'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->goodReceipt->code),
                    ];

                    if(count($data_good_receipts)<1){
                        $data_good_receipts[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$query->code,
                            'to'=>$data_good_receipt["key"],
                        ];
                        $data_id_gr[]=$row->goodReceipt->id; 
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
                                'from'=>$query->code,
                                'to'=>$data_good_receipt["key"],
                            ];
                            $data_id_gr[]=$row->goodReceipt->id;   
                        }
                    } 
                }
                /* melihat apakah ada hubungan lc */
                if($row->landedCost()->exists()){
                    $lc=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->landedCost->post_date],
                        ],
                        "key" => $row->landedCost->code,
                        "name" => $row->landedCost->code,
                        'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->landedCost->code),
                    ];
                    $data_go_chart[] = $lc;
                    $data_link[]=[
                        'from'=>$row->landedCost->code,
                        'to'=>$query->code,
                    ];
                    $data_id_lc[]=$row->landedCost->id; 
                }
            }
            
            if($query->purchaseInvoiceDp()->exists()){
                foreach($query->purchaseInvoiceDp as $row_pi){
                    $data_down_payment=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                        ],
                        "key" => $row_pi->purchaseDownPayment->code,
                        "name" => $row_pi->purchaseDownPayment->code,
                        'url'=>request()->root()."/admin/inventory/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                    ];
                    $data_go_chart[]=$data_down_payment;
                    $data_link[]=[
                        'from'=>$row_pi->purchaseDownPayment->code,
                        'to'=>$query->code,
                    ];
                    $data_purchase_downpayment[]=$data_down_payment;
                    
                }
            }

            $data_lcs=[];
            
            $added = true;
            while($added){
                $added=false;
                //Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
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

                    }

                    //landed cost searching
                    if($query_gr->landedCost()->exists()){
                        foreach($query_gr->landedCost as $landed_cost){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$landed_cost->post_date],
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
                    if($query_gr->purchaseInvoiceDetail()->exists()){
                        foreach($query_gr->purchaseInvoiceDetail as $invoice_detail){
                            $invoice_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
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
                                $data_id_invoice[]=$invoice_detail->purchaseInvoice->id;
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
                                    $data_id_invoice[]=$invoice_detail->purchaseInvoice->id;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrder()->exists()){
                            foreach($row->purchaseOrder as $row_po){
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    "color"=>"lightblue",
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];
                                /*memasukkan ke node data dan linknya*/
                                if(count($data_pos)<1){
                                    $data_pos[]=$po;
                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_po->code,
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
                                            'from'=>$query_invoice->code,
                                            'to'=>$row_po->code,
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
                                            'from'=>$query_invoice->code,
                                            'to'=>$row_po->code,
                                        ];  
                                        $data_go_chart[]=$po;
                                        $data_id_po[]= $purchase_order_detail->purchaseOrder->id; 
                                    }
                                }
                                //memasukkan dengan yang sama atau tidak
                                
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
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
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceipt()->exists()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->goodReceipt->post_date],
                                ],
                                "key" => $row->goodReceipt->code,
                                "name" => $row->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->goodReceipt->code),
                            ];
        
                            if(count($data_good_receipts)<1){
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$data_good_receipt["key"],
                                ];
                                $data_id_gr[]=$row->goodReceipt->id;   
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
                                        'from'=>$query_invoice->code,
                                        'to'=>$data_good_receipt["key"],
                                    ]; 
                                    $data_id_gr[]=$row->goodReceipt->id; 
                                }
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCost()->exists()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->landedCost->post_date],
                                ],
                                "key" => $row->landedCost->code,
                                "name" => $row->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->landedCost->code),
                            ];
                            if(count($data_lcs)<1){
                                $data_lcs[]=$data_lc;
                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row->landedCost->code,
                                ];
                                $data_id_lc = $row->landedCost->id;
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
                                        'to'=>$row->landedCost->code,
                                    ];
                                    $data_id_lc = $row->landedCost->id;
                                }
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/inventory/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
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
                                        ['name'=> "url", 'type'=> request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code)],
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
                'link'    => $data_link
            ];
            
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }
    
}