<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class OutstandingAP extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'outstanding_aps';
    protected $primaryKey = 'id';
    protected $fillable = [
        'post_date',
        'total',
    ];

    public function outstandingApDetail()
    {
        return $this->hasMany('App\Models\OutstandingAPDetail');
    }
}