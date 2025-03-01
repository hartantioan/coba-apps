<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportFromTransactionPageSampleTestInputPicNote;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use Illuminate\Validation\Rule;
use App\Helpers\CustomHelper;
use App\Models\SampleTestInput;
use App\Models\SampleTestInputPICNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SpecialNotePICSampleController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();

        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Catatan PIC',
            'place'         => Place::where('status','1')->get(),
            'content'       => 'admin.purchase.sample_test_input_pic_note',
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
            'sample_test_input_id',
            'status',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = SampleTestInputPICNote::count();

        $query_data = SampleTestInputPICNote::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhereHas('sampleTypeInput',function($query) use ($search, $request){
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

        $total_filtered = SampleTestInputPICNote::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->orWhereHas('sampleTypeInput',function($query) use ($search, $request){
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
                    $val->note,
                    $val->document ? $val->attachment() : 'file tidak ditemukan',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->sampleTestInput->id . ')"><i class="material-icons dp48">create</i></button>
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
            'sample_test_input_id'              => 'required',
            'note'              => 'required',

        ];

        $validation = Validator::make($request->all(), $rules,  [
            'sample_test_input_id.required'     => 'Supplier tidak boleh kosong.',
            'note.required' => 'Jenis sampel tidak boleh kosong.',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            // $lastSegment = $request->lastsegment;
            // $menu = Menu::where('url', $lastSegment)->first();
            // $newCode=SampleTestInput::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

			if($request->temp){
                DB::beginTransaction();

                    $query = SampleTestInputPICNote::find($request->temp);
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
                    $query->sample_test_input_id = $request->sample_test_input_id;
                    $query->note = $request->note_pic;
                    $query->document = $document;
                    $query->status = '1';
                    $query->save();
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
                    $query = SampleTestInputPICNote::create([

                        'user_id'             => session('bo_id'),
                        'sample_test_input_id'=> $request->sample_test_input_id,
                        'note'                => $request->note_pic,
                        'document'            => $fileUpload ? $fileUpload : NULL,
                        'status'              => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new SampleTestInputPICNote())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Catatan kusus.');

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

    public function exportFromTransactionPage(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $search = $request->search ? $request->search : '';
		return Excel::download(new ExportFromTransactionPageSampleTestInputPicNote($post_date,$end_date,$status,$search), 'sample_test_input'.uniqid().'.xlsx');
    }

    public function show(Request $request){
        $unit = SampleTestInput::find($request->id);
        $unit['temp'] = $unit->sampleTestInputPICNote->id;
        $unit['sample_test_input_code'] = $unit->sampleTestInputPICNote->sampleTestInput->code;
        $unit['note_pic'] = $unit->sampleTestInputPICNote->note;

        $unit['sample_test_input_id'] = $unit->sampleTestInputPICNote->sampleTestInput->id;

        $unit['sample_type_name'] = $unit->sampleType->name;
        $unit['province_name'] =$unit->province->name ;
        $unit['city_name'] = $unit->city->name;
        $unit['subdistrict_name'] = $unit->subdistrict->name??'-';

		return response()->json($unit);
    }

    public function destroy(Request $request){
        $query = SampleTestInputPICNote::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new SampleTestInputPICNote())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Sample Note PIC data');

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
