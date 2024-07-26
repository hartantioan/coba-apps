<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionFgReceive extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_fg_receives';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'production_order_detail_id',
        'item_id',
        'place_id',
        'line_id',
        'shift_id',
        'group',
        'item_unit_id',
        'qty',
        'post_date',
        'qty_reject',
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

    public function productionIssue(){
        return $this->hasMany('App\Models\ProductionIssue')->whereIn('status',['1','2','3']);
    }

    public function productionIssueList(){
        $arr = [];
        foreach($this->productionIssue as $row){
            $arr[] = $row->code;
        }
        return implode(',',$arr);
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

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function productionFgReceiveDetail()
    {
        return $this->hasMany('App\Models\ProductionFgReceiveDetail');
    }

    public function qty(){
        $qty = $this->productionFgReceiveDetail()->sum('qty');
        return $qty;
    }

    public function qtySell(){
        $qty = $this->productionFgReceiveDetail()->sum('qty_sell');
        return $qty;
    }

    public function productionHandover()
    {
        return $this->hasMany('App\Models\ProductionHandover')->whereIn('status',['1','2','3']);
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

    public function total(){
        $total = $this->productionFgReceiveDetail()->sum('total');
        return $total;
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
        $query = ProductionFgReceive::selectRaw('RIGHT(code, 8) as code')
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

        if($this->productionIssue()->exists()){
            $hasRelation = true;
        }

        if($this->productionHandover()->exists()){
            $hasRelation = true;
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
                        ->whereIn('status_closing', ['3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }

    public function productionBatchUsage(){
        return $this->hasMany('App\Models\ProductionBatchUsage','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function totalBatchUsage(){
        $total = $this->productionBatchUsage()->sum('qty');
        return $total;
    }

    public function createProductionIssue(){
        $lastSegment = 'production_issue';
        $menu = Menu::where('url', $lastSegment)->first();

        foreach($this->productionBatchUsage as $row){
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
                'note'                      => 'PRODUCTION RECEIVE FG NO. '.$this->code.' ( '.$this->productionOrderDetail->productionScheduleDetail->item->code.' - '.$this->productionOrderDetail->productionScheduleDetail->item->name.' )',
                'status'                    => '1',
            ]);
            $price = $row->productionBatch->price();
            $rowtotal = round($price * $row->qty,2);
            $itemStock = ItemStock::where('item_id',$row->productionBatch->item_id)->where('place_id',$row->productionBatch->place_id)->where('warehouse_id',$row->productionBatch->warehouse_id)->where('production_batch_id',$row->productionBatch->id)->first();
            $querydetail = ProductionIssueDetail::create([
                'production_issue_id'           => $query->id,
                'production_order_detail_id'    => $this->production_order_detail_id,
                'lookable_type'                 => 'items',
                'lookable_id'                   => $row->productionBatch->item_id,
                'bom_id'                        => NULL,
                'bom_detail_id'                 => NULL,
                'qty'                           => $row->qty,
                'nominal'                       => $price,
                'total'                         => $rowtotal,
                'qty_bom'                       => 0,
                'nominal_bom'                   => 0,
                'total_bom'                     => 0,
                'qty_planned'                   => $row->qty,
                'nominal_planned'               => $price,
                'total_planned'                 => $rowtotal,
                'from_item_stock_id'            => $itemStock->id,
                'place_id'                      => $itemStock->place_id,
                'warehouse_id'                  => $itemStock->warehouse_id,
            ]);
            if($query){
                CustomHelper::sendApproval($query->getTable(),$query->id,'Production Issue No. '.$query->code);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Issue No. '.$query->code,'Pengajuan Production Issue No. '.$query->code.' dari Production Receive FG No. '.$this->code,session('bo_id'));
    
                activity()
                    ->performedOn(new ProductionIssue())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit issue production.');
            }
        }

        $query = null;
        
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
            'note'                      => 'PRODUCTION RECEIVE FG NO. '.$this->code.' ( '.$this->productionOrderDetail->productionScheduleDetail->item->code.' - '.$this->productionOrderDetail->productionScheduleDetail->item->name.' )',
            'status'                    => '1',
        ]);

        foreach($this->productionBatchUsage as $row){
            $price = $row->productionBatch->price();
            $total = round($price * $row->qty,2);
            $itemStock = ItemStock::where('item_id',$row->productionBatch->item_id)->where('place_id',$row->productionBatch->place_id)->where('warehouse_id',$row->productionBatch->warehouse_id)->where('production_batch_id',$row->productionBatch->id)->first();
            $querydetail = ProductionIssueDetail::create([
                'production_issue_id'           => $query->id,
                'production_order_detail_id'    => $this->production_order_detail_id,
                'lookable_type'                 => 'items',
                'lookable_id'                   => $row->productionBatch->item_id,
                'bom_id'                        => NULL,
                'bom_detail_id'                 => NULL,
                'qty'                           => $row->qty,
                'nominal'                       => $price,
                'total'                         => $total,
                'qty_bom'                       => 0,
                'nominal_bom'                   => 0,
                'total_bom'                     => 0,
                'qty_planned'                   => $row->qty,
                'nominal_planned'               => $price,
                'total_planned'                 => $total,
                'from_item_stock_id'            => $itemStock->id,
                'place_id'                      => $itemStock->place_id,
                'warehouse_id'                  => $itemStock->warehouse_id,
                'is_wip'                        => '1',
            ]);
        }

        foreach($this->productionFgReceiveDetail as $key => $row){
            
            $bomAlternative = BomAlternative::whereHas('bom',function($query)use($row){
                $query->where('item_id',$row->item_id)->orderByDesc('created_at');
            })->whereNotNull('is_default')->first();

            if($bomAlternative){
                foreach($bomAlternative->bomDetail as $rowbom){
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
                        'qty_bom'                       => 0,
                        'nominal_bom'                   => 0,
                        'total_bom'                     => 0,
                        'qty_planned'                   => round($rowbom->qty * ($row->qty / $rowbom->bom->qty_output),3),
                        'nominal_planned'               => $nominal,
                        'total_planned'                 => $total,
                        'from_item_stock_id'            => $itemstock ? $itemstock->id : NULL,
                    ]);
                }

                if($bomAlternative->bom->bomStandard()->exists()){
                    foreach($bomAlternative->bom->bomStandard->bomStandardDetail as $rowbom){
                        $nominal = 0;
                        $total = 0;
                        $itemstock = NULL;
                        if($rowbom->lookable_type == 'items'){
                            $item = Item::find($rowbom->lookable_id);
                            if($item){
                                $price = $item->priceNowProduction($this->place_id,$this->post_date);
                                $total = round(round($rowbom->qty * $row->qty,3) * $price,2);
                                $nominal = $price;
                                $itemstock = ItemStock::where('item_id',$rowbom->lookable_id)->where('place_id',$this->place_id)->where('warehouse_id',$rowbom->lookable->warehouse())->first();
                            }
                        }elseif($rowbom->lookable_type == 'resources'){
                            $total = round(round($rowbom->qty * $row->qty,3) * $rowbom->nominal,2);
                            $nominal = $rowbom->nominal;
                        }
                        $querydetail = ProductionIssueDetail::create([
                            'production_issue_id'           => $query->id,
                            'production_order_detail_id'    => $this->production_order_detail_id,
                            'lookable_type'                 => $rowbom->lookable_type,
                            'lookable_id'                   => $rowbom->lookable_id,
                            'bom_id'                        => $rowbom->bom_id,
                            'bom_detail_id'                 => $rowbom->id,
                            'qty'                           => round($rowbom->qty * $row->qty,3),
                            'nominal'                       => $nominal,
                            'total'                         => $total,
                            'qty_bom'                       => round($rowbom->qty * $row->qty,3),
                            'nominal_bom'                   => $rowbom->nominal,
                            'total_bom'                     => $total,
                            'qty_planned'                   => round($rowbom->qty * $row->qty,3),
                            'nominal_planned'               => $rowbom->nominal,
                            'total_planned'                 => $total,
                            'from_item_stock_id'            => $itemstock ? $itemstock->id : NULL,
                            'place_id'                      => $itemstock ? $itemstock->place_id : NULL,
                            'warehouse_id'                  => $itemstock ? $itemstock->warehouse_id : NULL,
                        ]);
                    }
                }
            }
        }

        if($query){
            CustomHelper::sendApproval($query->getTable(),$query->id,'Production Issue No. '.$query->code);
            CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Production Issue No. '.$query->code,'Pengajuan Production Issue No. '.$query->code.' dari Production Receive FG No. '.$this->code,session('bo_id'));

            activity()
                ->performedOn(new ProductionIssue())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Add / edit issue production.');
        }
    }

    public function voidProductionIssue(){
        if($this->productionIssue()->exists()){
            foreach($this->productionIssue as $row){
                if(in_array($row->status,['2','3'])){
                    CustomHelper::removeJournal($row->getTable(),$row->id);
                    CustomHelper::removeCogs($row->getTable(),$row->id);
                }

                $row->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => 'Ditutup otomatis dari Production Receive FG '.$this->code,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

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
    
                CustomHelper::sendNotification($row->getTable(),$row->id,'Production Issue No. '.$row->code.' telah ditutup otomatis dari Production Receive FG '.$this->code.'.','Production Issue No. '.$row->code.' telah ditutup otomatis dari Production Receive FG '.$this->code.'.',$row->user_id);
                CustomHelper::removeApproval($row->getTable(),$row->id);
            }
        }
    }

    public function getRequesterByItem($item_id){
        return $this->user->name;
    }
}
