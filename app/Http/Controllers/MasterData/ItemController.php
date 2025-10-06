<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
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
use App\Models\Type;
use App\Models\Size;
use App\Models\Variety;
use App\Models\Pattern;
use App\Models\Grade;
use App\Models\Brand;
use App\Models\ItemGroup;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use App\Imports\ImportItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MitraApiSyncData;

use App\Exports\ExportItem;
use App\Imports\ImportItemMaster;
use App\Models\BomCalculator;
use App\Models\ItemBuffer;
use App\Models\ItemConversion;
use App\Models\ItemQcParameter;
use App\Models\ItemStockNew;
use App\Models\ItemUnit;
use App\Models\StoreItemStock;
use App\Models\User;

class ItemController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
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

        $itemGroup = ItemGroup::where('status',1)->get();

        $data = [
            'title'     => 'Item',
            'content'   => 'admin.master_data.item',
            'group'     => $itemGroup,
            'unit'      => Unit::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'warehouse' => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
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

                if($request->group){
                    $query->where(function($query) use ($request){
                        $query->whereIn('item_group_id', $request->group);
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
                if($request->group){
                    $query->where(function($query) use ($request){
                        $query->whereIn('item_group_id', $request->group);
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
                    $nomor,
                    $val->code,
                    $val->name??'',
                    $val->itemGroup->name??'',
                    $val->uomUnit->code??'',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat blue accent-2 white-text btn-small" data-popup="tooltip" title="Cetak Barcode" onclick="barcode(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">style</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-text btn-small" data-popup="tooltip" title="Document Relasi" onclick="documentRelation(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">device_hub</i></button>
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
            'code'			    => $request->temp ? ['required', Rule::unique('items', 'code')->ignore($request->temp)] : 'required|unique:items,code|uppercase',
            'name'              => 'required|uppercase',
            'uom_unit'          => 'required',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah dipakai',
            'code.uppercase'            => 'Kode harus huruf capital',
            'name.required'             => 'Nama tidak boleh kosong',
            'name.uppercase'            => 'Nama harus huruf capital',
            'uom_unit.required'         => 'Satuan stok & produksi tidak boleh kosong.',
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
                    /* if($query->hasChildDocument()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Pengeditan item tidak dapat dilakukan. Masih terdapat dokumen yang terkait dengan item tersebut.'
                        ]);
                    } */
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->item_group_id       = $request->item_group_id;
                    $query->uom_unit            = $request->uom_unit;
                    $query->is_inventory_item   = $request->is_inventory_item ? $request->is_inventory_item : NULL;
                    $query->is_sales_item       = $request->is_sales_item ? $request->is_sales_item : NULL;
                    $query->is_purchase_item    = $request->is_purchase_item ? $request->is_purchase_item : NULL;
                    $query->is_service          = $request->is_service ? $request->is_service : NULL;
                    $query->note                = $request->note;
                    $query->supplier_id                = $request->supplier_id ?? null;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    if($request->arr_item_conversion){
                        $query->childrenConversion()->delete();
                    }

                    DB::commit();
                }catch(\Exception $e){
                    Log::error($e);
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
                        'is_inventory_item' => $request->is_inventory_item ? $request->is_inventory_item : NULL,
                        'is_sales_item'     => $request->is_sales_item ? $request->is_sales_item : NULL,
                        'is_purchase_item'  => $request->is_purchase_item ? $request->is_purchase_item : NULL,
                        'is_service'        => $request->is_service ? $request->is_service : NULL,
                        'note'              => $request->note,
                        'status'            => $request->status ? $request->status : '2',

                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    Log::error($e);
                    DB::rollback();
                }
			}

			if($query) {

                $place = Place::where('status','1')->get();

                if(!$request->temp){
                    ItemStock::create([
                        'item_id'       => $query->id,
                        'qty'           => 0
                    ]);
                    $itemstocknew=ItemStockNew::create([
                        'item_id'       => $query->id,
                        'qty'           => 0
                    ]);
                }


                if($request->arr_item_conversion){
                    foreach($request->arr_item_conversion as $key => $row){
                        ItemConversion::create([
                            'item_id'       => $query->id,
                            'qty_conversion'=> str_replace(',', '.', str_replace('.', '', $request->arr_qty[$key])),
                            'item_child_id' => $row,
                        ]);
                        if(!$request->temp){
                            StoreItemStock::create([
                                'item_id'       => $row,
                                'qty'           => 0,
                                'item_stock_new_id' => $itemstocknew->id ?? null,
                            ]);
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
            $units .= '<td class="right-align">'.CustomHelper::formatConditionalQty($row->conversion).'</td>';
            $units .= '<td class="center-align">'.$data->uomUnit->name.'</td>';
            $units .= '<td>'.($row->is_sell_unit ? 'Ya' : 'Tidak').'</td>';
            $units .= '<td>'.($row->is_buy_unit ? 'Ya' : 'Tidak').'</td>';
            $units .= '<td class="center-align">'.($row->is_default ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10008;</span>').'</td>';
            $units .= '</tr>';
        }

        $buffers = '';

        foreach($data->itemBuffer as $key => $row){
            $buffers .= '<tr>';
            $buffers .= '<td>'.($key + 1).'</td>';
            $buffers .= '<td>'.$row->place->code.'</td>';
            $buffers .= '<td class="right-align">'.number_format($row->min_stock,2,',','.').'</td>';
            $buffers .= '<td class="right-align">'.number_format($row->max_stock,2,',','.').'</td>';
            $buffers .= '</tr>';
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
                                <th>
                                    Buffer Stock
                                </th>
                                <th>
                                    <table class="bordered" style="min-width:100%;max-width:100%;">
                                        <tr>
                                            <th>No</th>
                                            <th>Plant</th>
                                            <th>Min.Stock</th>
                                            <th>Max.Stock</th>
                                        </tr>
                                        '.$buffers.'
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
                                <th>Item Produksi</th>
                                <th>'.($data->is_production ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Pengecekan QC</th>
                                <th>'.($data->is_quality_check ? '&#10003;' : '&#10005;').'</th>
                            </tr>

                            <tr>
                                <th>Qty Timbang?</th>
                                <th>'.($data->qty_good_scale ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Item Top Secret</th>
                                <th>'.($data->is_hide_supplier ? '&#10003;' : '&#10005;').'</th>
                            </tr>
                            <tr>
                                <th>Item Reject</th>
                                <th>'.($data->is_reject ? '&#10003;' : '&#10005;').'</th>
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
                                <th>Grade</th>
                                <th>'.($data->grade()->exists() ? $data->grade->code.'-'.$data->grade->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Brand</th>
                                <th>'.($data->brand()->exists() ? $data->brand->code.'-'.$data->brand->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Bom Calculator</th>
                                <th>'.($data->bomCalculator()->exists() ? $data->bomCalculator->name.'-'.$data->bomCalculator->note : '-').'</th>
                            </tr>
                        </thead>
                    </table>';

        return response()->json($string);
    }

    public function show(Request $request){
        $item = Item::find($request->id);
        $item['uom_unit_id'] = $item->uomUnit->id;
        $item['uom_code'] = $item->uomUnit->code;
        $item['supplier_name'] = $item->supplier?->name ?? null;
        $item['supplier_code'] = $item->supplier?->code ?? null;

        $units = [];
        if($item->childrenConversion()->exists()){
            foreach($item->childrenConversion as $row){
                $units[] = [
                    'item_id'             => $row->item_id,
                    'qty_conversion'      => $row->qty_conversion ?? 0,
                    'item_child_id'       => $row->item_child_id,
                    'item_child_name'     => $row->child->name,
                ];
            }
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

        if(!$query->hasChildDocument()){
            if($query->delete()) {
                $query->itemStock()->delete();
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
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data tidak bisa dihapus karena telah digunakan pada form transaksi.'
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


            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$document_po
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

            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }

		return response()->json($response);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
        $group = $request->group ? $request->group : '';

		return Excel::download(new ExportItem($search,$status,$type,$group), 'item_'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        Excel::import(new ImportItemMaster, $request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
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

    public function documentRelation(Request $request)
    {
        $data   = Item::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<div class="row pt-1 pb-1"> <div class="col s12">'.$data->code.'-'.$data->name.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6"> Stock Item Saat Ini : '.$data->getStockAll().'</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">User</th>
                                <th class="center-align">Qty Dokumen</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Status Dokumen</th>
                            </tr>
                        </thead><tbody>';
        $no = 1;
        if($data->deliveryReceiveDetail()->exists()){

            foreach($data->deliveryReceiveDetail as $row_dr_d){
                if($row_dr_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_dr_d->deliveryReceive->code.'</td>
                        <td class="center-align">'.$row_dr_d->deliveryReceive->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_dr_d->qty).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_dr_d->deliveryReceive->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }
        if($data->itemPartitionDetails()->exists()){
            foreach($data->itemPartitionDetails as $row_ipar_d){
                if($row_ipar_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->code.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_ipar_d->qty).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }
        if($data->itemPartitionDetailscome()->exists()){
            foreach($data->itemPartitionDetailscome as $row_ipar_d){
                if($row_ipar_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->code.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_ipar_d->qty_partition).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_ipar_d->itemPartition->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }

        if($data->inventoryIssueDetail()->exists()){
            foreach($data->inventoryIssueDetail as $row_ii_d){
                if($row_ii_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->code.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_ii_d->qty).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }

        if($data->inventoryIssueDetailcome()->exists()){
            foreach($data->inventoryIssueDetailcome as $row_iv_d){
                if($row_ii_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->code.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_ii_d->qty_store_item).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_ii_d->inventoryIssue->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }

        if($data->invoiceDetail()->exists()){
            foreach($data->invoiceDetail as $row_iv_d){
                if($row_iv_d->deleted_at == null){
                    $string .= '<tr>
                        <td class="center-align">'.$no.'</td>
                        <td class="center-align">'.$row_iv_d->invoice->code.'</td>
                        <td class="center-align">'.$row_iv_d->invoice->user->name.'</td>
                        <td class="center-align">'.CustomHelper::formatConditionalQty($row_iv_d->qty).'</td>
                        <td class="center-align">'.$data->uomUnit->code.'</td>
                        <td class="center-align">'.$row_iv_d->invoice->status().'</td>
                    </tr>';
                    $no++;
                }
            }
        }


        $string .= '</tbody></table></div>';

        return response()->json($string);
    }

}
