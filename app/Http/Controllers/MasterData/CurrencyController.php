<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\CurrencyDate;

class CurrencyController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Mata Uang',
            'content'   => 'admin.master_data.currency',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'document_text',
            'symbol',
            'type',
            'max_decimal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Currency::count();

        $query_data = Currency::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = Currency::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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
                    $val->name,
                    $val->document_text,
                    $val->symbol,
                    $val->type(),
                    $val->max_decimal,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown accent-2 white-text btn-small" data-popup="tooltip" title="History" onclick="history(' . $val->id . ')"><i class="material-icons dp48">format_list_numbered</i></button>
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
            'code' 				=> $request->temp ? ['required', Rule::unique('currencies', 'code')->ignore($request->temp)] : 'required|unique:currencies,code',
            'name'              => 'required',
            'document_text'     => 'required',
            'symbol'            => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
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
                    $query = Currency::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->document_text   = $request->document_text;
                    $query->symbol          = $request->symbol;
                    $query->type            = $request->type;
                    $query->max_decimal     = $request->max_dec;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Currency::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'document_text' => $request->document_text,
                        'symbol'        => $request->symbol,
                        'type'          => $request->type,
                        'max_decimal'   => $request->max_dec,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {

                activity()
                    ->performedOn(new Currency())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit currency.');

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
        $currency = Currency::find($request->id);

		return response()->json($currency);
    }

    public function history(Request $request){
        $currency = CurrencyDate::where('currency_id',$request->id)
        ->orderBy('id', 'asc')->get();
        $c = Currency::find($request->id);
        $string = '<div class="row pt-1 pb-1 lighten-4">
                    <div class="col s12"></div><div class="col s12">
                    <table class="bordered">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">History Rates '.$c->code.'</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Tanggal</th>
                                <th class="center-align">Rates</th>
                            </tr>
                        </thead><tbody>';


        foreach($currency as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->currency_date.'</td>
                <td class="right-align">'.$row->currency_rate.'</td>
            </tr>';
        }


        $string .= '</tbody></table></div>';

		return $string;
    }

    public function destroy(Request $request){
        $query = Currency::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new Currency())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the currency data');

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

    public function currencyGet(Request $request){
        $dateString = now()->toDateString();
        if (Carbon::parse($request->date)->toDateString() === $dateString) {
            $adjustedDate = Carbon::parse($request->date)->subDay()->toDateString();
        } else {
            $adjustedDate = $request->date;
        }
        $find_currency = Currency::where('code',$request->code)->first();
        $find = CurrencyDate::where('currency_id',$find_currency->id)
        ->where('currency_date',$adjustedDate)
        ->first();

        if(!$find){
            $response = Http::get("https://api.vatcomply.com/rates", [
                'base' => $request->code,
                'date' => $request->date,
            ]);
            $data = $response->json();
            $query_currency_ada = CurrencyDate::where('id',$find_currency->id)
                ->where('currency_date',$data['date'])
                ->first();
            if(!$query_currency_ada){
                $m = [
                    'currency_id'   => $find_currency->id,
                    'currency_date' => $data['date'],
                    'currency_rate' => $data['rates']['IDR'],
                    'taken_from'    => 'https://api.vatcomply.com/rates',
                ];
                CurrencyDate::create($m);
                $find = $m;

            }else{
                $find = $query_currency_ada;

            }


        }

        return $find;
    }
}
