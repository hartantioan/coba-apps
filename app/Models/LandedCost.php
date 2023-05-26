<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandedCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'landed_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'supplier_id',
        'account_id',
        'good_receipt_id',
        'company_id',
        'post_date',
        'reference',
        'currency_id',
        'currency_rate',
        'note',
        'document',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date'
    ];

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\User', 'supplier_id', 'id')->withTrashed();
    }

    public function vendor()
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

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function landedCostDetail()
    {
        return $this->hasMany('App\Models\LandedCostDetail');
    }

    public function landedCostFeeDetail()
    {
        return $this->hasMany('App\Models\LandedCostFeeDetail');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
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
        $query = LandedCost::selectRaw('RIGHT(code, 9) as code')
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

        $pre = 'LC-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type','landed_costs')->where('lookable_id',$this->id)->first();
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

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceDetail as $row){
            $total -= $row->grandtotal;
        }

        return $total;
    }

    public function getListItem(){
        $html = '<ol>';

        foreach($this->goodReceipt->goodReceiptDetail as $row){
            $html .= '<li>'.$row->item->code.' - '.$row->item->name.' Qty. '.$row->qty.' '.$row->item->buyUnit->code.'</li>';
        }

        $html .= '</ol>';

        return $html;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->purchaseInvoiceDetail()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }
}
