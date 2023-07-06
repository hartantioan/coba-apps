<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'payment_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_request_id',
        'lookable_type',
        'lookable_id',
        'cost_distribution_id',
        'coa_id',
        'nominal',
        'note',
    ];

    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function purchaseDownPayment()
    {
        if($this->lookable_type == 'purchase_down_payments'){
            return true;
        }else{
            return false;
        }
    }

    public function getMemo(){
        $total = 0;

        if($this->lookable_type == 'purchase_down_payments'){
            $total = $this->lookable->totalMemo();
        }

        if($this->lookable_type == 'purchase_invoices'){
            $total = $this->lookable->totalMemo();
        }
        
        return $total;
    }
 
    public function purchaseInvoice()
    {
        if($this->lookable_type == 'purchase_invoices'){
            return true;
        }else{
            return false;
        }
    }

    public function fundRequest()
    {
        if($this->lookable_type == 'fund_requests'){
            return true;
        }else{
            return false;
        }
    }

    public function type(){
        $type = match ($this->lookable_type) {
            'fund_requests'             => 'Permohonan Dana',
            'purchase_invoices'         => 'A/P Invoice',
            'purchase_down_payments'    => 'AP Down Payment',
            'coas'                      => 'Coa Biaya',
            default                     => 'Belum ditentukan',
          };
  
          return $type;
    }
}
