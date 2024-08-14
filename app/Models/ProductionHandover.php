<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionHandover extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_handovers';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'production_fg_receive_id',
        'post_date',
        'document',
        'note',
        'status',
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

    public function productionFgReceive()
    {
        return $this->belongsTo('App\Models\ProductionFgReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function productionHandoverDetail()
    {
        return $this->hasMany('App\Models\ProductionHandoverDetail');
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

    public function attachment() 
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function deleteFile(){
		if(Storage::exists($this->document)) {
            Storage::delete($this->document);
        }
	}

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = ProductionHandover::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
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

        foreach($this->productionHandoverDetail as $row){
            if($row->productionBatch()->exists()){
                if($row->productionBatch->productionBatchUsage()->exists()){
                    $hasRelation = true;
                }
            }
        }

        return $hasRelation;
    }

    function checkArray($val,$array){
        $index = -1;
        foreach($array as $key => $row){
            if($row['psd_id'] == $val){
                $index = $key;
            }
        }
        return $index;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function isOpenPeriod(){
        $monthYear = substr($this->post_date, 0, 7); // '2023-02'

        // Query the LockPeriod model
        $see = LockPeriod::where('month', $monthYear)
                        ->whereIn('status_closing', ['2','3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }

    public function qty(){
        $qty = $this->productionHandoverDetail()->sum('qty');
        return $qty;
    }

    public function hasBalanceReceiveFg(){
        $qtyused = $this->productionFgReceive->qtyUsed();
        $qty = $this->productionFgReceive->qtySell() - $qtyused;
        if($qty > 0){
            return true;
        }else{
            return false;
        }
    }

    public function recalculate(){
        /* foreach($this->productionHandoverDetail as $key => $row){
            $prfgd = ProductionFgReceiveDetail::find($row->production_fg_receive_detail_id);
            if($prfgd){
                $qtyuom = $row->qty * $prfgd->conversion;
                $prodbatch = ProductionBatch::find($prfgd->productionBatch->id);
                $rowcost = round($prodbatch->priceByQty($qtyuom) * $qtyuom,2);
                $itemShading = ItemShading::where('item_id',$request->arr_item_id[$key])->where('code',$prfgd->shading)->first();

                if(!$itemShading){
                    $itemShading = ItemShading::create([
                        'item_id'   => $request->arr_item_id[$key],
                        'code'      => $prfgd->shading,
                    ]);
                }

                $querydetail = ProductionHandoverDetail::create([
                    'production_handover_id'            => $query->id,
                    'production_fg_receive_detail_id'   => $prfgd->id,
                    'item_id'                           => $request->arr_item_id[$key],
                    'qty'                               => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                    'shading'                           => $prfgd->shading,
                    'place_id'                          => $request->arr_place[$key],
                    'warehouse_id'                      => $request->arr_warehouse[$key],
                    'area_id'                           => $request->arr_area_id[$key],
                    'total'                             => $rowcost,
                    'item_shading_id'                   => $itemShading->id,
                ]);
                
                ProductionBatchUsage::create([
                    'production_batch_id'   => $prfgd->productionBatch->id,
                    'lookable_type'         => $querydetail->getTable(),
                    'lookable_id'           => $querydetail->id,
                    'qty'                   => $qtyuom,
                ]);

                CustomHelper::updateProductionBatch($prfgd->productionBatch->id,$qtyuom,'OUT');

                ProductionBatch::create([
                    'code'          => $querydetail->productionFgReceiveDetail->pallet_no,
                    'item_id'       => $querydetail->item_id,
                    'place_id'      => $request->arr_place[$key],
                    'warehouse_id'  => $request->arr_warehouse[$key],
                    'area_id'       => $request->arr_area_id[$key],
                    'item_shading_id'=> $itemShading->id,
                    'lookable_type' => $querydetail->getTable(),
                    'lookable_id'   => $querydetail->id,
                    'qty'           => $qtyuom,
                    'qty_real'      => $qtyuom,
                    'total'         => $rowcost,
                ]);
            }
        } */
    }

    public function getRequesterByItem($item_id){
        return $this->user->name;
    }
}
