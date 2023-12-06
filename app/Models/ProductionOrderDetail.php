<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_order_id',
        'bom_detail_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'qty_real',
        'nominal',
        'nominal_real',
        'total',
        'total_real',
    ];

    public function productionOrder()
    {
        return $this->belongsTo('App\Models\ProductionOrder');
    }

    public function bomDetail(){
        return $this->belongsTo('App\Models\BomDetail','bom_detail_id','id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function item(){
        if($this->lookable_type == 'items'){
            return $this->belongsTo('App\Models\Item', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function coa(){
        if($this->lookable_type == 'coas'){
            return $this->belongsTo('App\Models\Coa', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }
}