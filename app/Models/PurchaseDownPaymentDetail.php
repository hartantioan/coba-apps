<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseDownPaymentDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_down_payment_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_down_payment_id',
        'purchase_order_id',
        'fund_request_detail_id',
        'nominal',
        'note'
    ];

    public function purchaseDownPayment()
    {
        return $this->belongsTo('App\Models\PurchaseDownPayment', 'purchase_down_payment_id', 'id')->withTrashed();
    }

    public function purchaseOrder(){
        return $this->belongsTo('App\Models\PurchaseOrder','purchase_order_id','id')->withTrashed();
    }

    public function fundRequestDetail(){
        return $this->belongsTo('App\Models\FundRequestDetail','fund_request_detail_id','id')->withTrashed();
    }
}
