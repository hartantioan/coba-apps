<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class IncomingPaymentDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'incoming_payment_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'incoming_payment_id',
        'lookable_type',
        'lookable_id',
        'cost_distribution_id',
        'total',
        'rounding',
        'subtotal',
        'note',
    ];

    public function incomingPayment()
    {
        return $this->belongsTo('App\Models\IncomingPayment', 'incoming_payment_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function getCode(){
        $code = match ($this->lookable_type) {
            'coas'                      => $this->lookable->code.' - '.$this->lookable->name,
            default                     => $this->lookable->code,
        };

        return $code;
    }

    public function type(){
        $code = match ($this->lookable_type) {
            'coas'                          => 'Coa',
            'outgoing_payments'             => 'Kas Keluar / Outgoing Payment',
            'marketing_order_invoices'      => 'AR Invoice',
            'marketing_order_memos'         => 'AR Credit Memo',
            'marketing_order_down_payments' => 'AR Down Payment',
            default                         => $this->lookable->code,
        };

        return $code;
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id');
    }

    public function coa()
    {
        if($this->lookable_type == 'coas'){
            return $this->belongsTo('App\Models\Coa','lookable_id','id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function outgoingPayment()
    {
        if($this->lookable_type == 'outgoing_payments'){
            return $this->belongsTo('App\Models\OutgoingPayment','lookable_id','id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function marketingOrderInvoice(){
        if($this->lookable_type == 'marketing_order_invoices'){
            return $this->belongsTo('App\Models\MarketingOrderInvoice', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function marketingOrderMemo(){
        if($this->lookable_type == 'marketing_order_memos'){
            return $this->belongsTo('App\Models\MarketingOrderMemo', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function marketingOrderDownPayment(){
        if($this->lookable_type == 'marketing_order_down_payments'){
            return $this->belongsTo('App\Models\MarketingOrderDownPayment', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }
}
