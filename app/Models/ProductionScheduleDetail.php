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
        'marketing_order_plan_detail_id',
        'item_id',
        'bom_id',
        'production_date',
        'qty',
        'line_id',
        'warehouse_id',
        'note',
        'status',
        'type',
        'status_process',
    ];

    public function productionSchedule()
    {
        return $this->belongsTo('App\Models\ProductionSchedule', 'production_schedule_id', 'id')->withTrashed();
    }

    public function marketingOrderPlanDetail(){
        return $this->belongsTo('App\Models\MarketingOrderPlanDetail','marketing_order_plan_detail_id','id')->withTrashed();
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function productionOrderDetail(){
        return $this->hasOne('App\Models\ProductionOrderDetail','production_schedule_detail_id','id')->whereHas('productionOrder',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function status(){
        $status = match ($this->status) {
            NULL    => 'Menunggu',
            '1'     => 'Disetujui',
            '2'     => 'Ditolak',
            default => 'Invalid',
        };

        return $status;
    }

    public function statusProcess(){
        $status = match ($this->status) {
            NULL    => 'Menunggu',
            '1'     => 'Proses',
            '2'     => 'Selesai',
            '3'     => 'Ditunda',
            default => 'Invalid',
        };

        return $status;
    }

    public function type(){
        $type = match ($this->type) {
            '1'     => 'Powder',
            '2'     => 'Green Tile',
            '3'     => 'FG',
            default => 'Invalid',
        };

        return $type;
    }
}