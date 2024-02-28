<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Punishment extends Model
{

    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'punishments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'price',
        'minutes',
        'place_id',
        'type',
        'shift_id',
        'status',
    ];

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

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function shift(){
        return $this->belongsTo('App\Models\Shift','shift_id','id')->withTrashed();
    }

    public function type(){
        switch($this->type) {
            case '1':
                $type = 'Terlambat';
                break;
            case '2':
                $type = 'Tidak Check Masuk';
                break;
            default:
                $type = 'Tidak Check Pulang';
                break;
        }

        return $type;
    }
}
