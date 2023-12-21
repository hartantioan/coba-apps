<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class EmployeeSalaryComponent extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'employee_salary_components';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'salary_component_id',
        'nominal',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
    public function salaryComponent(){
        return $this->belongsTo('App\Models\SalaryComponent','salary_component_id','id')->withTrashed();
    }
}
