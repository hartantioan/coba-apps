<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReturnDetail extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_return_details';

    protected $fillable = [
        'inventory_return_id',
        'item_id',
        'price',
        'total',
        'grandtotal',
        'note',
    ];
    public function inventoryReturn()
    {
        return $this->belongsTo(InventoryReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
