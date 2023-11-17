<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderReturnDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_return_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_return_id',
        'marketing_order_delivery_detail_id',
        'item_id',
        'qty',
        'note',
        'place_id',
        'warehouse_id',
        'area_id',
    ];

    public function marketingOrderDeliveryDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryDetail', 'marketing_order_delivery_detail_id', 'id')->withTrashed();
    }

    public function marketingOrderReturn()
    {
        return $this->belongsTo('App\Models\MarketingOrderReturn', 'marketing_order_return_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }
    
    public function area(){
        return $this->belongsTo('App\Models\Area','area_id','id')->withTrashed();
    }

    public function getGrandtotal(){
        $total = 0;
        $totalDelivered = $this->marketingOrderDeliveryDetail->getGrandtotal();
        $bobot = $this->qty / $this->marketingOrderDeliveryDetail->qty;
        $total = $bobot * $totalDelivered;
        return $total;
    }
}