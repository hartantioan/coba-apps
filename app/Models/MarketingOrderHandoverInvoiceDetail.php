<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderHandoverInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_handover_invoice_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_handover_invoice_id',
        'lookable_type',
        'lookable_id',
        'note',
    ];

    public function marketingOrderHandoverInvoice()
    {
        return $this->belongsTo('App\Models\MarketingOrderHandoverInvoice', 'marketing_order_handover_invoice_id', 'id')->withTrashed();
    }

    public function marketingOrderInvoice()
    {
        if($this->lookable_type == 'marketing_order_invoices'){
            return true;
        }else{
            return false;
        }
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
