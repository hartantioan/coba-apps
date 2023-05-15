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
use App\Models\Bom;
use App\Models\BomMaterial;
use App\Models\BomCost;
use App\Models\Company;
use App\Models\Item;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportBom;

class BomController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Bill of Material',
            'content'   => 'admin.master_data.bom',
            'place'     => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'item_id',
            'place_id',
            'qty_output',
            'qty_planned',
            'type',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Bom::count();
        
        $query_data = Bom::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Bom::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
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
                    $val->item->name,
                    $val->place->name,
                    number_format($val->qty_output,3,',','.').' Satuan '.$val->item->uomUnit->code,
                    number_format($val->qty_planned,3,',','.').' Satuan '.$val->item->uomUnit->code,
                    $val->type(),
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
            'code'              => $request->temp ? ['required', Rule::unique('boms', 'code')->ignore($request->temp)] : 'required|unique:boms,code',
            'item_id'                   => 'required',
            'name'                      => 'required',
            'qty_output'                => 'required',
            'qty_planned'               => 'required',
            'type'                      => 'required',
            'place_id'                  => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_description_cost'      => 'required|array',
            'arr_nominal'               => 'required|array'
        ], [
            'code.required'                 => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai',
            'item_id.required'              => 'Item tidak boleh kosong.',
            'name.required'                 => 'Nama resep tidak boleh kosong.',
            'qty_output.required'           => 'Jumlah output produksi tidak boleh kosong',
            'qty_planned.required'          => 'Jumlah rata-rata produksi tidak boleh kosong',
            'type.required'                 => 'Tipe bill of material tidak boleh kosong',
            'place_id.required'             => 'Site tidak boleh kosong',
            'arr_item.required'             => 'Material tidak boleh kosong',
            'arr_item.array'                => 'Material haruslah dalam bentuk array',
            'arr_qty.required'              => 'Jumlah material tidak boleh kosong',
            'arr_qty.array'                 => 'Jumlah material haruslah dalam bentuk array',
            'arr_description_cost.required' => 'Deskripsi biaya tidak boleh kosong',
            'arr_description_cost.array'    => 'Deskripsi biaya haruslah dalam bentuk array',
            'arr_nominal.required'          => 'Nominal biaya tidak boleh kosong',
            'arr_nominal.array'             => 'Nominal biaya haruslah dalam bentuk array',
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
                    $query = Bom::find($request->temp);
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->user_id             = session('bo_id');
                    $query->item_id             = $request->item_id;
                    $query->place_id            = $request->place_id;
                    $query->qty_output          = str_replace(',','.',str_replace('.','',$request->qty_output));
                    $query->qty_planned         = str_replace(',','.',str_replace('.','',$request->qty_planned));
                    $query->type                = $request->type;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    $query->bomCost()->delete();
                    $query->bomMaterial()->delete();

                    foreach($request->arr_item as $key => $row){
                        BomMaterial::create([
                            'bom_id'        => $query->id,
                            'item_id'       => $row,
                            'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'description'   => $request->arr_description_material[$key]
                        ]);
                    }
    
                    foreach($request->arr_description_cost as $key => $row){
                        BomCost::create([
                            'bom_id'        => $query->id,
                            'coa_id'        => isset($request->arr_coa[$key]) ? $request->arr_coa[$key] : NULL,
                            'description'   => $request->arr_description_cost[$key],
                            'nominal'       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key]))
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Bom::create([
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'user_id'           => session('bo_id'),
                        'item_id'           => $request->item_id,
                        'place_id'          => $request->place_id,
                        'qty_output'        => str_replace(',','.',str_replace('.','',$request->qty_output)),
                        'qty_planned'       => str_replace(',','.',str_replace('.','',$request->qty_planned)),
                        'type'              => $request->type,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    foreach($request->arr_item as $key => $row){
                        BomMaterial::create([
                            'bom_id'        => $query->id,
                            'item_id'       => $row,
                            'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'description'   => $request->arr_description_material[$key]
                        ]);
                    }
    
                    foreach($request->arr_description_cost as $key => $row){
                        BomCost::create([
                            'bom_id'        => $query->id,
                            'coa_id'        => $request->arr_coa[$key],
                            'description'   => $request->arr_description_cost[$key],
                            'nominal'       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key]))
                        ]);
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Bom())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit bom data.');

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
        $data   = Bom::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4">';

        $string .= '<div class="col s6">
                        <table class="bordered">
                            <thead>
                                <tr>
                                    <th colspan="5" class="center">MATERIAL</th>
                                </tr>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Item</th>
                                    <th class="center">Qty</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach($data->bomMaterial as $key => $m){
            $string .= '<tr>
                <td class="center">'.($key + 1).'</td>
                <td>'.$m->item->name.'</td>
                <td class="right">'.number_format($m->qty,3,',','.').'</td>
                <td class="center">'.$m->item->uomUnit->code.'</td>
                <td>'.$m->description.'</td>
            </tr>';
        }

        $string .= '</tbody>
                        </table>
                    </div>';

        $string .= '<div class="col s6">
                    <table class="bordered">
                        <thead>
                            <tr>
                                <th colspan="4" class="center">BIAYA</th>
                            </tr>
                            <tr>
                                <th class="center">No</th>
                                <th class="center">Description</th>
                                <th class="center">Coa</th>
                                <th class="center">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>';

    foreach($data->bomCost as $key => $c){
        $string .= '<tr>
            <td class="center">'.($key + 1).'</td>
            <td>'.$c->description.'</td>
            <td>'.($c->coa()->exists() ? $c->coa->name : '-').'</td>
            <td class="right">'.number_format($c->nominal,3,',','.').'</td>
        </tr>';
    }

    $string .= '</tbody>
                    </table>
                </div>';

        $string .= '</div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $bom = Bom::find($request->id);
        $bom['item_name'] = $bom->item->name;
        $bom['qty_output'] = number_format($bom->qty_output,3,',','.');
        $bom['qty_planned'] = number_format($bom->qty_planned,3,',','.');

        $arrMaterial = [];
        $arrCost = [];

        foreach($bom->bomMaterial as $m){
            $arrMaterial[] = [
                'item_id'       => $m->item_id,
                'item_name'     => $m->item->name,
                'qty'           => number_format($m->qty,3,',','.'),
                'uom_unit'      => $m->item->uomUnit->code,
                'description'   => $m->description
            ];
        }

        foreach($bom->bomCost as $c){
            $arrCost[] = [
                'coa_id'        => $c->coa_id ? $c->coa_id : '',
                'coa_name'      => $c->coa()->exists() ? $c->coa->name : '',
                'description'   => $c->description,
                'nominal'       => number_format($c->nominal,3,',','.'),
            ];
        }

        $bom['material'] = $arrMaterial;
        $bom['cost'] = $arrCost;
        				
		return response()->json($bom);
    }

    public function destroy(Request $request){
        $query = Bom::find($request->id);
		
        if($query->delete()) {
            $query->bomCost()->delete();
            $query->bomMaterial()->delete();

            activity()
                ->performedOn(new Bom())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the bill of material data');

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
            'title' => 'BILL OF MATERIAL REPORT',
            'data' => Bom::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function ($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('name', 'like', "%$request->search%")
                            ->orWhereHas('item',function($query) use($request){
                                $query->where('name','like',"%$request->search%");
                            })->orWhereHas('place',function($query) use($request){
                                $query->where('name','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })->get()
		];
		
		return view('admin.print.master_data.bom', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $type = $request->type ? $request->type : '';
		
		return Excel::download(new ExportBom($search,$status,$type), 'bom_'.uniqid().'.xlsx');
    }
}
