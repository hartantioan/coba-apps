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
        'company_id',
        'post_date',
        'due_date',
        'document_date',
        'status',
        'type',
        'total',
        'tax',
        'total_after_tax',
        'rounding',
        'grandtotal',
        'downpayment',
        'balance',
        'document',
        'tax_no',
        'note',
        'void_id',
        'void_note',
        'void_date',
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

    public function marketingOrderInvoiceDeliveryProcess()
    {
        return $this->marketingOrderInvoiceDetail()->where('lookable_type','marketing_order_delivery_details');
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

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->marketingOrderInvoiceDetail as $row){
            if($row->marketingOrderMemoDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function balance(){
        $total = $this->balance;

        foreach($this->marketingOrderInvoiceDetail as $row){
            foreach($row->marketingOrderMemoDetail as $momd){
                $total -= $this->balance;
            }
        }

        return $total;
    }

    public function totalMemo(){
        $total = 0;

        foreach($this->marketingOrderInvoiceDetail as $row){
            foreach($row->marketingOrderMemoDetail as $momd){
                $total += $this->balance;
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
            $arr['total'] += $arrNominal['total'];
            $arr['tax'] += $arrNominal['tax'];
            $arr['total_after_tax'] += $arrNominal['total_after_tax'];
            $arr['rounding'] += $arrNominal['rounding'];
            $arr['grandtotal'] += $arrNominal['grandtotal'];
            $arr['downpayment'] += $arrNominal['downpayment'];
            $arr['balance'] += $arrNominal['balance'];
        }

        return $arr;
    }

    public function incomingPaymentDetail(){
        return $this->hasMany('App\Models\IncomingPaymentDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('incomingPayment',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balancePaymentIncoming(){
        $total = $this->balance - $this->totalPayMemo();
        return $total;
    }

    public function totalPay(){
        $total = 0;

        foreach($this->incomingPaymentDetail as $row){
            $total += $row->total;
        }

        return $total;
    }

    public function totalPayMemo(){
        $total = $this->totalPay() + $this->totalMemo();
        return $total;
    }

    public function getPercentPayment(){
        $total = $this->balance - $this->totalMemo();
        $percent = $total > 0 ? round(($this->totalPay() / $total) * 100) : 0;
        return $percent;
    }
}
