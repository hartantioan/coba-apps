<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\ResetCogsHelper;
use App\Jobs\ResetCogsNew;
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

    public function productionIssue()
    {
        return $this->hasMany('App\Models\ProductionIssue','production_receive_id','id')->whereIn('status',['1','2','3']);
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
                        ->whereIn('status_closing', ['2','3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }

    public function totalIssueExcept($id){
        $total = 0;
        foreach($this->productionReceiveIssue()->where('production_issue_id','<>',$id)->get() as $row){
            $total += $row->productionIssue->total();
        }
        return $total;
    }

    public function total(){
        $total = $this->productionReceiveDetail()->sum('total');
        return $total;
    }

    public function qty(){
        $qty = $this->productionReceiveDetail()->sum('qty');
        return $qty;
    }

    public function qtyReject(){
        $qty = $this->productionReceiveDetail()->sum('qty_reject');
        return $qty;
    }

    public function recalculate(){
        $totalQty = 0;
        foreach($this->productionReceiveDetail as $row){
            $totalQty += $row->qty;
        }
        $totalIssue = 0;
        foreach($this->productionReceiveIssue as $key => $row){
            $totalIssue += $row->productionIssue->total();
        }
        foreach($this->productionReceiveDetail as $row){
            $rowtotal = round(($row->qty / $totalQty) * $totalIssue,2);
            foreach($row->productionBatch as $rowbatch){
                $totalbatch = round(($rowbatch->qty_real / $row->qty) * $rowtotal,2);
                $rowbatch->update([
                    'total' => $totalbatch,
                ]);
            }
            $row->update([
                'total' => $rowtotal,
            ]);
        }
    }

    public function recalculateAndResetCogs(){
        $totalQty = 0;
        foreach($this->productionReceiveDetail as $row){
            $totalQty += $row->qty;
        }
        $totalIssue = 0;
        foreach($this->productionReceiveIssue as $key => $row){
            $totalIssue += $row->productionIssue->total();
        }
        foreach($this->productionReceiveDetail as $row){
            $rowtotal = round(($row->qty / $totalQty) * $totalIssue,2);
            foreach($row->productionBatch as $rowbatch){
                $totalbatch = round(($rowbatch->qty_real / $row->qty) * $rowtotal,2);
                $rowbatch->update([
                    'total' => $totalbatch,
                ]);
                ResetCogsHelper::gas($this->post_date,$this->company_id,$this->place_id,$row->item_id,NULL,NULL,$rowbatch->id);
            }
            $row->update([
                'total' => $rowtotal,
            ]);
        }
    }

    public function createProductionIssue(){
        $countbackflush = $this->productionOrderDetail->productionScheduleDetail->bom->bomDetail()->whereHas('bomAlternative',function($query){
            $query->whereNotNull('is_default');
        })->where('issue_method','2')->count();

        $countbomstandard = $this->productionOrderDetail->productionScheduleDetail->bom->whereHas('bomStandard')->count();

        if($countbackflush > 0 || $countbomstandard > 0){
            $lastSegment = 'production_issue';
            $menu = Menu::where('url', $lastSegment)->first();
            $newCode=ProductionIssue::generateCode($menu->document_code.date('y').substr($this->code,7,2));
            
            $query = ProductionIssue::create([
                'code'			            => $newCode,
                'user_id'		            => session('bo_id'),
                'company_id'                => $this->company_id,
                'production_order_detail_id'=> $this->production_order_detail_id,
                'production_receive_id'     => $this->id,
                'place_id'                  => $this->place_id,
                'shift_id'                  => $this->shift_id,
                'group'                     => $this->group,
                'line_id'                   => $this->line_id,
                'post_date'                 => $this->post_date,
                'note'                      => 'PRODUCTION RECEIVE NO. '.$this->code.' ( '.$this->productionOrderDetail->productionScheduleDetail->item->code.' - '.$this->productionOrderDetail->productionScheduleDetail->item->name.' )',
                'status'                    => '1',
            ]);

            foreach($this->productionReceiveDetail as $key => $row){
                $bomAlternative = BomAlternative::whereHas('bom',function($query)use($row){
                    $query->where('item_id',$row->item_id)->orderByDesc('created_at');
                })->whereNotNull('is_default')->first();

                if($bomAlternative){
                    foreach($bomAlternative->bomDetail()->where('issue_method','2')->get() as $rowbom){
                        $querydetail = ProductionIssueDetail::create([
                            'production_issue_id'           => $query->id,
                            'production_order_detail_id'    => $this->production_order_detail_id,
                            'lookable_type'                 => $rowbom->lookable_type,
                            'lookable_id'                   => $rowbom->lookable_id,
                            'bom_id'                        => $rowbom->bom_id,
                            'bom_detail_id'                 => $rowbom->id,
                        ]);
                        $nominal = 0;
                        $total = 0;
                        $itemstock = NULL;
                        $qty_planned = round($rowbom->qty * ($row->qty_planned / $rowbom->bom->qty_output),3);
                        $nominal_planned = 0;
                        $total_planned = 0;
                        if($rowbom->lookable_type == 'items'){
                            $item = Item::find($rowbom->lookable_id);
                            if($item){
                                if($item->productionBatchMoreThanZero()->exists()){
                                    $totalbatch = 0;
                                    $totalneeded = round($rowbom->qty * (($row->qty + $row->qty_reject) / $rowbom->bom->qty_output),3);
                                    foreach($item->productionBatchMoreThanZero()->orderBy('created_at')->get() as $rowbatch){
                                        $qtyused = 0;
                                        if($rowbatch->qty > $totalneeded){
                                            $qtyused = $totalneeded;
                                            $totalneeded -= $rowbatch->qty;
                                        }else{
                                            $qtyused = $rowbatch->qty;
                                        }
                                        if($qtyused > 0){
                                            $totalbatch += round($rowbatch->price() * $qtyused,2);
                                            ProductionBatchUsage::create([
                                                'production_batch_id'   => $rowbatch->id,
                                                'lookable_type'         => $querydetail->getTable(),
                                                'lookable_id'           => $querydetail->id,
                                                'qty'                   => $qtyused,
                                            ]);
                                            CustomHelper::updateProductionBatch($rowbatch->id,$qtyused,'OUT');
                                        }else{
                                            break;
                                        }
                                    }
                                    if($bomAlternative->bom->group == '1'){
                                        $price = $rowbom->lookable->priceNowProduction($this->place_id,$this->post_date);
                                        $totalbatch = round(($row->qty + $row->qty_reject) * $price,2);
                                    }else{
                                        $price = $totalbatch / round($rowbom->qty * (($row->qty + $row->qty_reject) / $rowbom->bom->qty_output),3);
                                    }
                                    $total = $totalbatch;
                                    $nominal = $price;
                                }else{
                                    $price = $item->priceNowProduction($this->place_id,$this->post_date);
                                    $total = round(round($rowbom->qty * (($row->qty + $row->qty_reject) / $rowbom->bom->qty_output),3) * $price,2);
                                    $nominal = $price;
                                    $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$this->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                                }
                                $nominal_planned = $nominal;
                            }
                        }elseif($rowbom->lookable_type == 'resources'){
                            $total = round(round($rowbom->qty * (($row->qty + $row->qty_reject) / $rowbom->bom->qty_output),3) * $rowbom->nominal,2);
                            $nominal = $rowbom->nominal;
                            $nominal_planned = $rowbom->nominal;
                        }
                        $total_planned = round($nominal_planned * $qty_planned,2);
                        $querydetail->update([
                            'qty'                           => round($rowbom->qty * (($row->qty + $row->qty_reject) / $rowbom->bom->qty_output),3),
                            'nominal'                       => $nominal,
                            'total'                         => $total,
                            'qty_bom'                       => $rowbom->qty,
                            'nominal_bom'                   => $rowbom->nominal,
                            'total_bom'                     => $rowbom->total,
                            'qty_planned'                   => $qty_planned,
                            'nominal_planned'               => $nominal_planned,
                            'total_planned'                 => $total_planned,
                            'from_item_stock_id'            => $itemstock ? $itemstock->id : NULL,
                            'place_id'                      => $itemstock ? $itemstock->place_id : $this->place_id,
                            'warehouse_id'                  => $itemstock ? $itemstock->warehouse_id : NULL,
                        ]);
                    }

                    if($bomAlternative->bom->bomStandard()->exists()){
                        foreach($bomAlternative->bom->bomStandard->bomStandardDetail as $rowbom){
                            $querydetail = ProductionIssueDetail::create([
                                'production_issue_id'           => $query->id,
                                'production_order_detail_id'    => $this->production_order_detail_id,
                                'lookable_type'                 => $rowbom->lookable_type,
                                'lookable_id'                   => $rowbom->lookable_id,
                                'bom_id'                        => $bomAlternative->bom_id,
                            ]);
                            $nominal = 0;
                            $total = 0;
                            $itemstock = NULL;
                            $qty_planned = round($rowbom->qty * $row->qty_planned,3);
                            $nominal_planned = 0;
                            $total_planned = 0;
                            if($rowbom->lookable_type == 'items'){
                                $item = Item::find($rowbom->lookable_id);
                                if($item){
                                    if($item->productionBatchMoreThanZero()->exists()){
                                        $totalbatch = 0;
                                        $totalneeded = round($rowbom->qty * ($row->qty + $row->qty_reject),3);
                                        foreach($item->productionBatchMoreThanZero()->orderBy('created_at')->get() as $rowbatch){
                                            $qtyused = 0;
                                            if($rowbatch->qty > $totalneeded){
                                                $qtyused = $totalneeded;
                                                $totalneeded -= $rowbatch->qty;
                                            }else{
                                                $qtyused = $rowbatch->qty;
                                            }
                                            if($qtyused > 0){
                                                $totalbatch += round($rowbatch->price() * $qtyused,2);
                                                ProductionBatchUsage::create([
                                                    'production_batch_id'   => $rowbatch->id,
                                                    'lookable_type'         => $querydetail->getTable(),
                                                    'lookable_id'           => $querydetail->id,
                                                    'qty'                   => $qtyused,
                                                ]);
                                                CustomHelper::updateProductionBatch($rowbatch->id,$qtyused,'OUT');
                                            }else{
                                                break;
                                            }
                                        }
                                        if($bomAlternative->bom->group == '1'){
                                            $price = $rowbom->lookable->priceNowProduction($this->place_id,$this->post_date);
                                            $totalbatch = round(($row->qty + $row->qty_reject) * $price,2);
                                        }else{
                                            $price = $totalbatch / round($rowbom->qty * ($row->qty + $row->qty_reject),3);
                                        }
                                        $total = $totalbatch;
                                        $nominal = $price;
                                    }else{
                                        $price = $item->priceNowProduction($this->place_id,$this->post_date);
                                        $total = round(round($rowbom->qty * ($row->qty + $row->qty_reject),3) * $price,2);
                                        $nominal = $price;
                                        $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$this->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                                    }
                                    $nominal_planned = $nominal;
                                }
                            }elseif($rowbom->lookable_type == 'resources'){
                                $total = round(round($rowbom->qty * ($row->qty + $row->qty_reject),3) * $rowbom->nominal,2);
                                $nominal = $rowbom->nominal;
                                $nominal_planned = $rowbom->nominal;
                            }
                            $total_planned = round($nominal_planned * $qty_planned,2);
                            $querydetail->update([
                                'qty'                           => round($rowbom->qty * ($row->qty + $row->qty_reject),3),
                                'nominal'                       => $nominal,
                                'total'                         => $total,
                                'qty_bom'                       => $rowbom->qty,
                                'nominal_bom'                   => $rowbom->nominal,
                                'total_bom'                     => $rowbom->total,
                                'qty_planned'                   => $qty_planned,
                                'nominal_planned'               => $nominal_planned,
                                'total_planned'                 => $total_planned,
                                'from_item_stock_id'            => $itemstock ? $itemstock->id : NULL,
                                'place_id'                      => $itemstock ? $itemstock->place_id : $this->place_id,
                                'warehouse_id'                  => $itemstock ? $itemstock->warehouse_id : NULL,
                                'cost_distribution_id'          => $rowbom->cost_distribution_id ?? NULL,
                            ]);
                        }
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

    public function voidProductionIssue(){
        if($this->productionIssue()->exists()){
            foreach($this->productionIssue as $row){
                $tempStatus = $row->status;

                $row->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => 'Ditutup otomatis dari Production Receive '.$this->code,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                if(in_array($tempStatus,['2','3'])){
                    CustomHelper::removeJournal($row->getTable(),$row->id);
                    CustomHelper::removeCogs($row->getTable(),$row->id);
                }

                foreach($row->productionIssueDetail as $rowdetail){
                    foreach($rowdetail->productionBatchUsage as $rowdetailkuy){
                        CustomHelper::updateProductionBatch($rowdetailkuy->production_batch_id,$rowdetailkuy->qty,'IN');
                        $rowdetailkuy->delete();
                    }
                }
    
                activity()
                    ->performedOn(new ProductionIssue())
                    ->causedBy(session('bo_id'))
                    ->withProperties($row)
                    ->log('Void the production issue data from production receive');
    
                CustomHelper::sendNotification($row->getTable(),$row->id,'Production Issue No. '.$row->code.' telah ditutup otomatis dari Production Receive '.$this->code.'.','Production Issue No. '.$row->code.' telah ditutup otomatis dari Production Receive '.$this->code.'.',$row->user_id);
                CustomHelper::removeApproval($row->getTable(),$row->id);
            }
        }
    }

    public function getRequesterByItem($item_id){
        return $this->user->name;
    }
}
