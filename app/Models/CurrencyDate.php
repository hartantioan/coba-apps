<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CurrencyDate extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'currency_dates';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'currency_id',
        'currency_date',
        'currency_rate',
        'taken_from',
    ];

    public function currency(){
        return $this->hasMany('App\Models\Currency','currency_id','id');
    }
}
