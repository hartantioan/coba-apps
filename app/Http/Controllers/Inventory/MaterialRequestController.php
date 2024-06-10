<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportMaterialRequest;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\Item;
use App\Models\Line;
use App\Models\LandedCost;
use App\Models\Machine;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ItemStock;
use App\Models\GoodIssueRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\UserDateUser;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\MaterialRequestDetail;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\Place;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\Department;
use App\Models\Division;
use App\Models\ItemUnit;
use App\Models\Menu;
use App\Exports\ExportOutstandingMaterialRequest;
use App\Exports\ExportMaterialRequestTransactionPage;
use App\Models\MenuUser;

class MaterialRequestController extends Controller
{
    protected $dataplaces, $lasturl, $mindate, $maxdate, $dataplacecode, $datawarehouses;

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
            'title'     => 'Item Request',
            'content'   => 'admin.inventory.request',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'=> Division::where('status','1')->orderBy('name')->get(),
            'line'      => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'machine'   => Machine::where('status','1')->orderBy('name')->get(),
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
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
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MaterialRequest::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })
        ->whereHas('materialRequestDetail',function($query){
            $query->whereIn('warehouse_id',$this->datawarehouses);
        })
        ->count();
        
        $query_data = MaterialRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('materialRequestDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
            ->whereHas('materialRequestDetail',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MaterialRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('materialRequestDetail',function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
            ->whereHas('materialRequestDetail',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $dis = '';
                if($val->isOpenPeriod()){

                    $dis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                   
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'SYSTEM' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'SYSTEM' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? 'SYSTEM' : 'SYSTEM'
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  lime white-text btn-small" data-popup="tooltip" title="Preview Print Multi Language" onclick="whatPrintingChi(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat indigo accent-2 white-text btn-small" data-popup="tooltip" title="Salin" onclick="duplicate(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">content_copy</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat red accent-2 white-text btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
                        <button type="button" class="btn-floating mb-1 btn-flat yellow accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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

    public function rowDetail(Request $request)
    {
        $data   = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1"> <div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="16">Daftar Item (Stok yang tampil adalah stok realtime pada saat dokumen dibuat)</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Stok</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan 1</th>
                                <th class="center-align">Keterangan 2</th>
                                <th class="center-align">Tgl.Dipakai</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                                <th class="center-align">Requester</th>
                                <th class="center-align">Status</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        foreach($data->materialRequestDetail as $key => $row){
            $totalqty+=$row->qty;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td>'.$row->item->code.' - '.$row->item->name.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->getStockNow($row->qty_conversion)).'</td>
                <td class="center-align">'.$row->itemUnit->unit->code.'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="center-align">'.date('d/m/Y',strtotime($row->required_date)).'</td>
                <td class="center-align">'.$row->place->code.'</td>
                <td class="center-align">'.$row->warehouse->name.'</td>
                <td class="center-align">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td class="center-align">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="center-align">'.($row->department()->exists() ? $row->department->name : '-').'</td>
                <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td class="">'.$row->requester.'</td>
                <td class="center-align" style="font-size:20px !important;">'.$row->status().'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . CustomHelper::formatConditionalQty($totalqty). '</td>
            </tr>  
        ';
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s6 mt-1"><table style="min-width:100%;max-width:100%;">
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
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function voidStatus(Request $request){
        $query = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new MaterialRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the Item Request data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Item Request No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);

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
                $pr = MaterialRequest::where('code',$row)->first();
                
                if($pr){
                    $pdf = PrintHelper::print($pr,'Item Request','a5','landscape','admin.print.inventory.request_individual');
                  
                    
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

            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }
        
		
		return response()->json($response);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			            => $request->temp ? ['required', Rule::unique('material_requests', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:material_requests,code',
            */ 'company_id'                => 'required',
			'post_date' 				=> 'required',
            'note'		                => 'required',
            'arr_satuan'                => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_warehouse'             => 'required|array',
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 	                => 'Kode tidak boleh kosong.',
            /* 'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'arr_satuan.array'                  => 'Satuan harus dalam bentuk array.',
            'arr_satuan.required'               => 'Satuan tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'arr_place.required'                => 'Penempatan tujuan tidak boleh kosong.',
            'arr_place.array'                   => 'Penempatan tujuan harus dalam bentuk array.',
            'arr_warehouse.required'            => 'Penempatan gudang tidak boleh kosong.',
            'arr_warehouse.array'               => 'Penempatan gudang harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedQty = true;

            foreach($request->arr_qty as $key => $row){
                if(str_replace(',','.',str_replace('.','',$row)) == 0){
                   $passedQty = false; 
                }
            }

            if(!$passedQty){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Qty tidak boleh 0.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = MaterialRequest::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->hasChildDocument()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Item Request telah dipakai pada dokumen lain, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','2','6'])){
                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->company_id = $request->company_id;
                        $query->status = '1';
                        $query->save();

                        foreach($query->materialRequestDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Item Request sudah SELESAI, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=MaterialRequest::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = MaterialRequest::create([
                        'code'			=> $newCode,
                        'user_id'		=> session('bo_id'),
                        'company_id'    => $request->company_id,
                        'status'        => '1',
                        'post_date'     => $request->post_date,
                        'note'          => $request->note,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                DB::beginTransaction();
                try {
                    $grandtotal = 0;
                    foreach($request->arr_item as $key => $row){
                        $item = Item::find(intval($row));
                        $itemUnit = ItemUnit::find(intval($request->arr_satuan[$key]));
                        $price = $item->priceNow($request->arr_place[$key],date('Y-m-d'));
                        $total = $price * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) * $itemUnit->conversion;
                        $grandtotal += $total;
                        $totalQty = ItemStock::where('item_id',$row)->where('place_id',$request->arr_place[$key])->where('warehouse_id',$request->arr_warehouse[$key])->sum('qty');
                        $purchaseQty = $totalQty > 0 ? $totalQty / $itemUnit->conversion : 0;
                        MaterialRequestDetail::create([
                            'material_request_id'   => $query->id,
                            'item_id'               => $row,
                            'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'stock'                 => $purchaseQty,
                            'item_unit_id'          => $request->arr_satuan[$key],
                            'qty_conversion'        => $itemUnit->conversion,
                            'note'                  => $request->arr_note[$key],
                            'note2'                 => $request->arr_note2[$key],
                            'required_date'         => $request->arr_required_date[$key],
                            'place_id'              => $request->arr_place[$key],
                            'warehouse_id'          => $request->arr_warehouse[$key],
                            'line_id'               => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                            'machine_id'            => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                            'department_id'         => $request->arr_department[$key] ? $request->arr_department[$key] : NULL,
                            'project_id'            => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                            'requester'             => $request->arr_requester[$key],
                            'total'                 => $total,
                        ]);
                    }
                    MaterialRequest::find($query->id)->update([
                        'grandtotal'    => $grandtotal,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Item Request No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new MaterialRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Item Request.');

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
        $pr = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $pr['code_place_id'] = substr($pr->code,7,2);

        $arr = [];

        foreach($pr->materialRequestDetail as $row){
            $arr[] = [
                'item_id'           => $row->item_id,
                'item_name'         => $row->item->code.' - '.$row->item->name,
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'qty_stock'         => $row->item_id ? CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion) : '-',
                'unit_stock'        => $row->item_id ? $row->item->uomUnit->code : '-',
                'item_unit_id'      => $row->item_unit_id,
                'note'              => $row->note ? $row->note : '',
                'note2'             => $row->note2 ? $row->note2 : '',
                'date'              => $row->required_date,
                'place_id'          => $row->place_id,
                'warehouse_id'      => $row->warehouse_id,
                'line_id'           => $row->line_id,
                'machine_id'        => $row->machine_id,
                'department_id'     => $row->department_id,
                'requester'         => $row->requester ? $row->requester : '',
                'stock_list'        => $row->item->currentStockPurchase($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $row->item->warehouseList(),
                'project_id'        => $row->project()->exists() ? $row->project->id : '',
                'project_name'      => $row->project()->exists() ? $row->project->name : '',
                'buy_units'         => $row->item->arrBuyUnits(),
            ];
        }

        $pr['details'] = $arr;
        				
		return response()->json($pr);
    }

    public function getCode(Request $request){
        $code = MaterialRequest::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function destroy(Request $request){
        $query = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Item Request sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->hasChildDocument()){
            return response()->json([
                'status'  => 500,
                'message' => 'Data telah digunakan pada form lainnya.'
            ]);
        }

        if($query->delete()) {
            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
            
            $query->materialRequestDetail()->delete();
            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new MaterialRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Item Request data');

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
    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $mr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/purchase/material_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        
        $data_go_chart[]=$mr;
        
        
        if($query) {
            
            //Pengambilan Main Branch beserta id terkait
            
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_mr',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2;          
            
            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){
                
                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }
                
                }
                $new_array = array_values($new_array);
                return $new_array;
            }

           
            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');
  

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link,
            ];
            
        } else {
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    

    public function approval(Request $request,$id){
        
        $pr = MaterialRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Item Request',
                'data'      => $pr
            ];

            return view('admin.approval.material_request', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = MaterialRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Item Request','a5','landscape','admin.print.inventory.request_individual');
            
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
        
            $document_po = PrintHelper::savePrint($content);
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function printIndividualChi(Request $request,$id){
        
        $pr = MaterialRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $pdf = PrintHelper::print($pr,'Item Request','a4','portrait','admin.print.inventory.request_individual_chi');
            
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            
            $content = $pdf->download()->getOriginalContent();
            
        
            $document_po = PrintHelper::savePrint($content);
    
            return $document_po;
        }else{
            abort(404);
        }
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
                        $query = MaterialRequest::where('code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Item Request','a5','landscape','admin.print.inventory.request_individual');
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
                    
                    $document_po = PrintHelper::savePrint($result);
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
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
                        $query = MaterialRequest::where('code', 'LIKE', '%'.$merged)->first();
                        if($query){
                            
                            $pdf = PrintHelper::print($query,'Item Request','a5','landscape','admin.print.inventory.request_individual');
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
                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function export(Request $request){
        $menu = Menu::where('url','material_request')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
        $modedata = $menuUser->mode ?? '';
        $nominal = $menuUser->show_nominal ?? '';
		return Excel::download(new ExportMaterialRequest($post_date,$end_date,$mode,$modedata,$nominal,$this->datawarehouses), 'item_request_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportMaterialRequestTransactionPage($search,$post_date,$end_date,$status,$modedata,$this->datawarehouses), 'purchase_request_'.uniqid().'.xlsx');
    }

    public function getOutstanding(Request $request){
       
		return Excel::download(new ExportOutstandingMaterialRequest($this->datawarehouses), 'item_request_'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = MaterialRequest::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new MaterialRequest())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Material Request data');
    
                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }
}