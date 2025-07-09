<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StoreItemPriceList extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'store_item_pricelists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'user_id',
        'item_id',
        'start_date',
        'end_date',
        'price',
        'discount',
        'qty_discount',
        'sell_price',
        'status',
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}
