<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserAbsensiMesin extends Model
{
    use HasFactory,Notifiable;

    protected $table = 'b_master_absensi_user_mesin';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uid',
        'nik',
        'nama',
        'mesin_absensi',
        'user_at',
    ];
}
