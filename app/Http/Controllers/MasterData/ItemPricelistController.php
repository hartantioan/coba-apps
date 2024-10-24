<?php

namespace App\Http\Controllers\MasterData;

use App\Exceptions\RowImportException;
use App\Exports\ExportTemplatePriceList;
use App\Exports\ExportTransactionPageItemPriceList;
use App\Http\Controllers\Controller;
use App\Imports\ImportPriceList;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\BenchmarkPrice;
use App\Models\Group;
use App\Models\ItemPricelist;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ItemPricelistController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];

    }

    public function index()
    {
        $data = [
            'title'     => 'Pricelist Item FG',
            'content'   => 'admin.master_data.item_pricelist',
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'group'     => Group::where('status','1')->where('type','2')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'type_id',
            'province_id',
            'city_id',
            'group_id',
            'place_id',
            'grade_id',
            'type_delivery',
            'price',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ItemPricelist::count();

        $query_data = ItemPricelist::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->whereHas('type',function($query) use ($search, $request) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        })
                        ->orWhereHas('customer',function($query) use ($search, $request) {
                            $query->where('employee_no', 'like', "%$search%")
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

        $total_filtered = ItemPricelist::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where(function($query) use ($search, $request) {
                            $query->whereHas('type',function($query) use ($search, $request) {
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('customer',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
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
                    $val->user->name,
                    $val->type->name ?? '-',
                    $val->province->name ?? '-',
                    $val->city->name ?? '-',
                    $val->group->name,
                    $val->deliveryType() ?? '-',
                    $val->grade->code ?? '-',
                    $val->place->code.' - '.$val->place->name,
                    number_format($val->discount,2,',','.') ?? 0,
                    number_format($val->sell_price,2,',','.') ?? '-',
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
            'type_id'               => 'required',
            'place_id'              => 'required',
            'group_id'              => 'required',
            'price'                 => 'required',
            'start_date'            => 'required',
            'end_date'              => 'required',
            'province_id'       => 'required',
            'city_id'           => 'required'
        ], [
            'type_id.required'      => 'Tipe Item tidak boleh kosong.',
            'place_id.required'     => 'Plant tidak boleh kosong.',
            'group_id.required'     => 'Group tidak boleh kosong.',
            'price.required'        => 'Harga tidak boleh kosong.',
            'province_id.required'  => 'Provinsi tidak boleh kosong.',
            'city_id.required'      => 'Kota tidak boleh kosong.',
            'start_date.required'   => 'Tgl.Mulai Aktif tidak boleh kosong.',
            'end_date.required'     => 'Tgl.Akhir Aktif tidak boleh kosong.',
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
                    $query = ItemPricelist::find($request->temp);
                    $query->user_id         = session('bo_id');

                    $query->group_id        = $request->group_id;
                    $query->place_id        = $request->place_id;
                    $query->type_id         = $request->type_id;
                    $query->grade_id        = $request->grade_id;
                    $query->type_delivery   = $request->type_delivery;
                    $query->province_id     = $request->province_id;
                    $query->city_id         = $request->city_id;
                    $query->discount           = str_replace(',','.',str_replace('.','',$request->discount));
                    $query->sell_price           = str_replace(',','.',str_replace('.','',$request->sell_price));
                    $query->price           = str_replace(',','.',str_replace('.','',$request->price));
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = ItemPricelist::create([
                        'code'              => strtoupper(Str::random(15)),
                        'user_id'           => session('bo_id'),

                        'group_id'          => $request->group_id,
                        'place_id'          => $request->place_id,

                        'type_id'			=> $request->type_id,
                        'province_id'			=> $request->province_id,
                        'city_id'			=> $request->city_id,
                        'grade_id'			=> $request->grade_id,
                        'type_delivery'          => $request->type_delivery,

                        'discount'             => str_replace(',','.',str_replace('.','',$request->discount)),
                        'sell_price'             => str_replace(',','.',str_replace('.','',$request->sell_price)),
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
                    ->performedOn(new ItemPricelist())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit item price list data.');

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
        $bp = ItemPricelist::find($request->id);
        $bp['price'] = number_format($bp->price,2,',','.');
        $bp['type'] = $bp->type;
        $bp['grade'] = $bp->grade;
        $bp['group']= $bp->group;
        $bp['province'] = $bp->province;
        $bp['city']= $bp->city;

		return response()->json($bp);
    }

    public function destroy(Request $request){
        $query = ItemPricelist::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new ItemPricelist())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the item price list data');

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

    public function getImportExcel(){
        return Excel::download(new ExportTemplatePriceList(), 'format_template_price_list'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportPriceList, $request->file('file'));
            return response()->json(['status' => 200, 'message' => 'Import successful']);
        } catch (RowImportException $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
                'row' => $e->getRowNumber(),
                'column' => $e->getColumn(),
                'sheet' => $e->getSheet(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportTransactionPageItemPriceList($search,$status), 'standar_harga_pelanggan_'.uniqid().'.xlsx');
    }
}
