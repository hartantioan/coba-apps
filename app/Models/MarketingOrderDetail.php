<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_id',
        'item_id',
        'qty',
        'item_unit_id',
        'qty_conversion',
        'price',
        'margin',
        'is_include_tax',
        'percent_tax',
        'tax_id',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'other_fee',
        'price_after_discount',
        'total',
        'tax',
        'grandtotal',
        'note',
        'place_id',
    ];

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function marketingOrder()
    {
        return $this->belongsTo('App\Models\MarketingOrder', 'marketing_order_id', 'id')->withTrashed();
    }

    public function marketingOrderDeliveryDetail(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryDetail','marketing_order_detail_id','id')->whereHas('marketingOrderDelivery',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function marketingOrderPlanDetail(){
        return $this->hasMany('App\Models\MarketingOrderPlanDetail','marketing_order_detail_id','id')->whereHas('marketingOrderPlan',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function balanceQtyMod(){
        $qty = $this->qty;

        foreach($this->marketingOrderDeliveryDetail as $row){
            $qty -= $row->qty;
            $qty += $row->qtyReturn();
        }

        return $qty;
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemUnit(){
        return $this->belongsTo('App\Models\ItemUnit','item_unit_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function taxId(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function realPriceAfterGlobalDiscount(){
        $bobot = $this->total / $this->marketingOrder->subtotal;
        $discountRow = $bobot * $this->marketingOrder->discount;
        $discountPerItem = $discountRow / $this->qty;
        $realPrice = $this->price_after_discount - $discountPerItem;

        return $realPrice;
    }
}
