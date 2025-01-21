<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleBpScale extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'rule_bp_scale';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'account_id',
        'item_id',
        'rule_procurement_id',
        'effective_date',
        'percentage_level',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }


    public function ruleProcurement()
    {
        return $this->belongsTo('App\Models\RuleProcurement', 'rule_procurement_id', 'id')->withTrashed();
    }
}
