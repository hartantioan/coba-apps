<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_invoice_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'price',
        'is_include_tax',
        'percent_tax',
        'tax_id',
        'total',
        'tax',
        'grandtotal',
        'note',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function marketingOrderInvoice()
    {
        return $this->belongsTo('App\Models\MarketingOrderInvoice', 'marketing_order_invoice_id', 'id')->withTrashed();
    }

    public function marketingOrderMemoDetail(){
        return $this->hasMany('App\Models\MarketingOrderMemoDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('marketingOrderMemo',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function getItem(){
        if($this->lookable_type == 'marketing_order_delivery_process_details'){
            return $this->lookable->itemStock->item->name;
        }else if($this->lookable_type == 'marketing_order_delivery_details'){
            return $this->lookable->item->name;
        }
    }

    public function getItemCode(){
        if($this->lookable_type == 'marketing_order_delivery_process_details'){
            return $this->lookable->itemStock->item->code;
        }else if($this->lookable_type == 'marketing_order_delivery_details'){
            return $this->lookable->item->code;
        }
    }

    public function getItemReal(){
        if($this->lookable_type == 'marketing_order_delivery_process_details'){
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit;
        }else if($this->lookable_type == 'marketing_order_delivery_details'){
            return $this->lookable->marketingOrderDetail->itemUnit;
        }
    }



    public function getDownPayment(){
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $downpayment = $bobot * $this->marketingOrderInvoice->downpayment;
        return $downpayment;
    }

    public function getMemo(){
        $total = 0;
        foreach($this->marketingOrderMemoDetail as $row){
            $total += $row->grandtotal;
        }
        return $total;
    }

    public function getGrandtotal(){
        if ($this->marketingOrderInvoice->total == 0) {
            $bobot = $this->total;
        } else {
            $bobot = $this->total / $this->marketingOrderInvoice->total;
        }

        $total = $bobot * $this->marketingOrderInvoice->grandtotal;
        return $total;
    }

    public function getRounding(){
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $total = $bobot * $this->marketingOrderInvoice->rounding;
        return $total;
    }

    public function getPrice(){
        $price = $this->total / $this->qty;
        return $price;
    }

    public function proportionalTaxFromHeader(){
        $tax = $this->marketingOrderInvoice->tax;
        $bobot = $this->marketingOrderInvoice->total > 0 ? $this->total / $this->marketingOrderInvoice->total : 0;
        $rowtax = round($tax * $bobot,0);
        return $rowtax;
    }

    public function arrBalanceMemo(){
        $total = round($this->total,2);
        $tax = round($this->tax,2);
        $grandtotal = round($this->grandtotal,2);
        $balance = round($this->grandtotal,2);

        $arr = [];

        foreach($this->marketingOrderMemoDetail as $row){
            $balance -= $row->grandtotal;
        }

        $arr = [
            'total'             => $total,
            'tax'               => $tax,
            'grandtotal'        => $grandtotal,
            'balance'           => $balance
        ];

        return $arr;
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function getPayment(){
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $total = $bobot * $this->marketingOrderInvoice->totalPay();
        return $total;
    }

    public function getQtyM2(){
        if($this->lookable_type == 'marketing_order_delivery_process_details'){
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion * $this->qty;
        }else if($this->lookable_type == 'marketing_order_delivery_details'){
            return $this->lookable->marketingOrderDetail->qty_conversion * $this->qty;
        }
    }

    public function getMoDetail(){
        if($this->lookable_type == 'marketing_order_delivery_process_details'){
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail;
        }else if($this->lookable_type == 'marketing_order_delivery_details'){
            return $this->lookable->marketingOrderDetail;
        }
    }


}
