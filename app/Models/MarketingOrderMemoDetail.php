<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseMemoDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_memo_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_memo_id',
        'lookable_type',
        'lookable_id',
        'total',
        'tax',
        'total_after_tax',
        'rounding',
        'grandtotal',
        'downpayment',
        'balance',
        'note',
    ];

    public function marketingOrderMemo()
    {
        return $this->belongsTo('App\Models\MarketingOrderMemo', 'marketing_order_memo_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
