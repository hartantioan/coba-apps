<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PurchaseDownPayment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_down_payments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'tax_id',
        'is_tax',
        'is_include_tax',
        'percent_tax',
        'wtax_id',
        'percent_wtax',
        'top',
        'post_date',
        'due_date',
        'status',
        'balance_status',
        'type',
        'currency_id',
        'currency_rate',
        'subtotal',
        'discount',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'document',
        'note',
        'note_external',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function balanceStatus(){
        $balance_status = match ($this->balance_status) {
            '1' => 'Selesai',
            default => 'Pending',
        };

        return $balance_status;
    }

    public function hasPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function hasPaymentRequestDetailPreq(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['1','2','3','6']);
        });
    }

    public function realPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function listCekBG(){
        $list = [];
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $list[] = $rowpayment->paymentRequest->payment_no;
        }
        if(count($list) > 0){
            return implode(', ',$list);
        }else{
            return '-';
        }   
    }

    public function listPaymentRequest(){
        $list = [];
        foreach($this->hasPaymentRequestDetail()->get() as $rowpayment){
            $list[] = $rowpayment->paymentRequest->code;
        }
        if(count($list) > 0){
            return implode(', ',$list);
        }else{
            return '-';
        }   
    }

    public function listOutgoingPayment(){
        $list = [];
        foreach($this->hasPaymentRequestDetail()->get() as $rowpayment){
            if($rowpayment->paymentRequest->outgoingPayment()->exists()){
                $list[] = $rowpayment->paymentRequest->outgoingPayment->code;
            }
        }
        if(count($list) > 0){
            return implode(', ',$list);
        }else{
            return '-';
        }   
    }

    public function listPayDate(){
        $list = [];
        foreach($this->hasPaymentRequestDetail()->get() as $rowpayment){
            if($rowpayment->paymentRequest->outgoingPayment()->exists()){
                $list[] = $rowpayment->paymentRequest->outgoingPayment->pay_date;
            }
        }
        if(count($list) > 0){
            return implode(', ',$list);
        }else{
            return '-';
        }   
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function taxModel()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function wtaxModel()
    {
        return $this->belongsTo('App\Models\Tax', 'wtax_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function supplier(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '1' => 'Termasuk',
          default => 'Tidak Termasuk',
        };

        return $type;
    }

    public function isTax(){
        $type = match ($this->is_tax) {
          NULL => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }

    public function type(){
        $type = match ($this->type) {
            '1'   => 'Tunai',
            '2'   => 'Transfer',
            '3'   => 'Cek/BG',
            default => 'Invalid',
        };
  
        return $type;
    }

    public static function typeStatic($original){
        $type = match ($original) {
            '1'   => 'Tunai',
            '2'   => 'Transfer',
            '3'   => 'Cek/BG',
            '4'   => 'BG',
            '5'   => 'Credit',
            default => 'Invalid',
        };

        return $type;
    }

    public static function getReference($code){
        $arr = [];

        $apdp = PurchaseDownPayment::where('code',$code)->first();

        foreach($apdp->purchaseDownPaymentDetail as $row){
            if($row->purchaseOrder()->exists()){
                $arr[] = $row->purchaseOrder->code;
            }
            if($row->fundRequestDetail()->exists()){
                $arr[] = $row->fundRequestDetail->fundRequest->code;
            }
        }

        $arr = array_unique($arr);

        return implode(', ',$arr);
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function purchaseDownPaymentDetail()
    {
        return $this->hasMany('App\Models\PurchaseDownPaymentDetail');
    }

    public function purchaseInvoiceDp()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDp')->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['1','2','3','7']);
        });
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceDp as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function balanceInvoiceByDate($date){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceDp()->whereHas('purchaseInvoice',function($query) use ($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->purchaseInvoiceDp as $row){
            $total += $row->nominal;
        }

        return $total;
    }

    public function balancePayment(){
        $total = $this->grandtotal;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function balancePaymentByDate($date){
        $total = $this->grandtotal - $this->totalMemoByDate($date);

        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use ($date){
            $query->whereHas('outgoingPayment',function ($query) use ($date){
                $query->whereDate('pay_date','<=',$date);
            });
        })->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }

        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use($date){
            $query->where('payment_type','5')->whereIn('status',['2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id')->whereDate('post_date','<=',$date);
        })->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }
        return $total;
    }

    public function balancePaymentRequestPreq(){
        $total = $this->grandtotal;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetailPreq as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function balancePaymentRequest(){
        $total = $this->grandtotal;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function balancePaymentRequestByDate($date){
        $total = $this->grandtotal - $this->totalMemoByDate($date);

        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use ($date){
            $query->whereHas('outgoingPayment',function ($query) use ($date){
                $query->whereDate('post_date','<=',$date);
            });
        })->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }

        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use($date){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id')->whereDate('post_date','<=',$date);
        })->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }
        return $total;
    }

    public function totalPaidByDate($date){
        $total = 0;
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use ($date){
            $query->whereHas('outgoingPayment',function ($query) use ($date){
                $query->whereDate('post_date','<=',$date);
            });
        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use($date){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id')->whereDate('post_date','<=',$date);
        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        return $total;
    }

    public function balancePaid(){
        $total = $this->grandtotal;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function balancePaidExcept($prd){
        $total = $this->grandtotal;
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->where('id','<>',$prd)->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->where('id','<>',$prd)->get() as $rowpayment){
            $total -= $rowpayment->nominal;
        }
        return $total;
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
        $query = PurchaseDownPayment::selectRaw('RIGHT(code, 8) as code')
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

        if($this->purchaseInvoiceDp()->exists()){
            $hasRelation = true;
        }

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
        }

        if($this->adjustRateDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function hasChildDocumentExceptAdjustRate(){
        $hasRelation = false;

        if($this->purchaseInvoiceDp()->exists()){
            $hasRelation = true;
        }

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function cancelDocument(){
        return $this->hasOne('App\Models\CancelDocument','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function purchaseMemoDetail(){
        return $this->hasMany('App\Models\PurchaseMemoDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseMemo',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function checklistDocumentList(){
        return $this->hasMany('App\Models\ChecklistDocumentList','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function balanceMemo(){
        $total = str_replace(',','.',str_replace('.','',$this->total));

        foreach($this->purchaseMemoDetail as $row){
            $total -= $row->total;
        }

        return $total;
    }

    public function hasBalanceMemo(){
        $total = $this->grandtotal;

        foreach($this->purchaseMemoDetail as $row){
            $total -= $row->grandtotal;
        }

        if($total > 0){
            return true;
        }else{
            return false;
        }
    }

    public function totalMemo(){
        $total = 0;
        foreach($this->purchaseMemoDetail as $row){
            $total += $row->grandtotal;
        }
        return $total;
    }

    public function totalMemoByDate($date){
        $total = 0;
        foreach($this->purchaseMemoDetail()->whereHas('purchaseMemo',function ($query) use ($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $rowdetail){
            $total += $rowdetail->grandtotal;
        }
        return $total;
    }

    public function getTotalPaid(){
        $total = $this->grandtotal;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function totalPaid(){
        $total = 0;
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        return $total;
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

    public function adjustRateDetail(){
        return $this->hasMany('App\Models\AdjustRateDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('adjustRate',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function latestCurrencyRate(){
        $currency_rate = $this->currency_rate;
        foreach($this->adjustRateDetail()->whereHas('adjustRate',function($query){
            $query/* ->where('post_date','<','2024-06-01') */->orderBy('post_date');
        })->get() as $row){
            $currency_rate = $row->adjustRate->currency_rate;
        }
        return $currency_rate;
    }

    public function latestCurrencyRateByDate($date){
        $currency_rate = $this->currency_rate;
        foreach($this->adjustRateDetail()->whereHas('adjustRate',function($query)use($date){
            $query->where('post_date','<=',$date)/* ->where('post_date','<','2024-06-01') */->orderBy('post_date');
        })->get() as $row){
            $currency_rate = $row->adjustRate->currency_rate;
        }
        return $currency_rate;
    }
}
