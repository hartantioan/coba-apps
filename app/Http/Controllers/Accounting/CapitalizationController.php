<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Division;
use App\Models\Menu;
use App\Models\Place;
use App\Models\User;
use App\Models\Asset;
use App\Models\Capitalization;
use App\Models\CapitalizationDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCapitalization;
use App\Exports\ExportCapitalizationTransactionPage;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;
use App\Models\Line;
use App\Models\Machine;
use App\Models\MenuUser;
use App\Models\Warehouse;

class CapitalizationController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Kapitalisasi Aset',
            'content'       => 'admin.accounting.capitalization',
            'company'       => Company::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Division::where('status','1')->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = Capitalization::generateCode($request->val);
        				
		return response()->json($code);
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

        $total_data = Capitalization::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
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
                    $query->whereIn('status', $request->status);
                }

                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $query->whereIn('status', $request->status);
                }

                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    $val->currency->code.' - '.$val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'sistem' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'sistem' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? null : null
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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
            'code'			        => $request->temp ? ['required', Rule::unique('capitalizations', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|unique:capitalizations,code',
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
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.unique'                       => 'Kode telah dipakai.',
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

                $approved = false;
                $revised = false;

                if($query->approval()){
                    foreach ($query->approval() as $detail){
                        foreach($detail->approvalMatrix as $row){
                            if($row->approved){
                                $approved = true;
                            }

                            if($row->revised){
                                $revised = true;
                            }
                        }
                    }
                }

                if($approved && !$revised){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Kapitalisasi telah diapprove, anda tidak bisa melakukan perubahan.'
                    ]);
                }
                if(!CustomHelper::checkLockAcc($query->post_date)){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                    ]);
                }
                if(in_array($query->status,['1','6'])){

                    $query->code = $request->code;
                    $query->user_id = session('bo_id');
                    $query->company_id = $request->company_id;
                    $query->currency_id = $request->currency_id;
                    $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                    $query->post_date = $request->post_date;
                    $query->note = $request->note;
                    $query->grandtotal = $grandtotal;
                    $query->status = '1';
                    $query->save();

                    foreach($query->capitalizationDetail as $row){
                        $row->delete();
                    }

                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status kapitalisasi sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
			}else{
                $query = Capitalization::create([
                    'code'			=> $request->code,
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
                        'place_id'              => $request->arr_place[$key] ? $request->arr_place[$key] : NULL,
                        'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                        'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                        'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                        'project_id'            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                        'cost_distribution_id'  => $request->arr_cost_distribution_cost[$key] ? $request->arr_cost_distribution_cost[$key] : NULL,
                        'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                        'unit_id'               => $request->arr_unit[$key],
                        'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                        'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                        'note'                  => $request->arr_note[$key]
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
        $data   = Capitalization::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
            $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser;
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">
                    <div class="col s12">'.$data->code.$x.'</div>
                    <div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Aset</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Unit</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        $total_qty = 0 ;
        $total_harga = 0 ;
        $totals= 0 ;
        foreach($data->capitalizationDetail as $key => $row){
            $total_qty += $row->qty;
            $total_harga += $row->price;
            $totals+=$row->total;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->asset->code.' - '.$row->asset->name.'</td>
                <td>'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td>'.($row->warehouse()->exists() ? $row->warehouse->name : '-').'</td>
                <td>'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td>'.($row->machine()->exists() ? $row->machine->code : '-').'</td>
                <td>'.($row->department()->exists() ? $row->department->name : '-').'</td>
                <td>'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td>'.($row->costDistribution()->exists() ? $row->costDistribution->name : '-').'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td>'.$row->note.'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="9"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_harga, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . $total_qty . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;"></td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>     
        </tr>';
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                                <th class="center-align">Tanggal</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';
    
                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }
    
                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
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
        $cap['currency_rate'] = number_format($cap->currency_rate,2,',','.');
        $cap['grandtotal'] = number_format($cap->grandtotal,2,',','.');
        $cap['code_place_id'] = substr($cap->code,7,2);

        $arr = [];
        
        foreach($cap->capitalizationDetail as $row){
            $arr[] = [
                'asset_id'              => $row->asset_id,
                'asset_code'            => $row->asset->code,
                'asset_name'            => $row->asset->name,
                'place_name'            => $row->asset->place->name,
                'place_code'            => $row->asset->place->code,
                'place_id'              => $row->place_id ? $row->place_id : '',
                'line_id'               => $row->line_id ? $row->line_id : '',
                'machine_id'            => $row->machine_id ? $row->machine_id : '',
                'department_id'         => $row->department_id ? $row->department_id : '',
                'project_id'            => $row->project()->exists() ? $row->project_id : '',
                'project_name'          => $row->project()->exists() ? $row->project->name : '',
                'cost_distribution_id'  => $row->costDistribution()->exists() ? $row->cost_distribution_id : '',
                'cost_distribution_name'=> $row->costDistribution()->exists() ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'qty'                   => CustomHelper::formatConditionalQty($row->qty),
                'unit_id'               => $row->unit_id,
                'unit_name'             => $row->unit->name,
                'price'                 => number_format($row->price,2,',','.'),
                'total'                 => number_format($row->total,2,',','.'),
                'note'                  => $row->note ? $row->note : '',
            ];
        }

        $cap['details'] = $arr;
        				
		return response()->json($cap);
    }

    public function voidStatus(Request $request){
        $query = Capitalization::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

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
                if(in_array($query->status,['2','3'])){
                    CustomHelper::removeJournal('capitalizations',$query->id);
                }
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

        $approved = false;
        $revised = false;

        if($query->approval()){
            foreach ($query->approval() as $detail){
                foreach($detail->approvalMatrix as $row){
                    if($row->approved){
                        $approved = true;
                    }

                    if($row->revised){
                        $revised = true;
                    }
                }
            }
        }

        if($approved && !$revised){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval('capitalizations',$query->id);
            
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

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = Capitalization::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Capitalization',
                        'data'      => $pr
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.accounting.capitalization_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();

            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $result);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{   
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);
                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = Capitalization::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Kapitalisasi',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.capitalization_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    
        
                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = Capitalization::where('Code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Kapitalisasi',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.accounting.capitalization_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportCapitalization($post_date,$end_date,$mode), 'capitalization_'.uniqid().'.xlsx');
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

    public function printIndividual(Request $request,$id){
        
        $pr = Capitalization::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $data = [
                'title'     => 'Print Capitalization',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.accounting.capitalization_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
  
            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
           
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = Capitalization::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += $row->nominal;
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += $row->nominal;
                }
                
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td class="center-align">'.($row->note ? $row->note : '').'</td>
                    <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';

                
            }
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }


    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportCapitalizationTransactionPage($search,$post_date,$end_date,$status,$modedata), 'purchase_request_'.uniqid().'.xlsx');
    }

}