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
        'inventory_coa_id',
        'coa_id',
        'lookable_type',
        'lookable_id',
        'cost_distribution_id',
        'place_id',
        'warehouse_id',
        'area_id',
        'item_shading_id',
        'line_id',
        'machine_id',
        'department_id',
        'project_id',
        'requester',
        'qty_return',
    ];

    public function getPlace(){
        $place = '';
        if($this->costDistribution()->exists()){
            $arrplace = [];
            foreach($this->costDistribution->costDistributionDetail as $row){
                if($row->place()->exists()){
                    $arrplace[] = $row->place->code;
                }
            }
            $place = implode(',',$arrplace);
        }else{
            $place = $this->place()->exists() ? $this->place->code : '-';
        }

        return $place;
    }

    public function getLine(){
        $line = '';
        if($this->costDistribution()->exists()){
            $arrtext = [];
            foreach($this->costDistribution->costDistributionDetail as $row){
                if($row->line()->exists()){
                    $arrtext[] = $row->line->code;
                }
            }
            $line = implode(',',$arrtext);
        }else{
            $line = $this->line()->exists() ? $this->line->code : '-';
        }

        return $line;
    }

    public function getMachine(){
        $machine = '';
        if($this->costDistribution()->exists()){
            $arrtext = [];
            foreach($this->costDistribution->costDistributionDetail as $row){
                if($row->machine()->exists()){
                    $arrtext[] = $row->machine->code;
                }
            }
            $machine = implode(',',$arrtext);
        }else{
            $machine = $this->machine()->exists() ? $this->machine->code : '-';
        }

        return $machine;
    }

    public function getDepartment(){
        $department = '';
        if($this->costDistribution()->exists()){
            $arrtext = [];
            foreach($this->costDistribution->costDistributionDetail as $row){
                if($row->department()->exists()){
                    $arrtext[] = $row->department->code;
                }
            }
            $department = implode(',',$arrtext);
        }else{
            $department = $this->department()->exists() ? $this->department->code : '-';
        }

        return $department;
    }

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

    public function inventoryCoa()
    {
        return $this->belongsTo('App\Models\InventoryCoa', 'inventory_coa_id', 'id')->withTrashed();
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

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
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

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail', 'good_issue_detail_id', 'id')->whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','usable_id','id')->where('usable_type',$this->table);
    }

    public function goodReturnIssueDetail()
    {
        return $this->hasMany('App\Models\GoodReturnIssueDetail', 'good_issue_detail_id', 'id')->whereHas('goodReturnIssue',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(', ',$arr);
    }

    public function arrSerial(){
        $arr = [];
        
        foreach($this->itemSerial as $row){
            $arr[] = [
                'serial_id'     => $row->id,
                'serial_number' => $row->serial_number,
            ];
        }

        return $arr;
    }

    public function qtyBalance(){
        $qty = $this->qty;

        foreach($this->purchaseOrderDetail as $row){
            $qty -= $row->qty * $row->qty_conversion;
        }

        return $qty;
    }

    public function qtyBalanceReturn(){
        $qty = $this->qty;

        foreach($this->goodReturnIssueDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }
}
