<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\ResetCogsHelper;
use App\Models\ApprovalTemplateMenu;
use App\Models\Journal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use App\Jobs\ResetCogs;
use App\Jobs\ResetCogsNew;
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
use App\Models\FundRequest;
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
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use App\Models\MenuUser;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\ProductionBarcodeDetail;
use App\Models\ProductionBatch;
use App\Models\ProductionFgReceive;
use App\Models\ProductionHandover;
use App\Models\ProductionIssue;
use App\Models\ProductionReceive;
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

        /* $itemcogs = ItemCogs::where('lookable_type','good_issues')->get();

        $temp = 0;
        $temp2 = 0;
        $index = 0;
        foreach($itemcogs as $row){
            $data = NULL;
            $data = GoodIssueDetail::where('good_issue_id',$row->lookable_id)->whereHas('itemStock',function($query)use($row){
                $query->where('item_id',$row->item_id);
            })->get();
            if($temp == $row->item_id && $temp2 == $row->lookable_id){
                $index++;
                $row->update([
                    'detailable_type'   => $data[$index]->getTable(),
                    'detailable_id'     => $data[$index]->id,
                ]);
            }else{
                $index = 0;
                $row->update([
                    'detailable_type'   => $data[$index]->getTable(),
                    'detailable_id'     => $data[$index]->id,
                ]);
            }
            $temp2 = $row->lookable_id;
            $temp = $row->item_id;
        } */

        /* $item = Item::find(5030);
        $startdate = '2024-08-16';

        foreach($item as $row){
            ResetCogsHelper::gas($startdate,1,1,$item->id,NULL,NULL,NULL);
        } */

        /* $data = [
            'title'     => 'Menu',
            'menu'      => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'content'   => 'admin.setting.menu'
        ];

        return view('admin.layouts.index', ['data' => $data]); */

        /* $data = MarketingOrderDeliveryProcess::whereHas('marketingOrderInvoice')->get();

        foreach($data as $row){
            $row->update([
                'status'    => '3'
            ]);
        } */

        /* $dataissue = ProductionIssue::whereIn('status',['2','3'])->where('post_date','>=','2024-09-23')->get();

        $datareceive = ProductionReceive::whereIn('status',['2','3'])->where('post_date','>=','2024-09-23')->get();
        
        $datafgreceive = ProductionFgReceive::whereIn('status',['2','3'])->where('post_date','>=','2024-09-23')->get();

        $datahandover = ProductionHandover::whereIn('status',['2','3'])->where('post_date','>=','2024-09-23')->get(); */

        /* $data = ProductionBatch::whereNotNull('lookable_type')->whereIn('lookable_type',['production_handover_details'])->where('post_date','>=','2024-09-23')->get();

        foreach($data as $batch){
            ResetCogsNew::dispatch($batch->post_date,1,$batch->place_id,$batch->item_id,$batch->area_id,$batch->item_shading_id,$batch->id);
        } */

        /* $data = Item::where('item_group_id',2)->get();

        foreach($data as $item){
            ResetCogsNew::dispatch('2024-09-03',1,1,$item->id,NULL,NULL,NULL);
        } */

        /* $data = Item::whereHas('itemGroup',function($query){
            $query->where('parent_id',3);
        })->get();

        foreach($data as $item){
            ResetCogsNew::dispatch('2024-09-03',1,1,$item->id,NULL,NULL,NULL);
        } */

        /* $data = ProductionBatch::whereNotNull('lookable_type')->where('post_date','>=','2024-09-03')->whereHas('item',function($query){
            $query->where('item_group_id',46);
        })->get();

        foreach($data as $batch){
            ResetCogsNew::dispatch($batch->post_date,1,$batch->place_id,$batch->item_id,$batch->area_id,$batch->item_shading_id,$batch->id);
        } */

        /* $data = ProductionBatch::whereNotNull('lookable_type')->where('post_date','>=','2024-09-03')->whereHas('item',function($query){
            $query->where('item_group_id',47);
        })->get();

        foreach($data as $batch){
            ResetCogsNew::dispatch($batch->post_date,1,$batch->place_id,$batch->item_id,$batch->area_id,$batch->item_shading_id,$batch->id);
        } */

        /* $data = ProductionBatch::whereNotNull('lookable_type')->where('post_date','>=','2024-09-03')->whereHas('item',function($query){
            $query->where('item_group_id',48);
        })->get();

        foreach($data as $batch){
            ResetCogsNew::dispatch($batch->post_date,1,$batch->place_id,$batch->item_id,$batch->area_id,$batch->item_shading_id,$batch->id);
        } */

        /* $data = Item::where('item_group_id',49)->get();

        foreach($data as $item){
            ResetCogsNew::dispatch('2024-09-03',1,1,$item->id,NULL,NULL,NULL);
        } */

        $data = ProductionBatch::whereNotNull('lookable_type')->where('post_date','>=','2024-09-03')->whereHas('item',function($query){
            $query->where('item_group_id',7);
        })->get();

        foreach($data as $batch){
            ResetCogsNew::dispatch($batch->post_date,1,$batch->place_id,$batch->item_id,$batch->area_id,$batch->item_shading_id,$batch->id);
        }

        /* ResetCogsNew::dispatch('2024-09-30',1,1,4385,NULL,NULL,NULL); */

        /* $data = ProductionBatch::whereNotNull('lookable_type')->get();

        foreach($data as $row){
            $row->update([
                'post_date' => $row->lookable->parent->post_date,
            ]);
        } */

        /* CustomHelper::accumulateCogs('2024-09-20',1,1,5388); */
        
        /* $data = ProductionBarcodeDetail::whereHas('productionBarcode',function($query){
            $query->whereIn('status',['2','3']);
        })->get();

        foreach($data as $row){
            $cekBatch = ProductionBatch::where('code',$row->pallet_no)->first();
            if(!$cekBatch){
                ProductionBatch::create([
                    'code'          => $row->pallet_no,
                    'item_id'       => $row->item_id,
                    'place_id'      => $row->productionBarcode->place_id,
                    'warehouse_id'  => $row->item->warehouse(),
                    'qty'           => $row->qty,
                    'qty_real'      => $row->qty,
                    'total'         => 0,
                ]);
            }
        } */

        /* $purchase = PurchaseOrder::whereIn('code',['PORD-24P1-00001412','PORD-24P1-00001411','PORD-24P1-00001409','PORD-24P1-00001408','PORD-24P1-00001407','PORD-24P1-00001573','PORD-24P1-00001579','PORD-24P1-00001596','PORD-24P1-00001602','PORD-24P1-00001627'])->whereIn('status',['2','3'])->get();
        $total = 0;
        foreach($purchase as $row){
            foreach($row->purchaseOrderDetail as $rowpod){
                foreach($rowpod->goodReceiptDetail as $rowgrd){
                    echo $row->code.' - '.$rowgrd->goodReceipt->code.' - '.number_format($rowgrd->total * $row->currency_rate,2,',','.').' - Nominal menjadi invoice '.number_format($rowgrd->totalInvoice(),2,',','.').'<br>';
                    $total += round($rowgrd->total * $row->currency_rate,2);
                }
            }
        }
        echo '<h1>'.number_format($total,2,',','.').'</h1>'; */
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
    

    public function saveOrderMenu(Request $request){
        function processNode($node, $parentId = null, $order = 0) {
            $id = $node['id'];
            
            // info("Processing node: ID = $id, Parent ID = $parentId, Order = $order\n");
            $query = Menu::find($id);
            $query->parent_id = $parentId;
            $query->order = $order;

            $query->save();
            // Perform any operation such as inserting into the database
            // Node::create(['id' => $id, 'parent_id' => $parentId, 'order' => $order]);
        
            if (isset($node['children']) && is_array($node['children'])) {
                foreach ($node['children'] as $index => $child) {
                    processNode($child, $id, $index);
                }
            }
        }
        
        $data = $request->data; 

        foreach ($data as $index => $row) {
            processNode($row, null, $index);
        }
        
        
		$response = [
            'status'    => 200,
            'title'     => '',
            'message'   => ''
        ];
        
        return response()->json($response);
    }
}
