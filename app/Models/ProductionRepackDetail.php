<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionRepackDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_repack_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_repack_id',
        'item_source_id',
        'item_stock_id',
        'qty',
        'item_unit_source_id',
        'item_target_id',
        'item_unit_target_id',
        'place_id',
        'warehouse_id',
        'item_shading_id',
        'production_batch_id',
        'area_id',
        'line_id',
        'shift_id',
        'group',
        'batch_no',
        'total',
    ];

    public function productionRepack(){
        return $this->belongsTo('App\Models\ProductionRepack','production_repack_id','id')->withTrashed();
    }

    public function itemSource(){
        return $this->belongsTo('App\Models\Item','item_source_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id')->withTrashed();
    }
    
    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }

    public function itemUnitSource(){
        return $this->belongsTo('App\Models\ItemUnit','item_unit_source_id','id')->withTrashed();
    }

    public function itemTarget(){
        return $this->belongsTo('App\Models\Item','item_target_id','id')->withTrashed();
    }

    public function itemUnitTarget(){
        return $this->belongsTo('App\Models\ItemUnit','item_unit_target_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function productionBatch(){
        return $this->belongsTo('App\Models\ProductionBatch','production_batch_id','id')->withTrashed();
    }

    public function area(){
        return $this->belongsTo('App\Models\Area','area_id','id')->withTrashed();
    }

    public function productionBatchUsage(){
        return $this->hasMany('App\Models\ProductionBatchUsage','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
