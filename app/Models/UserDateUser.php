<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserDateUser extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_date_users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_date_id',
        'user_id',
    ];

    public function userDate(){
        return $this->belongsTo('App\Models\UserDate','user_date_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}