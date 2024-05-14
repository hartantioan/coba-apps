<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDeliveryDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_delivery_id',
        'marketing_order_detail_id',
        'item_id',
        'qty',
        'note',
        'place_id',
    ];

    public function marketingOrderDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDetail', 'marketing_order_detail_id', 'id')->withTrashed();
    }

    public function getTotal(){
        $total = $this->qty * $this->marketingOrderDetail->realPriceAfterGlobalDiscount();
        if($this->marketingOrderDetail->tax_id > 0 && $this->marketingOrderDetail->is_include_tax == '1'){
            $total = $total / (1 + ($this->marketingOrderDetail->percent_tax / 100));
        }
        return $total;
    }

    public function getTax(){
        $tax = 0;
        if($this->marketingOrderDetail->tax_id > 0){
            $tax = $this->getTotal() * ($this->marketingOrderDetail->percent_tax / 100);
        }
        return $tax;
    }

    public function getGrandtotal(){
        $grandtotal = $this->getTotal() + $this->getTax();
        return $grandtotal;
    }

    public function marketingOrderDelivery()
    {
        return $this->belongsTo('App\Models\MarketingOrderDelivery', 'marketing_order_delivery_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
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

    public function marketingOrderDeliveryStock(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryStock');
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
}
