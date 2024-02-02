<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CapitalizationDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'capitalization_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'capitalization_id',
        'asset_id',
        'place_id',
        'warehouse_id',
        'line_id',
        'machine_id',
        'department_id',
        'project_id',
        'cost_distribution_id',
        'qty',
        'unit_id',
        'price',
        'total',
        'note'
    ];

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function capitalization(){
        return $this->belongsTo('App\Models\Capitalization', 'capitalization_id', 'id')->withTrashed();
    }

    public function asset(){
        return $this->belongsTo('App\Models\Asset', 'asset_id', 'id')->withTrashed();
    }

    public function unit(){
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }
}
