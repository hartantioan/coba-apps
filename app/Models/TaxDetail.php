<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxDetail extends Model
{   
    use HasFactory, SoftDeletes;
    protected $table = 'tax_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'tax_id',
        'item',
        'price',
        'qty',
        'subtotal',
        'discount',
        'total',
        'tax',
        'nominal_ppnbm',
        'ppnbm',
    ];
}
