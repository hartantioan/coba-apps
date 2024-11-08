<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IncomingPayment extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'incoming_payments';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'project_id',
        'post_date',
        'currency_id',
        'currency_rate',
        'wtax_id',
        'percent_wtax',
        'total',
        'wtax',
        'rounding',
        'grandtotal',
        'coa_id',
        'list_bg_check_id',
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

    public function listBgCheck()
    {
        return $this->belongsTo('App\Models\ListBgCheck', 'list_bg_check_id', 'id')->withTrashed();
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function currency(){
        return $this->belongsTo('App\Models\Currency','currency_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function project(){
        return $this->belongsTo('App\Models\Project','project_id','id')->withTrashed();
    }

    public function voidUser(){
        return $this->belongsTo('App\Models\User','void_id','id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function wTaxMaster()
    {
        return $this->belongsTo('App\Models\Tax', 'wtax_id', 'id')->withTrashed();
    }

    public function incomingPaymentDetail()
    {
        return $this->hasMany('App\Models\IncomingPaymentDetail');
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

    public function getInvoice(){
        $arr = [];
        foreach($this->incomingPaymentDetail as $row){
            if($row->lookable_type == 'marketing_order_invoices'){

                if(!in_array($row->lookable->code,$arr)){
                    $arr[] = $row->lookable->code;
                }

            }
        }
        if(count($arr) == 0){
            $arr[]='-';
        }
        return implode(', ',$arr);
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
        $query = IncomingPayment::selectRaw('RIGHT(code, 8) as code')
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
            $query->orderBy('post_date');
        })->get() as $row){
            $currency_rate = $row->adjustRate->currency_rate;
        }
        return $currency_rate;
    }

    public function latestCurrencyRateByDate($date){
        $currency_rate = $this->currency_rate;
        foreach($this->adjustRateDetail()->whereHas('adjustRate',function($query)use($date){
            $query->where('post_date','<=',$date)->orderBy('post_date');
        })->get() as $row){
            $currency_rate = $row->adjustRate->currency_rate;
        }
        return $currency_rate;
    }

    public function getARDPCode(){
        $arr = [];
        foreach($this->incomingPaymentDetail as $row){
            if($row->lookable_type == 'marketing_order_down_payments'){
                $arr[] = $row->lookable->code;
            }
        }
        return implode(',',$arr);
    }
}
