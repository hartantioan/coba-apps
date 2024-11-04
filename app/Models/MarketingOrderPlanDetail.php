<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderPlanDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_plan_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_plan_id',
        'marketing_order_detail_id',
        'item_id',
        'qty',
        'request_date',
        'note',
        'note2',
        'line_id',
    ];

    public function marketingOrderPlan()
    {
        return $this->belongsTo('App\Models\MarketingOrderPlan', 'marketing_order_plan_id', 'id')->withTrashed();
    }

    public function marketingOrderDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDetail', 'marketing_order_detail_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function totalScheduled()
    {
        $qty = 0;
        
        //logika ter-produksi
        foreach($this->productionScheduleTarget as $row){
            $qty += $row->qty;
        }

        return $qty;
    }

    public function productionScheduleTarget()
    {
        return $this->hasMany('App\Models\ProductionScheduleTarget')->whereHas('productionSchedule',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }
}
