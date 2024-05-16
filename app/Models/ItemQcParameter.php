<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemQcParameter extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_qc_parameters';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'unit',
        'is_affect_qty',
        'item_id',
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }
}
