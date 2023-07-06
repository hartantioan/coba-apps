<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class AssetUsage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'asset_usages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'user_id',
        'location',
        'ip_address',
        'info',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    
}
