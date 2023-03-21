<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LandedCostDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'landed_cost_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'landed_cost_id',
        'item_id',
        'qty',
        'nominal',
    ];

    public function landedCost()
    {
        return $this->belongsTo('App\Models\LandedCost', 'landed_cost_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
