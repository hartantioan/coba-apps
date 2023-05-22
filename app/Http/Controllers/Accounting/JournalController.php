<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Place;
use App\Models\User;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportJournal;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class JournalController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }
    public function index()
    {
        $data = [
            'title'     => 'Jurnal',
            'content'   => 'admin.accounting.journal',
            'company'   => Company::where('status','1')->get(),
            'currency'  => Currency::where('status','1')->get(),
            'place'     => Place::whereIn('id',$this->dataplaces)->where('status','1')->get(),
            'department'=> Department::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'post_date',
            'note'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Journal::count();
        
        $query_data = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })
                            ->orWhereHas('journalDetail',function($query) use ($search, $request){
                                $query->where('nominal', 'like', "%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })->orWhereHas('journalDetail',function($query) use ($search, $request){
                                $query->where('nominal', 'like', "%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    date('d/m/y',strtotime($val->post_date)),
                    $val->note,
                    $val->lookable_id ? $val->lookable->code : '-',
                    $val->status(),
                    !$val->lookable_id ? '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					' : '-',
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

    public function rowDetail(Request $request){
        $data   = Journal::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Perusahaan</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Bisnis Partner</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->journalDetail()->orderBy('id')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="center-align">'.$row->coa->company->name.'</td>
                <td class="center-align">'.($row->place_id ? $row->place->name : '-').'</td>
                <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                <td class="center-align">'.($row->item_id ? $row->item->name : '-').'</td>
                <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->approval()->approvalMatrix()->exists()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'company_id'                => 'required',
			'note'                      => 'required',
            'post_date'                 => 'required',
            'due_date'                  => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'arr_type'                  => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_department'            => 'required|array',
            'arr_nominal'               => 'required|array',
		], [
            'company_id.required'               => 'Perusahaan tidak boleh kosong',
			'note.required'                     => 'Catatan tidak boleh kosong',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'due_date.required'                 => 'Tgl tenggat tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe harus dalam bentuk array.',
            'arr_place.required'                => 'Penempatan tidak boleh kosong.',
            'arr_place.array'                   => 'Penempatan harus dalam bentuk array.',
            'arr_department.required'           => 'Departemen tidak boleh kosong.',
            'arr_department.array'              => 'Departemen harus dalam bentuk array.',
            'arr_nominal.required'              => 'Nominal tidak boleh kosong.',
            'arr_nominal.array'                 => 'Nominal harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $totalDebit = 0; 
            $totalCredit = 0;
            foreach($request->arr_nominal as $key => $row){
                if($request->arr_type[$key] == '1'){
                    $totalDebit += str_replace(',','.',str_replace('.','',$row));
                }elseif($request->arr_type[$key] == '2'){
                    $totalCredit += str_replace(',','.',str_replace('.','',$row));
                }
            }

            if($totalDebit - $totalCredit > 0 || $totalDebit - $totalCredit < 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Total debit dan kredit selisih '.(number_format($totalDebit - $totalCredit,2,',','.'))
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Journal::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Jurnal telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){

                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->note = $request->note;

                        $query->save();

                        foreach($query->journalDetail as $row){
                            $row->delete();
                        }

                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status jurnal sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    
                    $query = Journal::create([
                        'code'			            => Journal::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'currency_id'               => $request->currency_id,
                        'company_id'                => $request->company_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'due_date'                  => $request->due_date,
                        'note'                      => $request->note,
                        'status'                    => '1'
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                if($request->arr_type){
                    DB::beginTransaction();
                    try {
                        foreach($request->arr_type as $key => $row){
                            JournalDetail::create([
                                'journal_id'        => $query->id,
                                'coa_id'            => $request->arr_coa[$key],
                                'place_id'          => $request->arr_place[$key],
                                'account_id'        => $request->arr_account[$key] == 'NULL' ? NULL : $request->arr_account[$key],
                                'item_id'           => $request->arr_item[$key] == 'NULL' ? NULL : $request->arr_item[$key],
                                'department_id'     => $request->arr_department[$key],
                                'warehouse_id'      => $request->arr_warehouse[$key] == 'NULL' ? NULL : $request->arr_warehouse[$key],
                                'type'              => $row,
                                'nominal'           => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                            ]);
                        }
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('journals',$query->id,$query->note);
                CustomHelper::sendNotification('journals',$query->id,'Pengajuan Jurnal No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new Journal())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit journal.');

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

    public function createMulti(Request $request){
        $validation = Validator::make($request->all(), [
            'arr_multi_code'                          => 'required|array',
		], [
            'arr_multi_code.required'                 => 'Kode multi tidak boleh kosong.',
            'arr_multi_code.array'                    => 'Kode multi harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $totalDebit = 0; 
            $totalCredit = 0;
            foreach($request->arr_multi_debit as $key => $row){
                $totalDebit += floatval($row);
            }

            foreach($request->arr_multi_kredit as $key => $row){
                $totalCredit += floatval($row);
            }

            if($totalDebit - $totalCredit > 0 || $totalDebit - $totalCredit < 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Total debit dan kredit selisih '.(number_format($totalDebit - $totalCredit,2,',','.'))
                ]);
            }

            $cekSameCode = Journal::whereIn('code',$request->arr_multi_code)->count();

            if($cekSameCode > 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Kode jurnal telah terpakai, silahkan gunakan yang lainnya.'
                ]);
            }
        
            DB::beginTransaction();
            try {

                $temp = '';
                foreach($request->arr_multi_code as $key => $row){

                    if($temp !== $row){
                        $query = Journal::create([
                            'code'			            => $row,
                            'user_id'		            => session('bo_id'),
                            'currency_id'               => $request->arr_multi_currency[$key] ? $request->arr_multi_currency[$key] : NULL,
                            'company_id'                => $request->arr_multi_company[$key] ? $request->arr_multi_company[$key] : NULL,
                            'currency_rate'             => $request->arr_multi_conversion[$key] ? $request->arr_multi_conversion[$key] : NULL,
                            'post_date'                 => $request->arr_multi_post_date[$key] ? date('Y-m-d',strtotime($request->arr_multi_post_date[$key])) : NULL,
                            'due_date'                  => $request->arr_multi_due_date[$key] ? date('Y-m-d',strtotime($request->arr_multi_due_date[$key])) : NULL,
                            'note'                      => $request->arr_multi_note[$key] ? $request->arr_multi_note[$key] : NULL,
                            'status'                    => '1'
                        ]);

                        CustomHelper::sendApproval('journals',$query->id,$query->note);
                        CustomHelper::sendNotification('journals',$query->id,'Pengajuan Jurnal No. '.$query->code,$query->note,session('bo_id'));

                        activity()
                            ->performedOn(new Journal())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add / edit journal.');
                    }

                    if($query) {
                
                        if(floatval($request->arr_multi_debit[$key]) > 0){
                            JournalDetail::create([
                                'journal_id'        => $query->id,
                                'coa_id'            => $request->arr_multi_coa[$key],
                                'place_id'          => $request->arr_multi_place[$key],
                                'account_id'        => $request->arr_multi_bp[$key],
                                'item_id'           => $request->arr_multi_item[$key],
                                'department_id'     => $request->arr_multi_department[$key],
                                'warehouse_id'      => $request->arr_multi_warehouse[$key],
                                'type'              => '1',
                                'nominal'           => floatval($request->arr_multi_debit[$key]),
                            ]);
                        }

                        if(floatval($request->arr_multi_kredit[$key]) > 0){
                            JournalDetail::create([
                                'journal_id'        => $query->id,
                                'coa_id'            => $request->arr_multi_coa[$key],
                                'place_id'          => $request->arr_multi_place[$key],
                                'account_id'        => $request->arr_multi_bp[$key],
                                'item_id'           => $request->arr_multi_item[$key],
                                'department_id'     => $request->arr_multi_department[$key],
                                'warehouse_id'      => $request->arr_multi_warehouse[$key],
                                'type'              => '2',
                                'nominal'           => floatval($request->arr_multi_kredit[$key]),
                            ]);
                        }
                    }
                    
                    $temp = $row;
                }

                $response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
        }

        return response()->json($response);
    }

    public function show(Request $request){
        $jou = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        $jou['currency_rate'] = number_format($jou->currency_rate,2,',','.');

        $arr = [];
        
        foreach($jou->journalDetail as $row){
            $arr[] = [
                'type'              => $row->type,
                'coa_id'            => $row->coa_id,
                'coa_name'          => $row->coa->code.' - '.$row->coa->name.' - '.$row->coa->company->name,
                'place_id'          => $row->place_id,
                'account_id'        => $row->account_id ? $row->account_id : '',
                'account_name'      => $row->account_id ? $row->account->name : '',
                'item_id'           => $row->item_id ? $row->item_id : '',
                'item_name'         => $row->item_id ? $row->item->name : '',
                'department_id'     => $row->department_id,
                'warehouse_id'      => $row->warehouse_id,
                'warehouse_name'    => $row->warehouse_id ? $row->warehouse->name : '',
                'nominal'           => number_format($row->nominal,2,',','.')
            ];
        }

        $jou['details'] = $arr;
        				
		return response()->json($jou);
    }

    public function destroy(Request $request){
        
        $query = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Jurnal telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->journalDetail()->delete();

            CustomHelper::removeApproval('journals',$query->id);

            activity()
                ->performedOn(new Journal())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the journal data');

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

    public function voidStatus(Request $request){
        $query = Journal::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->journalDetail as $row){
                    $row->delete();
                }
    
                activity()
                    ->performedOn(new Journal())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the journal data');
    
                CustomHelper::sendNotification('journals',$query->id,'Jurnal No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function print(Request $request){

        $data = [
            'title' => 'JOURNAL REPORT',
            'data' => Journal::where(function($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('user',function($query) use ($request){
                                $query->where('name', 'like', "%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

            })
            ->get()
		];
		
		return view('admin.print.accounting.journal', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportJournal($request->search,$request->status,$request->currency,$this->dataplaces), 'journal_'.uniqid().'.xlsx');
    }

    public function approval(Request $request,$id){
        
        $cap = Journal::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Print Journal',
                'data'      => $cap
            ];

            return view('admin.approval.journal', $data);
        }else{
            abort(404);
        }
    }
}