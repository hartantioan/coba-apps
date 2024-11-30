<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RowImportException;
use App\Helpers\CustomHelper;
use App\Models\RuleBpScale;
use Illuminate\Validation\Rule;

class RuleBpScaleController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'User - BP Scale',
            'content'   => 'admin.master_data.rule_bp_scale',
            //'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'account_id',
            'item_id',
            'rule_procurement_id',
            'effective_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = RuleBpScale::count();

        $query_data = RuleBpScale::whereHas('user',function($query) use ($search){
            $query->where('name','like',"%$search%")
            ->orWhere('employee_no', 'like', "%$search%");
        })
        ->whereHas('item',function($query) use ($search){
            $query->where('name','like',"%$search%")
            ->orWhere('code', 'like', "%$search%");
        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();


        $total_filtered = RuleBpScale::whereHas('user',function($query) use ($search){
            $query->where('name','like',"%$search%")
            ->orWhere('employee_no', 'like', "%$search%");
        })
        ->whereHas('item',function($query) use ($search){
            $query->where('name','like',"%$search%")
            ->orWhere('code', 'like', "%$search%");
        })
        ->orderBy($order, $dir)
        ->count();


        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    $nomor,
                    $val->account->name,
                    $val->ruleProcurement->name,
                    date('d/m/Y',strtotime($val->effective_date)),
                    $val->item->name,
                    CustomHelper::formatConditionalQty($val->water_percent).'%',
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . $val->id . '`)"><i class="material-icons dp48">create</i></button>
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

    // public function import(Request $request)
    // {
    //     try {
    //         Excel::import(new ImportUserBrand, $request->file('file'));
    //         return response()->json(['status' => 200, 'message' => 'Import successful']);
    //     } catch (RowImportException $e) {
    //         return response()->json([
    //             'message' => 'Import failed',
    //             'error' => $e->getMessage(),
    //             'row' => $e->getRowNumber(),
    //             'column' => $e->getColumn(),
    //             'sheet' => $e->getSheet(),
    //         ], 400);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
    //     }
    // }

    // public function getImportExcel(){
    //     return Excel::download(new ExportTemplateUserBrand(), 'format_template_user_brand'.uniqid().'.xlsx');
    // }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'account_id' 			=> 'required',
            'item_id'               => 'required',
        ], [
            'account_id.required' 	    => 'User tidak boleh kosong.',
            'item_id.required'      => 'Brand tidak boleh kosong.',
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
                    $find = RuleBpScale::find($request->temp);
                    $query = $find->update([
                        'user_id'       => session('bo_id'),
                        'account_id'	=> $request->account_id,
                        'rule_procurement_id'	=> $request->rule_procurement_id,
                        'water_percent'	=> $request->water_percent,
                        'item_id'       => $request->item_id,
                        'effective_date'       => $request->effective_date,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = RuleBpScale::create([
                        'user_id'       => session('bo_id'),
                        'account_id'	=> $request->account_id,
                        'rule_procurement_id'	=> $request->rule_procurement_id,
                        'water_percent'	=> $request->water_percent,
                        'item_id'       => $request->item_id,
                        'effective_date'       => $request->effective_date,

                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

			}

			if($query) {

                activity()
                    ->performedOn(new RuleBpScale())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Rule BP X Scale.');

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
        $User = RuleBpScale::find($request->id);
        $User['rule_procurement_name'] = $User->ruleProcurement->name;
        $User['item_name'] = $User->item->name;
        $User['account_name'] = $User->account->name;


		return response()->json($User);
    }

    public function destroy(Request $request){
        $query = RuleBpScale::find($request->id);

        if($query->userBrand()->delete()) {
            activity()
                ->performedOn(new RuleBpScale())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Rule Bp xScale data');

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

    // public function export(Request $request){
    //     $search = $request->search ? $request->search : '';

	// 	return Excel::download(new ExportUserBrand($search), 'user_brand_'.uniqid().'.xlsx');
    // }
}
