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
        'pallet_unit',
        'pallet_convert',
        'production_unit',
        'production_convert',
        'tolerance_gr',
        'is_inventory_item',
        'is_sales_item',
        'is_purchase_item',
        'is_service',
        'note',
        'min_stock',
        'max_stock',
        'status',
        'type_id',
        'size_id',
        'variety_id',
        'pattern_id',
        'color_id',
        'grade_id',
        'brand_id',
    ];

    public function itemGroup(){
        return $this->belongsTo('App\Models\ItemGroup', 'item_group_id', 'id')->withTrashed();
    }

    public function type(){
        return $this->belongsTo('App\Models\Type', 'type_id', 'id')->withTrashed();
    }

    public function size(){
        return $this->belongsTo('App\Models\Size', 'size_id', 'id')->withTrashed();
    }

    public function variety(){
        return $this->belongsTo('App\Models\Variety', 'variety_id', 'id')->withTrashed();
    }

    public function pattern(){
        return $this->belongsTo('App\Models\Pattern', 'pattern_id', 'id')->withTrashed();
    }

    public function color(){
        return $this->belongsTo('App\Models\Color', 'color_id', 'id')->withTrashed();
    }

    public function grade(){
        return $this->belongsTo('App\Models\Grade', 'grade_id', 'id')->withTrashed();
    }

    public function brand(){
        return $this->belongsTo('App\Models\Brand', 'brand_id', 'id')->withTrashed();
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
                'code'  => $row->warehouse->code,
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

    public function palletUnit(){
        return $this->belongsTo('App\Models\Pallet', 'pallet_unit', 'id')->withTrashed();
    }
    
    public function sellUnit(){
        return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed();
    }

    public function productionUnit(){
        return $this->belongsTo('App\Models\Unit', 'production_unit', 'id')->withTrashed();
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

    public function oldSalePrices($dataplaces){
        $arrPrice = [];
        $po = MarketingOrder::whereHas('marketingOrderDetail', function($query) use ($dataplaces){
                $query->where('item_id',$this->id)->whereIn('place_id',$dataplaces);
            })->whereIn('status',['2','3'])->orderByDesc('post_date')->get();
        
        foreach($po as $row){
            foreach($row->marketingOrderDetail as $rowdetail){
                $arrPrice[] = [
                    'sales_code'    => $row->code,
                    'customer_id'   => $row->account_id,
                    'customer_name' => $row->account->name,
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

    public function priceNowProduction($place_id,$date){
        $pricenow = 0;
        $price = ItemCogs::where('item_id',$this->id)->where('place_id',$place_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
        if($price){
            $pricenow = $price->price_final / $this->production_convert;
        }
        
        return $pricenow;
    }

    public function currentStock($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->name.' - '.$detail->warehouse->name.' - '.($detail->area()->exists() ? $detail->area->name : ''),
                'warehouse_id'  => $detail->warehouse_id,
                'place_id'      => $detail->place_id,
                'qty'           => number_format($detail->qty,3,',','.').' '.$this->uomUnit->code,
                'qty_raw'       => number_format($detail->qty,3,',','.'),
            ];
        }
        
        return $arrData;
    }

    public function currentStockPurchase($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->code.' - '.$detail->warehouse->name,
                'warehouse_id'  => $detail->warehouse_id,
                'place_id'      => $detail->place_id,
                'qty'           => number_format($detail->qty / $detail->item->buy_convert,3,',','.').' '.$this->sellUnit->code,
                'qty_raw'       => number_format($detail->qty / $detail->item->buy_convert,3,',','.'),
            ];
        }
        
        return $arrData;
    }

    public function currentStockPurchasePlaceWarehouse($place,$warehouse){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->where('place_id',$place)->where('warehouse_id',$warehouse)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->code.' - '.$detail->warehouse->name,
                'warehouse_id'  => $detail->warehouse_id,
                'place_id'      => $detail->place_id,
                'qty'           => number_format($detail->qty / $detail->item->buy_convert,3,',','.').' '.$this->sellUnit->code,
                'qty_raw'       => number_format($detail->qty / $detail->item->buy_convert,3,',','.'),
                'qty_rawfull'   => $detail->qty / $detail->item->buy_convert,
            ];
        }
        
        return $arrData;
    }

    public function currentStockSales($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            $qtyUnapproved = $detail->totalQtyUnapproved();
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->name.' - '.$detail->warehouse->name,
                'warehouse_id'  => $detail->warehouse_id,
                'area'          => $detail->area()->exists() ? $detail->area->name : '',
                'area_id'       => $detail->area_id ? $detail->area_id : '',
                'place_id'      => $detail->place_id,
                'qty'           => number_format(($detail->qty / $detail->item->sell_convert) - $qtyUnapproved,3,',','.').' '.$this->uomUnit->code,
                'qty_raw'       => number_format(($detail->qty / $detail->item->sell_convert) - $qtyUnapproved,3,',','.'),
                'qty_commited'  => number_format($detail->totalUndeliveredItemSales(),3,',','.'),
            ];
        }
        
        return $arrData;
    }

    public function currentStockPlaceWarehouse($place,$warehouse){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->where('place_id',$place)->where('warehouse_id',$warehouse)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->code.' - '.$detail->warehouse->name.' - '.($detail->area()->exists() ? $detail->area->name : ''),
                'warehouse_id'  => $detail->warehouse_id,
                'area'          => $detail->area()->exists() ? $detail->area->name : '',
                'area_id'       => $detail->area_id ? $detail->area_id : '',
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

    public function itemShading()
    {
        return $this->hasMany('App\Models\ItemShading','item_id','id');
    }

    public function listShading(){
        $arr = [];
        foreach($this->itemShading as $row){
            $arr[] = $row->code;
        }
        return implode('|',$arr);
    }

    public function outletPriceDetail()
    {
        return $this->hasMany('App\Models\OutletPriceDetail','item_id','id')->whereHas('outletPrice',function($query){
            $query->where('status','1');
        });
    }

    public function bomPlace($place_id)
    {
        return $this->hasMany('App\Models\Bom','item_id','id')->where('place_id',intval($place_id))->where('status','1');
    }

    public function bom()
    {
        return $this->hasMany('App\Models\Bom','item_id','id')->where('status','1');
    }

    public function bomDetail()
    {
        return $this->hasMany('App\Models\BomDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('bom',function($query){
            $query->where('status','1');
        });
    }

    public function arrRawStock($place_id,$qty){
        $arr = [];
        $bom = $this->bomPlace($place_id)->orderByDesc('id')->first();
        if($bom){
            $bobot = $qty / $bom->qty_output;
            foreach($bom->bomDetail()->where('lookable_type','items')->get() as $key => $row){
                $stock = $row->lookable->getStockPlace($place_id);
                $qtyNeeded = $row->qty * $bobot;
                $status = '<span style="font-weight:800;color:green;">Cukup</span>';
                if($qtyNeeded > $stock){
                    $status = '<span style="font-weight:800;color:red;">Tidak Cukup</span>';
                }

                $arr[] = [
                    'item_id'   => $row->lookable_id,
                    'item_name' => $row->lookable->name,
                    'qty'       => number_format($qtyNeeded,3,',','.').' '.$row->lookable->uomUnit->code,
                    'stock'     => number_format($stock,3,',','.').' '.$row->lookable->uomUnit->code,
                    'stock_raw' => number_format($stock,3,',','.'),
                    'qty_raw'   => number_format($qtyNeeded,3,',','.'),
                    'status'    => $status,
                    'unit'      => $row->lookable->uomUnit->code,
                    'not_enough'=> $qtyNeeded > $stock ? '1':'',
                ];
            }
        }

        return $arr;
    }

    public function listOutletPrice()
    {
        $arr = [];

        foreach($this->outletPriceDetail as $detail){
            $arr[] = [
                'id'                    => $detail->id,
                'account_id'            => $detail->outletPrice->account_id,
                'outlet_id'             => $detail->OutletPrice->outlet_id,
                'date'                  => $detail->outletPrice->date,
                'price'                 => number_format($detail->price,2,',','.'),
                'margin'                => number_format($detail->margin,2,',','.'),
                'percent_discount_1'    => number_format($detail->percent_discount_1,2,',','.'),
                'percent_discount_2'    => number_format($detail->percent_discount_2,2,',','.'),
                'discount_3'            => number_format($detail->discount_3,2,',','.'),
                'final_price'           => number_format($detail->final_price,2,',','.'),
            ];
        }

        if(count($arr) > 0){
            $collection = collect($arr)->sortByDesc('date')->sortByDesc('id');

            $arr = $collection->values()->all();
        }

        return $arr;
    }

    public function getStockPlace($place_id){
        $total = $this->itemStock()->where('place_id',$place_id)->sum('qty');

        return $total;
    }

    public function getStockWarehousePlaceArea($place_id){
        $arr = [];

        $data = $this->itemStock()->whereIn('warehouse_id',$this->arrWarehouse())->where('place_id',$place_id)->get();

        foreach($data as $row){
            $area = $row->area()->exists() ? $row->area->code : '';
            $warehouse = $row->warehouse->name;
            $arr[] = $area.' - '.$warehouse;
        }

        return implode(', ',$arr);
    }
}
