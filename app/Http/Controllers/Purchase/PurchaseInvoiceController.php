<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LandedCost;
use App\Models\LandedCostDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodReceipt;
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Exports\ExportLandedCost;
use App\Models\Place;
use App\Models\User;
use App\Models\Department;

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
            'currency'      => Currency::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getGoodReceiptLandedCost(Request $request){
        $account = User::find($request->id);
        $account['deposit'] = number_format($account->deposit,3,',','.');

        $type = '';
        if($account->type == '3'){
            $data = GoodReceipt::where('account_id',$request->id)->where('status','2')->get();
            $type = 'good_receipt';
        }elseif($account->type == '4'){
            $data = LandedCost::where('account_id',$request->id)->where('status','2')->get();
            $type = 'landed_cost';
        }
        
        $details = [];

        foreach($data as $row){
            $details[] = [
                'type'          => $type,
                'code'          => CustomHelper::encrypt($row->code),
                'rawcode'       => $row->code,
                'post_date'     => date('d/m/y',strtotime($row->post_date)),
                'due_date'      => date('d/m/y',strtotime($row->due_date)),
                'total'         => number_format($row->total,3,',','.'),
                'tax'           => number_format($row->tax,3,',','.'),
                'grandtotal'    => number_format($row->grandtotal,3,',','.'),
                'department_id' => $row->department_id,
                'place_id'      => $row->place_id,
            ];
        }

        $account['details'] = $details;

        return response()->json($account);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'place_id',
            'department_id',
            'post_date',
            'due_date',
            'document_date',
            'type',
            'currency_id',
            'currency_rate',
            'document',
            'note',
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

        $total_data = PurchaseInvoice::whereIn('place_id',$this->dataplaces)->count();
        
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
                                    $query->where('code','like',"%$search%")
                                    ->orWhereHas('purchaseOrder', function($query) use($search, $request){
                                        $query->where('code','like',"%$search%");
                                    });
                                })->orWhereHas('goodReceipt',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%")
                                    ->orWhereHas('purchaseOrder', function($query) use($search, $request){
                                        $query->where('code','like',"%$search%");
                                    });
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

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = LandedCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
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
                                    $query->where('code','like',"%$search%")
                                    ->orWhereHas('purchaseOrder', function($query) use($search, $request){
                                        $query->where('code','like',"%$search%");
                                    });
                                })->orWhereHas('goodReceipt',function($query) use($search, $request){
                                    $query->where('code','like',"%$search%")
                                    ->orWhereHas('purchaseOrder', function($query) use($search, $request){
                                        $query->where('code','like',"%$search%");
                                    });
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

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->place_id){
                    $query->where('place_id',$request->place_id);
                }

                if($request->department_id){
                    $query->where('department_id',$request->department_id);
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
                    $val->account->name,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->due_date)),
                    date('d/m/y',strtotime($val->document_date)),
                    $val->type(),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    number_format($val->subtotal,3,',','.'),
                    number_format($val->percent_discount,3,',','.'),
                    number_format($val->nominal_discount,3,',','.'),
                    number_format($val->total,3,',','.'),
                    number_format($val->tax,3,',','.'),
                    number_format($val->grandtotal,3,',','.'),
                    number_format($val->downpayment,3,',','.'),
                    number_format($val->balance,3,',','.'),
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
			'account_id' 			=> 'required',
			'type'                  => 'required',
            'place_id'              => 'required',
            'department_id'         => 'required',
            'post_date'             => 'required',
            'due_date'              => 'required',
            'document_date'         => 'required',
            'currency_id'           => 'required',
            'currency_rate'         => 'required',
            'arr_type'                  => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_tax'                   => 'required|array',
            'arr_grandtotal'            => 'required|array'
		], [
			'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
			'type.required'                     => 'Tipe invoice tidak boleh kosong',
            'place_id.required'                 => 'Penempatan pabrik/kantor tidak boleh kosong.',
            'department_id.required'            => 'Departemen tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'due_date.required'                 => 'Tanggal tenggat tidak boleh kosong.',
            'document_date.required'            => 'Tanggal dokumen tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
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
            $grandtotal = 0;
            $balance = 0;
            $downpayment = str_replace(',','.',str_replace('.','',$request->downpayment));

            foreach($request->arr_total as $key => $row){
                $total += str_replace(',','.',str_replace('.','',$row));
                $tax += str_replace(',','.',str_replace('.','',$request->arr_tax[$key]));
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key]));
            }

            $balance = $grandtotal - $downpayment;

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
                        $query->place_id = $request->place_id;
                        $query->department_id = $request->department_id;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->document_date = $request->document_date;
                        $query->type = $request->type;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->grandtotal = round($grandtotal,3);
                        $query->downpayment = round($downpayment,3);
                        $query->balance = round($balance,3);
                        $query->document = $document;
                        $query->note = $request->note;

                        $query->save();

                        foreach($query->purchaseInvoiceDetail as $row){
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
                        'place_id'                  => $request->place_id,
                        'department_id'             => $request->department_id,
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->due_date,
                        'document_date'             => $request->document_date,
                        'type'                      => $request->type,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'downpayment'               => round($downpayment,3),
                        'balance'                   => round($balance,3),
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_invoices') : NULL,
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_item){
                    foreach($request->arr_item as $key => $row){
                        DB::beginTransaction();
                        try {
                            LandedCostDetail::create([
                                'landed_cost_id'        => $query->id,
                                'item_id'               => $row,
                                'qty'                   => floatval($request->arr_qty[$key]),
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            ]);
                            DB::commit();
                        }catch(\Exception $e){
                            DB::rollback();
                        }
                    }
                }

                CustomHelper::sendApproval('landed_costs',$query->id,$query->note);
                CustomHelper::sendNotification('landed_costs',$query->id,'Pengajuan Landed Cost No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit landed cost.');

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
        $data   = LandedCost::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Harga Total</th>
                                <th class="center-align">Harga Satuan</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->landedCostDetail) > 0){
            foreach($data->landedCostDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->item->code.' - '.$row->item->name.'</td>
                    <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                    <td class="center-align">'.$row->item->uomUnit->code.'</td>
                    <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
                    <td class="right-align">'.number_format(round($row->nominal / $row->qty,3),3,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="8">Data item tidak ditemukan.</td>
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
                    <td class="center-align">'.$row->approvalTable->level.'</td>
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
        
        $lc = LandedCost::where('code',CustomHelper::decrypt($id))->first();
                
        if($lc){
            $data = [
                'title'     => 'Print Landed Cost',
                'data'      => $lc
            ];

            return view('admin.approval.landed_cost', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $lc = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        $lc['vendor_name'] = $lc->vendor->name;
        $lc['good_receipt_note'] = $lc->goodReceipt->code.' - '.$lc->note;
        $lc['total'] = number_format($lc->total,3,',','.');
        $lc['tax'] = number_format($lc->tax,3,',','.');
        $lc['grandtotal'] = number_format($lc->grandtotal,3,',','.');
        $lc['percent_tax'] = number_format($lc->percent_tax,3,',','.');
        $lc['currency_rate'] = number_format($lc->currency_rate,3,',','.');

        $arr = [];

        foreach($lc->landedCostDetail as $row){
            $arr[] = [
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->name.' - '.$row->item->name,
                'qtyRaw'                    => $row->qty,
                'qty'                       => number_format($row->qty,3,',','.'),
                'nominal'                   => number_format($row->nominal,3,',','.'),
                'unit'                      => $row->item->uomUnit->code,
            ];
        }

        $lc['details'] = $arr;
        				
		return response()->json($lc);
    }

    public function voidStatus(Request $request){
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if($query->status == '5'){
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

                foreach($query->landedCostDetail as $rowdetail){
                    $pricelc = round($rowdetail->nominal / $rowdetail->qty,3);
                    $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$query->good_receipt_id)->where('branch_id',$query->branch_id)->where('item_id',$rowdetail->item_id)->first();
                    if($itemdata){
                        $pricenew = $itemdata->price_in - $pricelc;
                        $itemdata->update([
                            'price_in'	=> $pricenew,
                            'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                        ]);

                        CustomHelper::resetCogsItem($query->branch_id,$rowdetail->item_id);
                    }
                }
    
                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the landed cost data');
    
                CustomHelper::sendNotification('landed_costs',$query->id,'Purchase Order Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('landed_costs',$query->id);

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
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval() || in_array($query->status,['2','3'])){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Landed coast telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('landed_costs',$query->id);
            
            foreach($query->landedCostDetail as $rowdetail){
                $pricelc = round($rowdetail->nominal / $rowdetail->qty,3);
                $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$query->good_receipt_id)->where('branch_id',$query->branch_id)->where('item_id',$rowdetail->item_id)->first();
                if($itemdata){
                    $pricenew = $itemdata->price_in - $pricelc;
                    $itemdata->update([
                        'price_in'	=> $pricenew,
                        'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                    ]);

                    CustomHelper::resetCogsItem($query->branch_id,$rowdetail->item_id);
                }
            }

            $query->landedCostDetail()->delete();

            activity()
                ->performedOn(new PurchaseOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase order data');

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
            'title' => 'LANDED COST REPORT',
            'data' => LandedCost::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('due_date', 'like', "%$request->search%")
                            ->orWhere('reference', 'like', "%$request->search%")
                            ->orWhere('total', 'like', "%$request->search%")
                            ->orWhere('tax', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('landedCostDetail',function($query) use($request){
                                $query->whereHas('item',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%")
                                        ->orWhere('name','like',"%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            })
                            ->orWhereHas('purchaseOrder',function($query) use($request){
                                $query->where('code','like',"%$request->search%");
                            })
                            ->orWhereHas('goodReceipt',function($query) use($request){
                                $query->where('code','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->vendor_id){
                    $query->whereIn('account_id',$request->vendor_id);
                }

                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->where('branch_id',session('bo_branch_id'))
            ->get()
		];
		
		return view('admin.print.purchase.landed_cost', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportLandedCost($request->search,$request->status,$request->is_tax,$request->is_include_tax,$request->vendor,$request->currency), 'landed_cost'.uniqid().'.xlsx');
    }
    
}