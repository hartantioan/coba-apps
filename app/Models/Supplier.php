<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'suppliers';

    protected $fillable = [
        'code',
        'name',
        'user_id',
        'no_telp',
        'address',
        'group_id',
        'total',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function deliveryReceives()
    {
        return $this->hasMany(DeliveryReceive::class, 'account_id', 'id');
    }

    public function item()
    {
        return $this->hasMany(Item::class, 'supplier_id', 'id');
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
