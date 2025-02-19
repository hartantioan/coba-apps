<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HandoverPurchaseInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'handover_purchase_invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'purchase_invoice_id',
        'handover_purchase_invoice_id',
        'status',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo('App\Models\PurchaseInvoice', 'purchase_invoice_id', 'id')->withTrashed();
    }

    public function handOverPurchaseInvoice(){
        return $this->belongsTo('App\Models\HandOverPurchaseInvoice','handover_purchase_invoice_id','id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => 'Pending',
          '2' => 'Approved',
          '3' => 'Rejected',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusColor(){
        $status = match ($this->status) {
          '1' => 'yellow',
          '2' => 'green',
          '3' => 'red',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
