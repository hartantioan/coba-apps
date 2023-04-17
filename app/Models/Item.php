<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'items';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'item_group_id',
        'uom_unit',
        'buy_unit',
        'buy_convert',
        'sell_unit',
        'sell_convert',
        'is_inventory_item',
        'is_sales_item',
        'is_purchase_item',
        'is_service',
        'status'
    ];

    public function itemGroup(){
        return $this->belongsTo('App\Models\ItemGroup', 'item_group_id', 'id')->withTrashed();
    }

    public function itemWarehouse(){
        return $this->hasMany('App\Models\ItemWarehouse');
    }

    public function warehouses(){
        $arr = [];

        foreach($this->itemWarehouse as $row){
            $arr[] = $row->warehouse->name;
        }

        return implode(', ',$arr);
    }

    public function uomUnit(){
        return $this->belongsTo('App\Models\Unit', 'uom_unit', 'id')->withTrashed();
    }

    public function buyUnit(){
        return $this->belongsTo('App\Models\Unit', 'buy_unit', 'id')->withTrashed();
    }
    
    public function sellUnit(){
        return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function currentCogs($dataplaces){
        $arrPrice = [];
        foreach($dataplaces as $row){
            $price = ItemCogs::where('item_id',$this->id)->where('place_id',intval($row))->orderByDesc('id')->first();
            if($price){
                $arrPrice[] = [
                    'description'   => $price->company->name.' - '.date('d/m/y',strtotime($price->date)),
                    'price'         => number_format($price->price_final,2,',','.'),
                ];
            }
        }
        
        return $arrPrice;
    }
}
