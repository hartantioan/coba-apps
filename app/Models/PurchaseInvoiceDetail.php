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
        'good_receipt_id',
        'landed_cost_id',
        'purchase_order_id',
        'lookable_type',
        'lookable_id',
        'total',
        'tax_id',
        'wtax_id',
        'is_include_tax',
        'percent_tax',
        'tax',
        'percent_wtax',
        'wtax',
        'grandtotal',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo('App\Models\PurchaseInvoice', 'purchase_invoice_id', 'id')->withTrashed();
    }
    
    public function landedCost()
    {
        if($this->lookable_type == 'landed_costs'){
            return true;
        }else{
            return false;
        }
    }

    public function goodReceipt(){
        if($this->lookable_type == 'good_receipts'){
            return true;
        }else{
            return false;
        }
    }

    public function purchaseOrder(){
        if($this->lookable_type == 'purchase_orders'){
            return true;
        }else{
            return false;
        }
    }

    public function getCode(){
        $code = match ($this->lookable_type) {
            'good_receipts'     => $this->lookable->getPurchaseCode(),
            'landed_costs'      => $this->lookable->goodReceipt->getPurchaseCode(),
            'purchase_orders'   => $this->lookable->code,
            'coas'              => $this->lookable->code.' - '.$this->lookable->name,
            default             => '-',
        };

        return $code;
    }

    public function getPurchaseCode(){
        $code = match ($this->lookable_type) {
            'good_receipts'     => $this->lookable->getPurchaseCode(),
            'landed_costs'      => $this->lookable->goodReceipt->getPurchaseCode(),
            'purchase_orders'   => $this->lookable->code,
            default => '-',
        };

        return $code;
    }

    public function getDeliveryCode(){
        $code = match ($this->lookable_type) {
            'good_receipts'     => $this->lookable->code,
            'landed_costs'      => $this->lookable->goodReceipt->code,
            default => '-',
        };

        return $code;
    }

    public function getListItem(){
        $list = match ($this->lookable_type) {
            'good_receipts'     => $this->lookable->getListItem(),
            'landed_costs'      => $this->lookable->goodReceipt->getListItem(),
            default => '-',
        };


    }
}
