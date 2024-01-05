<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InventoryRevaluationDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_revaluation_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'inventory_revaluation_id',
        'item_stock_id',
        'item_id',
        'qty',
        'place_id',
        'warehouse_id',
        'nominal',
        'coa_id',
        'line_id',
        'machine_id',
        'department_id',
        'project_id',
    ];

    public function inventoryRevaluation()
    {
        return $this->belongsTo('App\Models\InventoryRevaluation', 'inventory_revaluation_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }
}
