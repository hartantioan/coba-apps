<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemShading extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_shadings';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'item_id',
        'code'
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->hasMany('App\Models\ItemStock','item_shading_id','id');
    }

    public function itemCogs()
    {
        return $this->hasMany('App\Models\ItemCogs','item_shading_id','id');
    }
}
