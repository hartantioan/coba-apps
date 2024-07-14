<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class WeightHistory extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'weight_histories';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'place_id',
        'nominal',
        'rawdata',
    ];
}