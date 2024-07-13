<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionFgReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_fg_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_fg_receive_id',
        'item_id',
        'bom_id',
        'item_unit_id',
        'pallet_no',
        'shading',
        'qty_sell',
        'qty',
        'conversion',
        'pallet_id',
        'grade_id',
        'total_batch',
        'total_material',
        'total',
    ];

    public function productionFgReceive()
    {
        return $this->belongsTo('App\Models\ProductionFgReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\ProductionFgReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }

    public function balanceHandover(){
        $total = $this->qty_sell;
        foreach($this->productionHandoverDetail as $row){
            $total -= $row->qty_received;
        }
        return $total;
    }

    public function productionHandoverDetail(){
        return $this->hasMany('App\Models\ProductionHandoverDetail')->whereHas('productionHandover',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function pallet()
    {
        return $this->belongsTo('App\Models\Pallet', 'pallet_id', 'id')->withTrashed();
    }

    public function bom()
    {
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id')->withTrashed();
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Grade', 'grade_id', 'id')->withTrashed();
    }

    public function productionBatch()
    {
        return $this->hasOne('App\Models\ProductionBatch', 'lookable_id', 'id')->where('lookable_type',$this->table);
    }
}
