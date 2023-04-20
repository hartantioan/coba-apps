<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class inventoryTransferDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_transfer_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'inventory_transfer_id',
        'item_id',
        'qty',
        'item_stock_id',
        'to_place_id',
        'to_warehouse_id',
        'note',
    ];

    public function inventoryTransfer()
    {
        return $this->belongsTo('App\Models\InventoryTransfer', 'inventory_transfer_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function toWarehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'to_warehouse_id', 'id')->withTrashed();
    }

    public function toPlace()
    {
        return $this->belongsTo('App\Models\Place', 'to_place_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id')->withTrashed();
    }
}
