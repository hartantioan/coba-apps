<?php

namespace App\Exports;

use App\Models\MaterialRequest;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\PurchaseOrder;

class ExportPaymentProgressReport implements  FromView,ShouldAutoSize,WithTitle
{
    protected $start_date,$end_date,$type,$group;
    public function __construct(string $start_date,string $end_date,string $type, string $group)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
    }
    public function title(): string
    {
        return 'Progres Pemmbelian'; // Set the custom name for the first sheet
    }

    public function view(): View
    {
        $data = PurchaseOrder::where(function($query) {
            
            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }
            if($this->group){
                $query->whereHas('purchaseOrderDetail',function($query){
                    $query->whereHas('item',function($query){
                        $query->whereIn('item_group_id', explode(',',$this->group));
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
                    'item'         => $row_item_po_detail->item->name ?? $row_item_po_detail->coa->name ,
                    'item_code'    => $row_item_po_detail->item->code ?? $row_item_po_detail->coa->code,
                    'po_code'      => $row_item_po->code,
                    'po_date'      => $row_item_po->post_date,
                    'po_qty'       => $row_item_po_detail->qty,
                    'nominal'      => number_format($row_item_po_detail->grandtotal,2,',','.'),
                    'status'       => $row_item_po->statusRaw(),
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
                            'grpo_qty'       => $row_gr_detail->qty,
                            'nominal'        => number_format($row_gr_detail->grandtotal,2,',','.'),
                            'status'         => $row_gr_detail->goodReceipt->statusRaw(),
                        ];
                        if($row_gr_detail->purchaseInvoiceDetail()->exists()){
                            if($max_count<count($row_gr_detail->purchaseInvoiceDetail)){
                                $max_count=count($row_gr_detail->purchaseInvoiceDetail);
                            }
                            
                            if ($max_count_pr < count($row_gr_detail->purchaseInvoiceDetail)) {
                                $max_count_pr = count($row_gr_detail->purchaseInvoiceDetail);
                            }
                            $total_po = 0 ;
                            $total_pyr = 0;
                            foreach($row_gr_detail->purchaseInvoiceDetail as $row_inv_detail){
                                $inv=[
                                    'inv_code'      => $row_inv_detail->purchaseInvoice->code,
                                    'inv_date'      => $row_inv_detail->purchaseInvoice->post_date,
                                    'inv_qty'       => $row_inv_detail->qty,
                                    'nominal'       => number_format($row_inv_detail->grandtotal,2,',','.'),
                                    'status'        => $row_inv_detail->purchaseInvoice->statusRaw(),
                                ];
                                if($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail()->exists()){
                                    
                                    
                                    foreach($row_inv_detail->purchaseInvoice->hasPaymentRequestDetail as $row_pyr_detail){
                                        $pyr=[
                                            'pyr_code'    => $row_pyr_detail->paymentRequest->code,
                                            'pyr_date'    => $row_pyr_detail->paymentRequest->post_date,
                                            'pyr_qty'     => $row_pyr_detail->qty,
                                            'nominal'     => number_format($row_pyr_detail->nominal,2,',','.'),
                                            'status'      => $row_pyr_detail->paymentRequest->statusRaw(),
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
                            if($max_count<$total_po){
                                $max_count=$total_po;
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
                                'grpo_qty'       => $row_gr_detail->qty,
                                'status'         => $row_gr_detail->goodReceipt->statusRaw(),
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

                }else{
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
      
        return view('admin.exports.payment_progress_report', [
            'data' => $array_detail,
            'type' => $this->type,
        ]);
    }
}
