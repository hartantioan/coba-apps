<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class InvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'invoice_id',
        'store_item_stock_id',
        'qty',
        'price',
        'total',
        'tax',
        'wtax',
        'discount',
        'before_discount',
    ];

    public function storeItemStock()
    {
        return $this->belongsTo('App\Models\StoreItemStock', 'store_item_stock_id', 'item_id');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Models\Invoice', 'invoice_id', 'id')->withTrashed();
    }

}
