<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_receive_id',
        'production_order_id',
        'item_id',
        'bom_id',
        'is_powder',
        'qty',
        'qty_planned',
        'place_id',
        'warehouse_id',
        'tank_id',
        'batch_no',
        'production_batch_id',
        'total',
    ];

    public function productionBatch()
    {
        return $this->hasOne('App\Models\ProductionBatch', 'production_batch_id', 'id');
    }

    public function productionReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_receive_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function productionOrder(){
        return $this->belongsTo('App\Models\ProductionOrder','production_order_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function tank(){
        return $this->belongsTo('App\Models\Tank','tank_id','id')->withTrashed();
    }
}
