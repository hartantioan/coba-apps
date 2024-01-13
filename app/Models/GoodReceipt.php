<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GoodReceipt extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receipts';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'account_id',
        'receiver_name',
        'post_date',
        'due_date',
        'document_date',
        'delivery_no',
        'document',
        'note',
        'status',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
    ];

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }
    
    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function goodReceiptDetail()
    {
        return $this->hasMany('App\Models\GoodReceiptDetail');
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = GoodReceipt::selectRaw('RIGHT(code, 8) as code')
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

    public function currency(){
        $currency = '';
        foreach($this->goodReceiptDetail as $row){
            $currency = $row->purchaseOrderDetail->purchaseOrder->currency;
        }

        return $currency;
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

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function landedCost()
    {
        return $this->hasMany('App\Models\LandedCost')->whereIn('status',['2','3']);;
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

    public function getPurchaseCode(){
        $arrCode = [];

        foreach($this->goodReceiptDetail as $row){
            if(!in_array($row->purchaseOrderDetail->purchaseOrder->code,$arrCode)){
                $arrCode[] = $row->purchaseOrderDetail->purchaseOrder->code;
            }
        }

        return implode(',',$arrCode);
    }

    public function getListItem(){
        $html = '<ol>';

        foreach($this->goodReceiptDetail as $row){
            $html .= '<li>'.$row->item->code.' - '.$row->item->name.' Qty. '.$row->qty.' '.$row->itemUnit->unit->code.'</li>';
        }

        $html .= '</ol>';

        return $html;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->landedCost()->exists()){
            $hasRelation = true;
        }

        if($this->purchaseInvoiceDetail()->exists()){
            $hasRelation = true;
        }

        foreach($this->goodReceiptDetail as $row){
            foreach($row->goodReceiptDetailSerial as $row){
                if($row->goodIssueDetail()->exists()){
                    $hasRelation = true;
                }
            }
        }

        return $hasRelation;
    }

    public function hasBalanceReturn(){
        $qty = 0;

        foreach($this->goodReceiptDetail as $row){
            $qty += $row->getBalanceReturn();
        }

        if($qty > 0){
            return true;
        }else{
            return false;
        }
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->goodReceiptDetail as $row){
            $total += $row->totalInvoice();
        }

        return $total;
    }

    public function hasBalanceInvoice(){
        $total = $this->grandtotal;

        foreach($this->goodReceiptDetail()->whereHas('purchaseInvoiceDetail')->get() as $row){
            foreach($row->purchaseInvoiceDetail as $rowinvoice){
                $total -= $rowinvoice->grandtotal;
            }
        }

        if($total > 0){
            return true;
        }else{
            return false;
        }
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function getLandedCostList(){
        $arr = [];

        foreach($this->goodReceiptDetail as $row){
            foreach($row->landedCostDetail as $rowdetail){
                $arr[] = $rowdetail->landedCost->code;
            }
        }

        $result = array_unique($arr);

        return implode(', ',$result);
    }

    public function updateRootDocumentStatusProcess(){
        foreach($this->goodReceiptDetail()->whereHas('purchaseOrderDetail')->get() as $row){
            $row->purchaseOrderDetail->purchaseOrder->update([
                'status'	=> '2'
            ]);
        }
    }

    public function updateRootDocumentStatusDone(){
        foreach($this->goodReceiptDetail()->whereHas('purchaseOrderDetail')->get() as $row){
            if(!$row->purchaseOrderDetail->purchaseOrder->hasBalance()){
                $row->purchaseOrderDetail->purchaseOrder->update([
                    'status'	=> '3'
                ]);
            }
        }
    }
}
