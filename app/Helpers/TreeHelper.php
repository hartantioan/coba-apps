<?php

namespace App\Helpers;
use App\Models\Company;

use App\Models\Line;
//models inventory
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\GoodIssueRequest;
//models purchase
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\LandedCost;
use App\Models\PurchaseRequest;
use App\Models\MaterialRequest;
use App\Models\PurchaseDownPayment;
//model finance 
use App\Models\IncomingPayment;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
//model sales
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessTracking;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderPlan;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use App\Models\MarketingOrder;
use App\Models\Retirement;

// models production
use App\Models\ProductionSchedule;
use App\Models\ProductionOrder;
use App\Models\ProductionIssueReceive;
use App\Models\ProductionIssue;
use App\Models\ProductionReceive;
use App\Models\ProductionFgReceive;
use App\Models\ProductionHandover;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class TreeHelper {
    public static function treeLoop1($data_go_chart = [] , $data_link = [], $data_id = '', $id = null ,$hide_nominal = null){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $data_id_good_scale = [];
        $data_id_good_issue = [];
        $data_id_mr = [];
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_inventory_transfer_out=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];
        $data_id_pyrcs=[];
        $data_id_gir = [];
        $data_id_cb  =[];
        $data_id_frs  =[];
        $data_id_op=[];
        $data_id_pcb=[];
    
        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_hand_over_invoice = [];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_id_mo_delivery_process=[];
        $data_id_mo_receipt = [];
        $data_incoming_payment=[];
        $data_id_hand_over_receipt=[];
        $data_id_mo_plan = [];
        
        $data_id_production_schedule=[];
        $data_id_production_order=[];
        $data_id_production_issue=[];
        $data_id_production_receive=[];
        $data_id_production_fg_receive=[];
        $data_id_production_handover=[];
        $data_id_production_schedule_target=[];

        $finished_data_id_gr=[];
        $finished_data_id_gscale=[];
        $finished_data_id_greturns=[];
        $finished_data_id_invoice=[];
        $finished_data_id_pyrs=[];
        $finished_data_id_pyrcs=[];
        $finished_data_id_dp=[];
        $finished_data_id_memo=[];
        $finished_data_id_gissue=[];
        $finished_data_id_lc=[];
        $finished_data_id_invetory_to=[];
        $finished_data_id_po=[];
        $finished_data_id_pr=[];
        $finished_data_id_mr=[];
        $finished_data_id_gir=[];
        $finished_data_id_cb=[];
        $finished_data_id_frs=[];
        $finished_data_id_pcb=[];
        $finished_data_id_op=[];
        $finished_data_id_mo=[];
        $finished_data_id_mo_delivery = [];
        $finished_data_id_mo_delivery_process = [];
        $finished_data_id_ip = [];
        $finished_data_id_mo_plan=[];
        $finished_data_id_production_schedule=[];
        $finished_data_id_production_order=[];
        $finished_data_id_production_issue_receive=[];
        $finished_data_id_production_issue=[];
        $finished_data_id_production_receive=[];
        $finished_data_id_production_fg_receive=[];
        $finished_data_id_production_handover=[];

        if (!isset($$data_id) || !is_array($$data_id)) {
            
            $$data_id = [];
        }
    
        $$data_id[] = $id;

        $added = true;
        while($added){
            
            $added=false;
            // Pengambilan foreign branch gr
            foreach($data_id_gr as $gr_id){
                if(!in_array($gr_id, $finished_data_id_gr)){
                    $finished_data_id_gr[]= $gr_id; 
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        
                        if($good_receipt_detail->purchaseOrderDetail->purchaseOrder->isSecretPo()){
                            $name = null;
                        }else{
                            $name = $good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name ?? null;
                        }
                        $properties = [
                            ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                            ['name'=> "Vendor  : ".($name !== null ? $name : '-')],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] = ['name'=> "Nominal : 0"];
                        }
                        $po = [
                            'properties'=>$properties,
                            'key'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'name'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                        ];

                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'to'=>$query_gr->code,
                            'string_link'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code.$query_gr->code
                        ];
                        //$data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                        if(!in_array($good_receipt_detail->purchaseOrderDetail->purchaseOrder->id, $data_id_po)){
                            $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            $added = true; 
                        }
                        
                       

                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                $good_return_tempura =[
                                    "name"=> $goodReturnPODetail->goodReturnPO->code,
                                    "key" => $goodReturnPODetail->goodReturnPO->code,
                                    
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
                                ];
                                                    
                                $data_go_chart[] = $good_return_tempura;
                                $data_link[]=[
                                    'from'=> $query_gr->code,
                                    'to'=>$goodReturnPODetail->goodReturnPO->code,
                                    'string_link'=>$query_gr->code.$goodReturnPODetail->goodReturnPO->code,
                                ];
                                $data_id_greturns[]=  $goodReturnPODetail->goodReturnPO->id;

                            }
                                
                                
                            
                        }

                        //landed cost searching
                        if($good_receipt_detail->landedCostDetail()->exists()){
                            foreach($good_receipt_detail->landedCostDetail as $landed_cost_detail){
                                $properties = [
                                    ['name'=> "Tanggal : ".$landed_cost_detail->landedCost->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($landed_cost_detail->landedCost).number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')];
                                }
                                $data_lc=[
                                    'properties'=> $properties,
                                    'key'=>$landed_cost_detail->landedCost->code,
                                    'name'=>$landed_cost_detail->landedCost->code,
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost_detail->landedCost->code),    
                                ];

                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost_detail->landedCost->code,
                                    'string_link'=>$query_gr->code.$landed_cost_detail->landedCost->code,
                                ];
                                
                                if(!in_array($landed_cost_detail->landedCost->id, $data_id_lc)){
                                    $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                    $added = true; 
                                }
                                
                                
                                
                            }
                        }

                        //invoice searching
                        if($good_receipt_detail->purchaseInvoiceDetail()->exists()){
                            foreach($good_receipt_detail->purchaseInvoiceDetail as $invoice_detail){
                                $properties = [
                                    ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($invoice_detail->purchaseInvoice).number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')];
                                }
                                $invoice_tempura=[
                                    'properties'=> $properties,
                                    'key'=>$invoice_detail->purchaseInvoice->code,
                                    'name'=>$invoice_detail->purchaseInvoice->code,
                                    'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                                ];

                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_gr->code.$invoice_detail->purchaseInvoice->code
                                ];
                                
                                if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                    $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                    
                                    $added = true; 
                                }
                            }
                        }

                        if($good_receipt_detail->goodScale()->exists()){
                            $name = $good_receipt_detail->goodScale->supplier->name ?? null;
                            $properties = [
                                ['name'=> "Tanggal: ".$good_receipt_detail->goodScale->post_date],
                                ['name'=> "Vendor  : ".($name !== null ? $name : ' ')],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodScale).number_format($good_receipt_detail->goodScale->grandtotal,2,',','.')];
                            }
                            $data_gscale = [
                                    'properties'=> $properties,
                                    'key'=>$good_receipt_detail->goodScale->code,
                                    'name'=>$good_receipt_detail->goodScale->code,
                                    'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($good_receipt_detail->goodScale->code),
                                ];
                                $data_go_chart[]=$data_gscale;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$good_receipt_detail->goodScale->code,
                                    'string_link'=>$good_receipt_detail->goodScale->code.$query_gr->code
                                ];
                                $data_id_good_scale[]= $good_receipt_detail->goodScale->id; 
                            
                        }

                    }
                }
                
            }

            foreach($data_id_op as $op_id){
                if(!in_array($op_id, $finished_data_id_op)){
                    $finished_data_id_op[]= $op_id;
                    $query = OutgoingPayment::where('id',$op_id)->first();
                    if($query->paymentRequest()->exists()){
                        foreach($query->paymentRequest->paymentRequestDetail as $row_pyr_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] =  ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pyr_tempura=[
                                'properties'=> $properties,
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                           
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->paymentRequest->code,
                                'to'=>$query->code,
                                'string_link'=>$row_pyr_detail->paymentRequest->code.$query->code,
                            ]; 
                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            
                            if($row_pyr_detail->fundRequest()){
                                $properties = [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        
                                    ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =  ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_fund_tempura=[
                                    'properties'=>$properties,
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_frs[]= $row_pyr_detail->lookable->id;  
                                    
                                
                                
                            }
                            foreach($row_pyr_detail->paymentRequest->paymentRequestDetail as $row_pyrd){
                                if($row_pyrd->purchaseDownPayment()){
                                    $properties = [
                                        ['name'=> "Tanggal :".$row_pyrd->lookable->post_date],
                                         
                                        ];
                                    
                                    if (!$hide_nominal) {
                                        $properties[] =    ['name'=> "Nominal :".formatNominal($row_pyrd->lookable).number_format($row_pyrd->lookable->grandtotal,2,',','.')]
                                        ;
                                    }
                                    $data_downp_tempura = [
                                        'properties'=> $properties,
                                        "key" => $row_pyrd->lookable->code,
                                        "name" => $row_pyrd->lookable->code,
                                        'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyrd->lookable->code),  
                                    ];
        
                                    $data_go_chart[]=$data_downp_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyrd->lookable->code,
                                        'to'=>$row_pyrd->paymentRequest->code,
                                        'string_link'=>$row_pyrd->lookable->code.$row_pyrd->paymentRequest->code,
                                    ]; 
                                    $data_id_dp[]= $row_pyrd->lookable->id;  
        
                                }  
                            }
                        }
                    }
                    if($query->paymentRequestCross()){
                       
                        foreach($query->paymentRequestCross as $row_pyr_cross){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_cross->lookable).number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pyrc_tempura = [
                                'properties'=> $properties,
                                "key" => $row_pyr_cross->lookable->code,
                                "name" => $row_pyr_cross->lookable->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                            ];
                
                            $data_go_chart[]=$data_pyrc_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_cross->lookable->code,
                                'to'=>$query->code,
                                'string_link'=>$row_pyr_cross->lookable->code.$query->code,
                            ];
                          
                            if(!in_array($row_pyr_cross->id, $data_id_pyrcs)){
                                $data_id_pyrcs[] = $row_pyr_cross->id;
                                
                            }
                        }
                    }
                }
            }

            foreach($data_id_cb as $cb_id){
                if(!in_array($cb_id,$finished_data_id_cb)){
                    $finished_data_id_cb[]= $cb_id; 
                    $query_cb = CloseBill::find($cb_id);
                    foreach($query_cb->closeBillDetail as $row_bill_detail){
                        if($row_bill_detail->outgoingPayment()->exists()){
                            
                            $outgoingpaymnet = [
                                'key'   => $row_bill_detail->outgoingPayment->code,
                                "name"  => $row_bill_detail->outgoingPayment->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->outgoingPayment->pay_date))],
                                    ['name'=> "Nominal: Rp".number_format($row_bill_detail->outgoingPayment->grandtotal,2,',','.')]
                                ],
                                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_bill_detail->outgoingPayment->code),
                                "title" => $row_bill_detail->outgoingPayment->code,
                            ];
                            $data_go_chart[]=$outgoingpaymnet;
                            $data_link[]=[
                                'from'=>$row_bill_detail->outgoingPayment->code,
                                'to'=>$query_cb->code,
                                'string_link'=>$row_bill_detail->outgoingPayment->code.$query_cb->code,
                            ];
                            if(!in_array($row_bill_detail->outgoingPayment->id, $data_id_op)){
                                $data_id_op[]= $row_bill_detail->outgoingPayment->id; 
                                $added = true; 
                            }
                        }
                        
                        
                        if($row_bill_detail->personalCloseBill()->exists()){
                            $data_pcb = [
                                'key'   => $row_bill_detail->personalCloseBill->code,
                                "name"  => $row_bill_detail->personalCloseBill->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->personalCloseBill->post_date))],
                                    ['name'=> "Nominal: Rp".number_format($row_bill_detail->personalCloseBill->grandtotal,2,',','.')]
                                ],
                                'url'   =>request()->root()."/admin/finance/close_bill_personal?code=".CustomHelper::encrypt($row_bill_detail->personalCloseBill->code),
                                "title" => $row_bill_detail->personalCloseBill->code,
                            ];
                            $data_go_chart[]=$data_pcb;

                            $data_link[]=[
                                'from'=>$row_bill_detail->personalCloseBill->code,
                                'to'=>$query_cb->code,
                                'string_link'=>$row_bill_detail->personalCloseBill->code.$query_cb->code,
                            ];
                            if(!in_array($row_bill_detail->personalCloseBill->id, $data_id_pcb)){
                                $data_id_pcb[]= $row_bill_detail->personalCloseBill->id; 
                                $added = true; 
                            }
                        }
                            
                    }

                }
            }

            foreach($data_id_good_scale as $gs_id){
                if(!in_array($gs_id, $finished_data_id_gscale)){
                    $finished_data_id_gscale[]=$gs_id;
                    $query_gs = GoodScale::where('id',$gs_id)->first();
                    
                    if($query_gs->goodReceiptDetail()->exists()){
                        foreach($query_gs->goodReceiptDetail as $row_scale){
                            $name = $row_scale->goodReceipt->supplier->name ?? null;
                            $gr = [
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$row_scale->goodReceipt->post_date],
                                    ['name'=> "Vendor  : ".($name !== null ? $name : ' ')],
                                    
                                ],
                                'key'=>$row_scale->goodReceipt->code,
                                'name'=>$row_scale->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($row_scale->goodReceipt->code),
                            ];

                            $data_go_chart[]=$gr;
                            $data_link[]=[
                                'from'=>$row_scale->goodReceipt->code,
                                'to'=>$query_gs->code,
                                'string_link'=>$row_scale->goodReceipt->code.$query_gs->code
                            ];
                            if(!in_array($row_scale->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[]= $row_scale->goodReceipt->id; 
                                $added = true; 
                            }
                            $data_id_gr[]= $row_scale->goodReceipt->id; 
                        }
                    }

                    if($query_gs->goodScaleDetail()->exists()){
                        foreach($query_gs->goodScaleDetail as $row_detail){
                            if($row_detail->marketingOrderDelivery()->exists()){
                                 $mod_tempura = [
                                     'properties'=> [
                                         ['name'=> "Tanggal: ".$row_detail->marketingOrderDelivery->post_date],
                                         
                                     ],
                                     'key'=>$row_detail->marketingOrderDelivery->code,
                                     'name'=>$row_detail->marketingOrderDelivery->code,
                                     'url'=>request()->root()."/admin/sales/marketing_order_delivery?code=".CustomHelper::encrypt($row_scale->goodReceipt->code),
                                 ];
 
                                 $data_go_chart[]=$mod_tempura;
                                 $data_link[]=[
                                     'from'=>$row_detail->marketingOrderDelivery->code,
                                     'to'=>$query_gs->code,
                                     'string_link'=>$row_detail->marketingOrderDelivery->code.$query_gs->code
                                 ];
                                 if(!in_array($row_detail->marketingOrderDelivery->id, $data_id_mo_delivery)){
                                     $data_id_mo_delivery[]= $row_detail->marketingOrderDelivery->id; 
                                     $added = true; 
                                 }
                                 $data_id_gr[]= $row_scale->goodReceipt->id;
                            }
                        }
                    }

                    // if($query_gs->purchaseOrderDetail()->exists()){
                    //     $po = [
                    //         'properties'=> [
                    //             ['name'=> "Tanggal: ".$query_gs->purchaseOrderDetail->purchaseOrder->post_date],
                    //             ['name'=> "Vendor  : ".($name !== null ? $name : ' ')],
                                
                    //         ],
                    //         'key'=>$query_gs->purchaseOrderDetail->purchaseOrder->code,
                    //         'name'=>$query_gs->purchaseOrderDetail->purchaseOrder->code,
                    //         'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($query_gs->purchaseOrderDetail->purchaseOrder->code),
                    //     ];

                    //     $data_go_chart[]=$po;
                    //     $data_link[]=[
                    //         'from'=>$query_gs->purchaseOrderDetail->purchaseOrder->code,
                    //         'to'=>$query_gs->code,
                    //         'string_link'=>$query_gs->purchaseOrderDetail->purchaseOrder->code.$query_gs->code
                    //     ];
                    //     if(!in_array($query_gs->purchaseOrderDetail->purchaseOrder->id, $data_id_po)){
                    //         $data_id_po[]= $query_gs->purchaseOrderDetail->purchaseOrder->id; 
                    //         $added = true; 
                    //     }
                    //     $data_id_po[]= $query_gs->purchaseOrderDetail->purchaseOrder->id; 
                    // }

                }
            }

            //mencari goodreturn foreign
            foreach($data_id_greturns as $good_return_id){
                if(!in_array($good_return_id, $finished_data_id_greturns)){
                    $finished_data_id_greturns[]=$good_return_id;
                    $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                    foreach($query_return->goodReturnPODetail as $good_return_detail){
                        $data_good_receipt = [
                            "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        
                        $data_good_receipt[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$data_good_receipt["key"],
                            'to'=>$query_return->code,
                            'string_link'=>$data_good_receipt["key"].$query_return->code,
                        ];
                        
                        if(!in_array($good_return_detail->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                            $data_id_gr[] = $good_return_detail->goodReceiptDetail->goodReceipt->id;
                            $added = true;
                        }
                    }
                }
            }

            // invoice insert foreign

            foreach($data_id_invoice as $invoice_id){
                if(!in_array($invoice_id, $finished_data_id_invoice)){
                    $finished_data_id_invoice[]=$invoice_id;
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrderDetail()){
                            
                            $row_po=$row->lookable->purchaseOrder;
                            if($row_po->isSecretPo()){
                                $name = null;
                            }else{
                                $name = $row_po->supplier->name ?? null;
                               
                            }
                                $properties = [
                                    ['name'=> "Tanggal :".$row_po->post_date],
                                    ['name'=> "Vendor  : ".($name !== null ? $name : '-')],
                                   ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =  ['name'=> "Nominal :".formatNominal($row_po).number_format($row_po->grandtotal,2,',','.')]
                                    ;
                                }
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    'properties'=> $properties,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->code),           
                                ];

                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row_po->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_po->code.$query_invoice->code
                                ]; 
                                $data_id_po[]= $row_po->id;  
                                    
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail()->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $properties = [
                                                ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                            ];
                                            
                                            if (!$hide_nominal) {
                                                $properties[] =  ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                                ;
                                            }
                                            $data_good_receipt=[
                                                'properties'=> $properties,
                                                "key" => $good_receipt_detail->goodReceipt->code,
                                                "name" => $good_receipt_detail->goodReceipt->code,
                                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                            ];
                                            
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row_po->code,
                                                'to'=>$data_good_receipt["key"],
                                                'string_link'=>$row_po->code.$data_good_receipt["key"]
                                            ];
                                            $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            
                                        }
                                    }
                                }
                            
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceiptDetail()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->goodReceipt->post_date],
                                    
                                ],
                                "key" => $row->lookable->goodReceipt->code,
                                "name" => $row->lookable->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->goodReceipt->code),
                            ];

                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query_invoice->code,
                                'string_link'=>$data_good_receipt["key"].$query_invoice->code,
                            ];
                            if(!in_array($row->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $row->lookable->goodReceipt->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCostFeeDetail()){
                            $properties =[
                                ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =   ['name'=> "Nominal :".formatNominal($row->lookable->landedCost).number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                ;
                            }
                            $data_lc=[
                                'properties'=>$properties ,
                                "key" => $row->lookable->landedCost->code,
                                "name" => $row->lookable->landedCost->code,
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$data_lc;
                            $data_link[]=[
                                'from'=>$row->lookable->landedCost->code,
                                'to'=>$query_invoice->code,
                                'string_link'=>$row->lookable->landedCost->code.$query_invoice->code,
                            ];
                            $data_id_lc[] = $row->lookable->landedCost->id;
                            
                        }

                        if($row->purchaseMemoDetail()->exists()){
                            foreach($row->purchaseMemoDetail as $purchase_memodetail){
                                $properties = [
                                    ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                    ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_memo = [
                                    "name"=>$purchase_memodetail->purchaseMemo->code,
                                    "key" => $purchase_memodetail->purchaseMemo->code,
                                    'properties'=>$properties,
                                    'url'=>request()->root()."/admin/finance/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                                ];
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$purchase_memodetail->purchaseMemo->code,
                                    'string_link'=>$query_invoice->code.$purchase_memodetail->purchaseMemo->code,
                                ];
                                $data_id_memo[]=$purchase_memodetail->purchaseMemo->id;
                                $data_go_chart[]=$data_memo;
                            }
                        }

                        if($row->fundRequestDetail()->exists()){
                            $name = $row->fundRequestDetail->fundRequest->account->name ?? null;
                            $properties = [
                                ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                ['name'=> "User :".$name],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $fr=[
                                "name"=>$row->fundRequestDetail->fundRequest->code,
                                "key" => $row->fundRequestDetail->fundRequest->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                            ];
                        
                            $data_go_chart[]=$fr;
                            $data_link[]=[
                                'from'=>$row->fundRequestDetail->fundRequest->code,
                                'to'=>$query_invoice->code,
                                'string_link'=>$row->fundRequestDetail->fundRequest->code.$query_invoice->code,
                            ];
                            if(!in_array($row->fundRequestDetail->fundRequest->id, $data_id_frs)){
                                $data_id_frs[] = $row->fundRequestDetail->fundRequest->id;
                                $added = true; 
                            } 
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $properties =[
                                ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_pi->purchaseDownPayment).number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ;
                            }
                            $data_down_payment=[
                                'properties'=> $properties,
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_pi->purchaseDownPayment->code.$query_invoice->code,
                                ];
            
                            if($row_pi->purchaseDownPayment->hasPaymentRequestDetail()->exists()){
                                foreach($row_pi->purchaseDownPayment->hasPaymentRequestDetail as $row_pyr_detail){
                                    $properties =[
                                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                                    ];
                                    
                                    if (!$hide_nominal) {
                                        $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ;
                                    }
                                    $data_pyr_tempura=[
                                        'properties'=> $properties,
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pi->purchaseDownPayment->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pi->purchaseDownPayment->code.$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        


                                    if($row_pyr_detail->fundRequest()){
                                        $properties =[
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                            ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                            ];
                                        
                                        if (!$hide_nominal) {
                                            $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ;
                                        }
                                        $data_fund_tempura=[
                                            'properties'=>$properties, 
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                    
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ];

                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                            $data_id_frs[] = $row_pyr_detail->lookable->id;
                                            $added = true; 
                                        } 

                                        
                                    }
                                    if($row_pyr_detail->purchaseDownPayment()){
                                        $properties =[
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ];
                                        
                                        if (!$hide_nominal) {
                                            $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ;
                                        }
                                        $data_downp_tempura = [
                                            'properties'=> $properties,
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                        
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        
                                    }
                                    if($row_pyr_detail->purchaseInvoice()){
                                        $properties =[
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ];
                                        
                                        if (!$hide_nominal) {
                                            $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ;
                                        }
                                        $data_invoices_tempura = [
                                            'properties'=> $properties,
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                
                                            
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                        ];
                                        
                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                            $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                            $added=true;
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if($query_invoice->realPaymentRequestDetail()->exists()){
                      
                        foreach($query_invoice->realPaymentRequestDetail as $row_pyr_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pyr_tempura=[
                                'properties'=>$properties,
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_invoice->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            // $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                            if(!in_array($row_pyr_detail->paymentRequest->id, $data_id_pyrs)){
                                $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                $added = true; 
                            
                            }    
                            
                            if($row_pyr_detail->fundRequest()){
                                $properties =  [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_fund_tempura=[
                                    'properties'=>$properties,
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                            
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                ];    
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                    $data_id_frs[] = $row_pyr_detail->lookable->id;
                                    $added = true; 
                                }           
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_downp_tempura = [
                                    'properties'=> $properties,
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];

                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                
                            }
                            if($row_pyr_detail->purchaseInvoice()){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_invoices_tempura = [
                                    'properties'=> $properties,
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                
                                    
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                    $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                    $added=true;
                                }
                            }
                        }
                    }
                }
            }

            foreach($data_id_pyrs as $payment_request_id){
                if(!in_array($payment_request_id, $finished_data_id_pyrs)){
                    $finished_data_id_pyrs[]=$payment_request_id;
                    $query_pyr = PaymentRequest::find($payment_request_id);
                    
                    if($query_pyr->outgoingPayment()->exists()){
                        $properties = [
                            ['name'=> "Tanggal :".$query_pyr->outgoingPayment->pay_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] = ['name'=> "Nominal :".formatNominal($query_pyr->outgoingPayment).number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ;
                        }
                        $outgoing_payment = [
                            'properties'=>$properties,
                            "key" => $query_pyr->outgoingPayment->code,
                            "name" => $query_pyr->outgoingPayment->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyr->outgoingPayment->code),  
                        ];

                        $data_go_chart[]=$outgoing_payment;
                        $data_link[]=[
                            'from'=>$query_pyr->code,
                            'to'=>$query_pyr->outgoingPayment->code,
                            'string_link'=>$query_pyr->code.$query_pyr->outgoingPayment->code,
                        ];
                        if(!in_array($query_pyr->outgoingPayment->id, $data_id_op)){
                            $data_id_op[]= $query_pyr->outgoingPayment->id; 
                            $added = true; 
                        }
                        
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        $properties = [
                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =  ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ;
                        }
                        $data_pyr_tempura=[
                            'properties'=> $properties,
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                    
                        if($row_pyr_detail->fundRequest()){
                            $x= '';
                            $color = ['color' => 'lightgrey'];
                            if($row_pyr_detail->lookable->code == $row_pyr_detail->paymentRequest->code){
                                $x = ' (FR)';
                                if($data_go_chart[0]['key'] == $row_pyr_detail->lookable->code){
                                    unset($data_go_chart[0]);
                                    $color = ['color' => 'lightblue'];
                                }
                            }
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =   ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $data_fund_tempura=[
                                'properties'=>$properties,
                                "key" => $row_pyr_detail->lookable->code.$x,
                                "name" => $row_pyr_detail->lookable->code . $x,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ]+$color;
                        
                            
                            $data_go_chart[]=$data_fund_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code.$x,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ];

                            if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                $data_id_frs[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                            } 
                            
                        }
                        if($row_pyr_detail->purchaseDownPayment()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $data_downp_tempura = [
                                'properties'=> $properties,
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];       
                            
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                            
                            }
                        }
                        if($row_pyr_detail->purchaseInvoice()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $data_invoices_tempura = [
                                'properties'=>$properties,
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                        
                                
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }
                        
                        if($row_pyr_detail->paymentRequest->paymentRequestCross()->exists()){
                        
                        
                            foreach($row_pyr_detail->paymentRequest->paymentRequestCross as $row_pyr_cross){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal :".formatNominal($row_pyr_cross->lookable).number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_pyrc_tempura = [
                                    'properties'=> $properties,
                                    "key" => $row_pyr_cross->lookable->code,
                                    "name" => $row_pyr_cross->lookable->code,
                                    'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                ];
                    
                                $data_go_chart[]=$data_pyrc_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_cross->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                if(!in_array($row_pyr_cross->id, $data_id_pyrcs)){
                                    $data_id_pyrcs[] = $row_pyr_cross->id;
                                    
                                }
                            }

                            
                        }
                    }
                }
                
            }

            foreach($data_id_pyrcs as $payment_request_cross_id){
                
                if(!in_array($payment_request_cross_id, $finished_data_id_pyrcs)){
                    $finished_data_id_pyrcs[]=$payment_request_cross_id;
                    $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                    if($query_pyrc->paymentRequest()->exists()){
                        $properties = [
                            ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->pay_date))],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal :".formatNominal($query_pyrc->paymentRequest).number_format($query_pyrc->paymentRequest->grandtotal,2,',','.')]
                            ;
                        }
                        $data_pyr_tempura = [
                            'key'   => $query_pyrc->paymentRequest->code,
                            "name"  => $query_pyrc->paymentRequest->code,
                            'properties'=>$properties,
                            'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                            "title" =>$query_pyrc->paymentRequest->code,
                        ];
                        $data_go_chart[]=$data_pyr_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                        ];
                        
                        if(!in_array($query_pyrc->paymentRequest->id, $data_id_pyrs)){
                            $data_id_pyrs[] = $query_pyrc->paymentRequest->id;
                            $added=true;
                        }
                        foreach($query_pyrc->paymentRequest->paymentRequestDetail as $row_pyr_detail){
                            if($row_pyr_detail->fundRequest()){
                                $properties = [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->pay_date))],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal :".formatNominal($query_pyrc->paymentRequest).number_format($query_pyrc->paymentRequest->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_fund_tempura=[
                                    'properties'=>$properties,
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                            
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
    
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                    $data_id_frs[] = $row_pyr_detail->lookable->id;
                                    $added = true; 
                                } 
                                
                            }
                        }
                        
                    }
                    if($query_pyrc->outgoingPayment()){
                        $properties =[
                            ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal :".formatNominal($query_pyrc->lookable).number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                            ;
                        }
                        $outgoing_tempura = [
                            'properties'=> $properties,
                            "key" => $query_pyrc->lookable->code,
                            "name" => $query_pyrc->lookable->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                        ];
    
                        $data_go_chart[]=$outgoing_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                        ];
                        if(!in_array($query_pyrc->lookable->id, $data_id_op)){
                            $data_id_op[]= $query_pyrc->lookable->id; 
                            $added = true; 
                        }
                    }
                }
            }
            
            foreach($data_id_dp as $downpayment_id){
                
                if(!in_array($downpayment_id, $finished_data_id_dp)){
                    $finished_data_id_dp[]=$downpayment_id;
                    
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder()->exists()){
                            if($row->purchaseOrder->isSecretPo()){
                                $name = null;
                            }else{ 
                                $name = $row->purchaseOrder->supplier->name ?? null;
                            }
                            $properties = [
                                ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".($name !== null ? $name : ' ')],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row->purchaseOrder).number_format($row->purchaseOrder->grandtotal,2,',','.')]
                                ;
                            }
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                            ];
                        
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$row->purchaseOrder->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$row->purchaseOrder->code.$query_dp->code,
                            ];
                            
                            $data_id_po []=$row->purchaseOrder->id; 
                                
                            /* mendapatkan request po */
                            foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){

                                if($po_detail->purchaseRequestDetail()->exists()){
                                
                                    $pr = [
                                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                        
                                        ],
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                                    ];
                                    $data_go_chart[]=$pr;
                                    $data_link[]=[
                                        'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$row->purchaseOrder->code,
                                        'string_link'=>$po_detail->purchaseRequestDetail->purchaseRequest->code.$row->purchaseOrder->code
                                    ];
                                    $data_id_pr[]=$po_detail->purchaseRequestDetail->purchaseRequest->id;
                                        
                                    
                                }
                                /* mendapatkan gr po */
                                if($po_detail->goodReceiptDetail()->exists()){
                                    foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                        $properties =[
                                            ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                            ];
                                        
                                        if (!$hide_nominal) {
                                            $properties[] =['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                            ;
                                        }
                                        $data_good_receipt = [
                                            'properties'=> $properties,
                                            "key" => $good_receipt_detail->goodReceipt->code,
                                            "name" => $good_receipt_detail->goodReceipt->code,
                                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                                        ];
                                            
                                        $data_go_chart[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$row->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                            'string_link'=>$row->purchaseOrder->code.$data_good_receipt["key"],
                                        ];
                                        
                                        
                                        if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                            $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                            $added = true;
                                        }
                    
                                    }
                                }
                            }
                            
        
                        }
                        
                        if($row->fundRequestDetail()->exists()){
                            $name = $row->fundRequestDetail->fundRequest->account->name ?? null;
                            $properties = [
                                ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                ['name'=> "User :".($name !== null ? $name : ' ')],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $fr=[
                                "name"=>$row->fundRequestDetail->fundRequest->code,
                                "key" => $row->fundRequestDetail->fundRequest->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                            ];
                        
                            $data_go_chart[]=$fr;
                            $data_link[]=[
                                'from'=>$row->fundRequestDetail->fundRequest->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$row->fundRequestDetail->fundRequest->code.$query_dp->code,
                            ];
                            if(!in_array($row->fundRequestDetail->fundRequest->id, $data_id_frs)){
                                $data_id_frs[] = $row->fundRequestDetail->fundRequest->id;
                                $added = true; 
                            } 
                        }
                    }

                    foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                        $properties =  [
                            ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal :".formatNominal($purchase_invoicedp->purchaseInvoice).number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')]
                            ;
                        }
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> $properties,
                            'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                        ];
                        
                        
                        $data_go_chart[]=$invoice_tempura;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            'string_link'=>$query_dp->code.$purchase_invoicedp->purchaseInvoice->code,
                        ];
                        
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
                        }
                    }

                    foreach($query_dp->purchaseMemoDetail as $purchase_memodetail){
                        $properties =[
                            ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')]
                            ;
                        }
                        $data_memo=[
                            "name"=>$purchase_memodetail->purchaseMemo->code,
                            "key" => $purchase_memodetail->purchaseMemo->code,
                            'properties'=> $properties,
                            'url'=>request()->root()."/admin/finance/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                        ];
                        $data_go_chart[]=$data_memo;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_memodetail->purchaseMemo->code,
                            'string_link'=>$query_dp->code.$purchase_memodetail->purchaseMemo->code,
                        ];
                        

                    }

                    if($query_dp->hasPaymentRequestDetail()->exists()){
                        
                        foreach($query_dp->hasPaymentRequestDetail as $row_pyr_detail){
                            $properties =[
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pyr_tempura=[
                                "name"=>$row_pyr_detail->paymentRequest->code,
                                "key" => $row_pyr_detail->paymentRequest->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),           
                            ];
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_dp->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_dp->code.$row_pyr_detail->paymentRequest->code,
                            ];

                            if(!in_array($row_pyr_detail->paymentRequest->id, $data_id_pyrs)){
                                $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                $added=true;
                            }
                        }
                    }
                }

            }

            foreach($data_id_memo as $memo_id){
                if(!in_array($memo_id, $finished_data_id_memo)){
                    $finished_data_id_memo []= $memo_id;
                    $query = PurchaseMemo::find($memo_id);
                    foreach($query->purchaseMemoDetail as $row){
                        if($row->lookable_type == 'purchase_invoice_details'){
                            $properties =[
                                ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row->lookable->purchaseInvoice).number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                ;
                            }
                            $data_invoices_tempura=[
                                'properties'=> $properties,
                                "key" => $row->lookable->purchaseInvoice->code,
                                "name" => $row->lookable->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row->lookable->purchaseInvoice->code),
                            ];
        
                            $data_go_chart[]=$data_invoices_tempura;
                            $data_link[]=[
                                'from'=>$data_invoices_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_invoices_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $row->lookable->purchaseInvoice->id;
                                $added=true;
                            }
                        }elseif($row->lookable_type == 'purchase_down_payments'){
                            $properties = [
                                ['name'=> "Tanggal :".$row->lookable->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row->lookable).number_format($row->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $data_downp_tempura=[
                                'properties'=>$properties,
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$data_downp_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_downp_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row->lookable->id;
                                $added=true;
                            }
                        }
                        
                    }
                }
            }
            
            foreach($data_id_good_issue as $good_issue_id){
                if(!in_array($good_issue_id, $finished_data_id_gissue)){
                    $finished_data_id_gissue[]=$good_issue_id;
                    $query_good_issue = GoodIssue::find($good_issue_id);
                    foreach($query_good_issue->goodIssueDetail as $data_detail_good_issue){
                        if($data_detail_good_issue->materialRequestDetail()){
                            $properties =[
                                ['name'=> "Tanggal :".$data_detail_good_issue->lookable->materialRequest->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->materialRequest).number_format($data_detail_good_issue->lookable->materialRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $material_request_tempura = [
                                "key" => $data_detail_good_issue->lookable->materialRequest->code,
                                "name" => $data_detail_good_issue->lookable->materialRequest->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/purchase/material_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->materialRequest->code),
                            ];

                            $data_go_chart[]=$material_request_tempura;
                            $data_link[]=[
                                'from'=>$data_detail_good_issue->lookable->materialRequest->code,
                                'to'=>$query_good_issue->code,
                                'string_link'=>$data_detail_good_issue->lookable->materialRequest->code.$query_good_issue->code,
                            ];
                            $data_id_mr[] = $data_detail_good_issue->lookable->materialRequest->id;
                        }

                        if($data_detail_good_issue->purchaseOrderDetail()->exists()){
                            foreach($data_detail_good_issue->purchaseOrderDetail as $data_purchase_order_detail){
                                $properties = [
                                    ['name'=> "Tanggal :".$data_purchase_order_detail->purchaseOrder->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal :".formatNominal($data_purchase_order_detail->purchaseOrder).number_format($data_purchase_order_detail->purchaseOrder->grandtotal,2,',','.')]
                                    ;
                                }
                                $po_tempura = [
                                    "key" => $data_purchase_order_detail->purchaseOrder->code,
                                    "name" => $data_purchase_order_detail->purchaseOrder->code,
                                    'properties'=>$properties,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($data_purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$material_request_tempura;
                                $data_link[]=[
                                    'from'=>$query_good_issue->code,
                                    'to'=>$data_purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_good_issue->code.$data_purchase_order_detail->purchaseOrder->code,
                                ];
                                $data_id_po[] = $data_purchase_order_detail->purchaseOrder->id;
                            }
                        }
                        
                        if($data_detail_good_issue->goodIssueRequestDetail()){
                            $properties = [
                                ['name'=> "Tanggal :".$data_detail_good_issue->lookable->goodIssueRequest->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->goodIssueRequest).number_format($data_detail_good_issue->lookable->goodIssueRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $good_issue_request_tempura = [
                                "key" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                "name" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/inventory/good_issue_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->goodIssueRequest->code),
                            ];

                            $data_go_chart[]=$good_issue_request_tempura;
                            $data_link[]=[
                                'from'=>$data_detail_good_issue->lookable->goodIssueRequest->code,
                                'to'=>$query_good_issue->code,
                                'string_link'=>$data_detail_good_issue->lookable->goodIssueRequest->code.$query_good_issue->code,
                            ];
                            $data_id_gir[] = $data_detail_good_issue->lookable->goodIssueRequest->id;  
                        }
                    }
                }
            }

            foreach($data_id_lc as $landed_cost_id){
                if(!in_array($landed_cost_id, $finished_data_id_lc)){
                    $finished_data_id_lc[]=$landed_cost_id;
                    $query= LandedCost::find($landed_cost_id);
                    foreach($query->landedCostDetail as $lc_detail ){
                        if($lc_detail->goodReceiptDetail()){
                            $data_good_receipt = [
                                "key" => $lc_detail->lookable->goodReceipt->code,
                                'name'=> $lc_detail->lookable->goodReceipt->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                    
                                ],
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
                            ];
                            
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_good_receipt["key"].$query->code,
                            ];
                            
                            
                            if(!in_array($lc_detail->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $lc_detail->lookable->goodReceipt->id;
                                $added = true;
                            }

                        }
                        if($lc_detail->landedCostDetail()){
                            $properties = [
                                ['name'=> "Tanggal :".$lc_detail->lookable->landedCost->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($lc_detail->lookable->landedCost).number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')]
                                ;
                            }
                            $lc_other = [
                                "key" => $lc_detail->lookable->landedCost->code,
                                "name" => $lc_detail->lookable->landedCost->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($lc_detail->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$lc_other;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->landedCost->code,
                                'string_link'=>$query->code.$lc_detail->lookable->landedCost->code,
                            ];
                            if(!in_array($lc_detail->lookable->landedCost->id,$data_id_lc)){
                                $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                $added = true;
                            }
                        
                                            
                        }//??
                        if($lc_detail->inventoryTransferOutDetail()){
                            $properties = [
                                ['name'=> "Tanggal :".$lc_detail->lookable->inventoryTransferOut->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($lc_detail->lookable->inventoryTransferOut).number_format($lc_detail->lookable->inventoryTransferOut->grandtotal,2,',','.')];
                            }
                            $inventory_transfer_out = [
                                "key" => $lc_detail->lookable->inventoryTransferOut->code,
                                "name" => $lc_detail->lookable->inventoryTransferOut->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($lc_detail->lookable->inventoryTransferOut->code),
                            ];

                            $data_go_chart[]=$inventory_transfer_out;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->inventoryTransferOut->code,
                                'string_link'=>$query->code.$lc_detail->lookable->inventoryTransferOut->code,
                            ];
                            $data_id_inventory_transfer_out[] = $lc_detail->lookable->inventoryTransferOut->id;
                                            
                        }
                    } // inventory transferout detail apakah perlu
                    if($query->landedCostFeeDetail()->exists()){
                        foreach($query->landedCostFeeDetail as $row_landedfee_detail){
                            foreach($row_landedfee_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                $properties =  [
                                    ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')];
                                }
                                $data_invoices_tempura = [
                                    'key'   => $row_invoice_detail->purchaseInvoice->code,
                                    "name"  => $row_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=>$properties,
                                    'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                ];
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'  =>  $query->code,
                                    'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query->code.$row_invoice_detail->purchaseInvoice->code
                                ];
                                if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                    $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                    $added = true;
                                }
                            }
                        
                        }
                    }
                }
            }

            foreach($data_id_inventory_transfer_out as $id_transfer_out){
                if(!in_array($id_transfer_out, $finished_data_id_invetory_to)){
                    $finished_data_id_invetory_to[]=$id_transfer_out;
                    $query_inventory_transfer_out = InventoryTransferOut::find($id_transfer_out);
                    foreach($query_inventory_transfer_out->inventoryTransferOutDetail as $row_transfer_out_detail){
                        if($row_transfer_out_detail->landedCostDetail->exists()){
                            $properties =[
                                ['name'=> "Tanggal :".$row_transfer_out_detail->landedCostDetail->landedCost->post_date],
                               ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_transfer_out_detail->landedCostDetail).number_format($row_transfer_out_detail->landedCostDetail->landedCost->grandtotal,2,',','.')];
                            }
                            $lc_tempura = [
                                "key" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                "name" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($row_transfer_out_detail->landedCostDetail->landedCost->code),
                            ];

                            $data_go_chart[]=$lc_tempura;
                            $data_link[]=[
                                'from'=>$query_inventory_transfer_out->code,
                                'to'=>$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                'string_link'=>$query_inventory_transfer_out->code.$row_transfer_out_detail->landedCostDetail->landedCost->code,
                            ];
                            if(!in_array($row_transfer_out_detail->landedCostDetail->landedCost->id,$data_id_lc)){
                                $data_id_lc[] = $row_transfer_out_detail->landedCostDetail->landedCost->id;
                                $added = true;
                            }
                        
                                
                        }
                    }
                }
            }

            foreach($data_id_pcb as $pcb_id){
                if(!in_array($pcb_id, $finished_data_id_pcb)){
                    $finished_data_id_pcb[]=$pcb_id;
                    $query_pcb = PersonalCloseBill::find($pcb_id);

                    foreach($query_pcb->personalCloseBillDetail as $row_pcbd){
                        if($row_pcbd->fundRequest()->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pcbd->fundRequest->code],
                                ['name'=> "User :".$row_pcbd->fundRequest->account->name],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row_pcbd->fundRequest).number_format($row_pcbd->fundRequest->grandtotal,2,',','.')];
                            }
                            $data_fund_tempura=[
                                'properties'=>$properties,
                                "key" => $row_pcbd->fundRequest->code,
                                "name" => $row_pcbd->fundRequest->code,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pcbd->fundRequest->code), 
                            ];
                        
                            $data_go_chart[]=$data_fund_tempura;
                            $data_link[]=[
                                'from'=>$row_pcbd->fundRequest->code,
                                'to'=>$query_pcb->code,
                                'string_link'=>$row_pcbd->fundRequest->code.$query_pcb->code,
                            ];

                            if(!in_array($row_pcbd->fundRequest->id, $data_id_frs)){
                                $data_id_frs[] = $row_pcbd->fundRequest->id;
                                $added = true; 
                            } 
                        }
                    }

                    if($query_pcb->closebillDetail()->exists()){
                        foreach($query_pcb->closebillDetail as $row_cbd){
                            $properties = [
                                ['name'=> "Tanggal :".$row_cbd->closeBill->code],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal :".formatNominal($row_cbd->closeBill).number_format($row_cbd->closeBill->grandtotal,2,',','.')]
                                ;
                            }
                            $data_cb_tempura=[
                                'properties'=>$properties,
                                "key" => $row_cbd->closeBill->code,
                                "name" => $row_cbd->closeBill->code,
                                'url'=>request()->root()."/admin/finance/close_bill?code=".CustomHelper::encrypt($row_cbd->closeBill->code), 
                            ];
                        
                            $data_go_chart[]=$data_cb_tempura;
                            $data_link[]=[
                                'from'=>$query_pcb->code,
                                'to'=>$row_cbd->closeBill->code,
                                'string_link'=>$query_pcb->code.$row_cbd->closeBill->code,
                            ];

                            if(!in_array($row_cbd->closeBill->id, $data_id_cb)){
                                $data_id_cb[] = $row_cbd->closeBill->id;
                                $added = true; 
                            } 
                        }
                    }
                }
            }

            foreach($data_id_frs as $fr_id){
                if(!in_array($fr_id, $finished_data_id_frs)){
                    $finished_data_id_frs[]=$fr_id;
                    $query_fr = FundRequest::find($fr_id);
                   
                    foreach($query_fr->fundRequestDetail as $row_fr_detail){
                        if($row_fr_detail->hasPaymentRequestDetail()->exists()){
                            foreach($row_fr_detail->hasPaymentRequestDetail as $row_pyr_detail){
                                $x= '';
                                if($row_pyr_detail->paymentRequest->code == $row_pyr_detail->paymentRequest->code){
                                    $x = ' (PYR)';
                                }
                                $properties =  [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_pyr_tempura=[
                                    'properties'=>$properties,
                                    "key" => $row_pyr_detail->paymentRequest->code . $x,
                                    "name" => $row_pyr_detail->paymentRequest->code . $x,
                                    'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                ];
                                $data_go_chart[]=$data_pyr_tempura;
                                $data_link[]=[
                                    'from'=>$query_fr->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code. $x,
                                    'string_link'=>$query_fr->code.$row_pyr_detail->paymentRequest->code.$x,
                                ];
                                if(!in_array($row_pyr_detail->paymentRequest->id,$data_id_pyrs)){
                                    $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                    $added = true;
                                } 
                                
                            }
                        }
                        
                        if($row_fr_detail->purchaseInvoiceDetail()->exists()){
                            foreach($row_fr_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                $properties =  [
                                    ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                    ;
                                }
                                $data_invoices_tempura = [
                                    'key'   => $row_invoice_detail->purchaseInvoice->code,
                                    "name"  => $row_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=>$properties,
                                    'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                ];
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_fr->code,
                                    'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_fr->code.$row_invoice_detail->purchaseInvoice->code
                                ];
                                if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                    $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                    $added = true;
                                }
                            }
                        }

                        if($row_fr_detail->purchaseDownPaymentDetail()->exists()){
                            foreach($row_fr_detail->purchaseDownPaymentDetail as $row_dp_detail){
                                $data_apdp_tempura = [
                                    'key'   => $row_dp_detail->purchaseDownPayment->code,
                                    "name"  => $row_dp_detail->purchaseDownPayment->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                        ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                    ],
                                    'url'   =>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                                ];
                                $data_go_chart[]=$data_apdp_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_fr->code,
                                    'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                    'string_link'=>$query_fr->code.$row_dp_detail->purchaseDownPayment->code,
                                ];
                                if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                    $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                    $added = true;
                                } 
                            }
                        }
                    }
                    if($query_fr->hasPaymentRequestDetail()->exists()){
                        foreach($query_fr->hasPaymentRequestDetail as $row_pyr_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->pay_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pyr_tempura=[
                                'properties'=>$properties,
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_fr->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_fr->code.$row_pyr_detail->paymentRequest->code,
                            ];
                            if(!in_array($row_pyr_detail->paymentRequest->id,$data_id_pyrs)){
                                $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                $added = true;
                            } 
                            
                        }
                    }
                    if($query_fr->personalCloseBillDetail()->exists()){

                        foreach($query_fr->personalCloseBillDetail as $row_pcbd){
                            $properties = [
                                ['name'=> "Tanggal :".$row_pcbd->personalCloseBill->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal :".formatNominal($row_pcbd->personalCloseBill).number_format($row_pcbd->personalCloseBill->grandtotal,2,',','.')]
                                ;
                            }
                            $data_pcb_tempura=[
                                'properties'=>$properties,
                                "key" => $row_pcbd->personalCloseBill->code,
                                "name" => $row_pcbd->personalCloseBill->code,
                                'url'=>request()->root()."/admin/finance/close_bill_personal?code=".CustomHelper::encrypt($row_pcbd->personalCloseBill->code),
                            ];
                            $data_go_chart[]=$data_pcb_tempura;
                            $data_link[]=[
                                'from'=>$query_fr->code,
                                'to'=>$row_pcbd->personalCloseBill->code,
                                'string_link'=>$query_fr->code.$row_pcbd->personalCloseBill->code,
                            ];
                            if(!in_array($row_pcbd->personalCloseBill->id,$data_id_pcb)){
                                $data_id_pcb[] = $row_pcbd->personalCloseBill->id;
                                $added = true;
                            } 
                        }
                    }

                }
            }

            //Pengambilan foreign branch po
            foreach($data_id_po as $po_id){
                if(!in_array($po_id, $finished_data_id_po)){
                    $finished_data_id_po[]=$po_id;
                    $query_po = PurchaseOrder::find($po_id);
                
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                        
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                        
                            $pr_tempura=[
                                'key'   => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                "name"  => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_order_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                    
                            $data_go_chart[]=$pr_tempura;
                            $data_link[]=[
                                'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                'to'=>$query_po->code,
                                'string_link'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code.$query_po->code,
                            ];
                            $data_id_pr[]=$purchase_order_detail->purchaseRequestDetail->purchaseRequest->id;
                            
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        
                                    ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseOrder->code,
                                    'to'=>$data_good_receipt["key"],
                                    'string_link'=>$purchase_order_detail->purchaseOrder->code.$data_good_receipt["key"],
                                ];
                                
                                $data_go_chart[]=$data_good_receipt;  
                                
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        if($purchase_order_detail->goodIssueDetail()->exists()){
                            $good_issue_tempura=[
                                'key'   => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                "name"  => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->goodIssueDetail->goodIssue->post_date],
                                
                                ],
                                'url'   =>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($purchase_order_detail->goodIssueDetail->goodIssue->code),
                            ];
                    
                            $data_go_chart[]=$good_issue_tempura;
                            $data_link[]=[
                                'from'=>$query_po->code,
                                'to'=>$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                'string_link'=>$query_po->code.$purchase_order_detail->goodIssueDetail->goodIssue->code,
                            ];
                            
                            if(!in_array($purchase_order_detail->goodIssueDetail->goodIssue->id,$data_id_good_issue)){
                                $data_id_good_issue[]=$purchase_order_detail->goodIssueDetail->goodIssue->id;
                                $added = true;
                            }
                            
                        }
                        if($purchase_order_detail->purchaseInvoiceDetail()->exists()){
                            foreach($purchase_order_detail->purchaseInvoiceDetail as $purchase_invoice_detail){
                                $data_invoices_tempura = [
                                    'key'   => $purchase_invoice_detail->purchaseInvoice->code,
                                    "name"  => $purchase_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_invoice_detail->purchaseInvoice->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoice_detail->purchaseInvoice->code),
                                ];
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_po->code,
                                    'to'    =>  $purchase_invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_po->code.$purchase_invoice_detail->purchaseInvoice->code,
                                ];
                                if(!in_array($purchase_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                    $data_id_invoice[]=$purchase_invoice_detail->purchaseInvoice->id;
                                    $added = true;
                                }
                            
                            }
                        }
                        if($purchase_order_detail->marketingOrderDeliveryProcess()->exists()){
                            
                            $data_marketing_order_delivery_process = [
                                'key'   => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                "name"  => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->marketingOrderDeliveryProcess->post_date],
                                
                                ],
                                'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_order_detail->marketingOrderDeliveryProcess->code),
                            ];
                            $data_go_chart[]=$data_marketing_order_delivery_process;
                            $data_link[]=[
                                'from'  =>  $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                'to'    =>  $query_po->code,
                                'string_link'=>$purchase_order_detail->marketingOrderDeliveryProcess->code.$query_po->code,
                            ];
                            if(!in_array($purchase_order_detail->marketingOrderDeliveryProcess->id,$data_id_mo_delivery_process)){
                                $data_id_mo_delivery_process[]=$purchase_order_detail->marketingOrderDeliveryProcess->id;
                                $added = true;
                            }
                            
                            
                        }
                        // if($purchase_order_detail->goodScale()->exists()){
                        //     foreach($purchase_order_detail->goodScale as $row_scale){
                        //         $data_good_scale_tempura = [
                        //             'key'   => $row_scale->code,
                        //             "name"  => $row_scale->code,
                                
                        //             'properties'=> [
                        //                 ['name'=> "Tanggal: ".$row_scale->post_date],
                                    
                        //             ],
                        //             'url'   =>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($row_scale->code),
                        //         ];
                        //         $data_go_chart[]=$data_good_scale_tempura;
                        //         $data_link[]=[
                        //             'from'  =>   $query_po->code,
                        //             'to'    =>   $row_scale->code,
                        //             'string_link'=>$query_po->code.$row_scale->code,
                        //         ];
                        //         if(!in_array($row_scale->id,$data_id_good_scale)){
                        //             $data_id_good_scale[]=$row_scale->id;
                        //             $added = true;
                        //         }
                        //     }
                        // }
                        
                    }

                    if($query_po->purchaseDownPaymentDetail()->exists()){
                        
                        foreach($query_po->purchaseDownPaymentDetail as $row_dp_detail){
                            $data_apdp_tempura = [
                                'key'   => $row_dp_detail->purchaseDownPayment->code,
                                "name"  => $row_dp_detail->purchaseDownPayment->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                    ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                ],
                                'url'   =>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                            ];
                            $data_go_chart[]=$data_apdp_tempura;
                            $data_link[]=[
                                'from'  =>  $query_po->code,
                                'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                'string_link'=>$query_po->code.$row_dp_detail->purchaseDownPayment->code,
                            ];
                            if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                $added = true;
                            } 
                        }
                    }
                }

            }

            foreach($data_id_pr as $pr_id){
                if(!in_array($pr_id, $finished_data_id_pr)){
                    $finished_data_id_pr[]=$pr_id;
                    $query_pr = PurchaseRequest::find($pr_id);
                    foreach($query_pr->purchaseRequestDetail as $purchase_request_detail){
                        if($purchase_request_detail->purchaseOrderDetail()->exists()){
                        
                            foreach($purchase_request_detail->purchaseOrderDetail as $purchase_order_detail){
                                if($purchase_order_detail->purchaseOrder->isSecretPo()){
                                    $name = null;
                                   
                                }else{
                                    $name = $purchase_order_detail->purchaseOrder->supplier->name ?? null;
                                }
                               
                                $po_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".($name !== null ? $name : '-')],
                                    ],
                                    'key'=>$purchase_order_detail->purchaseOrder->code,
                                    'name'=>$purchase_order_detail->purchaseOrder->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_pr->code,
                                    'to'=>$purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_pr->code.$purchase_order_detail->purchaseOrder->code,
                                ];
                                if(!in_array($purchase_order_detail->purchaseOrder->id,$data_id_po)){
                                    $data_id_po[] = $purchase_order_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }                     
                        
                        }
                        if($purchase_request_detail->materialRequestDetail()){
                            $mr=[
                                'properties'=> [
                                    ['name'=> "Tanggal : ".$purchase_request_detail->lookable->materialRequest->post_date],
                                    ['name'=> "Vendor  : ".$purchase_request_detail->lookable->materialRequest->user->name ?? ' '],
                                ],
                                'key'=>$purchase_request_detail->lookable->materialRequest->code,
                                'name'=>$purchase_request_detail->lookable->materialRequest->code,
                                'url'=>request()->root()."/admin/purchase/material_request?code=".CustomHelper::encrypt($purchase_request_detail->lookable->materialRequest->code),
                            ];
                            
                            $data_go_chart[]=$mr;
                            $data_link[]=[
                                'from'=>$purchase_request_detail->lookable->materialRequest->code,
                                'to'=>$query_pr->code,
                                'string_link'=>$purchase_request_detail->lookable->materialRequest->code.$query_pr->code,
                            ];
                            if(!in_array($purchase_request_detail->lookable->materialRequest->id,$data_id_mr)){
                                $data_id_mr[]= $purchase_request_detail->lookable->materialRequest->id;  
                                $added = true;
                            }
                        
                            
                        }
                    }
                }
            }

            foreach($data_id_gir as $gir_id){
                if(!in_array($gir_id, $finished_data_id_gir)){
                    $finished_data_id_gir[]=$gir_id;
                    $query_good_issue_request = GoodIssueRequest::find($gir_id);
                    foreach($query_good_issue_request->goodIssueRequestDetail as $row_gird){
                        if($row_gird->goodIssueDetail()->exists()){
                            foreach($row_gird->goodIssueDetail as $good_issue_detail){
                                $good_issue_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                    ],
                                    'key'=>$good_issue_detail->goodIssue->code,
                                    'name'=>$good_issue_detail->goodIssue->code,
                                    'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                ];
    
                                $data_go_chart[]=$good_issue_tempura;
                                $data_link[]=[
                                    'from'=>$query_good_issue_request->code,
                                    'to'=>$good_issue_detail->goodIssue->code,
                                    'string_link'=>$query_good_issue_request->code.$good_issue_detail->goodIssue->code,
                                ];
                                if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                    $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                    $added = true;
                                }
                            }
                        }
                        
                    }

                }
            }

            foreach($data_id_mr as $mr_id){
                if(!in_array($mr_id, $finished_data_id_mr)){
                    $finished_data_id_mr[]=$mr_id;
                    $query_material_request = MaterialRequest::find($mr_id);
                    foreach($query_material_request->materialRequestDetail as $row_material_request_detail){
                        if($row_material_request_detail->purchaseRequestDetail()->exists()){
                        
                            foreach($row_material_request_detail->purchaseRequestDetail as $row_purchase_request_detail){
                                $pr_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$row_purchase_request_detail->purchaseRequest->post_date],
                                        ['name'=> "Vendor  : ".$row_purchase_request_detail->purchaseRequest->user->name ?? ' '],
                                    ],
                                    'key'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'name'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_purchase_request_detail->purchaseRequest->code),
                                ];
    
                                $data_go_chart[]=$pr_tempura;
                                $data_link[]=[
                                    'from'=>$query_material_request->code,
                                    'to'=>$row_purchase_request_detail->purchaseRequest->code,
                                    'string_link'=>$query_material_request->code.$row_purchase_request_detail->purchaseRequest->code,
                                ];
                                if(!in_array($row_purchase_request_detail->purchaseRequest->id,$data_id_pr)){
                                    $data_id_pr[] = $row_purchase_request_detail->purchaseRequest->id;
                                    $added = true;
                                }
                            }                     
                        
                        }
                        if($row_material_request_detail->goodIssueDetail()->exists()){
                        
                            foreach($row_material_request_detail->goodIssueDetail as $good_issue_detail){
                                $good_issue_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                        ['name'=> "User  : ".$good_issue_detail->goodIssue->user->name ?? ' '],
                                    ],
                                    'key'=>$good_issue_detail->goodIssue->code,
                                    'name'=>$good_issue_detail->goodIssue->code,
                                    'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                ];
    
                                $data_go_chart[]=$good_issue_tempura;
                                $data_link[]=[
                                    'from'=>$query_material_request->code,
                                    'to'=>$good_issue_detail->goodIssue->code,
                                    'string_link'=>$query_material_request->code.$good_issue_detail->goodIssue->code,
                                ];
                            
                                if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                    $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                    $added = true;
                                }
                            }                     
                        
                        }
                    }
                }
            }

            // mencaari incoming payment
            foreach($data_incoming_payment as $row_id_ip){
                if(!in_array($row_id_ip, $finished_data_id_ip)){
                    $finished_data_id_ip[]=$row_id_ip;
                    $query_ip = IncomingPayment::find($row_id_ip);
                    foreach($query_ip->incomingPaymentDetail as $row_ip_detail){
                        if($row_ip_detail->marketingOrderDownPayment()->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_ip_detail->marketingOrderDownPayment->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderDownPayment->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_downpayment=[
                                "name"=>$row_ip_detail->marketingOrderDownPayment->code,
                                "key" => $row_ip_detail->marketingOrderDownPayment->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/finance/incoming_payment?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderDownPayment->code),
                            ];
                            $data_go_chart[]=$mo_downpayment;
                            $data_link[]=[
                                'from'=>$row_ip_detail->marketingOrderDownPayment->code,
                                'to'=>$query_ip->code,
                                'string_link'=>$row_ip_detail->marketingOrderDownPayment->code.$query_ip->code,
                            ];
                            $data_id_mo_dp[] = $row_ip_detail->marketingOrderDownPayment->id;
                            
                        }
                        if($row_ip_detail->marketingOrderInvoice()->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_ip_detail->marketingOrderInvoice->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderInvoice->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_invoice=[
                                "name"=>$row_ip_detail->marketingOrderInvoice->code,
                                "key" => $row_ip_detail->marketingOrderInvoice->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderInvoice->code),
                            ];
                            $data_go_chart[]=$mo_invoice;
                            $data_link[]=[
                                'from'=>$row_ip_detail->marketingOrderInvoice->code,
                                'to'=>$query_ip->code,
                                'string_link'=>$row_ip_detail->marketingOrderInvoice->code.$query_ip->code,
                            ];
                            $data_id_mo_invoice[] = $row_ip_detail->marketingOrderInvoice->id;
                            
                        }
                    }
                }
            }
            // menacari down_payment
            foreach($data_id_mo_dp as $row_id_dp){
                if(!in_array($row_id_dp, $finished_data_id_dp)){
                    $finished_data_id_dp[]=$row_id_dp;
                    $query_dp= MarketingOrderDownPayment::find($row_id_dp);
                    
                    if($query_dp->incomingPaymentDetail()->exists()){
                        foreach($query_dp->incomingPaymentDetail as $row_incoming_payment){
                            $properties = [
                                ['name'=> "Tanggal :".$row_incoming_payment->incomingPayment->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_incoming_payment->incomingPayment->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_incoming_payment=[
                                "name"=>$row_incoming_payment->incomingPayment->code,
                                "key" => $row_incoming_payment->incomingPayment->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_incoming_payment->incomingPayment->code),
                            ];
                            $data_go_chart[]=$mo_incoming_payment;
                            $data_link[]=[
                                'from'=>$query_dp->code,
                                'to'=>$row_incoming_payment->incomingPayment->code,
                                'string_link'=>$query_dp->code.$row_incoming_payment->incomingPayment->code,
                            ];
                            if(!in_array($row_incoming_payment->incomingPayment->id, $data_incoming_payment)){
                                $data_incoming_payment[] = $row_incoming_payment->incomingPayment->id;
                                $added = true;
                            }
                        }
                    }
                    
                    if($query_dp->marketingOrderInvoiceDetail()->exists()){
                        $arr = [];
                        foreach($query_dp->marketingOrderInvoiceDetail as $row_invoice_detail){
                            if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                                foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                                    $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                                }
                            }
                            
                            $newArray = array_unique($arr);
                            $string = implode(', ', $newArray);
                            $properties = [
                                ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                                ['name'=> "No Surat Jalan  :".$string.""]
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')]
                                ;
                            }
                            $data_invoice = [
                                "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                                "key" => $row_invoice_detail->marketingOrderInvoice->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                            ];
                            
                            $data_go_chart[]=$data_invoice;
                            $data_link[]=[
                                'from'=>$row_invoice_detail->marketingOrderInvoice->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$query_dp->code.$row_invoice_detail->marketingOrderInvoice->code,
                            ];
                            
                            if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                                $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                                $added = true;
                            }
                        }
                    }
                }


            }
            //marketing mo receipt
            foreach($data_id_mo_receipt as $id_mo_receipt){
                if(!in_array($id_mo_receipt, $finished_data_id_mo_receipt)){
                    $finished_data_id_mo_receipt[]=$id_mo_receipt;
                    $query_mo_receipt = MarketingOrderReceipt::find($id_mo_receipt);

                    if($query_mo_receipt->marketingOrderHandoverReceiptDetail->exists()){
                        foreach($query_mo_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_rd){
                            $properties =[
                                ['name'=> "Tanggal :".$row_mo_h_rd->marketingOrderHandoverReceipt->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_mo_h_rd->marketingOrderHandoverReceipt->grandtotal,2,',','.')]
                                ;
                            }
                            $mohr=[
                                "name"=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                "key" =>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_handover_receipt?code=".CustomHelper::encrypt($row_mo_h_rd->marketingOrderHandoverReceipt->code),
                            ];
                            $data_go_chart[]=$mohr;
                            $data_link[]=[
                                'from'=>$query_mo_receipt->code,
                                'to'=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                'string_link'=>$query_mo_receipt->code.$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                            ];
                            
                            if(!in_array($row_mo_h_rd->marketingOrderHandoverReceipt->id, $data_id_hand_over_receipt)){
                                $data_id_hand_over_receipt[] =$row_mo_h_rd->marketingOrderHandoverReceipt->id;
                                $added = true;
                            }
                        }
                    }
                    
                    foreach($query_mo_receipt->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                        if($row_mo_receipt_detail->marketingOrderInvoice()){
                            $properties =[
                                ['name'=> "Tanggal :".$row_mo_receipt_detail->lookable->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_invoice_tempura = [
                                "name"=>$row_mo_receipt_detail->lookable->code,
                                "key" => $row_mo_receipt_detail->lookable->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_receipt_detail->lookable->code),
                            ];
                            $data_go_chart[]=$mo_invoice_tempura;
                            $data_link[]=[
                                'from'=>$query_mo_receipt->code,
                                'to'=>$row_mo_receipt_detail->lookable->code,
                                'string_link'=>$query_mo_receipt->code.$row_mo_receipt_detail->lookable->code,
                            ];
                            if(!in_array($row_mo_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                $data_id_mo_invoice[] = $row_mo_receipt_detail->lookable->id;
                                $added = true;
                            }
                        }
                    }
                }

            }

            foreach($data_id_mo_delivery_process as $id_mo_delivery_process){
                if(!in_array($id_mo_delivery_process, $finished_data_id_mo_delivery_process)){
                    $finished_data_id_mo_delivery_process[]=$id_mo_delivery_process;
                    $query_mo_delivery_process = MarketingOrderDeliveryProcess::find($id_mo_delivery_process);

                    if($query_mo_delivery_process->purchaseOrderDetail()->exists()){
                        foreach($query_mo_delivery_process->purchaseOrderDetail as $row_po_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_po_detail->purchaseOrder->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_po_detail->purchaseOrder->grandtotal,2,',','.')]
                                ;
                            }
                            $po_tempura=[
                                "name"=>$row_po_detail->purchaseOrder->code,
                                "key" =>$row_po_detail->purchaseOrder->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po_detail->purchaseOrder->code),
                            ];
                            $data_go_chart[]=$po_tempura;
                            $data_link[]=[
                                'from'=>$query_mo_delivery_process->code,
                                'to'=>$row_po_detail->purchaseOrder->code,
                                'string_link'=>$query_mo_delivery_process->code.$row_po_detail->purchaseOrder->code,
                            ];
                            
                            if(!in_array($row_po_detail->purchaseOrder->id, $data_id_po)){
                                $data_id_po[] =$row_po_detail->purchaseOrder->id;
                                $added = true;
                            }
                        }
                    
                    }

                    if($query_mo_delivery_process->marketingOrderInvoice()->exists()){
                        $properties = [
                            ['name'=> "Tanggal :".$query_mo_delivery_process->marketingOrderInvoice->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal : Rp.:".number_format($query_mo_delivery_process->marketingOrderInvoice->grandtotal,2,',','.')]
                            ;
                        }
                        $po_tempura=[
                            "name"=>$query_mo_delivery_process->marketingOrderInvoice->code,
                            "key" =>$query_mo_delivery_process->marketingOrderInvoice->code,
                            'properties'=>$properties,
                            'url'=>request()->root()."admin/purchase/purchase_order?code=".CustomHelper::encrypt($query_mo_delivery_process->marketingOrderInvoice->code),
                        ];
                        $data_go_chart[]=$po_tempura;
                        $data_link[]=[
                            'from'=>$query_mo_delivery_process->code,
                            'to'=>$query_mo_delivery_process->marketingOrderInvoice->code,
                            'string_link'=>$query_mo_delivery_process->code.$query_mo_delivery_process->marketingOrderInvoice->code,
                        ];
                        
                        if(!in_array($query_mo_delivery_process->marketingOrderInvoice->id, $data_id_mo_invoice)){
                            $data_id_mo_invoice[] =$query_mo_delivery_process->marketingOrderInvoice->id;
                            $added = true;
                        } 
                    }
                }
            }

            //marketing handover receipt
            foreach($data_id_hand_over_receipt as $row_handover_id){
                if(!in_array($row_handover_id, $finished_data_id_handover)){
                    $finished_data_id_handover[]=$row_handover_id;
                    $query_handover_receipt = MarketingOrderHandoverReceipt::find($row_handover_id);
                    foreach($query_handover_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_receipt_detail){
                        if($row_mo_h_receipt_detail->marketingOrderInvoice()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_invoice_tempura=[
                                "name"=>$row_mo_h_receipt_detail->lookable->code,
                                "key" => $row_mo_h_receipt_detail->lookable->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                            ];
                            $data_go_chart[]=$mo_invoice_tempura;
                            $data_link[]=[
                                'from'=>$query_handover_receipt->code,
                                'to'=>$row_mo_h_receipt_detail->lookable->code,
                                'string_link'=>$query_handover_receipt->code.$row_mo_h_receipt_detail->lookable->code,
                            ];
                            if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                $added = true;
                            }
                        }
                    }
                }
            }
            //marketing handover invoice
            foreach($data_id_hand_over_invoice as $row_handover_invoice_id){
                if(!in_array($row_handover_invoice_id, $finished_data_id_handover_invoice)){
                    $finished_data_id_handover_invoice[]=$row_handover_invoice_id;
                    $query_handover_invoice = MarketingOrderHandoverInvoice::find($row_handover_invoice_id);
                    foreach($query_handover_invoice->marketingOrderHandoverInvoiceDetail as $row_mo_h_invoice_detail){
                        if($row_mo_h_invoice_detail->marketingOrderInvoice->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_invoice_tempura=[
                                "name"=>$row_mo_h_receipt_detail->lookable->code,
                                "key" => $row_mo_h_receipt_detail->lookable->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                            ];
                            $data_go_chart[]=$mo_invoice_tempura;
                            $data_link[]=[
                                'from'=>$query_handover_invoice->code,
                                'to'=>$row_mo_h_receipt_detail->lookable->code,
                                'string_link'=>$query_handover_invoice->code.$row_mo_h_receipt_detail->lookable->code,
                            ];
                            if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                $added = true;
                            }
                        }
                    }
                }
            }

            // menacari anakan invoice
            foreach($data_id_mo_invoice as $row_id_invoice){
                if(!in_array($row_id_invoice, $finished_data_id_invoice)){
                    $finished_data_id_invoice[]=$row_id_invoice;
                    $query_invoice = MarketingOrderInvoice::find($row_id_invoice);
                    if($query_invoice->incomingPaymentDetail()->exists()){
                        foreach($query_invoice->incomingPaymentDetail as $row_ip_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_ip_detail->incomingPayment->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_ip_detail->incomingPayment->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_incoming_payment=[
                                "name"=>$row_ip_detail->incomingPayment->code,
                                "key" => $row_ip_detail->incomingPayment->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_ip_detail->incomingPayment->code),
                            ];
                            $data_go_chart[]=$mo_incoming_payment;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_ip_detail->incomingPayment->code,
                                'string_link'=>$query_invoice->code.$row_ip_detail->incomingPayment->code,
                            ];
                            if(!in_array($row_ip_detail->incomingPayment->id, $data_incoming_payment)){
                                $data_incoming_payment[] = $row_ip_detail->incomingPayment->id;
                                $added = true;
                            }
                        }
                    }

                    if($query_invoice->marketingOrderDeliveryProcess()->exists()){
                        
                        $properties =[
                            ['name'=> "Tanggal :".$query_invoice->marketingOrderDeliveryProcess->post_date],
                            
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] = ['name'=> "Nominal : Rp.:".number_format($query_invoice->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                            ;
                        }
                        $mo_delivery=[
                            "name"=> $query_invoice->marketingOrderDeliveryProcess->code,
                            "key" => $query_invoice->marketingOrderDeliveryProcess->code,
                            'properties'=> $properties,
                            'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($query_invoice->marketingOrderDeliveryProcess->code),
                        ];
                        $data_go_chart[]=$mo_delivery;
                        $data_link[]=[
                            'from'=>$query_invoice->marketingOrderDeliveryProcess->code,
                            'to'=>$query_invoice->code,
                            'string_link'=>$query_invoice->marketingOrderDeliveryProcess->code.$query_invoice->code,
                        ];
                        $data_id_mo_delivery_process[]=$query_invoice->marketingOrderDeliveryProcess->id;
                    
                        
                    }

                    // if($query_invoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                    //     foreach($query_invoice->marketingOrderInvoiceDeliveryProcess as $row_delivery_detail){
                    //         $properties =[
                    //             ['name'=> "Tanggal :".$row_delivery_detail->lookable->marketingOrderDelivery->post_date],
                               
                    //         ];
                            
                    //         if (!$hide_nominal) {
                    //             $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_delivery_detail->lookable->marketingOrderDelivery->grandtotal,2,',','.')]
                    //             ;
                    //         }
                    //         $mo_delivery=[
                    //             "name"=> $row_delivery_detail->lookable->marketingOrderDelivery->code,
                    //             "key" => $row_delivery_detail->lookable->marketingOrderDelivery->code,
                    //             'properties'=> $properties,
                    //             'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_delivery_detail->lookable->marketingOrderDelivery->code),
                    //         ];
                    //         $data_go_chart[]=$mo_delivery;
                    //         $data_link[]=[
                    //             'from'=>$row_delivery_detail->lookable->marketingOrderDelivery->code,
                    //             'to'=>$query_invoice->code,
                    //             'string_link'=>$row_delivery_detail->lookable->marketingOrderDelivery->code.$query_invoice->code,
                    //         ];
                    //         $data_id_mo_delivery[]=$row_delivery_detail->lookable->marketingOrderDelivery->id;
                    //     }    
                        
                    // }
                    if($query_invoice->marketingOrderInvoiceDownPayment()->exists()){
                        foreach($query_invoice->marketingOrderInvoiceDownPayment as $row_dp){
                            $properties = [
                                ['name'=> "Tanggal :".$row_dp->lookable->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_dp->lookable->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_downpayment=[
                                "name"=>$row_dp->lookable->code,
                                "key" =>$row_dp->lookable->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_dp->lookable->code),
                            ];
                            $data_go_chart[]=$mo_downpayment;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_dp->lookable->code,
                                'string_link'=>$query_invoice->code.$row_dp->lookable->code,
                            ];
                            
                            if(!in_array($row_dp->lookable->id, $data_id_mo_dp)){
                                $data_id_mo_dp[] =$row_dp->lookable->id;
                                $added = true;
                            }
                        }
                        
                    }
                    if($query_invoice->marketingOrderHandoverInvoiceDetail()->exists()){
                        foreach($query_invoice->marketingOrderHandoverInvoiceDetail as $row_handover_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$row_handover_detail->marketingOrderHandoverInvoice->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =  ['name'=> "Nominal : Rp.:".number_format($row_handover_detail->marketingOrderHandoverInvoice->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_handover_tempura=[
                                "name"=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                "key" =>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_handover_invoice?code=".CustomHelper::encrypt($row_handover_detail->marketingOrderHandoverInvoice->code),
                            ];
                            $data_go_chart[]=$mo_handover_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                'string_link'=>$query_invoice->code.$row_handover_detail->marketingOrderHandoverInvoice->code,
                            ];
                            
                            if(!in_array($row_handover_detail->marketingOrderHandoverInvoice->id, $data_id_hand_over_invoice)){
                                $data_id_hand_over_invoice[] =$row_handover_detail->marketingOrderHandoverInvoice->id;
                                $added = true;
                            }
                        }
                    }
                    if($query_invoice->marketingOrderReceiptDetail()->exists()){
                        foreach($query_invoice->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                            $properties =[
                                ['name'=> "Tanggal :".$row_mo_receipt_detail->marketingOrderReceipt->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->marketingOrderReceipt->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_receipt_tempura=[
                                "name"=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                "key" =>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_receipt?code=".CustomHelper::encrypt($row_mo_receipt_detail->marketingOrderReceipt->code),
                            ];
                            $data_go_chart[]=$mo_receipt_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                'string_link'=>$query_invoice->code.$row_mo_receipt_detail->marketingOrderReceipt->code,
                            ];
                            
                            if(!in_array($row_mo_receipt_detail->marketingOrderReceipt->id, $data_id_mo_receipt)){
                                $data_id_mo_receipt[] =$row_mo_receipt_detail->marketingOrderReceipt->id;
                                $added = true;
                            }
                        }
                    }
                    foreach($query_invoice->marketingOrderInvoiceDetail as $row_invoice_detail){
                        if($row_invoice_detail->marketingOrderMemoDetail()->exists()){
                            foreach($row_invoice_detail->marketingOrderMemoDetail as $row_memo){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_memo->marketingOrderMemo->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal : Rp.:".number_format($row_memo->marketingOrderMemo->grandtotal,2,',','.')]
                                    ;
                                }
                                $mo_memo=[
                                    "name"=>$row_memo->marketingOrderMemo->code,
                                    "key" => $row_memo->marketingOrderMemo->code,
                                    'properties'=> $properties,
                                    'url'=>request()->root()."/admin/sales/marketing_order_memo?code=".CustomHelper::encrypt($row_memo->marketingOrderMemo->code),
                                ];
                                $data_go_chart[]=$mo_memo;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_memo->marketingOrderMemo->code,
                                    'string_link'=>$query_invoice->code.$row_memo->marketingOrderMemo->code,
                                ];
                                $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                // if(!in_array($row_memo->marketingOrderMemo->id, $data_id_mo_memo)){
                                //     $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                //     $added = true;
                                // }
                            }
                        }
                        
                    }
                }

            }

            foreach($data_id_mo_memo as $row_id_memo){
                if(!in_array($row_id_memo, $finished_data_id_memo)){
                    $finished_data_id_memo[]=$row_id_memo;
                    $query_mo_memo = MarketingOrderMemo::find($row_id_memo);
                    if($query_mo_memo->incomingPaymentDetail()->exists()){
                        foreach($query_mo_memo->incomingPaymentDetail as $ip_detail){
                            $properties = [
                                ['name'=> "Tanggal :".$ip_detail->incomingPayment->post_date],
                                ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($ip_detail->incomingPayment->grandtotal,2,',','.')]
                                ;
                            }
                            $ip_tempura = [
                                "name"=>$ip_detail->incomingPayment->code,
                                "key" => $ip_detail->incomingPayment->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($ip_detail->incomingPayment->code),
                            ];
                            
                            $data_go_chart[]=$ip_tempura;
                            $data_link[]=[
                                'from'=>$query_mo_memo->code,
                                'to'=>$ip_detail->incomingPayment->code,
                                'string_link'=>$query_mo_memo->code.$ip_detail->incomingPayment->code,
                            ];
                            if(!in_array($ip_detail->incomingPayment->id, $data_incoming_payment)){
                                $data_incoming_payment[]=$ip_detail->incomingPayment->id;
                                $added = true;
                            }  
                        }    
                    }
                    foreach($query_mo_memo->marketingOrderMemoDetail as $row_mo_memo_detail){
                            if($row_mo_memo_detail->marketingOrderDownPayment()){
                                $properties = [
                                    ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $mo_downpayment=[
                                    "name"=>$row_mo_memo_detail->lookable->code,
                                    "key" => $row_mo_memo_detail->lookable->code,
                                    'properties'=>$properties,
                                    'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_downpayment;
                                $data_link[]=[
                                    'from'=>$row_mo_memo_detail->lookable->code,
                                    'to'=>$query_mo_memo->code,
                                    'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                ];
                                $data_id_mo_dp[] = $row_mo_memo_detail->lookable->id;
                                
                                
                            }
                            if($row_mo_memo_detail->marketingOrderInvoiceDetail()){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                    ;
                                }
                                $mo_invoice_tempura=[
                                    "name"=>$row_mo_memo_detail->lookable->code,
                                    "key" => $row_mo_memo_detail->lookable->code,
                                    'properties'=> $properties,
                                    'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$row_mo_memo_detail->lookable->code,
                                    'to'=>$query_mo_memo->code,
                                    'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                ];
                                $data_id_mo_invoice[] = $row_mo_memo_detail->lookable->id;
                                
                            }
                    }
                }

            }
           
            foreach($data_id_mo_return as $row_id_mo_return){
                if(!in_array($row_id_mo_return, $finished_data_id_mo_return)){
                    $finished_data_id_mo_return[]=$row_id_mo_return;
                    $query_mo_return = MarketingOrderReturn::find($row_id_mo_return);
                    foreach($query_mo_return->marketingOrderReturnDetail as $row_mo_return_detail){
                        if($row_id_mo_return->marketingOrderDeliveryDetail()->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->grandtotal,2,',','.')]
                                ;
                            }
                            $data_mo_delivery_tempura = [
                                "name"=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                "key" => $row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_delivery/?code=".CustomHelper::encrypt($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code),
                            ];
                            $data_go_chart[]=$data_mo_delivery_tempura;
                            $data_link[]=[
                                'from'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                'to'=>$query_mo_return->code,
                                'string_link'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code.$query_mo_return->code,
                            ];
                            if(!in_array($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id, $data_id_mo_delivery)){
                                $data_id_mo_delivery[]=$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id;
                                $added = true;
                            }
                        }
                        
                        
                        
                    }
                }
            }
            // mencari delivery anakan
            foreach($data_id_mo_delivery as $row_id_mo_delivery){
                if(!in_array($row_id_mo_delivery, $finished_data_id_mo_delivery)){
                    $finished_data_id_mo_delivery[]=$row_id_mo_delivery;
                    $query_mo_delivery = MarketingOrderDelivery::find($row_id_mo_delivery);
                    if($query_mo_delivery->marketingOrderDeliveryProcess()->exists()){
                        $properties = [
                            ['name'=> "Tanggal :".$query_mo_delivery->marketingOrderDeliveryProcess->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                            ;
                        }
                        $data_mo_delivery_process = [
                            "name"=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                            "key" => $query_mo_delivery->marketingOrderDeliveryProcess->code,
                            'properties'=>$properties,
                            'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrderDeliveryProcess->code),
                        ];
                        
                        $data_go_chart[]=$data_mo_delivery_process;
                        $data_link[]=[
                            'from'=>$query_mo_delivery->code,
                            'to'=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                            'string_link'=>$query_mo_delivery->code.$query_mo_delivery->marketingOrderDeliveryProcess->code,
                        ];
                        if(!in_array($query_mo_delivery->marketingOrderDeliveryProcess->id, $data_id_mo_delivery_process)){
                            $data_id_mo_delivery_process[]=$query_mo_delivery->marketingOrderDeliveryProcess->id;
                            $added = true;
                        }
                        
                        
                    }//mencari process dari delivery
                    foreach($query_mo_delivery->marketingOrderDeliveryDetail as $row_delivery_detail){
                        // if($row_delivery_detail->marketingOrderInvoiceDetail()->exists()){
                        //     $arr = [];
                        //     foreach($row_delivery_detail->marketingOrderInvoiceDetail as $row_invoice_detail){
                        //         if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                        //             foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                        //                 $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                        //             }
                        //         }
                                
                        //         $newArray = array_unique($arr);
                        //         $string = implode(', ', $newArray);
                        //         $properties =  [
                        //             ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                        //             ['name'=> "No Surat Jalan  :".$string.""]
                        //         ];
                                
                        //         if (!$hide_nominal) {
                        //             $properties[] =['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')]
                        //             ;
                        //         }
                        //         $data_invoice = [
                        //             "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                        //             "key" => $row_invoice_detail->marketingOrderInvoice->code,
                        //             'properties'=>$properties,
                        //             'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                        //         ];
                                
                        //         $data_go_chart[]=$data_invoice;
                        //         $data_link[]=[
                        //             'from'=>$query_mo_delivery->code,
                        //             'to'=>$row_invoice_detail->marketingOrderInvoice->code,
                        //             'string_link'=>$query_mo_delivery->code.$row_invoice_detail->marketingOrderInvoice->code,
                        //         ];
                                
                        //         if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                        //             $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                        //             $added = true;
                        //         }
                        //     }
                        // }//mencari marketing order invoice

                        // if($row_delivery_detail->marketingOrderReturnDetail()->exists()){
                        //     foreach($row_delivery_detail->marketingOrderReturnDetail as $row_return_detail){
                        //         $properties =[
                        //             ['name'=> "Tanggal :".$row_return_detail->marketingOrderReturn->post_date],
                        //             ];
                                
                        //         if (!$hide_nominal) {
                        //             $properties[] =['name'=> "Nominal : Rp.:".number_format($row_return_detail->marketingOrderReturn->grandtotal,2,',','.')]
                        //             ;
                        //         }
                        //         $data_return = [
                        //             "name"=>$row_return_detail->marketingOrderReturn->code,
                        //             "key" => $row_return_detail->marketingOrderReturn->code,
                                    
                        //             'properties'=> $properties,
                        //             'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_return_detail->marketingOrderReturn->code),
                        //         ];
                                
                        //         $data_go_chart[]=$data_return;
                        //         $data_link[]=[
                        //             'from'=>$query_mo_delivery->code,
                        //             'to'=>$row_return_detail->marketingOrderReturn->code,
                        //             'string_link'=>$query_mo_delivery->code.$row_return_detail->marketingOrderReturn->code,
                        //         ];
                                
                        //         $data_id_mo_return[]=$row_return_detail->marketingOrderReturn->id;
                        //     }
                        // }//mencari marketing order return

                        if($row_delivery_detail->marketingOrderDetail()->exists()){
                            $properties =[
                                ['name'=> "Tanggal :".$row_delivery_detail->marketingOrderDetail->marketingOrder->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_delivery_detail->marketingOrderDetail->marketingOrder->grandtotal,2,',','.')]
                                ;
                            }
                            $data_marketing_order = [
                                "name"=> $row_delivery_detail->marketingOrderDetail->marketingOrder->code,
                                "key" => $row_delivery_detail->marketingOrderDetail->marketingOrder->code,
                                'properties'=> $properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_delivery?code=".CustomHelper::encrypt($row_delivery_detail->marketingOrderDetail->marketingOrder->code),           
                            ];
                            $data_go_chart[]= $data_marketing_order;
                            $data_id_mo[]=$row_delivery_detail->marketingOrderDetail->marketingOrder->id;
                        }
                    }
                    if($query_mo_delivery->goodScaleDetail()->exists()){
                        $properties = [
                            ['name'=> "Tanggal :".$query_mo_delivery->goodScaleDetail->goodScale->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->goodScaleDetail->goodScale->grandtotal,2,',','.')]
                            ;
                        }
                        $data_mo_delivery_process = [
                            "name"=>$query_mo_delivery->goodScaleDetail->goodScale->code,
                            "key" => $query_mo_delivery->goodScaleDetail->goodScale->code,
                            'properties'=>$properties,
                            'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($query_mo_delivery->goodScaleDetail->goodScale->code),
                        ];
                        
                        $data_go_chart[]=$data_mo_delivery_process;
                        $data_link[]=[
                            'from'=>$query_mo_delivery->code,
                            'to'=>$query_mo_delivery->goodScaleDetail->goodScale->code,
                            'string_link'=>$query_mo_delivery->code.$query_mo_delivery->goodScaleDetail->goodScale->code,
                        ];
                        if(!in_array($query_mo_delivery->goodScaleDetail->goodScale->id, $data_id_good_scale)){
                            $data_id_good_scale[]=$query_mo_delivery->goodScaleDetail->goodScale->id;
                            $added = true;
                        }
                    }
                    // if($query_mo_delivery->marketingOrder()->exists()){
                    //     $properties =[
                    //         ['name'=> "Tanggal :".$query_mo_delivery->marketingOrder->post_date],
                    //     ];
                        
                    //     if (!$hide_nominal) {
                    //         $properties[] =['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrder->grandtotal,2,',','.')]
                    //         ;
                    //     }
                    //     $data_marketing_order = [
                    //         "name"=> $query_mo_delivery->marketingOrder->code,
                    //         "key" => $query_mo_delivery->marketingOrder->code,
                    //         'properties'=> $properties,
                    //         'url'=>request()->root()."/admin/sales/marketing_order_delivery?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrder->code),           
                    //     ];
            
                    //     $data_go_chart[]= $data_marketing_order;
                    //     $data_id_mo[]=$query_mo_delivery->marketingOrder->id;
                    // }
                }
            }
            
            foreach($data_id_production_handover as $row_id_handover){
                if(!in_array($row_id_handover, $finished_data_id_production_handover)){
                    $finished_data_id_production_handover[]=$row_id_handover;
                    $query_production_handover = ProductionHandover::find($row_id_handover);
                  
                    
                    if($query_production_handover->productionFgReceive()->exists()){
                        $production_fg_receive = [
                            "name"=>$query_production_handover->productionFgReceive->code,
                            "key" => $query_production_handover->productionFgReceive->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_handover->productionFgReceive->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_handover?code=".CustomHelper::encrypt($query_production_handover->productionFgReceive->code),  
                        ];
                        $data_go_chart[]=$production_fg_receive;
                        $data_link[]=[
                            'from'=>$query_production_handover->productionFgReceive->code,
                            'to'=>$query_production_handover->code,
                            'string_link'=>$query_production_handover->productionFgReceive->code.$query_production_handover->code
                        ]; 

                        if(!in_array($query_production_handover->productionFgReceive->id, $data_id_production_fg_receive)){
                            $data_id_production_fg_receive[] = $query_production_handover->productionFgReceive->id; 
                            $added = true;
                        } 
                    }
                }
            }

            // foreach($data_id_production_issue_receive as $row_id_production_issue_receive){
            //     if(!in_array($row_id_production_issue_receive, $finished_data_id_production_issue_receive)){
            //         $finished_data_id_production_issue_receive[]=$row_id_production_issue_receive;
            //         $query_production_issue = ProductionIssueReceive::find($row_id_production_issue_receive);
            //         if($query_production_issue->productionOrder()->exists()){
            //             $production_order_tempura = [
            //                 "name"=>$query_production_issue->productionOrder->code,
            //                 "key" => $query_production_issue->productionOrder->code,
            //                 'properties'=> [
            //                     ['name'=> "Tanggal :".$query_production_issue->productionOrder->post_date],
                                
            //                 ],
            //                 'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($query_production_issue->productionOrder->code),  
            //             ];
            //             $data_go_chart[]=$production_order_tempura;
            //             $data_link[]=[
            //                 'from'=>$query_production_issue->productionOrder->code,
            //                 'to'=>$query_production_issue->code,
            //                 'string_link'=>$query_production_issue->productionOrder->code.$query_production_issue->code
            //             ]; 

            //             if(!in_array($query_production_issue->productionOrder->id, $data_id_production_order)){
            //                 $data_id_production_order[] = $query_production_issue->productionOrder->id; 
            //                 $added = true;
            //             } 
            //         }
            //     }
            // }

            foreach($data_id_production_fg_receive as $row_id_production_fg_receive){
                if(!in_array($row_id_production_fg_receive, $finished_data_id_production_fg_receive)){
                    $finished_data_id_production_fg_receive[]=$row_id_production_fg_receive;
                    $query_production_fg_receive = ProductionFgReceive::find($row_id_production_fg_receive);
                    if($query_production_fg_receive->productionOrderDetail()->exists()){
                        $production_order_tempura = [
                            "name"=>$query_production_fg_receive->productionOrderDetail->productionOrder->code,
                            "key" => $query_production_fg_receive->productionOrderDetail->productionOrder->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_fg_receive->productionOrderDetail->productionOrder->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($query_production_fg_receive->productionOrderDetail->productionOrder->code),  
                        ];
                        $data_go_chart[]=$production_order_tempura;
                        $data_link[]=[
                            'from'=>$query_production_fg_receive->productionOrderDetail->productionOrder->code,
                            'to'=>$query_production_fg_receive->code,
                            'string_link'=>$query_production_fg_receive->productionOrderDetail->productionOrder->code.$query_production_fg_receive->code
                        ]; 

                        if(!in_array($query_production_fg_receive->productionOrderDetail->productionOrder->id, $data_id_production_order)){
                            $data_id_production_order[] = $query_production_fg_receive->productionOrderDetail->productionOrder->id; 
                            $added = true;
                        } 
                    }
                    // if($query_production_fg_receive->productionIssue()->exists()){
                    //     foreach($query_production_fg_receive->productionIssue as $row_production_issue){
                    //         $production_issue_tempura = [
                    //             "name"=>$row_production_issue->code,
                    //             "key" => $row_production_issue->code,
                    //             'properties'=> [
                    //                 ['name'=> "Tanggal :".$row_production_issue->post_date],
                                    
                    //             ],
                    //             'url'=>request()->root()."/admin/production/production_issue?code=".CustomHelper::encrypt($row_production_issue->code),  
                    //         ];
                    //         $data_go_chart[]=$production_issue_tempura;
                    //         $data_link[]=[
                    //             'from'=>$row_production_issue->code,
                    //             'to'=>$query_production_fg_receive->code,
                    //             'string_link'=>$row_production_issue->code.$query_production_fg_receive->code
                    //         ]; 
    
                    //         if(!in_array($row_production_issue->id, $data_id_production_issue)){
                    //             $data_id_production_issue[] = $row_production_issue->id; 
                    //             $added = true;
                    //         }
                    //     }
                         
                    // }
                    if($query_production_fg_receive->productionHandover()->exists()){
                        foreach($query_production_fg_receive->productionHandover as $row_production_handover){
                            $production_fgr_tempura = [
                                "name"=>$row_production_handover->code,
                                "key" => $row_production_handover->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_production_handover->post_date],
                                    
                                ],
                                'url'=>request()->root()."/admin/production/production_handover?code=".CustomHelper::encrypt($row_production_handover->code),  
                            ];
                            $data_go_chart[]=$production_fgr_tempura;
                            $data_link[]=[
                                'from'=>$query_production_fg_receive->code,
                                'to'=>$row_production_handover->code,
                                'string_link'=>$query_production_fg_receive->code.$row_production_handover->code
                            ]; 
    
                            if(!in_array($row_production_handover->id, $data_id_production_handover)){
                                $data_id_production_handover[] = $row_production_handover->id; 
                                $added = true;
                             
                            } 
                        }
                    }
                }
            }

            foreach($data_id_production_issue as $row_id_production_issue){
                if(!in_array($row_id_production_issue, $finished_data_id_production_issue)){
                    $finished_data_id_production_issue[]=$row_id_production_issue;
                    $query_production_issue = ProductionIssue::find($row_id_production_issue);
                    if($query_production_issue->productionOrderDetail()->exists()){
                        $production_order_tempura = [
                            "name"=>$query_production_issue->productionOrderDetail->productionOrder->code,
                            "key" => $query_production_issue->productionOrderDetail->productionOrder->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_issue->productionOrderDetail->productionOrder->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($query_production_issue->productionOrderDetail->productionOrder->code),  
                        ];
                        $data_go_chart[]=$production_order_tempura;
                        $data_link[]=[
                            'from'=>$query_production_issue->productionOrderDetail->productionOrder->code,
                            'to'=>$query_production_issue->code,
                            'string_link'=>$query_production_issue->productionOrderDetail->productionOrder->code.$query_production_issue->code
                        ]; 

                        if(!in_array($query_production_issue->productionOrderDetail->productionOrder->id, $data_id_production_order)){
                            $data_id_production_order[] = $query_production_issue->productionOrderDetail->productionOrder->id; 
                            $added = true;
                        } 
                    }
                   /*  if($query_production_issue->productionFgReceive()->exists()){
                        $production_fgr_tempura = [
                            "name"=>$query_production_issue->productionFgReceive->code,
                            "key" => $query_production_issue->productionFgReceive->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_issue->productionFgReceive->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_fg_receive?code=".CustomHelper::encrypt($query_production_issue->productionFgReceive->code),  
                        ];
                        $data_go_chart[]=$production_fgr_tempura;
                        $data_link[]=[
                            'from'=>$query_production_issue->productionFgReceive->code,
                            'to'=>$query_production_issue->code,
                            'string_link'=>$query_production_issue->productionFgReceive->code.$query_production_issue->code
                        ]; 

                        if(!in_array($query_production_issue->productionFgReceive->id, $data_id_production_fg_receive)){
                            $data_id_production_fg_receive[] = $query_production_issue->productionFgReceive->id; 
                            $added = true;
                        } 
                    }
                    if($query_production_issue->productionReceive()->exists()){
                        $production_receive_tempura = [
                            "name"=>$query_production_issue->productionReceive->code,
                            "key" => $query_production_issue->productionReceive->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_issue->productionReceive->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_receive?code=".CustomHelper::encrypt($query_production_issue->productionReceive->code),  
                        ];
                        $data_go_chart[]=$production_receive_tempura;
                        $data_link[]=[
                            'from'=>$query_production_issue->productionReceive->code,
                            'to'=>$query_production_issue->code,
                            'string_link'=>$query_production_issue->productionReceive->code.$query_production_issue->code
                        ]; 

                        if(!in_array($query_production_issue->productionReceive->id, $data_id_production_receive)){
                            $data_id_production_receive[] = $query_production_issue->productionReceive->id; 
                            $added = true;
                        } 
                    } */
                }
            }

            foreach($data_id_production_receive as $row_id_production_receive){
                if(!in_array($row_id_production_receive, $finished_data_id_production_receive)){
                    $finished_data_id_production_receive[]=$row_id_production_receive;
                    $query_production_receive = ProductionReceive::find($row_id_production_receive);
                    if($query_production_receive->productionOrderDetail()->exists()){
                        $production_order_tempura = [
                            "name"=>$query_production_receive->productionOrderDetail->productionOrder->code,
                            "key" => $query_production_receive->productionOrderDetail->productionOrder->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_production_receive->productionOrderDetail->productionOrder->post_date],
                                
                            ],
                            'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($query_production_receive->productionOrderDetail->productionOrder->code),  
                        ];
                        $data_go_chart[]=$production_order_tempura;
                        $data_link[]=[
                            'from'=>$query_production_receive->productionOrderDetail->productionOrder->code,
                            'to'=>$query_production_receive->code,
                            'string_link'=>$query_production_receive->productionOrderDetail->productionOrder->code.$query_production_receive->code
                        ]; 

                        if(!in_array($query_production_receive->productionOrderDetail->productionOrder->id, $data_id_production_order)){
                            $data_id_production_order[] = $query_production_receive->productionOrderDetail->productionOrder->id; 
                            $added = true;
                        } 
                    }
                    /* if($query_production_receive->productionIssue()->exists()){
                        foreach($query_production_receive->productionIssue as $row_production_issue){
                            $production_issue_tempura = [
                                "name"=>$row_production_issue->code,
                                "key" => $row_production_issue->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_production_issue->post_date],
                                    
                                ],
                                'url'=>request()->root()."/admin/production/production_issue?code=".CustomHelper::encrypt($row_production_issue->code),  
                            ];
                            $data_go_chart[]=$production_issue_tempura;
                            $data_link[]=[
                                'from'=>$row_production_issue->code,
                                'to'=>$query_production_receive->code,
                                'string_link'=>$row_production_issue->code.$query_production_receive->code
                            ]; 
    
                            if(!in_array($row_production_issue->id, $data_id_production_issue)){
                                $data_id_production_issue[] = $row_production_issue->id; 
                                $added = true;
                            } 
                        }
                        
                    } */
                }
            }


            foreach($data_id_production_order as $row_id_production_order){
                if(!in_array($row_id_production_order, $finished_data_id_production_order)){
                    $finished_data_id_production_order[] = $row_id_production_order;
                    $query_production_order = ProductionOrder::find($row_id_production_order);
                    foreach($query_production_order->productionOrderDetail as $row_production_order){

                        if($row_production_order->productionScheduleDetail()->exists()){
                       
                            $productionschedule  = [
                                "name"=> $row_production_order->productionScheduleDetail->productionschedule->code,
                                "key" =>  $row_production_order->productionScheduleDetail->productionschedule->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :". $row_production_order->productionScheduleDetail->productionschedule->post_date],
                                ],
                                'url'=>request()->root()."/admin/production/production_schedule?code=".CustomHelper::encrypt( $row_production_order->productionScheduleDetail->productionschedule->code),  
                            ];
                            $data_go_chart[]=$productionschedule;
                            $data_link[]=[
                                'from'=>$row_production_order->productionScheduleDetail->productionschedule->code,
                                'to'=> $query_production_order->code,
                                'string_link'=>$row_production_order->productionScheduleDetail->productionschedule->code.$query_production_order->code
                            ]; 
                            
                            if(!in_array( $row_production_order->productionScheduleDetail->productionschedule->id, $data_id_production_schedule)){
                               
                                $data_id_production_schedule[] =  $row_production_order->productionScheduleDetail->productionSchedule->id; 
                                $added = true;
                            } 
                        }
                        if($row_production_order->productionIssue()->exists()){
                            foreach($row_production_order->productionIssue as $row_production_issue){
                                $productionissuerec  = [
                                    "name"=> $row_production_issue->code,
                                    "key" =>  $row_production_issue->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $row_production_issue->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/production/production_issue?code=".CustomHelper::encrypt( $row_production_issue->code),  
                                ];
                                $data_go_chart[]=$productionissuerec;
                                $data_link[]=[
                                    'from'=>$query_production_order->code,
                                    'to'=> $row_production_issue->code,
                                    'string_link'=>$query_production_order->code. $row_production_issue->code
                                ]; 
    
                                if(!in_array( $row_production_issue->id, $data_id_production_issue)){
                                    $data_id_production_issue_receive[] =  $row_production_issue->id; 
                                    $added = true;
                                } 
                            }
                        }
                        if($row_production_order->productionReceive()->exists()){
                            foreach($row_production_order->productionReceive as $row_production_receive){
                                $productionreceive  = [
                                    "name"=> $row_production_receive->code,
                                    "key" =>  $row_production_receive->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $row_production_receive->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/production/production_receive?code=".CustomHelper::encrypt( $row_production_receive->code),  
                                ];
                                $data_go_chart[]=$productionreceive;
                                $data_link[]=[
                                    'from'=>$query_production_order->code,
                                    'to'=> $row_production_receive->code,
                                    'string_link'=>$query_production_order->code. $row_production_receive->code
                                ]; 
    
                                if(!in_array( $row_production_receive->id, $data_id_production_receive)){
                                    $data_id_production_receive[] =  $row_production_receive->id; 
                                    $added = true;
                                } 
                            }
                        }
                        if($row_production_order->productionFgReceive()->exists()){
                            foreach($row_production_order->productionFgReceive as $row_production_fg_receive){
                                $productionfgreceive  = [
                                    "name"=> $row_production_fg_receive->code,
                                    "key" =>  $row_production_fg_receive->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $row_production_fg_receive->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/production/production_fg_receive?code=".CustomHelper::encrypt( $row_production_fg_receive->code),  
                                ];
                                $data_go_chart[]=$productionfgreceive;
                                $data_link[]=[
                                    'from'=>$query_production_order->code,
                                    'to'=> $row_production_fg_receive->code,
                                    'string_link'=>$query_production_order->code. $row_production_fg_receive->code
                                ]; 
    
                                if(!in_array( $row_production_fg_receive->id, $data_id_production_fg_receive)){
                                    $data_id_production_fg_receive[] =  $row_production_fg_receive->id; 
                                    $added = true;
                                } 
                            } 
                        }
                    }
                    
                }
            }

            foreach($data_id_production_schedule as $row_id_production_schedule){
                if(!in_array($row_id_production_schedule, $finished_data_id_production_schedule)){
                    
                    $finished_data_id_production_schedule[]=$row_id_production_schedule;
                    $query_prs = ProductionSchedule::find($row_id_production_schedule);
                    
                    foreach($query_prs->productionScheduleDetail as $row_production_schedule_detail){
                       
                        if($row_production_schedule_detail->productionOrderDetail()->exists()){
                            $properties = [
                                ['name'=> "Tanggal :".$row_production_schedule_detail->productionOrderDetail->productionOrder->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_production_schedule_detail->productionOrderDetail->productionOrder->grandtotal,2,',','.')]
                                ;
                            }
                            $production_order_tempura = [
                                "name"=>$row_production_schedule_detail->productionOrderDetail->productionOrder->code,
                                "key" => $row_production_schedule_detail->productionOrderDetail->productionOrder->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/production/production_order?code=".CustomHelper::encrypt($row_production_schedule_detail->productionOrderDetail->productionOrder->code),  
                            ];
                            $data_go_chart[]=$production_order_tempura;
                            $data_link[]=[
                                'from'=>$query_prs->code,
                                'to'=>$row_production_schedule_detail->productionOrderDetail->productionOrder->code,
                                'string_link'=>$query_prs->code.$row_production_schedule_detail->productionOrderDetail->productionOrder->code
                            ]; 

                            if(!in_array($row_production_schedule_detail->productionOrderDetail->productionOrder->id, $data_id_production_order)){
                                $data_id_production_order[] = $row_production_schedule_detail->productionOrderDetail->productionOrder->id; 
                                $added = true;
                            }  
                        }

                    }
                    foreach($query_prs->productionScheduleTarget as $row_production_target){
                        if($row_production_target->marketingOrderPlanDetail()->exists()){
                            $row_mop_detail=$row_production_target->marketingOrderPlanDetail;
                            $properties = [
                                ['name'=> "Tanggal :".$row_mop_detail->marketingOrderPlan->post_date],
                            ];
                            
                            if (!$hide_nominal) {
                                $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mop_detail->marketingOrderPlan->grandtotal,2,',','.')]
                                ;
                            }
                            $mo_plan_tempura = [
                                "name"=>$row_mop_detail->marketingOrderPlan->code,
                                "key" => $row_mop_detail->marketingOrderPlan->code,
                                'properties'=>$properties,
                                'url'=>request()->root()."/admin/sales/marketing_order_plan?code=".CustomHelper::encrypt($row_mop_detail->marketingOrderPlan->code),  
                            ];
                            $data_go_chart[]=$mo_plan_tempura;
                            $data_link[]=[
                                'from'=>$row_mop_detail->marketingOrderPlan->code,
                                'to'=>$query_prs->code,
                                'string_link'=>$row_mop_detail->marketingOrderPlan->code.$query_prs->code
                            ]; 
    
                            if(!in_array($row_mop_detail->marketingOrderPlan->id, $data_id_mo_plan)){
                                $data_id_mo_plan[] = $row_mop_detail->marketingOrderPlan->id; 
                                $added = true;
                            } 
                        }
                    }
                    
                    
                }
            }

            foreach($data_id_mo_plan as $row_id_mo_plan){
                if(!in_array($row_id_mo_plan, $finished_data_id_mo_plan)){
                    $finished_data_id_mo_plan[] = $row_id_mo_plan;
                    $query_mop = MarketingOrderPlan::find($row_id_mo_plan);

                    foreach($query_mop->marketingOrderPlanDetail as $row_mop_detail){
                        if($row_mop_detail->productionScheduleTarget()->exists()){
                            foreach($row_mop_detail->productionScheduleTarget as $row_pro_sched_target){
                                $productiontargettempura  = [
                                    "name"=>$row_pro_sched_target->productionSchedule->code,
                                    "key" => $row_pro_sched_target->productionSchedule->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pro_sched_target->productionSchedule->post_date],
                                        
                                    ],
                                    'url'=>request()->root()."/admin/production/production_schedule?code=".CustomHelper::encrypt($row_pro_sched_target->productionSchedule->code),  
                                ];
                                $data_go_chart[]=$productiontargettempura;
                                $data_link[]=[
                                    'from'=>$query_mop->code,
                                    'to'=>$row_pro_sched_target->productionSchedule->code,
                                    'string_link'=>$query_mop->code.$row_pro_sched_target->productionSchedule->code
                                ]; 
    
                                if(!in_array($row_pro_sched_target->productionSchedule->id, $data_id_production_schedule)){
                                    $data_id_production_schedule[] = $row_pro_sched_target->productionSchedule->id; 
                                    $added = true;
                                } 
                            }
                           
                        }
                    }
                    if($query_mop->marketingOrder()->exists()){
                        $properties = [
                            ['name'=> "Tanggal :".$query_mop->marketingOrder->post_date],
                        ];
                        
                        if (!$hide_nominal) {
                            $properties[] =['name'=> "Nominal : Rp.:".number_format($query_mop->marketingOrder->grandtotal,2,',','.')]
                            ;
                        }
                        $marketing_order_tempura = [
                            "name"=>$query_mop->marketingOrder->code,
                            "key" => $query_mop->marketingOrder->code,
                            'properties'=> $properties,
                            'url'=>request()->root()."/admin/sales/marketing_order?code=".CustomHelper::encrypt($query_mop->marketingOrder->code),  
                        ];
                        $data_go_chart[]=$marketing_order_tempura;
                        $data_link[]=[
                            'from'=>$query_mop->marketingOrder->code,
                            'to'=>$query_mop->code,
                            'string_link'=>$query_mop->marketingOrder->code.$query_mop->code
                        ]; 

                        if(!in_array($query_mop->marketingOrder->id, $data_id_mo)){
                            $data_id_mo_plan[] = $query_mop->marketingOrder->id; 
                            $added = true;
                        } 
                    }
                    // if($query_mop->marketingOrderDetail()->exists()){
                    //     foreach($query_mop->marketingOrderDetail as $row_marketing_order_detail){
                    //         $marketing_order_tempura = [
                    //             "name"=>$row_marketing_order_detail->marketingOrder->code,
                    //             "key" => $row_marketing_order_detail->marketingOrder->code,
                    //             'properties'=> [
                    //                 ['name'=> "Tanggal :".$row_marketing_order_detail->marketingOrder->post_date],
                    //                 ['name'=> "Nominal : Rp.:".number_format($row_marketing_order_detail->marketingOrder->grandtotal,2,',','.')]
                    //             ],
                    //             'url'=>request()->root()."/admin/sales/marketing_order?code=".CustomHelper::encrypt($row_marketing_order_detail->marketingOrder->code),  
                    //         ];
                    //         $data_go_chart[]=$marketing_order_tempura;
                    //         $data_link[]=[
                    //             'from'=>$row_marketing_order_detail->marketingOrder->code,
                    //             'to'=>$query_mop->code,
                    //             'string_link'=>$row_marketing_order_detail->marketingOrder->code.$query_mo->code
                    //         ]; 
    
                    //         if(!in_array($row_marketing_order_detail->marketingOrder->id, $data_id_mo)){
                    //             $data_id_mo_plan[] = $row_marketing_order_detail->marketingOrder->id; 
                    //             $added = true;
                    //         } 
                    //     }
                    // }
                    

                }
            }

            foreach($data_id_mo as $row_id_mo){
                if(!in_array($row_id_mo, $finished_data_id_mo)){
                    $finished_data_id_mo[]=$row_id_mo;
                    $query_mo= MarketingOrder::find($row_id_mo);

                    // foreach($query_mo->marketingOrderDelivery as $row_mod_del){
                    //     $properties = [
                    //         ['name'=> "Tanggal :".$row_mod_del->post_date],
                    //     ];
                        
                    //     if (!$hide_nominal) {
                    //         $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mod_del->grandtotal,2,',','.')]
                    //         ;
                    //     }
                    //     $modelvery=[
                    //         "name"=>$row_mod_del->code,
                    //         "key" => $row_mod_del->code,
                    //         'properties'=>$properties,
                    //         'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_mod_del->code),  
                    //     ];
    
                    //     $data_go_chart[]=$modelvery;
                    //     $data_link[]=[
                    //         'from'=>$query_mo->code,
                    //         'to'=>$row_mod_del->code,
                    //         'string_link'=>$query_mo->code.$row_mod_del->code
                    //     ]; 

                    //     if(!in_array($row_mod_del->id, $data_id_mo_delivery)){
                    //         $data_id_mo_delivery[] = $row_mod_del->id; 
                    //         $added = true;
                    //     } 
                    // }

                    foreach($query_mo->marketingOrderDetail as $row_marketing_order_detail){
                        if($row_marketing_order_detail->marketingOrderPlanDetail()->exists()){
                            foreach($row_marketing_order_detail->marketingOrderPlanDetail as $row_mop_detail){
                                $properties =[
                                    ['name'=> "Tanggal :".$row_mop_detail->marketingOrderPlan->post_date],
                                ];
                                
                                if (!$hide_nominal) {
                                    $properties[] = ['name'=> "Nominal : Rp.:".number_format($row_mop_detail->marketingOrderPlan->grandtotal,2,',','.')]
                                    ;
                                }
                                $mo_plan_tempura = [
                                    "name"=>$row_mop_detail->marketingOrderPlan->code,
                                    "key" => $row_mop_detail->marketingOrderPlan->code,
                                    'properties'=>$properties,
                                    'url'=>request()->root()."/admin/sales/marketing_order_plan?code=".CustomHelper::encrypt($row_mop_detail->marketingOrderPlan->code),  
                                ];
                                $data_go_chart[]=$mo_plan_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo->code,
                                    'to'=>$row_mop_detail->marketingOrderPlan->code,
                                    'string_link'=>$query_mo->code.$row_mop_detail->marketingOrderPlan->code
                                ]; 

                                if(!in_array($row_mop_detail->marketingOrderPlan->id, $data_id_mo_plan)){
                                    $data_id_mo_plan[] = $row_mop_detail->marketingOrderPlan->id; 
                                    $added = true;
                                } 
                            }
                        }
                        if($row_marketing_order_detail->marketingOrderDeliveryDetail()->exists()){
                            foreach($row_marketing_order_detail->marketingOrderDeliveryDetail as $row_mo_delivery_detail){
                                $properties = [
                                    ['name'=> "Tanggal :".$row_mo_delivery_detail->marketingorderdelivery->post_date],
                                    ];
                                
                                if (!$hide_nominal) {
                                    $properties[] =['name'=> "Nominal : Rp.:".number_format($row_mo_delivery_detail->marketingorderdelivery->grandtotal,2,',','.')]
                                    ;
                                }
                                $modelvery=[
                                    "name"=>$row_mo_delivery_detail->marketingorderdelivery->code,
                                    "key" => $row_mo_delivery_detail->marketingorderdelivery->code,
                                    'properties'=>$properties,
                                    'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_mo_delivery_detail->marketingorderdelivery->code),  
                                ];
            
                                $data_go_chart[]=$modelvery;
                                $data_link[]=[
                                    'from'=>$query_mo->code,
                                    'to'=>$row_mo_delivery_detail->marketingorderdelivery->code,
                                    'string_link'=>$query_mo->code.$row_mo_delivery_detail->marketingorderdelivery->code
                                ]; 
        
                                if(!in_array($row_mo_delivery_detail->marketingorderdelivery->id, $data_id_mo_delivery)){
                                    $data_id_mo_delivery[] = $row_mo_delivery_detail->marketingorderdelivery->id; 
                                    $added = true;
                                } 
                            }
                        }
                    }
                    
                }
            }
        }
        function filterDuplicates(array $array): array {
            $filteredArray = [];
        
            foreach ($array as $item) {
                if ($item['from'] !== $item['to']) {
                    $filteredArray[] = $item;
                }
            }
        
            return $filteredArray;
        }
        $data_link = filterDuplicates($data_link);
       
        return [$data_go_chart, $data_link];
    }

}

