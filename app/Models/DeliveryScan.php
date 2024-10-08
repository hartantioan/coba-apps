<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryScan extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'delivery_scans';

    protected $fillable = [
        'user_id',
        'lookable_type',
        'lookable_id',
        'post_date',
    ];

    public function user(){
        return $this->hasMany('App\Models\User');
    }

    public function lookable(){
        return $this->morphTo();
    }
}
