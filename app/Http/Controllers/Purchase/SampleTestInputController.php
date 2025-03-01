<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportFromTransactionPageSampleTestInput;
use App\Exports\ExportFromTransactionPageSampleTestInputPicNote;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuUser;
use Illuminate\Validation\Rule;
use App\Models\Place;
use App\Models\SampleTestInput;
use App\Models\UsedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SampleTestInputController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();

        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title' => 'Input Sampel',
            'place'         => Place::where('status','1')->get(),
            'content' => 'admin.purchase.sample_test_input',
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
            'code',
            'user_id',
            'sample_type_id',
            'province_id',
            'city_id',
            'subdistrict_id',
            'village_name',
            'supplier',
            'sample_date',
            'supplier_name',
            'supplier_phone',
            'post_date',
            'link_map',
            'permission_type',
            'permission_name',
            'commodity_permits',
            'permits_period',
            'receiveable_capacity',
            'price_estimation_loco',
            'supplier_sample_code',
            'company_sample_code',
            'document',
            'note',
            'type',
            'price_estimation_franco',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = SampleTestInput::count();

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

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = SampleTestInput::where(function($query) use ($search, $request) {
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
                        ->orWhere('supplier','like',"%$search%")
                        ->orWhere('supplier_name','like',"%$search%");

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
                    $val->code,
                    $val->account?->name ?? '-',
                    $val->sampleType->name,
                    $val->supplier,
                    $val->supplier_name,
                    $val->province->name,
                    $val->city->name,
                    $val->subdistrict?->name ?? '-',
                    $val->supplier_phone,
                    $val->sample_date,
                    $val->status(),
                    $val->link_map ? '<a  target="_blank" href="'.$val->link_map.'"><i class="material-icons dp48" style="font-size: 40px;">location_on</i></a>' : '-' ,
                    $val->permission_type,
                    $val->permission_name,
                    $val->commodity_permits,
                    $val->permits_period,

                    number_format($val->receiveable_capacity,2,',','.'),
                    number_format($val->price_estimation_loco,2,',','.'),
                    number_format($val->price_estimation_franco,2,',','.'),
                    $val->supplier_sample_code,
                    $val->company_sample_code,
                    $val->document ? $val->attachment() : 'file tidak ditemukan',
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
            'supplier'              => 'required',
            'province_id'              => 'required',
            'city_id'              => 'required',
            'account_id'              => 'required',
            'district_id'              => 'required',
            'sample_type_id'              => 'required',
            'sample_date'              => 'required',
            'supplier_name'              => 'required',
            'supplier_phone'              => 'required',
            'permission_type'              => 'required',
            'permission_name'              => 'required',
            'commodity_permits'              => 'required',
            'receiveable_capacity'              => 'required',
            'price_estimation_loco'              => 'required',
            'price_estimation_franco'              => 'required',
            'supplier_sample_code'              => 'required',
            'permits_period'              => 'required',
            'company_sample_code' => $request->company_sample_code
            ? ['required', 'string', Rule::unique('sample_test_inputs', 'company_sample_code')->ignore($request->temp)]
            : ['required', 'string', 'unique:sample_test_inputs,company_sample_code']

        ];

        $validation = Validator::make($request->all(), $rules,  [
            'supplier.required'             => 'Supplier tidak boleh kosong.',
            'province_id.required'          => 'Provinsi tidak boleh kosong.',
            'account_id.required'              => 'PIC SAMPLE tidak boleh kosong',
            'city_id.required'              => 'Kota tidak boleh kosong.',
            'district_id.required'          => 'Kecamatan tidak boleh kosong.',
            'sample_type_id.required'       => 'Jenis sampel tidak boleh kosong.',
            'sample_date.required'          => 'Tanggal sampel tidak boleh kosong.',
            'supplier_name.required'        => 'Nama supplier tidak boleh kosong.',
            'supplier_phone.required'       => 'Nomor telepon supplier tidak boleh kosong.',
            'permission_type.required'      => 'Tipe izin tidak boleh kosong.',
            'permission_name.required'      => 'Nama izin tidak boleh kosong.',
            'commodity_permits.required'    => 'Izin komoditas tidak boleh kosong.',
            'receiveable_capacity.required' => 'Kapasitas yang dapat diterima tidak boleh kosong.',
            'price_estimation_loco.required'=> 'Estimasi harga loco tidak boleh kosong.',
            'price_estimation_franco.required' => 'Estimasi harga franco tidak boleh kosong.',
            'supplier_sample_code.required' => 'Kode sampel supplier tidak boleh kosong.',
            'permits_period.required'       => 'Periode izin tidak boleh kosong.',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $lastSegment = $request->lastsegment;
            $menu = Menu::where('url', $lastSegment)->first();
            $newCode=SampleTestInput::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

			if($request->temp){
                DB::beginTransaction();

                    $query = SampleTestInput::find($request->temp);

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
                    $query->sample_type_id = $request->sample_type_id;
                    $query->province_id = $request->province_id;
                    $query->city_id = $request->city_id;
                    $query->subdistrict_id = $request->district_id;
                    $query->account_id = $request->account_id;
                    $query->village_name = $request->village_name;
                    $query->supplier = $request->supplier;
                    $query->supplier_name = $request->supplier_name;
                    $query->supplier_phone = $request->supplier_phone;
                    $query->sample_date = $request->sample_date;
                    $query->link_map = $request->link_map;
                    $query->permission_type = $request->permission_type;
                    $query->permission_name = $request->permission_name;
                    $query->commodity_permits = $request->commodity_permits;
                    $query->permits_period = $request->permits_period;
                    $query->receiveable_capacity = $request->receiveable_capacity;
                    $query->price_estimation_loco = str_replace(',','.',str_replace('.','',$request->price_estimation_loco));
                    $query->price_estimation_franco = str_replace(',','.',str_replace('.','',$request->price_estimation_franco));
                    $query->type = $request->type;
                    $query->supplier_sample_code = $request->supplier_sample_code;
                    $query->company_sample_code = $request->company_sample_code;
                    $query->document = $document;
                    $query->note = $request->note;
                    $query->save();
                    DB::commit();

			}else{
                DB::beginTransaction();
                    $fileUpload = '';

                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/purchase_orders');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }
                    $query = SampleTestInput::create([
                        'code'                => $newCode,
                        'user_id'             => session('bo_id'),
                        'sample_type_id'      => $request->sample_type_id,
                        'province_id'         => $request->province_id,
                        'account_id'         => $request->account_id,
                        'city_id'             => $request->city_id,
                        'subdistrict_id'      => $request->district_id,
                        'village_name'        => $request->village_name,
                        'supplier'            => $request->supplier,
                        'supplier_name'       => $request->supplier_name,
                        'supplier_phone'      => $request->supplier_phone,
                        'sample_date'         => $request->sample_date,
                        'post_date'           => now(),
                        'link_map'            => $request->link_map,
                        'permission_type'     => $request->permission_type,
                        'permission_name'     => $request->permission_name,
                        'commodity_permits'   => $request->commodity_permits,
                        'permits_period'      => $request->permits_period,
                        'receiveable_capacity'=> $request->receiveable_capacity,
                        'price_estimation_loco'      => str_replace(',','.',str_replace('.','',$request->price_estimation_loco)),
                        'price_estimation_franco'    => str_replace(',','.',str_replace('.','',$request->price_estimation_franco)),
                        'type'                => $request->type,
                        'supplier_sample_code'=> $request->supplier_sample_code,
                        'company_sample_code' => $request->company_sample_code,
                        'document'            => $fileUpload ? $fileUpload : NULL,
                        'note'                => $request->note,
                        'status'              => 1,
                    ]);

                    DB::commit();

			}

			if($query) {

                activity()
                    ->performedOn(new SampleTestInput())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit unit.');

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

        $unit['account_name'] = $unit->account->name ?? '-';
        $unit['sample_type_name'] = $unit->sampleType->name;
        $unit['province_name'] =$unit->province->name ;
        $unit['city_name'] = $unit->city->name;
        $unit['subdistrict_name'] = $unit->subdistrict->name??'-';

		return response()->json($unit);
    }

    public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = SampleTestInput::generateCode($request->val);

		return response()->json($code);
    }

    public function exportFromTransactionPage(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $search = $request->search ? $request->search : '';
		return Excel::download(new ExportFromTransactionPageSampleTestInput($post_date,$end_date,$status,$search), 'sample_test_input'.uniqid().'.xlsx');
    }

    public function destroy(Request $request){
        $query = SampleTestInput::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new SampleTestInput())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Sample type data');

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
