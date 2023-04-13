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
        'item_id',
        'qty',
        'note',
        'place_id',
        'department_id',
        'warehouse_id',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo('App\Models\GoodReceipt', 'good_receipt_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }
    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo('App\Models\PurchaseOrderDetail', 'purchase_order_detail_id', 'id')->withTrashed();
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
            $rowprice = $datarow->subtotal / $datarow->qty;
        }

        $total = ($rowprice * $this->qty) - ($bobot * $discount);

        if($datarow->is_tax == '1' && $datarow->is_include_tax == '1'){
            $total = $total / (1 + ($datarow->percent_tax / 100));
        }

        return round($total,3);
    }

    public function qtyConvert(){
        $qty = round($this->qty * $this->item->buy_convert,3);

        return $qty;
    }
}
