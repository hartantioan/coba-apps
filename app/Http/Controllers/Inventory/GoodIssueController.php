<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Models\GoodIssueRequest;
use App\Models\Journal;
use App\Models\MaterialRequest;
use App\Models\Place;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\PurchaseOrder;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\Item;

use App\Models\LandedCost;

use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;


use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportGoodIssueTransactionPage;
use App\Models\GoodIssueDetail;
use App\Models\User;
use App\Helpers\TreeHelper;

use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportGoodIssue;
use App\Models\Division;
use App\Models\GoodReceiptDetailSerial;
use App\Models\InventoryCoa;
use App\Models\ItemSerial;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\UsedData;
class GoodIssueController extends Controller
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
            'title'     => 'Barang Keluar',
            'content'   => 'admin.inventory.good_issue',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'line'      => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'machine'   => Machine::where('status','1')->orderBy('name')->get(),
            'coa_cost'  => InventoryCoa::where('status','1')->where('type','1')->get(),
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $gi = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$gi;
        
        
        
        if($query) {

            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_good_issue',$query->id);
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
                'link'    => $data_link,
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

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = GoodIssue::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodIssue::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = GoodIssue::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodIssueDetail', function($query) use($search, $request){
                                $query->whereHas('itemStock',function($query) use($search, $request){
                                    $query->whereHas('item',function($query) use($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
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
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodIssue::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodIssueDetail', function($query) use($search, $request){
                                $query->whereHas('itemStock',function($query) use($search, $request){
                                    $query->whereHas('item',function($query) use($search, $request){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
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
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
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
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
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
           /*  'code'			            => $request->temp ? ['required', Rule::unique('good_issues', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:good_issues,code',
             */'company_id'                => 'required',
			'post_date'		            => 'required',
            'note'		                => 'required',
            'arr_item_stock'            => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_inventory_coa'         => 'required|array',
            'arr_lookable_type'         => 'required|array',
            'arr_lookable_id'           => 'required|array',
		], [
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
           'company_id.required'               => 'Perusahaan tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
            'note.required' 				    => 'Keterangan utama tidak boleh kosong.',
			'warehouse_id.required'				=> 'Gudang tujuan tidak boleh kosong',
            'arr_item_stock.required'           => 'Item stok tidak boleh kosong',
            'arr_item_stock.array'              => 'Item stok harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array',
            'arr_inventory_coa.required'        => 'Tipe Biaya tidak boleh kosong',
            'arr_inventory_coa.array'           => 'Tipe Biaya harus dalam bentuk array',
            'arr_lookable_type.required'        => 'Tipe referensi tidak boleh kosong',
            'arr_lookable_type.array'           => 'Tipe referensi harus dalam bentuk array',
            'arr_lookable_id.required'          => 'Id referensi tidak boleh kosong',
            'arr_lookable_id.array'             => 'Id referensi harus dalam bentuk array',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            /* DB::beginTransaction();
            try { */

                $grandtotal = 0;
                $passed = true;
                $passedQtyMinus = true;
                $arrItemNotPassed = [];
                $passedZeroQty = true;
                $arr_item_stock = $request->arr_item_stock;
                $arr_qty = $request->arr_qty;


                $keys = array_keys($arr_item_stock);

                
                array_multisort($arr_item_stock, SORT_ASC, $arr_qty, $keys);
                $cumulative_qty = [];
                
                $x=0;
                foreach ($arr_item_stock as $key_arr => $row_stock) {
                    // Remove commas and convert qty to int
                    $qty = str_replace(',','.',str_replace('.','',$arr_qty[$key_arr]));
                    $qty = floatval($qty);
                    
                    // If the current item matches the previous one, accumulate the quantity
                    if ($key_arr > 0 && $row_stock == $arr_item_stock[$key_arr - 1]) {
                        $cumulative_qty[$x] = $cumulative_qty[$x] + $qty;
                    } else { // Otherwise, set the quantity directly
                        if($key_arr>0){
                            $x++; 
                        }
                        $cumulative_qty[$x] = $qty;
                    }
                }
                $unique_array = array_unique($arr_item_stock);
                $unique_array = array_values($unique_array);
                
                
                foreach($unique_array as $key => $row){
                    if($cumulative_qty[$key] <= 0){
                        $passedZeroQty = false;
                    }
                    $rowprice = NULL;
                    $item_stock = ItemStock::find(intval($row));
                    $rowprice = $item_stock->priceNow();
                    $grandtotal += round($rowprice * $cumulative_qty[$key],2);
                    if($item_stock){

                        $qtyout = $cumulative_qty[$key];

                        $itemCogsBefore = ItemCogs::where('place_id',$item_stock->place_id)->where('warehouse_id',$item_stock->warehouse_id)->where('item_id',$item_stock->item_id)->whereDate('date','<=',$request->post_date)->orderByDesc('date')->orderByDesc('id')->first();
                        $itemCogsAfter = ItemCogs::where('place_id',$item_stock->place_id)->where('warehouse_id',$item_stock->warehouse_id)->where('item_id',$item_stock->item_id)->whereDate('date','>',$request->post_date)->orderBy('date')->orderBy('id')->get();

                        if($itemCogsBefore){
                            if($itemCogsBefore->qty_final < $qtyout){
                                $passed = false;
                                $arrItemNotPassed[] = $item_stock->item->name;
                            }else{
                                $startqty = $itemCogsBefore->qty_final - $qtyout;
                                foreach($itemCogsAfter as $row){
                                    if($row->type == 'IN'){
                                        $startqty += $row->qty_in;
                                    }elseif($row->type == 'OUT'){
                                        $startqty -= $row->qty_out;
                                    }
                                    if($startqty < 0){
                                        $passedQtyMinus = false;
                                       
                                    }
                                }
                            }
                        }else{
                            $passed = false;
                        }

                    }
                }

                if($request->arr_serial){
                    $passedQtyAndSerial = true;
                    foreach($request->arr_serial as $key => $row){
                        if($row){
                            $rowArr = explode(',',$row);
                            if(count($rowArr) != floatval(str_replace(',','.',str_replace('.','',$request->arr_qty[$key])))){
                                $passedQtyAndSerial = false;
                            }
                        }
                    }

                    if($passedQtyAndSerial == false){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Maaf, salah satu item aktiva jumlah qty dengan jumlah nomor serial tidak sama.',
                        ]);
                    }
                }

                if($passedZeroQty == false){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Maaf, qty tidak boleh 0.',
                    ]);
                }

                if($passedQtyMinus == false){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Maaf, pada tanggal setelah tanggal posting terdapat qty minus pada stok.',
                    ]);
                }
    
                if($passed == false){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Maaf, pada tanggal '.date('d/m/Y',strtotime($request->post_date)).', barang '.implode(", ",$arrItemNotPassed).', stok tidak tersedia atau melebihi stok yang tersedia.',
                    ]);
                }
                if($request->temp){
                    $query = GoodIssue::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Barang Keluar telah diapprove, anda tidak bisa melakukan perubahan.'
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
                            $document = $request->file('file')->store('public/good_issues');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->grandtotal = round($grandtotal,2);
                        $query->status = '1';

                        $query->save();

                        foreach($query->goodIssueDetail as $row){
                            $row->itemSerial()->update([
                                'usable_id'     => NULL,
                                'usable_type'   => NULL,
                            ]);
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status barang keluar sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=GoodIssue::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                   
                    $query = GoodIssue::create([
                        'code'			        => $newCode,
                        'user_id'		        => session('bo_id'),
                        'company_id'		    => $request->company_id,
                        'post_date'             => $request->post_date,
                        'document'              => $request->file('file') ? $request->file('file')->store('public/good_issues') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'grandtotal'            => round($grandtotal,2)
                    ]);
                }
                
                if($query) {
                    
                    foreach($request->arr_item_stock as $key => $row){
                        $rowprice = NULL;
                        $item_stock = ItemStock::find(intval($row));
                        $rowprice = $item_stock->priceNow();
                        $gid = GoodIssueDetail::create([
                            'good_issue_id'         => $query->id,
                            'item_stock_id'         => $row,
                            'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'price'                 => $rowprice,
                            'total'                 => $rowprice * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                  => $request->arr_note[$key],
                            'note2'                 => $request->arr_note2[$key],
                            'inventory_coa_id'      => $request->arr_inventory_coa[$key] ? $request->arr_inventory_coa[$key] : NULL,
                            'coa_id'                => $request->arr_inventory_coa[$key] ? NULL : ($request->arr_coa[$key] ? $request->arr_coa[$key] : NULL),
                            'lookable_type'         => $request->arr_lookable_type[$key] ? $request->arr_lookable_type[$key] : NULL,
                            'lookable_id'           => $request->arr_lookable_id[$key] ? $request->arr_lookable_id[$key] : NULL,
                            'cost_distribution_id'  => $request->arr_cost_distribution[$key] ? $request->arr_cost_distribution[$key] : NULL,
                            'place_id'              => $request->arr_place[$key] ? $request->arr_place[$key] : NULL,
                            'warehouse_id'          => $item_stock->warehouse_id,
                            'area_id'               => $item_stock->area_id ? $item_stock->area_id : NULL,
                            'item_shading_id'       => $item_stock->item_shading_id ? $item_stock->item_shading_id : NULL,
                            'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                            'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                            'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                            'project_id'            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                            'requester'             => $request->arr_requester[$key] ? $request->arr_requester[$key] : NULL,
                            'qty_return'            => $request->arr_qty_return[$key] ? str_replace(',','.',str_replace('.','',$request->arr_qty_return[$key])) : 0,
                        ]);

                        if($request->arr_serial[$key]){
                            $rowArr = explode(',',$request->arr_serial[$key]);
                            foreach($rowArr as $rowdetail){
                                ItemSerial::find(intval($rowdetail))->update([
                                    'usable_type'   => $gid->getTable(),
                                    'usable_id'     => $gid->id
                                ]);
                            }
                        }
                    }

                    CustomHelper::sendApproval('good_issues',$query->id,$query->note);
                    CustomHelper::sendNotification('good_issues',$query->id,'Barang Keluar No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new GoodIssue())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit penggunaan barang.');

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

                /* DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            } */
		}
		
		return response()->json($response);
    }

    public function rowDetail(Request $request){
        $data   = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="19">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Ket.1</th>
                                <th class="center-align">Ket.2</th>
                                <th class="center-align">Tipe Biaya</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Dari Plant</th>
                                <th class="center-align">Dari Gudang</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Area</th>
                                <th class="center-align">Shading</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                                <th class="center-align">Requester</th>
                                <th class="center-align">Qty Kembali</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totalqtyreturn=0;
        foreach($data->goodIssueDetail as $key => $row){
            $totalqty+=$row->qty;
            $totalqtyreturn+=$row->qty_return;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->itemStock->item->code.' - '.$row->itemStock->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->itemStock->item->uomUnit->code.'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="">'.($row->inventoryCoa()->exists() ? $row->inventoryCoa->name : '-').'</td>
                <td class="">'.($row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '-').'</td>
                <td class="center-align">'.$row->itemStock->place->code.'</td>
                <td class="center-align">'.$row->itemStock->warehouse->name.'</td>
                <td class="center-align">'.($row->costDistribution()->exists() ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-').'</td>
                <td class="center-align">'.$row->getPlace().'</td>
                <td class="center-align">'.($row->itemStock->area()->exists() ? $row->itemStock->area->name : '-').'</td>
                <td class="center-align">'.($row->itemShading()->exists() ? $row->itemShading->code : '-').'</td>
                <td class="center-align">'.$row->getLine().'</td>
                <td class="center-align">'.$row->getMachine().'</td>
                <td class="center-align">'.$row->getDepartment().'</td>
                <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td class="center-align">'.($row->requester ? $row->requester : '-').'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty_return).'</td>
            </tr>
            <tr>
                <td colspan="16">Serial : '.$row->listSerial().'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;" colspan="16" ></td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqtyreturn, 3, ',', '.') . '</td>
                </tr>  
        ';

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

    public function show(Request $request){
        $gr = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();
        $gr['code_place_id'] = substr($gr->code,7,2);

        $arr = [];
        $arr_used = [];
        foreach($gr->goodIssueDetail as $row){
            $arr[] = [
                'item_id'                   => $row->itemStock->item_id,
                'item_name'                 => $row->itemStock->item->code.' - '.$row->itemStock->item->name,
                'uom'                       => $row->itemStock->item->uomUnit->code,
                'item_stock_id'             => $row->item_stock_id,
                'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                'qtyraw'                    => in_array($gr->status,['2','3']) ? CustomHelper::formatConditionalQty($row->qty + $row->itemStock->qty) : number_format($row->itemStock->qty),
                'price'                     => number_format($row->price,2,',','.'),
                'total'                     => number_format($row->total,2,',','.'),
                'inventory_coa_id'          => $row->inventoryCoa()->exists() ? $row->inventory_coa_id : '',
                'inventory_coa_name'        => $row->inventoryCoa()->exists() ? $row->inventoryCoa->code.' - '.$row->inventoryCoa->name : '',
                'coa_inventory_id'          => $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa_id : '',
                'coa_inventory_name'        => $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa->name : '',
                'coa_id'                    => $row->coa()->exists() ? $row->coa_id : '',
                'coa_name'                  => $row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '',
                'note'                      => $row->note ? $row->note : '',
                'note2'                     => $row->note2 ? $row->note2 : '',
                'lookable_type'             => $row->lookable_type ? $row->lookable_type : '',
                'lookable_id'               => $row->lookable_id ? $row->lookable_id : '',
                'reference_id'              => $row->lookable_type ? ($row->lookable_type == 'material_request_details' ? $row->lookable->materialRequest->id : $row->lookable->goodIssueRequest->id) : '',
                'stock_list'                => $row->itemStock->item->currentStock($this->dataplaces,$this->datawarehouses),
                'place_id'                  => $row->place_id,
                'line_id'                   => $row->line_id,
                'machine_id'                => $row->machine_id,
                'department_id'             => $row->department_id,
                'project_id'                => $row->project()->exists() ? $row->project->id : '',
                'project_name'              => $row->project()->exists() ? $row->project->name : '',
                'requester'                 => $row->requester ?? '',
                'qty_return'                => CustomHelper::formatConditionalQty($row->qty_return),
                'is_activa'                 => $row->itemStock->item->itemGroup->is_activa ? $row->itemStock->item->itemGroup->is_activa : '',
                'list_serial'               => $row->arrSerial(),
                'cost_distribution_id'      => $row->cost_distribution_id ? $row->cost_distribution_id : '',
                'cost_distribution_name'    => $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
            ];
            $used_datas = $row->getParentUsedData();
            $arr_used_codes = array_column($arr_used, 'code');
            if ($used_datas && !in_array($used_datas->code, $arr_used_codes)) {
                CustomHelper::sendUsedData($used_datas->getTable(), $used_datas->id, 'Form Good Issue');
                $arr_used[] = [
                    'code' => $used_datas->code,
                    'table' => $used_datas->getTable(),
                    'id' => $used_datas->id,
                ];
            }
        }

        $gr['details'] = $arr;
        $gr['used_datas'] = $arr_used;
		return response()->json($gr);
    }

    public function voidStatus(Request $request){
        $query = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada form Good Return Issue / Barang Kembali.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->goodIssueDetail as $row){
                    $row->itemSerial()->update([
                        'usable_id'  => NULL,
                        'usable_type'=> NULL,
                    ]);
                    /* $row->delete(); */
                }

                $query->updateRootDocumentStatusProcess();

                CustomHelper::removeJournal($query->getTable(),$query->id);
                CustomHelper::removeCogs($query->getTable(),$query->id);
    
                activity()
                    ->performedOn(new GoodIssue())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good issue data');
    
                CustomHelper::sendNotification('good_issues',$query->id,'Barang Keluar No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_issues',$query->id);
                
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
        $query = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();

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

        if(!CustomHelper::checkLockAcc($query->post_date)){
            return response()->json([
                'status'  => 500,
                'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
            ]);
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

            foreach($query->goodIssueDetail as $row){
                $row->itemSerial()->update([
                    'usable_type'   => NULL,
                    'usable_id'     => NULL,
                ]);
            }

            CustomHelper::removeJournal('good_issues',$query->id);
            CustomHelper::removeCogs('good_issues',$query->id);

            /* $query->goodIssueDetail()->delete(); */

            CustomHelper::removeApproval('good_issues',$query->id);

            activity()
                ->performedOn(new GoodIssue())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good receive data');

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

    public function approval(Request $request,$id){
        
        $gr = GoodIssue::where('code',CustomHelper::decrypt($id))->first();
                
        if($gr){
            $data = [
                'title'     => 'Print Goods Receive (Barang Keluar)',
                'data'      => $gr
            ];

            return view('admin.approval.good_issue', $data);
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
            foreach($request->arr_id as $key =>$row){
                $pr = GoodIssue::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Good Issue','a5','landscape','admin.print.inventory.good_issue_individual');
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
                        $query = GoodIssue::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Issue','a5','landscape','admin.print.inventory.good_issue_individual');
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
                        $query = GoodIssue::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Issue','a5','landscape','admin.print.inventory.good_issue_individual');
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
        
        $pr = GoodIssue::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $pdf = PrintHelper::print($pr,'Good Issue','a5','landscape','admin.print.inventory.good_issue_individual',$menuUser->mode);
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportGoodIssue($post_date,$end_date,$mode), 'good_issue_'.uniqid().'.xlsx');
    }
    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportGoodIssueTransactionPage($search,$post_date,$end_date,$status,$modedata), 'good_issue'.uniqid().'.xlsx');
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = GoodIssue::where('code',CustomHelper::decrypt($id))->first();
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

    public function sendUsedData(Request $request){

        $data = GoodIssueRequest::find($request->id);
       
        if(!$data->used()->exists()){
            CustomHelper::sendUsedData($request->type,$request->id,'Form Good Issue / Barang Keluar');
            return response()->json([
                'status'    => 200,
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Dokumen no. '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.'
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
        $query_done = GoodIssue::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new GoodIssue())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Good Issue data');
    
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