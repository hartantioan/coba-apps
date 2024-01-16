<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class SalaryReportUser extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'salary_report_users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'salary_report_id',
        'user_id',
        'total_plus',
        'total_minus',
        'total_received',
    ];
    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function salaryReport(){
        return $this->belongsTo('App\Models\SalaryReport', 'salary_report_id', 'id')->withTrashed();
    }

    public function salaryReportDetail(){
        return $this->hasMany('App\Models\SalaryReportDetail');
    }
    
}
