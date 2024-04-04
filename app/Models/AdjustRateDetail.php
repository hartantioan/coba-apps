<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AdjustRateDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'adjust_rate_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'adjust_rate_id',
        'lookable_type',
        'lookable_id',
        'nominal_fc',
        'nominal_rate',
        'nominal_rp',
        'nominal_new',
        'nominal',
    ];

    public function adjustRate()
    {
        return $this->belongsTo('App\Models\AdjustRate', 'adjust_rate_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
