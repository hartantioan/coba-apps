<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CapitalizationDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'capitalization_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'capitalization_id',
        'asset_id',
        'qty',
        'unit_id',
        'price',
        'total',
        'note'
    ];

    public function capitalization(){
        return $this->belongsTo('App\Models\Capitalization', 'capitalization_id', 'id')->withTrashed();
    }

    public function asset(){
        return $this->belongsTo('App\Models\Asset', 'asset_id', 'id')->withTrashed();
    }

    public function unit(){
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }
}
