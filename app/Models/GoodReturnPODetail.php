<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReturnPODetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_return_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_return_id',
        'good_receipt_detail_id',
        'item_id',
        'qty',
        'note',
        'note2',
    ];

    public function goodReturnPO()
    {
        return $this->belongsTo('App\Models\GoodReturnPO', 'good_return_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function goodReceiptDetail()
    {
        return $this->belongsTo('App\Models\GoodReceiptDetail', 'good_receipt_detail_id', 'id')->withTrashed();
    }

    public function getRowTotal(){
        $total = 0;
        $rowprice = round($this->goodReceiptDetail->total / $this->goodReceiptDetail->qty,2);

        $total = $rowprice * $this->qty;

        return round($total,2);
    }

    public function qtyConvert(){
        $qty = round($this->qty * $this->item->buy_convert,3);

        return $qty;
    }
}
