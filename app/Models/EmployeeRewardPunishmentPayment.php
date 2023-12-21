<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class EmployeeRewardPunishmentPayment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'employee_reward_punishment_payments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'period_id',
        'post_date',
        'nominal',
        'employee_reward_punishment_detail_id',
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
    public function attendancePeriod(){
        return $this->belongsTo('App\Models\AttendancePeriod','period_id','id')->withTrashed();
    }

    public function employeeRewardPunishmentDetail(){
        return $this->belongsTo('App\Models\EmployeeRewardPunishmentDetail','employee_reward_punishment_detail_id','id')->withTrashed();
    }
}
