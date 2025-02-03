<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GoodReceive extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receives';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'good_issue_id',
        'post_date',
        'currency_id',
        'currency_rate',
        'note',
        'document',
        'grandtotal',
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

    public function hasBatchProduction(){
        $has = false;
        $count = $this->goodReceiveDetail()->where(function($query){
            $query->whereNotNull('batch_no')->orWhere('batch_no','!=','');
        })->count();
        if($count > 0){
            $has = true;
        }
        return $has;
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function goodIssue()
    {
        return $this->belongsTo('App\Models\GoodIssue', 'good_issue_id', 'id');
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function voidUser(){
        return $this->belongsTo('App\Models\User','void_id','id')->withTrashed();
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function goodReceiveDetail()
    {
        return $this->hasMany('App\Models\GoodReceiveDetail');
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
        $query = GoodReceive::selectRaw('RIGHT(code, 8) as code')
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

    public function getRequesterByItem($item_id){
        return $this->user->name;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->goodReceiveDetail as $row){
            if($row->productionBatch()->exists()){
                if($row->productionBatch->productionBatchUsage()->exists()){
                    $hasRelation = true;
                }
            }
        }

        return $hasRelation;
    }

    public function totalQty(){
        $total = $this->goodReceiveDetail()->sum('qty');
        return $total;
    }

    public function updateGrandtotal(){
        $total = 0;

        foreach($this->goodReceiveDetail as $row){
            $total += round($row->total,2);
        }

        $this->update([
            'grandtotal'    => $total
        ]);
    }

    public function upgradeDetail(){
        $total = $this->goodIssue()->exists() ? $this->goodIssue->grandtotal : $this->total;
        $balance = $total;
        foreach($this->goodReceiveDetail as $row){
            $rowtotal = round(($row->qty / $this->totalQty()) * $total,2);
            if(($balance - $rowtotal) >= 0){
                $row->update([
                    'total' => $rowtotal,
                    'price' => round($rowtotal / $row->qty,5),
                ]);
            }else{
                $row->update([
                    'total' => $balance,
                    'price' => round($balance / $row->qty,5),
                ]);
            }
            $balance -= $rowtotal;
            CustomHelper::sendCogs($this->table,
                $this->id,
                $row->place->company_id,
                $row->place_id,
                $row->warehouse_id,
                $row->item_id,
                $row->qty,
                round($row->total * $this->currency_rate,2),
                'IN',
                $this->post_date,
                $row->area_id,
                $row->item_shading_id ? $row->item_shading_id : NULL,
                $row->productionBatch()->exists() ? $row->productionBatch->id : ($row->itemStock()->exists() ? $row->itemStock->production_batch_id : NULL),
                $row->getTable(),
                $row->id,
            );
        }
        $this->updateGrandtotal();
    }
}
