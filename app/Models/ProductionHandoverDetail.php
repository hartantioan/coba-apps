<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionHandoverDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_handover_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_handover_id',
        'production_fg_receive_detail_id',
        'item_id',
        'qty',
        'shading',
        'place_id',
        'warehouse_id',
        'area_id',
        'total',
        'item_shading_id',
    ];

    public function productionBatchUsage(){
        return $this->hasOne('App\Models\ProductionBatchUsage','lookable_id','id')->where('lookable_type',$this->table)->withTrashed();
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function productionBatch(){
        return $this->hasOne('App\Models\ProductionBatch','lookable_id','id')->where('lookable_type',$this->table)->withTrashed();
    }

    public function productionHandover()
    {
        return $this->belongsTo('App\Models\ProductionHandover','production_handover_id','id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\ProductionHandover','production_handover_id','id')->withTrashed();
    }

    public function productionFgReceiveDetail()
    {
        return $this->belongsTo('App\Models\ProductionFgReceiveDetail','production_fg_receive_detail_id','id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function itemShading()
    {
        return $this->belongsTo('App\Models\ItemShading', 'item_shading_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
