<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class BomMaterial extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'bom_materials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'bom_id',
        'item_id',
        'qty',
        'description'
    ];

    public function bom(){
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id');
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id');
    }

}
