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
        'item_id',
        'qty',
        'note',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo('App\Models\GoodReceipt', 'good_receipt_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function getRowTotal(){
        $total = 0;
        $rowprice = 0;
        $po = $this->goodReceipt->purchaseOrder;
        $discount = $po->discount;
        $subtotal = $po->subtotal;
        $bobot = 0;

        $datarow = $po->purchaseOrderDetail()->where('item_id',$this->item_id)->first();

        if($datarow){
            $bobot = $datarow->subtotal / $subtotal;
            $rowprice = round($datarow->subtotal / $datarow->qty,3);
        }

        $total = ($rowprice * $this->qty) - ($bobot * $discount);

        if($po->is_tax == '1' && $po->is_include_tax == '1'){
            $total = $total / (1 + ($po->percent_tax / 100));
        }

        return round($total,3);
    }

    public function qtyConvert(){
        $qty = round($this->qty * $this->item->buy_convert,3);

        return $qty;
    }
}
