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
use App\Models\GoodReceiptMain;
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
    public function index()
    {
        $data = [
            'title'         => 'Landed Cost',
            'content'       => 'admin.purchase.landed_cost',
            'currency'      => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getGoodReceipt(Request $request){
        $data = GoodReceiptMain::find($request->id);
        
        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Good Receipt '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Landed Cost');

            $details = [];
            
            foreach($data->goodReceipt as $gr){
                foreach($gr->goodReceiptDetail as $row){

                    $index = -1;

                    foreach($details as $key => $rowcek){
                        if($rowcek['item_id'] == $row->item_id){
                            $index = $key;
                        }
                    }

                    if($index >= 0){
                        $details[$index]['qty'] = number_format($details[$index]['qtyRaw'] + $row->qtyConvert(),3,',','.');
                        $details[$index]['qtyRaw'] = $details[$index]['qtyRaw'] + $row->qtyConvert();
                    }else{
                        $details[] = [
                            'item_id'       => $row->item_id,
                            'item_name'     => $row->item->code.' - '.$row->item->name,
                            'qty'           => number_format($row->qtyConvert(),2,',','.'),
                            'qtyRaw'        => $row->qtyConvert(),
                            'unit'          => $row->item->uomUnit->code,
                        ];
                    }
                }
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
            'place_id',
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

        $total_data = LandedCost::whereIn('place_id',$this->dataplaces)->count();
        
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
                            })
                            ->orWhereHas('goodReceiptMain',function($query) use($search, $request){
                                $query->where('code','like',"%$search%");
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
            ->whereIn('place_id',$this->dataplaces)
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
                            })
                            ->orWhereHas('goodReceiptMain',function($query) use($search, $request){
                                $query->where('code','like',"%$search%");
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
                    $val->vendor->name,
                    $val->goodReceiptMain->code,
                    $val->place->name.' - '.$val->place->company->name,
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

            $gr = GoodReceiptMain::find($request->good_receipt_id);
            
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
                        $query->good_receipt_main_id = $gr->id;
                        $query->place_id = $gr->place_id;
                        $query->department_id = $gr->department_id;
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->reference = $request->reference;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->is_tax = $request->is_tax ? $request->is_tax : NULL;
                        $query->is_include_tax = $request->is_include_tax ? $request->is_include_tax : '0';
                        $query->percent_tax = str_replace(',','.',str_replace('.','',$request->percent_tax));
                        $query->is_wtax = $request->is_wtax ? $request->is_wtax : NULL;
                        $query->percent_wtax = str_replace(',','.',str_replace('.','',$request->percent_wtax));
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
                        'good_receipt_main_id'	    => $gr->id,
                        'place_id'                  => $gr->place_id,
                        'department_id'             => $gr->department_id,
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->due_date,
                        'reference'                 => $request->reference,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'is_tax'                    => $request->is_tax ? $request->is_tax : NULL,
                        'is_include_tax'            => $request->is_include_tax ? $request->is_include_tax : '0',
                        'percent_tax'               => str_replace(',','.',str_replace('.','',$request->percent_tax)),
                        'is_wtax'                   => $request->is_wtax ? $request->is_wtax : NULL,
                        'percent_wtax'              => str_replace(',','.',str_replace('.','',$request->percent_wtax)),
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
                CustomHelper::removeUsedData($query->goodReceiptMain->getTable(),$query->goodReceiptMain->id);

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
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    <td class="right-align">'.number_format(round($row->nominal / $row->qty,3),2,',','.').'</td>
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
        $lc['good_receipt_note'] = $lc->goodReceiptMain->code.' - '.$lc->note;
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
                'nominal'                   => number_format($row->nominal,2,',','.'),
                'unit'                      => $row->item->uomUnit->code,
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
            }else{

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->landedCostDetail as $rowdetail){
                    $pricelc = $rowdetail->nominal / $rowdetail->qty;
    
                    foreach($query->goodReceiptMain->goodReceipt as $gr){
                        $pricenew = 0;
                        $itemdata = NULL;
                        $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$gr->id)->where('place_id',$query->place_id)->where('item_id',$rowdetail->item_id)->first();
                        if($itemdata){
                            $pricenew = $itemdata->price_in - $pricelc;
                            $itemdata->update([
                                'price_in'	=> $pricenew,
                                'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                            ]);
                        }
                    }
    
                    CustomHelper::resetCogsItem($query->place_id,$rowdetail->item_id);
                }
    
                activity()
                    ->performedOn(new LandedCost())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the landed cost data');
    
                CustomHelper::sendNotification('landed_costs',$query->id,'Purchase Order Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('landed_costs',$query->id);
                /* CustomHelper::removeJournal('landed_costs',$query->id); */

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

        if(in_array($query->status,['2','3'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('landed_costs',$query->id);
            
            foreach($query->landedCostDetail as $rowdetail){
                $pricelc = $rowdetail->nominal / $rowdetail->qty;

                foreach($query->goodReceiptMain->goodReceipt as $gr){
                    $pricenew = 0;
					$itemdata = NULL;
                    $itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$gr->id)->where('place_id',$query->place_id)->where('item_id',$rowdetail->item_id)->first();
                    if($itemdata){
                        $pricenew = $itemdata->price_in - $pricelc;
                        $itemdata->update([
                            'price_in'	=> $pricenew,
                            'total_in'	=> round($pricenew * $itemdata->qty_in,3),
                        ]);
                    }
                }

                CustomHelper::resetCogsItem($query->place_id,$rowdetail->item_id);
            }

            $query->landedCostDetail()->delete();
            /* CustomHelper::removeJournal('landed_costs',$query->id); */

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
                            ->orWhereHas('goodReceiptMain',function($query) use($request){
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
            ->whereIn('place_id',$this->dataplaces)
            ->get()
		];
		
		return view('admin.print.purchase.landed_cost', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportLandedCost($request->search,$request->status,$request->is_tax,$request->is_include_tax,$request->vendor,$request->currency,$this->dataplaces), 'landed_cost'.uniqid().'.xlsx');
    }
    
    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('good_receipt_mains',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }
}