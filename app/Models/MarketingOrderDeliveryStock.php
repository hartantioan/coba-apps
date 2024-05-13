<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDeliveryStock extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_stocks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_delivery_detail_id',
        'marketing_order_detail_id',
        'item_stock_id',
        'qty',
    ];

    public function marketingOrderDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDetail', 'marketing_order_detail_id', 'id')->withTrashed();
    }

    public function marketingOrderDeliveryDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryDetail', 'marketing_order_delivery_detail_id', 'id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }
}
