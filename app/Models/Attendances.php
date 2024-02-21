<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Attendances extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'code',
        'employee_no',
        'attendance_machine_id',
        'date',
        'verify_type',
        'location',
        'latitude',
        'longitude',
        'revision_attendance_h_r_d_id',
    ];

    public function verifyType(){
        $verify_type = match ($this->verify_type) {
            '1' => 'Finger Print',
            '2' => 'Application',
            '3' => 'Password',
            '4' => 'Web',
            '5' => 'HRD revision',
            default => 'Invalid',
        };

        return $verify_type;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'revision_id', 'id');
    }

    public function revisionAttendanceHRD()
    {
        return $this->belongsTo('App\Models\RevisionAttendanceHRD', 'employee_no', 'employee_no')->withTrashed();
    }
    
    public function plant(){
        return $this->belongsTo('App\Models\Place','plant_id','id')->withTrashed();
    }
}
