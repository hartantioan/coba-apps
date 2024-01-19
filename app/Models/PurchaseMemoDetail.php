<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseMemoDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_memo_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_memo_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'description',
        'description2',
        'is_include_tax',
        'tax_id',
        'wtax_id',
        'percent_tax',
        'percent_wtax',
        'total',
        'tax',
        'wtax',
        'grandtotal',
    ];

    public function purchaseMemo()
    {
        return $this->belongsTo('App\Models\PurchaseMemo', 'purchase_memo_id', 'id')->withTrashed();
    }

    public function getCode(){
        $code = match ($this->lookable_type) {
            'purchase_invoice_details'  => $this->lookable->purchaseInvoice->code,
            'purchase_down_payments'    => $this->lookable->code,
            default => '-',
        };

        return $code;
    }

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function wTaxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'wtax_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
