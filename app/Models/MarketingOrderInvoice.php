<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderInvoice extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_invoices';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'user_data_id',
        'company_id',
        'marketing_order_delivery_process_id',
        'post_date',
        'due_date',
        'due_date_internal',
        'status',
        'type',
        'document',
        'tax_no',
        'tax_id',
        'note',
        'subtotal',
        'downpayment',
        'total',
        'tax',
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

    public function taxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function marketingOrderDeliveryProcess()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryProcess', 'marketing_order_delivery_process_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function userData()
    {
        return $this->belongsTo('App\Models\UserData', 'user_data_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'DP',
          '2' => 'Credit',
          default => 'Invalid',
        };

        return $type;
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function marketingOrderInvoiceDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderInvoiceDetail');
    }

    public function marketingOrderInvoiceDeliveryProcessDetail()
    {
        return $this->marketingOrderInvoiceDetail()->where('lookable_type','marketing_order_delivery_process_details');
    }

    public function marketingOrderInvoiceDownPayment()
    {
        return $this->marketingOrderInvoiceDetail()->where('lookable_type','marketing_order_down_payments');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          '7' => '<span class="blue darken-4 medium-small white-text padding-3">Schedule</span>',
          '8' => '<span class="pink darken-4 medium-small white-text padding-3">Ditutup Balik</span>',
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
            '7' => 'Schedule',
            '8' => 'Ditutup Balik',
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
        $query = MarketingOrderInvoice::selectRaw('RIGHT(code, 8) as code')
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

    public function getAge(){
        $age = time() -  strtotime($this->post_date);
        
        return round($age / (60 * 60 * 24));
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->marketingOrderInvoiceDetail as $row){
            if($row->marketingOrderMemoDetail()->exists()){
                $hasRelation = true;
            }
        }

        if($this->marketingOrderHandoverInvoiceDetail()->exists()){
            $hasRelation = true;
        }

        if($this->marketingOrderReceiptDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function balance(){
        $total = $this->grandtotal;

        foreach($this->marketingOrderInvoiceDetail as $row){
            foreach($row->marketingOrderMemoDetail as $momd){
                $total -= $this->grandtotal;
            }
        }

        return $total;
    }

    public function totalMemo(){
        $total = 0;

        foreach($this->marketingOrderInvoiceDetail as $row){
            foreach($row->marketingOrderMemoDetail as $momd){
                $total += $momd->grandtotal;
            }
        }

        return $total;
    }

    public function totalMemoByDate($date){
        $total = 0;

        foreach($this->marketingOrderInvoiceDetail as $row){
            foreach($row->marketingOrderMemoDetail()->whereHas('marketingOrderMemo',function($query)use($date){
                $query->whereDate('post_date','<=',$date);
            })->get() as $momd){
                $total += $momd->grandtotal;
            }
        }

        return $total;
    }

    public function arrBalanceMemo(){
        $arr = [
            'total'             => 0,
            'tax'               => 0,
            'total_after_tax'   => 0,
            'rounding'          => 0,
            'grandtotal'        => 0,
            'downpayment'       => 0,
            'balance'           => 0,
        ];

        foreach($this->marketingOrderInvoiceDetail as $row){
            $arrNominal = $row->arrBalanceMemo();
            $arr['balance'] += $arrNominal['balance'];
        }

        return $arr;
    }

    public function incomingPaymentDetail(){
        return $this->hasMany('App\Models\IncomingPaymentDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('incomingPayment',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function marketingOrderHandoverInvoiceDetail(){
        return $this->hasMany('App\Models\MarketingOrderHandoverInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('marketingOrderHandoverInvoice',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function marketingOrderReceiptDetail(){
        return $this->hasMany('App\Models\MarketingOrderReceiptDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('MarketingOrderReceipt',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function latestHandoverInvoice(){
        $code = '-';
        foreach($this->marketingOrderHandoverInvoiceDetail as $row){
            $code = $row->marketingOrderHandoverInvoice->code;
        }
        return $code;
    }

    public function latestReceipt(){
        $code = '-';
        foreach($this->marketingOrderReceiptDetail as $row){
            $code = $row->marketingOrderReceipt->code;
        }
        return $code;
    }

    public function latestHandoverReceipt(){
        $code = '-';
        foreach($this->marketingOrderReceiptDetail as $row){
            foreach($row->marketingOrderReceipt->marketingOrderHandoverReceiptDetail as $row){
                $code = $row->marketingOrderHandoverReceipt->code;
            }
        }
        return $code;
    }

    public function listTrackingCollector(){
        $arr = [];

        foreach($this->marketingOrderReceiptDetail()->whereHas('marketingOrderReceipt',function($query){
            $query->whereHas('marketingOrderHandoverReceiptDetail');
        })->get() as $row){
            foreach($row->marketingOrderReceipt->marketingOrderHandoverReceiptDetail as $row){
                $arr[] = [
                    'code'      => $row->marketingOrderHandoverReceipt->code,
                    'receipt'   => $row->marketingOrderReceipt->code,
                    'collector' => $row->marketingOrderHandoverReceipt->account->name,
                    'status'    => $row->status(),
                    'note'      => $row->note,
                    'date'      => $row->updated_at,
                ];
            }
        }

        return $arr;
    }

    public function balancePaymentIncoming(){
        $total = $this->grandtotal - $this->totalPayMemo();
        return $total;
    }

    public function totalPay(){
        $total = 0;

        foreach($this->incomingPaymentDetail as $row){
            $total += $row->subtotal;
        }

        return $total;
    }

    public function totalPayByDate($date){
        $total = 0;

        foreach($this->incomingPaymentDetail()->whereHas('incomingPayment',function($query)use($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $row){
            $total += $row->subtotal;
        }

        return $total;
    }

    public function totalPayMemo(){
        $total = $this->totalPay() + $this->totalMemo();
        return $total;
    }

    public function getPercentPayment(){
        $total = $this->grandtotal - $this->totalMemo();
        $percent = $total > 0 ? round(($this->totalPay() / $total) * 100) : 0;
        return $percent;
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
}
