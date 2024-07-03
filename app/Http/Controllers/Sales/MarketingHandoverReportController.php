<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingPrice;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDetail;
use App\Models\MarketingOrderInvoice;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MarketingHandoverReportController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Tanda Terima Invoice & Kwitansi',
            'content'   => 'admin.sales.handover_report',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'account_id',
            'post_date',
            'balance',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderInvoice::whereIn('status',['1','2','3'])->count();
        
        $query_data = MarketingOrderInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('account',function($query) use($search,$request){
                        $query->where('name','like', $search);
                    });
                }
                if($request->account_id){
                    $query->where('account_id',$request->account_id);
                }
                if($request->type){
                    $query->when($request->type == '1',function($query){
                        $query->whereDoesntHave('marketingOrderHandoverInvoiceDetail');
                    })->when($request->type == '2',function($query){
                        $query->whereDoesntHave('marketingOrderReceiptDetail');
                    })->when($request->type == '3',function($query) use($search,$request){
                        $query->whereHas('marketingOrderReceiptDetail',function($query) use($search,$request){
                            $query->whereHas('marketingOrderReceipt',function($query) use($search,$request){
                                $query->whereDoesntHave('marketingOrderHandoverReceiptDetail');
                            });
                        });
                    });
                }
            })
            ->whereIn('status',['1','2','3'])
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('account',function($query) use($search,$request){
                        $query->where('name','like', $search);
                    });
                }
                if($request->account_id){
                    $query->where('account_id',$request->account_id);
                }
            })
            ->whereIn('status',['1','2','3'])
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            foreach($query_data as $val) {
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->account->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    number_format($val->balance,2,',','.'),
                    number_format($val->balancePaymentIncoming(),2,',','.'),
                    $val->latestHandoverInvoice(),
                    $val->latestReceipt(),
                    $val->latestHandoverReceipt(),
                ];
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
        $data   = MarketingOrderInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Item & Surat Jalan</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Surat Jalan</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totals=0;
        $totalppn=0;
        $totalgrandtotal=0;
        foreach($data->marketingOrderInvoiceDeliveryProcess as $key => $row){
            $totalqty+=$row->qty;
            $totals+=$row->total;
            $totalppn+=$row->tax;
            $totalgrandtotal+=$row->grandtotal;
    
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code.'</td>
                <td class="center-align">'.$row->lookable->item->name.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->lookable->item->sellUnit->code.'</td>
                <td class="">'.$row->note_internal.' - '.$row->note_external.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2">  </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalppn, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
            </tr>  
        ';
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-3"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar AR Down Payment</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Dokumen</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->marketingOrderInvoiceDownPayment as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="">'.$row->note.'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-3"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align">Total</th>
                                <th class="center-align">PPN</th>
                                <th class="center-align">Total Stl.Pajak</th>
                                <th class="center-align">Rounding</th>
                                <th class="center-align">Grandtotal</th>
                                <th class="center-align">Downpayment</th>
                                <th class="center-align">Sisa</th>
                            </tr>
                            <tr>
                                <th class="center-align gradient-45deg-amber-amber"><h6 class="white-text">'.number_format($data->total,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-indigo-blue"><h6 class="white-text">'.number_format($data->tax,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-brown-brown"><h6 class="white-text">'.number_format($data->total_after_tax,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-deep-orange-orange"><h6 class="white-text">'.number_format($data->rounding,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-purple-deep-orange"><h6 class="white-text">'.number_format($data->grandtotal,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-light-blue-cyan"><h6 class="white-text">'.number_format($data->downpayment,2,',','.').'</h6></th>
                                <th class="center-align gradient-45deg-green-teal"><h6 class="white-text">'.number_format($data->balance,2,',','.').'</h6></th>
                            </tr>
                        </thead></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Tracking TT Invoice</th>
                            </tr>
                            <tr>
                                <th class="center-align">Tgl.TT</th>
                                <th class="center-align">TT.Invoice</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';

        if($data->marketingOrderHandoverInvoiceDetail()->exists()){
            foreach($data->marketingOrderHandoverInvoiceDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->marketingOrderHandoverInvoice->post_date)).'</td>
                    <td class="">'.$row->marketingOrderHandoverInvoice->code.'</td>
                    <td class="">'.$row->marketingOrderHandoverInvoice->status().'</td>
                    <td class="">'.$row->marketingOrderHandoverInvoice->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Data tidak ditemukan</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Tracking TT Kwitansi</th>
                            </tr>
                            <tr>
                                <th class="center-align">Tgl.Update</th>
                                <th class="center-align">Kwitansi</th>
                                <th class="center-align">TT.Kwitansi</th>
                                <th class="center-align">Collector</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        $arrTrackingTT = $data->listTrackingCollector();

        if(count($arrTrackingTT) > 0){
            foreach($data->listTrackingCollector() as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row['date'].'</td>
                    <td class="">'.$row['receipt'].'</td>
                    <td class="">'.$row['code'].'</td>
                    <td class="">'.$row['collector'].'</td>
                    <td class="">'.$row['status'].'</td>
                    <td class="">'.$row['note'].'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Data tidak ditemukan</td>
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
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol></div>';
		
        return response()->json($string);
    }

    public function export(Request $request){
		$item = $request->item ? $request->item:'';
        $search = $request->input('search.value')?$request->input('search.value'):'';
		return Excel::download(new ExportMarketingPrice($search,$item), 'history_price_item_so'.uniqid().'.xlsx');
    }
}
