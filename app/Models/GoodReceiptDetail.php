<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receipt_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_receipt_id',
        'purchase_order_detail_id',
        'good_scale_id',
        'item_id',
        'qty',
        'item_unit_id',
        'qty_conversion',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'note',
        'note2',
        'remark',
        'water_content',
        'viscosity',
        'residue',
        'place_id',
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo('App\Models\GoodReceipt', 'good_receipt_id', 'id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\GoodReceipt', 'good_receipt_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }
    
    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }
    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo('App\Models\PurchaseOrderDetail', 'purchase_order_detail_id', 'id')->withTrashed();
    }

    public function goodScale()
    {
        return $this->belongsTo('App\Models\GoodScale', 'good_scale_id', 'id')->withTrashed();
    }

    public function goodReturnPODetail(){
        return $this->hasMany('App\Models\GoodReturnPODetail','good_receipt_detail_id','id')->whereHas('goodReturnPO',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(', ',$arr);
    }

    public function getRowTotal(){
        $total = 0;
        $rowprice = 0;
        $po = $this->purchaseOrderDetail->purchaseOrder;
        $discount = $po->discount;
        $subtotal = $po->subtotal;
        $bobot = 0;

        $datarow = $this->purchaseOrderDetail;

        if($datarow){
            $bobot = $datarow->subtotal / $subtotal;
            /* $rowprice = round($datarow->subtotal / $datarow->qty,2); */
            $rowprice = $datarow->price;
        }

        $total = ($rowprice * $this->qty) - ($bobot * $discount);

        if($datarow->is_tax == '1' && $datarow->is_include_tax == '1'){
            $total = $total / (1 + ($datarow->percent_tax / 100));
        }

        return round($total,2);
    }

    public function qtyConvert(){
        $qty = round($this->qty * $this->qty_conversion,3);

        return $qty;
    }

    public function getBalanceReturn(){
        $returned = $this->goodReturnPODetail()->sum('qty');

        $balance = $this->qty - $returned;

        return $balance;
    }

    public function qtyReturn(){
        $returned = $this->goodReturnPODetail()->sum('qty');

        return $returned;
    }

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['1','2','3','7']);
        });
    }

    public function landedCostDetail()
    {
        return $this->hasMany('App\Models\LandedCostDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('landedCost',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function balanceQtyInvoice(){
        $qty = $this->qty;

        foreach($this->purchaseInvoiceDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }
    

    public function qtyInvoice(){
        $qty = $this->purchaseInvoiceDetail()->sum('qty');

        return $qty;
    }

    public function balanceInvoice(){
        $total = round($this->total,2);

        foreach($this->purchaseInvoiceDetail as $row){
            $total -= $row->total;
        }

        return $total;
    }

    public function balanceTotalInvoice(){
        $total = round($this->total,2);

        foreach($this->purchaseInvoiceDetail as $row){
            $total -= $row->total;
        }

        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->purchaseInvoiceDetail as $row){
            $total += $row->total;
        }

        return $total;
    }

    public function updateTaxGrandtotal(){
        $total = $this->total;
        $tax = 0;
        $wtax = 0;
        $grandtotal = 0;

        if($this->purchaseOrderDetail->is_tax == '1' && $this->purchaseOrderDetail->is_include_tax == '1'){
            $total = round($total / (1 + ($this->purchaseOrderDetail->percent_tax / 100)),2);
        }

        if($this->purchaseOrderDetail->is_tax == '1'){
            $tax = round($total * ($this->purchaseOrderDetail->percent_tax / 100),2);
        }

        if($this->purchaseOrderDetail->is_wtax == '1'){
            $wtax = round($total * ($this->purchaseOrderDetail->percent_wtax / 100),2);
        }

        $grandtotal = $total + $tax - $wtax;

        $this->update([
            'tax'       => $tax,
            'wtax'      => $wtax,
            'grandtotal'=> $grandtotal
        ]);
    }
}
