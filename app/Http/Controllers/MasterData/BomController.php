<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\BomDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Bom;
use App\Models\Place;
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
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
            'arr_type'                  => 'required|array',
            'arr_detail'                => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_description'           => 'required|array',
            'arr_nominal'               => 'required|array',
            'arr_total'                 => 'required|array'
        ], [
            'code.required'                 => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai',
            'item_id.required'              => 'Item tidak boleh kosong.',
            'name.required'                 => 'Nama resep tidak boleh kosong.',
            'qty_output.required'           => 'Jumlah output produksi tidak boleh kosong',
            'qty_planned.required'          => 'Jumlah rata-rata produksi tidak boleh kosong',
            'type.required'                 => 'Tipe bill of material tidak boleh kosong',
            'place_id.required'             => 'Plant tidak boleh kosong',
            'arr_type.required'             => 'Tipe tidak boleh kosong',
            'arr_type.array'                => 'Tipe haruslah dalam bentuk array',
            'arr_detail.required'           => 'Detail item/biaya tidak boleh kosong',
            'arr_detail.array'              => 'Detail item/biaya haruslah dalam bentuk array',
            'arr_qty.required'              => 'Jumlah material tidak boleh kosong',
            'arr_qty.array'                 => 'Jumlah material haruslah dalam bentuk array',
            'arr_description.required'      => 'Deskripsi biaya tidak boleh kosong',
            'arr_description.array'         => 'Deskripsi biaya haruslah dalam bentuk array',
            'arr_nominal.required'          => 'Nominal biaya tidak boleh kosong',
            'arr_nominal.array'             => 'Nominal biaya haruslah dalam bentuk array',
            'arr_total.required'            => 'Total tidak boleh kosong',
            'arr_total.array'               => 'Total haruslah dalam bentuk array',
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

                    $query->bomDetail()->delete();

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

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                foreach($request->arr_type as $key => $row){
                    BomDetail::create([
                        'bom_id'        => $query->id,
                        'lookable_type' => $row,
                        'lookable_id'   => $request->arr_detail[$key],
                        'qty'           => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        'nominal'       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                        'total'         => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                        'description'   => $request->arr_description[$key]
                    ]);
                }

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
        $data   = Bom::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4">';

        $string .= '<div class="col s12">
                        <table class="bordered" style="min-width:100%;max-width:100%;">
                            <thead>
                                <tr>
                                    <th colspan="7" class="center">MATERIAL</th>
                                </tr>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Bahan/Biaya</th>
                                    <th class="center">Deskripsi</th>
                                    <th class="center">Qty</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Nominal</th>
                                    <th class="center">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach($data->bomDetail as $key => $m){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$m->lookable->code.' - '.$m->lookable->name.'</td>
                <td>'.$m->description.'</td>
                <td class="right-align">'.number_format($m->qty,3,',','.').'</td>
                <td class="center-align">'.($m->lookable_type == 'items' ? $m->lookable->uomUnit->code : '-').'</td>
                <td class="right-align">'.number_format($m->nominal,2,',','.').'</td>
                <td class="right-align">'.number_format($m->total,2,',','.').'</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';

        return response()->json($string);
    }

    public function show(Request $request){
        $bom = Bom::find($request->id);
        $bom['item_name'] = $bom->item->name;
        $bom['qty_output'] = number_format($bom->qty_output,3,',','.');
        $bom['qty_planned'] = number_format($bom->qty_planned,3,',','.');

        $arr = [];

        foreach($bom->bomDetail as $m){
            $arr[] = [
                'lookable_type' => $m->lookable_type,
                'lookable_id'   => $m->lookable_id,
                'detail_text'   => $m->lookable->code.' - '.$m->lookable->name,    
                'qty'           => number_format($m->qty,3,',','.'),
                'uom_unit'      => $m->lookable_type == 'items' ? $m->lookable->uomUnit->code : '-',
                'nominal'       => number_format($m->nominal,2,',','.'),
                'total'         => number_format($m->total,2,',','.'),
                'description'   => $m->description,
            ];
        }

        $bom['details'] = $arr;
        				
		return response()->json($bom);
    }

    public function destroy(Request $request){
        $query = Bom::find($request->id);
		
        if($query->delete()) {
            $query->bomDetail()->delete();

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
                $pr[]= Bom::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master BOM',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.bom', $data)->setPaper('a5', 'landscape');
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
		
		return Excel::download(new ExportBom($search,$status,$type), 'bom_'.uniqid().'.xlsx');
    }
}
