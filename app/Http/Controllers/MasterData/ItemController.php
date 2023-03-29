<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Models\ItemWarehouse;
use App\Models\Unit;
use App\Models\ItemGroup;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportItem;

class ItemController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Item',
            'content'   => 'admin.master_data.item',
            'group'     => ItemGroup::where('status','1')->get(),
            'unit'      => Unit::where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'item_group_id',
            'uom_unit',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Item::count();
        
        $query_data = Item::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where(function($query) use ($request){
                        foreach($request->type as $row){
                            if($row == '1'){
                                $query->OrWhereNotNull('is_inventory_item');
                            }
                            if($row == '2'){
                                $query->OrWhereNotNull('is_sales_item');
                            }
                            if($row == '3'){
                                $query->OrWhereNotNull('is_purchase_item');
                            }
                            if($row == '4'){
                                $query->OrWhereNotNull('is_service');
                            }
                        }
                    });
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Item::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }
                
                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where(function($query) use ($request){
                        foreach($request->type as $row){
                            if($row == '1'){
                                $query->OrWhereNotNull('is_inventory_item');
                            }
                            if($row == '2'){
                                $query->OrWhereNotNull('is_sales_item');
                            }
                            if($row == '3'){
                                $query->OrWhereNotNull('is_purchase_item');
                            }
                            if($row == '4'){
                                $query->OrWhereNotNull('is_service');
                            }
                        }
                    });
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
                    $val->name,
                    $val->itemGroup->name.' Coa : '.$val->itemGroup->coa->code.' - '.$val->itemGroup->coa->name,
                    $val->uomUnit->code,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
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
            'code'			    => $request->temp ? ['required', Rule::unique('items', 'code')->ignore($request->temp)] : 'required|unique:items,code',
            'name'              => 'required',
            'item_group_id'     => 'required',
            'uom_unit'          => 'required',
            'buy_unit'          => 'required',
            'buy_convert'       => 'required',
            'sell_unit'         => 'required',
            'sell_convert'      => 'required',
            /* 'warehouse_id'      => 'required', */
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah dipakai',
            'name.required'             => 'Nama tidak boleh kosong.',
            'item_group_id.required'    => 'Grup item tidak boleh kosong.',
            'uom_unit.required'         => 'Satuan stok & produksi tidak boleh kosong.',
            'buy_unit.required'         => 'Satuan beli tidak boleh kosong.',
            'buy_convert.required'      => 'Satuan konversi beli ke stok tidak boleh kosong.',
            'sell_unit.required'        => 'Satuan jual tidak boleh kosong.',
            'sell_convert.required'     => 'Satuan konversi jual ke stok tidak boleh kosong.',
            /* 'warehouse_id.required'     => 'Gudang tidak boleh kosong.', */
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Item::find($request->temp);
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->item_group_id       = $request->item_group_id;
                    $query->uom_unit            = $request->uom_unit;
                    $query->buy_unit            = $request->buy_unit;
                    $query->buy_convert         = str_replace(',','.',str_replace('.','',$request->buy_convert));
                    $query->sell_unit           = $request->sell_unit;
                    $query->sell_convert        = str_replace(',','.',str_replace('.','',$request->sell_convert));
                    $query->is_inventory_item   = $request->is_inventory_item ? $request->is_inventory_item : NULL;
                    $query->is_sales_item       = $request->is_sales_item ? $request->is_sales_item : NULL;
                    $query->is_purchase_item    = $request->is_purchase_item ? $request->is_purchase_item : NULL;
                    $query->is_service          = $request->is_service ? $request->is_service : NULL;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    $query->itemWarehouse()->delete();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Item::create([
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'item_group_id'     => $request->item_group_id,
                        'uom_unit'          => $request->uom_unit,
                        'buy_unit'          => $request->buy_unit,
                        'buy_convert'       => str_replace(',','.',str_replace('.','',$request->buy_convert)),
                        'sell_unit'         => $request->sell_unit,
                        'sell_convert'      => str_replace(',','.',str_replace('.','',$request->sell_convert)),
                        'is_inventory_item' => $request->is_inventory_item ? $request->is_inventory_item : NULL,
                        'is_sales_item'     => $request->is_sales_item ? $request->is_sales_item : NULL,
                        'is_purchase_item'  => $request->is_purchase_item ? $request->is_purchase_item : NULL,
                        'is_service'        => $request->is_service ? $request->is_service : NULL,
                        'status'            => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                foreach($request->warehouse_id as $row){
                    ItemWarehouse::create([
                        'item_id'       => $query->id,
                        'warehouse_id'  => intval($row)
                    ]);
                }

                activity()
                    ->performedOn(new Item())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit item data.');

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
        $data   = Item::find($request->id);

        $string = '<table>
                        <thead>
                            <tr>
                                <th>Satuan Beli</th>
                                <th>'.$data->buyUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Konversi Satuan Beli ke Stok</th>
                                <th>1 '.$data->buyUnit->code.' = '.number_format($data->buy_convert,3,',','.').' '.$data->uomUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Satuan Jual</th>
                                <th>'.$data->sellUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Konversi Satuan Jual ke Stok</th>
                                <th>1 '.$data->sellUnit->code.' = '.number_format($data->sell_convert,3,',','.').' '.$data->uomUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Item Stok</th>
                                <th>'.($data->is_inventory_item ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Item Penjualan</th>
                                <th>'.($data->is_sales_item ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Item Pembelian</th>
                                <th>'.($data->is_purchase_item ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Item Service</th>
                                <th>'.($data->is_service ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Gudang</th>
                                <th>'.$data->warehouses().'</th>
                            </tr>
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $item = Item::find($request->id);
        $item['buy_convert'] = number_format($item->buy_convert,3,',','.');
        $item['sell_convert'] = number_format($item->sell_convert,3,',','.');

        $arrWarehouse = [];

        foreach($item->itemWarehouse as $row){
            $arrWarehouse[] = $row->warehouse_id;
        }

        $item['warehouses'] = $arrWarehouse;
        				
		return response()->json($item);
    }

    public function destroy(Request $request){
        $query = Item::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Item())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the item data');

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
            'title' => 'ITEM REPORT',
            'data' => Item::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where(function ($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('name', 'like', "%$request->search%");
                    });
                    
                }

                if ($request->status) {
                    $query->where('status',$request->status);
                }

                if($request->type){
                    $query->where(function($query) use ($request){
                        foreach($request->type as $row){
                            if($row == '1'){
                                $query->OrWhereNotNull('is_inventory_item');
                            }
                            if($row == '2'){
                                $query->OrWhereNotNull('is_sales_item');
                            }
                            if($row == '3'){
                                $query->OrWhereNotNull('is_purchase_item');
                            }
                            if($row == '4'){
                                $query->OrWhereNotNull('is_service');
                            }
                        }
                    });
                }
            })->get()
		];
		
		return view('admin.print.master_data.item', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
		
		return Excel::download(new ExportItem($search,$status,$type), 'item_'.uniqid().'.xlsx');
    }
}
