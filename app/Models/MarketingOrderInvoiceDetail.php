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
        $bobot = $this->total / $this->marketingOrderInvoice->total;
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
}
