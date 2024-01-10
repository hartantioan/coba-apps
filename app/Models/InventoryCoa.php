<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InventoryCoa extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_coas';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'coa_id',
        'type',
        'status',
    ];

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
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

    public function type(){
        switch($this->type) {
            case '1':
                $type = 'Debit';
                break;
            case '2':
                $type = 'Kredit';
                break;
            default:
                $type = 'Invalid';
                break;
        }

        return $type;
    }
}