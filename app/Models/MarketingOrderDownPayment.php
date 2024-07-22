<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDownPayment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_down_payments';
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
        'post_date',
        'due_date',
        'status',
        'type',
        'currency_id',
        'currency_rate',
        'subtotal',
        'discount',
        'total',
        'tax',
        'grandtotal',
        'document',
        'tax_no',
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

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function taxId()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function marketingOrderDownPaymentDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderDownPaymentDetail');
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak Termasuk',
          '1' => 'Termasuk',
          default => 'Invalid',
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
          '1' => 'Cash',
          '2' => 'Transfer',
          '3' => 'Giro/Check',
          default => 'Invalid',
        };

        return $type;
    }

    public static function typeStatic($original){
        $type = match ($original) {
            '1' => 'Cash',
            '2' => 'Transfer',
            '3' => 'Giro/Check',
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
        $query = MarketingOrderDownPayment::selectRaw('RIGHT(code, 8) as code')
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

    public function marketingOrderInvoiceDetail(){
        return $this->hasMany('App\Models\MarketingOrderInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('marketingOrderInvoice',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function incomingPaymentDetail(){
        return $this->hasMany('App\Models\IncomingPaymentDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('incomingPayment',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function marketingOrderMemoDetail(){
        return $this->hasMany('App\Models\MarketingOrderMemoDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('marketingOrderMemo',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceInvoice(){
        $total = $this->grandtotal;

        foreach($this->marketingOrderInvoiceDetail as $row){
            $total -= $row->grandtotal;
        }

        foreach($this->marketingOrderMemoDetail as $row){
            $total -= $row->balance;
        }

        return $total;
    }

    public function balanceInvoicePaid(){
        $total = $this->totalPay();

        foreach($this->marketingOrderInvoiceDetail as $row){
            $total -= $row->grandtotal;
        }

        return $total;
    }

    public function totalMemo(){
        $total = 0;

        foreach($this->marketingOrderMemoDetail as $row){
            $total += $row->balance;
        }

        return $total;
    }

    public function totalMemoByDate($date){
        $total = 0;

        foreach($this->marketingOrderMemoDetail()->whereHas('marketingOrderMemo',function($query)use($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $row){
            $total += $row->balance;
        }

        return $total;
    }

    public function balancePaymentIncoming(){
        $total = $this->grandtotal - $this->totalMemo() - $this->totalPay();

        return $total;
    }

    public function totalPay(){
        $total = 0;

        foreach($this->incomingPaymentDetail as $row){
            $total += $row->total;
        }

        return $total;
    }

    public function totalPayByDate($date){
        $total = 0;

        foreach($this->incomingPaymentDetail()->whereHas('incomingPayment',function($query)use($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $row){
            $total += $row->total;
        }

        return $total;
    }

    public function totalPayMemo(){
        $total = $this->totalPay() + $this->totalMemo();
        return $total;
    }

    public function arrBalanceInvoice(){
        $balance = $this->grandtotal;

        foreach($this->marketingOrderInvoiceDetail as $row){
            $balance -= $row->grandtotal;
        }

        foreach($this->marketingOrderMemoDetail as $row){
            $balance -= $row->balance;
        }

        $bobot = $balance / $this->grandtotal;

        $arr = [
            'total'         => number_format(round($bobot * $this->total,2),2,',','.'),
            'tax'           => number_format(round($bobot * $this->tax,2),2,',','.'),
            'grandtotal'    => number_format(round($bobot * $this->grandtotal,2),2,',','.'),
        ];

        return $arr;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->marketingOrderMemoDetail()->exists()){
            $hasRelation = true;
        }

        if($this->incomingPaymentDetail()->exists()){
            $hasRelation = true;
        }

        if($this->marketingOrderInvoiceDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
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
                        ->whereIn('status_closing', ['3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }
}
