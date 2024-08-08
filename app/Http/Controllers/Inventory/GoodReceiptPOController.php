<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Exports\ExportOutstandingGRPO;
use App\Exports\ExportGoodReceiptTransactionPage;
use App\Models\PurchaseOrderDetail;

use App\Models\PurchaseOrder;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\Place;
use App\Models\GoodReceiptDetail;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportGoodReceipt;
use App\Models\Division;
use App\Models\ItemSerial;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\UsedData;
class GoodReceiptPOController extends Controller
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
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'     => 'Penerimaan Barang PO',
            'content'   => 'admin.inventory.good_receipt',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = GoodReceipt::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'receiver_name',
            'post_date',
            'document_date',
            'note',
            'delivery_no',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodReceipt::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })
        ->whereHas('goodReceiptDetail',function($query){
            $query->whereIn('warehouse_id',$this->datawarehouses);
        })
        ->count();
        
        $query_data = GoodReceipt::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })/* 
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            }) */;
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
            ->whereHas('goodReceiptDetail',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodReceipt::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('document_date', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })/* 
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            }) */;
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
            ->whereHas('goodReceiptDetail',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';
                if($val->isOpenPeriod()){

                    $dis = 'style="cursor: default;
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
                    $val->user->name ?? '',
                    /* $val->account->name ?? '', */
                    $val->company->name,
                    $val->type(),
                    $val->receiver_name ?? '',
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->document_date)),
                    $val->note,
                    $val->delivery_no,
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
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
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <!-- <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button> -->
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

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->whereIn('status',['2','3'])->first();
        $data['account_name'] = $data->supplier->employee_no.' - '.$data->supplier->name;
        $data['secret_po'] = $data->isSecretPo() ? '1' : '';

        if($data->used()->exists()){
            $data['status'] = '500';
            $data['message'] = 'Purchase Order '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.';
        }else{
            if($request->type == '1'){
                if($data->hasBalance()){
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Goods Receipt');
                    $details = [];
                    $serials = [];
                    $maxcolumn = 0;
                    foreach($data->purchaseOrderDetail as $row){
                        if($request->type == '1'){
                            $qtyBalance = $row->getBalanceReceipt();
                        }elseif($request->type == '2'){
                            $qtyBalance = $row->getBalanceReceiptRM();
                        }
                        
                        if($qtyBalance > 0){
                            $details[] = [
                                'purchase_order_detail_id'  => $row->id,
                                'item_id'                   => $row->item_id,
                                'item_name'                 => $row->item->code.' - '.$row->item->name,
                                'qty'                       => CustomHelper::formatConditionalQty($qtyBalance),
                                'unit'                      => $row->itemUnit->unit->code,
                                'qty_stock'                 => CustomHelper::formatConditionalQty($qtyBalance * $row->qty_conversion),
                                'unit_stock'                => $row->item->uomUnit->code,
                                'place_id'                  => $row->place_id,
                                'place_name'                => $row->place->code,
                                'line_id'                   => $row->line_id ? $row->line_id : '',
                                'line_name'                 => $row->line()->exists() ? $row->line->name : '-',
                                'machine_id'                => $row->machine_id ? $row->machine_id : '',
                                'machine_name'              => $row->machine()->exists() ? $row->machine->name : '-',
                                'department_id'             => $row->department_id ? $row->department_id : '',
                                'department_name'           => $row->department_id ? $row->department->name : '-',
                                'warehouse_id'              => $row->warehouse_id,
                                'warehouse_name'            => $row->warehouse->name,
                                'note'                      => $row->note ? $row->note : '',
                                'note2'                     => $row->note2 ? $row->note2 : '',
                                'qty_conversion'            => $row->qty_conversion,
                                'is_activa'                 => $row->item->itemGroup->is_activa ? $row->item->itemGroup->is_activa : '',
                            ];
                            if($row->item()->exists()){
                                if($row->item->itemGroup->is_activa){
                                    $serials[] = [
                                        'purchase_order_detail_id'  => $row->id,
                                        'item_id'                   => $row->item_id,
                                        'item_name'                 => $row->item->code.' - '.$row->item->name,
                                        'qty_serial'                => $qtyBalance,
                                    ];
                                    $maxcolumn = $qtyBalance > $maxcolumn ? $qtyBalance : $maxcolumn;
                                }
                            }
                        }
                    }
    
                    $data['details'] = $details;
                    $data['serials'] = $serials;
                    $data['maxcolumn'] = $maxcolumn;
                }else{
                    $data['status'] = '500';
                    $data['message'] = 'Seluruh item pada purchase order '.$data->code.' telah diterima di gudang.';
                }
            }elseif($request->type == '2'){
                if($data->hasBalanceRM()){
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Goods Receipt');
                    $details = [];
                    $serials = [];
                    $maxcolumn = 0;
                    foreach($data->purchaseOrderDetail as $row){
                        if($request->type == '1'){
                            $qtyBalance = $row->getBalanceReceipt();
                        }elseif($request->type == '2'){
                            $qtyBalance = $row->getBalanceReceiptRM();
                        }
                        
                        if($qtyBalance > 0){
                            $details[] = [
                                'purchase_order_detail_id'  => $row->id,
                                'item_id'                   => $row->item_id,
                                'item_name'                 => $row->item->code.' - '.$row->item->name,
                                'qty'                       => CustomHelper::formatConditionalQty($qtyBalance),
                                'unit'                      => $row->itemUnit->unit->code,
                                'qty_stock'                 => CustomHelper::formatConditionalQty($qtyBalance * $row->qty_conversion),
                                'unit_stock'                => $row->item->uomUnit->code,
                                'place_id'                  => $row->place_id,
                                'place_name'                => $row->place->code,
                                'line_id'                   => $row->line_id ? $row->line_id : '',
                                'line_name'                 => $row->line()->exists() ? $row->line->name : '-',
                                'machine_id'                => $row->machine_id ? $row->machine_id : '',
                                'machine_name'              => $row->machine()->exists() ? $row->machine->name : '-',
                                'department_id'             => $row->department_id ? $row->department_id : '',
                                'department_name'           => $row->department_id ? $row->department->name : '-',
                                'warehouse_id'              => $row->warehouse_id,
                                'warehouse_name'            => $row->warehouse->name,
                                'note'                      => $row->note ? $row->note : '',
                                'note2'                     => $row->note2 ? $row->note2 : '',
                                'qty_conversion'            => $row->qty_conversion,
                                'is_activa'                 => $row->item->itemGroup->is_activa ? $row->item->itemGroup->is_activa : '',
                            ];
                            if($row->item()->exists()){
                                if($row->item->itemGroup->is_activa){
                                    $serials[] = [
                                        'purchase_order_detail_id'  => $row->id,
                                        'item_id'                   => $row->item_id,
                                        'item_name'                 => $row->item->code.' - '.$row->item->name,
                                        'qty_serial'                => $qtyBalance,
                                    ];
                                    $maxcolumn = $qtyBalance > $maxcolumn ? $qtyBalance : $maxcolumn;
                                }
                            }
                        }
                    }
    
                    $data['details'] = $details;
                    $data['serials'] = $serials;
                    $data['maxcolumn'] = $maxcolumn;
                }else{
                    $data['status'] = '500';
                    $data['message'] = 'Seluruh item pada purchase order '.$data->code.' telah diterima di gudang.';
                }
            }
        }

        return response()->json($data);
    }

    public function getPurchaseOrderAll(Request $request){
        $rows = PurchaseOrder::where('account_id',$request->id)->whereIn('status',['2','3'])->get();
        
        $arrdata = [];
        
        foreach($rows as $data){
            if(!$data->used()->exists()){
                if($data->hasBalance()){
                    CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Goods Receipt');
                    $details = [];

                    foreach($data->purchaseOrderDetail as $row){
                        $details[] = [
                            'purchase_order_detail_id'  => $row->id,
                            'item_id'                   => $row->item_id,
                            'item_name'                 => $row->item->code.' - '.$row->item->name,
                            'qty'                       => CustomHelper::formatConditionalQty($row->getBalanceReceipt()),
                            'item_unit_id'              => $row->item_unit_id,
                            'place_id'                  => $row->place_id,
                            'place_name'                => $row->place->code.' - '.$row->place->company->name,
                            'department_id'             => $row->department_id,
                            'department_name'           => $row->department->name,
                            'warehouse_id'              => $row->warehouse_id,
                            'warehouse_name'            => $row->warehouse->name,
                        ];
                    }
    
                    $data['details'] = $details;
                    $arrdata[] = $data;
                }
            }
        }

        return response()->json($arrdata);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
           /*  'code'			            => $request->temp ? ['required', Rule::unique('good_receipts', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:good_receipts,code',
             *//* 'account_id'                => 'required', */
            'company_id'                => 'required',
			'receiver_name'			    => 'required',
			'post_date'		            => 'required',
            'document_date'		        => 'required',
            'delivery_no'		        => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'account_id.required'               => 'Supplier/vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'receiver_name.required'            => 'Nama penerima tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'document_date.required' 			=> 'Tanggal dokumen tidak boleh kosong.',
            'delivery_no.required' 			    => 'No surat jalan tidak boleh kosong.',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array'
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $totalall = 0;
            $taxall = 0;
            $wtaxall = 0;
            $grandtotalall = 0;
            $overtolerance = false;
            $arrDetail = [];
            $passedSerial = true;
            $arrErrorSerial = [];
            $passedScale = true;
            $arrMustScaleItem = [];

            if(!$request->temp){
                if($request->arr_serial){
                    foreach($request->arr_serial as $keyserial => $rowserial){
                        $itemcek = ItemSerial::where('item_id',intval($request->arr_serial_item[$keyserial]))->where('serial_number',$rowserial)->first();
                        if($itemcek){
                            $passedSerial = false;
                            $arrErrorSerial[] = $itemcek->item->name.' - '.$rowserial;
                        }
                    }
                }
            }

            if(!$passedSerial){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Terdapat serial yang telah terpakai untuk item yang diterima. Daftarnya adalah sbb : '.implode(', ',$arrErrorSerial).'.',
                ]);
            }

            $arrToleranceMessage = [];

            $account_id = NULL;

            foreach($request->arr_purchase as $key => $row){
                $wtax = 0;
                $total = 0;
                $grandtotal = 0;
                $tax = 0;

                $pod = PurchaseOrderDetail::find(intval($row));

                $account_id = $pod->purchaseOrder->account_id;

                if($pod){

                    /* if($pod->item->is_quality_check){
                        if(!$request->arr_scale[$key] && $request->account_id){
                            $passedScale = false;
                            $arrMustScaleItem[] = $pod->item->code.' - '.$pod->item->name;
                        }
                    } */

                    $tolerance_gr = $pod->item->tolerance_gr ? $pod->item->tolerance_gr : 0;

                    $balanceqtygr = floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key]))) + $pod->qtyGR();

                    $balance = round($balanceqtygr - $pod->qty,2);
                    $percent_balance = round(($balance / $pod->qty) * 100,2);
                    if($percent_balance > $tolerance_gr){
                        $overtolerance = true;
                        $arrToleranceMessage[] = 'Item '.$pod->item->name.' toleransi terima '.$tolerance_gr.'% sedangkan kelebihan qty '.$percent_balance.'% dari '.CustomHelper::formatConditionalQty($balance).'/'.CustomHelper::formatConditionalQty($pod->qty).'. Qty sudah diterima dan akan diterima sebesar '.CustomHelper::formatConditionalQty($balanceqtygr).', Qty PO sebesar '.CustomHelper::formatConditionalQty($pod->qty);
                    }

                    $discount = $pod->purchaseOrder->discount;
                    $subtotal = $pod->purchaseOrder->subtotal;

                    $rowprice = 0;

                    $bobot = $pod->subtotal / $subtotal;
                    $rowprice = $pod->price;

                    if($pod->total){
                        $total = $pod->total;
                    }else{
                        $total = round(($rowprice * floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])))) - ($bobot * $discount),2);
                    }

                    if($pod->is_tax == '1' && $pod->is_include_tax == '1'){
                        $total = round($total / (1 + ($pod->percent_tax / 100)),2);
                    }

                    if($pod->is_tax == '1'){
                        $tax = round($total * ($pod->percent_tax / 100),2);
                    }

                    if($pod->is_wtax == '1'){
                        $wtax = round($total * ($pod->percent_wtax / 100),2);
                    }

                    $grandtotal = $total + $tax - $wtax;

                    $arrDetail[] = [
                        'total'         => $total,
                        'tax'           => $tax,
                        'wtax'          => $wtax,
                        'grandtotal'    => $grandtotal,
                    ];

                    $totalall += $total;
                    $taxall += $tax;
                    $wtaxall += $wtax;
                    $grandtotalall += $grandtotal;
                }
            }

            if($overtolerance){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Prosentase qty diterima melebihi prosentase toleransi yang telah diatur. Dengan keterangan sbb : '.implode(', ',$arrToleranceMessage)
                ]);
            }

            if(!$passedScale){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Ups. Barang : '.implode(', ',$arrMustScaleItem).' harus memilih data timbangan / QC karena termasuk item wajib QC.',
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = GoodReceipt::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Goods Receipt PO telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/good_receipts');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $account_id;
                        $query->company_id = $request->company_id;
                        $query->receiver_name = $request->receiver_name;
                        $query->post_date = $request->post_date;
                        $query->document_date = $request->document_date;
                        $query->delivery_no = $request->delivery_no;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->total = $totalall;
                        $query->tax = $taxall;
                        $query->wtax = $wtaxall;
                        $query->grandtotal = $grandtotalall;
                        $query->type = $request->type;
                        $query->status = '1';

                        $query->save();

                        foreach($query->goodReceiptDetail as $row){
                            $row->itemSerial()->delete();
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status GRPO sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=GoodReceipt::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                   
                    $query = GoodReceipt::create([
                        'code'			        => $newCode,
                        'user_id'		        => session('bo_id'),
                        'account_id'            => $account_id,
                        'company_id'            => $request->company_id,
                        'receiver_name'         => $request->receiver_name,
                        'post_date'             => $request->post_date,
                        'document_date'         => $request->document_date,
                        'delivery_no'           => $request->delivery_no,
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receipts') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'total'                 => $totalall,
                        'tax'                   => $taxall,
                        'wtax'                  => $wtaxall,
                        'grandtotal'            => $grandtotalall,
                        'type'                  => $request->type,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_purchase as $key => $row){
                        $pod = PurchaseOrderDetail::find(intval($row));
                        $grd = GoodReceiptDetail::create([
                            'good_receipt_id'           => $query->id,
                            'purchase_order_detail_id'  => $row,
                            'good_scale_id'             => $request->arr_scale[$key] ? $request->arr_scale[$key] : NULL,
                            'item_id'                   => $request->arr_item[$key],
                            'qty'                       => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'item_unit_id'              => $pod->item_unit_id,
                            'qty_conversion'            => $pod->qty_conversion,
                            'total'                     => $arrDetail[$key]['total'],
                            'tax'                       => $arrDetail[$key]['tax'],
                            'wtax'                      => $arrDetail[$key]['wtax'],
                            'grandtotal'                => $arrDetail[$key]['grandtotal'],
                            'note'                      => $request->arr_note[$key],
                            'note2'                     => $request->arr_note2[$key],
                            'remark'                    => $request->arr_remark[$key],
                            'water_content'             => str_replace(',','.',str_replace('.','',$request->arr_water_content[$key])),
                            'viscosity'                 => str_replace(',','.',str_replace('.','',$request->arr_viscosity[$key])),
                            'residue'                   => str_replace(',','.',str_replace('.','',$request->arr_residue[$key])),
                            'place_id'                  => $request->arr_place[$key],
                            'line_id'                   => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                            'machine_id'                => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                            'department_id'             => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                            'warehouse_id'              => $request->arr_warehouse[$key]
                        ]);
                        if($request->arr_serial_po){
                            foreach($request->arr_serial_po as $keyserial => $rowserial){
                                if($rowserial == $row){
                                    ItemSerial::create([
                                        'lookable_type'             => $grd->getTable(),
                                        'lookable_id'               => $grd->id,
                                        'item_id'                   => $request->arr_serial_item[$keyserial],
                                        'serial_number'             => $request->arr_serial[$keyserial],
                                    ]);
                                }
                            }
                        }
                    }

                    CustomHelper::sendApproval('good_receipts',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receipts',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');

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
        $data   = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">
                        <div class="col s12">'.$data->code.$x.'</div>
                        <div class="col s12">
                            <table class="bordered" style="min-width:100%;max-width:100%;">
                                <thead>
                                    <tr>
                                        <th class="center-align" colspan="13">Daftar Item</th>
                                    </tr>
                                    <tr>
                                        <th class="center-align">No.</th>
                                        <th class="center-align">Item</th>
                                        <th class="center-align">Qty</th>
                                        <th class="center-align">Satuan</th>
                                        <th class="center-align">Keterangan 1</th>
                                        <th class="center-align">Keterangan 2</th>
                                        <th class="center-align">Remark</th>
                                        <th class="center-align">Plant</th>
                                        <th class="center-align">Line</th>
                                        <th class="center-align">Mesin</th>
                                        <th class="center-align">Divisi</th>
                                        <th class="center-align">Gudang</th>
                                        <th class="center-align">Timbangan</th>
                                    </tr>
                                </thead>
                                <tbody>';
        $totalqty=0;
        foreach($data->goodReceiptDetail as $key => $rowdetail){
            $totalqty+=$rowdetail->qty;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$rowdetail->item->code.' - '.$rowdetail->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($rowdetail->qty).'</td>
                <td class="center-align">'.$rowdetail->itemUnit->unit->code.'</td>
                <td class="center-align">'.$rowdetail->note.'</td>
                <td class="center-align">'.$rowdetail->note2.'</td>
                <td class="center-align">'.$rowdetail->remark.'</td>
                <td class="center-align">'.$rowdetail->place->code.'</td>
                <td class="center-align">'.($rowdetail->line()->exists() ? $rowdetail->line->name : '-').'</td>
                <td class="center-align">'.($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-').'</td>
                <td class="center-align">'.($rowdetail->department_id ? $rowdetail->department->name : '-').'</td>
                <td class="center-align">'.$rowdetail->warehouse->name.'</td>
                <td class="center-align">'.($rowdetail->goodScale()->exists() ? $rowdetail->goodScale->code : '-').'</td>
            </tr>
            <tr>
                <td colspan="13">Serial No : '.$rowdetail->listSerial().'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                </tr>  
        ';
        
        $string .= '</tbody></table>';

        $string .= '</td></tr>';

        $string .= '</tbody></table></div><div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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
        
        $pr = GoodReceipt::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Goods Receipt (Penerimaan Barang)',
                'data'      => $pr
            ];

            return view('admin.approval.good_receipt', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $grm = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $grm['account_name'] = $grm->account->name;
        $grm['code_place_id'] = substr($grm->code,7,2);

        $arr = [];
        $serials = [];
        
        foreach($grm->goodReceiptDetail as $row){
            $arr[] = [
                'purchase_order_detail_id'  => $row->purchase_order_detail_id,
                'good_scale_id'             => $row->goodScale()->exists() ? $row->good_scale_id : '',
                'good_scale_name'           => $row->goodScale()->exists() ? $row->goodScale->code.' '.$row->goodScale->item->name.' '.$row->goodScale->qty_final.' '.$row->goodScale->itemUnit->unit->code : '',
                'item_id'                   => $row->item_id,
                'item_name'                 => $row->item->name,
                'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                'unit'                      => $row->itemUnit->unit->code,
                'qty_stock'                 => CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion),
                'unit_stock'                => $row->item->uomUnit->code,
                'note'                      => $row->note ? $row->note : '',
                'note2'                     => $row->note2 ? $row->note2 : '',
                'remark'                    => $row->remark,
                'water_content'             => CustomHelper::formatConditionalQty($row->water_content),
                'viscosity'                 => CustomHelper::formatConditionalQty($row->viscosity),
                'residue'                   => CustomHelper::formatConditionalQty($row->residue),
                'place_id'                  => $row->place_id,
                'place_name'                => $row->place->code,
                'line_id'                   => $row->line_id ? $row->line_id : '',
                'line_name'                 => $row->line_id ? $row->line->name : '-',
                'machine_id'                => $row->machine_id ? $row->machine_id : '',
                'machine_name'              => $row->machine_id ? $row->machine->name : '-',
                'department_id'             => $row->department_id ? $row->department_id : '',
                'department_name'           => $row->department_id ? $row->department->name : '-',
                'warehouse_id'              => $row->warehouse_id,
                'warehouse_name'            => $row->warehouse->name,
                'is_activa'                 => $row->item->itemGroup->is_activa ? $row->item->itemGroup->is_activa : '',
                'qty_conversion'            => $row->qty_conversion,
                'secret_po'                 => $row->item->is_hide_supplier ?? '',
            ];
        }

        foreach($grm->goodReceiptDetail as $row){
            if($row->itemSerial()->exists()){
                $rowserials = [];

                foreach($row->itemSerial as $rowserial){
                    $rowserials[] = $rowserial->serial_number;
                }

                $serials[] = [
                    'purchase_order_id'         => $row->purchaseOrderDetail->purchaseOrder->id,
                    'purchase_order_detail_id'  => $rowserial->goodReceiptDetail->purchase_order_detail_id,
                    'item_id'                   => $row->item_id,
                    'item_name'                 => $row->item->code.' - '.$row->item->name,
                    'list_serial_number'        => $rowserials,
                ];
            }
        }

        $grm['details'] = $arr;
        $grm['serials'] = $serials;
        				
		return response()->json($grm);
    }

    public function voidStatus(Request $request){
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }
            $array_minus_stock=[];
          
            foreach($query->goodReceiptDetail as $row_good_receipt_detail){
                $item_real_stock = $row_good_receipt_detail->item->getStockPlaceWarehouse($row_good_receipt_detail->place_id,$row_good_receipt_detail->warehouse_id);
                $item_stock_detail = $row_good_receipt_detail->qtyConvert();
                if($item_real_stock-$item_stock_detail < -1){
                    $array_minus_stock[]=$row_good_receipt_detail->item->name;
                }
            }
            if(count($array_minus_stock) > 0){
                $arrError = [];
                foreach($array_minus_stock as $row){
                    $arrError[] = $row;
                }
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf GRPO tidak dapat di void karena item stock saat ini kurang dari 0. Daftar Item : '.implode(', ',$arrError),
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
                    'message' => 'Data telah digunakan pada Landed Cost / A/P Invoice.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                $query->updateRootDocumentStatusProcess();

                foreach($query->goodReceiptDetail as $row){
                    $row->itemSerial()->delete();
                }

                CustomHelper::removeJournal('good_receipts',$query->id);
                CustomHelper::removeCogs('good_receipts',$query->id);
                CustomHelper::sendNotification('good_receipts',$query->id,'Goods Receipt No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_receipts',$query->id);
    
                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good receipt data');
                
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
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->goodReceiptDetail as $row){
                $row->itemSerial()->delete();
            }

            CustomHelper::removeJournal('good_receipts',$query->id);
            CustomHelper::removeCogs('good_receipts',$query->id);
            CustomHelper::removeApproval('good_receipts',$query->id);

            $query->goodReceiptDetail()->delete();

            activity()
                ->performedOn(new GoodReceipt())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good receipt data');

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
                $pr = GoodReceipt::where('code',$row)->first();
                
                if($pr){
                    
                    $pdf = PrintHelper::print($pr,'Good Receipt','a5','landscape','admin.print.inventory.good_receipt_individual');
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
                        $query = GoodReceipt::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Receipt','a5','landscape','admin.print.inventory.good_receipt_individual');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = GoodReceipt::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Receipt','a5','landscape','admin.print.inventory.good_receipt_individual');
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

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = GoodReceipt::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $pdf = PrintHelper::print($pr,'Good Receipt','a5','landscape','admin.print.inventory.good_receipt_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $menu = Menu::where('url','good_receipt_po')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
        $modedata = $menuUser->mode ?? '';
        $nominal = $menuUser->show_nominal ?? '';
		return Excel::download(new ExportGoodReceipt($post_date,$end_date,$mode,$modedata,$nominal,$this->datawarehouses), 'good_receipt_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $menu = Menu::where('url','good_receipt_po')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		$modedata = $request->modedata ? $request->modedata : '';
        $nominal = $menuUser->show_nominal ?? '';
		return Excel::download(new ExportGoodReceiptTransactionPage($search,$post_date,$end_date,$status,$modedata,$nominal,$this->datawarehouses), 'good_receipt_'.uniqid().'.xlsx');
    }
    
    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();
        $data_link=[];
        $data_go_chart=[];

        
        
        if($query) {
            $data_good_receipt = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                ],
                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($query->code),
            ];
            $data_go_chart[]=$data_good_receipt;
            

            //pengambilan foreign branch
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_gr',$query->id);
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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('purchase_orders',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = GoodReceipt::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->journal->note,
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

    public function getOutstanding(Request $request){
       
		
		return Excel::download(new ExportOutstandingGRPO($this->datawarehouses), 'outstanding_grpo_'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = GoodReceipt::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new GoodReceipt())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Good Receipt data');
    
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
}