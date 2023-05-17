<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\BenchmarkPrice;
use Illuminate\Support\Facades\DB;

class BenchmarkPriceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Harga Benchmark',
            'content'   => 'admin.master_data.benchmark_price',
            'place'     => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'item_id',
            'place_id',
            'price',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = BenchmarkPrice::count();
        
        $query_data = BenchmarkPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('item',function($query) use ($search, $request) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        })
                        ->orWhereHas('place',function($query) use ($search, $request) {
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

        $total_filtered = BenchmarkPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('item',function($query) use ($search, $request) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        })
                        ->orWhereHas('place',function($query) use ($search, $request) {
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
                    $nomor,
                    $val->user->name,
                    $val->item->code.' - '.$val->item->name,
                    $val->place->code.' - '.$val->place->name,
                    number_format($val->price,2,',','.'),
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
            'item_id'               => 'required',
            'place_id'              => 'required',
            'price'                 => 'required',
        ], [
            'item_id.required'      => 'Item tidak boleh kosong.',
            'place_id.required'     => 'Site tidak boleh kosong.',
            'price.required'        => 'Harga tidak boleh kosong.',
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
                    $query = BenchmarkPrice::find($request->temp);
                    $query->user_id         = session('bo_id');
                    $query->item_id	        = $request->item_id;
                    $query->place_id        = $request->place_id;
                    $query->price           = str_replace(',','.',str_replace('.','',$request->price));
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = BenchmarkPrice::create([
                        'user_id'           => session('bo_id'),
                        'item_id'			=> $request->item_id,
                        'place_id'          => $request->place_id,
                        'price'             => str_replace(',','.',str_replace('.','',$request->price)),
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new BenchmarkPrice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit benchmark price data.');

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
        $bp = BenchmarkPrice::find($request->id);
        $bp['price'] = number_format($bp->price,2,',','.');
        $bp['item_name'] = $bp->item->code.' - '.$bp->item->name;
        				
		return response()->json($bp);
    }

    public function destroy(Request $request){
        $query = BenchmarkPrice::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new BenchmarkPrice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the benchmark price data');

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
