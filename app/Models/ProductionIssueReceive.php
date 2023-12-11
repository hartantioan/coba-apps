<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionIssueReceive extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_issue_receives';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'production_order_id',
        'post_date',
        'document',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function productionOrder()
    {
        return $this->belongsTo('App\Models\ProductionOrder', 'production_order_id', 'id')->withTrashed();
    }

    public function productionIssueReceiveDetail()
    {
        return $this->hasMany('App\Models\ProductionIssueReceiveDetail');
    }

    public function productionIssueReceiveCost()
    {
        return $this->productionIssueReceiveDetail()->where('lookable_type','coas');
    }

    public function productionIssueReceiveItem()
    {
        return $this->productionIssueReceiveDetail()->where('lookable_type','items');
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
        $query = ProductionIssueReceive::selectRaw('RIGHT(code, 8) as code')
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

    public function dataView(){
        $arrData = [];

        foreach($this->productionIssueReceiveDetail as $row){
            $cekIndex = $this->checkArray($row->production_schedule_detail_id,$arrData);
            if($cekIndex >= 0){
                //do nothing
            }else{
                $arrData[] = [
                    'ps_id'             => $row->productionScheduleDetail->productionSchedule->id,
                    'psd_id'            => $row->production_schedule_detail_id,
                    'production_date'   => date('d/m/y',strtotime($row->productionScheduleDetail->production_date)),
                    'shift'             => $row->productionScheduleDetail->shift->code,
                    'place_code'        => $row->productionScheduleDetail->productionSchedule->place->code,    
                    'machine_code'      => $row->productionScheduleDetail->productionSchedule->machine->code,
                    'item_name'         => $row->productionScheduleDetail->item->name,
                    'qty'               => number_format($row->productionScheduleDetail->qty,3,',','.'),
                    'unit'              => $row->productionScheduleDetail->item->uomUnit->code,
                    'details_issue'     => [],
                    'details_receive'   => [],
                    'rowspan'           => 1,
                ];
            }
        }

        foreach($this->productionIssueReceiveDetail as $row){
            $cekIndex = $this->checkArray($row->production_schedule_detail_id,$arrData);
            if($cekIndex >= 0){
                if($row->type == '1'){
                    $arrData[$cekIndex]['details_issue'][] = [
                        'type'          => $row->type,
                        'name'          => $row->lookable->name,
                        'nominal'       => number_format($row->nominal,3,',','.'),
                        'batch_no'      => '',
                        'unit'          => $row->lookable_type == 'items' ? $row->lookable->uomUnit->code : '-',
                        'lookable_id'   => $row->lookable_id,
                        'lookable_type' => $row->lookable_type,
                    ];
                }elseif($row->type == '2'){
                    $arrData[$cekIndex]['details_receive'][] = [
                        'type'          => $row->type,
                        'name'          => $row->lookable->name,
                        'nominal'       => number_format($row->nominal,3,',','.'),
                        'batch_no'      => $row->batch_no,
                        'unit'          => $row->lookable->uomUnit->code,
                        'lookable_id'   => $row->lookable_id,
                        'lookable_type' => $row->lookable_type,
                    ];
                }
                $arrData[$cekIndex]['rowspan']++;
            }
        }

        return $arrData;
    }
}
