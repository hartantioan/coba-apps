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
        'item_stock_id',
        'place_id',
        'warehouse_id',
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

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function tax(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }
}
