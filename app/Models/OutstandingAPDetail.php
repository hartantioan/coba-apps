<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class OutstandingAPDetail extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'outstanding_ap_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'outstanding_ap_id',
        'code',
        'account',
        'post_date',
        'received_date',
        'top',
        'due_date',
        'total',
        'paid',
        'balance',
    ];

    public function outstandingAp(){
        return $this->belongsTo('App\Models\OutstandingAP','outstanding_ap_id','id')->withTrashed();
    }
}