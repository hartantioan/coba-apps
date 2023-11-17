<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendancePunishment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'attendance_punishments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'employee_id',
        'period_id',
        'punishment_id',
        'type',
        'frequent',
        'total',
        'dates',
    ];

    public function punishment()
    {
        return $this->belongsTo('App\Models\Punishment', 'punishment_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
    public function employee()
    {
        return $this->belongsTo('App\Models\User', 'employee_id','id')->withTrashed();
    }

    public function period()
    {
        return $this->belongsTo('App\Models\AttendancePeriod', 'period_id','id')->withTrashed();
    }
}
