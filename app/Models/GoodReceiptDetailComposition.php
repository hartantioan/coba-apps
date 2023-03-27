<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptDetailComposition extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receipt_detail_compositions';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'grd_id',
        'po_id',
        'qty'
    ];

    public function goodReceiptDetail()
    {
        return $this->belongsTo('App\Models\GoodReceiptDetail', 'grd_id', 'id')->withTrashed();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'po', 'id')->withTrashed();
    }
}
