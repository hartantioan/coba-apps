<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BomCalculatorDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'bom_calculator_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'bom_calculator_id',
        'lookable_type',
        'lookable_id',
        'name',
        'qty',
        'price',
        'total',
        'group',
    ];

    public function bomCalculator()
    {
        return $this->belongsTo('App\Models\BomCalculator', 'bom_calculator_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }
}
