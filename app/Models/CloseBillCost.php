<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CloseBillCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'close_bill_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'close_bill_id',
        'cost_distribution_id',
        'coa_id',
        'place_id',
        'line_id',
        'machine_id',
        'division_id',
        'project_id',
        'total',
        'tax_id',
        'percent_tax',
        'is_include_tax',
        'wtax_id',
        'percent_wtax',
        'tax',
        'wtax',
        'grandtotal',
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

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function taxMaster(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function wtaxMaster(){
        return $this->belongsTo('App\Models\Tax','wtax_id','id')->withTrashed();
    }

    public function closeBill(){
        return $this->belongsTo('App\Models\CloseBill', 'close_bill_id', 'id')->withTrashed();
    }

    public function fundRequest(){
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }
}
