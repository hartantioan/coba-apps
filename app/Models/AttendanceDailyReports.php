<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendanceDailyReports extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendance_daily_reports';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'user_id',
        'shift_id',
        'period_id',
        'date',
        'masuk',
        'pulang',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id');
    }

    public function rawStatus(){
        $status = match ($this->status) {
          '1' => 'Tepat Waktu',
          '2' => 'Tidak Check Masuk',
          '3' => 'Tidak Check Pulang',
          '4' => 'Terlambat Saja',
          '5' => 'Terlambat Tidak Check Pulang',
          '6' => 'Absen',
          '7' => 'Tidak Ada Jadwal',
          '8' => 'Ada Ijin',
          '9' => 'Cuti Melahirkan',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="green darken-4 medium-small white-text padding-3">Tepat Waktu</span>',
          '2' => '<span class="deep-purple medium-small white-text padding-3">Tidak Check Masuk</span>',
          '3' => '<span class="amber accent-4 medium-small white-text padding-3">Tidak Check Pulang</span>',
          '4' => '<span class="light-blue darken-4 medium-small white-text padding-3">Terlambat Saja</span>',
          '5' => '<span class="red darken-3 medium-small white-text padding-3">Terlambat Tidak Check Pulang</span>',
          '6' => '<span class="red accent-4 medium-small white-text padding-3">Absen</span>',
          '7' => '<span class="grey darken-3 medium-small white-text padding-3">Tidak Ada Jadwal</span>',
          '8' => '<span class="light-green accent-4 medium-small white-text padding-3">Ada Ijin</span>',
          '9' => '<span class=" pink accent-1 medium-small white-text padding-3">Cuti Melahirkan</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
