<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderHandoverReceiptDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_handover_receipt_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_handover_receipt_id',
        'marketing_order_receipt_id',
        'status',
        'note',
    ];

    public function marketingOrderHandoverReceipt()
    {
        return $this->belongsTo('App\Models\MarketingOrderHandoverReceipt', 'marketing_order_handover_receipt_id', 'id')->withTrashed();
    }

    public function marketingOrderReceipt()
    {
        return $this->belongsTo('App\Models\MarketingOrderReceipt', 'marketing_order_receipt_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
            '1' => 'Diterima Admin Penagihan',
            '2' => 'Diterima Customer',
            default => 'Dikirim oleh kurir',
        };

        return $status;
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
