<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStockNew extends Model
{
    use HasFactory;

    protected $table = 'item_stocks_new';
    protected $fillable = ['item_id', 'qty'];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function priceDate($date){
        $price = 0;
        $cek = ItemMove::where('item_id',$this->item_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $price = $cek->qty_final > 0 || $cek->qty_final < 0 ? $cek->total_final / $cek->qty_final : 0;
        }

        return $price;
    }
}
