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
        'tolerance_gr',
        'is_inventory_item',
        'is_sales_item',
        'is_purchase_item',
        'is_service',
        'note',
        'status'
    ];

    public function itemGroup(){
        return $this->belongsTo('App\Models\ItemGroup', 'item_group_id', 'id')->withTrashed();
    }

    public function warehouses(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = $row->warehouse->name;
        }

        return implode(', ',$arr);
    }

    public function arrWarehouse(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = $row->warehouse_id;
        }

        return $arr;
    }

    public function warehouseList(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = [
                'id'    => $row->warehouse_id,
                'name'  => $row->warehouse->name,
            ];
        }

        return $arr;
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
            
        $price = ItemCogs::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->orderByDesc('date')->orderByDesc('id')->first();
        if($price){
            $arrPrice[] = [
                'description'   => $price->company->name.' - '.date('d/m/y',strtotime($price->date)),
                'price'         => number_format($price->price_final,2,',','.'),
            ];
        }
        
        return $arrPrice;
    }

    public function oldPrices($dataplaces){
        $arrPrice = [];
        $po = PurchaseOrder::whereHas('purchaseOrderDetail', function($query) use ($dataplaces){
                $query->where('item_id',$this->id)->whereIn('place_id',$dataplaces);
            })->whereIn('status',['2','3'])->orderByDesc('post_date')->get();
        
        foreach($po as $row){
            foreach($row->purchaseOrderDetail as $rowdetail){
                $arrPrice[] = [
                    'purchase_code' => $row->code,
                    'supplier_id'   => $row->account_id,
                    'supplier_name' => $row->supplier->name,
                    'price'         => number_format($rowdetail->price,2,',','.'),
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                ];
            }
        }
        
        return $arrPrice;
    }

    public function priceNow($place_id,$date){
        $pricenow = 0;
        $price = ItemCogs::where('item_id',$this->id)->where('place_id',$place_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
        if($price){
            $pricenow = $price->price_final;
        }
        
        return $pricenow;
    }

    public function currentStock($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->name.' - '.$detail->warehouse->name,
                'warehouse_id'  => $detail->warehouse_id,
                'place_id'      => $detail->place_id,
                'qty'           => number_format($detail->qty,3,',','.').' '.$this->uomUnit->code,
                'qty_raw'       => number_format($detail->qty,3,',','.'),
            ];
        }
        
        return $arrData;
    }

    public function currentStockPerPlace($place_id){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->where('place_id',$place_id)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->name.' - '.$detail->warehouse->name,
                'qty'           => number_format($detail->qty,3,',','.').' '.$this->uomUnit->code,
                'qty_raw'       => number_format($detail->qty,3,',','.'),
            ];
        }
        
        return $arrData;
    }

    public function itemStock()
    {
        return $this->hasMany('App\Models\ItemStock','item_id','id');
    }

    public function getStockPlace($place_id){
        $total = $this->itemStock()->where('place_id',$place_id)->sum('qty');

        return $total;
    }
}
