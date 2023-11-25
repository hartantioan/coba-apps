<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferOutDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_transfer_out_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'inventory_transfer_out_id',
        'item_stock_id',
        'item_id',
        'qty',
        'price',
        'total',
        'note',
        'area_id',
    ];

    public function inventoryTransferOut()
    {
        return $this->belongsTo('App\Models\InventoryTransferOut', 'inventory_transfer_out_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function landedCostDetail()
    {
        return $this->hasMany('App\Models\LandedCostDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('landedCost',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
