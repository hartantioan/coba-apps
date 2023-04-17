<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDp extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_invoice_dps';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_invoice_id',
        'purchase_down_payment_id',
        'nominal',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo('App\Models\PurchaseInvoice', 'purchase_invoice_id', 'id')->withTrashed();
    }

    public function purchaseDownPayment()
    {
        return $this->belongsTo('App\Models\PurchaseDownPayment', 'purchase_down_payment_id', 'id')->withTrashed();
    }
}
