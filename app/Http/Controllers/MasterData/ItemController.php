<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Models\ItemShading;
use App\Models\Pallet;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Exports\ExportTemplateMasterItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ItemGroup;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use App\Imports\ImportItem;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportItem;
use App\Imports\ImportItemMaster;
use App\Models\ItemUnit;

class ItemController extends Controller
{
    public function index()
    {
        
        $itemWithoutRelations = Item::whereDoesntHave('itemUnit')
        ->first();

        if ($itemWithoutRelations) {
        
            $result = 1;
        } else {
        
            $result = 0;
        }

        $itemWithoutShading = Item::where('is_sales_item', 1)
        ->whereDoesntHave('itemShading')
        ->first();
        if ($itemWithoutShading) {
        
            $result1 = 1;
        } else {
        
            $result1 = 0;
        }

        $data = [
            'title'     => 'Item',
            'content'   => 'admin.master_data.item',
            'group'     => ItemGroup::where('status','1')->get(),
            'unit'      => Unit::where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->get(),
            'pallet'    => Pallet::where('status','1')->get(),
            'itemex'    => $result,
            'itemsh'    => $result1,
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

                if($request->adaUnit == 1){
                    $query->whereDoesntHave('itemUnit');
                }

                if($request->adaShading == 1){
                    $query->where('is_sales_item', true)
                    ->whereDoesntHave('itemShading');
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
				$btnShading = $val->is_sales_item ? '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light '.($val->itemShading()->exists() ? 'green' : 'amber darken-3').' accent-2 white-text btn-small" data-popup="tooltip" title="Shading Item : '.count($val->itemShading).'" onclick="shading(' . $val->id . ',`'.$val->name.'`)"><i class="material-icons dp48">devices_other</i></button>' : '';
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->name,
                    $val->itemGroup->name,
                    $val->uomUnit->code,
                    $val->status(),
                    $btnShading.
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
            'code'			    => $request->temp ? ['required', Rule::unique('items', 'code')->ignore($request->temp), 'uppercase'] : 'required|unique:items,code|uppercase',
            'name'              => 'required|uppercase',
            'item_group_id'     => 'required',
            'uom_unit'          => 'required',
            'arr_unit'          => 'required',
            'arr_conversion'    => 'required',  
            'tolerance_gr'      => 'required',
            'min_stock'         => 'required',
            'max_stock'         => 'required',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah dipakai',
            'code.uppercase'            => 'Kode harus huruf capital',
            'name.required'             => 'Nama tidak boleh kosong',
            'name.uppercase'            => 'Nama harus huruf capital',
            'item_group_id.required'    => 'Grup item tidak boleh kosong.',
            'uom_unit.required'         => 'Satuan stok & produksi tidak boleh kosong.',
            'arr_unit.required'         => 'Satuan konversi tidak boleh kosong.',
            'arr_conversion.required'   => 'Nilai konversi tidak boleh kosong.',
            'tolerance_gr.required'     => 'Toleransi penerimaan barang tidak boleh kosong.',
            'min_stock.required'        => 'Nilai minimal stock tidak boleh kosong.',
            'max_stock.required'        => 'Nilai maksimal stock tidak boleh kosong.',
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
                    $query->tolerance_gr        = str_replace(',','.',str_replace('.','',$request->tolerance_gr));
                    $query->is_inventory_item   = $request->is_inventory_item ? $request->is_inventory_item : NULL;
                    $query->is_sales_item       = $request->is_sales_item ? $request->is_sales_item : NULL;
                    $query->is_purchase_item    = $request->is_purchase_item ? $request->is_purchase_item : NULL;
                    $query->is_service          = $request->is_service ? $request->is_service : NULL;
                    $query->note                = $request->note;
                    $query->min_stock           = str_replace(',','.',str_replace('.','',$request->min_stock));
                    $query->max_stock           = str_replace(',','.',str_replace('.','',$request->max_stock));
                    $query->status              = $request->status ? $request->status : '2';
                    $query->type_id             = $request->type_id ? $request->type_id : NULL;
                    $query->size_id             = $request->size_id ? $request->size_id : NULL;
                    $query->variety_id          = $request->variety_id ? $request->variety_id : NULL;
                    $query->pattern_id          = $request->pattern_id ? $request->pattern_id : NULL;
                    $query->color_id            = $request->color_id ? $request->color_id : NULL;
                    $query->grade_id            = $request->grade_id ? $request->grade_id : NULL;
                    $query->brand_id            = $request->brand_id ? $request->brand_id : NULL;
                    $query->save();

                    if($request->arr_unit){
                        $query->itemUnit()->whereNotIn('unit_id',$request->arr_unit)->delete();
                    }

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
                        'tolerance_gr'      => str_replace(',','.',str_replace('.','',$request->tolerance_gr)),
                        'is_inventory_item' => $request->is_inventory_item ? $request->is_inventory_item : NULL,
                        'is_sales_item'     => $request->is_sales_item ? $request->is_sales_item : NULL,
                        'is_purchase_item'  => $request->is_purchase_item ? $request->is_purchase_item : NULL,
                        'is_service'        => $request->is_service ? $request->is_service : NULL,
                        'note'              => $request->note,
                        'min_stock'         => str_replace(',','.',str_replace('.','',$request->min_stock)),
                        'max_stock'         => str_replace(',','.',str_replace('.','',$request->max_stock)),
                        'status'            => $request->status ? $request->status : '2',
                        'type_id'           => $request->type_id ? $request->type_id : NULL,
                        'size_id'           => $request->size_id ? $request->size_id : NULL,
                        'variety_id'        => $request->variety_id ? $request->variety_id : NULL,
                        'pattern_id'        => $request->pattern_id ? $request->pattern_id : NULL,
                        'color_id'          => $request->color_id ? $request->color_id : NULL,
                        'grade_id'          => $request->grade_id ? $request->grade_id : NULL,
                        'brand_id'          => $request->brand_id ? $request->brand_id : NULL,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                if($request->arr_unit){
                    foreach($request->arr_unit as $key => $row){
                        $cek = ItemUnit::where('item_id',$query->id)->where('unit_id',intval($row))->count();
                        if($cek == 0){
                            ItemUnit::create([
                                'item_id'       => $query->id,
                                'unit_id'       => $row,
                                'conversion'    => str_replace(',','.',str_replace('.','',$request->arr_conversion[$key])),
                                'is_sell_unit'  => $request->arr_sell_unit[$key] ? $request->arr_sell_unit[$key] : NULL,
                                'is_buy_unit'   => $request->arr_buy_unit[$key] ? $request->arr_buy_unit[$key] : NULL,
                                'is_default'    => $request->arr_default[$key] ? $request->arr_default[$key] : NULL,
                            ]);
                        }else{
                            $itemUnit = ItemUnit::where('item_id',$query->id)->where('unit_id',intval($row))->first();
                            if($itemUnit){
                                $itemUnit->update([
                                    'conversion'    => str_replace(',','.',str_replace('.','',$request->arr_conversion[$key])),
                                    'is_sell_unit'  => $request->arr_sell_unit[$key] ? $request->arr_sell_unit[$key] : NULL,
                                    'is_buy_unit'   => $request->arr_buy_unit[$key] ? $request->arr_buy_unit[$key] : NULL,
                                    'is_default'    => $request->arr_default[$key] ? $request->arr_default[$key] : NULL,
                                ]);
                            }
                        }
                    }
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

    public function createShading(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'tempShading'       => 'required',
                'shading_code'      => 'required',
            ], [
                'tempShading.required'      => 'Id item tidak boleh kosong.',
                'shading_code.required'     => 'Kode shading tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                
                $item = Item::find(intval($request->tempShading));

                if($item){
                    $query = ItemShading::create([
                        'item_id'   => $request->tempShading,
                        'code'      => $request->shading_code,
                    ]);
                }

                if($query) {
                    activity()
                        ->performedOn(new ItemShading())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit item shading data.');

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

            DB::commit();
            
            return response()->json($response);

        }catch(\Exception $e){
            DB::rollback();
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = Item::where('code',CustomHelper::decrypt($request->id))->first();

        $units = '';

        foreach($data->itemUnit as $key => $row){
            $units .= '<tr>';
            $units .= '<td>'.($key + 1).'</td>';
            $units .= '<td>'.$row->unit->name.'</td>';
            $units .= '<td class="right-align">'.number_format($row->conversion,3,',','.').'</td>';
            $units .= '<td class="center-align">'.$data->uomUnit->name.'</td>';
            $units .= '<td>'.($row->is_sell_unit ? 'Ya' : 'Tidak').'</td>';
            $units .= '<td>'.($row->is_buy_unit ? 'Ya' : 'Tidak').'</td>';
            $units .= '<td class="center-align">'.($row->is_default ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10008;</span>').'</td>';
            $units .= '</tr>';
        }

        $string = '<table style="min-width:50%;max-width:50%;">
                        <thead>
                            <tr>
                                <th>
                                    List Satuan Konversi
                                </th>
                                <th>
                                    <table class="bordered" style="min-width:100%;max-width:100%;">
                                        <tr>
                                            <th>No</th>
                                            <th>Satuan</th>
                                            <th>Konversi</th>
                                            <th>Stock</th>
                                            <th>Jual</th>
                                            <th>Beli</th>
                                            <th>Default</th>
                                        </tr>
                                        '.$units.'
                                    </table>
                                </th>
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
                            <tr>
                                <th>Keterangan</th>
                                <th>'.$data->note.'</th>
                            </tr>
                            <tr>
                                <th>Toleransi Penerimaan Barang Lebih (%)</th>
                                <th>'.number_format($data->tolerance_gr,2,',','.').'</th>
                            </tr>
                            <tr>
                                <th>Minimal Stock</th>
                                <th>'.number_format($data->min_stock,2,',','.').' '.$data->uomUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Maksimal Stock</th>
                                <th>'.number_format($data->max_stock,2,',','.').' '.$data->uomUnit->code.'</th>
                            </tr>
                            <tr>
                                <th>Shading</th>
                                <th>'.($data->listShading()).'</th>
                            </tr>
                            <tr>
                                <th>Tipe</th>
                                <th>'.($data->type()->exists() ? $data->type->code.'-'.$data->type->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Ukuran</th>
                                <th>'.($data->size()->exists() ? $data->size->code.'-'.$data->size->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Jenis</th>
                                <th>'.($data->variety()->exists() ? $data->variety->code.'-'.$data->variety->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Motif</th>
                                <th>'.($data->pattern()->exists() ? $data->pattern->code.'-'.$data->pattern->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Warna</th>
                                <th>'.($data->color()->exists() ? $data->color->code.'-'.$data->color->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Grade</th>
                                <th>'.($data->grade()->exists() ? $data->grade->code.'-'.$data->grade->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Brand</th>
                                <th>'.($data->brand()->exists() ? $data->brand->code.'-'.$data->brand->name : '-').'</th>
                            </tr>
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $item = Item::find($request->id);
        $item['uom_unit_id'] = $item->uomUnit->id;
        $item['uom_code'] = $item->uomUnit->code;
        $item['tolerance_gr'] = number_format($item->tolerance_gr,2,',','.');
        $item['min_stock'] = number_format($item->min_stock,3,',','.');
        $item['max_stock'] = number_format($item->max_stock,3,',','.');
        $item['type_name'] = $item->type()->exists() ? $item->type->code.' - '.$item->type->name : '';
        $item['type_code'] = $item->type()->exists() ? $item->type->code : '';
        $item['type_name_real'] = $item->type()->exists() ? $item->type->name : '';
        $item['size_name'] = $item->size()->exists() ? $item->size->code.' - '.$item->size->name : '';
        $item['size_code'] = $item->size()->exists() ? $item->size->code : '';
        $item['size_name_real'] = $item->size()->exists() ? $item->size->name : '';
        $item['variety_name'] = $item->variety()->exists() ? $item->variety->code.' - '.$item->variety->name : '';
        $item['variety_code'] = $item->variety()->exists() ? $item->variety->code : '';
        $item['variety_name_real'] = $item->variety()->exists() ? $item->variety->name : '';
        $item['pattern_name'] = $item->pattern()->exists() ? $item->pattern->code.' - '.$item->pattern->name : '';
        $item['pattern_code'] = $item->pattern()->exists() ? $item->pattern->code : '';
        $item['pattern_name_real'] = $item->pattern()->exists() ? $item->pattern->name : '';
        $item['color_name'] = $item->color()->exists() ? $item->color->code.' - '.$item->color->name : '';
        $item['color_code'] = $item->color()->exists() ? $item->color->code : '';
        $item['color_name_real'] = $item->color()->exists() ? $item->color->name : '';
        $item['grade_name'] = $item->grade()->exists() ? $item->grade->code.' - '.$item->grade->name : '';
        $item['grade_code'] = $item->grade()->exists() ? $item->grade->code : '';
        $item['grade_name_real'] = $item->grade()->exists() ? $item->grade->name : '';
        $item['brand_name'] = $item->brand()->exists() ? $item->brand->code.' - '.$item->brand->name : '';
        $item['brand_code'] = $item->brand()->exists() ? $item->brand->code : '';
        $item['brand_name_real'] = $item->brand()->exists() ? $item->brand->name : '';
        $item['used'] = $item->hasChildDocument() ? '1' : '';
        
        $units = [];
        foreach($item->itemUnit as $row){
            $units[] = [
                'unit_id'       => $row->unit_id,
                'conversion'    => number_format($row->conversion,2,',','.'),
                'is_sell_unit'  => $row->is_sell_unit ? $row->is_sell_unit : '',
                'is_buy_unit'   => $row->is_buy_unit ? $row->is_buy_unit : '',
                'is_default'    => $row->is_default ? $row->is_default : '', 
            ];
        }
        $item['units'] = $units;

		return response()->json($item);
    }

    public function showShading(Request $request){
        $item = Item::find($request->id);
        
        $shadings = [];

        foreach($item->itemShading as $row){
            $shadings[] = [
                'id'        => $row->id,
                'item_id'   => $row->item_id,
                'code'      => $row->code,
            ];
        }

        $item['shadings'] = $shadings;
        				
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

    public function destroyShading(Request $request){
        $query = ItemShading::find($request->id);
		
        $passedDelete = true;

        foreach($query->itemStock as $row){
            if($row->qty > 0){
                $passedDelete = false;
            }
        }

        if(!$passedDelete){
            return response()->json([
                'status'  => 500,
                'message' => 'Item shading masih memiliki stok di gudang. Anda tidak bisa menghapusnya.'
            ]);
        }

        if($query->delete()) {
            activity()
                ->performedOn(new ItemShading())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the item shading data');

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
            $pr=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr[]= Item::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master Item',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.item', $data)->setPaper('a5', 'landscape');
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();


            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function printBarcode(Request $request){

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
            $pr=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr[]= Item::where('code',$row)->first();
            }
            $data = [
                'title'     => 'Master Item',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.item_barcode', $data);
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();

            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
		
		return response()->json($response);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
		
		return Excel::download(new ExportItem($search,$status,$type), 'item_'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportItem, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'    => 500,
                'error'     => $e->getMessage()
            ];
            return response()->json($response);
        }
    }

    public function importMaster(Request $request)
    {
        Excel::import(new ImportItemMaster,$request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterItem(), 'format_master_item'.uniqid().'.xlsx');
    }
    
}
