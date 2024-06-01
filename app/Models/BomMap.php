<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BomMap extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'bom_maps';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'parent_id',
        'child_id',
    ];

    public function parent(){
        return $this->belongsTo('App\Models\Bom','parent_id','id')->withTrashed();
    }

    public function child(){
        return $this->belongsTo('App\Models\Bom','child_id','id')->withTrashed();
    }
}
