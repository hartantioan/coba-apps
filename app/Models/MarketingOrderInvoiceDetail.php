<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_invoice_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'price',
        'is_include_tax',
        'percent_tax',
        'tax_id',
        'total',
        'tax',
        'grandtotal',
        'note',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function marketingOrderInvoice()
    {
        return $this->belongsTo('App\Models\MarketingOrderInvoice', 'marketing_order_invoice_id', 'id')->withTrashed();
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }
}
