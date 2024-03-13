<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'payment_request_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_request_id',
        'cost_distribution_id',
        'coa_id',
        'place_id',
        'line_id',
        'machine_id',
        'division_id',
        'project_id',
        'nominal_debit',
        'nominal_credit',
        'nominal_debit_fc',
        'nominal_credit_fc',
        'note',
        'note2',
    ];

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine','machine_id','id')->withTrashed();
    }

    public function division(){
        return $this->belongsTo('App\Models\Division','division_id','id')->withTrashed();
    }

    public function project(){
        return $this->belongsTo('App\Models\Project','project_id','id')->withTrashed();
    }

    public function paymentRequest(){
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }
}
