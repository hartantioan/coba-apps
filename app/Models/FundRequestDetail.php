<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FundRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'fund_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'fund_request_id',
        'note',
        'qty',
        'unit_id',
        'price',
        'total',
    ];

    public function fundRequest()
    {
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }
}
