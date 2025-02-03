<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportExpeditionRanking;
use App\Http\Controllers\Controller;
use App\Models\DeliveryCost;
use App\Models\Region;
use App\Models\Transportation;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpeditionPriceRankingReport extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct()
    {
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'         => 'Rekapitulasi',
            'transport'         => Transportation::where('status','1')->get(),
            'content'       => 'admin.sales.expedition_price_ranking_report',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'account_id',
            'transportation_id',
            'to_city_id',
            'to_subdistrict_id',
            'qty_tonnage',
            'tonnage',
            'ritage',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column', 'tonnage')];
        $dir    = $request->input('order.0.dir', 'asc');
        $search = $request->input('search.value');

        $total_data = DeliveryCost::count();
        $query_data = DeliveryCost::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('name','like',"%$search%")
                    ->orWhere('code','like',"%$search%")
                    ->orWhereHas('account',function($query) use ($search) {
                        $query->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }


            if($request->filter_province&& !$request->filter_city && !$request->filter_district){

                $region = Region::find($request->filter_province);

                $query->whereHas('toCity', function($query) use ($region) {
                    $query->whereRaw('substr(code, 1, 2) = ?', [substr($region->code, 0, 2)]);
                });
            }

            if($request->filter_city && !$request->filter_district ){
                $query->where('to_city_id', $request->filter_city);
            }
            if($request->filter_district){
                $query->where('to_subdistrict_id', $request->filter_district);
            }
            if($request->account_id){
                $query->where('account_id', $request->account_id);
            }

            if($request->filter_transportation){
                $query->where('transportation_id', $request->filter_transportation);
            }

            if($request->start_date && $request->finish_date) {
                $query->where(function($query) use ($request) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_from', '<=', $request->finish_date);
                })->orWhere(function($query) use ($request) {
                    $query->whereDate('valid_to', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                });
            } else if($request->start_date) {
                $query->whereDate('valid_from','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('valid_to','<=', $request->finish_date);
            }

            if($request->status){
                $query->where('status', $request->status);
            }
        })
        ->offset($start)
        ->limit($length)
        ->orderBy('tonnage', 'asc')
        ->get();
        $total_filtered = DeliveryCost::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('name','like',"%$search%")
                    ->orWhere('code','like',"%$search%")
                    ->orWhereHas('account',function($query) use ($search) {
                        $query->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                });
            }

            if($request->filter_province&& !$request->filter_city && !$request->filter_district){

                $region = Region::find($request->filter_province);

                $query->whereHas('toCity', function($query) use ($region) {
                    $query->whereRaw('substr(code, 1, 2) = ?', [substr($region->code, 0, 2)]);
                });
            }

            if($request->filter_city && !$request->filter_district ){
                $query->where('to_city_id', $request->filter_city);
            }
            if($request->filter_district){
                $query->where('to_subdistrict_id', $request->filter_district);
            }
            if($request->account_id){
                $query->where('account_id', $request->account_id);
            }

            if($request->filter_transportation){
                $query->where('transportation_id', $request->filter_transportation);
            }

            if($request->start_date && $request->finish_date) {
                $query->where(function($query) use ($request) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_from', '<=', $request->finish_date);
                })->orWhere(function($query) use ($request) {
                    $query->whereDate('valid_to', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                });
            } else if($request->start_date) {
                $query->whereDate('valid_from','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('valid_to','<=', $request->finish_date);
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
                    $val->name,
                    $val->transportation->name ?? '',
                    $val->toCity->name,
                    $val->toSubdistrict->name,
                    number_format($val->qty_tonnage,3,',','.'),
                    number_format($val->tonnage,2,',','.'),
                    $val->status(),
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

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        if($request->account == "null"){
            $account = '';
        }else{
		    $account = $request->account ? $request->account : '';
        }


        $filter_province = $request->filter_province? $request->filter_province : '';
        $filter_city = $request->filter_city ? $request->filter_city : '';
        $filter_district = $request->filter_district ? $request->filter_district : '';
        $filter_transportation = $request->filter_transportation ? $request->filter_transportation : '';
		return Excel::download(new ExportExpeditionRanking($search,$post_date,$end_date,$status,$account,$filter_province,$filter_city,$filter_district,$filter_transportation), 'delivery_cost_'.uniqid().'.xlsx');
    }
}
