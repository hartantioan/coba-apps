<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ToleranceScale extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'tolerance_scales';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'item_id',
        'percentage',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
