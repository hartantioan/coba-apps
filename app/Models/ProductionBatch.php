<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_batches';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'item_id',
        'place_id',
        'warehouse_id',
        'area_id',
        'item_shading_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'qty_real',
        'total'
    ];

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading', 'item_shading_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function area(){
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function price(){
        $price = $this->total / $this->qty_real;
        return $price;
    }

    public function priceByQty($qtyuom){
        $total = $this->total;
        $price = 0;
        $qty = $this->qtyBalance() - $qtyuom;
        if($qty <= 0){
            $total = $total - round($this->qtyUsed() * $this->price(),2);
            $price = $total / $qtyuom;
        }else{
            $price = $this->price();
        }
        
        return $price;
    }

    public function getDate(){
        $date = date('d/m/Y',strtotime($this->created_at));
        if($this->lookable_type == 'production_receive_details'){
            $date = date('d/m/Y',strtotime($this->lookable->productionReceive->post_date));
        }elseif($this->lookable_type == 'production_fg_receive_details'){
            $date = date('d/m/Y',strtotime($this->lookable->productionFgReceive->post_date));
        }elseif($this->lookable_type == 'production_handover_details'){
            $date = date('d/m/Y',strtotime($this->lookable->productionHandover->post_date));
        }
        return $date;
    }

    public function totalById($id){
        $total = $this->total;
        $qty = $this->qty_real;
        $qtyused = 0;
        $dataused = $this->productionBatchUsage()->where('id','<',$id)->get();
        $qtycheck = $this->productionBatchUsage()->where('id','=',$id)->sum('qty');
        foreach($dataused as $row){
            $total -= round($row->qty * $this->price(),2);
            $qtyused += $row->qty;
        }
        if(($qty - ($qtyused + $qtycheck)) <= 0){
            $totalnew = $total;
        }else{
            $totalnew = round($qtycheck * $this->price(),2);
        }
        
        return $totalnew;
    }

    public function qtyUsed(){
        $total = $this->productionBatchUsage()->sum('qty');
        return $total;
    }

    public function qtyBalance(){
        $total = $this->qty_real - $this->qtyUsed();
        return $total;
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function productionBatchUsage()
    {
        return $this->hasMany('App\Models\ProductionBatchUsage');
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id');
    }

    public static function generateCode($type,$line,$group){
        $newcode = '';
        if($type == 'normal'){
            $newcode .= 'PD'.date('y');
        }elseif($type == 'powder'){
            $newcode .= 'PW'.date('y');
        }
        $query = ProductionBatch::selectRaw('SUBSTRING(code,5,8) as code')
            ->whereRaw("code LIKE '$newcode%'")
            ->orderByDesc('id')
            ->limit(1)
            ->get();
        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return $newcode.$no.'.'.strtoupper($line).strtoupper($group);
    }

    public static function generateCodeWithNumber($type,$line,$group,$number){
        $newcode = '';
        if($type == 'normal'){
            $newcode .= 'PD'.date('y');
        }elseif($type == 'powder'){
            $newcode .= 'PW'.date('y');
        }
        $query = ProductionBatch::selectRaw('SUBSTRING(code,5,8) as code')
            ->whereRaw("code LIKE '$newcode%'")
            ->orderByDesc('id')
            ->limit(1)
            ->get();
        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1 + $number;
        } else {
            $code = 1 + $number;
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return $newcode.$no.'.'.strtoupper($line).strtoupper($group);
    }

    public static function getLatestCodeFg($yearmonth){
        $query = ProductionBatch::selectRaw('RIGHT(code, 5) as code')
            ->whereRaw("RIGHT(code, 9) LIKE '$yearmonth%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00001';
        }

        $no = str_pad($code, 5, 0, STR_PAD_LEFT);

        return $yearmonth.$no;
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }
}