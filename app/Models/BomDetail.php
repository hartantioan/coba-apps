<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'bom_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'bom_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'nominal',
        'total',
        'description'
    ];

    public function bom(){
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id');
    }

    public function lookable(){
        return $this->morphTo();
    }

}
