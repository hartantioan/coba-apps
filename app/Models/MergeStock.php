<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MergeStock extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'merge_stocks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'post_date',
        'document',
        'note',
        'grandtotal',
        'item_id',
        'item_shading_id',
        'qty',
        'to_place_id',
        'to_warehouse_id',
        'to_area_id',
        'item_stock_id',
        'batch_no',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'delete_date',
        'done_id',
        'done_note',
        'done_date',
    ];

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = MergeStock::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('code')
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

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function mergeStockDetail()
    {
        return $this->hasMany('App\Models\MergeStockDetail');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','item_stock_id','id');
    }

    public function toPlace(){
        return $this->belongsTo('App\Models\Place','to_place_id','id')->withTrashed();
    }

    public function toWarehouse(){
        return $this->belongsTo('App\Models\Warehouse','to_warehouse_id','id')->withTrashed();
    }

    public function toArea(){
        return $this->belongsTo('App\Models\Area','to_area_id','id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function hasChildDocument(){
        $hasRelation = false;

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

    public function productionBatch(){
        return $this->hasOne('App\Models\ProductionBatch','lookable_id','id')->where('lookable_type',$this->table);
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

    public function updateGrandtotal(){
        $total = $this->mergeStockDetail()->sum('total');
        $this->update([
            'grandtotal'    => round($total,2)
        ]);
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','lookable_id','id')->where('lookable_type',$this->table)->whereNull('detailable_type')->whereNull('detailable_id')->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
