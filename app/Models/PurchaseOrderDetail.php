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
        'item_id',
        'qty',
        'price',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'subtotal',
        'note',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function getBalanceReceipt()
    {
        $item = $this->item_id;
        $po = $this->purchase_order_id;

        $received = GoodReceiptDetail::whereHas('goodReceipt',function($query) use($po){
            $query->where('purchase_order_id',$po)
                ->whereHas('goodReceiptMain',function($query){
                    $query->whereIn('status',['1','2','3']);
                });
        })->where('item_id',$item)->sum('qty');

        $balance = $this->qty - $received;

        return $balance;
    }

    public function purchaseOrderDetailComposition()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetailComposition','pod_id','id');
    }

    public function purchaseRequestList(){
        $content = '';

        foreach($this->purchaseOrderDetailComposition as $row){
            $content .= $row->purchaseRequest->code.' - '.$row->qty.' '.$this->item->buyUnit->code.'<br>';
        }

        return $content;
    }
}
