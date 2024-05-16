<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserItemItem extends Model
{

    use HasFactory,SoftDeletes;
    protected $table = 'user_item_items';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_item_id',
        'item_id',
    ];
    public function userItem(){
        return $this->belongsTo('App\Models\UserItem','user_item_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
