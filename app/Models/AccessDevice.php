<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
class AccessDevice extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'access_devices';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'user_agent',
        'is_mobile',
        'is_computer',
        'ip',
    ];

}
