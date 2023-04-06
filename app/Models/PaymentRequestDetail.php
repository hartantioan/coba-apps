<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'payment_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_request_id',
        'fund_request_id',
        'purchase_down_payment_id',
        'purchase_invoice_id',
        'nominal',
        'note',
    ];

    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }
    
    public function fundRequest()
    {
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }

    public function purchaseDownPayment(){
        return $this->belongsTo('App\Models\PurchaseDownPayment','purchase_down_payment_id','id')->withTrashed();
    }

    public function purchaseInvoice(){
        return $this->belongsTo('App\Models\PurchaseInvoice','purchase_invoice_id','id')->withTrashed();
    }
}
