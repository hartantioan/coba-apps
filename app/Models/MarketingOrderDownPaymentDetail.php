<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDownPaymentDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_down_payment_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_down_payment_id',
        'marketing_order_id',
    ];

    public function marketingOrderDownPayment()
    {
        return $this->belongsTo('App\Models\MarketingOrderDownPayment', 'marketing_order_down_payment_id', 'id')->withTrashed();
    }

    public function marketingOrder()
    {
        return $this->belongsTo('App\Models\MarketingOrder', 'marketing_order_id', 'id')->withTrashed();
    }
}
