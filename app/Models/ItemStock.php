<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function priceFgNow($date){
        $pricenow = 0;
        $cogs = ItemCogs::where('item_id',$this->item_id)->where('place_id',$this->place_id)->where('warehouse_id',$this->warehouse_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->where('date','<=',$date)->orderBy('date')->orderBy('id')->get();
        $total = 0;
        $qty = 0;
        foreach($cogs as $row){
            if($row->type == 'IN'){
                $qty += round($row->qty_in,3);
                $total += round($row->total_in,2);
            }elseif($row->type == 'OUT'){
                $qty -= round($row->qty_out,3);
                $total -= round($row->total_out,2);
            }
        }

        /* $data = DB::select("
            SELECT 
                IFNULL(SUM(ROUND(ic.qty_in,3)),0) AS qty_in,
                IFNULL(SUM(ROUND(ic.qty_out,3)),0) AS qty_out,
                IFNULL(SUM(ROUND(ic.total_in,2)),0) AS total_in,
                IFNULL(SUM(ROUND(ic.total_out,2)),0) AS total_out
            FROM item_cogs ic
            WHERE 
                ic.date <= :date 
                AND ic.item_id = :item_id
                AND ic.place_id = :place_id
                AND ic.warehouse_id = :warehouse_id
                AND ic.item_shading_id ".($this->item_shading_id ? "= ".$this->item_shading_id : "IS NULL")."
                AND ic.production_batch_id ".($this->production_batch_id ? "= ".$this->production_batch_id : "IS NULL")."
                AND ic.deleted_at IS NULL
        ", array(
            'date'              => $date,
            'item_id'           => $this->item_id,
            'place_id'          => $this->place_id,
            'warehouse_id'      => $this->warehouse_id,
        ));
        
        $qty = $data[0]->qty_in - $data[0]->qty_out;
        $total = $data[0]->total_in - $data[0]->total_out; */

        $pricenow = $qty > 0 ? $total / $qty : 0;
        return $pricenow;
    }

    public function stockByDate($date){
        $qty = 0;
        
        $data_in = DB::select("
                SELECT 
                    IFNULL(SUM(ROUND(ic.qty_in,3)),0) AS total_qty_in,
                    IFNULL(SUM(ROUND(ic.qty_out,3)),0) AS total_qty_out
                FROM item_cogs ic
                WHERE 
                    ic.date <= :date 
                    AND ic.item_id = :item_id
                    AND ic.place_id = :place_id
                    AND ic.warehouse_id = :warehouse_id
                    AND ic.item_shading_id ".($this->item_shading_id ? "= ".$this->item_shading_id : "IS NULL")."
                    AND ic.production_batch_id ".($this->production_batch_id ? "= ".$this->production_batch_id : "IS NULL")."
                    AND ic.area_id ".($this->area_id ? "= ".$this->area_id : "IS NULL")."
                    AND ic.deleted_at IS NULL
            ", array(
                'date'              => $date,
                'item_id'           => $this->item_id,
                'place_id'          => $this->place_id,
                'warehouse_id'      => $this->warehouse_id,
            ));

        $qty = round($data_in[0]->total_qty_in,3) - round($data_in[0]->total_qty_out,3);
        
        return $qty;
    }

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
            $price = $cek->qty_final > 0 || $cek->qty_final < 0 ? $cek->total_final / $cek->qty_final : 0;
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
        
        $modp = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess',function($query){
            $query->whereIn('status',['1','2','3']);
        })
        ->where('item_stock_id',$this->id)
        ->get();

        foreach($modp as $row){
            if(!$row->marketingOrderDeliveryProcess->isItemSent()){
                $balance -= round(($row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion),3);
            }
        }

        return $balance;
    }

    public function fullName(){
        return $this->place->code.' - '.$this->warehouse->name.($this->area()->exists() ? ' - '.$this->area->code : '');
    }
}
