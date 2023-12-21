<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class OvertimeCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'overtime_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'place_id',
        'level_id',
        'nominal',
        'start_date',
        'end_date',
        'type',
        'status',
    ];
    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function level(){
        return $this->belongsTo('App\Models\Level','level_id','id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'Khusus',
          '2' => 'Normal',
          default => 'Invalid',
        };

        return $type;
    }
}
