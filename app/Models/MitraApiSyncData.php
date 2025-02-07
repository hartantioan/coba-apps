<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraApiSyncData extends Model{
    use HasFactory, Notifiable;

    protected $table      = 'mitra_api_sync_datas';
    protected $primaryKey = 'id';
    // protected $dates      = ['deleted_at'];
    protected $fillable   = [
        'mitra_id',
        'lookable_type',
        'lookable_id',
        'operation',
        'operation_params',
        'payload',
        'status',
        'attempts',
        'api_response',
    ];
    
    protected $casts = [
        'payload' => 'json',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function mitra(){
        return $this->belongsTo('App\Models\User', 'mitra_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
            '0' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Pending</span>',
            '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Success</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

}
