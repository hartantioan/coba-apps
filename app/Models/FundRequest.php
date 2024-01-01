<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FundRequest extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'fund_requests';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'place_id',
        'department_id',
        'account_id',
        'type',
        'post_date',
        'required_date',
        'currency_id',
        'currency_rate',
        'note',
        'termin_note',
        'payment_type',
        'name_account',
        'no_account',
        'document',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'document_status',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function fundRequestDetail()
    {
        return $this->hasMany('App\Models\FundRequestDetail');
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function hasPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['2','3']);
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

    public function totalReceivable(){
        $total = 0;
        if($this->document_status == '3'){
            foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            })->get() as $row){
                $total += $row->nominal;
            }
        }
        return $total;
    }

    public function totalReceivableByDate($date){
        $total = 0;
        if($this->document_status == '3'){
            foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query)use($date){
                $query->whereHas('outgoingPayment',function($query)use($date){
                    $query->whereDate('pay_date','<=',$date);
                });
            })->get() as $row){
                $total += $row->nominal;
            }
        }
        return $total;
    }

    public function totalReceivableUsedPaid(){
        $total = 0;
        if($this->document_status == '3'){
            foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            })->get() as $row){
                $total += $row->totalOutgoingUsedWeight() + $row->totalIncomingUsedWeight();
            }
        }
        return $total;
    }

    public function totalReceivableUsedPaidExcept($prd){
        $total = 0;
        if($this->document_status == '3'){
            foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            })->where('id','<>',$prd)->get() as $row){
                $total += $row->totalOutgoingUsedWeight() + $row->totalIncomingUsedWeight();
            }
        }
        return $total;
    }

    public function totalReceivableUsedPaidByDate($date){
        $total = 0;
        if($this->document_status == '3'){
            foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query)use($date){
                $query->whereHas('outgoingPayment',function($query)use($date){
                    $query->whereDate('pay_date','<=',$date);
                });
            })->get() as $row){
                $total += $row->totalOutgoingUsedWeightByDate($date) + $row->totalIncomingUsedWeightByDate($date);
            }
        }
        return $total;
    }

    public function closeBillDetail(){
        return $this->hasMany('App\Models\CloseBillDetail','fund_request_id','id')->whereHas('closeBill',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balancePaymentRequest(){
        $total = $this->grandtotal;

        foreach($this->hasPaymentRequestDetail()->whereHas('paymentRequest', function($query){
            $query->whereIn('status',['2','3']);
        })->get() as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function balanceCloseBill(){
        $total = floatval($this->grandtotal);

        foreach($this->closeBillDetail as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function getCoaPaymentRequestAll(){
        $arr_coa = [];

        foreach($this->hasPaymentRequestDetail as $row){
            $arr_coa[] = $row->coa->code.' - '.$row->coa->name;
        }

        return implode(',',$arr_coa);
    }

    public function getCoaPaymentRequestOne(){
        $coa_id = 0;
        foreach($this->hasPaymentRequestDetail as $row){
            $coa_id = $row->coa_id;
        }
        return $coa_id;
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'BS',
          '2' => 'Pinjaman',
          default => 'Invalid',
        };

        return $type;
    }

    public function documentStatus(){
        $status = match ($this->document_status) {
            '1' => 'MENUNGGU',
            '2' => 'LENGKAP',
            '3' => 'TIDAK LENGKAP',
          default => 'Invalid',
        };

        return $status;
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

    public function paymentType(){
        $type = match ($this->payment_type) {
          '1' => 'Tunai',
          '2' => 'Transfer',
          '3' => 'CEK',
          '4' => 'BG',
          default => 'Invalid',
        };

        return $type;
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = FundRequest::selectRaw('RIGHT(code, 8) as code')
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

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function addLimitCreditEmployee($nominal){
        $user = User::find($this->account_id);
        $user->count_limit_credit = $user->count_limit_credit + $nominal;
        $user->save();
    }
    public function removeLimitCreditEmployee($nominal){
        $user = User::find($this->account_id);
        $user->count_limit_credit = $user->count_limit_credit - $nominal;
        $user->save();
    }
}
