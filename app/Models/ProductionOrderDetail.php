<?php

namespace App\Models;

use App\Helpers\CustomHelper;
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

    public function productionFgReceive()
    {
        return $this->hasMany('App\Models\ProductionFgReceive')->whereIn('status',['1','2','3']);
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

    public function qtyReceive(){
        $qty = 0;
        
        if($this->productionReceive()->exists()){
            foreach($this->productionReceive as $rowreceive){
                $qty += $rowreceive->qty();
            }
        }

        if($this->productionFgReceive()->exists()){
            foreach($this->productionFgReceive as $rowreceive){
                $qty += $rowreceive->qty();
            }
        }
        
        return $qty;
    }

    public function htmlContentIssue(){
        $html = '<table class="bordered"><thead>
                    <tr>
                        <th colspan="4">Daftar Item & Resource Issue</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Item/Resource</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                    </tr>
                </thead><tbody>';
        $no = 1;
        foreach($this->productionIssue as $rowissue){
            foreach($rowissue->productionIssueDetail as $key => $row){
                $html .= '<tr>
                            <td class="center-align">'.$no.'</td>
                            <td>'.$row->lookable->code.' - '.$row->lookable->name.'</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                            <td>'.$row->lookable->uomUnit->code.'</td>
                        </tr>';
                $no++;
            }
        }

        $html .= '</tbody></table>';

        return $html;
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

    public function totalIssueItem(){
        $total = 0;
        
        if($this->productionIssue()->exists()){
            foreach($this->productionIssue as $rowissue){
                $total += $rowissue->totalItem();
            }
        }
        
        return $total;
    }

    public function totalIssueResource(){
        $total = 0;
        
        if($this->productionIssue()->exists()){
            foreach($this->productionIssue as $rowissue){
                $total += $rowissue->totalResource();
            }
        }
        
        return $total;
    }

    public function totalItem(){
        $total = 0;
        
        if($this->productionReceive()->exists()){
            foreach($this->productionReceive as $rowreceive){
                $total += $rowreceive->total();
            }
        }

        if($this->productionFgReceive()->exists()){
            foreach($this->productionFgReceive as $rowreceive){
                $total += $rowreceive->total();
            }
        }
        
        return $total;
    }
}