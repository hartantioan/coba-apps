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
            default                     => '-',
        };

        return $code;
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id');
    }
}
