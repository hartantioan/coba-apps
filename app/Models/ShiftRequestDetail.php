<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ShiftRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'shift_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'shift_request_id',
        'employee_schedule_id',
        'shift_id',
        'date',
    ];

    public function shiftRequest(){
        return $this->belongsTo('App\Models\ShiftRequest','shift_request_id','id');
    }

    public function employeeSchedule(){
        return $this->belongsTo('App\Models\EmployeeSchedule','employee_schedule_id','id');
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id');
    }
}
