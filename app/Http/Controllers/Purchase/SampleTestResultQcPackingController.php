<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\SampleTestInput;
use App\Models\SampleTestQcPackingResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SampleTestResultQcPackingController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();

        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Hasil Uji Qc Packing',
            'place'         => Place::where('status','1')->get(),
            'content'       => 'admin.purchase.sample_test_result_qc_packing',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),

        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'sample_type_id',
            'province_id',
            'city_id',
            'subdistrict_id',
            'village_name',
            'sample_date',
            'supplier',
            'supplier_name',
            'supplier_phone',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = SampleTestInput::where('type','3')->count();

        $query_data = SampleTestInput::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('sampleType',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('city',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('province',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('subdistrict',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })
                    ->orWhere('note','like',"%$search%")
                    ->orWhere('supplier_name','like',"%$search%");

                });
            }
            $query->where('type','3');
            if($request->status){
                $query->where('status', $request->status);
            }
        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();

        $total_filtered =  SampleTestInput::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('sampleType',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('city',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('province',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })->orWhereHas('subdistrict',function($query) use ($search, $request){
                        $query->where('name','like',"%$search%");
                    })
                    ->orWhere('note','like',"%$search%")
                    ->orWhere('supplier_name','like',"%$search%");

                });
            }
            $query->where('type','3');
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
                    $nomor,
                    $val->code,
                    $val->company_sample_code,
                    $val->sampleTestResultQcPacking?->user->name ?? '-',
                    $val->sampleTestResultQcPacking?->document ? $val->sampleTestResultQcPacking->attachment()  : 'file tidak ditemukan',
                    $val->sampleTestResultQcPacking?->note?? '-',
                    $val->sampleTestResultQcPacking?->decision() ?? '-',
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
        $rules =[
            'note'                             => 'required',

        ];

        $validation = Validator::make($request->all(), $rules,  [
            'supplier.required'     => 'Supplier tidak boleh kosong.',


        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp_test){
                DB::beginTransaction();

                    $query = SampleTestQcPackingResult::find($request->temp_test);

                    if($request->has('file')) {

                        if($query->document){
                            $arrFile = explode(',',$query->document);
                            foreach($arrFile as $row){
                                if(Storage::exists($row)){
                                    Storage::delete($row);
                                }
                            }
                        }

                        $arrFile = [];

                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/sample_test_input');
                        }

                        $document = implode(',',$arrFile);
                    } else {
                        $document = $query->document;
                    }

                    $query->user_id = session('bo_id');
                    $query->sample_test_input_id = $request->temp;
                    $query->dry_whiteness_value = str_replace(',','.',str_replace('.','',$request->dry_whiteness_value));
                    $query->document = $document;
                    $query->decision = $request->decision;
                    $query->note = $request->note;
                    $query->save();
                    $sample_test = SampleTestInput::find($request->temp);
                    $sample_test->update([
                        'status'=>2
                    ]);
                    DB::commit();

			}else{
                DB::beginTransaction();
                try {
                    $fileUpload = '';

                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/purchase_orders');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }
                    $query = SampleTestQcPackingResult::create([
                        'user_id'                   => session('bo_id'),
                        'sample_test_input_id'      => $request->temp,
                        'dry_whiteness_value'       => str_replace(',','.',str_replace('.','',$request->dry_whiteness_value)),

                        'decision'                  => $request->decision,
                        'document'                  => $fileUpload ? $fileUpload : NULL,
                        'note'                      => $request->note,
                    ]);

                    $sample_test = SampleTestInput::find($request->temp);
                    $sample_test->update([
                        'status'=>2
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new SampleTestQcPackingResult())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit hasil uji.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
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
        $unit = SampleTestInput::find($request->id);
        $unit['sample_test_input_code'] = $unit->code;
        $unit['company_sample_code'] = $unit->company_sample_code;

        $decision = '';
        $note = '';
        $id = '';
        if($unit->sampleTestResultQcPacking()->exists()){
            $decision = $unit->sampleTestResultQcPacking->decision;
            $note = $unit->sampleTestResultQcPacking->note;
            $id = $unit->sampleTestResultQcPacking->id;
        }
        $unit['id_test'] = $id;
        $unit['decision'] = $decision;
        $unit['note'] = $note;

		return response()->json($unit);
    }


    // public function exportFromTransactionPage(Request $request){
    //     $post_date = $request->start_date? $request->start_date : '';
    //     $end_date = $request->end_date ? $request->end_date : '';
    //     $status = $request->status ? $request->status : '';
    //     $search = $request->search ? $request->search : '';
	// 	return Excel::download(new ExportFromTransactionPageSampleTestQcPackingResult($post_date,$end_date,$status,$search), 'sample_test_input'.uniqid().'.xlsx');
    // }

    public function destroy(Request $request){
        $query = SampleTestInput::find($request->id);
        if($query->sampleTestResultQcPacking()->exists()){
            if($query->sampleTestResultQcPacking->delete()) {
                $query->update([
                    'status'=>1
                ]);
                activity()
                    ->performedOn(new SampleTestQcPackingResult())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Delete the Sample Result data');

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
                'message' => 'Data belum memiliki data hasil uji.'
            ];
        }


        return response()->json($response);
    }
}
