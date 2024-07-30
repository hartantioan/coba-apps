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
        'production_order_detail_id',
        'item_id',
        'bom_id',
        'item_reject_id',
        'is_powder',
        'qty',
        'qty_planned',
        'qty_reject',
        'place_id',
        'warehouse_id',
        'total',
    ];

    public function totalBatch(){
        $total = $this->productionBatch()->sum('total');
        return $total;
    }

    public function productionBatch()
    {
        return $this->hasMany('App\Models\ProductionBatch', 'lookable_id', 'id')->where('lookable_type',$this->table);
    }

    public function getProductionBatchCodesAttribute()
    {
        return $this->productionBatch->pluck('code')->implode(', ');
    }

    public function productionReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_receive_id', 'id')->withTrashed();
    }

    public function parent()
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

    public function productionOrderDetail()
    {
        return $this->belongsTo('App\Models\ProductionOrderDetail', 'production_order_detail_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemReject(){
        return $this->belongsTo('App\Models\Item','item_reject_id','id')->withTrashed();
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }
}
