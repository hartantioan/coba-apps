<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Place;
use App\Models\User;
use App\Models\Asset;
use App\Models\Capitalization;
use App\Models\CapitalizationDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCapitalization;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class CapitalizationController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index()
    {
        $data = [
            'title'     => 'Kapitalisasi Aset',
            'content'   => 'admin.accounting.capitalization',
            'company'   => Company::where('status','1')->get(),
            'currency'  => Currency::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'currency_id',
            'currency_rate',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Capitalization::count();
        
        $query_data = Capitalization::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Capitalization::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    $val->currency->code.' - '.$val->currency->name,
                    number_format($val->currency_rate,3,',','.'),
                    date('d M Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        '.$btn_jurnal.'
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'post_date'			    => 'required',
			'company_id'		    => 'required',
            'currency_id'		    => 'required',
            'currency_rate'		    => 'required',
            'note'		            => 'required',
            'arr_asset_id'          => 'required|array',
            'arr_price'             => 'required|array',
            'arr_qty'               => 'required|array',
            'arr_unit'              => 'required|array',
            'arr_total'             => 'required|array',
		], [
			'post_date.required' 			    => 'Tanggal post tidak boleh kosong.',
			'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'currency_id.required' 			    => 'Mata uang tidak boleh kosong.',
            'currency_rate.required' 			=> 'Konversi tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'arr_asset_id.required'             => 'Aset tidak boleh kosong',
            'arr_asset_id.array'                => 'Aset harus dalam bentuk array.',
            'arr_price.required'                => 'Harga tidak boleh kosong',
            'arr_price.array'                   => 'Harga harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty tidak boleh kosong',
            'arr_qty.array'                     => 'Qty harus dalam bentuk array.',
            'arr_unit.required'                 => 'Satuan tidak boleh kosong',
            'arr_unit.array'                    => 'Satuan harus dalam bentuk array.',
            'arr_total.required'                => 'Total tidak boleh kosong',
            'arr_total.array'                   => 'Total harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $grandtotal = 0;

            foreach($request->arr_total as $row){
                $grandtotal += str_replace(',','.',str_replace('.','',$row));
            }

			if($request->temp){
                
                $query = Capitalization::where('code',CustomHelper::decrypt($request->temp))->first();

                if($query->approval()){
                    foreach($query->approval()->approvalMatrix as $row){
                        if($row->status == '2'){
                            return response()->json([
                                'status'  => 500,
                                'message' => 'Kapitalisasi aset telah diapprove, anda tidak bisa melakukan perubahan.'
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
                    $query->note = $request->note;
                    $query->grandtotal = $grandtotal;
                    $query->save();

                    foreach($query->capitalizationDetail as $row){
                        $row->delete();
                    }

                    CustomHelper::removeJournal('capitalizations',$query->id);

                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status kapitalisasi sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
			}else{
                $query = Capitalization::create([
                    'code'			=> Capitalization::generateCode(),
                    'user_id'		=> session('bo_id'),
                    'company_id'    => $request->company_id,
                    'currency_id'   => $request->currency_id,
                    'currency_rate' => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                    'post_date'	    => $request->post_date,
                    'status'        => '1',
                    'note'          => $request->note,
                    'grandtotal'    => $grandtotal
                ]);
			}
			
			if($query) {
                
                foreach($request->arr_asset_id as $key => $row){
                    CapitalizationDetail::create([
                        'capitalization_id'     => $query->id,
                        'asset_id'              => $row,
                        'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        'unit_id'               => $request->arr_unit[$key],
                        'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                        'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                        'note'                  => $request->arr_note[$key]
                    ]);
                    Asset::find(intval($row))->update([
                        'date'          => $query->post_date,
                        'nominal'       => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                        'book_balance'  => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                    ]);
                }

                CustomHelper::sendApproval('capitalizations',$query->id,$query->note);
                CustomHelper::sendNotification('capitalizations',$query->id,'Pengajuan Kapitalisasi No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new Capitalization())
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
        $data   = Capitalization::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Aset</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Unit</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->capitalizationDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->asset->code.' - '.$row->asset->name.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td>'.$row->note.'</td>
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

    public function show(Request $request){
        $cap = Capitalization::where('code',CustomHelper::decrypt($request->id))->first();
        $cap['currency_rate'] = number_format($cap->currency_rate,3,',','.');

        $arr = [];
        
        foreach($cap->capitalizationDetail as $row){
            $arr[] = [
                'asset_id'          => $row->asset_id,
                'asset_code'        => $row->asset->code,
                'asset_name'        => $row->asset->name,
                'qty'               => $row->qty,
                'unit_id'           => $row->unit_id,
                'unit_name'         => $row->unit->name,
                'price'             => number_format($row->price,3,',','.'),
                'total'             => number_format($row->total,3,',','.'),
                'note'              => $row->note
            ];
        }

        $cap['details'] = $arr;
        				
		return response()->json($cap);
    }

    public function voidStatus(Request $request){
        $query = Capitalization::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                foreach($query->capitalizationDetail as $row){
                    Asset::find($row->asset_id)->update([
                        'date'          => NULL,
                        'nominal'       => NULL,
                        'book_balance'  => NULL,
                    ]);
                }
    
                activity()
                    ->performedOn(new Capitalization())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the capitalization data');
    
                CustomHelper::sendNotification('capitalizations',$query->id,'Kapitalisasi No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('capitalizations',$query->id);
                CustomHelper::removeJournal('capitalizations',$query->id);

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
        $query = Capitalization::where('code',CustomHelper::decrypt($request->id))->first();

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

            CustomHelper::removeApproval('capitalizations',$query->id);
            CustomHelper::removeJournal('capitalizations',$query->id);
            
            foreach($query->capitalizationDetail as $row){
                Asset::find($row->asset_id)->update([
                    'date'          => NULL,
                    'nominal'       => NULL,
                    'book_balance'  => NULL,
                ]);
                $row->delete();
            }

            activity()
                ->performedOn(new Capitalization())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the capitalization data');

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
            'title' => 'ASSET CAPITALIZATION REPORT',
            'data' => Capitalization::where(function($query) use ($request) {
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
		
		return view('admin.print.accounting.capitalization', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportCapitalization($request->search,$request->status,$this->dataplaces), 'capitalization_'.uniqid().'.xlsx');
    }

    public function approval(Request $request,$id){
        
        $cap = Capitalization::where('code',CustomHelper::decrypt($id))->first();
                
        if($cap){
            $data = [
                'title'     => 'Print Capitalization',
                'data'      => $cap
            ];

            return view('admin.approval.capitalization', $data);
        }else{
            abort(404);
        }
    }

    public function viewJournal(Request $request,$id){
        $query = Capitalization::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' =>  $query->lookable_id ? $query->lookable->code : '-',
            ];
            $string='';
            foreach($query->journal->journalDetail()->orderBy('id')->get() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.$row->coa->company->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->name : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';
            }
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

}