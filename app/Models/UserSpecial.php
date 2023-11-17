<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserSpecial extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_specials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
