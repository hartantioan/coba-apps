<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_issue_id',
        'item_stock_id',
        'qty',
        'price',
        'total',
        'note',
        'coa_id',
        'lookable_type',
        'lookable_id',
        'place_id',
        'warehouse_id',
        'area_id',
        'item_shading_id',
        'line_id',
        'machine_id',
        'department_id',
        'project_id',
        'requester',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function materialRequestDetail()
    {
        if($this->lookable_type == 'material_request_details'){
            return true;
        }else{
            return false;
        }
    }

    public function goodIssue()
    {
        return $this->belongsTo('App\Models\GoodIssue', 'good_issue_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function qtyConvertToBuy()
    {
        $qty = round($this->qty / $this->itemStock->item->buy_convert,3);

        return $qty;
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail', 'good_issue_detail_id', 'id')->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function qtyBalance(){
        $qty = $this->qtyConvertToBuy();

        foreach($this->purchaseOrderDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }
}
