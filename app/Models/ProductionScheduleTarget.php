<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionScheduleTarget extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_schedule_targets';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_schedule_id',
        'marketing_order_plan_detail_id',
        'qty',
    ];

    public function productionSchedule()
    {
        return $this->belongsTo('App\Models\ProductionSchedule', 'production_schedule_id', 'id')->withTrashed();
    }

    public function marketingOrderPlanDetail(){
        return $this->belongsTo('App\Models\MarketingOrderPlanDetail','marketing_order_plan_detail_id','id')->withTrashed();
    }
}
