<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LandedCostDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'landed_cost_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'landed_cost_id',
        'item_id',
        'coa_id',
        'qty',
        'nominal',
        'place_id',
        'department_id',
        'warehouse_id',
        'lookable_type',
        'lookable_id',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function landedCost()
    {
        return $this->belongsTo('App\Models\LandedCost', 'landed_cost_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function goodReceiptDetail()
    {
        if($this->lookable_type == 'good_receipt_details'){
            return true;
        }else{
            return false;
        }
    }

    public function landedCostDetail()
    {
        if($this->lookable_type == 'landed_cost_details'){
            return true;
        }else{
            return false;
        }
    }

    public function landedCostDetailSelf()
    {
        return $this->hasMany('App\Models\LandedCostDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('landedCost',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
