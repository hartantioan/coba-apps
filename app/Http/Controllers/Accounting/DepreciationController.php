<?php

namespace App\Http\Controllers\Accounting;
use App\Exports\ExportDepreciation;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Place;
use App\Models\User;
use App\Models\Asset;
use App\Models\Depreciation;
use App\Models\DepreciationDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class DepreciationController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'     => 'Depresiasi Aset',
            'content'   => 'admin.accounting.depreciation',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'period',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Depreciation::count();
        
        $query_data = Depreciation::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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

        $total_filtered = Depreciation::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d/m/y',strtotime($val->post_date)),
                    date('F Y',strtotime($val->period)),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function preview(Request $request){
        $data = Asset::whereNotNull('book_balance')->where('book_balance','>',0)->whereHas('place', function($query) use($request){
            $query->where('company_id',$request->company_id);
        })->get();

        $arr = [];

        foreach($data as $row){
            if(!$row->checkDepreciationByMonth($request->period)){
                $arr[] = [
                    'asset_id'      => $row->id,
                    'asset_code'    => $row->code,
                    'asset_name'    => $row->name,
                    'asset_place'   => $row->place->name,
                    'method'        => $row->method,
                    'method_name'   => $row->method(),
                    'nominal'       => number_format($row->nominalDepreciation(),2,',','.'),
                ];
            }
        }

        return response()->json($arr);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'company_id'		    => 'required',
            'period'		        => 'required',
            'note'		            => 'required',
            'arr_asset_id'          => 'required|array',
            'arr_total'             => 'required|array',
		], [
			'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'period.required' 			        => 'Periode tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'arr_asset_id.required'             => 'Aset tidak boleh kosong',
            'arr_asset_id.array'                => 'Aset harus dalam bentuk array.',
            'arr_total.required'                => 'Nominal total tidak boleh kosong',
            'arr_total.array'                   => 'Nominal total harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {


			if($request->temp){
                
                $query = Depreciation::where('code',CustomHelper::decrypt($request->temp))->first();

                if($query->approval()){
                    foreach($query->approval()->approvalMatrix as $row){
                        if($row->status == '2'){
                            return response()->json([
                                'status'  => 500,
                                'message' => 'Depresiasi aset telah diapprove, anda tidak bisa melakukan perubahan.'
                            ]);
                        }
                    }
                }

                if($query->status == '1'){
                    $query->user_id = session('bo_id');
                    $query->company_id = $request->company_id;
                    $query->post_date = date('Y-m-d');
                    $query->period = $request->period;
                    $query->note = $request->note;
                    $query->save();

                    foreach($query->depreciationDetail as $row){
                        $row->delete();
                    }

                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status depresiasi aset sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
			}else{
                $query = Depreciation::create([
                    'code'			=> Depreciation::generateCode(),
                    'user_id'		=> session('bo_id'),
                    'company_id'    => $request->company_id,
                    'post_date'	    => date('Y-m-d'),
                    'period'        => $request->period,
                    'status'        => '1',
                    'note'          => $request->note,
                ]);
			}
			
			if($query) {
                
                foreach($request->arr_asset_id as $key => $row){
                    DepreciationDetail::create([
                        'depreciation_id'       => $query->id,
                        'asset_id'              => $row,
                        'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                    ]);
                }

                CustomHelper::sendApproval('depreciations',$query->id,$query->note);
                CustomHelper::sendNotification('depreciations',$query->id,'Pengajuan Depresiasi No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new Depreciation())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase request.');

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

    public function rowDetail(Request $request){
        $data   = Depreciation::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Aset</th>
                                <th class="center-align">Depresiasi Ke</th>
                                <th class="center-align">Nominal</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->depreciationDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->asset->code.' - '.$row->asset->name.'</td>
                <td class="center-align">'.$row->depreciationNumber().' / '.$row->asset->assetGroup->depreciation_period.'</td>
                <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
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
                    <td class="center-align">'.$row->approvalTable->level.'</td>
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

    public function show(Request $request){
        $dpr = Depreciation::where('code',CustomHelper::decrypt($request->id))->first();

        $arr = [];
        
        foreach($dpr->depreciationDetail as $row){
            $arr[] = [
                'asset_id'      => $row->asset_id,
                'asset_code'    => $row->asset->code,
                'asset_name'    => $row->asset->name,
                'asset_place'   => $row->asset->place->name,
                'method'        => $row->asset->method,
                'method_name'   => $row->asset->method(),
                'nominal'       => number_format($row->nominal,2,',','.'),
            ];
        }

        $dpr['details'] = $arr;
        				
		return response()->json($dpr);
    }

    public function voidStatus(Request $request){
        $query = Depreciation::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                if(in_array($query->status,['2','3'])){
                    foreach($query->depreciationDetail as $row){
                        CustomHelper::updateBalanceAsset($row->asset_id,$row->nominal,'IN');
                    }
                }
    
                activity()
                    ->performedOn(new Depreciation())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the depreciation data');
    
                CustomHelper::sendNotification('depreciations',$query->id,'Depresiasi Aset No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('depreciations',$query->id);
                CustomHelper::removeJournal('depreciations',$query->id);

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

    public function destroy(Request $request){
        $query = Depreciation::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Kapitalisasi telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
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

            CustomHelper::removeApproval('depreciations',$query->id);
            
            $query->depreciationDetail()->delete();

            activity()
                ->performedOn(new Depreciation())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the depreciation data');

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

    public function print(Request $request){

        $data = [
            'title' => 'ASSET DEPRECIATION REPORT',
            'data' => Depreciation::where(function($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->get()
		];
		
		return view('admin.print.accounting.depreciation', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportDepreciation($request->search,$request->status,$this->dataplaces), 'depreciation_'.uniqid().'.xlsx');
    }
    
    public function approval(Request $request,$id){
        
        $dpr = Depreciation::where('code',CustomHelper::decrypt($id))->first();
                
        if($dpr){
            $data = [
                'title'     => 'Print Depreciation',
                'data'      => $dpr
            ];

            return view('admin.approval.depreciation', $data);
        }else{
            abort(404);
        }
    }
}