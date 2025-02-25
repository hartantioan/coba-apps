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
        'description',
        'lookable_type',
        'lookable_id',
        'qty',
        'unit_id',
        'price',
        'is_include_tax',
        'percent_tax',
        'tax_id',
        'total',
        'tax',
        'grandtotal',
        'note',
    ];

    public function priceBeforeDiscount(){
        $price = 0;
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            $price = $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            $price = $this->lookable->marketingOrderDetail->price;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            $price = $this->price;
        }
        return $price;
    }

    public function discount(){
        $discount = 0;
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            $discount = $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price - $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price_after_discount;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            $discount = $this->lookable->marketingOrderDetail->price - $this->lookable->marketingOrderDetail->price_after_discount;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            $discount = 0;
        }
        return $discount;
    }

    public function priceBeforeTax(): mixed{
        $price = $this->priceBeforeDiscount();
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            if(date('Y-m-d',strtotime($this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->created_at)) >= '2024-12-24'){
                //do nothing
            }else{
                if($this->is_include_tax == '1'){
                    $price = $price / ((100 + $this->percent_tax) / 100);
                }
            }
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            if(date('Y-m-d',strtotime($this->lookable->marketingOrderDetail->created_at)) >= '2024-12-24'){
                //do nothing
            }else{
                if($this->is_include_tax == '1'){
                    $price = $price / ((100 + $this->percent_tax) / 100);
                }
            }
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            $price = $price / ((100 + $this->percent_tax) / 100);
        }

        return $price;
    }

    public function totalDpp2025(){
        $totalDpp = 11/12 * (round($this->totalBeforeTax(), 2) - round($this->totalDiscountBeforeTax(), 2));
        return round($totalDpp,2);
    }
    
    public function totalTax2025(){
        $totalTax = $this->totalDpp2025() * (12 / 100);
        return round($totalTax,2);
    }

    public function discountBeforeTax(){
        $discount = $this->discount();
        if($discount > 0){
            $newVersion = false;
            if ($this->lookable_type == 'marketing_order_delivery_details') {
                if(date('Y-m-d',strtotime($this->lookable->marketingOrderDetail->created_at)) >= '2024-12-24'){
                    $newVersion = true;
                }
            }
            if ($this->lookable_type == 'marketing_order_delivery_process_details') {
                if(date('Y-m-d',strtotime($this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->created_at)) >= '2024-12-24'){
                    $newVersion = true;
                }
            }
            if(!$newVersion){
                $discount = $discount / ((100 + $this->percent_tax) / 100);
            }
        }
        return $discount;
    }

    public function totalDiscountBeforeTax(){
        $total = $this->getQtyM2()  * $this->discountBeforeTax();
        return $total;
    }

    public function totalBeforeTax(){
        $total = round($this->getQtyM2() * $this->priceBeforeTax(),2);
        return $total;
    }

    public function lookable()
    {
        return $this->morphTo();
    }

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }

    public function marketingOrderInvoice()
    {
        return $this->belongsTo('App\Models\MarketingOrderInvoice', 'marketing_order_invoice_id', 'id')->withTrashed();
    }

    public function marketingOrderMemoDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderMemoDetail', 'lookable_id', 'id')->where('lookable_type', $this->table)->whereHas('marketingOrderMemo', function ($query) {
            $query->whereIn('status', ['2', '3']);
        });
    }

    public function getItem()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return $this->description;
        }
    }

    public function getItemBrand()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->brand->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->brand->name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getItemBrandCategory()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->brand->type();
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->brand->type();
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getItemColor()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->pattern->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->pattern->name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getQualityCategory()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->grade->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->grade->name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getItemColorCategory()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->variety->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->variety->name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getPrintName()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->print_name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->print_name;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getItemCode()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->code;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->code;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }

    public function getMarketingOrder()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail();
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail() ?? null;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return null;
        }
    }

    public function getItemReal()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail->itemUnit;
        }
    }

    public function getItemType()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->item->type->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail->item->type->name;
        } else {
            return '';
        }
    }

    public function getItemCategoryColor()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->item->variety->name;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail->item->variety->name;
        } else {
            return '';
        }
    }

    public function getDownPayment()
    {
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $downpayment = $bobot * $this->marketingOrderInvoice->downpayment;
        return $downpayment;
    }

    public function getMemo()
    {
        $total = 0;
        foreach ($this->marketingOrderMemoDetail as $row) {
            $total += $row->grandtotal;
        }
        return $total;
    }

    public function getGrandtotal()
    {
        if ($this->marketingOrderInvoice->total == 0) {
            $bobot = $this->total;
        } else {
            $bobot = $this->total / $this->marketingOrderInvoice->total;
        }

        $total = $bobot * $this->marketingOrderInvoice->grandtotal;
        return $total;
    }

    public function getRounding()
    {
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $total = $bobot * $this->marketingOrderInvoice->rounding;
        return $total;
    }

    public function getPrice()
    {
        $price = $this->total / $this->qty;
        return $price;
    }

    public function proportionalTaxFromHeader()
    {
        $tax = $this->marketingOrderInvoice->tax;
        //tax > 0 ,, karna DP 100%,, tax nya pasti 0, sedangkan FP,, tetap minta ada PPN nya di detail
        if ($tax > 0) {
            $bobot = $this->marketingOrderInvoice->total > 0 ? $this->total / $this->marketingOrderInvoice->total : 0;
            $rowtax = round($tax * $bobot, 0);
            return $rowtax;
        }
        else {
            return floor($this->tax);
        }
    }

    public function arrBalanceMemo()
    {
        $total = round($this->total, 2);
        $tax = round($this->tax, 2);
        $grandtotal = round($this->grandtotal, 2);
        $balance = round($this->grandtotal, 2);

        $arr = [];

        foreach ($this->marketingOrderMemoDetail as $row) {
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

    public function isIncludeTax()
    {
        $type = match ($this->is_include_tax) {
            '0' => 'Tidak',
            '1' => 'Termasuk',
            default => 'Invalid',
        };

        return $type;
    }

    public function getPayment()
    {
        $bobot = $this->total / $this->marketingOrderInvoice->total;
        $total = $bobot * $this->marketingOrderInvoice->totalPay();
        return $total;
    }

    public function getQtyM2()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion * $this->qty;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail->qty_conversion * $this->qty;
        } else {
            return $this->qty;
        }
    }

    public function getBoxConversion()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->pallet->box_conversion;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->pallet->box_conversion;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return 1;
        }
    }

    public function getHSCode()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->itemStock->item->type->hs_code;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->item->type->hs_code;
        }else if ($this->lookable_type == '' || $this->lookable_type == null ){
            return '';
        }
    }



    public function getMoDetail()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return $this->lookable->marketingOrderDeliveryDetail->marketingOrderDetail;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDetail;
        } else {
            return $this;
        }
    }

    public function getHPP()
    {
        if ($this->lookable_type == 'marketing_order_delivery_process_details') {
            return  $this->lookable_type->total;
        } else if ($this->lookable_type == 'marketing_order_delivery_details') {
            return $this->lookable->marketingOrderDeliveryProcessDetail()->sum('total');
        } else {
            return 0;
        }
    }
}
