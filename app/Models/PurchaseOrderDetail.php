<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_order_id',
        'purchase_request_detail_id',
        'good_issue_detail_id',
        'item_id',
        'coa_id',
        'qty',
        'price',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'subtotal',
        'note',
        'is_tax',
        'is_include_tax',
        'percent_tax',
        'is_wtax',
        'percent_wtax',
        'tax_id',
        'wtax_id',
        'place_id',
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
    ];

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function isTax(){
        $type = match ($this->is_tax) {
          NULL => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }

    public function isWtax(){
        $type = match ($this->is_wtax) {
          NULL => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine','machine_id','id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function department(){
        return $this->belongsTo('App\Models\Department','department_id','id')->withTrashed();
    }

    public function tax(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function wtax(){
        return $this->belongsTo('App\Models\Tax','wtax_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function goodReceiptDetail(){
        return $this->hasMany('App\Models\GoodReceiptDetail','purchase_order_detail_id','id')->whereHas('goodReceipt',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function getArrayTotal(){
        $wtax = 0;
        $total = 0;
        $grandtotal = 0;
        $tax = 0;
        $discount = $this->purchaseOrder->discount;
        $subtotal = $this->purchaseOrder->subtotal;
        $rowprice = 0;
        $bobot = $this->subtotal / $subtotal;
        $rowprice = round($this->subtotal / $this->qty,2);
        $total = ($rowprice * $this->qty) - ($bobot * $discount);

        if($this->is_tax == '1' && $this->is_include_tax == '1'){
            $total = $total / (1 + ($this->percent_tax / 100));
        }
        
        if($this->is_tax == '1'){
            $tax = round($total * ($this->percent_tax / 100),2);
        }

        if($this->is_wtax == '1'){
            $wtax = round($total * ($this->percent_wtax / 100),2);
        }

        $grandtotal = $total + $tax - $wtax;

        $arrDetail = [
            'total'         => $total,
            'tax'           => $tax,
            'wtax'          => $wtax,
            'grandtotal'    => $grandtotal,
        ];

        return $arrDetail;
    }

    public function getBalanceReceipt()
    {

        $received = $this->goodReceiptDetail()->sum('qty');

        $balance = $this->qty - $received;

        return $balance;
    }

    public function purchaseRequestDetail()
    {
        return $this->belongsTo('App\Models\PurchaseRequestDetail','purchase_request_detail_id','id');
    }

    public function goodIssueDetail()
    {
        return $this->belongsTo('App\Models\GoodIssueDetail','good_issue_detail_id','id');
    }

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceInvoice(){
        $total = round($this->getArrayTotal()['grandtotal'],2);

        foreach($this->purchaseInvoiceDetail as $row){
            $total -= $row->grandtotal;
        }

        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->purchaseInvoiceDetail as $row){
            $total += $row->grandtotal;
        }

        return $total;
    }
}
