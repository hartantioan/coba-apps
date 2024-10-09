<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDeliveryProcessDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_process_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_delivery_process_id',
        'marketing_order_delivery_detail_id',
        'item_stock_id',
        'qty',
        'total',
    ];

    public function marketingOrderDeliveryDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryDetail', 'marketing_order_delivery_detail_id', 'id')->withTrashed();
    }

    public function productionBatchUsage(){
        return $this->hasMany('App\Models\ProductionBatchUsage','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function marketingOrderDeliveryProcess()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryProcess', 'marketing_order_delivery_process_id', 'id')->withTrashed();
    }

    public function realPriceAfterGlobalDiscount(){
        return $this->marketingOrderDeliveryDetail->marketingOrderDetail->realPriceAfterGlobalDiscount();
    }

    public function getTotal(){
        $total = $this->qty * $this->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion * $this->marketingOrderDeliveryDetail->marketingOrderDetail->realPriceAfterGlobalDiscount();
        return $total;
    }

    public function getTax(){
        $tax = 0;
        if($this->marketingOrderDeliveryDetail->marketingOrderDetail->tax_id > 0){
            $tax = $this->getTotal() * ($this->marketingOrderDeliveryDetail->marketingOrderDetail->percent_tax / 100);
        }
        return $tax;
    }

    public function getGrandtotal(){
        $grandtotal = $this->getTotal() + $this->getTax();
        return $grandtotal;
    }

    public function isPallet(){
        $yeah = false;
        if(strpos($this->itemStock->item->pallet->prefix_code,'PLT') !== false){
            $yeah = true;
        }
        return $yeah;
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }

    public function marketingOrderReturnDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderReturnDetail')->whereHas('marketingOrderReturn',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function marketingOrderInvoiceDetail(){
        return $this->hasMany('App\Models\MarketingOrderInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('marketingOrderInvoice',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function listMarketingOrderInvoice(){
        $arr=[];
        if($this->marketingOrderInvoiceDetail()->exists()){
            foreach($this->marketingOrderInvoiceDetail()->get() as $item){
                $arr[] = $item->marketingOrderInvoice->code;
            }
            $x=implode(',', $arr);
        }else{
            $x= '-';
        }
        return $x;
    }

    public function balanceInvoice(){
        $qtytotal = $this->qty - $this->qtyReturn();

        foreach($this->marketingOrderInvoiceDetail as $row){
            $qtytotal -= $row->qty;
        }

        return $qtytotal;
    }

    public function qtyReturn(){
        return $this->marketingOrderReturnDetail()->sum('qty');
    }

    public function getBalanceQtySentMinusReturn(){
        $total = $this->qty;
        foreach($this->marketingOrderReturnDetail as $row){
            $total -= $row->qty;
        }

        return $total;
    }

    public function getPriceHpp(){
        $pricenow = 0;
        $cogs = ItemCogs::where('item_id',$this->itemStock->item_id)->where('place_id',$this->itemStock->place_id)->where('warehouse_id',$this->itemStock->warehouse_id)->where('item_shading_id',$this->itemStock->item_shading_id)->where('production_batch_id',$this->itemStock->production_batch_id)->where('date','<=',$this->marketingOrderDeliveryProcess->post_date)->orderBy('date')->orderBy('id')->get();
        $total = 0;
        $qty = 0;
        foreach($cogs as $row){
            if($row->type == 'IN'){
                $qty += $row->qty_in;
                $total += $row->total_in;
            }elseif($row->type == 'OUT'){
                $qty -= $row->qty_out;
                $total -= $row->total_out;
            }
        }
        $pricenow = $qty > 0 ? $total / $qty : 0;
        return $pricenow;
    }

    public function getHpp(){
        return round($this->getPriceHpp() * $this->qty * $this->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,2);
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
