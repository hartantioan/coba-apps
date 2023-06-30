<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestCross extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'payment_request_crosses';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_request_id',
        'lookable_type',
        'lookable_id',
        'nominal',
    ];

    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function outgoingPayment()
    {
        if($this->lookable_type == 'outgoing_payments'){
            return true;
        }else{
            return false;
        }
    }
    public function type(){
        $type = match ($this->lookable_type) {
            'outgoing_payments'         => 'Kas/Bank Keluar',
            default                     => 'Belum ditentukan',
          };
  
          return $type;
    }
}
