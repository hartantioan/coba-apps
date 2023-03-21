<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserWarehouse extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_warehouses';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'warehouse_id',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id');
    }
}