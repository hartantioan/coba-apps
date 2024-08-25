<?php

namespace App\Http\Controllers\MasterData;

use App\Exceptions\RowImportException;
use App\Exports\ExportDeliveryCostStandard;
use App\Exports\ExportTemplateDeliveryCostStandard;
use App\Http\Controllers\Controller;
use App\Imports\ImportDeliveryCostStandard;
use App\Models\DeliveryCostStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class DeliveryCostStandardController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Standard Harga Pengiriman',
            'content'   => 'admin.master_data.delivery_cost_standard',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'category_transportation',
            'city_id',
            'district_id',
            'price',
            'start_date',
            'end_date',
            'note',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = DeliveryCostStandard::count();
        
        $query_data = DeliveryCostStandard::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhere('price', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search) {
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('city',function($query) use ($search) {
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('district',function($query) use ($search) {
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

        $total_filtered = DeliveryCostStandard::where(function($query) use ($search, $request) {
                 if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhere('price', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search) {
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('city',function($query) use ($search) {
                            $query->where('name','like',"%$search%");
                        })->orWhereHas('district',function($query) use ($search) {
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
                    $val->code,
                    $val->user->name,
                    $val->categoryTransportation(),
                    $val->city->name,
                    $val->district->name,
                    date('d/m/Y',strtotime($val->start_date)),
                    date('d/m/Y',strtotime($val->end_date)),
                    number_format($val->price,2,',','.'),
                    $val->note,
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
          
            'category_transportation'        => 'required',
            'city_id'                        => 'required',
            'district_id'                    => 'required',
            'price'                          => 'required',
            'note'                           => 'required',
            'start_date'                     => 'required',
            'end_date'                       => 'required',
		], [
            'category_transportation.required'                       => 'Transportasi Tidak boleh kosong',
            'city_id.required'                          => 'Kota tidak boleh kosong.',
            'district_id.required'                         => 'District tidak boleh kosong.',
            'price.required'                          => 'Harga tidak boleh kosong.',
            'note.required'                     => 'note tidak boleh kosong.',
			'end_date.required'                            => 'Tanggal akhir tidak boleh kosong.',
            'start_date.required'                            => 'Tanggal mulai tidak boleh kosong.',
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
                    $query = DeliveryCostStandard::find($request->temp);
                  
                    $query->user_id = session('bo_id');
                    
                    $query->city_id = $request->city_id;
                    $query->district_id = $request->district_id;
                    
                    $query->category_transportation = $request->category_transportation;

                    $query->start_date      = $request->start_date;
                    $query->end_date        = $request->end_date;
                    $query->price           = str_replace(',','.',str_replace('.','',$request->price));
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                    
                    
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{
                DB::beginTransaction();
               
                   
                    $query = DeliveryCostStandard::create([
                        'code'			            => Str::random(10),
                        'user_id'			        => session('bo_id'),
                        'city_id'	                => $request->city_id,
                        'district_id'               => $request->district_id,
                        'note'                      => $request->note,
                        'payment_type'              => $request->payment_type,
                        'price'                     => str_replace(',','.',str_replace('.','',$request->price)),
                        'category_transportation'   => $request->category_transportation,
                        'start_date'                => $request->start_date,
                        'end_date'                  => $request->end_date,
                        'status'                    => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                    
                   
                
			}
			
			if($query) {

                activity()
                    ->performedOn(new DeliveryCostStandard())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit  standard Cost Delivery data.');


				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
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
        $cd = DeliveryCostStandard::find($request->id);
        $cd['user'] = $cd->user;
        $cd['district'] = $cd->district;
        $cd['city'] = $cd->city;
        $cd['price'] = number_format($cd->price,2,',','.');
 		return response()->json($cd);
    }

    public function destroy(Request $request){
        $query = DeliveryCostStandard::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new DeliveryCostStandard())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the standard cost delivery data');

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

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $company = $request->company ? $request->company : 0;
        $type = $request->type ? $request->type : '';
		
		// return Excel::download(new ExportCoa($search,$status,$company,$type), 'coa_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        
        $start_date = $request->start_date ? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportDeliveryCostStandard($search,$status,$start_date,$end_date), 'standar_harga_pelanggan_'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportDeliveryCostStandard, $request->file('file'));
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

    public function getImportExcel(){
        return Excel::download(new ExportTemplateDeliveryCostStandard(), 'format_template_delivery_cost_standard'.uniqid().'.xlsx');
    }
}
