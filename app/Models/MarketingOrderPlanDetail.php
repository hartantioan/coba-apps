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
        'item_id',
        'qty',
        'request_date',
        'note',
        'is_urgent',
    ];

    public function marketingOrderPlan()
    {
        return $this->belongsTo('App\Models\MarketingOrderPlan', 'marketing_order_plan_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function productionScheduleDetail()
    {
        return $this->hasMany('App\Models\ProductionScheduleDetail')->whereHas('productionSchedule',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function isUrgent(){
        $is_urgent = match ($this->is_urgent) {
            '1' => 'Ya',
            default => 'Tidak',
        };

        return $is_urgent;
    }

    public function totalScheduled()
    {
        $total = 0;
        foreach($this->productionScheduleDetail as $row){
            $total += $row->qty;
        }
        return $total;
    }

    public function productionScheduleTarget()
    {
        return $this->hasMany('App\Models\ProductionScheduleTarget')->whereHas('productionSchedule',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
