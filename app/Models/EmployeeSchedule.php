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
        'is_closed',
        'shift_request_id'
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','employee_no')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id')->withTrashed();
    }

    public function leaveRequestShift(){
        return $this->hasMany('App\Models\LeaveRequestShift')->whereHas('leaveRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            case '3':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Ada Ijin</span>';
                break;
            case '4':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Melahirkan</span>';
                break;
            case '5':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Lembur</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

}
