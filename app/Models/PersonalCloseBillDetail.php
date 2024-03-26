<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PersonalCloseBillDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'personal_close_bill_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'personal_close_bill_id',
        'fund_request_id',
        'nominal',
        'note',
    ];

    public function personalCloseBill(){
        return $this->belongsTo('App\Models\PersonalCloseBill', 'personal_close_bill_id', 'id')->withTrashed();
    }

    public function fundRequest(){
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }
}
