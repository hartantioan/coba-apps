<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionReceive extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_receives';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'production_order_detail_id',
        'place_id',
        'shift_id',
        'group',
        'line_id',
        'post_date',
        'start_process_time',
        'end_process_time',
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

    public function productionOrderDetail()
    {
        return $this->belongsTo('App\Models\ProductionOrderDetail', 'production_order_detail_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }
    
    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Shift', 'shift_id', 'id')->withTrashed();
    }

    public function productionReceiveDetail()
    {
        return $this->hasMany('App\Models\ProductionReceiveDetail');
    }

    public function productionReceiveIssue()
    {
        return $this->hasMany('App\Models\ProductionReceiveIssue');
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
        $query = ProductionReceive::selectRaw('RIGHT(code, 8) as code')
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
                        ->whereIn('status_closing', ['3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }

    public function total(){
        $total = $this->productionReceiveDetail()->sum('total');
        return $total;
    }

    public function qty(){
        $qty = $this->productionReceiveDetail()->sum('qty');
        return $qty;
    }

    public function createProductionIssue(){
        $countbackflush = $this->productionOrderDetail->productionScheduleDetail->bom->bomDetail()->whereHas('bomAlternative',function($query){
            $query->whereNotNull('is_default');
        })->where('issue_method','2')->count();

        if($countbackflush > 0){
            $lastSegment = 'production_issue';
            $menu = Menu::where('url', $lastSegment)->first();
            $newCode=ProductionIssue::generateCode($menu->document_code.date('y').substr($this->code,7,2));
            
            $query = ProductionIssue::create([
                'code'			            => $newCode,
                'user_id'		            => session('bo_id'),
                'company_id'                => $this->company_id,
                'production_order_detail_id'=> $this->production_order_detail_id,
                'production_fg_receive_id'  => $this->id,
                'place_id'                  => $this->place_id,
                'shift_id'                  => $this->shift_id,
                'group'                     => $this->group,
                'line_id'                   => $this->line_id,
                'post_date'                 => date('Y-m-d'),
                'note'                      => 'Dibuat otomatis dari Production Receive No. '.$this->code,
                'status'                    => '1',
            ]);

            foreach($this->productionReceiveDetail as $key => $row){
                
                $bomAlternative = BomAlternative::whereHas('bom',function($query)use($row){
                    $query->where('item_id',$row->item_id)->orderByDesc('created_at');
                })->whereNotNull('is_default')->first();

                if($bomAlternative){
                    foreach($bomAlternative->bomDetail()->where('issue_method','2')->get() as $rowbom){
                        $nominal = 0;
                        $total = 0;
                        $itemstock = NULL;
                        if($rowbom->lookable_type == 'items'){
                            $item = Item::find($rowbom->lookable_id);
                            if($item){
                                $price = $item->priceNowProduction($this->place_id,$this->post_date);
                                $total = round(round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3) * $price,2);
                                $nominal = $price;
                                $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$this->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                            }
                        }elseif($rowbom->lookable_type == 'resources'){
                            $total = round(round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3) * $rowbom->nominal,2);
                            $nominal = $rowbom->nominal;
                        }
                        $querydetail = ProductionIssueDetail::create([
                            'production_issue_id'           => $query->id,
                            'production_order_detail_id'    => $this->production_order_detail_id,
                            'lookable_type'                 => $rowbom->lookable_type,
                            'lookable_id'                   => $rowbom->lookable_id,
                            'bom_id'                        => $rowbom->bom_id,
                            'bom_detail_id'                 => $rowbom->id,
                            'qty'                           => round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3),
                            'nominal'                       => $nominal,
                            'total'                         => $total,
                            'qty_bom'                       => round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3),
                            'nominal_bom'                   => $rowbom->nominal,
                            'total_bom'                     => $total,
                            'qty_planned'                   => round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3),
                            'nominal_planned'               => $rowbom->nominal,
                            'total_planned'                 => $total,
                            'from_item_stock_id'            => $itemstock ? $itemstock->id : NULL,
                        ]);
                    }
                }
            }

            if($query){
                ProductionReceiveIssue::create([
                    'production_receive_id' => $this->id,
                    'production_issue_id'   => $query->id,
                ]);

                CustomHelper::sendApproval($query->getTable(),$query->id,'Production Issue No. '.$query->code);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Issue No. '.$query->code,'Pengajuan Production Issue No. '.$query->code.' dari Production Receive No. '.$this->code,session('bo_id'));

                activity()
                    ->performedOn(new ProductionIssue())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit issue production.');
            }
        }
    }
}
