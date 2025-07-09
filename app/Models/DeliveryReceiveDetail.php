<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryReceiveDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_receive_details'; // Explicitly set the table name

    protected $fillable = [
        'delivery_receive_id',
        'item_id',
        'qty',
        'price',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'note',
        'remark',
    ];

    protected $dates = ['deleted_at'];

    public function deliveryReceive()
    {
        return $this->belongsTo(DeliveryReceive::class, 'delivery_receive_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
