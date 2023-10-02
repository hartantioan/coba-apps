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
        'shift_id',
        'item_id',
        'bom_id',
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
}
