<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\BomDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Exports\ExportTemplateMasterBom;
use Illuminate\Support\Facades\Validator;
use App\Models\Bom;
use App\Models\Place;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportBom;
use App\Imports\BomsImport;
use App\Models\BomAlternative;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Warehouse;
use Illuminate\Support\Str;
class BomController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Bill of Material',
            'content'   => 'admin.master_data.bom',
            'place'     => Place::where('status','1')->get(),
            'warehouse' => Warehouse::where('status','1')->get(),
            'line'      => Line::where('status','1')->get(),
            'machine'   => Machine::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'item_id',
            'item_reject_id',
            'place_id',
            'warehouse_id',
            'qty_output',
            'is_powder',
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
                    $val->itemReject()->exists() ? $val->itemReject->name : '-',
                    $val->place->code,
                    $val->warehouse->name,
                    CustomHelper::formatConditionalQty($val->qty_output).' Satuan '.$val->item->uomUnit->code,
                    $val->isPowder(),
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
            'place_id'                  => 'required',
            'warehouse_id'              => 'required',
            'arr_type'                  => 'required|array',
            'arr_detail'                => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_description'           => 'required|array',
            'arr_nominal'               => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_alternative'           => 'required|array',
            'arr_issue_method'          => 'required|array',
        ], [
            'code.required'                 => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai',
            'item_id.required'              => 'Item tidak boleh kosong.',
            'name.required'                 => 'Nama resep tidak boleh kosong.',
            'qty_output.required'           => 'Jumlah output produksi tidak boleh kosong',
            'place_id.required'             => 'Plant tidak boleh kosong',
            'warehouse_id.required'         => 'Gudang tidak boleh kosong',
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
            'arr_alternative.required'      => 'Alternatif tidak boleh kosong',
            'arr_alternative.array'         => 'Alternatif haruslah dalam bentuk array',
            'arr_issue_method.required'     => 'Issue method tidak boleh kosong',
            'arr_issue_method.array'        => 'Issue method haruslah dalam bentuk array',
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
                    $query->item_reject_id      = $request->item_reject_id;
                    $query->place_id            = $request->place_id;
                    $query->warehouse_id        = $request->warehouse_id;
                    $query->qty_output          = str_replace(',','.',str_replace('.','',$request->qty_output));
                    $query->status              = $request->status ? $request->status : '2';
                    $query->is_powder           = $request->is_powder ?? NULL;
                    $query->save();

                    $query->bomDetail()->delete();
                    $query->bomAlternative()->delete();

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
                        'item_reject_id'    => $request->item_reject_id,
                        'place_id'          => $request->place_id,
                        'warehouse_id'      => $request->warehouse_id,
                        'qty_output'        => str_replace(',','.',str_replace('.','',$request->qty_output)),
                        'status'            => $request->status ? $request->status : '2',
                        'is_powder'         => $request->is_powder ?? NULL,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               
                $temp = '';
                foreach($request->arr_main_alternative as $key => $row){
                    $queryA = BomAlternative::create([
                        'code'          => Str::random(15),
                        'bom_id'        => $query->id,
                        'name'          => $request->arr_alternative_name[$key] ?? '',
                        'is_default'    => $request->arr_alternative_default[$key] ?? NULL,
                    ]);
                    foreach($request->arr_type as $keydetail => $rowdetail){
                        if($request->arr_alternative[$keydetail] == $row){
                            BomDetail::create([
                                'bom_id'                => $query->id,
                                'bom_alternative_id'    => $queryA->id,
                                'lookable_type'         => $rowdetail,
                                'lookable_id'           => $request->arr_detail[$keydetail],
                                'cost_distribution_id'  => $request->arr_cost_distribution[$keydetail] ?? NULL,
                                'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$keydetail])),
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_nominal[$keydetail])),
                                'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$keydetail])),
                                'description'           => $request->arr_description[$keydetail],
                                'issue_method'          => $request->arr_issue_method[$keydetail],
                            ]);
                        }
                    }
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

    public function import(Request $request)
    {
        Excel::import(new BomsImport, $request->file('file'));

        return response()->json([
            'status'    => 200,
            'message'   => 'Import sukses!'
        ]);
    }

    public function getImportExcel(){
        return Excel::download(new ExportTemplateMasterBom(), 'format_master_bom'.uniqid().'.xlsx');
    }

    public function rowDetail(Request $request)
    {
        $data   = Bom::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4">';

        foreach($data->bomAlternative as $rowalt){
            $string .= '<div class="col s12">
                        <h5>'.$rowalt->name.($rowalt->is_default ? '(*)' : '').'</h5>
                        <table class="bordered" style="min-width:100%;max-width:100%;">
                            <thead>
                                <tr>
                                    <th colspan="10" class="center">MATERIAL</th>
                                </tr>
                                <tr>
                                    <th class="center">No</th>
                                    <th class="center">Tipe</th>
                                    <th class="center">Item/Resource</th>
                                    <th class="center">Deskripsi</th>
                                    <th class="center">Qty</th>
                                    <th class="center">Satuan</th>
                                    <th class="center">Nominal</th>
                                    <th class="center">Total</th>
                                    <th class="center">Dist.Biaya</th>
                                    <th class="center">Issue Method</th>
                                </tr>
                            </thead>
                            <tbody>';

            foreach($rowalt->bomDetail as $key => $m){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$m->type().'</td>
                    <td>'.$m->lookable->code.' - '.$m->lookable->name.'</td>
                    <td>'.$m->description.'</td>
                    <td class="right-align">'.CustomHelper::formatConditionalQty($m->qty).'</td>
                    <td class="center-align">'.$m->lookable->uomUnit->code.'</td>
                    <td class="right-align">'.number_format($m->nominal,2,',','.').'</td>
                    <td class="right-align">'.number_format($m->total,2,',','.').'</td>
                    <td>'.($m->costDistribution()->exists() ? $m->costDistribution->code.' - '.$m->costDistribution->name : '').'</td>
                    <td class="center-align">'.$m->issueMethod().'</td>
                </tr>';
            }

            $string .= '</tbody></table></div>';
        }
        
        $string .= '</div>';

        return response()->json($string);
    }

    public function show(Request $request){
        $bom = Bom::find($request->id);
        $bom['item_name'] = $bom->item->name;
        $bom['item_reject_id'] = $bom->itemReject()->exists() ? $bom->item_reject_id : '';
        $bom['item_reject_name'] = $bom->itemReject()->exists() ? $bom->itemReject->code.' - '.$bom->itemReject->name : '';
        $bom['qty_output'] = CustomHelper::formatConditionalQty($bom->qty_output);

        $details = [];

        foreach($bom->bomAlternative as $row){
            $arr = [];
            foreach($row->bomDetail as $m){
                $arr[] = [
                    'lookable_type'             => $m->lookable_type,
                    'lookable_id'               => $m->lookable_id,
                    'detail_text'               => $m->lookable->code.' - '.$m->lookable->name,    
                    'qty'                       => CustomHelper::formatConditionalQty($m->qty),
                    'uom_unit'                  => $m->lookable->uomUnit->code,
                    'nominal'                   => number_format($m->nominal,2,',','.'),
                    'total'                     => number_format($m->total,2,',','.'),
                    'description'               => $m->description ?? '',
                    'cost_distribution_id'      => $m->cost_distribution_id ?? '',
                    'cost_distribution_name'    => $m->costDistribution()->exists() ? $m->costDistribution->code.' - '.$m->costDistribution->name : '',
                    'issue_method'              => $m->issue_method ?? '',
                ];
            }
            $row['code'] = strtoupper(Str::random(10));
            $row['details'] = $arr;
            $details[] = $row;
        }

        $bom['details'] = $details;
        				
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
		
		return Excel::download(new ExportBom($search,$status), 'bom_'.uniqid().'.xlsx');
    }
}
