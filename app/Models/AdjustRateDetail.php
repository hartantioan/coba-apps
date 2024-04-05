<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AdjustRateDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'adjust_rate_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'adjust_rate_id',
        'lookable_type',
        'lookable_id',
        'coa_id',
        'nominal_fc',
        'nominal_rate',
        'nominal_rp',
        'nominal_new',
        'nominal',
        'type',
    ];

    public function adjustRate()
    {
        return $this->belongsTo('App\Models\AdjustRate', 'adjust_rate_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function getType(){
        $type = match ($this->lookable_type) {
            'good_receipts'             => 'GRPO',
            'purchase_down_payments'    => 'APDP',
            default                     => 'Kosong',
        };
  
        return $type;
    }
}
