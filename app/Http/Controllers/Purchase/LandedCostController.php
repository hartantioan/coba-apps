<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\PaymentRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\Tax;
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
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Exports\ExportLandedCost;
use App\Models\User;

class LandedCostController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Landed Cost',
            'content'       => 'admin.purchase.landed_cost',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getGoodReceipt(Request $request){
        $data = GoodReceipt::find($request->id);
        
        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Good Receipt '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Landed Cost');

            $details = [];
            
            foreach($data->goodReceiptDetail as $row){
                $details[] = [
                    'item_id'                   => $row->item_id,
                    'item_name'                 => $row->item->code.' - '.$row->item->name,
                    'qty'                       => number_format($row->qtyConvert(),5,',','.'),
                    'totalrow'                  => $row->getRowTotal(),
                    'qtyRaw'                    => $row->qtyConvert(),
                    'unit'                      => $row->item->uomUnit->code,
                    'place_name'                => $row->place->name.' - '.$row->place->company->name,
                    'department_name'           => $row->department->name,
                    'warehouse_name'            => $row->warehouse->name,
                    'place_id'                  => $row->place_id,
                    'department_id'             => $row->department_id,
                    'warehouse_id'              => $row->warehouse_id,
                    'good_receipt_detail_id'    => $row->id
                ];
            }

            $data['details'] = $details;
        }

        return response()->json($data);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'purchase_order_id',
            'good_receipt_id',
            'company_id',
            'post_date',
            'due_date',
            'reference',
            'currency_id',
            'currency_rate',
            'is_tax',
            'is_included_tax',
            'percent_tax',
            'is_wtax',
            'percent_wtax',
            'note',
            'document',
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

        $total_data = LandedCost::count();
        
        $query_data = LandedCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('reference', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('landedCostDetail',function($query) use($search, $request){
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
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = LandedCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('reference', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('landedCostDetail',function($query) use($search, $request){
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
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->vendor->name,
                    $val->goodReceipt->code,
                    $val->company->name,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->due_date)),
                    $val->reference,
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->isTax(),
                    $val->isIncludeTax(),
                    number_format($val->percent_tax,2,',','.'),
                    $val->isWtax(),
                    number_format($val->percent_wtax,2,',','.'),
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
			'good_receipt_id' 			=> 'required',
            'company_id' 			    => 'required',
			'vendor_id'                 => 'required',
            'post_date'                 => 'required',
            'due_date'                  => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'total'                     => 'required',
            'tax'                       => 'required',
            'grandtotal'                => 'required',
            'arr_item'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_qty'                   => 'required|array'
		], [
			'good_receipt_id.required' 			=> 'Good receipt tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
			'vendor_id.required'                => 'Vendor/ekspedisi tidak boleh kosong',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'due_date.required'                 => 'Tgl tenggat tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'total.required'                    => 'Total tagihan tidak boleh kosong.',
            'tax.required'                      => 'Total pajak tidak boleh kosong.',
            'grandtotal.required'               => 'Total tagihan tidak boleh kosong.',
            'arr_item.required'                 => 'Item tidak boleh kosong.',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'arr_price.required'                => 'Harga per item tidak boleh kosong.',
            'arr_price.array'                   => 'Harga per item harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $gr = GoodReceipt::find($request->good_receipt_id);
            
            $total = str_replace(',','.',str_replace('.','',$request->total));
            $tax = str_replace(',','.',str_replace('.','',$request->tax));
            $wtax = str_replace(',','.',str_replace('.','',$request->wtax));
            $grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = LandedCost::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            $document = $request->file('document')->store('public/landed_costs');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->vendor_id;
                        $query->good_receipt_id = $gr->id;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->reference = $request->reference;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->tax_id = $request->tax_id;
                        $query->wtax_id = $request->wtax_id;
                        $query->is_tax = $request->tax_id > 0 ? '1' : NULL;
                        $query->is_include_tax = $request->is_include_tax ? $request->is_include_tax : '0';
                        $query->percent_tax = $request->percent_tax;
                        $query->is_wtax = $request->wtax_id > 0 ? '1' : NULL;
                        $query->percent_wtax = $request->percent_wtax;
                        $query->note = $request->note;
                        $query->document = $document;
                        $query->total = round($total,3);
                        $query->tax = round($tax,3);
                        $query->wtax = round($wtax,3);
                        $query->grandtotal = round($grandtotal,3);

                        $query->save();

                        foreach($query->landedCostDetail as $row){
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

                    $query = LandedCost::create([
                        'code'			            => LandedCost::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->vendor_id,
                        'good_receipt_id'	        => $gr->id,
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->due_date,
                        'reference'                 => $request->reference,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'tax_id'                    => $request->tax_id,
                        'wtax_id'                   => $request->wtax_id,
                        'is_tax'                    => $request->tax_id > 0 ? '1' : NULL,
                        'is_include_tax'            => $request->is_include_tax ? $request->is_include_tax : '0',
                        'percent_tax'               => $request->percent_tax,
                        'is_wtax'                   => $request->wtax_id > 0 ? '1' : NULL,
                        'percent_wtax'              => $request->percent_wtax,
                        'note'                      => $request->note,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/landed_costs') : NULL,
                        'total'                     => round($total,3),
                        'tax'                       => round($tax,3),
                        'wtax'                      => round($wtax,3),
                        'grandtotal'                => round($grandtotal,3),
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_item){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_item as $key => $row){
                            LandedCostDetail::create([
                                'landed_cost_id'        => $query->id,
                                'item_id'               => $row,
                                'qty'                   => floatval($request->arr_qty[$key]),
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                                'place_id'              => $request->arr_place[$key],
                                'department_id'         => $request->arr_department[$key],
                                'warehouse_id'          => $request->arr_warehouse[$key],
                                'good_receipt_detail_id'=> $request->arr_good_receipt[$key],
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('landed_costs',$query->id,$query->note);
                CustomHelper::sendNotification('landed_costs',$query->id,'Pengajuan Landed Cost No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::removeUsedData('good_receipts',$query->goodReceipt->id);

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
                                <th class="center-align">Site</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->landedCostDetail) > 0){
            foreach($data->landedCostDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->item->code.' - '.$row->item->name.'</td>
                    <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                    <td class="center-align">'.$row->item->uomUnit->code.'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    <td class="right-align">'.number_format(round($row->nominal / $row->qty,3),2,',','.').'</td>
                    <td class="center-align">'.$row->place->name.' - '.$row->place->company->name.'</td>
                    <td class="center-align">'.$row->department->name.'</td>
                    <td class="center-align">'.$row->warehouse->name.'</td>
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
        $lc['total'] = number_format($lc->total,2,',','.');
        $lc['tax'] = number_format($lc->tax,2,',','.');
        $lc['wtax'] = number_format($lc->wtax,2,',','.');
        $lc['grandtotal'] = number_format($lc->grandtotal,2,',','.');
        $lc['percent_tax'] = number_format($lc->percent_tax,2,',','.');
        $lc['currency_rate'] = number_format($lc->currency_rate,2,',','.');

        $arr = [];

        foreach($lc->landedCostDetail as $row){
            $arr[] = [
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->name.' - '.$row->item->name,
                'qtyRaw'                    => $row->qty,
                'qty'                       => number_format($row->qty,3,',','.'),
                'nominal'                   => number_format($row->nominal,5,',','.'),
                'unit'                      => $row->item->uomUnit->code,
                'place_name'                => $row->place->name.' - '.$row->place->company->name,
                'department_name'           => $row->department->name,
                'warehouse_name'            => $row->warehouse->name,
                'place_id'                  => $row->place_id,
                'department_id'             => $row->department_id,
                'warehouse_id'              => $row->warehouse_id,
                'good_receipt_detail_id'    => $row->good_receipt_detail_id,
            ];
        }

        $lc['details'] = $arr;
        				
		return response()->json($lc);
    }

    public function voidStatus(Request $request){
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Purchase Invoice.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->landedCostDetail as $rowdetail){
                    $pricelc = $rowdetail->nominal / $rowdetail->qty;
    
                    $pricenew = 0;
                    $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$rowdetail->goodReceiptDetail->good_receipt_id)->where('place_id',$rowdetail->place_id)->where('item_id',$rowdetail->item_id)->first();
                    if($itemdata){
                        $pricenew = $pricelc - $itemdata->price_in;
                        $itemdata->update([
                            'price_in'	=> $pricenew,
                            'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                        ]);
                    }
    
                    CustomHelper::resetCogsItem($rowdetail->place_id,$rowdetail->item_id);
                }
    
                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the landed cost data');
    
                CustomHelper::sendNotification('landed_costs',$query->id,'Purchase Order Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('landed_costs',$query->id);
                CustomHelper::removeJournal('landed_costs',$query->id);

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

            CustomHelper::removeApproval('landed_costs',$query->id);
            
            foreach($query->landedCostDetail as $rowdetail){
                $pricelc = $rowdetail->nominal / $rowdetail->qty;

                $pricenew = 0;
                $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$rowdetail->goodReceiptDetail->good_receipt_id)->where('place_id',$rowdetail->place_id)->where('item_id',$rowdetail->item_id)->first();
                if($itemdata){
                    $pricenew = $pricelc - $itemdata->price_in;
                    $itemdata->update([
                        'price_in'	=> $pricenew,
                        'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                    ]);
                }

                CustomHelper::resetCogsItem($rowdetail->place_id,$rowdetail->item_id);
            }

            $query->landedCostDetail()->delete();
            
            CustomHelper::removeJournal('landed_costs',$query->id);

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
            ->get()
		];
		
		return view('admin.print.purchase.landed_cost', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportLandedCost($request->search,$request->status,$request->is_tax,$request->is_include_tax,$request->vendor,$request->currency,$this->dataplaces), 'landed_cost'.uniqid().'.xlsx');
    }
    
    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('good_receipts',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function viewStructureTree(Request $request){
        $query = LandedCost::where('code',CustomHelper::decrypt($request->id))->first();
        
        $data_good_receipts=[];
        $data_purchase_requests=[];
        $data_pos = [];

        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_purchase_downpayment=[];
        $data_id_pyrs=[];
        $data_id_frs=[];
        $data_frs=[];
        $data_pyrs = [];
        $data_outgoingpayments = [];
        
        $data_invoices=[];
        $data_link = [];
        $data_go_chart = [];
        if($query) {
            $lc = [
                "key" => $query->code,
                "name" => $query->code,
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')],
                 ],
                'color'=>"lightblue",
                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($query->code),
            ];
            $data_go_chart[]=$lc;
            $data_lcs[]=$lc;


            if($query->purchaseInvoiceDetail()->exists()){
                foreach($query->purchaseInvoiceDetail as $row){
                    $invoice=[
                        "key"=>$row->code,
                        "name"=>$row->code,
                        'properties'=> [
                            ['name'=> "Tanggal :".$row->code],
                            ['name'=> "Nominal : Rp.:".number_format($row->grandtotal,2,',','.')],
                         ],
                        'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row->code),
                    ];
                    $data_invoices[]=$invoice;
                    $data_go_chart[]=$invoice;
                    $data_link[]=[
                        'from'=>$query->code,
                        'to'=>$row->code,
                    ];
                }
            }
            if($query->goodReceipt()->exists()){
                $data_good_receipt = [
                    "key" => $query->goodReceipt->code,
                    'name'=>$query->goodReceipt->code,
                    'properties'=> [
                        ['name'=> "Tanggal :".$query->goodReceipt->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($query->goodReceipt->grandtotal,2,',','.')],
                     ],
                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($query->goodReceipt->code),
                ];
                $data_good_receipts[] = $data_good_receipt;
                $data_go_chart[]=$data_good_receipt;
                $data_link[]=[
                    'from'=>$query->goodReceipt->code,
                    'to'=>$query->code,
                ];
                $data_id_gr[]=$query->goodReceipt->id;
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

                    }

                    //landed cost searching
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
                    //invoice searching
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

                // invoice insert foreign

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
                                    if($po_detail->goodReceiptDetail->exists()){
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
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceipt()->exists()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->goodReceipt->grandtotal,2,',','.')]
                                ],
                                "key" => $row->goodReceipt->code,
                                "name" => $row->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->goodReceipt->code),
                            ];
        
                            if(count($data_good_receipts)<1){
                                $data_good_receipts[]=$data_good_receipt;
                                $data_go_chart[]=$data_good_receipt;
                                $data_link[]=[
                                    'from'=>$data_good_receipt["key"],
                                    'to'=>$query_invoice->code,
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
                                        'from'=>$data_good_receipt["key"],
                                        'to'=>$query_invoice->code,
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
                                    ['name'=> "Nominal : Rp.".number_format($row->landedCost->grandtotal,2,',','.')]
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
                                                    'from'=>$row->purchaseOrder->code,
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
                'link' => $data_link
            ];


        } else {
            info("rusak sini");
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data not Found.'
            ];
        }
        return response()->json($response);
    }

}