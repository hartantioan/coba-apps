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
        'location',
        'qty'
    ];

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
        $cek = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $price = $cek->total_final / $cek->qty_final;
        }

        return $price;
    }

    public function priceDate($date){
        $price = 0;
        $cek = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
        if($cek){
            $price = $cek->price_final;
        }

        return $price;
    }

    public function requestSparepartDetail(){
        return $this->hasMany('App\Models\RequestSparepartDetail');
    }

    public function marketingOrderDetail(){
        return $this->hasMany('App\Models\MarketingOrderDetail','item_stock_id','id')->whereHas('marketingOrder',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function marketingOrderDeliveryDetailUnapproved(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryDetail','item_stock_id','id')->whereHas('marketingOrderDelivery',function($query){
            $query->whereIn('status',['1','2','3'])->whereDoesntHave('marketingOrderDeliveryProcess');
        });
    }

    public function totalQtyUnapproved(){
        $total = 0;
        foreach($this->marketingOrderDeliveryDetailUnapproved as $row){
            $total += $row->qty;
        }
        return $total;
    }

    public function totalUndeliveredItem(){
        $totalUndelivered = 0;
        $totalDelivered = 0;
        $totalOrder = 0;

        foreach($this->marketingOrderDetail as $row){
            $totalOrder += $row->qty;
            $totalDelivered += $row->marketingOrderDeliveryDetail()->whereHas('marketingOrderDelivery',function($query){
                $query->whereHas('marketingOrderDeliveryProcess');
            })->sum('qty');
        }

        $totalUndelivered = ($totalOrder - $totalDelivered) * $this->item->sell_convert;

        return $totalUndelivered;
    }

    public function totalUndeliveredItemSales(){
        $totalUndelivered = 0;
        $totalDelivered = 0;
        $totalOrder = 0;
        $totalReturn = 0;

        foreach($this->marketingOrderDetail as $row){
            $totalOrder += $row->qty;
            foreach($row->marketingOrderDeliveryDetail()->whereHas('marketingOrderDelivery',function($query){
                $query->whereHas('marketingOrderDeliveryProcess');
            })->get() as $rowdetail){
                $totalDelivered += $rowdetail->qty;
                foreach($rowdetail->marketingOrderReturnDetail as $rowreturn){
                    $totalReturn += $rowreturn->qty;
                }
            }
        }

        $totalUndelivered = $totalOrder - $totalDelivered + $totalReturn;

        return $totalUndelivered;
    }

    public function fullName(){
        return $this->place->code.' - '.$this->warehouse->name.($this->area()->exists() ? ' - '.$this->area->code : '');
    }
}
