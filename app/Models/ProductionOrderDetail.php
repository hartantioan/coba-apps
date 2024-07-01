<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_order_id',
        'production_schedule_detail_id',
    ];

    public function productionOrder()
    {
        return $this->belongsTo('App\Models\ProductionOrder');
    }

    public function productionScheduleDetail()
    {
        return $this->belongsTo('App\Models\ProductionScheduleDetail');
    }

    public function productionIssue()
    {
        return $this->hasMany('App\Models\ProductionIssue')->whereIn('status',['1','2','3']);
    }

    public function productionReceive()
    {
        return $this->hasMany('App\Models\ProductionReceive')->whereIn('status',['1','2','3']);
    }

    public function qtyReceiveFg(){
        $qty = 0;
        
        if($this->productionReceive()->exists()){
            foreach($this->productionReceive as $rowreceive){
                $qty += $rowreceive->qty();
            }
        }
        
        return $qty;
    }

    public function totalFg(){
        $total = 0;
        
        if($this->productionIssue()->exists()){
            foreach($this->productionIssue as $rowissue){
                $total += $rowissue->total();
            }
        }
        
        return $total;
    }
}