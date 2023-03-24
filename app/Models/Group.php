<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'note',
        'type',
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

    public function type(){
        $type = match ($this->type) {
          '1' => 'Pegawai',
          '2' => 'Customer',
          '3' => 'Supplier',
          '4' => 'Expedisi',
          default => 'Invalid',
        };

        return $type;
    }

    public function user(){
        return $this->hasMany('App\Models\User');
    }
}
