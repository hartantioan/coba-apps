<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\CloseBillDetail;
use App\Models\CostDistribution;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\LandedCost;
use App\Models\InventoryTransferOut;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use App\Models\GoodIssueRequest;
use App\Models\PurchaseRequest;
use App\Models\Tax;
use App\Models\User;
use App\Helpers\TreeHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportCloseBill;
use App\Exports\ExportCloseBillTransactionPage;
use App\Models\CloseBillCost;
use App\Models\Division;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\OutgoingPayment;
use App\Models\PersonalCloseBill;
use App\Models\Place;
use Illuminate\Database\Eloquent\Builder;
use App\Models\UsedData;
class CloseBillController extends Controller
{

    protected $dataplaces, $dataplacecode, $datawarehouse;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouse = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Penutupan BS Karyawan',
            'content'       => 'admin.finance.close_bill',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'distribution'  => CostDistribution::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'division'      => Division::where('status','1')->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
            'currency'      => Currency::where('status','1')->get(),
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
            'currency_id',
            'currency_rate',
            'total',
            'tax',
            'wtax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = CloseBill::count();
        
        $query_data = CloseBill::where(function($query) use ($search, $request) {
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

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = CloseBill::where(function($query) use ($search, $request) {
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

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                    $val->currency->name,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
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
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = CloseBill::where('code',CustomHelper::decrypt($id))->first();
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
                    <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
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

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_type){
            foreach($request->arr_type as $key => $row){
                if($row == 'outgoing_payments'){
                    $op = OutgoingPayment::whereDoesntHave('used')->where('id',$request->arr_id[$key])->first();
                    if($op){
                        CustomHelper::sendUsedData($op->getTable(),$op->id,'Form Tutupan BS');
                        $balance = $op->balancePaymentCross();
                        if($balance > 0){
                            $details[] = [
                                'type'          => $op->getTable(),
                                'id'            => $op->id,
                                'code'          => $op->code,
                                'bp'            => $op->account->employee_no.' - '.$op->account->name,
                                'post_date'     => $op->pay_date,
                                'total'         => number_format($op->balance,2,',','.'),
                                'used'          => number_format($op->totalUsedCross(),2,',','.'),
                                'balance'       => number_format($balance,2,',','.'),
                                'note'          => $op->note,
                                'list_details'  => [],
                            ];
                        }
                    }
                }elseif($row == 'personal_close_bills'){
                    $op = PersonalCloseBill::whereDoesntHave('used')->where('id',$request->arr_id[$key])->first();
                    if($op){
                        CustomHelper::sendUsedData($op->getTable(),$op->id,'Form Tutupan BS Karyawan');
                        $balance = $op->balanceCloseBill();
                        if($balance > 0){
                            $listDetails = [];

                            foreach($op->personalCloseBillCost as $rowdetail){
                                if($rowdetail->total > 0 || $rowdetail->total < 0){
                                    $listDetails[] = [
                                        'note'          => $rowdetail->note,
                                        'nominal'       => number_format($rowdetail->total,2,',','.'),
                                        'type'          => '1',
                                        'coa_id'        => '',
                                        'coa_name'      => '',
                                        'place_id'      => $rowdetail->place_id ?? '',
                                        'line_id'       => $rowdetail->line_id ?? '',
                                        'machine_id'    => $rowdetail->machine_id ?? '',
                                        'division_id'   => $rowdetail->division_id ?? '',
                                        'project_id'    => $rowdetail->project_id ?? '',
                                        'project_name'  => $rowdetail->project()->exists() ? $rowdetail->project->name : '',
                                    ];
                                }

                                if($rowdetail->tax > 0 || $rowdetail->tax < 0){
                                    $listDetails[] = [
                                        'note'          => $rowdetail->note,
                                        'nominal'       => number_format($rowdetail->tax,2,',','.'),
                                        'type'          => '1',
                                        'coa_id'        => $rowdetail->taxMaster()->exists() ? $rowdetail->taxMaster->coa_purchase_id : '',
                                        'coa_name'      => $rowdetail->taxMaster()->exists() ? $rowdetail->taxMaster->coaPurchase->code.' - '.$rowdetail->taxMaster->coaPurchase->name : '',
                                        'place_id'      => $rowdetail->place_id ?? '',
                                        'line_id'       => '',
                                        'machine_id'    => '',
                                        'division_id'   => '',
                                        'project_id'    => '',
                                        'project_name'  => '',
                                    ];
                                }

                                if($rowdetail->wtax > 0 || $rowdetail->wtax < 0){
                                    $listDetails[] = [
                                        'note'          => $rowdetail->note,
                                        'nominal'       => number_format($rowdetail->wtax,2,',','.'),
                                        'type'          => '2',
                                        'coa_id'        => $rowdetail->wtaxMaster()->exists() ? $rowdetail->wtaxMaster->coa_purchase_id : '',
                                        'coa_name'      => $rowdetail->wtaxMaster()->exists() ? $rowdetail->wtaxMaster->coaPurchase->code.' - '.$rowdetail->wtaxMaster->coaPurchase->name : '',
                                        'place_id'      => $rowdetail->place_id ?? '',
                                        'line_id'       => '',
                                        'machine_id'    => '',
                                        'division_id'   => '',
                                        'project_id'    => '',
                                        'project_name'  => '',
                                    ];
                                }
                            }

                            $details[] = [
                                'type'          => $op->getTable(),
                                'id'            => $op->id,
                                'code'          => $op->code,
                                'bp'            => $op->user->employee_no.' - '.$op->user->name,
                                'post_date'     => $op->post_date,
                                'total'         => number_format($op->grandtotal,2,',','.'),
                                'used'          => number_format($op->totalCloseBill(),2,',','.'),
                                'balance'       => number_format($balance,2,',','.'),
                                'note'          => $op->note,
                                'list_details'  => $listDetails,
                            ];
                        }
                    }
                }
            }
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function getData(Request $request){
        $details = [];
        /* $data = OutgoingPayment::whereHas('account',function($query){
            $query->where('type','1');
        })
        ->whereIn('status',['2','3'])
        ->whereHas('paymentRequest',function($query){
            $query->whereHas('paymentRequestDetail',function($query){
                $query->whereHasMorph('lookable',
                [FundRequest::class],
                function (Builder $query){
                    $query->where('type','1')->where('document_status','3');
                });
            });
        })
        ->whereDoesntHave('used')
        ->get(); */
        $data = PersonalCloseBill::whereIn('status',['2'])->whereDoesntHave('used')->get();

        foreach($data as $row){
            $balance = $row->balanceCloseBill();
            if(!$row->used()->exists() && $balance > 0){
                $details[] = [
                    'id'                    => $row->id,
                    'type'                  => $row->getTable(),
                    'code'                  => $row->code,
                    'name'                  => $row->user->employee_no.' - '.$row->user->name,
                    'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                    'total'                 => number_format($row->total,2,',','.'),
                    'tax'                   => number_format($row->tax,2,',','.'),
                    'wtax'                  => number_format($row->wtax,2,',','.'),
                    'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                    'used'                  => number_format($row->totalCloseBill(),2,',','.'),
                    'balance'               => number_format($balance,2,',','.'),
                    'note'                  => $row->note,
                ];
            }
        }

        return response()->json([
            'status'    => 200,
            'data'      => $details,
            'message'   => 'Data tidak ditemukan.' 
        ]);
    }

    public function rowDetail(Request $request){
        $data   = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">'.$x.'<div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Outgoing Payment (BS)</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">OP/CREQ No.</th>
                                <th class="center-align">Partner Bisnis</th>
                                <th class="center-align">Tgl.Bayar</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Nominal Terpakai</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->closeBillDetail as $key => $row){
            if($row->outgoingPayment()->exists()){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->outgoingPayment->code.'</td>
                    <td class="center-align">'.$row->outgoingPayment->account->employee_no.' - '.$row->outgoingPayment->account->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->outgoingPayment->pay_date)).'</td>
                    <td class="">'.$row->note.'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
            if($row->personalCloseBill()->exists()){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->personalCloseBill->code.'</td>
                    <td class="center-align">'.$row->personalCloseBill->user->employee_no.' - '.$row->personalCloseBill->user->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->personalCloseBill->post_date)).'</td>
                    <td class="">'.$row->note.'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="13">Daftar Biaya</th>
                            </tr>
                            <tr>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                                <th class="center-align">Ket.1</th>
                                <th class="center-align">Ket.2</th>
                                <th class="center-align">Debit FC</th>
                                <th class="center-align">Kredit FC</th>
                                <th class="center-align">Debit Rp</th>
                                <th class="center-align">Kredit Rp</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->closeBillCost as $key => $row){
            $string .= '<tr>
                <td class="">'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="">'.($row->costDistribution()->exists() ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-').'</td>
                <td class="">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td class="">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td class="">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="">'.($row->division()->exists() ? $row->division->code : '-').'</td>
                <td class="">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="right-align">'.number_format($row->nominal_debit_fc,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_credit_fc,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_debit,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_credit,2,',','.').'</td>
            </tr>';
        }
        
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
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';
		
        return response()->json($string);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function approval(Request $request,$id){
        
        $pr = CloseBill::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Approval Tutupan BS',
                'data'      => $pr
            ];

            return view('admin.approval.close_bill', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $cb = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $total_op = $cb->totalOp();
        $cb['code_place_id'] = substr($cb->code,7,2);
        $cb['balance'] = number_format($total_op - $cb->grandtotal,2,',','.');
        $cb['total_op'] = number_format($total_op,2,',','.');
        $cb['grandtotal'] = number_format($cb->grandtotal,2,',','.');
        $cb['currency_rate'] = number_format($cb->currency_rate,2,',','.');

        $details = [];
        $costs = [];

        foreach($cb->closeBillDetail as $row){
            if($row->outgoingPayment()->exists()){
                $balance = $row->outgoingPayment->balancePaymentCross();
                $details[] = [
                    'type'      => $row->outgoingPayment->getTable(),
                    'id'        => $row->outgoing_payment_id,
                    'code'      => $row->outgoingPayment->code,
                    'bp'        => $row->outgoingPayment->account->employee_no.' - '.$row->outgoingPayment->account->name,
                    'post_date' => $row->outgoingPayment->pay_date,
                    'total'     => number_format($row->outgoingPayment->balance,2,',','.'),
                    'used'      => number_format($row->outgoingPayment->totalUsedCross(),2,',','.'),
                    'balance'   => number_format($balance + $row->nominal,2,',','.'),
                    'nominal'   => number_format($row->nominal,2,',','.'),
                    'note'      => $row->note,
                ];
            }
            if($row->personalCloseBill()->exists()){
                $balance = $row->personalCloseBill->balanceCloseBill();
                $details[] = [
                    'type'      => $row->personalCloseBill->getTable(),
                    'id'        => $row->personal_close_bill_id,
                    'code'      => $row->personalCloseBill->code,
                    'bp'        => $row->personalCloseBill->user->employee_no.' - '.$row->personalCloseBill->user->name,
                    'post_date' => $row->personalCloseBill->post_date,
                    'total'     => number_format($row->personalCloseBill->grandtotal,2,',','.'),
                    'used'      => number_format($row->personalCloseBill->totalCloseBill(),2,',','.'),
                    'balance'   => number_format($balance + $row->nominal,2,',','.'),
                    'nominal'   => number_format($row->nominal,2,',','.'),
                    'note'      => $row->note,
                ];
            }
        }

        foreach($cb->closeBillCost as $row){
            $costs[] = [
                'coa_id'                => $row->coa_id,
                'coa_name'              => $row->coa->code.' - '.$row->coa->name,
                'cost_distribution_id'  => $row->cost_distribution_id ?? '',
                'cost_distribution_name'=> $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'place_id'              => $row->place_id ?? '',
                'line_id'               => $row->line_id ?? '',
                'machine_id'            => $row->machine_id ?? '',
                'division_id'           => $row->division_id ?? '',
                'project_id'            => $row->project_id ?? '',
                'project_name'          => $row->project_id ? $row->project->code.' - '.$row->project->name : '',
                'nominal_debit'         => number_format($row->nominal_debit,2,',','.'),
                'nominal_credit'        => number_format($row->nominal_credit,2,',','.'),
                'nominal_debit_fc'      => number_format($row->nominal_debit_fc,2,',','.'),
                'nominal_credit_fc'     => number_format($row->nominal_credit_fc,2,',','.'),
                'note'                  => $row->note ? $row->note : '',
                'note2'                 => $row->note2 ? $row->note2 : '',
            ];
        }

        $cb['details'] = $details;
        $cb['costs'] = $costs;
        				
		return response()->json($cb);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = CloseBill::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                  => 'required',
            'code_place_id'         => 'required',
            'company_id'            => 'required',
            'post_date'             => 'required',
            'arr_type'              => 'required|array',
            'arr_id'                => 'required|array',
            'arr_coa'               => 'required|array',
            'arr_nominal'           => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'arr_type.required'                 => 'OP tidak boleh kosong.',
            'arr_type.array'                    => 'OP harus array.',
            'arr_coa.required'                  => 'Coa tidak boleh kosong.',
            'arr_coa.array'                     => 'Coa harus array.',
            'arr_id.required'                   => 'ID tidak boleh kosong.',
            'arr_id.array'                      => 'ID harus array.',
            'arr_nominal.required'              => 'Nominal OP tidak boleh kosong.',
            'arr_nominal.array'                 => 'Nominal OP harus array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            /* DB::beginTransaction();
            try { */

                $grandtotal = 0;
                $op = 0;

                if($request->arr_nominal){
                    foreach($request->arr_nominal as $key => $row){
                        $op += str_replace(',','.',str_replace('.','',$row));
                    }
                }

                if($request->arr_nominal_debit_fc){
                    foreach($request->arr_nominal_debit_fc as $key => $row){
                        $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_nominal_debit_fc[$key]));
                    }
                    foreach($request->arr_nominal_credit_fc as $key => $row){
                        $grandtotal -= str_replace(',','.',str_replace('.','',$request->arr_nominal_credit_fc[$key]));
                    }
                }

                $balance = $op - $grandtotal;

                if($balance > 0 || $balance < 0){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Nominal selisih tutupan BS harus 0.'
                    ]);
                }
                
                if($request->temp){
                    
                    $query = CloseBill::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Tutupan BS telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->status = '1';
                        $query->grandtotal = $op;

                        $query->save();

                        $query->closeBillDetail()->delete();
                        $query->closeBillCost()->delete();

                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Penutupan BS Karyawan sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=CloseBill::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = CloseBill::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'note'                      => $request->note,
                        'status'                    => '1',
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'grandtotal'                => $op,
                    ]);
                }
                
                if($query) {
                    foreach($request->arr_type as $key => $row){
                        if($row == 'outgoing_payments'){
                            $op = OutgoingPayment::find($request->arr_id[$key]);
                            $cbd = CloseBillDetail::create([
                                'close_bill_id'         => $query->id,
                                'outgoing_payment_id'   => $op->id,
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                  => $request->arr_note_source[$key],
                            ]);
                            CustomHelper::removeCountLimitCredit($op->account_id,$cbd->nominal);
                        }elseif($row == 'personal_close_bills'){
                            $op = PersonalCloseBill::find($request->arr_id[$key]);
                            $cbd = CloseBillDetail::create([
                                'close_bill_id'         => $query->id,
                                'personal_close_bill_id'=> $op->id,
                                'nominal'               => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                  => $request->arr_note_source[$key],
                            ]);
                            CustomHelper::removeCountLimitCredit($op->user_id,$cbd->nominal);
                        }
                        CustomHelper::removeUsedData($row,$request->arr_id[$key]);
                    }
                    foreach($request->arr_coa as $key => $row){
                        CloseBillCost::create([
                            'close_bill_id'                 => $query->id,
                            'cost_distribution_id'          => $request->arr_cost_distribution_cost[$key] ?? NULL,
                            'coa_id'                        => $request->arr_coa[$key],
                            'place_id'                      => $request->arr_place[$key] ?? NULL,
                            'line_id'                       => $request->arr_line[$key] ?? NULL,
                            'machine_id'                    => $request->arr_machine[$key] ?? NULL,
                            'division_id'                   => $request->arr_division[$key] ?? NULL,
                            'project_id'                    => $request->arr_project[$key] ?? NULL,
                            'nominal_debit'                 => str_replace(',','.',str_replace('.','',$request->arr_nominal_debit[$key])),
                            'nominal_credit'                => str_replace(',','.',str_replace('.','',$request->arr_nominal_credit[$key])),
                            'nominal_debit_fc'              => str_replace(',','.',str_replace('.','',$request->arr_nominal_debit_fc
                            [$key])),
                            'nominal_credit_fc'             => str_replace(',','.',str_replace('.','',$request->arr_nominal_credit_fc
                            [$key])),
                            'note'                          => $request->arr_note[$key] ?? NULL,
                            'note2'                         => $request->arr_note2[$key] ?? NULL,
                        ]);
                    }

                    CustomHelper::sendApproval('close_bills',$query->id,$query->note);
                    CustomHelper::sendNotification('close_bills',$query->id,'Penutupan BS Karyawan No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new CloseBill())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit close bill out.');

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

                /* DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            } */
		}
		
		return response()->json($response);
    }

    public function voidStatus(Request $request){
        $query = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                if($query->journal()->exists()){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->closeBillDetail as $row){
                    if($row->outgoingPayment()->exists()){
                        foreach($row->outgoingPayment->paymentRequest->paymentRequestDetail as $rowdetail){
                            if($rowdetail->lookable_type == 'fund_requests'){
                                $rowdetail->lookable->update([
                                    'balance_status'	=> NULL
                                ]);
                            }
                        }
                        CustomHelper::addCountLimitCredit($row->outgoingPayment->account_id,$row->nominal);
                    }elseif($row->personalCloseBill()->exists()){
                        foreach($row->personalCloseBill->personalCloseBillDetail as $rowdetail){
                            $rowdetail->fundRequest->update([
                                'balance_status'	=> NULL
                            ]);
                        }
                        $row->personalCloseBill->update([
                            'status'    => '2'
                        ]);
                        CustomHelper::addCountLimitCredit($row->personalCloseBill->user_id,$row->nominal);
                    }
                }
    
                activity()
                    ->performedOn(new CloseBill())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the close bill data');
    
                CustomHelper::sendNotification('close_bills',$query->id,'Penutupan BS / Closing Bill No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('close_bills',$query->id);
                CustomHelper::removeJournal('close_bills',$query->id);

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
        $query = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal / dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            foreach($query->closeBillDetail as $row){
                if($row->outgoingPayment()->exists()){
                    CustomHelper::addCountLimitCredit($row->outgoingPayment->account_id,$row->nominal);
                }elseif($row->personalCloseBill()->exists()){
                    CustomHelper::addCountLimitCredit($row->personalCloseBill->user_id,$row->nominal);
                }
                $row->delete();
            }
            $query->closeBillCost()->delete();

            CustomHelper::removeApproval('close_bills',$query->id);

            activity()
                ->performedOn(new CloseBill())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the close bill data');

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
                $pr = CloseBill::where('code',$row)->first();
                
                if($pr){
                    
                    $pdf = PrintHelper::print($pr,'Good Issue','a4','portrait','admin.print.finance.close_bill_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                      
                        $menu = CloseBill::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = CloseBill::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Issue','a4','portrait','admin.print.finance.close_bill_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = CloseBill::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Good Issue','a4','portrait','admin.print.finance.close_bill_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = CloseBill::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $pdf = PrintHelper::print($pr,'Good Issue','a4','portrait','admin.print.finance.close_bill_individual',$menuUser->mode);
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;

    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportCloseBill($post_date,$end_date,$mode), 'close_bill_'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $close_bill = [
            'key'   => $query->code,
            "name"  => $query->code,
            "color" => "lightblue",
            'properties'=> [
                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')]
                ],
            'url'   =>request()->root()."/admin/finance/close_bill?code=".CustomHelper::encrypt($query->code),
            "title" =>$query->code,
        ];

        $data_go_chart[]=$close_bill;
            


        if($query) {
            
           
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_cb',$query->id);
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
            
        } else {
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = CloseBill::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new CloseBill())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Good Issue Request data');
    
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

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $company = $request->company ? $request->company : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportCloseBillTransactionPage($search,$company,$post_date,$end_date,$status,$modedata), 'purchase_request_'.uniqid().'.xlsx');
    }
}