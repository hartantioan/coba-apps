<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class UserItemUser extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'user_item_users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_item_id',
        'user_id',
    ];
    public function userItem(){
        return $this->belongsTo('App\Models\UserItem','user_item_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}
