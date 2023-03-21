<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserPlace extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_places';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'place_id',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id');
    }
}