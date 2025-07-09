<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\StoreItemPriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreItemPriceListController extends Controller
{
    public function index()
    {
        $data = [
            'title'   => 'StoreItemPriceList',
            'content' => 'admin.master_data.store_item_pricelist'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'user_id',
            'no_telp',
            'address',
            'group_id',
            'total',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = StoreItemPriceList::count();

        $query_data = StoreItemPriceList::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('item', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
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

        $total_filtered = StoreItemPriceList::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('item', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%");
                                }
                            );
                    });
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    $nomor,
                    $val->item->name,
                    $val->price,
                    $val->discount,
                    $val->qty_discount,
                    $val->start_date,
                    $val->end_date,
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
            'name' 				=> 'required',
            'address'           => 'required',
        ], [
            'name.required' 	    => 'Nama tidak boleh kosong.',
            'address.required'      => 'Alamat tidak boleh kosong.',
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
                    $query               = StoreItemPriceList::find($request->temp);
                    $query->user_id      = session('bo_id');
                    $query->item_id      = $request->item_id;
                    $query->start_date   = $request->start_date;
                    $query->end_date     = $request->end_date;
                    $query->price        = $request->price;
                    $query->discount     = $request->discount;
                    $query->sell_price   = $request->sell_price;
                    $query->qty_discount = $request->qty_discount;
                    $query->status       = $request->status ?? '2';

                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = StoreItemPriceList::create([
                        'code'         => $request->code,
                        'user_id'      => session('bo_id'),
                        'item_id'      => $request->item_id,
                        'start_date'   => $request->start_date,
                        'end_date'     => $request->end_date,
                        'price'        => $request->price,
                        'discount'     => $request->discount,
                        'qty_discount' => $request->qty_discount,
                        'sell_price'   => $request->sell_price,
                        'status'       => $request->status ?? '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new StoreItemPriceList())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit StoreItemPriceList.');

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
        $StoreItemPriceList = StoreItemPriceList::find($request->id);

		return response()->json($StoreItemPriceList);
    }

    public function destroy(Request $request){
        $query = StoreItemPriceList::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new StoreItemPriceList())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the StoreItemPriceList data');

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
