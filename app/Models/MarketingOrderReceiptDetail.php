<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderReceiptDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_receipt_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_receipt_id',
        'lookable_type',
        'lookable_id',
        'note',
    ];

    public function marketingOrderReceipt()
    {
        return $this->belongsTo('App\Models\MarketingOrderReceipt', 'marketing_order_receipt_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
