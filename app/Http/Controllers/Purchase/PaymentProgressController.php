<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\Menu;
use App\Models\ItemGroup;
use App\Models\MenuUser;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPaymentProgressReport;
use App\Models\purchaseInvoice;
use App\Models\PurchaseOrder;

class PaymentProgressController extends Controller
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
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
            
        $data = [
            'title'     => 'Laporan Progress Pembayaran',
            'content'   => 'admin.purchase.report_payment_progress',
            'menus'     => Menu::where('parent_id',$menu->id)
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','view');
                            })
                            ->orderBy('order')->get(),
            'group'     =>  $itemGroup,
        ];


        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){

        $data = PurchaseOrder::where(function($query) use ($request) {
            // $query->where('code','PORD-24P1-00000222');
            if($request->start_date && $request->end_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->end_date);
            } else if($request->start_date) {
                $query->whereDate('post_date','>=', $request->start_date);
            } else if($request->end_date) {
                $query->whereDate('post_date','<=', $request->end_date);
            }
            if($request->filter_group){
                $query->whereHas('purchaseOrderDetail',function($query) use($request){
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('item_group_id', $request->filter_group);
                    });
                });
            }
        })
        ->get();
       
        $array_detail=[];
        foreach($data as $row_item_po){
            
            foreach($row_item_po->purchaseOrderDetail as $row_item_po_detail){
                $max_count=1;
                $array_item_po = [
                    'item'         => $row_item_po_detail->item->name ?? $row_item_po_detail->note ,
                    'item_code'    => $row_item_po_detail->item->code ?? $row_item_po_detail->coa->code,
                    'po_code'      => $row_item_po->code,
                    'po_date'      => $row_item_po->post_date,
                    'po_qty'       => CustomHelper::formatConditionalQty($row_item_po_detail->qty),
                    'nominal'   => number_format($row_item_po_detail->grandtotal,2,',','.'),
                    'status'       => $row_item_po->status(),
                ];
                $max_count_pr = 1;
            
                $all_grpo=[];
                if($row_item_po_detail->goodReceiptDetail()->exists()){
                    if($max_count<count($row_item_po_detail->goodReceiptDetail)){
                        $max_count=count($row_item_po_detail->goodReceiptDetail);
                    }
                    
                  
                    $total_grpo = 0;
                    foreach($row_item_po_detail->goodReceiptDetail as $row_gr_detail){
                    
                        $total_grpo++;
                        $grpo=[
                            'grpo_code'      => $row_gr_detail->goodReceipt->code,
                            'grpo_date'      => $row_gr_detail->goodReceipt->post_date,
                            'grpo_qty'       => CustomHelper::formatConditionalQty($row_gr_detail->qty),
                            'nominal'        => number_format($row_gr_detail->grandtotal,2,',','.'),
                            'status'         => $row_gr_detail->goodReceipt->status(),
                        ];
                        if($row_gr_detail->purchaseInvoiceDetail()->exists()){
                            if($max_count<count($row_gr_detail->purchaseInvoiceDetail)){
                                $max_count=count($row_gr_detail->purchaseInvoiceDetail);
                            }
                            
                            if ($max_count_pr < count($row_gr_detail->purchaseInvoiceDetail)) {
                                $max_count_pr = count($row_gr_detail->purchaseInvoiceDetail);
                            }
                           
                            $total_pyr = 0;
                            foreach($row_gr_detail->purchaseInvoiceDetail as $row_inv_detail){
                                $inv=[
                                    'inv_code'      => $row_inv_detail->purchaseInvoice->code,
                                    'inv_date'      => $row_inv_detail->purchaseInvoice->post_date,
                                    'inv_qty'       => CustomHelper::formatConditionalQty($row_inv_detail->qty),
                                    'nominal'       => number_format($row_inv_detail->grandtotal,2,',','.'),
                                    'status'        => $row_inv_detail->purchaseInvoice->status(),
                                ];
                                if($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail()->exists()){
                                    
                                    
                                    foreach($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail as $row_pyr_detail){
                                        $pyr=[
                                            'pyr_code'    => $row_pyr_detail->paymentRequest->code,
                                            'pyr_date'    => $row_pyr_detail->paymentRequest->post_date,
                                            'pyr_qty'     => CustomHelper::formatConditionalQty($row_pyr_detail->qty),
                                            'nominal'     => number_format($row_pyr_detail->nominal,2,',','.'),
                                            'status'      => $row_pyr_detail->paymentRequest->status(),
                                            'opym_code'   => $row_pyr_detail->paymentRequest->outgoingPayment()->exists() ? $row_pyr_detail->paymentRequest->outgoingPayment->code : ''
                                        ];
                                        $total_pyr++;
                                        $inv['pyr'][]=$pyr;
                                       
                                        
                                    }
                                    $grpo['invoice'][]=$inv;
                                    
                                    if($max_count<$total_pyr){
                                        $max_count=$total_pyr;
                                    }
                                    
                                    if ($max_count_pr < $total_pyr) {
                                        $max_count_pr = $total_pyr;
                                    }
                                    $grpo['rowspan']=$max_count_pr;
                                   
                                }else{
                                    $inv['pyr'][]=[
                                        'pyr_code'    => '',
                                        'pyr_date'    => '',
                                        'pyr_qty'     => '',
                                        'nominal'     => '',
                                        'status'       => '',
                                        'opym_code'     => '',
                                    ];
                                    $grpo['invoice'][]=$inv;
                                    $grpo['rowspan']=$max_count_pr;
                                    
                                    
                                }
                            }
                        
                        }else{
                            $pyr=[ 'pyr_code'    => '',
                                'pyr_date'    => '',
                                'pyr_qty'     => '',
                                'status'       => '',
                                'nominal'     => '',
                                'opym_code'     => '',];
                            $inv=['inv_code'      => '',
                                'inv_date'      => '',
                                'inv_qty'       => '',
                                'status'       => '',
                                'nominal'     => '',];
                            $grpo=[
                                'grpo_code'      => $row_gr_detail->goodReceipt->code,
                                'grpo_date'      => $row_gr_detail->goodReceipt->post_date,
                                'grpo_qty'       => CustomHelper::formatConditionalQty($row_gr_detail->qty),
                                'status'         => $row_gr_detail->goodReceipt->status(),
                                'nominal'        => number_format($row_gr_detail->grandtotal,2,',','.'),
                            ];
                            $inv['pyr'][]=$pyr;
                            $grpo['invoice'][]=$inv;
                            $grpo['rowspan']=$max_count_pr;
                            
                          
                        }
                        $all_grpo[]=$grpo;
                    }
                    if($max_count<$total_grpo){
                        $max_count=$total_grpo;
                    }
                
                }else if($row_item_po_detail->purchaseInvoiceDetail()->exists()){
                    $grpo=[
                        'grpo_code'      => 'JASA',
                        'grpo_date'      => '',
                        'grpo_qty'       => '',
                        'status'       => '',
                        'nominal'     => '',
                    ];
                    if ($max_count_pr < count($row_item_po_detail->purchaseInvoiceDetail)) {
                        $max_count_pr = count($row_item_po_detail->purchaseInvoiceDetail);
                    }
                    $total_pyr = 0;
                    foreach($row_item_po_detail->purchaseInvoiceDetail as $row_inv_detail){
                        $inv=[
                            'inv_code'      => $row_inv_detail->purchaseInvoice->code,
                            'inv_date'      => $row_inv_detail->purchaseInvoice->post_date,
                            'inv_qty'       => CustomHelper::formatConditionalQty($row_inv_detail->qty),
                            'nominal'       => number_format($row_inv_detail->grandtotal,2,',','.'),
                            'status'        => $row_inv_detail->purchaseInvoice->status(),
                        ];
                        if($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail()->exists()){
                            
                            
                            foreach($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail as $row_pyr_detail){
                                $pyr=[
                                    'pyr_code'    => $row_pyr_detail->paymentRequest->code,
                                    'pyr_date'    => $row_pyr_detail->paymentRequest->post_date,
                                    'pyr_qty'     => CustomHelper::formatConditionalQty($row_pyr_detail->qty),
                                    'nominal'     => number_format($row_pyr_detail->nominal,2,',','.'),
                                    'status'      => $row_pyr_detail->paymentRequest->status(),
                                    'opym_code'   => $row_pyr_detail->paymentRequest->outgoingPayment()->exists() ? $row_pyr_detail->paymentRequest->outgoingPayment->code : ''
                                ];
                                $total_pyr++;
                                $inv['pyr'][]=$pyr;
                               
                                
                            }
                            $grpo['invoice'][]=$inv;
                            
                            if($max_count<$total_pyr){
                                $max_count=$total_pyr;
                            }
                            
                            if ($max_count_pr < $total_pyr) {
                                $max_count_pr = $total_pyr;
                            }
                            $grpo['rowspan']=$max_count_pr;
                           
                        }else{
                            $inv['pyr'][]=[
                                'pyr_code'    => '',
                                'pyr_date'    => '',
                                'pyr_qty'     => '',
                                'nominal'     => '',
                                'status'       => '',
                                'opym_code'     => '',
                            ];
                            $grpo['invoice'][]=$inv;
                            $grpo['rowspan']=$max_count_pr;
                            
                            
                        }
                    }
                    $all_grpo[]=$grpo;

                }
                else{
                    $pyr=[ 'pyr_code'    => '',
                    'pyr_date'    => '',
                    'pyr_qty'     => '',
                    'status'       => '',
                    'nominal'     => '',
                    'opym_code'   => ''];
                    $inv=['inv_code'      => '',
                    'inv_date'      => '',
                    'inv_qty'       => '',
                    'status'       => '',
                    'nominal'     => '',];
                    $grpo=[
                        'grpo_code'      => '',
                        'grpo_date'      => '',
                        'grpo_qty'       => '',
                        'status'       => '',
                        'nominal'     => '',
                    ];
                    $inv['pyr'][]=$pyr;
                    $grpo['invoice'][]=$inv;
                    $grpo['rowspan']=$max_count_pr;
                    $all_grpo[]=$grpo;
                }
                
                $array_item_po['grpo']=$all_grpo;
                $array_item_po['rowspan']=$max_count;
                $array_detail[]=$array_item_po;
            }
           
        }
       
        return response()->json([
            'status'  => 200,
            'message' => $this->renderTable($array_detail,$request->type),
        ]);

        
    }

    public function renderTable($data,$request)
    {
    

        // Generate the HTML for the table
        $tableHtml = '<table border="1">';
        $tableHtml .= '<thead>';
        $tableHtml .= '<tr>';
        $tableHtml .= '<th>No</th>';
        $tableHtml .= '<th>Item</th>';
        $tableHtml .= '<th>Item Code</th>';
        $tableHtml .= '<th>PO Code</th>';
        $tableHtml .= '<th>PO Date</th>';
        $tableHtml .= '<th>PO Qty</th>';
        $tableHtml .= '<th>PO Nominal</th>';
        $tableHtml .= '<th>PO Status</th>';
        $tableHtml .= '<th>GRPO Code</th>';
        $tableHtml .= '<th>GRPO Date</th>';
        $tableHtml .= '<th>GRPO Qty</th>';
        $tableHtml .= '<th>GRPO Nominal</th>';
        $tableHtml .= '<th>GRPO Status</th>';
        $tableHtml .= '<th>INV Code</th>';
        $tableHtml .= '<th>INV Date</th>';
        $tableHtml .= '<th>INV Qty</th>';
        $tableHtml .= '<th>INV Nominal</th>';
        $tableHtml .= '<th>PO Status</th>';
        $tableHtml .= '<th>PYR Code</th>';
        $tableHtml .= '<th>PYR Date</th>';
        $tableHtml .= '<th>PYR Qty</th>';
        $tableHtml .= '<th>PYR Nominal</th>';
        $tableHtml .= '<th>PYR Status</th>';
        $tableHtml .= '<th>OPYM Code</th>';
        $tableHtml .= '</tr>';
        $tableHtml .= '</thead>';
        $tableHtml .= '<tbody>';
        $no=0;
        foreach ($data as $row) {
           
            $max_count_grpo=1;
            foreach ($row['grpo'] as $grinvIndex => $grpo) {
                $poCount = count($grpo['invoice']);
                if($max_count_grpo<$poCount){
                    $max_count_grpo=$poCount;
                }
                $grpoTotalCount = 0; // Initialize total count of grpo
                foreach ($grpo['invoice'] as $invIndex => $inv) {
                    $grpoTotalCount += count($inv['pyr']); // Increment total count of grpo
                }
                foreach ($grpo['invoice'] as $invIndex => $inv) {
                    $grpoCount = count($inv['pyr']);
                    if($max_count_grpo<$grpoCount){
                        $max_count_grpo=$grpoCount;
                    }
                    if($max_count_grpo<$grpoTotalCount){
                        $max_count_grpo=$grpoTotalCount;
                    }
                    $masuk = 0 ;
                    if($request != 'all'){
                        foreach ($inv['pyr'] as $pyrIndex => $pyr) {
                            if($pyr['opym_code'] == ''){
                                $masuk =1; 
                            }
                        }
                    }else{
                        $masuk = 1;
                    }
                    
                    foreach ($inv['pyr'] as $pyrIndex => $pyr) {
                        if($masuk ==1){
                            $tableHtml .= '<tr>';
                            if ($grinvIndex === 0 && $invIndex === 0 && $pyrIndex === 0) {
                                $no++;
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $no . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['item_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['item'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['po_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['po_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['po_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['nominal'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $row['rowspan'] . '">' . $row['status'] . '</td>';
                            }
                            if ($invIndex === 0 && $pyrIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $max_count_grpo . '">' . $grpo['grpo_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_grpo . '">' . $grpo['grpo_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_grpo . '">' . $grpo['grpo_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_grpo . '">' . $grpo['nominal'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $max_count_grpo . '">' . $grpo['status'] . '</td>';
                            }
                            if ($pyrIndex === 0) {
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $inv['inv_code'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $inv['inv_date'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $inv['inv_qty'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $inv['nominal'] . '</td>';
                                $tableHtml .= '<td rowspan="' . $grpoCount . '">' . $inv['status'] . '</td>';
                            }
                        
                            $tableHtml .= '<td>' . $pyr['pyr_code'] . '</td>';
                            $tableHtml .= '<td>' . $pyr['pyr_date'] . '</td>';
                            $tableHtml .= '<td>' . $pyr['pyr_qty'] . '</td>';
                            $tableHtml .= '<td>' . $pyr['nominal'] . '</td>';
                            $tableHtml .= '<td>' . $pyr['status'] . '</td>';
                            $tableHtml .= '<td>' . $pyr['opym_code'] . '</td>';
                            $tableHtml .= '</tr>';
                        }
                        
                    }
                }
            }
        }

        $tableHtml .= '</tbody>';
        $tableHtml .= '</table>';

        // Return only the HTML table
        return $tableHtml;
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $type = $request->type ? $request->type : '';
        $group = $request->group ? $request->group:'';
		return Excel::download(new ExportPaymentProgressReport($post_date,$end_date,$type,$group), 'payment_progress_report'.uniqid().'.xlsx');
    }
}
