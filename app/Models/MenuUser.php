<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MenuUser extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'menu_users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'menu_id',
        'user_id',
        'type',
        'mode',
    ];

    public function menu(){
        return $this->belongsTo('App\Models\Menu');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
