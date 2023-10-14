<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionScheduleDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_schedule_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_schedule_id',
        'production_date',
        'shift_id',
        'item_id',
        'bom_id',
        'marketing_order_plan_detail_id',
        'qty',
    ];

    public function productionSchedule()
    {
        return $this->belongsTo('App\Models\ProductionSchedule', 'production_schedule_id', 'id')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id')->withTrashed();
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function marketingOrderPlanDetail(){
        return $this->belongsTo('App\Models\MarketingOrderPlanDetail','marketing_order_plan_detail_id','id')->withTrashed();
    }

    public function productionIssueReceiveDetail(){
        return $this->hasMany('App\Models\ProductionIssueReceiveDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('productionIssueReceive',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}