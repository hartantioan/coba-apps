<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AttendancePeriod extends Model
{
    
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attendance_periods';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'code',
        'name',
        'start_date',
        'end_date',
        'plant_id',
        'status',
    ];

    public function plant(){
        return $this->belongsTo('App\Models\Place','plant_id','id')->withTrashed();
    }

    public function getPunishment(){
        $array_punishment=[];
        $query_punishment = Punishment::where("place_id",$this->plant_id)
                            ->where("type","1")
                            ->get();

        foreach($query_punishment as $row_punishment){
            $array_punishment[]=$row_punishment->code;
        }
        return $array_punishment;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Closed</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
