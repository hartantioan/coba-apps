<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDeliveryProcess extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_processes';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'company_id',
        'account_id',
        'marketing_order_delivery_id',
        'post_date',
        'return_date',
        'user_driver_id',
        'driver_name',
        'driver_hp',
        'vehicle_name',
        'vehicle_no',
        'note_internal',
        'note_external',
        'status',
        'status_tracking',
        'document',
        'total',
        'tax',
        'rounding',
        'grandtotal',
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

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function userDriver()
    {
        return $this->belongsTo('App\Models\UserDriver', 'user_driver_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function marketingOrderDelivery()
    {
        return $this->belongsTo('App\Models\MarketingOrderDelivery', 'marketing_order_delivery_id', 'id')->withTrashed();
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

    public function statusTracking(){
        $status = $this->marketingOrderDeliveryProcessTrack()->orderByDesc('status')->first();

        if($status){
            return $status->status();
        }else{
            return 'Status tracking tidak ditemukan.';
        }
    }

    public function statusTrackingRaw(){
        $status = $this->marketingOrderDeliveryProcessTrack()->orderByDesc('status')->first();

        if($status){
            return $status->status;
        }else{
            return 'Status tracking tidak ditemukan.';
        }
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = MarketingOrderDeliveryProcess::selectRaw('RIGHT(code, 8) as code')
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

    public function marketingOrderDeliveryProcessTrack(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryProcessTrack','marketing_order_delivery_process_id','id');
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
    }

    public function getArrStatusTracking(){
        $arr = $this->marketingOrderDeliveryProcessTrack()->orderBy('status')->pluck('status')->toArray();

        return $arr;
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

        foreach($this->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            if($row->marketingOrderReturnDetail()->exists()){
                $hasRelation = true;
            }
        }

        foreach($this->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            if($row->marketingOrderInvoiceDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function updateJournal(){
        $journal = Journal::where('lookable_type',$this->table)->where('lookable_id',$this->id)->first();
        
        if($journal){
            foreach($this->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
                $priceout = $row->item->priceNow($row->place_id,$this->post_date);
				$nominal = round($row->qty * $row->item->sell_convert * $priceout,2);

                $row->update([
                    'price'     => $priceout,
                    'total'     => $nominal
                ]);

                if($journal){
                    foreach($journal->journalDetail()->where('item_id',$row->item_id)->get() as $rowupdate){
                        $rowupdate->update([
                            'nominal'   => $nominal
                        ]);
                    }
                }
            }
        }
    }

    public function journal(){
        return $this->hasMany('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function balanceInvoice(){
        $total = 0;

        foreach($this->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            $total += $row->balanceInvoice();
        }

        return $total;
    }

    public function createInvoice(){
        #linkkan ke AR Invoice
        $query = $this;
        $passed = true;
        foreach($query->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            if($row->marketingOrderInvoiceDetail()->exists()){
                $passed = false;
            }
        }
        if($passed){
            if($query->marketingOrderDelivery->marketingOrder->account->is_ar_invoice){
                $total = 0;
                $tax = 0;
                $total_after_tax = 0;
                $downpayment = 0;
                $arrDownPayment = [];
                $passedDp = false;
                $passedTaxSeries = false;
                
                foreach($query->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
                    $total += $row->getTotal();
                    $tax += $row->getTax();
                    $total_after_tax += $row->getGrandtotal();
                }

                if($query->marketingOrderDelivery->marketingOrder->percent_dp > 0){
                    $tempDownpayment = $total_after_tax * ($query->marketingOrderDelivery->marketingOrder->percent_dp / 100);
                    $tempBalance = $tempDownpayment;
                    foreach($query->marketingOrderDelivery->marketingOrder->account->marketingOrderDownPayment()->orderBy('code')->get() as $row){
                        if($tempBalance > 0){
                            $balanceInvoice = $row->balanceInvoice();
                            if($balanceInvoice > 0){
                                $nominal = 0;
                                if($tempBalance > $balanceInvoice){
                                    $nominal = $balanceInvoice;
                                }else{
                                    $nominal = $tempBalance;
                                }
                                $arrDownPayment[] = [
                                    'id'            => $row->id,
                                    'type'          => $row->getTable(),
                                    'code'          => $row->code,
                                    'is_include_tax'=> $row->is_include_tax,
                                    'percent_tax'   => $row->percent_tax,
                                    'tax_id'        => $row->tax_id,
                                    'total'         => $nominal / (1 + ($row->percent_tax / 100)),
                                    'tax'           => ($nominal / (1 + ($row->percent_tax / 100))) * ($row->percent_tax / 100),
                                    'grandtotal'    => $nominal,
                                ];
                                $downpayment += $nominal;
                                $tempBalance -= $nominal;
                            }
                        }
                    }
                    if($tempBalance > 0){
                        $passedDp = false;
                    }else{
                        $passedDp = true;
                    }
                }else{
                    $passedDp = true;
                }

                if($passedDp){
                    $balance = $total_after_tax - $downpayment;
                    $code = MarketingOrderInvoice::generateCode('SINV-'.date('y',strtotime($query->return_date)).substr($query->code,7,2));
                    $dueDate = date('Y-m-d', strtotime($query->return_date. ' + '.$query->marketingOrderDelivery->marketingOrder->account->top.' days'));
                    $querymoi = MarketingOrderInvoice::create([
                        'code'			                => $code,
                        'user_id'		                => $query->user_id,
                        'account_id'                    => $query->marketingOrderDelivery->marketingOrder->account_id,
                        'company_id'                    => $query->company_id,
                        'post_date'                     => $query->return_date,
                        'due_date'                      => $dueDate,
                        'document_date'                 => $query->return_date,
                        'status'                        => '1',
                        'type'                          => $query->marketingOrderDelivery->marketingOrder->payment_type,
                        'total'                         => $total,
                        'tax'                           => $tax,
                        'total_after_tax'               => $total_after_tax,
                        'rounding'                      => 0,
                        'grandtotal'                    => $total_after_tax,
                        'downpayment'                   => $downpayment,
                        'balance'                       => $balance,
                        'document'                      => NULL,
                        'note'                          => 'Dibuat otomatis setelah Surat Jalan No. '.$query->code,
                    ]);

                    foreach($query->marketingOrderDelivery->marketingOrderDeliveryDetail as $key => $rowdata){
                        MarketingOrderInvoiceDetail::create([
                            'marketing_order_invoice_id'    => $querymoi->id,
                            'lookable_type'                 => $rowdata->getTable(),
                            'lookable_id'                   => $rowdata->id,
                            'qty'                           => $rowdata->qty,
                            'price'                         => $rowdata->marketingOrderDetail->realPriceAfterGlobalDiscount(),
                            'is_include_tax'                => $rowdata->marketingOrderDetail->is_include_tax,
                            'percent_tax'                   => $rowdata->marketingOrderDetail->percent_tax,
                            'tax_id'                        => $rowdata->marketingOrderDetail->tax_id,
                            'total'                         => $rowdata->getTotal(),
                            'tax'                           => $rowdata->getTax(),
                            'grandtotal'                    => $rowdata->getGrandtotal(),
                            'note'                          => $rowdata->marketingOrderDetail->marketingOrder->code.' - '.$rowdata->marketingOrderDelivery->code.' - '.$query->code,
                        ]);
                    }

                    foreach($arrDownPayment as $rowdata){
                        MarketingOrderInvoiceDetail::create([
                            'marketing_order_invoice_id'    => $querymoi->id,
                            'lookable_type'                 => $rowdata['type'],
                            'lookable_id'                   => $rowdata['id'],
                            'qty'                           => 1,
                            'price'                         => $rowdata['total'],
                            'is_include_tax'                => $rowdata['is_include_tax'],
                            'percent_tax'                   => $rowdata['percent_tax'],
                            'tax_id'                        => $rowdata['tax_id'],
                            'total'                         => $rowdata['total'],
                            'tax'                           => $rowdata['tax'],
                            'grandtotal'                    => $rowdata['grandtotal'],
                            'note'                          => $rowdata['code'],
                        ]);
                    }
    
                    CustomHelper::sendApproval($querymoi->getTable(),$querymoi->id,$querymoi->note_internal.' - '.$querymoi->note_external);
                    CustomHelper::sendNotification($querymoi->getTable(),$querymoi->id,'Pengajuan AR Invoice No. '.$querymoi->code,$querymoi->note_internal.' - '.$querymoi->note_external,$query->user_id);
    
                    activity()
                        ->performedOn(new MarketingOrderInvoice())
                        ->causedBy($query->user_id)
                        ->withProperties($querymoi)
                        ->log('Add / edit AR Invoice.');
                }
            }
            #end linkkan ke AR Invoice
        }
    }

    public function createJournalSentDocument(){
        $modp = $this;
			
        $query = Journal::create([
            'user_id'		=> $modp->user_id,
            'company_id'    => $modp->company_id,
            'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($modp->post_date)).'00'),
            'lookable_type'	=> 'marketing_order_delivery_processes',
            'lookable_id'	=> $modp->id,
            'post_date'		=> $modp->post_date,
            'note'			=> $modp->code,
            'status'		=> '3'
        ]);
        
        $coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$modp->company_id)->first();

        foreach($modp->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){

            $hpp = $row->getHpp();

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coabdp->bp_journal ? $modp->marketingOrderDelivery->marketingOrder->account_id : NULL,
                'coa_id'		=> $coabdp->id,
                'place_id'		=> $row->place_id,
                'item_id'		=> $row->item_id,
                'warehouse_id'	=> $row->warehouse_id,
                'type'			=> '1',
                'nominal'		=> $hpp,
                'note'          => 'Item dikirimkan / keluar dari gudang.'
            ]);

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'coa_id'		=> $row->itemStock->item->itemGroup->coa_id,
                'place_id'		=> $row->place_id,
                'item_id'		=> $row->item_id,
                'warehouse_id'	=> $row->warehouse_id,
                'type'			=> '2',
                'nominal'		=> $hpp,
                'note'          => 'Item dikirimkan / keluar dari gudang.'
            ]);

            CustomHelper::sendCogs('marketing_order_delivery_processes',
                $modp->id,
                $row->place->company_id,
                $row->place_id,
                $row->warehouse_id,
                $row->item_id,
                $row->qty * $row->item->sell_convert,
                $hpp,
                'OUT',
                $modp->post_date,
                $row->area_id,
                NULL,
            );

            CustomHelper::sendStock(
                $row->place_id,
                $row->warehouse_id,
                $row->item_id,
                $row->qty * $row->item->sell_convert,
                'OUT',
                $row->area_id,
                NULL,
            );
        }
    }

    public function createJournalReceiveDocument(){
        $modp = $this;
			
        $query = Journal::create([
            'user_id'		=> $modp->user_id,
            'company_id'    => $modp->company_id,
            'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($modp->return_date)).'00'),
            'lookable_type'	=> 'marketing_order_delivery_processes',
            'lookable_id'	=> $modp->id,
            'post_date'		=> $modp->return_date,
            'note'			=> $modp->code,
            'status'		=> '3'
        ]);
        
        $coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$modp->company_id)->first();
        $coahpp = Coa::where('code','500.01.01.01.01')->where('company_id',$modp->company_id)->first();

        foreach($modp->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){

            $hpp = $row->getHpp();

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coahpp->bp_journal ? $modp->marketingOrderDelivery->marketingOrder->account_id : NULL,
                'coa_id'		=> $coahpp->id,
                'place_id'		=> $row->place_id,
                'item_id'		=> $row->item_id,
                'warehouse_id'	=> $row->warehouse_id,
                'type'			=> '1',
                'nominal'		=> $hpp,
                'note'          => 'Dokumen Surat Jalan telah kembali ke admin penagihan.'
            ]);

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coabdp->bp_journal ? $modp->marketingOrderDelivery->marketingOrder->account_id : NULL,
                'coa_id'		=> $coabdp->id,
                'place_id'		=> $row->place_id,
                'item_id'		=> $row->item_id,
                'warehouse_id'	=> $row->warehouse_id,
                'type'			=> '2',
                'nominal'		=> $hpp,
                'note'          => 'Dokumen Surat Jalan telah kembali ke admin penagihan.'
            ]);
        }
    }

    public function hasBalance(){
        $status = true;

        return $status;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }
}
