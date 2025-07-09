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
        'note',
    ];

    // Relationships (optional but recommended)

    public function partition()
    {
        return $this->belongsTo(ItemPartition::class, 'item_partition_id')->withTrashed();
    }

    public function fromStock()
    {
        return $this->belongsTo(ItemStockNew::class, 'item_stock_new_id')->withTrashed();
    }

    public function toStock()
    {
        return $this->belongsTo(ItemStockNew::class, 'to_item_stock_new_id')->withTrashed();
    }
}
