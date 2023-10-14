<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class EmployeeSchedule extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'employee_schedules';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'shift_id',
        'date',
        'status',
        'shift_request_id'
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','employee_no')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id')->withTrashed();
    }
}
