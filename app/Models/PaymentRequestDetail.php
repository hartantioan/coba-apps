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
    
    public function lookable(){
        return $this->morphTo();
    }

    public function type(){
        $type = match ($this->lookable_type) {
            'fund_requests'             => 'Permohonan Dana',
            'purchase_invoices'         => 'AP Invoice',
            'purchase_down_payments'    => 'AP Down Payment',
            default                     => 'Belum ditentukan',
          };
  
          return $type;
    }
}
