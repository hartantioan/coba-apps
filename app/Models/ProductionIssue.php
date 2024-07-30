<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductionIssue extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_issues';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'production_order_detail_id',
        'production_fg_receive_id',
        'production_receive_id',
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

    public function productionFgReceive()
    {
        return $this->belongsTo('App\Models\ProductionFgReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }

    public function productionReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_receive_id', 'id')->withTrashed();
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

    public function productionIssueDetail()
    {
        return $this->hasMany('App\Models\ProductionIssueDetail');
    }

    public function productionReceiveIssue(){
        return $this->hasOne('App\Models\ProductionReceiveIssue','production_issue_id','id');
    }

    public function total(){
        $total = $this->productionIssueDetail()->sum('total');
        return $total;
    }

    public function totalItem(){
        $total = $this->productionIssueDetail()->where('lookable_type','items')->sum('total');
        return $total;
    }

    public function totalResource(){
        $total = $this->productionIssueDetail()->where('lookable_type','resources')->sum('total');
        return $total;
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
        $query = ProductionIssue::selectRaw('RIGHT(code, 8) as code')
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

    public function getRequesterByItem($item_id){
        return $this->user->name;
    }

    public function reJournalAndRecalculateReceive(){
        if(in_array($this->status,['2','3'])){
            if($this->journal()->exists()){
                $this->journal->journalDetail()->delete();

                $total = 0;
                $coawip = Coa::where('code','100.01.04.03.01')->where('company_id',$this->company_id)->first();
                $arrBom = [];
                
                foreach($this->productionIssueDetail as $row){
                    if($row->bom()->exists()){
                        if(!in_array($row->bom_id,$arrBom)){
                            $arrBom[] = $row->bom_id;
                        }
                    }
                }

                #lek misal item receive fg kelompokkan dri child
                if($this->productionFgReceive()->exists() && count($arrBom) > 0){
                    foreach($arrBom as $row){
                        $totalrow = $this->productionIssueDetail()->whereNull('is_wip')->where('bom_id',$row)->sum('total');
    
                        JournalDetail::create([
                            'journal_id'	=> $this->journal->id,
                            'coa_id'		=> $coawip->id,
                            'line_id'		=> $this->line_id,
                            'place_id'		=> $this->place_id,
                            'machine_id'	=> $this->machine_id,
                            'type'			=> '1',
                            'nominal'		=> $totalrow,
                            'nominal_fc'	=> $totalrow,
                            'note'			=> $this->productionOrderDetail->productionOrder->code,
                        ]);
                        
                        foreach($this->productionIssueDetail()->whereNull('is_wip')->where('bom_id',$row)->orderBy('id')->get() as $row){
                            if($row->lookable_type == 'items'){
                                if($row->is_wip){
                                    //do nothing
                                }else{
                                    JournalDetail::create([
                                        'journal_id'	=> $this->journal->id,
                                        'coa_id'		=> $row->lookable->itemGroup->coa_id,
                                        'place_id'		=> $row->itemStock->place_id,
                                        'line_id'		=> $row->productionIssue->line_id,
                                        'item_id'		=> $row->itemStock->item_id,
                                        'warehouse_id'	=> $row->itemStock->warehouse_id,
                                        'type'			=> '2',
                                        'nominal'		=> $row->total,
                                        'nominal_fc'	=> $row->total,
                                        'note'			=> $this->productionOrderDetail->productionOrder->code,
                                    ]);
                                }
                            }elseif($row->lookable_type == 'resources'){
                                if($row->bomDetail()->exists()){
                                    if($row->bomDetail->cost_distribution_id){
                                        $lastIndex = count($row->bomDetail->costDistribution->costDistributionDetail) - 1;
                                        $accumulation = 0;
                                        foreach($row->bomDetail->costDistribution->costDistributionDetail as $key => $rowcost){
                                            if($key == $lastIndex){
                                                $nominal = $row->total - $accumulation;
                                            }else{
                                                $nominal = round(($rowcost->percentage / 100) * $row->total);
                                                $accumulation += $nominal;
                                            }
                                            JournalDetail::create([
                                                'journal_id'                    => $this->journal->id,
                                                'cost_distribution_detail_id'   => $rowcost->id,
                                                'coa_id'						=> $row->lookable->coa_id,
                                                'place_id'                      => $rowcost->place_id ?? ($this->place_id ?? NULL),
                                                'line_id'                       => $rowcost->line_id ?? ($this->line_id ?? NULL),
                                                'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
                                                'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
                                                'type'                          => '2',
                                                'nominal'						=> $nominal,
                                                'nominal_fc'					=> $nominal,
                                                'note'							=> $this->productionOrderDetail->productionOrder->code,
                                            ]);
                                        }
                                    }else{
                                        JournalDetail::create([
                                            'journal_id'	=> $this->journal->id,
                                            'coa_id'		=> $row->lookable->coa_id,
                                            'line_id'		=> $this->line_id,
                                            'place_id'		=> $this->place_id,
                                            'type'			=> '2',
                                            'nominal'		=> $row->total,
                                            'nominal_fc'	=> $row->total,
                                            'note'			=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }else{
                                    if($row->cost_distribution_id){
                                        $lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
                                        $accumulation = 0;
                                        foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
                                            if($key == $lastIndex){
                                                $nominal = $row->total - $accumulation;
                                            }else{
                                                $nominal = round(($rowcost->percentage / 100) * $row->total);
                                                $accumulation += $nominal;
                                            }
                                            JournalDetail::create([
                                                'journal_id'                    => $this->journal->id,
                                                'cost_distribution_detail_id'   => $rowcost->id,
                                                'coa_id'						=> $row->lookable->coa_id,
                                                'place_id'                      => $rowcost->place_id ?? ($this->place_id ?? NULL),
                                                'line_id'                       => $rowcost->line_id ?? ($this->line_id ?? NULL),
                                                'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
                                                'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
                                                'type'                          => '2',
                                                'nominal'						=> $nominal,
                                                'nominal_fc'					=> $nominal,
                                                'note'							=> $this->productionOrderDetail->productionOrder->code,
                                            ]);
                                        }
                                    }else{
                                        JournalDetail::create([
                                            'journal_id'	=> $this->journal->id,
                                            'coa_id'		=> $row->lookable->coa_id,
                                            'line_id'		=> $this->line_id,
                                            'place_id'		=> $this->place_id,
                                            'type'			=> '2',
                                            'nominal'		=> $row->total,
                                            'nominal_fc'	=> $row->total,
                                            'note'			=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }else{
        
                    foreach($this->productionIssueDetail()->orderBy('id')->get() as $row){
                        if($row->lookable_type == 'items'){
                            if($row->is_wip){
                                //do nothing
                            }else{
                                if($row->productionBatchUsage()->exists()){
                                    foreach($row->productionBatchUsage as $rowbatchusage){
                                        $price = $rowbatchusage->productionBatch->item->priceNowProduction($rowbatchusage->productionBatch->place_id,$this->post_date);
                                        $rowtotal = round($rowbatchusage->qty * $price,2);
                                        JournalDetail::create([
                                            'journal_id'	=> $this->journal->id,
                                            'coa_id'		=> $rowbatchusage->productionBatch->item->itemGroup->coa_id,
                                            'place_id'		=> $rowbatchusage->productionBatch->place_id,
                                            'line_id'		=> $row->productionIssue->line_id,
                                            'item_id'		=> $rowbatchusage->productionBatch->item_id,
                                            'warehouse_id'	=> $rowbatchusage->productionBatch->warehouse_id,
                                            'type'			=> '2',
                                            'nominal'		=> $rowtotal,
                                            'nominal_fc'	=> $rowtotal,
                                            'note'			=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }else{
                                    #jika wip sebelum final ke wip final jurnal batchnya disini
                                    if($this->productionFgReceive()->exists()){
                                        foreach($this->productionFgReceive->productionBatchUsage()->whereHas('productionBatch',function($querykuy)use($row){
                                            $querykuy->where('item_id',$row->lookable_id);
                                        })->get() as $rowbatchusage){
                                            $totalCost = round(($rowbatchusage->productionBatch->total / $rowbatchusage->productionBatch->qty_real) * $rowbatchusage->qty,2);
    
                                            JournalDetail::create([
                                                'journal_id'	=> $this->journal->id,
                                                'coa_id'		=> $rowbatchusage->productionBatch->item->itemGroup->coa_id,
                                                'place_id'		=> $rowbatchusage->productionBatch->place_id,
                                                'line_id'		=> $this->productionFgReceive->line_id,
                                                'item_id'		=> $rowbatchusage->productionBatch->item_id,
                                                'warehouse_id'	=> $rowbatchusage->productionBatch->warehouse_id,
                                                'type'			=> '2',
                                                'nominal'		=> $totalCost,
                                                'nominal_fc'	=> $totalCost,
                                                'note'			=> $this->productionOrderDetail->productionOrder->code,
                                            ]);
                                        }
                                    }else{
                                        #jika production issue biasa
                                        JournalDetail::create([
                                            'journal_id'	=> $this->journal->id,
                                            'coa_id'		=> $row->lookable->itemGroup->coa_id,
                                            'place_id'		=> $row->itemStock->place_id,
                                            'line_id'		=> $row->productionIssue->line_id,
                                            'item_id'		=> $row->itemStock->item_id,
                                            'warehouse_id'	=> $row->itemStock->warehouse_id,
                                            'type'			=> '2',
                                            'nominal'		=> $row->total,
                                            'nominal_fc'	=> $row->total,
                                            'note'			=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }
                            }
                        }elseif($row->lookable_type == 'resources'){
                            if($row->bomDetail()->exists()){
                                if($row->bomDetail->cost_distribution_id){
                                    $lastIndex = count($row->bomDetail->costDistribution->costDistributionDetail) - 1;
                                    $accumulation = 0;
                                    foreach($row->bomDetail->costDistribution->costDistributionDetail as $key => $rowcost){
                                        if($key == $lastIndex){
                                            $nominal = $row->total - $accumulation;
                                        }else{
                                            $nominal = round(($rowcost->percentage / 100) * $row->total);
                                            $accumulation += $nominal;
                                        }
                                        JournalDetail::create([
                                            'journal_id'                    => $this->journal->id,
                                            'cost_distribution_detail_id'   => $rowcost->id,
                                            'coa_id'						=> $row->lookable->coa_id,
                                            'place_id'                      => $rowcost->place_id ?? ($this->place_id ?? NULL),
                                            'line_id'                       => $rowcost->line_id ?? ($this->line_id ?? NULL),
                                            'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
                                            'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
                                            'type'                          => '2',
                                            'nominal'						=> $nominal,
                                            'nominal_fc'					=> $nominal,
                                            'note'							=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }else{
                                    JournalDetail::create([
                                        'journal_id'	=> $this->journal->id,
                                        'coa_id'		=> $row->lookable->coa_id,
                                        'line_id'		=> $this->line_id,
                                        'place_id'		=> $this->place_id,
                                        'type'			=> '2',
                                        'nominal'		=> $row->total,
                                        'nominal_fc'	=> $row->total,
                                        'note'			=> $this->productionOrderDetail->productionOrder->code,
                                    ]);
                                }
                            }else{
                                if($row->cost_distribution_id){
                                    $lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
                                    $accumulation = 0;
                                    foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
                                        if($key == $lastIndex){
                                            $nominal = $row->total - $accumulation;
                                        }else{
                                            $nominal = round(($rowcost->percentage / 100) * $row->total);
                                            $accumulation += $nominal;
                                        }
                                        JournalDetail::create([
                                            'journal_id'                    => $this->journal->id,
                                            'cost_distribution_detail_id'   => $rowcost->id,
                                            'coa_id'						=> $row->lookable->coa_id,
                                            'place_id'                      => $rowcost->place_id ?? ($this->place_id ?? NULL),
                                            'line_id'                       => $rowcost->line_id ?? ($this->line_id ?? NULL),
                                            'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
                                            'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
                                            'type'                          => '2',
                                            'nominal'						=> $nominal,
                                            'nominal_fc'					=> $nominal,
                                            'note'							=> $this->productionOrderDetail->productionOrder->code,
                                        ]);
                                    }
                                }else{
                                    JournalDetail::create([
                                        'journal_id'	=> $this->journal->id,
                                        'coa_id'		=> $row->lookable->coa_id,
                                        'line_id'		=> $this->line_id,
                                        'place_id'		=> $this->place_id,
                                        'type'			=> '2',
                                        'nominal'		=> $row->total,
                                        'nominal_fc'	=> $row->total,
                                        'note'			=> $this->productionOrderDetail->productionOrder->code,
                                    ]);
                                }
                            }
                        }
        
                        if(!$row->is_wip){
                            $total += $row->total;
                        }
                    }
        
                    JournalDetail::create([
                        'journal_id'	=> $this->journal->id,
                        'coa_id'		=> $coawip->id,
                        'line_id'		=> $this->line_id,
                        'place_id'		=> $this->place_id,
                        'machine_id'	=> $this->machine_id,
                        'type'			=> '1',
                        'nominal'		=> $total,
                        'nominal_fc'	=> $total,
                        'note'			=> $this->productionOrderDetail->productionOrder->code,
                    ]);
                }
            }

            if($this->productionReceiveIssue()->exists()){
                $productionReceive = $this->productionReceiveIssue->productionReceive;
                $totalIssue = $this->total() + $productionReceive->totalIssueExcept($this->id);
                $totalReceive = $productionReceive->total();
                foreach($productionReceive->productionReceiveDetail as $row){
                    $bobot = $row->total / $totalReceive;
                    $totalrow = round($bobot * $totalIssue,2);
                    $totalbatch = $row->totalBatch();
                    foreach($row->productionBatch as $rowbatch){
                        $bobotreceive = $rowbatch->total / $totalbatch;
                        $totalnewbatch = round($bobotreceive * $totalrow,2);
                        $rowbatch->update([
                            'total' => $totalnewbatch
                        ]);
                    }
                    $row->update([
                        'total' => $totalrow
                    ]);
                }
                CustomHelper::removeJournal($productionReceive->getTable(),$productionReceive->id);
                CustomHelper::removeCogs($productionReceive->getTable(),$productionReceive->id);
                CustomHelper::sendJournal($productionReceive->getTable(),$productionReceive->id);
            }
        }
    }
}
