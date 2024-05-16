<?php

namespace App\Http\Controllers\Sales;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Illuminate\Http\Request;
use App\Models\User;

class MarketingOrderPaymentController extends Controller
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
        $data = [
            'title'         => 'Histori Pembayaran',
            'content'       => 'admin.sales.payment_history',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatableInvoice(Request $request){
        $column = [
            'code',
            'account_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderInvoice::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->whereIn('status',['2','3'])
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->whereIn('status',['2','3'])
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $percent = $val->getPercentPayment();
                $response['data'][] = [
                    $val->code,
                    $val->account->name,
                    '<div class="progress red lighten-2 white-text" data-position="top" data-tooltip="Progress was at 50% when tested">
					    <div class="determinate green" style="width: '.$percent.'%; animation: grow 2s;">'.$percent.'%</div>
                    </div>',
                    '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Lihat Detail" onclick="showInvoice(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">web</i></button>'
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

    public function datatableDownpayment(Request $request){
        $column = [
            'code',
            'account_id',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderDownPayment::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->whereIn('status',['2','3'])
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
            })
            ->whereIn('status',['2','3'])
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $percent = $val->getPercentPayment();
                $response['data'][] = [
                    $val->code,
                    $val->account->name,
                    '<div class="progress red lighten-2 white-text" data-position="top" data-tooltip="Progress was at 50% when tested">
					    <div class="determinate green" style="width: '.$percent.'%; animation: grow 2s;">'.$percent.'%</div>
                    </div>',
                    '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Lihat Detail" onclick="showDownpayment(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">web</i></button>'
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

    public function show(Request $request){
        $code = CustomHelper::decrypt($request->code);
        $mode = $request->mode;

        if($mode == 'dp'){
            $data = MarketingOrderDownPayment::where('code',$code)->first();
            $html = '<div class="row">
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-light-blue-cyan border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.$data->code.'</h5>
                                <p class="white-text lighten-4">Dokumen</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-red-pink border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->total,2,',','.').'</h5>
                                <p class="white-text lighten-4">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-amber-amber border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->tax,2,',','.').'</h5>
                                <p class="white-text lighten-4">PPN</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-green-teal border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->grandtotal,2,',','.').'</h5>
                                <p class="white-text lighten-4">Grandtotal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">DAFTAR PEMBAYARAN</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">Kas/Bank</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Rounding</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
            $total = 0;
            foreach($data->incomingPaymentDetail as $key => $row){
                $html .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->incomingPayment->code.'</td>
                    <td>'.$row->incomingPayment->coa->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->incomingPayment->post_date)).'</td>
                    <td>'.$row->incomingPayment->note.' - '.$row->note.'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->rounding,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->subtotal,2,',','.').'</td>
                </tr>';
                $total += $row->subtotal;
            }
            
            $html .= '</tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right-align"><h6>Total</h6></td>
                        <td class="right-align"><h6>'.number_format($total,2,',','.').'</h6></td>
                    </tr>
                </tfoot>
            </table></div></div>';


        }elseif($mode == 'inv'){
            $data = MarketingOrderInvoice::where('code',$code)->first();
            $html = '<div class="row">
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-light-blue-cyan border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.$data->code.'</h5>
                                <p class="white-text lighten-4">Dokumen</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-red-pink border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->grandtotal,2,',','.').'</h5>
                                <p class="white-text lighten-4">Grandtotal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-amber-amber border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->downpayment,2,',','.').'</h5>
                                <p class="white-text lighten-4">Downpayment</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s3">
                        <div class="card gradient-shadow gradient-45deg-green-teal border-radius-3 animate fadeUp">
                            <div class="card-content center">
                                <h5 class="white-text lighten-4">'.number_format($data->balance,2,',','.').'</h5>
                                <p class="white-text lighten-4">Tagihan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">DAFTAR PEMBAYARAN</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">Kas/Bank</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Rounding</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
            $total = 0;
            foreach($data->incomingPaymentDetail as $key => $row){
                $html .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->incomingPayment->code.'</td>
                    <td>'.$row->incomingPayment->coa->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->incomingPayment->post_date)).'</td>
                    <td>'.$row->incomingPayment->note.' - '.$row->note.'</td>
                    <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->rounding,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->subtotal,2,',','.').'</td>
                </tr>';
                $total += $row->subtotal;
            }
            
            $html .= '</tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right-align"><h6>Total</h6></td>
                        <td class="right-align"><h6>'.number_format($total,2,',','.').'</h6></td>
                    </tr>
                </tfoot>
            </table></div></div>';
        }

        return response()->json($html);
    }
}