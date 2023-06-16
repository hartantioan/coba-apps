<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_issue_id',
        'item_stock_id',
        'qty',
        'price',
        'total',
        'note',
        'coa_id',
    ];

    public function goodIssue()
    {
        return $this->belongsTo('App\Models\GoodReceive', 'good_issue_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function qtyConvertToBuy()
    {
        $qty = round($this->qty / $this->itemStock->item->buy_convert,3);

        return $qty;
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail', 'good_issue_detail_id', 'id')->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function qtyBalance(){
        $qty = $this->qtyConvertToBuy();

        foreach($this->purchaseOrderDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }
}
