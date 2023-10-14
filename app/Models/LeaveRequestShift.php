<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class LeaveRequestShift extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'leave_request_shift';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'leave_request_id',
        'employee_schedule_id',
    ];

    public function employeeSchedule(){
        return $this->belongsTo('App\Models\EmployeeSchedule','employee_schedule_id','id');
    }

    public function leaveRequest(){
        return $this->belongsTo('App\Models\LeaveRequest','leave_request_id','id');
    }
}
