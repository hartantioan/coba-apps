<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\SampleTestInput;
use App\Models\SampleTestQcResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SampleTestQcResultController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();

        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Hasil Uji Qc Lab',
            'place'         => Place::where('status','1')->get(),
            'content'       => 'admin.purchase.sample_test_result_qc',
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
            'sample_test_input_id',
            'user_id',
            'wet_whiteness_value',
            'dry_whiteness_value',
            'document',
            'note',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = SampleTestQcResult::count();

        $query_data = SampleTestQcResult::where(function($query) use ($search, $request) {
                if($search) {
                    $query->orWhereHas('sampleTestInput',function($query) use ($search, $request){
                        $query->where('code','like',"%$search%")
                        ->orWhere('note','like',"%$search%")
                        ->orWhere('supplier','like',"%$search%")
                        ->orWhere('supplier_name','like',"%$search%")
                        ->orWhereHas('city',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('province',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('subdistrict',function($query) use ($search, $request){
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

        $total_filtered = SampleTestQcResult::where(function($query) use ($search, $request) {
                if($search) {
                    $query->orWhereHas('sampleTestInput',function($query) use ($search, $request){
                        $query->where('code','like',"%$search%")
                        ->orWhere('note','like',"%$search%")
                        ->orWhere('supplier','like',"%$search%")
                        ->orWhere('supplier_name','like',"%$search%")
                        ->orWhereHas('city',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('province',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('subdistrict',function($query) use ($search, $request){
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
                    $nomor,
                    $val->sampleTestInput->code,
                    $val->user->name,
                    $val->sampleTestInput->supplier,
                    $val->wet_whiteness_value,
                    $val->dry_whiteness_value,
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->note,
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
            'wet_whiteness_value'                      => 'required',
            'dry_whiteness_value'              => 'required',

        ];

        if ($request->has('with_test') && $request->with_test == '1') {
            $rules['wet_whiteness_value'] = 'required';
            $rules['dry_whiteness_value'] = 'required';
        }

        $validation = Validator::make($request->all(), $rules,  [
            'supplier.required'     => 'Supplier tidak boleh kosong.',

            'wet_whiteness_value.required' => 'Wet Whiteness Value tidak boleh kosong.',
            'dry_whiteness_value.required' => 'Dry Whiteness Value tidak boleh kosong.',

        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();

                    $query = SampleTestQcResult::find($request->temp);

                    if($request->has('file')) {
                        if($query->document){
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                        }
                        $document = $request->file('file')->store('public/sample_test_input');
                    } else {
                        $document = $query->document;
                    }

                    $query->user_id = session('bo_id');
                    $query->sample_test_input_id = $request->sample_test_input_id;
                    $query->wet_whiteness_value = str_replace(',','.',str_replace('.','',$request->wet_whiteness_value));
                    $query->dry_whiteness_value = str_replace(',','.',str_replace('.','',$request->dry_whiteness_value));
                    $query->document = $document;
                    $query->note = $request->note;
                    $query->save();
                    $sample_test = SampleTestInput::find($request->sample_test_input_id);
                    $sample_test->update([
                        'status'=>2
                    ]);
                    DB::commit();

			}else{
                DB::beginTransaction();
                try {
                    $query = SampleTestQcResult::create([
                        'user_id'                   => session('bo_id'),
                        'sample_test_input_id'      => $request->sample_test_input_id,
                        'wet_whiteness_value'       => str_replace(',','.',str_replace('.','',$request->wet_whiteness_value)),
                        'dry_whiteness_value'       => str_replace(',','.',str_replace('.','',$request->dry_whiteness_value)),

                        'document'                  => $request->file('file') ? $request->file('file')->store('public/sample_test_input') : NULL,
                        'note'                      => $request->note,
                    ]);

                    $sample_test = SampleTestInput::find($request->sample_test_input_id);
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
                    ->performedOn(new SampleTestQcResult())
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
        $unit = SampleTestQcResult::find($request->id);

        $unit['sample_test_input_code'] = $unit->sampleTestInput->code;
        $unit['company_sample_code'] = $unit->sampleTestInput->company_sample_code;
        $unit['sample_type'] = $unit->sampleTestInput->sampleType->name;
		return response()->json($unit);
    }


    // public function exportFromTransactionPage(Request $request){
    //     $post_date = $request->start_date? $request->start_date : '';
    //     $end_date = $request->end_date ? $request->end_date : '';
    //     $status = $request->status ? $request->status : '';
    //     $search = $request->search ? $request->search : '';
	// 	return Excel::download(new ExportFromTransactionPageSampleTestQcResult($post_date,$end_date,$status,$search), 'sample_test_input'.uniqid().'.xlsx');
    // }

    public function destroy(Request $request){
        $query = SampleTestQcResult::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new SampleTestQcResult())
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

        return response()->json($response);
    }
}
