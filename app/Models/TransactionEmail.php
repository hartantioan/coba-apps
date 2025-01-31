<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionEmail extends Model
{
    use HasFactory;
    protected $table = 'transaction_emails';

    protected $fillable = [
        'user_id',
        'account_id',
        'email_to',
        'cc_email_to',
        'status',
        'lookable_type',
        'lookable_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function Vendor(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function lookable()
    {
        return $this->morphTo();
    }
}
