<?php

namespace App\Http\Controllers\MasterData;
use App\Exports\ExportAssetGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ResourceGroup;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ResourceGroupController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Grup Resource',
            'content'   => 'admin.master_data.resource_group',
            'parent'    => ResourceGroup::where('status','1')->get(),
            'coa'       => Coa::where('status', '1')->where('level',5)->oldest('code')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'other_name',
            'parent_id',
            'coa_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ResourceGroup::count();
        
        $query_data = ResourceGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('other_name', 'like', "%$search%");
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

        $total_filtered = ResourceGroup::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('other_name', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->other_name,
                    $val->parentSub()->exists() ? $val->parentSub->name : 'is Parent',
                    $val->coa()->exists() ? $val->coa->name : '-',
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
            'code'			        => $request->temp ? ['required', Rule::unique('resource_groups', 'code')->ignore($request->temp)] : 'required|unique:resource_groups,code',
            'name'                  => 'required',
            'coa_id'                => $request->parent_id ? 'required' : '',
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah dipakai',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'coa_id.required'               => 'Coa resource tidak boleh kosong.',
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
                    $query = ResourceGroup::find($request->temp);
                    $query->code                = $request->code;
                    $query->name                = $request->name;
                    $query->other_name          = $request->other_name;
                    $query->parent_id           = $request->parent_id ? $request->parent_id : NULL;
                    $query->coa_id              = $request->coa_id;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ResourceGroup::create([
                        'code'                  => $request->code,
                        'name'			        => $request->name,
                        'other_name'			=> $request->other_name,
                        'parent_id'             => $request->parent_id ? $request->parent_id : NULL,
                        'coa_id'                => $request->coa_id,
                        'status'                => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                $newdata = [];

                $newdata[] = '<option value="">Parent (Utama)</option>';

                foreach(ResourceGroup::whereNull('parent_id')->get() as $m){
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

                activity()
                    ->performedOn(new ResourceGroup())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit resource group.');

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
        $rg = ResourceGroup::find($request->id);
        $rg['coa_name'] = $rg->coa()->exists() ? $rg->coa->name : '';
        				
		return response()->json($rg);
    }

    public function destroy(Request $request){
        $query = ResourceGroup::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new ResourceGroup())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the resource group data');

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
                $pr[]= ResourceGroup::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master Grup Resource',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.resource_group', $data)->setPaper('a5', 'landscape');
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
		
		return Excel::download(new ExportAssetGroup($search,$status), 'asset_group_'.uniqid().'.xlsx');
    }
}