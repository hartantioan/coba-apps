<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class IncomingPaymentList extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'incoming_payment_lists';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'incoming_payment_id',
        'list_bg_check_id',
    ];

    public function incomingPayment()
    {
        return $this->belongsTo('App\Models\IncomingPayment', 'incoming_payment_id', 'id')->withTrashed();
    }

    public function listBgCheck()
    {
        return $this->belongsTo('App\Models\ListBgCheck', 'list_bg_check_id', 'id')->withTrashed();
    }
}
