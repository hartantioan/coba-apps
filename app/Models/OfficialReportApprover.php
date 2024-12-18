<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Storage;

class OfficialReportApprover extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'official_report_approvers';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'official_report_id',
        'user_id',
    ];

    public function officialReport(){
        return $this->belongsTo('App\Models\OfficialReport','official_report_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}