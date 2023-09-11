<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendanceDailyReports extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendance_daily_reports';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'user_id',
        'shift_id',
        'period_id',
        'date',
        'masuk',
        'pulang',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\UserAbsensiMesin','user_id','nik');
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id');
    }
}
