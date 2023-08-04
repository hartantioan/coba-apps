<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class AttendanceTemp extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'attendance_temps';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'verify_type',
        'record_time',
        'machine_id',
    ];
}
