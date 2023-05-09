<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemGroupWarehouse extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_group_warehouses';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'item_group_id',
        'warehouse_id',
    ];

    public function item(){
        return $this->belongsTo('App\Models\ItemGroup')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }
}
