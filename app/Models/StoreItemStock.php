<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreItemStock extends Model
{
    use HasFactory;

    protected $table = 'store_item_stocks';
    protected $fillable = ['item_id', 'qty','item_stock_new_id'];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id');
    }

    public function itemStockNew(){
        return $this->belongsTo('App\Models\ItemStockNew','item_stock_new_id','id');
    }

}
