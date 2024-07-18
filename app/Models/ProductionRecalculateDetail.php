<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionRecalculateDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_recalculate_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_recalculate_id',
        'lookable_type',
        'lookable_id',
        'production_batch_id',
        'resource_id',
        'total',
    ];

    public function productionRecalculate(){
        return $this->belongsTo('App\Models\ProductionRecalculate','production_recalculate_id','id')->withTrashed();
    }

    public function productionBatch(){
        return $this->belongsTo('App\Models\ProductionBatch','production_batch_id','id')->withTrashed();
    }

    public function resource()
    {
        return $this->belongsTo('App\Models\Resource', 'resource_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }
}
