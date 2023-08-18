<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ItemStock;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\Place;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;

class MarketingOrderDeliveryController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }
    
    public function index(Request $request)
    {
        $data = [
            'title'         => 'Marketing Order Delivery',
            'content'       => 'admin.sales.order_delivery',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'MORD-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = MarketingOrderDelivery::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'customer_id',
            'company_id',
            'account_id',
            'marketing_order_no',
            'post_date',
            'delivery_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDelivery::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderDelivery::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
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
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_id){
                    $query->whereIn('marketing_order_id',$request->marketing_order_id);
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

        $total_filtered = MarketingOrderDelivery::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('marketingOrderDeliveryDetail',function($query) use ($search, $request){
                                $query->whereHas('item',function($query) use ($search, $request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
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
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->marketing_order_id){
                    $query->whereIn('marketing_order_id',$request->marketing_order_id);
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
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->marketingOrder->account->name,
                    $val->company->name,
                    $val->account->name,
                    $val->marketingOrder->code,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->delivery_date)),
                    $val->note,
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

    public function getMarketingOrder(Request $request){
        $data = MarketingOrder::find($request->id);
        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Marketing Order No. '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($data->hasBalanceMod()){
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Marketing Order Delivery');

                $details = [];

                foreach($data->marketingOrderDetail as $row){
                    $details[] = [
                        'id'            => $row->id,
                        'item_id'       => $row->item_id,
                        'item_name'     => $row->item->code.' - '.$row->item->name,
                        'place_id'      => $row->place_id,
                        'place_name'    => $row->place->name,
                        'warehouse_id'  => $row->warehouse_id,
                        'warehouse_name'=> $row->warehouse->name,
                        'list_stock'    => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                        'qty'           => number_format($row->balanceQtyMod(),3,',','.'),
                        'unit'          => $row->item->sellUnit->code,
                        'note'          => $row->note,
                        'item_stock_id' => $row->item_stock_id,
                    ];
                }

                $data['details'] = $details;
            }else{
                $data['status'] = '500';
                $data['message'] = 'Seluruh item pada Sales Order No. '.$data->code.' sudah menjadi MOD. Data tidak bisa ditambahkan.';
            }
        }

        return response()->json($data);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('marketing_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'			            => $request->temp ? ['required', Rule::unique('marketing_order_deliveries', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_deliveries,code',
            'code_place_id'             => 'required',
            'account_id' 				=> 'required',
            'company_id'			    => 'required',
            'marketing_order_id'		=> 'required',
            'post_date'		            => 'required',
            'delivery_date'		        => 'required',
            'arr_modi'                  => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_item_stock'            => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_warehouse'             => 'required|array',
            'arr_qty'                   => 'required|array',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai',
            'account_id.required' 				=> 'Vendor/Ekspedisi/Broker tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'marketing_order_id.required' 	    => 'Sales Order tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'delivery_date.required' 			=> 'Tanggal kirim tidak boleh kosong.',
            'arr_modi.required'                 => 'Data detail sales order tidak boleh kosong.',
            'arr_modi.array'                    => 'Data detail sales order harus array.',
            'arr_item.required'                 => 'Item baris tidak boleh kosong.',
            'arr_item.array'                    => 'item baris harus array.',
            'arr_item_stock.required'           => 'Stok item tidak boleh kosong.',
            'arr_item_stock.array'              => 'Stok item harus array.',
            'arr_qty.required'                  => 'Baris qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Baris qty harus array.',
            'arr_place.required'                => 'Baris plant tidak boleh kosong.',
            'arr_place.array'                   => 'Baris plant harus array.',
            'arr_warehouse.required'            => 'Baris gudang tidak boleh kosong.',
            'arr_warehouse.array'               => 'Baris gudang harus array.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedZero = true;
            $passedQty = true;

            if($request->arr_qty){
                foreach($request->arr_qty as $key => $row){
                    if(floatval(str_replace(',','.',str_replace('.','',$row))) == 0){
                        $passedZero = false;
                    }
                    $item_stock = ItemStock::find($request->arr_item_stock[$key]);
                    $qtysell = round($item_stock->qty / $item_stock->item->sell_convert,3);
                    if(floatval(str_replace(',','.',str_replace('.','',$row))) > $qtysell){
                        $passedQty = false;
                    }
                }

                $errorMessage = [];

                if(!$passedZero){
                    $errorMessage[] = 'Qty tidak boleh 0.';
                }

                if(!$passedQty){
                    $errorMessage[] = 'Salah satu item memiliki qty kurang dari stok saat ini.';
                }

                if(count($errorMessage) > 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => implode(', ',$errorMessage)
                    ]);
                }
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Jadwal Kirim telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->marketing_order_id = $request->marketing_order_id;
                        $query->post_date = $request->post_date;
                        $query->delivery_date = $request->delivery_date;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->marketingOrderDeliveryDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status jadwal kirim detail sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = MarketingOrderDelivery::create([
                        'code'			            => $request->code,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'marketing_order_id'	    => $request->marketing_order_id,
                        'post_date'                 => $request->post_date,
                        'delivery_date'             => $request->delivery_date,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                DB::beginTransaction();
                try {
                    
                    foreach($request->arr_modi as $key => $row){
                        MarketingOrderDeliveryDetail::create([
                            'marketing_order_delivery_id'   => $query->id,
                            'marketing_order_detail_id'     => $row,
                            'item_id'                       => $request->arr_item[$key],
                            'qty'                           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                          => $request->arr_note[$key],
                            'item_stock_id'                 => $request->arr_item_stock[$key],
                            'place_id'                      => $request->arr_place[$key],
                            'warehouse_id'                  => $request->arr_warehouse[$key],
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('marketing_order_deliveries',$query->id,$query->note);
                CustomHelper::sendNotification('marketing_order_deliveries',$query->id,'Pengajuan Marketing Order Delivery No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new MarketingOrderDelivery())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit marketing order delivery.');

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

    public function show(Request $request){
        $po = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);
        $po['account_name'] = $po->account->name;
        $po['marketing_order_code'] = $po->marketingOrder->code;

        $arr = [];
        
        foreach($po->marketingOrderDeliveryDetail as $row){
            $arr[] = [
                'id'                    => $row->marketing_order_detail_id,
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'qty'                   => number_format($row->qty,3,',','.'),
                'unit'                  => $row->item->sellUnit->code,
                'note'                  => $row->note,
                'item_stock_id'         => $row->item_stock_id,
                'item_stock_name'       => $row->itemStock->place->code.' - '.$row->itemStock->warehouse->code,
                'item_stock_qty'        => number_format($row->itemStock->qty,3,',','.'),
                'list_stock'            => $row->item->currentStockSales($this->dataplaces,$this->datawarehouses),
                'place_id'              => $row->place_id,
                'warehouse_id'          => $row->warehouse_id,
                'place_name'            => $row->place->name,
                'warehouse_name'        => $row->warehouse->name,
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="17">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Ambil Dari</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->marketingOrderDeliveryDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.$row->itemStock->place->name.' - '.$row->itemStock->warehouse->name.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').' - '.$row->getHpp().'</td>
                <td class="center-align">'.$row->item->sellUnit->code.'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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

    public function approval(Request $request,$id){
        
        $mod = MarketingOrderDelivery::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Print Marketing Order Delivery',
                'data'      => $mod
            ];

            return view('admin.approval.marketing_order_delivery', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = MarketingOrderDelivery::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Marketing Order Delivery',
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
             
            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
            // $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
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
                $pr = MarketingOrderDelivery::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Print Marketing Order Delivery',
                        'data'      => $pr,
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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

            Storage::put('public/pdf/bubla.pdf',$result);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
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
                        $query = MarketingOrderDelivery::where('Code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Marketing Order Delivery',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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

                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
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
                        $query = MarketingOrderDelivery::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Print Marketing Order Delivery',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.order_delivery_individual', $data)->setPaper('a5', 'landscape');
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

                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
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

    public function voidStatus(Request $request){
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
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
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new MarketingOrderDelivery())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the marketing order delivery data');
    
                CustomHelper::sendNotification('marketing_order_deliveries',$query->id,'Marketing Order Delivery No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('marketing_order_deliveries',$query->id);

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
        $query = MarketingOrderDelivery::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->marketingOrderDeliveryDetail()->delete();

            CustomHelper::removeApproval('marketing_order_deliveries',$query->id);

            activity()
                ->performedOn(new MarketingOrderDelivery())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the marketing order delivery data');

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
}