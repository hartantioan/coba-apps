<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CloseBillDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'close_bill_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'close_bill_id',
        'outgoing_payment_id',
        'personal_close_bill_id',
        'nominal',
        'note',
    ];

    public function closeBill(){
        return $this->belongsTo('App\Models\CloseBill', 'close_bill_id', 'id')->withTrashed();
    }

    public function personalCloseBill(){
        return $this->belongsTo('App\Models\PersonalCloseBill', 'personal_close_bill_id', 'id')->withTrashed();
    }

    public function outgoingPayment(){
        return $this->belongsTo('App\Models\OutgoingPayment', 'outgoing_payment_id', 'id')->withTrashed();
    }
}
