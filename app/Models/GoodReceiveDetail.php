<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_receive_id',
        'item_id',
        'place_id',
        'warehouse_id',
        'qty',
        'price',
        'total',
        'note',
        'inventory_coa_id',
        'coa_id',
        'cost_distribution_id',
        'place_cost_id',
        'line_id',
        'machine_id',
        'department_id',
        'area_id',
        'item_shading_id',
        'batch_no',
        'project_id',
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
            $place = $this->placeCost()->exists() ? $this->placeCost->code : '-';
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

    public function inventoryCoa()
    {
        return $this->belongsTo('App\Models\InventoryCoa', 'inventory_coa_id', 'id')->withTrashed();
    }

    public function goodReceive()
    {
        return $this->belongsTo('App\Models\GoodReceive', 'good_receive_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function productionBatch(){
        return $this->hasOne('App\Models\ProductionBatch','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function placeCost()
    {
        return $this->belongsTo('App\Models\Place', 'place_cost_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(',',$arr);
    }
}
