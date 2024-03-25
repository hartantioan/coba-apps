<?php

namespace App\Exports;

use App\Models\MaterialRequest;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\CustomHelper;

class ExportPurchaseProgressReport implements FromView,ShouldAutoSize,WithTitle
{
    protected $start_date,$end_date;
    public function __construct(string $start_date,string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';

    }
    public function title(): string
    {
        return 'Progres Pemmbelian'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $data = MaterialRequest::where(function($query) {
            
            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }
        })
        ->get();
        $array_detail=[];
        foreach($data as $row_item_request){
            
            foreach($row_item_request->materialRequestDetail as $row_item_request_detail){
                $max_count=1;
                $array_item_req = [
                    'item'         => $row_item_request_detail->item->name,
                    'item_code'    => $row_item_request_detail->item->code,
                    'ir_code'      => $row_item_request->code,
                    'ir_date'      => $row_item_request->post_date,
                    'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                    'status'    => $row_item_request->statusRaw(),
                ];
                $max_count_pr = 1;
            
                $all_pr=[];
                if($row_item_request_detail->purchaseRequestDetail()->exists()){
                    if($max_count<count($row_item_request_detail->purchaseRequestDetail)){
                        $max_count=count($row_item_request_detail->purchaseRequestDetail);
                    }
                    
                  
                    $total_pr = 0;
                    foreach($row_item_request_detail->purchaseRequestDetail as $row_pr_detail){
                    
                        $total_pr++;
                        $pr=[
                            'pr_code'      => $row_pr_detail->purchaseRequest->code,
                            'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                            'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                            'status'       => $row_pr_detail->purchaseRequest->statusRaw(),
                        ];
                        if($row_pr_detail->purchaseOrderDetail()->exists()){
                            if($max_count<count($row_pr_detail->purchaseOrderDetail)){
                                $max_count=count($row_pr_detail->purchaseOrderDetail);
                            }
                            
                            if ($max_count_pr < count($row_pr_detail->purchaseOrderDetail)) {
                                $max_count_pr = count($row_pr_detail->purchaseOrderDetail);
                            }
                            $total_po = 0 ;
                            $total_grpo = 0;
                            foreach($row_pr_detail->purchaseOrderDetail as $row_po_detail){
                                $po=[
                                    'po_code'      => $row_po_detail->purchaseOrder->code,
                                    'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                    'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                    'status'       => $row_po_detail->purchaseOrder->statusRaw(),
                                ];
                                if($row_po_detail->goodReceiptDetail()->exists()){
                                    
                                    
                                    foreach($row_po_detail->goodReceiptDetail as $row_grpo_detail){
                                        $grpo=[
                                            'grpo_code'    => $row_grpo_detail->goodReceipt->code,
                                            'grpo_date'    => $row_grpo_detail->goodReceipt->post_date,
                                            'grpo_qty'     => CustomHelper::formatConditionalQty($row_grpo_detail->qty),
                                            'outstanding'  => $row_po_detail->getBalanceReceipt(),
                                            'status'       => $row_grpo_detail->goodReceipt->statusRaw(),
                                        ];
                                        $total_grpo++;
                                        $po['grpo'][]=$grpo;
                                        // $array_detail[]=[
                                        //     'item'         => $row_item_request_detail->item->name,
                                        //     'item_code'    => $row_item_request_detail->item->code,
                                        //     'ir_code'      => $row_item_request->code,
                                        //     'ir_date'      => $row_item_request->post_date,
                                        //     'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                        //     'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                        //     'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                        //     'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                        //     'po_code'      => $row_po_detail->purchaseOrder->code,
                                        //     'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                        //     'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                        //     'grpo_code'    => $row_grpo_detail->goodReceipt->code,
                                        //     'grpo_date'    => $row_grpo_detail->goodReceipt->post_date,
                                        //     'grpo_qty'     => CustomHelper::formatConditionalQty($row_grpo_detail->qty),
                                        // ];
                                        
                                    }
                                    $pr['po'][]=$po;
                                    
                                    if($max_count<$total_grpo){
                                        $max_count=$total_grpo;
                                    }
                                    
                                    if ($max_count_pr < $total_grpo) {
                                        $max_count_pr = $total_grpo;
                                    }
                                    $pr['rowspan']=$max_count_pr;
                                   
                                }else{
                                    $po['grpo'][]=[
                                        'grpo_code'    => '',
                                        'grpo_date'    => '',
                                        'grpo_qty'     => '',
                                        'status'       => '',
                                        'outstanding'  => '',
                                    ];
                                    $pr['po'][]=$po;
                                    $pr['rowspan']=$max_count_pr;
                                   
                                    /* $array_detail[]=[
                                        'item'         => $row_item_request_detail->item->name,
                                        'item_code'    => $row_item_request_detail->item->code,
                                        'ir_code'      => $row_item_request->code,
                                        'ir_date'      => $row_item_request->post_date,
                                        'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                        'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                        'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                        'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                        'po_code'      => $row_po_detail->purchaseOrder->code,
                                        'po_date'      => $row_po_detail->purchaseOrder->post_date,
                                        'po_qty'       => CustomHelper::formatConditionalQty($row_po_detail->qty),
                                        'grpo_code'    => '',
                                        'grpo_date'    => '',
                                        'grpo_qty'     => '',
                                    ]; */
                                }
                            }
                            if($max_count<$total_po){
                                $max_count=$total_po;
                            }
                        
                        }else{
                            $grpo=['grpo_code'    => '',
                            'grpo_date'    => '',
                            'grpo_qty'     => '',
                            'status'       => '',
                            'outstanding'  => ''];
                            $po=['po_code'      => '',
                            'po_date'      => '',
                            'po_qty'       => '',
                            'status'       => '',];
                            $pr=[
                                'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                'status'       => $row_pr_detail->purchaseRequest->statusRaw(),
                            ];
                            $po['grpo'][]=$grpo;
                            $pr['po'][]=$po;
                            $pr['rowspan']=$max_count_pr;
                           
                           /*  $array_detail[]=[
                                'item'         => $row_item_request_detail->item->name,
                                'item_code'    => $row_item_request_detail->item->code,
                                'ir_code'      => $row_item_request->code,
                                'ir_date'      => $row_item_request->post_date,
                                'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                                'pr_code'      => $row_pr_detail->purchaseRequest->code,
                                'pr_date'      => $row_pr_detail->purchaseRequest->post_date,
                                'pr_qty'       => CustomHelper::formatConditionalQty($row_pr_detail->qty),
                                'po_code'      => '',
                                'po_date'      => '',
                                'po_qty'       => '',
                                'grpo_code'    => '',
                                'grpo_date'    => '',
                                'grpo_qty'     => '',
                            ]; */
                        }
                        $all_pr[]=$pr;
                    }
                    if($max_count<$total_pr){
                        $max_count=$total_pr;
                    }
                
                }else{
                    $grpo=[ 'grpo_code'    => '',
                            'grpo_date'    => '',
                            'grpo_qty'     => '',
                            'status'       => '',
                            'outstanding'  => ''];
                    $po=['po_code'      => '',
                    'po_date'      => '',
                    'po_qty'       => '',
                    'status'       => '',];
                    $pr=[
                        'pr_code'      => '',
                        'pr_date'      => '',
                        'pr_qty'       => '',
                        'status'       => '',
                    ];
                    $po['grpo'][]=$grpo;
                    $pr['po'][]=$po;
                    $pr['rowspan']=$max_count_pr;
                    
                    /* $array_detail[]=[
                        'item'         => $row_item_request_detail->item->name,
                        'item_code'    => $row_item_request_detail->item->code,
                        'ir_code'      => $row_item_request->code,
                        'ir_date'      => $row_item_request->post_date,
                        'ir_qty'       => CustomHelper::formatConditionalQty($row_item_request_detail->qty),
                        'pr_code'      => '',
                        'pr_date'      => '',
                        'pr_qty'       => '',
                        'po_code'      => '',
                        'po_date'      => '',
                        'po_qty'       => '',
                        'grpo_code'    => '',
                        'grpo_date'    => '',
                        'grpo_qty'     => '',
                    ]; */
                }
                
                $array_item_req['pr']=$all_pr;
                $array_item_req['rowspan']=$max_count;
                $array_detail[]=$array_item_req;
            }
           
        }
        info($array_detail);
        return view('admin.exports.purchase_progress_report', [
            'data' => $array_detail,
        ]);
    }
}
