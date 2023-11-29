<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserSpecial extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_specials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'limit',
        'punishment_id',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function punishment(){
        return $this->belongsTo('App\Models\Punishment','punishment_id','id');
    }
    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }
}
