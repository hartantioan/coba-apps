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
        'item_stock_id',
        'place_id',
        'warehouse_id',
    ];

    public function marketingOrderDetail()
    {
        return $this->belongsTo('App\Models\MarketingOrderDetail', 'marketing_order_detail_id', 'id')->withTrashed();
    }

    public function marketingOrderDelivery()
    {
        return $this->belongsTo('App\Models\MarketingOrderDelivery', 'marketing_order_delivery_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function getHpp(){
        $total = round($this->itemStock->priceDate($this->marketingOrderDelivery->post_date) * $this->qty * $this->item->sell_convert,2);
        return $total;
    }

    public function getPriceHpp(){
        return $this->itemStock->priceDate($this->marketingOrderDelivery->post_date);
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
