<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'quality_controls';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_scale_id',
        'name',
        'nominal',
        'unit',
        'is_affect_qty',
        'note',
    ];

    public function goodScale(){
        return $this->belongsTo('App\Models\GoodScale', 'good_scale_id', 'id')->withTrashed();
    }
}
