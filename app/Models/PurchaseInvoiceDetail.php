<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_invoice_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'price',
        'total',
        'tax_id',
        'wtax_id',
        'is_include_tax',
        'percent_tax',
        'tax',
        'percent_wtax',
        'wtax',
        'grandtotal',
        'note',
        'note2',
        'place_id',
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function wTaxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'wtax_id', 'id')->withTrashed();
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo('App\Models\PurchaseInvoice', 'purchase_invoice_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }
    
    public function coa()
    {
        if($this->lookable_type == 'coas'){
            return true;
        }else{
            return false;
        }
    }
    
    public function landedCostFeeDetail()
    {
        if($this->lookable_type == 'landed_cost_fee_details'){
            return true;
        }else{
            return false;
        }
    }

    public function goodReceiptDetail(){
        if($this->lookable_type == 'good_receipt_details'){
            return true;
        }else{
            return false;
        }
    }

    public function purchaseOrderDetail(){
        if($this->lookable_type == 'purchase_order_details'){
            return true;
        }else{
            return false;
        }
    }

    public function getCode(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->item->code.' - '.$this->lookable->item->name,
            'landed_cost_fee_details'   => $this->lookable->landedCostFee->name,
            'purchase_order_details'    => $this->lookable->item_id ? $this->lookable->item->code.' - '.$this->lookable->item->name : $this->lookable->coa->name,
            'coas'                      => $this->lookable->name,
            default                     => '-',
        };

        return $code;
    }

    public function getHeaderCode(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->goodReceipt->code,
            'landed_cost_fee_details'   => $this->lookable->landedCost->code,
            'purchase_order_details'    => $this->lookable->purchaseOrder->code,
            'coas'                      => '-',
            default                     => '-',
        };

        return $code;
    }

    public function getTop(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->purchaseOrderDetail->purchaseOrder->payment_term,
            'landed_cost_fee_details'   => '0',
            'purchase_order_details'    => $this->lookable->purchaseOrder->payment_term,
            default                     => '-',
        };

        return $code;
    }

    public function getUnitCode(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->item->buyUnit->code,
            'purchase_order_details'    => $this->lookable->item_id ? $this->lookable->item->buyUnit->code : '-',
            default                     => '-',
        };

        return $code;
    }

    public function getPostDate(){
        $date = match ($this->lookable_type) {
            'good_receipt_details'      => date('d/m/y',strtotime($this->lookable->goodReceipt->post_date)),
            'landed_cost_fee_details'   => date('d/m/y',strtotime($this->lookable->landedCost->post_date)),
            'purchase_order_details'    => date('d/m/y',strtotime($this->lookable->purchaseOrder->post_date)),
            default                     => '-',
        };

        return $date;
    }

    public function getDueDate(){
        $date = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->goodReceipt->due_date ? date('d/m/y',strtotime($this->lookable->goodReceipt->due_date)) : '-',
            'purchase_order_details'    => $this->lookable->purchaseOrder->due_date ? date('d/m/y',strtotime($this->lookable->purchaseOrder->due_date)) : '-',
            default                     => '-',
        };

        return $date;
    }

    public function getPurchaseCode(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->purchaseOrderDetail->purchaseOrder->code,
            'landed_cost_fee_details'   => $this->lookable->landedCost->getPurchaseCode(),
            'purchase_order_details'    => $this->lookable->purchaseOrder->code.' - '.$this->lookable->purchaseOrder->code,
            'coas'                      => $this->lookable->code.' - '.$this->lookable->name,
            default                     => '-',
        };

        return $code;
    }

    public function getDeliveryCode(){
        $code = match ($this->lookable_type) {
            'good_receipt_details'      => $this->lookable->goodReceipt->delivery_no,
            'landed_cost_fee_details'   => $this->lookable->landedCost->getListDeliveryNo(),
            default => '-',
        };

        return $code;
    }

    public function getListItem(){
        $list = match ($this->lookable_type) {
            'good_receipts'             => $this->lookable->getListItem(),
            'landed_cost_fee_details'   => $this->lookable->landedCost->getListItem(),
            default => '-',
        };

        return $list;
    }

    public function purchaseMemoDetail(){
        return $this->hasMany('App\Models\PurchaseMemoDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseMemo',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceMemo(){
        $total = $this->total;

        foreach($this->purchaseMemoDetail as $row){
            $total -= $row->total;
        }

        return $total;
    }
}
