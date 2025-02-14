<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Support\Str;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\ApprovalTemplate;
use App\Models\ComplaintSales;
use App\Models\ComplaintSalesDetail;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\UsedData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ComplaintSalesController extends Controller
{
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();

        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Komplain Penjualan',
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->get(),
            'content'       => 'admin.sales.complaint_sales',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'document',
            'user_id',
            'account_id',
            'lookable_type',
            'lookable_id',
            'marketing_order_id_complaint',
            'note',
            'note_complaint',
            'solution',
            'post_date',
            'complaint_date',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ComplaintSales::count();

        $query_data = ComplaintSales::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note_complaint', 'like', "%$search%")
                            ->orWhere('solution', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('complaint_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }



            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ComplaintSales::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note_complaint', 'like', "%$search%")
                            ->orWhere('solution', 'like', "%$search%")
                            ->orWhere('note_external', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('complaint_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;

            foreach($query_data as $val) {
				$dis = '';
                if($val->lookable_type == 'undefined'){
                    $code = '';
                }else{
                    $code = $val->lookable->code;
                }
                /*  <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>*/
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->complaint_date)),
                    $val->attachments(),
                    $code,
                    $val->note,
                    $val->note_complaint,
                    $val->solution,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown accent-2 white-text btn-small" data-popup="tooltip" title="Done Status" onclick="doneStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">thumb_up</i></button>
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

    public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = ComplaintSales::generateCode($request->val);

		return response()->json($code);
    }

    public function getModSj(Request $request){
        $sj = MarketingOrderDeliveryProcess::find($request->id);
        if($sj){
            $mod = $sj->marketingOrderDelivery;
            $detail = [];
            foreach($mod->marketingOrderDeliveryDetail  as $row_detail){
                $box_conversion = $sj->marketingOrderDeliveryProcessDetail->first() ?
                        $sj->marketingOrderDeliveryProcessDetail->first()->itemStock->item->pallet->box_conversion :
                        null;
                $detail[] = [
                    'item' => $row_detail->item->name.' ukuran 1 item:'. number_format($row_detail->item->size->m2_conversion,2,',','.').' m2',
                    'id'   => $row_detail->id,
                    'lookable_type'=>'marketing_order_delivery_details',
                    'lookable_id'  =>$row_detail->id,
                    'm2_conversion'  =>$row_detail->item->size->m2_conversion,
                    'box_conversion'  =>$box_conversion,
                    'sale_conversion'=>$row_detail->item->itemUnitDefault()->conversion,

                ];
            }

            $so = [];


            $response = [
                'status' => 200,
                'detail'=>$detail,
                'sales_order'  =>$sj->getSalesOrderCode(),
                'customer'     =>$mod->customer->name,
                'grandtotal_so'=>number_format($sj->getGrandtotalSalesOrder(),2,',','.'),
                'qty_m2'       =>number_format($sj->totalQty(),2,',','.'),
                'box'          =>$sj->qtyPerShading(),
                'plant'        =>$mod->getPlaceCode(),
                'tgl_sj'       =>$sj->post_date,
            ];

        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data User Tidak Ditemukan'
            ];
        }
        return response()->json($response);
    }

    public function rowDetail(Request $request)
    {
        $data   = ComplaintSales::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.' - '.$data->account->name.$x.'</div><div class="col s12" style="overflow:auto;"><table style="min-width:2500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="23">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th>Item</th>
                                <th>Ketidaksesuaian Warna</th>
                                <th>Ketidaksesuaian Motif</th>
                                <th>Ketidaksesuaian Ukuran</th>
                                <th>Patah/Rusak</th>
                                <th>Ketidaksesuaian Qty</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->complaintSalesDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.($row->lookable->item->name).'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_color_mistake).'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_motif_mistake).'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_size_mistake).'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_broken).'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty_mistake).'</td>
            </tr>';
        }
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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

        $string .= '</tbody></table></div>
            ';


        return response()->json($string);
    }

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'account_id' 				=> 'required',
                'lookable_id'               => 'required',
                'note'			            => 'required',
                'solution'		            => 'required',
                'post_date'                     => 'required',
                'complaint_date'                       => 'required',
            ], [
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'account_id.required' 				=> 'PIC tidak boleh kosong.',
                'lookable_id.required' 			    => 'Dokumen berkaitan tidak boleh kosong.',
                'note.required' 			        => 'Keterangan tidak boleh kosong.',
                'solution.required' 			    => 'Solusi tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal Post tidak boleh kosong.',
                'complaint_date.required'		    => 'Tanggal Komplain tidak boleh kosong.',

            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                $percent = floatval($request->percent);
                if($percent == 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Tidak ada data komplain jumlah percent 0.'
                    ]);
                }
                if($request->temp){
                    $query = ComplaintSales::where('code',CustomHelper::decrypt($request->temp))->first();

                    $approved = false;
                    $revised = false;
                    if($query->marketing_order_id_complaint){
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
                    }


                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Sales Order telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','2','6'])){
                        if($request->has('file')) {

                            if($query->document){
                                $arrFile = explode(',',$query->document);
                                foreach($arrFile as $row){
                                    if(Storage::exists($row)){
                                        Storage::delete($row);
                                    }
                                }
                            }

                            $arrFile = [];

                            foreach($request->file('file') as $key => $file)
                            {
                                $arrFile[] = $file->store('public/complaint_sales');
                            }

                            $document = implode(',',$arrFile);
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->post_date = $request->post_date;
                        $query->complaint_date = $request->complaint_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->note_complaint = $request->note_complaint;
                        $query->lookable_id = $request->lookable_id;
                        $query->lookable_type = 'marketing_order_delivery_processes';
                        $query->solution = $request->solution;

                        $query->save();

                        foreach($query->complaintSalesDetail as $row){
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status sales order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $fileUpload = '';
                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/complaint_sales');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ComplaintSales::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = ComplaintSales::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'            => $request->account_id,
                        'post_date'            => $request->post_date,
                        'complaint_date'       => $request->complaint_date,
                        'document'             => $fileUpload,
                        'note'                 => $request->note,
                        'note_complaint'       => $request->note_complaint,
                        'solution'             => $request->solution,
                        'lookable_id'          => $request->lookable_id,
                        'lookable_type'        => 'marketing_order_delivery_processes',
                        'marketing_order_id_complaint'             => $request->marketing_order_id_complaint,
                        'status'                    => '1',
                    ]);
                }
                if($query) {
                    foreach($request->arr_lookable_type as $key => $row){
                        info($request->arr_production_batch_code[$key]);
                        ComplaintSalesDetail::create([
                            'complaint_sales_id'=> $query->id,
                            'lookable_type'=> 'marketing_order_delivery_details',
                            'lookable_id'=> $request->arr_lookable_id[$key],
                            'production_batch_code'=> $request->arr_production_batch_code[$key],
                            'qty_color_mistake'=> str_replace(',', '.',$request->arr_qty_color_mistake[$key]),
                            'qty_motif_mistake'=> str_replace(',', '.',$request->arr_qty_motif_mistake[$key]),
                            'qty_size_mistake'=> str_replace(',', '.',$request->arr_qty_size_mistake[$key]),
                            'qty_broken'=> str_replace(',', '.', $request->arr_qty_broken[$key]),
                            'qty_mistake'=> str_replace(',', '.',$request->arr_qty_mistake[$key]),
                            'note'=> $request->arr_note[$key],
                        ]);
                    }

                    $resetdata = ApprovalSource::where('lookable_type',$query->getTable())->where('lookable_id',$query->id)->get();
                    foreach($resetdata as $rowreset){
                        foreach($rowreset->approvalMatrix as $detailmatrix){
                            $detailmatrix->delete();
                        }
                        $rowreset->delete();
                    }
                    $data = DB::table($query->getTable())->where('id',$query->id)->first();
                    $approvalTemplate = ApprovalTemplate::where('status','1')
                    ->whereHas('approvalTemplateMenu',function($querys) use($query){
                        $querys->where('table_name',$query->getTable());
                    })
                    ->whereHas('approvalTemplateOriginator',function($query){
                        $query->where('user_id',session('bo_id'));
                    })->get();

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
                        if($approved && !$revised){
                            $query->status = '3';

                            $query->save();
                        }
                    }else{
                        foreach($approvalTemplate as $row){

                            $source = ApprovalSource::create([
                                'code'			=> strtoupper(uniqid()),
                                'user_id'		=> session('bo_id'),
                                'date_request'	=> date('Y-m-d H:i:s'),
                                'lookable_type'	=> $query->getTable(),
                                'lookable_id'	=> $query->id,
                                'note'			=> $query->note,
                            ]);

                            $passed = true;

                            if($passed == true){

                                $count = 0;

                                foreach($row->approvalTemplateStage()->orderBy('id')->get() as $rowTemplateStage){
                                        $status = $count == 0 ? '1': '0';
                                        $check = true;
                                        if($check){
                                            if($percent > 5){
                                                ApprovalMatrix::create([
                                                    'code'							=> strtoupper(Str::random(30)),
                                                    'approval_template_stage_id'	=> $rowTemplateStage->id,
                                                    'approval_source_id'			=> $source->id,
                                                    'user_id'						=> 354,
                                                    'date_request'					=> date('Y-m-d H:i:s'),
                                                    'status'						=> $status
                                                ]);
                                            }else{
                                                ApprovalMatrix::create([
                                                    'code'							=> strtoupper(Str::random(30)),
                                                    'approval_template_stage_id'	=> $rowTemplateStage->id,
                                                    'approval_source_id'			=> $source->id,
                                                    'user_id'						=> 746,
                                                    'date_request'					=> date('Y-m-d H:i:s'),
                                                    'status'						=> $status
                                                ]);
                                            }
                                        }
                                    }

                                }
                        }
                    }



                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Komplain Sales. '.$query->code,$query->note.' - '.$query->note_complaint,session('bo_id'));

                    activity()
                        ->performedOn(new ComplaintSales())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit complaint sales.');

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

            DB::commit();
        }catch(\Exception $e){
            info($e->getMessage());
            DB::rollback();
        }

		return response()->json($response);
    }

    public function show(Request $request){
        $po = ComplaintSales::where('code',CustomHelper::decrypt($request->id))->first();
        $po['account_name'] = $po->account->name;
        $po['post_date']=date('Y-m-d',strtotime($po->post_date));
        $po['complaint_date']=date('Y-m-d',strtotime($po->complaint_date));
        $po['lookable_date']=date('Y-m-d',strtotime($po->lookable->post_date));
        $po['plant']    = $po->lookable->marketingOrderDelivery->getPlaceCode();
        $po['sj_code']  = $po->lookable->getSalesOrderCode();
        $po['choosen_sj']  = $po->lookable->code;
        $po['choosen_so']  = $po->marketingOrder->code ?? null;
        $po['customer']  = $po->lookable->marketingOrderDelivery->customer->name;
        $po['grandtotal_so']  = number_format($po->lookable->getGrandtotalSalesOrder(),2,',','.');
        $po['qty_m2']  = number_format($po->lookable->totalQty(),2,',','.');
        $po['box']  = $po->lookable->qtyPerShading();
        $arr = [];

        foreach($po->complaintSalesDetail as $row){
            $arr[] = [
                'id'                    => $row->id,
                'lookable_id'               => $row->lookable_id,
                'item'                      => $row->lookable->item->name,
                'production_batch_code'                      => $row->production_batch_code,
                'qty_color_mistake'                   => CustomHelper::formatConditionalQty($row->qty_color_mistake),
                'qty_motif_mistake'               => CustomHelper::formatConditionalQty($row->qty_motif_mistake),
                'qty_size_mistake'                  => CustomHelper::formatConditionalQty($row->qty_size_mistake),
                'qty_broken'                 => CustomHelper::formatConditionalQty($row->qty_broken),
                'qty_mistake'        => CustomHelper::formatConditionalQty($row->qty_mistake),
                'note'            => $row->note
            ];
        }

        $po['details'] = $arr;

		return response()->json($po);
    }

    public function approval(Request $request,$id){

        $pr = ComplaintSales::where('code',CustomHelper::decrypt($id))->first();

        if($pr){
            $data = [
                'title'     => 'Print Approval',
                'data'      => $pr
            ];

            return view('admin.approval.complaint_sales', $data);
        }else{
            abort(404);
        }
    }

    public function doneStatus(Request $request){
        $query = ComplaintSales::where('code',CustomHelper::decrypt($request->id))->first();

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


        if(in_array($query->status,['3'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query) {

            if($approved && !$revised){
                $query->update([
                    'status'     => '3',
                ]);
            }


            activity()
                ->performedOn(new ComplaintSales())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('update done the complaint sales order data');

            $response = [
                'status'  => 200,
                'message' => 'Data updated successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to update.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = ComplaintSales::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {

            $query->complaintSalesDetail()->delete();

            CustomHelper::removeApproval('complaint_sales',$query->id);

            activity()
                ->performedOn(new ComplaintSales())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the ComplaintSales order data');

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
}
