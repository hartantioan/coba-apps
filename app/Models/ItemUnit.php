<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_units';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'item_id',
        'unit_id',
        'is_sell_unit',
        'is_purchase_unit',
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function unit(){
        return $this->belongsTo('App\Models\Unit','unit_id','id')->withTrashed();
    }
}
