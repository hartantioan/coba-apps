<?php

namespace App\Http\Controllers\Purchase;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Exports\ExportPurchaseRequest;

class PurchaseRequestController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'     => 'Purchase Request',
            'content'   => 'admin.purchase.request',
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'=> Department::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'post_date',
            'due_date',
            'required_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $user = User::find(session('bo_id'));

        $dataplaces = $user->userPlaceArray();

        $total_data = PurchaseRequest::whereIn('place_id',$dataplaces)->count();
        
        $query_data = PurchaseRequest::where(function($query) use ($search, $request, $dataplaces) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseRequestDetail',function($query) use($search, $request){
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

                if($request->status){
                    $query->where('status', $request->status);
                }

                $query->whereIn('place_id',$dataplaces);
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseRequest::where(function($query) use ($search, $request, $dataplaces) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseRequestDetail',function($query) use($search, $request){
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

                if($request->status){
                    $query->where('status', $request->status);
                }

                $query->whereIn('place_id',$dataplaces);
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->user->name,
                    $val->code,
                    $val->place->name.' - '.$val->place->company->name,
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->due_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat red accent-2 white-text btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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
        $data   = PurchaseRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Tgl.Dipakai</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->purchaseRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->item->buyUnit->code.'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.date('d M Y',strtotime($row->required_date)).'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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

    public function voidStatus(Request $request){
        $query = PurchaseRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->purchaseOrderDetailComposition()->exists()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Purchase Order.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase request data');
    
                CustomHelper::sendNotification('purchase_requests',$query->id,'Purchase Request No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('purchase_requests',$query->id);

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
            'title' => 'PURCHASE REQUEST REPORT',
            'data' => PurchaseRequest::where(function ($query) use ($request) {
                if ($request->search) {
                    $query->where(function ($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('due_date', 'like', "%$request->search%")
                            ->orWhere('required_date', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('purchaseRequestDetail',function($query) use($request){
                                $query->whereHas('item',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%")
                                        ->orWhere('name','like',"%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                    
                }

                if ($request->status) {
                    $query->where('status',$request->status);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->get()
		];
		
		return view('admin.print.purchase.request', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';

		
		return Excel::download(new ExportPurchaseRequest($search,$status,$this->dataplaces), 'purchase_request_'.uniqid().'.xlsx');
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
			'post_date' 				=> 'required',
			'due_date'			        => 'required',
			'required_date'		        => 'required',
            'note'		                => 'required',
            'arr_item'                  => 'required|array',
            'place_id'                  => 'required',
            'arr_warehouse'             => 'required|array',
            'arr_place'                 => 'required|array',
            'arr_department'            => 'required|array'
		], [
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'due_date.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
			'required_date.required' 			=> 'Tanggal dipakai tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'place_id.required'                 => 'Penempatan lokasi tidak boleh kosong.',
            'arr_warehouse.required'            => 'Gudang tujuan tidak boleh kosong.',
            'arr_warehouse.array'               => 'Gudang harus dalam bentuk array.',
            'arr_place.required'                => 'Penempatan tujuan tidak boleh kosong.',
            'arr_place.array'                   => 'Penempatan harus dalam bentuk array.',
            'arr_department.required'           => 'Departemen tujuan tidak boleh kosong.',
            'arr_department.array'              => 'Departemen harus dalam bentuk array.'
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
                    $query = PurchaseRequest::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Request telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/purchase_requests');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->post_date = $request->post_date;
                        $query->due_date = $request->due_date;
                        $query->required_date = $request->required_date;
                        $query->note = $request->note;
                        $query->document = $document;
                        $query->project_id = $request->project_id ? $request->project_id : NULL;
                        $query->place_id = $request->place_id;
                        $query->department_id = session('bo_department_id');
                        $query->save();

                        foreach($query->purchaseRequestDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase request sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = PurchaseRequest::create([
                        'code'			=> PurchaseRequest::generateCode(),
                        'user_id'		=> session('bo_id'),
                        'place_id'      => $request->place_id,
                        'department_id'	=> session('bo_department_id'),
                        'status'        => '1',
                        'post_date'     => $request->post_date,
                        'due_date'      => $request->due_date,
                        'required_date' => $request->required_date,
                        'note'          => $request->note,
                        'project_id'    => $request->project_id ? $request->project_id : NULL,
                        'document'      => $request->file('file') ? $request->file('file')->store('public/purchase_requests') : NULL,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                
                foreach($request->arr_item as $key => $row){
                    DB::beginTransaction();
                    try {
                        PurchaseRequestDetail::create([
                            'purchase_request_id'   => $query->id,
                            'item_id'               => $row,
                            'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                  => $request->arr_note[$key],
                            'required_date'         => $request->arr_required_date[$key],
                            'place_id'              => $request->arr_place[$key],
                            'department_id'         => $request->arr_department[$key],
                            'warehouse_id'          => $request->arr_warehouse[$key]
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                CustomHelper::sendApproval('purchase_requests',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_requests',$query->id,'Pengajuan Purchase Request No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PurchaseRequest())
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

    public function show(Request $request){
        $pr = PurchaseRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $pr['project_id'] = $pr->project_id ? $pr->project_id : '';
        $pr['project_name'] = $pr->project()->exists() ? $pr->project->code.' - '.$pr->project->name : '';

        $arr = [];

        foreach($pr->purchaseRequestDetail as $row){
            $arr[] = [
                'item_id'           => $row->item_id,
                'item_name'         => $row->item->name,
                'qty'               => $row->qty,
                'unit'              => $row->item->buyUnit->code,
                'note'              => $row->note,
                'date'              => $row->required_date,
                'warehouse_name'    => $row->warehouse->name,
                'place_id'          => $row->place_id,
                'department_id'     => $row->department_id
            ];
        }

        $pr['details'] = $arr;
        				
		return response()->json($pr);
    }

    public function destroy(Request $request){
        $query = PurchaseRequest::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Request telah diapprove, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Purchase Request sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->purchaseOrderDetailComposition()->exists()){
            return response()->json([
                'status'  => 500,
                'message' => 'Data telah digunakan pada Purchase Order.'
            ]);
        }

        if($query->delete()) {
            
            $query->purchaseRequestDetail()->delete();
            CustomHelper::removeApproval('purchase_requests',$query->id);

            activity()
                ->performedOn(new PurchaseRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase request data');

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
        $query = PurchaseRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_pr = [];
        if($query) {
            if($query->purchaseOrderDetailComposition()->exists()){
               info("masuk sini");
                $pr = [
                    "Kode" => $query->code,
                    'tipe'=>"buka",
                    "name" => "Purchase Request",
                    'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($query->code),
                    "title" =>$query->code,
                ];
                $data_pr[] = $pr;
               foreach($query->purchaseOrderDetailComposition as $row){

                    if($row->purchaseOrderDetail->exists()){
                       $po=$row->purchaseOrderDetail->purchaseOrder;
                       $data_po = [
                            "Kode" => $po->code,
                            'tipe'=>"buka",
                            "name" => "Purchase Order",
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($po->code),
                            "title"=>  $po->code,
                        ];
                        if($po->goodReceipt()->exists()){
                            $data_good_receipt = [
                                'tipe'=>"buka",
                                "Kode" => $po->goodReceipt->goodReceiptMain->code,
                                "name" => "Good Receipt",
                                'title'=> $po->goodReceipt->goodReceiptMain->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($po->goodReceipt->goodReceiptMain->code),
                                'children'=>[],
                            ];
                            $data_po["children"] = $data_pr;
                            $data_good_receipt["children"][] = $data_po;
                            
                            $response = [
                                'status'  => 200,
                                'message' => $data_good_receipt
                            ];
                        }else{
                            $data_po["children"] = $data_pr;
                            $response = [
                                'status'  => 200,
                                'message' => $data_po
                            ];
                            info($data_po);
                        }
                    }else{
                        info("tdkmasuk sini2");
                    }
                }
            }else{
                info("tidak masuk sini");
               
                
            }
        } else {
            info("rusak sini");
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function approval(Request $request,$id){
        
        $pr = PurchaseRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Purchase Request',
                'data'      => $pr
            ];

            return view('admin.approval.purchase_request', $data);
        }else{
            abort(404);
        }
    }
}