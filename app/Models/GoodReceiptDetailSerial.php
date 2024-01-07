<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptDetailSerial extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receipt_detail_serials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_receipt_id',
        'good_receipt_detail_id',
        'item_id',
        'serial_number',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo('App\Models\GoodReceipt', 'good_receipt_id', 'id')->withTrashed();
    }

    public function goodReceiptDetail()
    {
        return $this->belongsTo('App\Models\GoodReceiptDetail', 'good_receipt_detail_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }
}
