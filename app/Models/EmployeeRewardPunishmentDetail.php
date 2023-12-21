<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class EmployeeRewardPunishmentDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'employee_reward_punishment_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nominal_payment',
        'instalment',
        'nominal_total',
        'user_id',
        'note',
        'employee_reward_punishment_id',
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function employeeRewardPunishment(){
        return $this->belongsTo('App\Models\EmployeeRewardPunishment','employee_reward_punishment_id','id')->withTrashed();
    }
}
