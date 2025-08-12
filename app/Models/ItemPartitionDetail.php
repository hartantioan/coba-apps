<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPartitionDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_partition_details';

    protected $fillable = [
        'item_partition_id',
        'item_stock_new_id',
        'to_item_stock_new_id',
        'qty',
        'price',
        'total',
        'qty_partition',
        'note',
    ];

    public function itemPartition()
    {
        return $this->belongsTo(ItemPartition::class, 'item_partition_id');
    }

    public function fromStock()
    {
        return $this->belongsTo(ItemStockNew::class, 'item_stock_new_id');
    }

    public function toStock()
    {
        return $this->belongsTo(ItemStockNew::class, 'to_item_stock_new_id');
    }

}
