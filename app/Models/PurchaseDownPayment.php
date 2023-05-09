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
        'wtax',
        'grandtotal',
        'document',
        'note',
        'void_id',
        'void_note',
        'void_date'
    ];

    public function hasPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function tax()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function supplier(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
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
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceDp()->whereHas('purchaseInvoice', function($query){
            $query->whereIn('status',['2','3']);
        })->get() as $row){
            $total -= $row->nominal;
        }

        return $total;
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

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-1">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-1">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-1">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-1">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-1">Void</span>',
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

    public static function generateCode()
    {
        $query = PurchaseDownPayment::selectRaw('RIGHT(code, 9) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000000001';
        }

        $no = str_pad($code, 9, 0, STR_PAD_LEFT);

        $pre = 'PODP-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type','purchase_down_payments')->where('lookable_id',$this->id)->first();
        if($source && $source->approvalMatrix()->exists()){
            return $source;
        }else{
            return '';
        }
    }

    public function listApproval(){
        $source = $this->approval();
        if($source){
            $html = '';
            foreach($source->approvalMatrix()->whereHas('approvalTemplateStage',function($query){ $query->whereHas('approvalStage', function($query) { $query->orderBy('level'); }); })->get() as $row){
                $html .= '<span style="top:-10px;">'.$row->user->name.'</span> '.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br>';
            }

            return $html;
        }else{
            return '';
        }
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->purchaseInvoiceDp()->exists()){
            $hasRelation = true;
        }

        if($this->hasPaymentRequestDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }
}
