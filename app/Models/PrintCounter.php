<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PrintCounter extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'print_counters';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'lookable_type',
        'lookable_id',
        'user_id',
    ];
}
