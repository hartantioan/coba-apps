<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'item_stocks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'place_id',
        'warehouse_id',
        'item_id',
        'qty'
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }
}
