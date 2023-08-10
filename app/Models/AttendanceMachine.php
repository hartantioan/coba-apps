<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendanceMachine extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendance_machines';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'code',
        'name',
        'ip_address',
        'port',
        'location',
        'status'
    ];
}
