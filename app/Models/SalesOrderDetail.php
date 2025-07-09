<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_order_details';

    protected $fillable = [
        'sales_order_id',
        'item_id',
        'qty',
        'price',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'price_after_discount',
        'note',
    ];

    protected $dates = ['deleted_at'];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
