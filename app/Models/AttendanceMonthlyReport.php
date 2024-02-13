<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendanceMonthlyReport extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendance_monthly_reports';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'user_id',
        'late',
        'leave_early',
        'period_id',
        'effective_day',
        'absent',//masuk
        'special_occasion',
        'sick',
        'outstation',//dinas keluar
        'furlough',//cuti
        'dispen',
        'permit',
        'shift_exchange',
        'alpha',//tidak masuk
        'wfh',
        'arrived_on_time',
        'out_on_time',
        'out_log_forget',
        'arrived_forget',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function period(){
        return $this->belongsTo('App\Models\AttendancePeriod','period_id','id');
    }
    

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
