<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FgGroup extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'fg_groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'parent_id',
        'item_id',
    ];

    public function parent(){
        return $this->belongsTo('App\Models\Item','parent_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
