<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class OutletController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Outlet',
            'content'   => 'admin.master_data.outlet',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'name',
            'type',
            'address',
            'phone',
            'province_id',
            'city_id',
            'district_id',
            'subdistrict_id',
            'link_gmap',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Outlet::count();
        
        $query_data = Outlet::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhereHas('province',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('city',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('district',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('subdistrict',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = Outlet::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhereHas('province',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('city',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('district',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('subdistrict',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
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
                    $val->id,
                    $val->user->name,
                    $val->code,
                    $val->name,
                    $val->type(),
                    $val->address,
                    $val->phone,
                    $val->province->name,
                    $val->city->name,
                    $val->district->name,
                    $val->subdistrict->name,
                    '<a href="'.$val->link_gmap.'" target="_blank"><i class="material-icons dp48" style="font-size: 40px;">location_on</i></a>',
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
            'code' 				=> $request->temp ? ['required', Rule::unique('outlets', 'code')->ignore($request->temp)] : 'required|unique:outlets,code',
            'name'              => 'required',
            'type'              => 'required',
            'address'           => 'required',
            'phone'             => 'required',
            'province_id'       => 'required',
            'city_id'           => 'required',
            'district_id'       => 'required',
            'subdistrict_id'    => 'required',
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique'               => 'Kode telah terpakai.',
            'name.required'             => 'Nama tidak boleh kosong.',
            'type.required'             => 'Tipe tidak boleh kosong.',
            'address.required'          => 'Alamat tidak boleh kosong.',
            'phone.required'            => 'Telepon tidak boleh kosong.',
            'province_id.required'      => 'Provinsi tidak boleh kosong.',
            'city_id.required'          => 'Kota tidak boleh kosong.',
            'district_id.required'      => 'Kecamatan tidak boleh kosong.',
            'subdistrict_id.required'   => 'Kelurahan tidak boleh kosong.',
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
                    $query = Outlet::find($request->temp);
                    $query->user_id         = session('bo_id');
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->type            = $request->type;
                    $query->phone           = $request->phone;
                    $query->address         = $request->address;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->district_id     = $request->district_id;
                    $query->subdistrict_id  = $request->subdistrict_id;
                    $query->link_gmap       = $request->link_gmap;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Outlet::create([
                        'user_id'           => session('bo_id'),
                        'code'              => $request->code,
                        'name'			    => $request->name,
                        'type'              => $request->type,
                        'phone'             => $request->phone,
                        'address'           => $request->address,
                        'province_id'       => $request->province_id,
                        'city_id'           => $request->city_id,
                        'district_id'       => $request->district_id,
                        'subdistrict_id'    => $request->subdistrict_id,
                        'link_gmap'         => $request->link_gmap,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Outlet())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit outlet.');

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
        $unit = Outlet::find($request->id);
        $unit['province_name'] = $unit->province->name;
        $unit['cities'] = $unit->province->getCity();
        				
		return response()->json($unit);
    }

    public function destroy(Request $request){
        $query = Outlet::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Outlet())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the outlet data');

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
