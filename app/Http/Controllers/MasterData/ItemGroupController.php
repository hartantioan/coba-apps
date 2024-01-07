<?php

namespace App\Http\Controllers\MasterData;
use App\Exports\ExportItemGroup;
use App\Models\ItemGroupWarehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\ItemGroup;
use App\Models\Coa;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ItemGroupController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Grup Item',
            'content'   => 'admin.master_data.item_group',
            'parent'    => ItemGroup::where('status','1')->get(),
            /* 'coa'       => Coa::where('status', '1')->oldest('code')->get() */
            'coa'       => Coa::where('status', '1')->where('level',5)->oldest('code')->get(),
            'warehouse' => Warehouse::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'parent_id',
            'coa_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ItemGroup::count();
        
        $query_data = ItemGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = ItemGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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
                    $val->code,
                    $val->name,
                    $val->parentSub()->exists() ? $val->parentSub->name : 'is Parent',
                    $val->coa->name,
                    $val->listWarehouse(),
                    $val->productionType(),
                    $val->isActiva(),
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
            'code'			=> $request->temp ? ['required', Rule::unique('item_groups', 'code')->ignore($request->temp)] : 'required|unique:item_groups,code',
            'name'          => 'required',
            'coa_id'        => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah dipakai',
            'name.required'         => 'Nama tidak boleh kosong.',
            'coa_id.required'       => 'Coa tidak boleh kosong.'
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
                    $query = ItemGroup::find($request->temp);
                    $query->code            = $request->code;
                    $query->name            = $request->name;
                    $query->parent_id       = $request->parent_id ? $request->parent_id : NULL;
                    $query->coa_id          = $request->coa_id;
                    $query->production_type = $request->production_type ? $request->production_type : NULL;
                    $query->is_activa       = $request->is_activa ? $request->is_activa : NULL;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();

                    $query->itemGroupWarehouse()->delete();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ItemGroup::create([
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'parent_id'         => $request->parent_id ? $request->parent_id : NULL,
                        'coa_id'            => $request->coa_id,
                        'production_type'   => $request->production_type ? $request->production_type : NULL,
                        'is_activa'         => $request->is_activa ? $request->is_activa : NULL,
                        'status'            => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                $newdata = [];

                $newdata[] = '<option value="">Parent (Utama)</option>';

                foreach(ItemGroup::whereNull('parent_id')->get() as $m){
                    $newdata[] = '<option value="'.$m->id.'">'.$m->name.'</option>';
                    foreach($m->childSub as $m2){
                        $newdata[] = '<option value="'.$m2->id.'"> - '.$m2->name.'</option>';
                        foreach($m2->childSub as $m3){
                            $newdata[] = '<option value="'.$m3->id.'"> - - '.$m3->name.'</option>';
                            foreach($m3->childSub as $m4){
                                $newdata[] = '<option value="'.$m4->id.'"> - - - '.$m4->name.'</option>';
                            }
                        }
                    }
                }

                if($request->warehouse_id){
                    foreach($request->warehouse_id as $row){
                        ItemGroupWarehouse::create([
                            'item_group_id' => $query->id,
                            'warehouse_id'  => intval($row)
                        ]);
                    }
                }

                activity()
                    ->performedOn(new ItemGroup())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit item group.');

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
        $itemgroup = ItemGroup::find($request->id);
        $arrWarehouse = [];

        foreach($itemgroup->itemGroupWarehouse as $row){
            $arrWarehouse[] = $row->warehouse_id;
        }

        $itemgroup['warehouses'] = $arrWarehouse;
        				
		return response()->json($itemgroup);
    }

    public function destroy(Request $request){
        $query = ItemGroup::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new ItemGroup())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the item group data');

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
                $pr[]= ItemGroup::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master Item Group',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.item_group', $data)->setPaper('a5', 'landscape');
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
		
		return Excel::download(new ExportItemGroup($search,$status), 'item_group_'.uniqid().'.xlsx');
    }
}