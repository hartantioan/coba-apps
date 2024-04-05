<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PurchaseInvoice extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_invoices';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'post_date',
        'received_date',
        'due_date',
        'document_date',
        'type',
        'currency_id',
        'currency_rate',
        'total',
        'tax',
        'wtax',
        'rounding',
        'grandtotal',
        'downpayment',
        'balance',
        'document',
        'note',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'document_no',
        'tax_no',
        'tax_cut_no',
        'cut_date',
        'spk_no',
        'invoice_no',
    ];

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function top(){
        $dateStart = strtotime($this->received_date);
        $dateEnd = strtotime($this->due_date);
        $diff = $dateEnd - $dateStart;
        $days = floor($diff / (60 * 60 * 24));
        return $days;
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
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

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    /* public function currency(){
        $currency = '';
        foreach($this->purchaseInvoiceDetail as $row){
            if($row->lookable_type == 'coas'){
                $currency = Currency::where('code','IDR')->where('status','1')->first();
            }elseif($row->lookable_type == 'purchase_order_details'){
                $currency = $row->lookable->purchaseOrder->currency;
            }elseif($row->lookable_type == 'landed_cost_fee_details'){
                $currency = $row->lookable->landedCost->currency;
            }elseif($row->lookable_type == 'fund_request_details'){
                $currency = $row->lookable->fundRequest->currency;
            }else{
                $currency = $row->lookable->purchaseOrderDetail->purchaseOrder->currency;
            }
        }

        return $currency;
    } */

    /* public function currencyRate(){
        $rate = 1;
        foreach($this->purchaseInvoiceDetail as $row){
            if($row->lookable_type == 'coas'){
                $rate = 1;
            }elseif($row->lookable_type == 'purchase_order_details'){
                $rate = $row->lookable->purchaseOrder->currency_rate;
            }elseif($row->lookable_type == 'landed_cost_fee_details'){
                $rate = $row->lookable->landedCost->currency_rate;
            }elseif($row->lookable_type == 'fund_request_details'){
                $rate = $row->lookable->fundRequest->currency_rate;
            }else{
                $rate = $row->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
            }
        }

        return $rate;
    } */

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail');
    }

    public function purchaseInvoiceDp()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDp');
    }

    public function hasPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['2','3','7']);
        });
    }

    public function realPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['1','2','3','7']);
        });
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
        $query = PurchaseInvoice::selectRaw('RIGHT(code, 8) as code')
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

    public function balancePaymentRequest(){
        $total = $this->balance - $this->totalMemo();

        foreach($this->hasPaymentRequestDetail as $row){
            $total -= $row->nominal;
        }

        return $total;
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

    public function hasGoodReceiptThatHasLandedCost(){
        $has = false;
        foreach($this->purchaseInvoiceDetail as $row){
            if($row->goodReceiptDetail()){
                if($row->lookable->landedCostDetail()->exists()){
                    $has = true;
                }
            }
        }
        return $has;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
        }

        foreach($this->purchaseInvoiceDetail as $row){
            if($row->purchaseMemoDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function hasBalanceMemo(){
        $total = $this->grandtotal;

        foreach($this->purchaseInvoiceDetail as $row){
            foreach($row->purchaseMemoDetail as $rowdetail){
                $total -= $rowdetail->grandtotal;
            }
        }

        if($total > 0){
            return true;
        }else{
            return false;
        }
    }

    public function balanceMemo(){
        $total = $this->grandtotal;

        foreach($this->purchaseInvoiceDetail as $row){
            foreach($row->purchaseMemoDetail as $rowdetail){
                $total -= $rowdetail->grandtotal;
            }
        }

        return $total;
    }

    public function totalMemo(){
        $total = 0;
        foreach($this->purchaseInvoiceDetail as $row){
            foreach($row->purchaseMemoDetail as $rowdetail){
                $total += $rowdetail->grandtotal;
            }
        }
        return $total;
    }

    public function totalMemoByDate($date){
        $total = 0;
        foreach($this->purchaseInvoiceDetail as $row){
            foreach($row->purchaseMemoDetail()->whereHas('purchaseMemo',function ($query) use ($date){
                $query->whereDate('post_date','<=',$date);
            })->get() as $rowdetail){
                $total += $rowdetail->grandtotal;
            }
        }
        return $total;
    }

    public function updateTotal(){
        $total = 0;
        $tax = 0;
        $wtax = 0;
        $grandtotal = 0;

        foreach($this->purchaseInvoiceDetail as $row){
            $total += $row->total;
            $tax += $row->tax;
            $wtax += $row->wtax;
            $grandtotal += $row->grandtotal;
        }

        PurchaseInvoice::find($this->id)->update([
            'total'         => $total,
            'tax'           => $tax,
            'wtax'          => $wtax,
            'grandtotal'    => $grandtotal,
            'balance'       => $grandtotal,
        ]);
    }

    public function getTop(){
        $top = 0;

        foreach($this->purchaseInvoiceDetail as $row){
            $top = $row->getTop();
        }

        return $top;
    }

    public function getTotalPaid(){
        $total = $this->balance;
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

    public function getTotalPaidExcept($prd){
        $total = $this->balance;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->where('id','<>',$prd)->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id');
        })->where('id','<>',$prd)->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }

    public function getTotalPaidDate($date){
        $total = 0;
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use ($date){
            $query->whereHas('outgoingPayment',function ($query) use ($date){
                $query->whereDate('pay_date','<=',$date);
            });

        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use($date){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id')->whereDate('post_date','<=',$date);
        })->get() as $rowpayment){
            $total += $rowpayment->nominal;
        }
        $totalPayByJournal = JournalDetail::whereHas('coa',function($query){
            $query->where('code','200.01.03.01.01');
        })->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<=',$date)
                ->whereIn('status',['2','3']);
        })->where('note','VOID*'.$this->code)->sum('nominal');

        $total += $totalPayByJournal;

        return $total;
    }

    public function getTotalPaidByDate($date){
        $total = $this->balance;
        $totalAfterMemo = $total - $this->totalMemoByDate($date);
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use ($date){
            $query->whereHas('outgoingPayment',function ($query) use ($date){
                $query->whereDate('pay_date','<=',$date);
            });

        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query) use($date){
            $query->where('payment_type','5')->whereIn('status',['1','2','3'])->whereDoesntHave('outgoingPayment')->whereNull('coa_source_id')->whereDate('post_date','<=',$date);
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        $totalPayByJournal = JournalDetail::whereHas('coa',function($query){
            $query->where('code','200.01.03.01.01');
        })->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<=',$date)
                ->whereIn('status',['2','3']);
        })->where('note','VOID*'.$this->code)->sum('nominal');
        $totalAfterMemo -= $totalPayByJournal;
        return $totalAfterMemo;
    }

    public function updateRootDocumentStatusProcess(){
        foreach($this->purchaseInvoiceDetail as $row){
            if($row->purchaseOrderDetail()){
                $row->lookable->purchaseOrder->update([
                    'status'    => '2'
                ]);
            }

            if($row->goodReceiptDetail()){
                $row->lookable->goodReceipt->update([
                    'status'    => '2'
                ]);
            }

            if($row->landedCostFeeDetail()){
                $row->lookable->landedCost->update([
                    'status'    => '2'
                ]);
            }

            if($row->fundRequestDetail()->exists()){
                $row->fundRequestDetail->fundRequest->update([
                    'status'    => '2'
                ]);
            }
        }
    }

    public function updateRootDocumentStatusDone(){
        foreach($this->purchaseInvoiceDetail as $row){
            if($row->purchaseOrderDetail()){
                if(!$row->lookable->purchaseOrder->hasBalanceInvoice()){
                    $row->lookable->purchaseOrder->update([
                        'status'    => '3'
                    ]);
                }
            }

            if($row->goodReceiptDetail()){
                if(!$row->lookable->goodReceipt->hasBalanceInvoice()){
                    $row->lookable->goodReceipt->update([
                        'status'    => '3'
                    ]);
                }
            }

            if($row->landedCostFeeDetail()){
                if(!$row->lookable->landedCost->hasBalanceInvoice()){
                    $row->lookable->landedCost->update([
                        'status'    => '3'
                    ]);
                }
            }

            if($row->fundRequestDetail()->exists()){
                if(!$row->fundRequestDetail->fundRequest->hasBalanceInvoice()){
                    $row->fundRequestDetail->fundRequest->update([
                        'status'    => '3'
                    ]);
                }
            }
        }
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }
}
