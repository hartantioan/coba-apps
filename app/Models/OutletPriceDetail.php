<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OutletPriceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'outlet_price_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'outlet_price_id',
        'item_id',
        'price',
        'margin',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'final_price',
    ];

    public function outletPrice(){
        return $this->belongsTo('App\Models\OutletPrice','outlet_price_id','id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}