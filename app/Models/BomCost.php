<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class BomCost extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'bom_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'bom_id',
        'coa_id',
        'description',
        'nominal'
    ];

    public function bom(){
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id');
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id');
    }

}
