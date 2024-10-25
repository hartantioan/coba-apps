<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestDetail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\ExportPaymentRequestDateReport;
use Maatwebsite\Excel\Facades\Excel;
class PaymentRequestDateReportController extends Controller
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
            'title'     => 'Finance Report',
            'content'   => 'admin.finance.payment_request_date_report',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $array_filter=[];

        $query_data = PaymentRequestDetail::whereHas('paymentRequest',function($query)use($request){
            if($request->start_date && $request->end_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->end_date);
            } else if($request->start_date) {
                $query->whereDate('post_date','>=', $request->start_date);
            } else if($request->end_date) {
                $query->whereDate('post_date','<=', $request->end_date);
            }
            if($request->filter_payment_request){
                $query->whereIn('id',$request->filter_payment_request);
            }
            if($request->filter_account){
                $query->whereIn('account_id',$request->filter_account);
            }
        })->where('lookable_type','purchase_invoices')->get();

        $table = '<div class="col s12" style="overflow:auto;"><table>
                <thead>
                    <tr>
                        <th class="center-align" colspan="7">Daftar PYR</th>
                    </tr>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Payment Request Code</th>
                        <th class="center-align">Catatan PYR</th>
                        <th class="center-align">No Purchase Invoice</th>
                        <th class="center-align">Vendor</th>
                        <th class="center-align">No Vendor</th>
                        <th class="center-align">Tgl.Bayar</th>
                        <th class="center-align">Tgl.OPYM</th>
                        <th class="center-align">Status</th>
                    </tr>
                </thead><tbody>';
        if($query_data){
            foreach($query_data as $key => $row){
                if ($row->paymentRequest->outgoingPayment()->exists()) {
                    $date= date('d/m/Y',strtotime($row->paymentRequest->outgoingPayment->post_date));
                } else {
                    $date = '';
                }
                $table .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td class="center-align">'.$row->paymentRequest->code.'</td>
                    <td class="center-align">'.$row->paymentRequest->note.'</td>
                    <td class="center-align">'.$row->lookable->code.'</td>
                    <td class="center-align">'.$row->lookable->account->name.'</td>
                    <td class="center-align">'.$row->lookable->invoice_no.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->paymentRequest->pay_date)).'</td>
                    <td class="center-align">'.$date.'</td>
                    <td class="center-align">'.$row->paymentRequest->status().'</td>
                </tr>';
            }
        }else{
            $table .= '<tr>
                <td class="center-align" colspan="7">data tidak ditemukan.</td>
            </tr>';
        }
        $table .= '</tbody></table></div>
            ';
        return response()->json($table);
    }

    public function export(Request $request){

        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $filter_payment_request = $request->filter_payment_request ? $request->filter_payment_request : '';
        $filter_account = $request->filter_account ? $request->filter_account : '';
		return Excel::download(new ExportPaymentRequestDateReport($post_date,$end_date,$filter_payment_request,$filter_account), 'payment_request_date_report_'.uniqid().'.xlsx');
    }
}
