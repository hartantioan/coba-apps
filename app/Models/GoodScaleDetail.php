<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodScaleDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_scale_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_scale_id',
        'purchase_order_detail_id',
        'item_id',
        'qty_in',
        'qty_out',
        'qty_balance',
        'note',
        'note2',
        'place_id',
        'warehouse_id',
    ];

    public function goodScale()
    {
        return $this->belongsTo('App\Models\GoodScale', 'good_scale_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo('App\Models\PurchaseOrderDetail', 'purchase_order_detail_id', 'id')->withTrashed();
    }

    public function goodReceiptDetail(){
        return $this->hasMany('App\Models\GoodReceiptDetail','good_scale_detail_id','id')->whereHas('goodReceipt',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }
}
