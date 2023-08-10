<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BottomPrice;
use Illuminate\Support\Facades\DB;
use App\Imports\ImportBottomPrice;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class BottomPriceController extends Controller
{
    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];

    }
    public function index()
    {
        $data = [
            'title'     => 'Item Bottom Price',
            'content'   => 'admin.master_data.bottom_price',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'item_id',
            'place_id',
            'nominal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = BottomPrice::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = BottomPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('nominal', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->place_id){
                    $query->whereIn('place_id', $request->place_id);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = BottomPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('nominal', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->place_id){
                    $query->whereIn('place_id', $request->place_id);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->user->name,
                    $val->item->name,
                    $val->place->name,
                    number_format($val->nominal,2,',','.'),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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
            'item_id'               => 'required',
            'place_id'              => 'required',
            'nominal'               => 'required'
        ], [
            'item_id.required' 	    => 'Item tidak boleh kosong.',
            'place_id.required'     => 'Plant tidak boleh kosong.',
            'nominal.required'      => 'Nominal tidak boleh kosong',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {

                if($request->temp){
                    $query = BottomPrice::where('code',CustomHelper::decrypt($request->temp))->first();
                    $query->item_id	        = $request->item_id;
                    $query->place_id        = $request->place_id;
                    $query->nominal         = str_replace(',','.',str_replace('.','',$request->nominal));
                    $query->save();
                }else{
                    $query = BottomPrice::create([
                        'code'          => Str::random(30),
                        'user_id'       => session('bo_id'),
                        'item_id'		=> $request->item_id,
                        'place_id'      => $request->place_id,
                        'nominal'       => str_replace(',','.',str_replace('.','',$request->nominal)),
                    ]);
                }
			
                if($query) {

                    activity()
                        ->performedOn(new BottomPrice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit bottom price.');

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

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function show(Request $request){
        $bp = BottomPrice::where('code',CustomHelper::decrypt($request->id))->first();
        $bp['nominal'] = number_format($bp->nominal,2,',','.');
        $bp['item_name'] = $bp->item->code.' - '.$bp->item->name;
        $bp['place_name'] = $bp->place->code.' - '.$bp->place->name;
        				
		return response()->json($bp);
    }

    public function destroy(Request $request){
        $query = BottomPrice::where('code',CustomHelper::decrypt($request->id))->first();
		
        if($query->delete()) {
            activity()
                ->performedOn(new BottomPrice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Bottom Price Data');

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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportBottomPrice, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
            
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
            ];
            return response()->json($response);
        }
    }
}