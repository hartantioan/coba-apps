<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\CustomHelper;
use App\Models\ApprovalTemplateMenu;
use App\Models\Journal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use App\Jobs\ResetCogs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Menu;
use App\Models\Approval;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use App\Models\GoodIssue;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReturnPO;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use App\Models\MenuUser;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;

class MenuController extends Controller
{
    public function index()
    {
        /* $user = User::where('type','3')->orderBy('id')->get();

        foreach($user as $key => $row){
            $no = $key + 1;
            $row->update([
                'employee_no'   => 'S'.str_pad($no, 6, 0, STR_PAD_LEFT),
            ]);
        } */

        /* $data = [
            'title'     => 'Menu',
            'menus'     => Menu::whereNull('parent_id')->get(),
            'content'   => 'admin.setting.menu'
        ];

        return view('admin.layouts.index', ['data' => $data]); */

        $dp = PurchaseInvoice::whereIn('status',['2','3'])->whereHas('purchaseInvoiceDetail',function($query){
            $query->whereHas('fundRequestDetail');
        })->get();
        
        foreach($dp as $row){
            if($row->getTotalPaid() <= 0){
                foreach($row->purchaseInvoiceDetail as $rowdetail){
                    if($rowdetail->fundRequestDetail()->exists()){
                        if(!$rowdetail->fundRequestDetail->fundRequest->hasBalanceInvoice()){
                            $rowdetail->fundRequestDetail->fundRequest->update([
                                'status'			=> '3',
                                'balance_status'	=> '1'
                            ]);
                        }else{
                            $rowdetail->fundRequestDetail->fundRequest->update([
                                'status'			=> '2',
                                'balance_status'	=> NULL
                            ]);
                        }
                    }
                }
            }
        }

        /* $dp = PurchaseDownPayment::whereIn('status',['2','3'])->whereHas('purchaseDownPaymentDetail',function($query){
            $query->whereHas('fundRequestDetail');
        })->get();
        
        foreach($dp as $row){
            if($row->balanceInvoice() <= 0){
                foreach($row->purchaseDownPaymentDetail as $rowdetail){
                    $rowdetail->fundRequestDetail->fundRequest->update([
                        'balance_status'    => '1'
                    ]);
                }
            }else{
                foreach($row->purchaseDownPaymentDetail as $rowdetail){
                    $rowdetail->fundRequestDetail->fundRequest->update([
                        'balance_status'    => NULL
                    ]);
                }
            }
            if($row->getTotalPaid() <= 0){
                foreach($row->purchaseDownPaymentDetail as $rowdetail){
                    $rowdetail->fundRequestDetail->fundRequest->update([
                        'status'    => '3'
                    ]);
                }
            }else{
                foreach($row->purchaseDownPaymentDetail as $rowdetail){
                    $rowdetail->fundRequestDetail->fundRequest->update([
                        'status'    => '2'
                    ]);
                }
            }
        } */

        /* $pr = PurchaseRequest::whereIn('code',['1-OPB23060004','1-OPB23070007','1-OPB23070016','1-OPB23070019','1-OPB23070029','1-OPB23070031','1-OPB23080001','1-OPB23080002','1-OPB23080006','1-OPB23080010','1-OPB23080011','1-OPB23090004','1-OPB23090006','1-OPB23100006','1-OPB23100008','1-OPB23100010','1-OPB23100014','1-OPB23100016','1-OPB23100017','1-OPB23100019','1-OPB23110001','1-OPB23110004','1-OPB23110009','1-OPB23110012','1-OPB23110017','1-OPB23110019','1-OPB23110020','1-OPB23110024','1-OPB23110025','1-OPB23110026','1-OPB23110027','1-OPB23110028','1-OPB23110029','1-OPB23110030','1-OPB23110031','1-OPB23120002','1-OPB23120003','1-OPB23120004','1-OPB23120007','1-OPB23120008','1-OPB23120009','1-OPB23120010','1-OPB23120012','1-OPB23120013','1-OPB23120014','1-OPB23120015','1-OPB23120016','1-OPB23120017','1-OPB23120018','1-OPB23120019','1-OPB23120022','1-OPB23120023','1-OPB23120024','1-OPB23120026','1-OPB23120028','1-OPB23120029','1-OPB24010002','1-OPB24010008','1-OPB24010013','1-OPB24010015','1-OPB24010016','1-OPB24010017','1-OPB24010018','1-OPB24010021','PRQS-24P1-00000002','PRQS-24P1-00000009','PRQS-24P1-00000012','PRQS-24P1-00000013','PRQS-24P1-00000018','PRQS-24P1-00000019','PRQS-24P1-00000023','PRQS-24P1-00000029','PRQS-24P1-00000033','PRQS-24P1-00000038','PRQS-24P1-00000039','PRQS-24P1-00000040','PRQS-24P1-00000082','PRQS-24P1-00000085','PRQS-24P1-00000090','PRQS-24P1-00000148','PRQS-24P1-00000162','PRQS-24P1-00000178','PRQS-24P1-00000182'])->get();

        foreach($pr as $row){
            if($row->hasChildDocument()){
                $row->update([
                    'status'    => '3'
                ]);
            }else{
                $row->update([
                    'status'    => '5',
                    'void_date' => date('Y-m-d H:i:s'),
                    'void_note' => 'Void the purchase request data by system.'
                ]);
            }
        } */

        /* $jr = Journal::where('lookable_type','good_receipts')->get();

        foreach($jr as $row){
            foreach($row->lookable->goodReceiptDetail as $rowdetail){
                $row->update([
                    'currency_id'   => $rowdetail->purchaseOrderDetail->purchaseOrder->currency_id,
                    'currency_rate' => $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate,
                ]);
            }
            $row->update([
                'currency_id'   => 1,
                'currency_rate' => 1,
            ]);
        } */

        /* $gr = GoodReceipt::whereIn('status',['2','3'])->whereDate('post_date','<=','2024-02-29')->whereDate('post_date','>=','2024-02-01')->get();

        foreach($gr as $row){
            if(round($row->totalFromDetail(),2) !== round($row->totalFromJournal(),2)){
                echo $row->code.' '.$row->totalFromDetail().' jurnal '.$row->totalFromJournal().'<br>';
            }
        } */

        /* $data = OutgoingPayment::whereHas('paymentRequest',function($query){
            $query->whereBetween('code',['PREQ-24P1-00000037','PREQ-24P1-00000060']);
        })->get();

        foreach($data as $row){
            $row->journal->journalDetail()->delete();
            $row->journal->delete();
            CustomHelper::sendJournal($row->getTable(),$row->id,$row->account_id);
        } */

        /* foreach(MaterialRequestDetail::all() as $row){
            $total = 0;
            $itemCogs = ItemCogs::where('item_id',$row->item_id)->where('place_id',$row->place_id)->whereDate('date','<=',$row->materialRequest->post_date)->orderByDesc('id')->first();
            if($itemCogs){
                $total = $itemCogs->price_final * $row->qty * $row->qty_conversion;
            }
            $row->update([
                'total' => $total
            ]);
        } */

        /* $pr = PurchaseRequest::whereHas('purchaseRequestDetail',function($query){
            $query->where('requester','sammy');
        })->get();
        $user = User::where('name','like',"%albayani%")->first();
        foreach($pr as $row){
            if($user){
                $row->update([
                    'user_id'   => $user->id
                ]);
            }
        } */

        /* $pinv = PurchaseInvoice::all();
        foreach($pinv as $row){
            CustomHelper::sendJournal($row->getTable(),$row->id,$row->account_id);
        } */

        /* $gr = GoodReceiptDetail::all();

        foreach($gr as $row){
            $data = ItemStock::where('place_id',$row->place_id)->where('warehouse_id',$row->warehouse_id)->where('item_id',$row->item_id)->first();
            if($data){
                $data->update([
                    'qty' => $data->qty + $row->qty,
                ]);
            }else{
                ItemStock::create([
                    'place_id'      => $row->place_id,
                    'warehouse_id'  => $row->warehouse_id,
                    'item_id'       => $row->item_id,
                    'qty'           => $row->qty,
                ]);
            }
        } */

        /* $gi = GoodIssueDetail::all();

        foreach($gi as $row){
            $dataupdate = ItemStock::find($row->item_stock_id);
            $dataupdate->update([
                'qty'   => $dataupdate->qty - $row->qty,
            ]);
        } */

        /* $grcv = GoodReceiveDetail::all();

        foreach($grcv as $row){
            $dataupdate = ItemStock::where('place_id',$row->place_id)->where('warehouse_id',$row->warehouse_id)->where('item_id',$row->item_id)->first();
            $dataupdate->update([
                'qty'   => $dataupdate->qty + $row->qty,
            ]);
        } */

        /* $gr = GoodReceipt::whereIn('status',['2','3'])->whereDate('post_date','>=','2024-02-01')->get();
        $grcv = GoodReceive::whereIn('status',['2','3'])->whereDate('post_date','>=','2024-02-01')->get();
        $gi = GoodIssue::whereIn('status',['2','3'])->whereDate('post_date','>=','2024-02-01')->get();
        $grrt = GoodReturnPO::whereIn('status',['2','3'])->whereDate('post_date','>=','2024-02-01')->get();

        $data = [];

        foreach($gr as $row){
            $data[] = [
                'type'          => 'IN',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        foreach($grcv as $row){
            $data[] = [
                'type'          => 'IN',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        foreach($gi as $row){
            $data[] = [
                'type'          => 'OUT',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        foreach($grrt as $row){
            $data[] = [
                'type'          => 'OUT',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        $collection = collect($data)->sortBy(function($item) {
                        return [$item['date'], $item['type']];
                    })->values();

        foreach($collection as $row){
            CustomHelper::sendJournal($row['lookable_type'],$row['lookable_id']);
        } */

        /* ResetCogs::dispatch('2024-02-19',1,900); */

        /* $podp = PurchaseDownPayment::all();
        foreach($podp as $row){
            CustomHelper::sendJournal($row->getTable(),$row->id,$row->account_id);
        } */

        /* $item = Item::where('status','1')->get();
        foreach($item as $row){
            foreach($row->itemGroup->itemGroupWarehouse as $rowdetail){
                $itemStock = ItemStock::where('item_id',$row->id)->where('warehouse_id',$rowdetail->warehouse_id)->where('place_id',1)->first();
                if(!$itemStock){
                    ItemStock::create([
                        'item_id'       => $row->id,
                        'place_id'      => 1,
                        'warehouse_id'  => $rowdetail->warehouse_id,
                        'qty'           => 0,
                    ]);
                }
            }
        } */

        /* $data = PurchaseOrder::all();

        foreach($data as $row){
            if($row->hasBalance()){
                $row->update([
                    'status'    => '2'
                ]);
            }else{
                $row->update([
                    'status'    => '3'
                ]);
            }
        } */
    }

    function addToArr(&$arr, $data){
        if ($data['parent_id'] == 0){
            return $arr[] =  [
                'id'        => $data['id'], 
                'order'     => $data['order'], 
                'name'      => $data['name'], 
                'parent_id' => $data['parent_id'],
                'children'  => []
            ];
        }
        foreach($arr as &$e) {
            if ($e['id'] == $data['parent_id']) {
                $e['children'][] = [
                    'id'        => $data['id'], 
                    'order'     => $data['order'],
                    'name'      => $data['name'],
                    'parent_id' => $data['parent_id'], 
                    'children'  => []
                ];
                break;
            }
            $key_values = array_column($e['children'], 'order'); 
            array_multisort($key_values, SORT_ASC, $e['children']);
            $this->addToArr($e['children'], $data);
        }
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'url',
            'icon',
            'table_name',
            'parent',
            'order',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Menu::count();
        
        $query_data = Menu::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('url', 'like', "%$search%")
                            ->orWhere('icon', 'like', "%$search%")
                            ->orWhere('table_name', 'like', "%$search%")
                            ->orWhere('order', 'like', "%$search%")
                            ->orWhereHas('parentSub', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Menu::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('url', 'like', "%$search%")
                            ->orWhere('icon', 'like', "%$search%")
                            ->orWhere('table_name', 'like', "%$search%")
                            ->orWhere('order', 'like', "%$search%")
                            ->orWhereHas('parentSub', function ($query) use ($search) {
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $val->id,
                    $val->name,
                    $val->url,
                    '<i class="material-icons dp48">'.$val->icon.'</i>',
                    $val->table_name,
                    $val->parentsub()->exists() ? $val->parentSub->name : 'None',
                    $val->order,
                    $val->status(),
                    $val->isMaintenance(),
                    $val->whitelist,
                    $val->isNew(),
                    !$val->sub()->exists() ?
                    '
                        <a href="'.url('admin/setting/menu/operation_access').'/'.$val->id.'" class="btn-floating mb-1 btn-flat waves-effect waves-light purple accent-2 white-text" data-popup="tooltip" title="Edit hak akses operasional halaman"><i class="material-icons dp48">folder_shared</i></a>
					' : '',
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
            'name' 				=> 'required',
            'url'			    => $request->temp ? ['required', Rule::unique('menus', 'url')->ignore($request->temp)] : 'required|unique:menus,url',
            'icon'		        => 'required',
            'order'		        => 'required',
            'document_code'     => $request->type == '1' ? 'required' : '',
            'whitelist'         => $request->maintenance ? 'required' : '',
        ], [
            'name.required' 					=> 'Nama menu tidak boleh kosong.',
            'url.required' 					    => 'Url tidak boleh kosong.',
            'url.unique'                        => 'Url telah terpakai',
            'icon.required'			            => 'Icon tidak boleh kosong.',
            'order.required'				    => 'Urutan tidak boleh kosong.',
            'document_code.required'            => 'Kode Dokumen harus diisi',
            'whitelist.required'                => 'Whitelist IP tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->parent_id){
                $parent = Menu::find($request->parent_id);

                if($parent->menuUser()->exists()){
                    $parent->menuUser()->delete();
                    /* return response()->json([
                        'status'  => 500,
                        'message' => 'The parent menu already have(s) operation access rules, please delete it to continue add this menu as parent.'
                    ]); */
                }
            }

			if($request->temp){
                if($request->table_name){
                    $cek = Menu::where('table_name',$request->table_name)->where('id','<>',$request->temp)->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Maaf. Tabel telah terpakai.'
                        ]);
                    }
                }

                DB::beginTransaction();
                try {
                    $query = Menu::find($request->temp);

                    if($query->is_maintenance){
                        if(!$request->maintenance){
                            if($query->parentsub()->exists()){
                                $siblingMaintenance = false;
                                foreach($query->parentsub->sub as $row){
                                    if($row->is_maintenance && $row->id !== $query->id){
                                        $siblingMaintenance = true;
                                    }
                                }
                                if(!$siblingMaintenance){
                                    $query->parentSub->update([
                                        'is_maintenance' => NULL
                                    ]);
                                    if($query->parentSub->parentSub()->exists()){
                                        $query->parentSub->parentSub->update([
                                            'is_maintenance' => NULL
                                        ]);
                                        if($query->parentSub->parentSub->parentSub()->exists()){
                                            $query->parentSub->parentSub->parentSub->update([
                                                'is_maintenance' => NULL
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($query->is_new){
                        if(!$request->new){
                            if($query->parentsub()->exists()){
                                $query->parentSub->update([
                                    'is_new' => NULL
                                ]);
                                if($query->parentSub->parentSub()->exists()){
                                    $query->parentSub->parentSub->update([
                                        'is_new' => NULL
                                    ]);
                                    if($query->parentSub->parentSub->parentSub()->exists()){
                                        $query->parentSub->parentSub->parentSub->update([
                                            'is_new' => NULL
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    $query->name = $request->name;
                    $query->url = $request->url;
                    $query->icon = $request->icon;
                    $query->table_name = $request->table_name;
                    $query->parent_id = $request->parent_id ? $request->parent_id : NULL;
                    $query->order = $request->order;
                    $query->type  = $request->type;
                    $query->document_code = $request->document_code;
                    $query->status = $request->status ? $request->status : '2';
                    $query->is_maintenance = $request->maintenance ? $request->maintenance : NULL;
                    $query->is_new = $request->new ? $request->new : NULL;
                    $query->whitelist = $request->maintenance ? $request->whitelist : NULL;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{

                if($request->table_name){
                    $cek = Menu::where('table_name',$request->table_name)->first();

                    if($cek){
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Maaf. Tabel telah terpakai.'
                        ]);
                    }
                }

                DB::beginTransaction();
                try {
                    $query = Menu::create([
                        'name'			    => $request->name,
                        'url'			    => $request->url,
                        'icon'		        => $request->icon,
                        'table_name'	    => $request->table_name,
                        'parent_id'	        => $request->parent_id ? $request->parent_id : NULL,
                        'order'             => $request->order,
                        'type'              => $request->type,
                        'document_code'     => $request->document_code,
                        'status'            => $request->status ? $request->status : '2',
                        'is_maintenance'    => $request->maintenance ? $request->maintenance : NULL,
                        'is_new'            => $request->new ? $request->new : NULL,
                        'whitelist'         => $request->maintenance ? $request->whitelist : NULL,
                    ]);
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Menu())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit menu.');

                $newdata = [];
                
                if($query->table_name){
                    ApprovalTemplateMenu::where('menu_id',$query->id)->update([
                        'table_name'    => $query->table_name
                    ]);
                }

                if($request->maintenance){
                    if($query->parentsub()->exists()){
                        $query->parentSub->update([
                            'is_maintenance' => $request->maintenance
                        ]);
                        if($query->parentSub->parentSub()->exists()){
                            $query->parentSub->parentSub->update([
                                'is_maintenance' => $request->maintenance
                            ]);
                            if($query->parentSub->parentSub->parentSub()->exists()){
                                $query->parentSub->parentSub->parentSub->update([
                                    'is_maintenance' => $request->maintenance
                                ]);
                            }
                        }
                    }
                }

                if($request->new){
                    if($query->parentsub()->exists()){
                        $query->parentSub->update([
                            'is_new' => $request->new
                        ]);
                        if($query->parentSub->parentSub()->exists()){
                            $query->parentSub->parentSub->update([
                                'is_new' => $request->new
                            ]);
                            if($query->parentSub->parentSub->parentSub()->exists()){
                                $query->parentSub->parentSub->parentSub->update([
                                    'is_new' => $request->new
                                ]);
                            }
                        }
                    }
                }
                
                $newdata[] = '<option value="">Parent (Utama)</option>';

                foreach(Menu::whereNull('parent_id')->get() as $m){
                    $newdata[] = '<option value="'.$m->id.'">'.$m->name.'</option>';
                    foreach($m->sub as $m2){
                        $newdata[] = '<option value="'.$m2->id.'"> - '.$m2->name.'</option>';
                        foreach($m2->sub as $m3){
                            $newdata[] = '<option value="'.$m3->id.'"> - - '.$m3->name.'</option>';
                            foreach($m3->sub as $m4){
                                $newdata[] = '<option value="'.$m4->id.'"> - - - '.$m4->name.'</option>';
                            }
                        }
                    }
                }

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
                    'data'      => $newdata
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
        $menu = Menu::find($request->id);
        $menu['parent_id'] = $menu['parent_id'] ? $menu['parent_id'] : '';
        				
		return response()->json($menu);
    }

    public function getMenus(Request $request){
        $listItems = [];

        foreach(Menu::where('status','1')->get() as $row){
            $listItems[] = [
                'url'       => !$row->sub()->exists() ? url('admin').'/'.$row->fullUrl() : 'javascript:void(0);',
                'name'      => $row->name,
                'icon'      => $row->icon,
                'category'  => $row->parentsub()->exists() ? $row->parentsub->name : 'Parent Pages'
            ];
        }

        return response()->json([
            'status'    => 200,
            'listItems' => $listItems
        ]);
    }

    public function destroy(Request $request){
        $query = Menu::find($request->id);
		
        if($query->delete()) {
            $query->menuUser()->delete();

            activity()
                ->performedOn(new Menu())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the menu data');

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

    public function operationAccessIndex(Request $request, $id){
        $menu = Menu::find($id);

        $data = [
            'title'     => 'Pengaturan Akses Transaksi',
            'menu'      => $menu,
            'user'      => User::where('status','1')->where('type','1')->get(),
            'content'   => 'admin.setting.menu_operation_access'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function operationAccessCreate(Request $request){
        $menu = $request->id;
        $val = $request->val;
        $user = $request->ps;
        $type = $request->tp;

        $cekmenu = Menu::find($menu);

        if (!$cekmenu->sub()->exists()) {

            $query = MenuUser::where('menu_id', $menu)->where('user_id', $user)->where('type', $type)->first();

            if ($query) {
                if ($val) {

                } else {
                    $query->delete();
                }
            } else {
                if ($val) {
                    DB::beginTransaction();
                    try {
                        MenuUser::create([
                            'menu_id'       => $menu,
                            'user_id'   => $user,
                            'type'          => $type
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }                    
                }
            }

            $response = [
                'status' => 200,
                'message' => 'Data updated successfully.'
            ];

        }else{
            $response = [
                'status' => 500,
                'message' => 'Data failed to update. This menu is not meant to be.'
            ];
        }

        return response()->json($response);
    }

    public function getPageStatusMaintenance(Request $request){

        $query = Menu::where('url',$request->value)->first();
		
        if($query) {
            if($query->is_maintenance){
                $response = [
                    'status'    => 300,
                    'title'     => 'Halaman sedang dalam perbaikan!',
                    'message'   => 'Mohon maaf, halaman sedang dalam perbaikan, mohon untuk tidak diakses. Terima kasih.'
                ];
            }else{
                $response = [
                    'status'    => 200,
                    'title'     => '',
                    'message'   => ''
                ];
            }
        }else{
            $response = [
                'status'    => 200,
                'title'     => '',
                'message'   => ''
            ];
        }

        return response()->json($response);
    }
}
