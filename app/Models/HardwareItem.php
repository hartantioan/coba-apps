<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class HardwareItem extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'hardware_items';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'item_id',
        'user_id',
        'hardware_item_group_id',
        'detail1',
        'detail2',
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

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function hardwareItemGroup(){
        return $this->belongsTo('App\Models\HardwareItemGroup', 'hardware_item_group_id', 'id')->withTrashed();
    }

    public function hardwareItemDetail(){
        return $this->hasMany('App\Models\HardwareItemDetail');
    }

    public function receptionHardwareItemsUsage(){
        return $this->hasMany('App\Models\ReceptionHardwareItemsUsage','hardware_item_id','id')->whereIn('status',['1','2','3']);
    }
}
