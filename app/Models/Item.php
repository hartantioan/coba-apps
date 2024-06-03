<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class Item extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'items';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'other_name',
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
        'is_production',
        'note',
        'min_stock',
        'max_stock',
        'status',
        'is_quality_check',
        'is_hide_supplier',
        'type_id',
        'size_id',
        'variety_id',
        'pattern_id',
        'pallet_id',
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

    public function pallet(){
        return $this->belongsTo('App\Models\Pallet', 'pallet_id', 'id')->withTrashed();
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

    public function qualityCheck(){
        $check = match ($this->is_quality_check) {
          '1' => 'Ya',
          default => 'Tidak',
        };

        return $check;
    }

    public function hideSupplier(){
        $hide = match ($this->is_hide_supplier) {
          '1' => 'Ya',
          default => 'Tidak',
        };

        return $hide;
    }

    public function currentCogs($dataplaces){
        $arrPrice = [];
            
        $price = ItemCogs::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->orderByDesc('date')->orderByDesc('id')->first();
        if($price){
            $arrPrice[] = [
                'description'   => $price->company->name.' - '.date('d/m/Y',strtotime($price->date)),
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
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
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
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
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
            $pricenow = ($price->total_final / $price->qty_final) / $this->production_convert;
        }
        
        return $pricenow;
    }

    public function currentStock($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'                => $detail->id,
                'warehouse'         => $detail->place->code.' - '.$detail->warehouse->name.' - '.($detail->area()->exists() ? $detail->area->name : ''),
                'name'              => $detail->place->code.' Gudang: '.$detail->warehouse->name.($detail->area()->exists() ? ' Area: '.$detail->area->name : ''),
                'warehouse_id'      => $detail->warehouse_id,
                'place_id'          => $detail->place_id,
                'area_id'           => $detail->area()->exists() ? $detail->area_id : '',
                'area'              => $detail->area()->exists() ? $detail->area->name : '',
                'item_shading_id'   => $detail->itemShading()->exists() ? $detail->item_shading_id : '',
                'shading'           => $detail->itemShading()->exists() ? $detail->itemShading->code : '',
                'qty'               => CustomHelper::formatConditionalQty($detail->qty).' '.$this->uomUnit->code,
                'qty_raw'           => CustomHelper::formatConditionalQty($detail->qty),
            ];
        }
        
        return $arrData;
    }

    public function getOutstandingIssueRequest(){
        $qty = 0;
        foreach($this->goodIssueRequestDetail()->whereHas('goodIssueRequest',function($query){
            $query->where('status','2');
        })->get() as $row){
            $qty += $row->balanceGi();
        }
        return $qty;
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
                'qty'           => CustomHelper::formatConditionalQty($detail->qty).' '.$this->uomUnit->code,
                'qty_raw'       => CustomHelper::formatConditionalQty($detail->qty),
            ];
        }
        
        return $arrData;
    }

    public function currentStockSales($dataplaces,$datawarehouses){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->whereIn('place_id',$dataplaces)->whereIn('warehouse_id',$datawarehouses)->get();
        foreach($data as $detail){
            /* $qtyUnapproved = $detail->totalQtyUnapproved(); */
            $qtyUnapproved = 0;
            $arrData[] = [
                'id'            => $detail->id,
                'warehouse'     => $detail->place->code.' - '.$detail->warehouse->name.' - '.($detail->area()->exists() ? $detail->area->name : '').' - '.($detail->itemShading()->exists() ? $detail->itemShading->code : ''),
                'warehouse_id'  => $detail->warehouse_id,
                'area'          => $detail->area()->exists() ? $detail->area->name : '',
                'area_id'       => $detail->area_id ? $detail->area_id : '',
                'place_id'      => $detail->place_id,
                'qty'           => CustomHelper::formatConditionalQty($detail->qty - $qtyUnapproved).' '.$this->uomUnit->code,
                'qty_raw'       => CustomHelper::formatConditionalQty($detail->qty - $qtyUnapproved),
                'qty_commited'  => CustomHelper::formatConditionalQty($detail->totalUndeliveredItemSales()),
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
                'warehouse'     => 'Plant : '.$detail->place->code.' - Gudang : '.$detail->warehouse->name.' - Area : '.($detail->area()->exists() ? $detail->area->name : '').' - Shading : '.($detail->itemShading()->exists() ? $detail->itemShading->code : '-'),
                'warehouse_id'  => $detail->warehouse_id,
                'area'          => $detail->area()->exists() ? $detail->area->name : '',
                'area_id'       => $detail->area_id ? $detail->area_id : '',
                'place_id'      => $detail->place_id,
                'qty'           => CustomHelper::formatConditionalQty($detail->qty).' '.$this->uomUnit->code,
                'qty_raw'       => CustomHelper::formatConditionalQty($detail->qty),
            ];
        }
        
        return $arrData;
    }

    public function currentStockPerPlace($place_id){
        $arrData = [];

        $data = ItemStock::where('item_id',$this->id)->where('place_id',$place_id)->get();
        foreach($data as $detail){
            $arrData[] = [
                'id'                    => $detail->id,
                'warehouse'             => $detail->place->code.' - '.$detail->warehouse->name.($detail->area()->exists() ? ' - '.$detail->area->code : ''),
                'qty'                   => CustomHelper::formatConditionalQty($detail->qty).' '.$this->uomUnit->code,
                'qty_raw'               => CustomHelper::formatConditionalQty($detail->qty),
            ];
        }
        
        return $arrData;
    }

    public function itemStock()
    {
        return $this->hasMany('App\Models\ItemStock','item_id','id');
    }

    public function itemCogs()
    {
        return $this->hasMany('App\Models\ItemCogs','item_id','id');
    }

    public function itemBuffer()
    {
        return $this->hasMany('App\Models\ItemBuffer','item_id','id');
    }

    public function itemQcParameter()
    {
        return $this->hasMany('App\Models\ItemQcParameter','item_id','id');
    }

    public function fgGroup()
    {
        return $this->hasMany('App\Models\FgGroup','parent_id','id');
    }

    public function benchmarkPrice()
    {
        return $this->hasMany('App\Models\BenchmarkPrice','item_id','id')->where('status','1');
    }

    public function lastBenchmarkPricePlant($place){
        $price = 0;
        $bp = $this->benchmarkPrice()->where('place_id',$place)->orderByDesc('id')->first();
        if($bp){
            $price = $bp->price;
        }
        return $price;
    }

    public function getStockAll(){
        $total = $this->itemStock()->sum('qty');

        return $total;
    }

    public function itemShading()
    {
        return $this->hasMany('App\Models\ItemShading','item_id','id');
    }

    public function itemUnit()
    {
        return $this->hasMany('App\Models\ItemUnit','item_id','id');
    }

    public function listShading(){
        $arr = [];
        foreach($this->itemShading as $row){
            $arr[] = $row->code;
        }
        return implode('|',$arr);
    }

    public function arrShading(){
        $arr = [];
        foreach($this->itemShading as $row){
            $arr[] = [
                'id'    => $row->id,
                'code'  => $row->code,
            ];
        }
        return $arr;
    }

    public function arrBuyUnits(){
        $arr = [];
        foreach($this->itemUnit()->whereNotNull('is_buy_unit')->orderByDesc('is_default')->get() as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->unit->code,
                'conversion'    => $row->conversion,
            ];
        }
        return $arr;
    }

    public function arrSellUnits(){
        $arr = [];
        foreach($this->itemUnit()->whereNotNull('is_sell_unit')->orderByDesc('is_default')->get() as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->unit->code,
                'conversion'    => $row->conversion,
            ];
        }
        return $arr;
    }

    public function outletPriceDetail()
    {
        return $this->hasMany('App\Models\OutletPriceDetail','item_id','id')->whereHas('outletPrice',function($query){
            $query->where('status','1');
        });
    }

    public function goodIssueRequestDetail()
    {
        return $this->hasMany('App\Models\GoodIssueRequestDetail','item_id','id')->whereHas('goodIssueRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function bomPlace($place_id)
    {
        return $this->hasMany('App\Models\Bom','item_id','id')->where('place_id',intval($place_id))->where('status','1')->orderByDesc('id');
    }

    public function bom()
    {
        return $this->hasMany('App\Models\Bom','item_id','id')->where('status','1');
    }

    public function listBom(){
        $firstBom = $this->bom()->latest()->first();
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
                    'qty'       => CustomHelper::formatConditionalQty($qtyNeeded).' '.$row->lookable->uomUnit->code,
                    'stock'     => CustomHelper::formatConditionalQty($stock).' '.$row->lookable->uomUnit->code,
                    'stock_raw' => CustomHelper::formatConditionalQty($stock),
                    'qty_raw'   => CustomHelper::formatConditionalQty($qtyNeeded),
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

    public function getStockArrayPlace($arr){
        $total = $this->itemStock()->whereIn('place_id',$arr)->sum('qty');

        return $total;
    }

    public function getStockWarehouse($warehouse_id){
        $total = $this->itemStock()->where('warehouse_id',$warehouse_id)->sum('qty');

        return $total;
    }

    public function getStockPlaceWarehouse($place_id,$warehouse_id){
        $total = $this->itemStock()->where('place_id',$place_id)->where('warehouse_id',$warehouse_id)->sum('qty');

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

    public function materialRequestDetail(){
        return $this->hasMany('App\Models\MaterialRequestDetail','item_id','id')->withTrashed();
    }

    public function purchaseRequestDetail(){
        return $this->hasMany('App\Models\PurchaseRequestDetail','item_id','id')->withTrashed();
    }

    public function purchaseOrderDetail(){
        return $this->hasMany('App\Models\PurchaseOrderDetail','item_id','id')->withTrashed();
    }

    public function goodReceiptDetail(){
        return $this->hasMany('App\Models\GoodReceiptDetail','item_id','id')->withTrashed();
    }

    public function landedCostDetail(){
        return $this->hasMany('App\Models\LandedCostDetail','item_id','id')->withTrashed();
    }

    public function activeMaterialRequestDetail(){
        return $this->hasMany('App\Models\MaterialRequestDetail','item_id','id')->whereHas('materialRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function activePurchaseRequestDetail(){
        return $this->hasMany('App\Models\PurchaseRequestDetail','item_id','id')->whereHas('purchaseRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function activePurchaseOrderDetail(){
        return $this->hasMany('App\Models\PurchaseOrderDetail','item_id','id')->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function activeGoodReceiptDetail(){
        return $this->hasMany('App\Models\GoodReceiptDetail','item_id','id')->whereHas('goodReceipt',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function activeGoodIssueRequestDetail(){
        return $this->hasMany('App\Models\GoodIssueRequestDetail','item_id','id')->whereHas('goodIssueRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function activeLandedCostDetail(){
        return $this->hasMany('App\Models\LandedCostDetail','item_id','id')->whereHas('landedCost',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->materialRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->purchaseRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->purchaseOrderDetail()->exists()){
            $hasRelation = true;
        }

        if($this->goodReceiptDetail()->exists()){
            $hasRelation = true;
        }

        if($this->goodIssueRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->landedCostDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function hasActiveChildDocument(){
        $hasRelation = false;

        if($this->activeMaterialRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->activePurchaseRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->activePurchaseOrderDetail()->exists()){
            $hasRelation = true;
        }

        if($this->activeGoodReceiptDetail()->exists()){
            $hasRelation = true;
        }

        if($this->activeGoodIssueRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->activeLandedCostDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }
}
