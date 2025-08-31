<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreItemMove extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_item_moves';

    protected $fillable = [
        'lookable_type',
        'lookable_id',
        'item_id',
        'qty_in',
        'price_in',
        'total_in',
        'qty_out',
        'price_out',
        'total_out',
        'qty_final',
        'price_final',
        'total_final',
        'date',
        'type',

        'lookable_detail_type',
        'lookable_detail_id'
    ];

    protected $dates = ['deleted_at', 'date'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function infoFg(){
        $qty = 0;
        $total = 0;
        $arr = [];
        $cogs = StoreItemMove::where('item_id',$this->item_id)->where('date','<=',$this->date)->orderBy('date')->orderBy('id')->get();
        foreach($cogs as $row){
            if($row->type == 1){
                $qty += round($row->qty_in,3);
                $total += round($row->total_in,2);
            }elseif($row->type == 2){
                $qty -= round($row->qty_out,3);
                $total -= round($row->total_out,2);
            }
        }
        $arr = [
            'qty'   => $qty,
            'total' => $total,
        ];
        return $arr;
    }

    public function lookable()
    {
        return $this->morphTo();
    }
}
