<?php

namespace App\Models;

use App\Helpers\CustomHelper;
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
        'production_order_detail_id',
        'lookable_type',
        'lookable_id',
        'bom_id',
        'bom_detail_id',
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
        'is_wip',
    ];

    public function productionBatchUsage(){
        return $this->hasMany('App\Models\ProductionBatchUsage','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function productionIssue()
    {
        return $this->belongsTo('App\Models\ProductionIssue', 'production_issue_id', 'id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\ProductionIssue', 'production_issue_id', 'id')->withTrashed();
    }

    public function productionOrderDetail()
    {
        return $this->belongsTo('App\Models\ProductionOrderDetail', 'production_order_detail_id', 'id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','from_item_stock_id','id');
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function bomDetail(){
        return $this->belongsTo('App\Models\BomDetail','bom_detail_id','id')->withTrashed();
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

    public function listBatchUsed(){
        $arr = [];
        foreach($this->productionBatchUsage as $row){
            $arr[] = $row->productionBatch->code.' - '.CustomHelper::formatConditionalQty($row->qty);
        }
        return implode(', ',$arr);
    }
}
