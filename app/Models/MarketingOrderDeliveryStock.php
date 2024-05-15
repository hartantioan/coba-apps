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
        'cogs',
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

    public function getHpp(){
        $total = round($this->itemStock->priceDate($this->marketingOrderDeliveryDetail->marketingOrderDelivery->post_date) * $this->qty * $this->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,2);
        return $total;
    }

    public function getPriceHpp(){
        return ($this->cogs / ($this->qty * $this->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion));
    }
}
