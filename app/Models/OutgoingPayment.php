<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OutgoingPayment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'outgoing_payments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'account_id',
        'payment_request_id',
        'coa_source_id',
        'currency_id',
        'currency_rate',
        'post_date',
        'pay_date',
        'cost_distribution_id',
        'total',
        'rounding',
        'admin',
        'grandtotal',
        'payment',
        'balance',
        'document',
        'note',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note'
    ];

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }

    public function getTotalPiutangKaryawan(){
        $total = 0;
        foreach($this->paymentRequest->paymentRequestDetail()->whereHasMorph('lookable',[FundRequest::class],function (Builder $query){
            $query->where('document_status','3');
        })->get() as $row){
            $total += $row->nominal;
        }
        return $total;
    }

    public function paymentRequestCross(){
        return $this->hasMany('App\Models\PaymentRequestCross','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function incomingPaymentDetail(){
        return $this->hasMany('App\Models\IncomingPaymentDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('incomingPayment',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balancePaymentCross(){
        $total = $this->getTotalPiutangKaryawan();
        foreach($this->paymentRequestCross as $row){
            $total -= $row->nominal;
        }
        return $total;
    }

    public function balancePaymentIncoming(){
        $total = $this->balancePaymentCross();
        foreach($this->incomingPaymentDetail as $row){
            $total -= $row->total;
        }
        return $total;
    }

    public function totalUsedCross(){
        $total = 0;
        foreach($this->paymentRequestCross as $row){
            $total += $row->nominal;
        }
        return $total;
    }

    public function coaSource()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_source_id', 'id')->withTrashed();
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
        $query = OutgoingPayment::selectRaw('RIGHT(code, 8) as code')
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

    

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }
}
