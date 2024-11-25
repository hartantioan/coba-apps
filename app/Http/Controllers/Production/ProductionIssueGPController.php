<?php

namespace App\Http\Controllers\Production;

use App\Exports\ExportIssueGlaze;
use App\Helpers\CustomHelper;
use App\Helpers\TreeHelper;
use App\Http\Controllers\Controller;
use App\Models\IssueGlaze;
use App\Models\IssueGlazeDetail;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Helpers\PrintHelper;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\User;
use App\Models\Company;
use App\Models\Line;
use App\Models\Place;
use App\Models\Area;
use App\Models\UsedData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductionIssueGPController extends Controller
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
        $data = [
            'title'         => 'Issue Glaze Prep',
            'content'       => 'admin.production.issue_glaze',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'line'          => Line::where('status','1')->whereIn('place_id',$this->dataplaces)->get(),
            'area'          => Area::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'warehouse'     => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
            'unit'          => Unit::where('status','1')->get(),
            'items'         => Item::whereIn('code',['102.02.0032','102.02.0034'])->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = IssueGlaze::generateCode($request->val);

		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'place_id',
            'line_id',
            'document',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = IssueGlaze::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();

        $query_data = IssueGlaze::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->line){
                    $query->where('line_id', $request->line);
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = IssueGlaze::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('code','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->line){
                    $query->where('line_id', $request->line);
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
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->item->code.' - '.$val->item->name,
                    CustomHelper::formatConditionalQty($val->qty),
                    $val->ballmill_no,
                    $val->line->code,
                    $val->place->code,
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
            'code'                      => 'required',
            'code_place_id'             => 'required',
            'company_id'			    => 'required',
            'place_id'                  => 'required',
            'ballmill_no'               => 'required',
            'line_id'                   => 'required',
            'item_id'                   => 'required',
            'post_date'		            => 'required',
        ], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'place_id.required'                 => 'Plant tidak boleh kosong.',
            'ballmill_no.required'              => 'Ballmill tidak boleh kosong.',
            'line_id.required'                  => 'Line tidak boleh kosong.',
            'item_id.required'                  => 'Item tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passedQty = true;
            $arrNotPassedQty = [];
            $sameItem = false;
            $arrItem = [];

            foreach($request->arr_qty as $key => $row){
                if($request->arr_lookable_type[$key] == 'items'){
                    if(in_array($request->arr_lookable_id[$key],$arrItem)){
                        $sameItem = true;
                    }
                    $item = Item::find($request->arr_lookable_id[$key]);
                    $itemstock = $item->itemCogs()->where('place_id',$request->arr_place[$key])->where('warehouse_id',$request->arr_warehouse[$key])->whereDate('date','<=',$request->post_date)->orderByDesc('date')->orderByDesc('id')->first();
                    $qty = str_replace(',','.',str_replace('.','',$row));
                    if($itemstock){
                        $qtyFinal = $itemstock->infoFg()['qty'];
                        if(round($qty,3) > round($qtyFinal,3)){
                            $passedQty = false;
                            $arrNotPassedQty[] = $itemstock->item->code.' - '.$itemstock->item->name.' - Qty Dibutuhkan : '.CustomHelper::formatConditionalQty(round($qty,3)).' - Qty Stok : '.CustomHelper::formatConditionalQty(round($qtyFinal,3));
                        }
                    }else{
                        $passedQty = false;
                        $arrNotPassedQty[] = $item->code.' - '.$item->name;
                    }
                    $arrItem[] = $request->arr_lookable_id[$key];
                }
            }

            if($sameItem){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf terdapat item yang sama. Silahkan hapus item yang sama dan jadikan satu.'
                ]);
            }

            if(!$passedQty){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf, item '.implode(' | ',$arrNotPassedQty).' tidak mencukupi stok yang ada. Silahkan atur qty yang ingin diproduksi.'
                ]);
            }

            $itemHeader = Item::find($request->item_id);

            $itemStockHeader = ItemStock::where('item_id',$request->item_id)->where('place_id',$request->place_id)->where('warehouse_id',$itemHeader->warehouse())->first();

            if($request->temp){
                $query = IssueGlaze::where('code',CustomHelper::decrypt($request->temp))->first();

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
                        'message' => 'Production Issue telah diapprove, anda tidak bisa melakukan perubahan.'
                    ]);
                }

                if(in_array($query->status,['1','6'])){
                    if($request->has('file')) {
                        if($query->document){
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                        }
                        $document = $request->file('file')->store('public/issue_glazes');
                    } else {
                        $document = $query->document;
                    }

                    $query->user_id = session('bo_id');
                    $query->code = $request->code;
                    $query->company_id = $request->company_id;
                    $query->place_id = $request->place_id;
                    $query->line_id = $request->line_id;
                    $query->ballmill_no = $request->ballmill_no;
                    $query->item_id = $request->item_id;
                    $query->item_stock_id = $itemStockHeader->id;
                    $query->post_date = $request->post_date;
                    $query->document = $document;
                    $query->note = $request->note;
                    $query->status = '1';

                    $query->save();

                    foreach($query->issueGlazeDetail as $row){
                        $row->delete();
                    }
                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status Issue Glaze Prep sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }else{

                $lastSegment = $request->lastsegment;
                $menu = Menu::where('url', $lastSegment)->first();
                $newCode = IssueGlaze::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                $query = IssueGlaze::create([
                    'code'			            => $newCode,
                    'user_id'		            => session('bo_id'),
                    'company_id'                => $request->company_id,
                    'place_id'                  => $request->place_id,
                    'line_id'                   => $request->line_id,
                    'ballmill_no'               => $request->ballmill_no,
                    'item_id'                   => $request->item_id,
                    'item_stock_id'             => $itemStockHeader->id,
                    'post_date'                 => $request->post_date,
                    'document'                  => $request->file('file') ? $request->file('file')->store('public/issue_glazes') : NULL,
                    'note'                      => $request->note,
                    'status'                    => '1',
                ]);
            }

            if($query) {
                $grandtotal = 0;
                $qtyAll = 0;
                foreach($request->arr_qty as $key => $row){
                    $nominal = 0;
                    $total = 0;
                    $itemstock = NULL;
                    if($request->arr_lookable_type[$key] == 'items'){
                        $item = Item::find($request->arr_lookable_id[$key]);
                        if($item){
                            $itemstock = $item->itemStock()->where('place_id',$request->arr_place[$key])->where('warehouse_id',$request->arr_warehouse[$key])->first();
                            $nominal = $itemstock->priceDate($query->post_date);
                            $total = round(str_replace(',','.',str_replace('.','',$row)) * $nominal,2);
                            $grandtotal += $total;
                        }
                    }
                    $querydetail = IssueGlazeDetail::create([
                        'issue_glaze_id'                => $query->id,
                        'lookable_type'                 => $request->arr_lookable_type[$key] == 'manual' ? NULL : $request->arr_lookable_type[$key],
                        'lookable_id'                   => $request->arr_lookable_id[$key],
                        'note'                          => $request->arr_note[$key],
                        'qty'                           => str_replace(',','.',str_replace('.','',$row)),
                        'unit_id'                       => $request->arr_unit_id[$key] ?? NULL,
                        'place_id'                      => $request->arr_place[$key],
                        'warehouse_id'                  => $request->arr_warehouse[$key],
                        'item_stock_id'                 => $itemstock ? $itemstock->id : NULL,
                        'total'                         => $total,
                    ]);
                    $qtyAll += str_replace(',','.',str_replace('.','',$row));
                }

                $query->update([
                    'grandtotal'    => $grandtotal,
                    'qty'           => $qtyAll,
                ]);

                CustomHelper::sendApproval($query->getTable(),$query->id,'Issue Glaze Prep No. '.$query->code);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Issue Glaze Prep No. '.$query->code,'Pengajuan Issue Glaze Prep No. '.$query->code,session('bo_id'));

                activity()
                    ->performedOn(new IssueGlaze())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit issue glaze prep.');

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
        $ig = IssueGlaze::where('code',CustomHelper::decrypt($request->id))->first();
        $ig['code_place_id'] = substr($ig->code,7,2);

        if($ig){
            $arr = [];
            foreach($ig->issueGlazeDetail()->orderBy('id')->get() as $row){
                $arr[] = [
                    'lookable_id'               => $row->lookable_type == 'items' ? $row->lookable_id : '',
                    'lookable_type'             => $row->lookable_type == 'items' ? $row->lookable_type : 'manual',
                    'lookable_name'             => $row->lookable_type == 'items' ? $row->lookable->code.' - '.$row->lookable->name : '',
                    'uom'                       => $row->lookable_type == 'items' ? $row->itemStock->item->uomUnit->code : '',
                    'unit_id'                   => $row->lookable_type == 'items' ? '' : $row->unit_id,
                    'unit_name'                 => $row->lookable_type == 'items' ? '' : $row->unit->code.' - '.$row->unit->name,
                    'qty'                       => CustomHelper::formatConditionalQty($row->qty),
                    'note'                      => $row->note ? $row->note : '',
                    'place_id'                  => $row->place_id,
                    'warehouse_id'              => $row->warehouse_id,
                    'list_warehouse'            => $row->lookable_type == 'items' ? $row->lookable->warehouseList() : [],
                ];
            }

            $ig['details'] = $arr;
            $result = [
                'status'    => 200,
                'data'      => $ig,
            ];
        }else{
            $result = [
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.'
            ];
        }
        
		return response()->json($result);
    }

    public function approval(Request $request,$id){

        $pr = IssueGlaze::where('code',CustomHelper::decrypt($id))->first();

        if($pr){
            $data = [
                'title'     => 'Issue Glaze',
                'data'      => $pr
            ];

            return view('admin.approval.issue_glaze', $data);
        }else{
            abort(404);
        }
    }

    public function rowDetail(Request $request)
    {
        $data   = IssueGlaze::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;" class="bordered" id="table-detail-row">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="7" style="font-size:20px !important;">Daftar Item Issue (Terpakai)</th>
                            </tr>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Item</th>
                                <th class="center">Keterangan</th>
                                <th class="center">Qty</th>
                                <th class="center">Satuan</th>
                                <th class="center">Plant</th>
                                <th class="center">Gudang</th>
                            </tr>
                        </thead><tbody>';
        foreach($data->issueGlazeDetail()->orderBy('id')->get() as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key+1).'.</td>
                <td>'.($row->lookable_type == 'items' ? $row->lookable->code.' - '.$row->lookable->name : '-').'</td>
                <td>'.$row->note.'</td>
                <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.($row->lookable_type == 'items' ? $row->lookable->uomUnit->code : $row->unit->code).'</td>
                <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td class="center-align">'.($row->warehouse()->exists() ? $row->warehouse->name : '-').'</td>
            </tr>';
        }

        $string .= '<tr>
                <th class="right-align" colspan="3">TOTAL</th>
                <th class="right-align">'.CustomHelper::formatConditionalQty($data->qty).'</th>
                <th colspan="3"></th>
            </tr>';

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

        $string .= '</tbody></table></div></div>';

        return response()->json($string);
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $pr = IssueGlaze::where('code',CustomHelper::decrypt($id))->first();

        if($pr){
            $pdf = PrintHelper::print($pr,'Issue Glaze','a4','portrait','admin.print.production.issue_glaze_individual',$menuUser->mode);
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);$var_link=$document_po;

            return $document_po;
        }else{
            abort(404);
        }
    }

    public function voidStatus(Request $request){
        $query = IssueGlaze::where('code',CustomHelper::decrypt($request->id))->first();

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
                $tempStatus = $query->status;

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                if(in_array($tempStatus,['2','3'])){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                    CustomHelper::removeCogs($query->getTable(),$query->id);
                }

                activity()
                    ->performedOn(new IssueGlaze())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the issue glaze data');

                CustomHelper::sendNotification($query->getTable(),$query->id,'Issue Glaze No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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

    public function destroy(Request $request){
        $query = IssueGlaze::where('code',CustomHelper::decrypt($request->id))->first();

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

        if($query->delete()){

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            foreach($query->issueGlazeDetail as $row){
                $row->delete();
            }

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new IssueGlaze())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the issue glaze data');

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
        $query = IssueGlaze::where('code',CustomHelper::decrypt($request->id))->first();

        $data_go_chart=[];
        $data_link=[];


        if($query){
            $data_core = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/production/production_issue?code=".CustomHelper::encrypt($query->code),
            ];

            $data_go_chart[]= $data_core;
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_production_issue',$query->id);
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
                'link'    => $data_link
            ];
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = IssueGlaze::where('code',CustomHelper::decrypt($id))->first();
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
            foreach($query->journal->journalDetail()->orderBy('id')->get() as $key => $row){
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

    /* public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';

		return Excel::download(new ExportIssueGlazeTransactionPage($search,$status,$end_date,$start_date), 'production_schedule'.uniqid().'.xlsx');
    } */

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
        $menu = Menu::where('url','issue_gp')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $nominal = $menuUser->show_nominal ?? '';
        $line_id = $request->line_id ? $request->line_id : '';
		return Excel::download(new ExportIssueGlaze($post_date,$end_date,$mode,$nominal,$line_id), 'issue_glaze'.uniqid().'.xlsx');
    }
}
