<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'machines';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'note',
        'status'
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

    public function statusRaw(){
        $status = match ($this->status) {
          '1' => 'Active',
          '2' => 'Not Active',
          default => 'Invalid',
        };

        return $status;
    }

    // public function line(){
    //     return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    // }
}
