<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
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
            'pallet'    => Pallet::where('status','1')->get(),
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->name,
                    $val->itemGroup->name,
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
            'pallet_unit'       => 'required',
            'pallet_convert'    => 'required',
            'tolerance_gr'      => 'required',
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
            'pallet_unit.required'      => 'Satuan pallet tidak boleh kosong.',
            'pallet_convert.required'   => 'Satuan konversi pallet ke satuan jual tidak boleh kosong.',
            'tolerance_gr.required'     => 'Toleransi penerimaan barang tidak boleh kosong.',
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
                    $query->pallet_unit         = $request->pallet_unit;
                    $query->pallet_convert      = str_replace(',','.',str_replace('.','',$request->pallet_convert));
                    $query->tolerance_gr        = str_replace(',','.',str_replace('.','',$request->tolerance_gr));
                    $query->is_inventory_item   = $request->is_inventory_item ? $request->is_inventory_item : NULL;
                    $query->is_sales_item       = $request->is_sales_item ? $request->is_sales_item : NULL;
                    $query->is_purchase_item    = $request->is_purchase_item ? $request->is_purchase_item : NULL;
                    $query->is_service          = $request->is_service ? $request->is_service : NULL;
                    $query->note                = $request->note;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

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
                        'pallet_unit'       => $request->pallet_unit,
                        'pallet_convert'    => str_replace(',','.',str_replace('.','',$request->pallet_convert)),
                        'tolerance_gr'      => str_replace(',','.',str_replace('.','',$request->tolerance_gr)),
                        'is_inventory_item' => $request->is_inventory_item ? $request->is_inventory_item : NULL,
                        'is_sales_item'     => $request->is_sales_item ? $request->is_sales_item : NULL,
                        'is_purchase_item'  => $request->is_purchase_item ? $request->is_purchase_item : NULL,
                        'is_service'        => $request->is_service ? $request->is_service : NULL,
                        'note'              => $request->note,
                        'status'            => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

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
        $data   = Item::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<table style="min-width:100%;max-width:100%;">
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
                                <th>Konversi Pallet ke Satuan Jual</th>
                                <th>1 '.$data->palletUnit->code.' = '.number_format($data->pallet_convert,3,',','.').' '.$data->sellUnit->code.'</th>
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
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $item = Item::find($request->id);
        $item['buy_convert'] = number_format($item->buy_convert,3,',','.');
        $item['sell_convert'] = number_format($item->sell_convert,3,',','.');
        $item['pallet_convert'] = number_format($item->pallet_convert,3,',','.');
        $item['tolerance_gr'] = number_format($item->tolerance_gr,2,',','.');
        				
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


            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
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

            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
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
    
}
