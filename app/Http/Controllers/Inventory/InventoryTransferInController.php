<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\inventoryTransferOutDetail;
use App\Models\User;
use App\Models\Company;
use App\Helpers\CustomHelper;
use App\Exports\ExportInventoryTransferIn;

class InventoryTransferInController extends Controller
{
    protected $dataplaces, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index()
    {
        $data = [
            'title'     => 'Transfer Antar Gudang - Masuk',
            'content'   => 'admin.inventory.transfer_in',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function sendUsedData(Request $request){
        $ito = InventoryTransferOut::find($request->id);
        if(!$ito->used()->exists()){
            CustomHelper::sendUsedData($ito->getTable(),$request->id,'Form Inventori Transfer Masuk');
            return response()->json([
                'status'    => 200,
                'code'      => $ito->code,
                'id'        => $ito->id
            ]);
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Inventori Transfer no. '.$ito->used->lookable->code.' telah dipakai di '.$ito->used->ref.', oleh '.$ito->used->user->name.'.'
            ]);
        }
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData('inventory_transfer_outs',$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function getTotalTransferOut(Request $request){
        $total_data_out = InventoryTransferOut::where(function($query){
            $query->where(function($query){
                $query->whereIn('place_from',$this->dataplaces)
                    ->whereIn('warehouse_from',$this->datawarehouses);
            })->orWhere(function($query){
                $query->whereIn('place_to',$this->dataplaces)
                    ->whereIn('warehouse_to',$this->datawarehouses);
            });
        })
        ->whereDoesntHave('inventoryTransferIn')
        ->whereIn('status',['2','3'])
        ->whereDoesntHave('used')
        ->count();

        return response()->json($total_data_out);
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

        $total_data = InventoryTransferIn::whereHas('inventoryTransferOut',function($query){
            $query->where(function($query){
                $query->whereIn('place_to',$this->dataplaces)
                    ->whereIn('warehouse_to',$this->datawarehouses);
            });
        })->count();
        
        $query_data = InventoryTransferIn::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
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
                    $query->where('status', $request->status);
                }
            })
            ->whereHas('inventoryTransferOut',function($query){
                $query->where(function($query){
                    $query->whereIn('place_to',$this->dataplaces)
                        ->whereIn('warehouse_to',$this->datawarehouses);
                });
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = InventoryTransferIn::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('inventoryTransferDetail', function($query) use($search, $request){
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
                    $query->where('status', $request->status);
                }
            })
            ->whereHas('inventoryTransferOut',function($query){
                $query->where(function($query){
                    $query->whereIn('place_to',$this->dataplaces)
                        ->whereIn('warehouse_to',$this->datawarehouses);
                });
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d M Y',strtotime($val->post_date)),
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->inventoryTransferOut->code,
                    $val->inventoryTransferOut->placeFrom->name,
                    $val->inventoryTransferOut->warehouseFrom->name,
                    $val->inventoryTransferOut->placeTo->name,
                    $val->inventoryTransferOut->warehouseTo->name,
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
            'company_id'                => 'required',
            'inventory_transfer_out_id' => 'required',
			'post_date'		            => 'required',
		], [
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'inventory_transfer_out_id.required'=> 'Inventori Transfer Asal tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {
                if($request->temp){
                    
                    $query = InventoryTransferIn::where('code',CustomHelper::decrypt($request->temp))->first();

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/inventory_transfer_ins');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->inventory_transfer_out_id = $request->inventory_transfer_out_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();

                        foreach($query->inventoryTransferDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status barang transfer sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $query = InventoryTransferIn::create([
                        'code'			            => InventoryTransferIn::generateCode(),
                        'user_id'		            => session('bo_id'),
                        'company_id'		        => $request->company_id,
                        'inventory_transfer_out_id' => $request->inventory_transfer_out_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/inventory_transfer_ins') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {

                    CustomHelper::sendApproval('inventory_transfer_ins',$query->id,$query->note);
                    CustomHelper::sendNotification('inventory_transfer_ins',$query->id,'Barang Transfer - Masuk No. '.$query->code,$query->note,session('bo_id'));
                        
                    activity()
                        ->performedOn(new InventoryTransferIn())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit barang transfer masuk.');

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
                
                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function rowDetail(Request $request){
        $data   = InventoryTransferIn::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12 mt-1"><table style="max-width:800px;">
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
        
        if($data->approval()){                
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
        $iti = InventoryTransferIn::where('code',CustomHelper::decrypt($request->id))->first();
        $iti['inventory_transfer_out_name'] = $iti->inventoryTransferOut->code.' - '.$iti->inventoryTransferOut->user->name;

        $details = [];

        foreach($iti->inventoryTransferOut->inventoryTransferOutDetail as $row){
            $details[] = [
                'name'      => $row->item->name,
                'origin'    => $row->itemStock->place->name.' - '.$row->itemStock->warehouse->name,
                'qty'       => number_format($row->qty,3,',','.'),
                'unit'      => $row->item->uomUnit->code,
                'note'      => $row->note,
            ];
        }

        $iti['details'] = $details;
        				
		return response()->json($iti);
    }

    public function viewJournal(Request $request,$id){
        $query = InventoryTransferIn::where('code',CustomHelper::decrypt($id))->first();
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

    public function voidStatus(Request $request){
        $query = InventoryTransferIn::where('code',CustomHelper::decrypt($request->id))->first();
        
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

                if(in_array($query->status,['2','3','4','5'])){
                    CustomHelper::removeJournal('inventory_transfer_ins',$query->id);
                    CustomHelper::removeCogs('inventory_transfer_ins',$query->id);
                }
    
                activity()
                    ->performedOn(new InventoryTransferIn())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the inventory transfer in data');
    
                CustomHelper::sendNotification('inventory_transfer_ins',$query->id,'Inventori Transfer Masuk No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('inventory_transfer_ins',$query->id);
                
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
        $query = InventoryTransferIn::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Barang transfer telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Barang transfer sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            CustomHelper::removeApproval('inventory_transfer_ins',$query->id);

            activity()
                ->performedOn(new InventoryTransferIn())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the inventory transfer in data');

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

    public function approval(Request $request,$id){
        
        $gr = InventoryTransferOut::where('code',CustomHelper::decrypt($id))->first();
                
        if($gr){
            $data = [
                'title'     => 'Print Inventory Transfer Keluar',
                'data'      => $gr
            ];

            return view('admin.approval.inventory_transfer_out', $data);
        }else{
            abort(404);
        }
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
                $pr = InventoryTransferIn::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Inventory Transfer Out',
                        'data'      => $pr
                    ];
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.inventory.good_iventory_transfer_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
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

            Storage::put('public/pdf/bubla.pdf',$result);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
		
		return response()->json($response);

    }

    public function export(Request $request){
		return Excel::download(new ExportInventoryTransferIn($request->search,$request->status,$this->dataplaces,$this->datawarehouses), 'inventory_transfer_in_'.uniqid().'.xlsx');
    }
}