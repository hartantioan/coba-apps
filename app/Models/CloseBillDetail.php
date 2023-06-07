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
        'fund_request_id',
        'coa_id',
        'cost_distribution_id',
        'nominal',
        'tax_id',
        'is_include_tax',
        'percent_tax',
        'wtax_id',
        'percent_wtax',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'balance',
        'note',
    ];

    public function closeBill(){
        return $this->belongsTo('App\Models\CloseBill', 'close_bill_id', 'id')->withTrashed();
    }

    public function costDistribution(){
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function tax(){
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function wtax(){
        return $this->belongsTo('App\Models\Tax', 'wtax_id', 'id')->withTrashed();
    }

    public function fundRequest(){
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }
}
