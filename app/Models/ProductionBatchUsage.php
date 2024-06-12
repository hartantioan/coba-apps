<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionBatchUsage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_batch_usages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'item_id',
        'lookable_type',
        'lookable_id',
        'qty'
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id');
    }
}