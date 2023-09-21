<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PresenceReport extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'presence_report';
    protected $primaryKey = 'id';

    protected $fillable = [    
        'user_id',
        'period_id',
        'nama_shift',
        'date',
        'late_status',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    
}
