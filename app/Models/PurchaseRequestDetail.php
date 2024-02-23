<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'qty',
        'item_unit_id',
        'qty_conversion',
        'note',
        'note2',
        'required_date',
        'place_id',
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
        'lookable_type',
        'lookable_id',
        'status',
        'requester',
        'project_id',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function purchaseRequest()
    {
        return $this->belongsTo('App\Models\PurchaseRequest', 'purchase_request_id', 'id')->withTrashed();
    }

    public function materialRequestDetail()
    {
        if($this->lookable_type == 'material_request_details'){
            return true;
        }else{
            return false;
        }
    }

    public function getBaseDocument(){
        $code = '';
        if($this->materialRequestDetail()){
            $code = $this->lookable->materialRequest->code;
        }
        return $code;
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }

    public function qtyPO(){
        $qty = 0;

        foreach($this->purchaseOrderDetail as $row){
            $qty += $row->qty;
        }

        return $qty;
    }

    public function qtyBalance(){
        $qty = $this->qty;

        foreach($this->purchaseOrderDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }

    public function qtyUnreceivedPO(){
        $total = 0;

        foreach(PurchaseOrderDetail::where('item_id',$this->item_id)->where('place_id',$this->place_id)->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2','3']);
        })->get() as $row){
            $total += $row->getBalanceReceipt();
        }

        return $total;
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail', 'purchase_request_detail_id', 'id')->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function getListPo(){
        $list = [];
        foreach($this->purchaseOrderDetail as $row){
            $list[] = $row->purchaseOrder->code;
        }
        return implode(',',$list);
    }
}
