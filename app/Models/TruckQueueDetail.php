<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckQueueDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'truck_queue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'truck_queue_id',
        'good_scale_id',
        'time_in',
    ];

    public function truckQueue()
    {
        return $this->belongsTo(TruckQueue::class);
    }

    public function goodScale()
    {
        return $this->hasOne('App\Models\GoodScale','id','good_scale_id');
    }

}
