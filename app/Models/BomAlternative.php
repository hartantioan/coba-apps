<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BomAlternative extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'bom_alternatives';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'bom_id',
        'name',
        'is_default'
    ];

    public function bom(){
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id');
    }

    public function bomDetail(){
        return $this->hasMany('App\Models\BomDetail');
    }
}
