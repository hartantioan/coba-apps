<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CostDistributionDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'cost_distribution_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'cost_distribution_id',
        'place_id',
        'line_id',
        'department_id',
        'warehouse_id',
        'percentage',
    ];

    public function costDistribution(){
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function department(){
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }
}
