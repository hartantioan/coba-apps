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
        'item_unit_id',
        'qty_conversion',
        'note',
        'note2',
    ];

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

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
        $rowprice = $this->goodReceiptDetail->total / $this->goodReceiptDetail->qty;

        $total = $rowprice * $this->qty;

        return round($total,2);
    }

    public function qtyConvert(){
        $qty = round($this->qty * $this->qty_conversion,3);

        return $qty;
    }

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','usable_id','id')->where('usable_type',$this->table);
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(', ',$arr);
    }

    public function arrSerial(){
        $arr = [];
        
        foreach($this->itemSerial as $row){
            $arr[] = [
                'serial_id'     => $row->id,
                'serial_number' => $row->serial_number,
            ];
        }

        return $arr;
    }
}
