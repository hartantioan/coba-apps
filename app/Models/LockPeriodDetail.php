<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LockPeriodDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'lock_period_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'lock_period_id',
        'user_id',
    ];

    public function lockPeriod()
    {
        return $this->belongsTo('App\Models\LockPeriod', 'lock_period_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
}
