<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Attendances extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'code',
        'employee_no',
        'date',
        'verify_type',
        'location',
        'latitude',
        'longitude'
    ];
}
