<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'material_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'material_request_id',
        'item_id',
        'qty',
        'stock',
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
        'project_id',
        'status',
        'requester',
        'total',
    ];

    public function materialRequest()
    {
        return $this->belongsTo('App\Models\MaterialRequest', 'material_request_id', 'id')->withTrashed();
    }

    public function header()
    {
        return $this->belongsTo('App\Models\MaterialRequest', 'material_request_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseRequestDetail()
    {
        return $this->hasMany('App\Models\PurchaseRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function goodIssueDetail()
    {
        return $this->hasMany('App\Models\GoodIssueDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('goodIssue',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function balancePr(){
        $totalPr = $this->qty - $this->getStockNow($this->qty_conversion);
        foreach($this->purchaseRequestDetail as $row){
            $totalPr -= $row->qty;
        }
        return $totalPr;
    }

    public function totalPr(){
        $total = 0;
        foreach($this->purchaseRequestDetail as $row){
            $total += $row->qty;
        }
        return $total;
    }

    public function totalGi(){
        $total = 0;
        foreach($this->goodIssueDetail as $row){
            $total += round($row->qty / $this->qty_conversion,3);
        }
        return $total;
    }

    public function getStockNow($conversion){
        $stock = 0;
        $itemStock = ItemStock::where('place_id',$this->place_id)->where('warehouse_id',$this->warehouse_id)->where('item_id',$this->item_id)->first();
        if($itemStock){
            $stock = $itemStock->qty > 0 ? round($itemStock->qty / $conversion,3) : 0;
        }
        return $stock;
    }

    public function balancePrGi(){
        $total = $this->qty - $this->getStockNow($this->qty_conversion);
        foreach($this->purchaseRequestDetail as $row){
            $total -= $row->qty;
        }
        foreach($this->goodIssueDetail as $row){
            $total -= round(($row->qty / $this->qty_conversion),3);
        }
        return $total;
    }

    public function balanceGi(){
        $totalGi = $this->qty - $this->getStockNow($this->qty_conversion);
        if($totalGi > 0){
            $totalGi = $this->getStockNow($this->qty_conversion);
        }else{
            $totalGi = $this->qty;
        }
        foreach($this->goodIssueDetail as $row){
            $totalGi -= round(($row->qty / $this->qty_conversion),3);
        }
        return $totalGi;
    }

    public function status(){
        $status = match ($this->status) {
            '1' => '<b style="font-weight:900;color:green;">&#x2713;</b>',
            '2' => '<b style="font-weight:900;color:red;">&#10060;</b>',
            default => '<b style="font-weight:900;color:yellow;">&#9898;</b>',
        };
  
        return $status;
    }

    public function statusConvert(){
        $status = match ($this->status) {
            '1' => 'Disetujui',
            '2' => 'Ditolak',
            default => 'Menunggu',
        };
  
        return $status;
    }
}
