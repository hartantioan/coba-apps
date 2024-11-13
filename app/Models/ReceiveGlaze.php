<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ReceiveGlaze extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'receive_glazes';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'place_id',
        'line_id',
        'document',
        'note',
        'item_id',
        'qty',
        'to_place_id',
        'to_warehouse_id',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
        'post_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function receiveGlazeDetail()
    {
        return $this->hasMany('App\Models\ReceiveGlazeDetail');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function placeTo(){
        return $this->belongsTo('App\Models\Place','place_to','id')->withTrashed();
    }

    public function warehouseTo(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_to','id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }
}
