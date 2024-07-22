<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_orders';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'post_date',
        'note',
        'status',
        'standard_item_cost',
        'standard_resource_cost',
        'standard_product_cost',
        'actual_item_cost',
        'actual_resource_cost',
        'total_product_cost',
        'planned_qty',
        'completed_qty',
        'rejected_qty',
        'real_time_start',
        'real_time_end',
        'total_production_time',
        'total_additional_time',
        'total_run_time',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function productionOrderDetail()
    {
        return $this->hasMany('App\Models\ProductionOrderDetail');
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Direvisi',
            default => 'Invalid',
        };

        return $status;
    }

    public static function generateCode($prefix,$order)
    {
        $cek = substr($prefix,0,7);
        $query = ProductionOrder::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $newcode = $order.substr($query[0]->code,1,7);
            $code = (int)$newcode + 1;
        } else {
            $code = $order.'0000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }

    public function hasDetailMatrix(){
        $ada = false;
        if($this->approval()){
            foreach($this->approval() as $row){
                if($row->approvalMatrix()->exists()){
                    $ada = true;
                }
            }
        }

        return $ada;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->productionOrderDetail as $row){
            if($row->productionIssue()->exists()){
                $hasRelation = true;
            }

            if($row->productionReceive()->exists()){
                $hasRelation = true;
            }

            if($row->productionFgReceive()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function isOpenPeriod(){
        $monthYear = substr($this->post_date, 0, 7); // '2023-02'

        // Query the LockPeriod model
        $see = LockPeriod::where('month', $monthYear)
                        ->whereIn('status_closing', ['3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }

    public function total(){
        $total = 0;
        
        foreach($this->productionOrderDetail as $row){
            if($row->productionIssue()->exists()){
                foreach($row->productionIssue as $rowissue){
                    $total += $rowissue->total();
                }
            }

            if($row->productionReceive()->exists()){
                foreach($row->productionReceive as $rowreceive){
                    $total -= $rowreceive->total();
                }
            }
        }
        
        return $total;
    }

    public function totalFg(){
        $total = 0;
        
        foreach($this->productionOrderDetail as $row){
            if($row->productionIssue()->exists()){
                foreach($row->productionIssue as $rowissue){
                    $total += $rowissue->total();
                }
            }
        }
        
        return $total;
    }

    public function qtyReceiveFg(){
        $qty = 0;
        
        foreach($this->productionOrderDetail as $row){
            if($row->productionReceive()->exists()){
                foreach($row->productionReceive as $rowreceive){
                    $qty += $rowreceive->qty();
                }
            }
        }
        
        return $qty;
    }

    public function updateProductionScheduleDone(){
        $status = true;

        $productionSchedule = NULL;

        foreach($this->productionOrderDetail as $row){
            $productionSchedule = $row->productionScheduleDetail->productionSchedule;
        }

        foreach($productionSchedule->productionScheduleDetail as $row){
            if($row->productionOrderDetail()->exists()){
                if($row->productionOrderDetail->productionOrder->status !== '3'){
                    $status = false;
                }
            }
        }

        if($status){
            $productionSchedule->update([
                'status'    => '3',
                'done_date' => date('Y-m-d H:i:s'),
            ]);

            activity()
                    ->performedOn(new ProductionSchedule())
                    ->causedBy(session('bo_id'))
                    ->withProperties($productionSchedule)
                    ->log('Done the Production Schedule data from Production Order');
        }
    }
}
