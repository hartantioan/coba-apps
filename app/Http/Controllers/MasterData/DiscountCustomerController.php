<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportCustomerDiscount;
use App\Http\Controllers\Controller;
use App\Imports\ImportCustomerDiscount;
use App\Models\CustomerDiscount;
use App\Exceptions\RowImportException;
use App\Exports\ExportTemplateDiscountCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DiscountCustomerController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Diskon Customer',
            'content'   => 'admin.master_data.customer_discount',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'account_id',
            'city_id',
            'brand_id',
            'type_id',
            'payment_type',
            'disc1',
            'disc2',
            'disc3',
            'post_date',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = CustomerDiscount::count();
        
        $query_data = CustomerDiscount::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('group',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                ->orWhere('code','like',"%$search%");
                            })->orWhereHas('account',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('brand',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('city',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('type',function($query) use($search){
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

        $total_filtered = CustomerDiscount::where(function($query) use ($search, $request) {
                 if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('price', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('group',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                ->orWhere('code','like',"%$search%");
                            })->orWhereHas('account',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('brand',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('city',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('type',function($query) use($search){
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
                    $val->account->name,
                    $val->city->name,
                    $val->brand->name,
                    $val->type->name,
                    $val->paymentType(),
                    number_format($val->disc1,2,',','.'),
                    number_format($val->disc2,2,',','.'),
                    number_format($val->disc3,2,',','.'),
                    date('d/m/Y',strtotime($val->post_date)),
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
            'code'                           => 'required',
            'account_id'                     => 'required',
            'city_id'                        => 'required',
            'brand_id'                       => 'required',
            'type_id'                        => 'required',
            'payment_type'                   => 'required',
            'disc1'                          => 'required',
		], [
            'code.required' 	                        => 'Kode tidak boleh kosong.',
            'account_id.required'                       => 'Pelanggan Tidak boleh kosong',
            'city_id.required'                          => 'Kota tidak boleh kosong.',
            'brand_id.required'                         => 'Brand tidak boleh kosong.',
            'type_id.required'                          => 'Item tidak boleh kosong.',
            'payment_type.required'                     => 'Tipe payment tidak boleh kosong.',
			'disc1.required'                            => 'diskon tidak boleh kosong.',
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
                    $query = CustomerDiscount::find($request->temp);
                  
                    $query->code = $request->code;
                    $query->user_id = session('bo_id');
                    
                    $query->account_id = $request->account_id;
                    $query->city_id = $request->city_id;
                    $query->brand_id = $request->brand_id;
                    $query->type_id = $request->type_id;
                    
                    $query->payment_type = $request->payment_type;

                    $query->disc1 = str_replace(',','.',str_replace('.','',$request->disc1));
                    $query->disc2 = str_replace(',','.',str_replace('.','',$request->disc2));
                    $query->disc3 = str_replace(',','.',str_replace('.','',$request->disc3));
                  
                    $query->status = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                    
                    
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{
                DB::beginTransaction();
               
                   
                    $query = CustomerDiscount::create([
                        'code'			            => $request->code,
                        'user_id'			        => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'city_id'	                => $request->city_id,
                        'brand_id'                  => $request->brand_id,
                        'type_id'                   => $request->type_id,
                        'payment_type'              => $request->payment_type,
                        'disc1'                     => str_replace(',','.',str_replace('.','',$request->disc1)),
                        'disc2'                     => str_replace(',','.',str_replace('.','',$request->disc2)),
                        'disc3'                     => str_replace(',','.',str_replace('.','',$request->disc3)),
                        'post_date'                 => now(),
                        'status'                    => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                    
                   
                
			}
			
			if($query) {

                activity()
                    ->performedOn(new CustomerDiscount())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit standar price pelanggan data.');


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
        $cd = CustomerDiscount::find($request->id);
        $cd['account'] = $cd->account;
        $cd['user'] = $cd->user;
        $cd['brand'] = $cd->brand;
        $cd['city'] = $cd->city;
        $cd['item'] = $cd->item;
 		return response()->json($cd);
    }

    public function destroy(Request $request){
        $query = CustomerDiscount::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new CustomerDiscount())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the standar price customer data');

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
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportCustomerDiscount($search,$status), 'standar_harga_pelanggan_'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportCustomerDiscount, $request->file('file'));
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
        return Excel::download(new ExportTemplateDiscountCustomer(), 'format_template_customer_discount'.uniqid().'.xlsx');
    }
}
