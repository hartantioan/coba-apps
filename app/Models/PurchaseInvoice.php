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
        'subtotal',
        'discount',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'downpayment',
        'rounding',
        'balance',
        'document',
        'note',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'tax_no',
        'tax_cut_no',
        'cut_date',
        'spk_no',
        'invoice_no'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'Cash',
          '2' => 'Credit',
          default => 'Invalid',
        };

        return $type;
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

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
            $query->whereIn('status',['2','3']);
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

    public static function generateCode($post_date)
    {
        $query = PurchaseInvoice::selectRaw('RIGHT(code, 9) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000000001';
        }

        $no = str_pad($code, 9, 0, STR_PAD_LEFT);

        $pre = 'POIN-'.date('ymd',strtotime($post_date)).'-';

        return $pre.$no;
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

    

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
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

    public function getTotalPaid(){
        $total = $this->balance;
        $totalAfterMemo = $total - $this->totalMemo();
        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
            $query->whereHas('outgoingPayment');
        })->get() as $rowpayment){
            $totalAfterMemo -= $rowpayment->nominal;
        }
        return $totalAfterMemo;
    }
}
