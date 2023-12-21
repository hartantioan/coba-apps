<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class SalaryReportTemplate extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'salary_report_templates';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'salary_report_id',
        'lookable_type',
        'lookable_id',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function salaryComponent(){
        if($this->lookable_type == 'salary_components'){
           return $this->belongsTo('App\Models\SalaryComponent','lookable_id','id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function punishment(){
        if($this->lookable_type == 'punishments'){
           return $this->belongsTo('App\Models\Punishment','lookable_id','id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function salaryReport(){
        return $this->belongsTo('App\Models\SalaryReport', 'salary_report_id', 'id')->withTrashed();
    }
}
