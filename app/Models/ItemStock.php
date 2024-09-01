<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'item_stocks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'place_id',
        'warehouse_id',
        'area_id',
        'item_id',
        'item_shading_id',
        'production_batch_id',
        'location',
        'qty'
    ];

    public function productionBatch(){
        return $this->belongsTo('App\Models\ProductionBatch', 'production_batch_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }


    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function area(){
        return $this->belongsTo('App\Models\Area','area_id','id')->withTrashed();
    }

    public function valueNow(){
        $totalNow = 0;
        $cek = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $totalNow = $cek->total_final;
        }

        return $totalNow;
    }

    public function priceNow(){
        $price = 0;
        $cek = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $price = $cek->qty_final > 0 || $cek->qty_final < 0 ? round($cek->total_final / $cek->qty_final,6) : 0;
        }

        return $price;
    }

    public function priceDate($date){
        $price = 0;
        $cek = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->whereDate('date','<=',$date)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $price = $cek->qty_final > 0 || $cek->qty_final < 0 ? round($cek->total_final / $cek->qty_final,6) : 0;
        }

        return $price;
    }

    public function requestSparepartDetail(){
        return $this->hasMany('App\Models\RequestSparepartDetail');
    }

    public function marketingOrderDeliveryStock(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryStock','item_stock_id','id')->whereHas('marketingOrderDeliveryDetail',function($query){
            $query->whereHas('marketingOrderDelivery',function($query){
                $query->whereIn('status',['1','2','3']);
            });
        });
    }

    public function balanceWithUnsent(){
        $balance = $this->qty;
        
        return $balance;
    }

    public function fullName(){
        return $this->place->code.' - '.$this->warehouse->name.($this->area()->exists() ? ' - '.$this->area->code : '');
    }
}
