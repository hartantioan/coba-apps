<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCoa;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class JournalController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'     => 'Jurnal',
            'content'   => 'admin.accounting.journal',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'post_date',
            'note'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Journal::whereHas('journalDetail',function($query){ $query->whereIn('place_id',$this->dataplaces); })->count();
        
        $query_data = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->whereHas('journalDetail',function($query){ $query->whereIn('place_id',$this->dataplaces); })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Journal::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            })->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->whereHas('journalDetail',function($query){ $query->whereIn('place_id',$this->dataplaces); })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    date('d/m/y',strtotime($val->post_date)),
                    $val->note,
                    $val->lookable_type == 'good_receipts' ? $val->lookable->goodReceiptMain->code : $val->lookable->code,
                    $val->status(),
                    '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
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

    public function rowDetail(Request $request){
        $data   = PurchaseOrder::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Perusahaan</th>
                                <th class="center-align">Pabrik/Plant</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->purchaseOrderDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.$row->qty.'</td>
                <td class="center-align">'.$row->item->buyUnit->code.'</td>
                <td class="right-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_1,3,',','.').'</td>
                <td class="center-align">'.number_format($row->percent_discount_2,3,',','.').'</td>
                <td class="right-align">'.number_format($row->discount_3,3,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,3,',','.').'</td>
                <td class="center-align">'.$row->note.'</td>
            </tr>
            <tr>
                <td class="center-align" colspan="10">
                    '.$row->purchaseRequestList().'
                </td>
            </tr>';
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }
}