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
        'note',
        'required_date',
        'place_id',
        'warehouse_id',
        'status',
    ];

    public function materialRequest()
    {
        return $this->belongsTo('App\Models\MaterialRequest', 'material_request_id', 'id')->withTrashed();
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
            $query->whereIn('status',['2','3']);
        });
    }

    public function goodIssueDetail()
    {
        return $this->hasMany('App\Models\GoodIssueDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('goodIssue',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balancePr(){
        $totalPr = $this->qty - $this->stock;
        foreach($this->purchaseRequestDetail as $row){
            $totalPr -= $row->qty;
        }
        return $totalPr;
    }

    public function balanceGi(){
        $totalGi = $this->qty - $this->stock;
        if($totalGi > 0){
            $totalGi = $this->stock;
        }else{
            $totalGi = $this->qty;
        }
        foreach($this->goodIssueDetail as $row){
            $totalGi -= round(($row->qty / $row->itemStock->item->buy_convert),3);
        }
        return $totalGi;
    }

    public function status(){
        $status = match ($this->status) {
            '1' => in_array($this->materialRequest->status,['2','3']) ? '<b style="font-weight:900;color:green;">&#x2713;</b>' : '<b style="font-weight:900;color:yellow;">&#9898;</b>',
            default => in_array($this->materialRequest->status,['2','3']) ? '<b style="font-weight:900;color:red;">&#x2715;</b>' : '<b style="font-weight:900;color:yellow;">&#9898;</b>',
        };
  
          return $status;
    }
}
