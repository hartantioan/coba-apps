<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CostDistribution;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\Place;
use App\Models\Tax;
use App\Models\UsedData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExpensesController extends Controller
{
    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Pengeluaran Lain-Lain',
            'content'       => 'admin.finance.expense',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'currency'      => Currency::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        $menu = Menu::where('url', 'expenses')->first();
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = Expense::generateCode($menu->document_code);

		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'post_date',
            'document',
            'note',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Expense::count();

        $query_data = Expense::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
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
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Expense::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
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
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">info_outline</i></button>',
                    $val->code,
                    $val->user->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    number_format($val->grandtotal,2,',','.'),
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light indigo darken-4 white-text btn-small" data-popup="tooltip" title="Edit Catatan" onclick="showNote(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">mode_edit</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
            'post_date'             => 'required',
            'grandtotal'            => 'required',
            'note'                  => 'required',
		], [
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
            'note.required'                     => 'Keterangan tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));

            if($grandtotal <= 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Nominal tidak boleh dibawah sama dengan 0.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Expense::where('code',CustomHelper::decrypt($request->temp))->first();


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
                                $arrFile[] = $file->store('public/expenses');
                            }

                            $document = implode(',',$arrFile);
                        } else {
                            $document = $query->document;
                        }
                        $query->code = $request->code;
                        $query->document = $document;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->account_id = $request->account_id ? $request->account_id : NULL;
                        $query->post_date = $request->post_date;

                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));

                        $query->note = $request->note;

                        $query->save();
                        foreach($query->expenseDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Kas / Bank Masuk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $lastSegment = $request->lastsegment;
                    $fileUpload='';
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=Expense::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/purchase_orders');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }
                    $query = Expense::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'post_date'                 => $request->post_date,
                        'document'                  => $fileUpload ? $fileUpload : NULL,
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}

			if($query) {
                DB::beginTransaction();
                foreach($request->arr_expense_type as $key => $row){
                    $total = str_replace(',','.',str_replace('.','',$request->arr_total[$key]));
                    ExpenseDetail::create([
                        'expense_id'      => $query->id,
                        'expense_type_id' => $row,
                        'total'           => $total,
                        'note'            => $request->arr_note[$key],
                    ]);
                }
                DB::commit();


                activity()
                    ->performedOn(new Expense())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit pengeluaran.');

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
        $po = Expense::where('code',CustomHelper::decrypt($request->id))->first();
        $po['grandtotal'] = number_format($po->grandtotal,2,',','.');
        $arr=[];
        foreach($po->expenseDetail as $row){
            $arr[] = [
                'expense_type_id'   => $row->expense_type_id,
                'expense_type_name' => $row->expenseType->name,
                'note'              => $row->note ?? '',
                'total'             => number_format($row->total,2,',','.'),
            ];
        }
        $po['details'] = $arr;
		return response()->json($po);
    }
}
