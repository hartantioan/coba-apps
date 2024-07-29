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

    public function getItemIdBomChild(){
        $arr = [];
        foreach($this->productionScheduleDetail->bom->bomDetail()->where('lookable_type','items')->get() as $rowbom){
            if($rowbom->lookable->bom()->exists()){
                $arr[] = $rowbom->lookable_id;
            }
        }
        return $arr;
    }

    public function qtyReceive(){
        $qty = 0;
        
        if($this->productionReceive()->exists()){
            foreach($this->productionReceive as $rowreceive){
                $qty += $rowreceive->qty();
            }
        }
        
        return $qty;
    }

    public function qtyReject(){
        $qty = 0;
        
        if($this->productionReceive()->exists()){
            foreach($this->productionReceive as $rowreceive){
                $qty += $rowreceive->qtyReject();
            }
        }
        
        return $qty;
    }

    public function hasHandover(){
        $has = false;

        if($this->productionFgReceive()->exists()){
            foreach($this->productionFgReceive as $row){
                if($row->productionHandover()->exists()){
                    foreach($row->productionHandover as $rowhandover){
                        $has = true;
                    }
                }
            }
        }

        return $has;
    }

    public function htmlContentIssue(){
        $html = '<table class="bordered" style="max-width:600px !important;"><thead>
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
            foreach($rowissue->productionIssueDetail()->whereNull('is_wip')->get() as $key => $row){
                $html .= '<tr>
                            <td class="center-align">'.$no.'</td>
                            <td>'.$row->lookable->code.' - '.$row->lookable->name.'</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                            <td class="center-align">'.$row->lookable->uomUnit->code.'</td>
                        </tr>';
                $no++;
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    public function htmlHandover(){
        $html = '';
        if($this->hasHandover()){
            $html = '<table class="bordered mt-1"><thead>
                    <tr>
                        <th colspan="8">Daftar Item Receive</th>
                    </tr>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Item</th>
                        <th class="center-align">Qty</th>
                        <th class="center-align">Satuan</th>
                        <th class="center-align">Shading</th>
                        <th class="center-align">Plant</th>
                        <th class="center-align">Gudang</th>
                        <th class="center-align">Area</th>
                    </tr>
                </thead><tbody>';
            $no = 1;
            foreach($this->productionFgReceive as $row){
                foreach($row->productionHandover as $rowhandover){
                    foreach($rowhandover->productionHandoverDetail as $rowdetail){
                        $html .= '<tr>
                            <td class="center-align">'.$no.'</td>
                            <td>'.$rowdetail->item->code.' - '.$rowdetail->item->name.'</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($rowdetail->qty_received).'</td>
                            <td class="center-align">'.$rowdetail->productionFgReceiveDetail->itemUnit->unit->code.'</td>
                            <td class="center-align">'.$rowdetail->shading.'</td>
                            <td class="center-align">'.$rowdetail->place->code.'</td>
                            <td class="center-align">'.$rowdetail->warehouse->name.'</td>
                            <td class="center-align">'.$rowdetail->area->code.'</td>
                        </tr>';
                        $no++;
                    }
                }
            }

            $html .= '</tbody></table>';
        }

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
        
        return $total;
    }
}