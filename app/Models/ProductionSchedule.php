<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionSchedule extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_schedules';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'place_id',
        'line_id',
        'post_date',
        'document',
        'status',
        'note',
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

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function productionScheduleDetail()
    {
        return $this->hasMany('App\Models\ProductionScheduleDetail');
    }

    public function productionScheduleTarget()
    {
        return $this->hasMany('App\Models\ProductionScheduleTarget');
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
        $query = ProductionSchedule::selectRaw('RIGHT(code, 8) as code')
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

    public function productionOrder(){
        return $this->hasMany('App\Models\ProductionOrder','production_schedule_id','id');
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->productionOrder()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function createProductionOrder(){
        $lastSegment = 'production_order';
        foreach($this->productionScheduleDetail()->where('status','1')->get() as $row){
            $menu = Menu::where('url', $lastSegment)->first();
            $newCode=ProductionOrder::generateCode($menu->document_code.date('y',strtotime($this->post_date)).substr($this->code,7,2));
            
            $query = ProductionOrder::create([
                'code'			                => $newCode,
                'user_id'		                => $this->user_id,
                'company_id'                    => $this->company_id,
                'production_schedule_id'	    => $this->id,
                'production_schedule_detail_id'	=> $row->id,
                'warehouse_id'                  => $row->warehouse_id,
                'post_date'                     => $this->post_date,
                'note'                          => $row->note,
                'standard_item_cost'            => 0,
                'standard_resource_cost'        => 0,
                'standard_product_cost'         => 0,
                'actual_item_cost'              => 0,
                'actual_resource_cost'          => 0,
                'total_product_cost'            => 0,
                'planned_qty'                   => $row->qty,
                'completed_qty'                 => 0,
                'rejected_qty'                  => 0,
                'total_production_time'         => 0,
                'total_additional_time'         => 0,
                'total_run_time'                => 0,
                'status'                        => '2',
            ]);

            CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Order Produksi No. '.$query->code,'Pengajuan Order Produksi No. '.$query->code,$this->user_id);

            activity()
                    ->performedOn(new ProductionOrder())
                    ->causedBy($this->user_id)
                    ->withProperties($query)
                    ->log('Add / edit production order from production schedule approval.');
        }
    }
}
