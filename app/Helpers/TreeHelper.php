<?php

namespace App\Helpers;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\GoodIssueRequest;
use App\Models\Line;
use App\Models\LandedCost;
use App\Models\PurchaseRequest;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\CloseBill;
use App\Models\FundRequest;
use App\Models\Coa;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\Retirement;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class TreeHelper {
    public static function treeLoop1($data_go_chart = [] , $data_link = []){
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
        $added = true;
        while($added){
            
            $added=false;
            // Pengambilan foreign branch gr
            foreach($data_id_gr as $gr_id){
                if(!in_array($gr_id, $finished_data_id_gr)){
                    $finished_data_id_gr[]= $gr_id; 
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                ['name'=> "Nominal :".formatNominal($good_receipt_detail->purchaseOrderDetail->purchaseOrder).number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
                            ],
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
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$landed_cost_detail->landedCost->post_date],
                                        ['name'=> "Nominal :".formatNominal($landed_cost_detail->landedCost).number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')]
                                    ],
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
                                $invoice_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal :".formatNominal($invoice_detail->purchaseInvoice).number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        
                                    ],
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

                        if($good_receipt_detail->goodScaleDetail()->exists()){
                            $data_gscale = [
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$good_receipt_detail->goodScaleDetail->goodScale->post_date],
                                        ['name'=> "Vendor  : ".$good_receipt_detail->goodScaleDetail->goodScale->supplier->name],
                                        ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodScaleDetail->goodScale).number_format($good_receipt_detail->goodScaleDetail->goodScale->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'name'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($good_receipt_detail->goodScaleDetail->goodScale->code),
                                ];
                                $data_go_chart[]=$data_gscale;
                                $data_link[]=[
                                    'from'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                    'to'=>$query_gr->code,
                                    'string_link'=>$good_receipt_detail->goodScaleDetail->goodScale->code.$query_gr->code
                                ];
                                $data_id_good_scale[]= $good_receipt_detail->goodScaleDetail->goodScale->id; 
                            
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
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->outgoingPayment->post_date))],
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
                                'url'   =>request()->root()."/admin/finance/personal_close_bill?code=".CustomHelper::encrypt($row_bill_detail->personalCloseBill->code),
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
                    
                    foreach($query_gs->goodScaleDetail as $data_gs){
                        if($data_gs->goodReceiptDetail->exists()){
                            $gr = [
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$data_gs->goodReceiptDetail->goodReceipt->post_date],
                                    ['name'=> "Vendor  : ".$data_gs->goodReceiptDetail->goodReceipt->supplier->name],
                                    
                                ],
                                'key'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'name'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($data_gs->goodReceiptDetail->goodReceipt->code),
                            ];
    
                            $data_go_chart[]=$gr;
                            $data_link[]=[
                                'from'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                'to'=>$query_gs->code,
                                'string_link'=>$data_gs->goodReceiptDetail->goodReceipt->code.$query_gs->code
                            ];
                            if(!in_array($data_gs->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
                                $added = true; 
                            }
                            // $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
    
                        }
                    }
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
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal :".formatNominal($row_po).number_format($row_po->grandtotal,2,',','.')]
                                    ],
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
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                                ],
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
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                                    ['name'=> "Nominal :".formatNominal($row->lookable->landedCost).number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->landedCost->code,
                                "name" => $row->lookable->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
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
                                $data_memo = [
                                    "name"=>$purchase_memodetail->purchaseMemo->code,
                                    "key" => $purchase_memodetail->purchaseMemo->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                        ['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                    ],
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
                            $fr=[
                                "name"=>$row->fundRequestDetail->fundRequest->code,
                                "key" => $row->fundRequestDetail->fundRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                    ['name'=> "User :".$row->fundRequestDetail->fundRequest->account->name],
                                    ['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')],
                                ],
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
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pi->purchaseDownPayment).number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ],
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
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
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
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                                ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
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
                                        $data_downp_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
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
                                        $data_invoices_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
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
                    if($query_invoice->hasPaymentRequestDetail()->exists()){
                        foreach($query_invoice->hasPaymentRequestDetail as $row_pyr_detail){
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
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
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
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
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
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
                                $data_invoices_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
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
                        $outgoing_payment = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                ['name'=> "Nominal :".formatNominal($query_pyr->outgoingPayment).number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ],
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
                        
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        
                        $data_pyr_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                    
                        if($row_pyr_detail->fundRequest()){
                            
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
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
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
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
                            $data_invoices_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
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
                                
                                $data_pyrc_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_cross->lookable).number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                    ],
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
                        $data_pyr_tempura = [
                            'key'   => $query_pyrc->paymentRequest->code,
                            "name"  => $query_pyrc->paymentRequest->code,
                            'properties'=> [
                                ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->post_date))],
                            ],
                            'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
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
                    }
                    if($query_pyrc->outgoingPayment()){
                        $outgoing_tempura = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                                ['name'=> "Nominal :".formatNominal($query_pyrc->lookable).number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                            ],
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
                    }
                }
            }
            
            foreach($data_id_dp as $downpayment_id){
                
                if(!in_array($downpayment_id, $finished_data_id_dp)){
                    $finished_data_id_dp[]=$downpayment_id;
                    
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder()->exists()){
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal :".formatNominal($row->purchaseOrder).number_format($row->purchaseOrder->grandtotal,2,',','.')],
                                ],
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
                            
                                        $data_good_receipt = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                            ],
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
                            $fr=[
                                "name"=>$row->fundRequestDetail->fundRequest->code,
                                "key" => $row->fundRequestDetail->fundRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                    ['name'=> "User :".$row->fundRequestDetail->fundRequest->account->name],
                                    ['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')],
                                ],
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
                        
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                ['name'=> "Nominal :".formatNominal($purchase_invoicedp->purchaseInvoice).number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                ],
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
                        $data_memo=[
                            "name"=>$purchase_memodetail->purchaseMemo->code,
                            "key" => $purchase_memodetail->purchaseMemo->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                ['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                ],
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
                            $data_pyr_tempura=[
                                "name"=>$row_pyr_detail->paymentRequest->code,
                                "key" => $row_pyr_detail->paymentRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')],
                                    ],
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
                            $data_invoices_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                                    ['name'=> "Nominal :".formatNominal($row->lookable->purchaseInvoice).number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                ],
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
                            $data_downp_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal :".formatNominal($row->lookable).number_format($row->lookable->grandtotal,2,',','.')]
                                ],
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
                            $material_request_tempura = [
                                "key" => $data_detail_good_issue->lookable->materialRequest->code,
                                "name" => $data_detail_good_issue->lookable->materialRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$data_detail_good_issue->lookable->materialRequest->post_date],
                                    ['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->materialRequest).number_format($data_detail_good_issue->lookable->materialRequest->grandtotal,2,',','.')],
                                ],
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
                                $po_tempura = [
                                    "key" => $data_purchase_order_detail->purchaseOrder->code,
                                    "name" => $data_purchase_order_detail->purchaseOrder->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Nominal :".formatNominal($data_purchase_order_detail->purchaseOrder).number_format($data_purchase_order_detail->purchaseOrder->grandtotal,2,',','.')],
                                    ],
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
                            $good_issue_request_tempura = [
                                "key" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                "name" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$data_detail_good_issue->lookable->goodIssueRequest->post_date],
                                    ['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->goodIssueRequest).number_format($data_detail_good_issue->lookable->goodIssueRequest->grandtotal,2,',','.')],
                                ],
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
                                'url'=>request()->root()."/admin/purchase/good_receipt?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
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
                            $lc_other = [
                                "key" => $lc_detail->lookable->landedCost->code,
                                "name" => $lc_detail->lookable->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->landedCost->post_date],
                                    ['name'=> "Nominal :".formatNominal($lc_detail->lookable->landedCost).number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')],
                                ],
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
                            $inventory_transfer_out = [
                                "key" => $lc_detail->lookable->inventoryTransferOut->code,
                                "name" => $lc_detail->lookable->inventoryTransferOut->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->inventoryTransferOut->post_date],
                                    ['name'=> "Nominal :".formatNominal($lc_detail->lookable->inventoryTransferOut).number_format($lc_detail->lookable->inventoryTransferOut->grandtotal,2,',','.')],
                                ],
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
                                $data_invoices_tempura = [
                                    'key'   => $row_invoice_detail->purchaseInvoice->code,
                                    "name"  => $row_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                    ],
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
                            $lc_tempura = [
                                "key" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                "name" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_transfer_out_detail->landedCostDetail->landedCost->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_transfer_out_detail->landedCostDetail).number_format($row_transfer_out_detail->landedCostDetail->landedCost->grandtotal,2,',','.')],
                                ],
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
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pcbd->fundRequest->code],
                                    ['name'=> "User :".$row_pcbd->fundRequest->account->name],
                                    ['name'=> "Nominal :".formatNominal($row_pcbd->fundRequest).number_format($row_pcbd->fundRequest->grandtotal,2,',','.')]
                                ],
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
                            $data_cb_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_cbd->closeBill->code],
                                    ['name'=> "Nominal :".formatNominal($row_cbd->closeBill).number_format($row_cbd->closeBill->grandtotal,2,',','.')]
                                ],
                                "key" => $row_cbd->closeBill->code,
                                "name" => $row_cbd->closeBill->code,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_cbd->closeBill->code), 
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
                                $data_pyr_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                    ],
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
                        
                        if($row_fr_detail->purchaseInvoiceDetail()->exists()){
                            foreach($row_fr_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                $data_invoices_tempura = [
                                    'key'   => $row_invoice_detail->purchaseInvoice->code,
                                    "name"  => $row_invoice_detail->purchaseInvoice->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                    ],
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
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
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
                            $data_pcb_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pcbd->personalCloseBill->post_date],
                                    ['name'=> "Nominal :".formatNominal($row_pcbd->personalCloseBill).number_format($row_pcbd->personalCloseBill->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pcbd->personalCloseBill->code,
                                "name" => $row_pcbd->personalCloseBill->code,
                                'url'=>request()->root()."/admin/finance/personal_close_bill?code=".CustomHelper::encrypt($row_pcbd->personalCloseBill->code),
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
                                $po_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".$purchase_order_detail->purchaseOrder->supplier->name],
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
                                    ['name'=> "Vendor  : ".$purchase_request_detail->lookable->materialRequest->user->name],
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
                                        ['name'=> "Vendor  : ".$row_purchase_request_detail->purchaseRequest->user->name],
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
                                        ['name'=> "User  : ".$good_issue_detail->goodIssue->user->name],
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
        }
         
        return [$data_go_chart, $data_link];
    }

}

