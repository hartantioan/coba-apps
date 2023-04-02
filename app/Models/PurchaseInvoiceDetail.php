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
        'good_receipt_main_id',
        'landed_cost_id',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'is_wtax',
        'percent_wtax'
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo('App\Models\PurchaseInvoice', 'purchase_invoice_id', 'id')->withTrashed();
    }

    public function isWtax(){
        $type = match ($this->is_wtax) {
          '0' => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }
    
    public function landedCost()
    {
        return $this->belongsTo('App\Models\LandedCost', 'landed_cost_id', 'id')->withTrashed();
    }

    public function goodReceiptMain(){
        return $this->belongsTo('App\Models\GoodReceiptMain','good_receipt_main_id','id')->withTrashed();
    }
}
