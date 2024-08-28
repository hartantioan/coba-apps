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
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
        'project_id',
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

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
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

    public function inventoryTransferOutDetail()
    {
        if($this->lookable_type == 'inventory_transfer_out_details'){
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

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function getTax(){
        $total = $this->landedCost->total;
        $tax = ($this->nominal / $total) * $this->landedCost->tax;

        return $tax;
    }

    public function getWtax(){
        $total = $this->landedCost->total;
        $wtax = ($this->nominal / $total) * $this->landedCost->wtax;

        return $wtax;
    }

    public function getGrandtotal(){
        $grandtotal = $this->nominal + $this->getTax() - $this->getWtax();

        return $grandtotal;
    }

    public function priceFinalCogs(){
        $item_cogs = ItemCogs::where('lookable_type',$this->landedCost->getTable())->where('lookable_id',$this->landedCost->id)->where('item_id',$this->item_id)->first();

        return $item_cogs ? $item_cogs->price_final : 0;
    }

    public function getReference(){
        if($this->lookable_type == 'good_receipt_details'){
            return $this->lookable->goodReceipt->code;
        }else{
            return '';
        }
    }
}
