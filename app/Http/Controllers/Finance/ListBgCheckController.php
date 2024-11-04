<?php

namespace App\Http\Controllers\Finance;

use App\Exports\ExportListBGCheck;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\ListBgCheck;
use App\Models\Company;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\User;

class ListBgCheckController extends Controller
{
    protected $dataplaces, $dataplacecode, $url, $menu;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->url = request()->segment(3);
        $this->menu = Menu::where('url', $this->url)->first();
    }

    public function index(Request $request)
    {
        $menu = $this->menu;
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $data = [
            'title'         => 'List Giro Cek',
            'content'       => 'admin.finance.list_bg_check',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'newcode'       => $menu->document_code.date('y'),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = ListBgCheck::generateCode($request->val);

		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'valid_until_date',
            'pay_date',
            'coa_id',
            'type',
            'document_no',
            'document',
            'note',
            'nominal',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ListBgCheck::count();

        $query_data = ListBgCheck::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('document', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('coa',function($query) use ($search, $request) {
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            });
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

        $total_filtered = ListBgCheck::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('document_no', 'like', "%$search%")
                            ->orWhere('document', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request) {
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWhereHas('coa',function($query) use ($search, $request) {
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name', 'like', "%$search%");
                            });
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
                    $nomor,
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->valid_until_date)),
                    $val->pay_date ? date('d/m/Y',strtotime($val->pay_date)) : '-',
                    $val->coa->code.' - '.$val->coa->name,
                    $val->type(),
                    $val->document_no,
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->note,
                    CustomHelper::formatConditionalQty($val->nominal),
                    CustomHelper::formatConditionalQty($val->grandtotal),
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat pink accent-2 white-text btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code' 				=> $request->temp ? ['required', Rule::unique('list_bg_checks', 'code')->ignore($request->temp)] : 'required|unique:list_bg_checks,code',
            'coa_id'            => 'required',
            'note'              => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'coa_id.required'       => 'Coa tidak boleh kosong.',
            'note.required'         => 'Nama tidak boleh kosong.',
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
                    $query = ListBgCheck::find($request->temp);

                    if($request->has('file')) {
                        if($query->document){
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                        }
                        $document = $request->file('file')->store('public/list_bg_check');
                    } else {
                        $document = $query->document;
                    }

                    $query->code            = $request->code;
                    $query->user_id         = session('bo_id');
                    $query->account_id      = $request->account_id;
                    $query->company_id      = $request->company_id;
                    $query->post_date       = $request->post_date;
                    $query->valid_until_date = $request->valid_until_date;
                    $query->pay_date        = $request->pay_date;
                    $query->coa_id          = $request->coa_id;
                    $query->type            = $request->type;
                    $query->document_no     = $request->document_no;
                    $query->document        = $document;
                    $query->note            = $request->note;
                    $query->nominal         = str_replace(',','.',str_replace('.','',$request->nominal));
                    // $query->grandtotal      = str_replace(',','.',str_replace('.','',$request->grandtotal?? null));
                    $query->status          = '1';

                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ListBgCheck::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = ListBgCheck::create([
                        'code'              => $newCode,
                        'user_id'           => session('bo_id'),
                        'account_id'        => $request->account_id,
                        'company_id'        => $request->company_id,
                        'post_date'         => $request->post_date,
                        'valid_until_date'  => $request->valid_until_date,
                        'pay_date'          => $request->pay_date,
                        'coa_id'            => $request->coa_id,
                        'type'              => $request->type,
                        'document_no'       => $request->document_no,
                        'document'          => $request->file('file') ? $request->file('file')->store('public/list_bg_check') : NULL,
                        'note'              => $request->note,
                        'nominal'           => str_replace(',','.',str_replace('.','',$request->nominal)),
                        // 'grandtotal'        => str_replace(',','.',str_replace('.','',$request->grandtotal?? null)),
                        'status'            => '1',
                    ]);

                    DB::commit();

			}

			if($query) {

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan List BG/Check No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new ListBgCheck())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit list bg check.');

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
        $list = ListBgCheck::find($request->id);
        $list['code_place_id'] = substr($list->code,7,2);
        $list['nominal'] = number_format($list->nominal,2,',','.');
        $list['account_name'] = $list->account->name;
        $list['grandtotal'] = number_format($list->grandtotal,2,',','.');
        $list['coa_name'] = $list->coa->code.' - '.$list->coa->name;
		return response()->json($list);
    }

    public function voidStatus(Request $request){
        $query = ListBgCheck::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            /* if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            } */

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
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

                $query->updateRootDocumentStatusProcess();

                activity()
                    ->performedOn(new ListBgCheck())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the list bg check data');

                CustomHelper::sendNotification('list_bg_check',$query->id,'list bg check No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('list_bg_check',$query->id);

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
        $query = ListBgCheck::find($request->id);

        if($query->delete()) {
            activity()
                ->performedOn(new ListBgCheck())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the list bg check data');

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

    public function export(Request $request){
        $status = $request->status? $request->status : '';
        $search= $request->search? $request->search : '';
		return Excel::download(new ExportListBGCheck($search,$status), 'list_bg_check_'.uniqid().'.xlsx');
    }
}
