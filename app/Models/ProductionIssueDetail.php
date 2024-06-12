<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_issue_id',
        'production_order_id',
        'lookable_type',
        'lookable_id',
        'bom_id',
        'qty',
        'nominal',
        'total',
        'qty_bom',
        'nominal_bom',
        'total_bom',
        'qty_planned',
        'nominal_planned',
        'total_planned',
        'from_item_stock_id',
        'batch_no',
        'place_id',
        'line_id',
        'warehouse_id',
        'area_id',
        'production_batch_id',
    ];

    public function productionIssueReceive()
    {
        return $this->belongsTo('App\Models\ProductionIssueReceive', 'production_issue_receive_id', 'id')->withTrashed();
    }

    public function productionOrder(){
        return $this->belongsTo('App\Models\ProductionOrder','production_order_id','id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','from_item_stock_id','id');
    }

    public function productionBatch(){
        return $this->belongsTo('App\Models\ProductionBatch','production_batch_id','id');
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id');
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id');
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id');
    }

    public function area(){
        return $this->belongsTo('App\Models\Area','area_id','id');
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function type(){
        $type = match ($this->type) {
            '1' => 'Issue',
            '2' => 'Receive',
            default => 'Invalid',
        };

        return $type;
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
