<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'inventory_type',
        'purchasing_type',
        'shipping_type',
        'is_tax',
        'is_include_tax',
        'document_no',
        'document_po',
        'percent_tax',
        'payment_type',
        'payment_term',
        'currency_id',
        'currency_rate',
        'post_date',
        'delivery_date',
        'note',
        'subtotal',
        'discount',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'receiver_name',
        'receiver_address',
        'receiver_phone'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function supplier(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function purchasingType(){
        $type = match ($this->purchasing_type) {
          '1' => 'Standart PO',
          '2' => 'Planned PO',
          '3' => 'Blanked PO',
          '4' => 'Contract PO',
          default => 'Invalid',
        };

        return $type;
    }

    public function inventoryType(){
        $type = match ($this->inventory_type) {
          '1' => 'Inventori',
          '2' => 'Jasa',
          default => 'Invalid',
        };

        return $type;
    }

    public function paymentType(){
        $type = match ($this->payment_type) {
          '1' => 'Cash',
          '2' => 'Credit',
          '3' => 'CBD',
          '4' => 'DP',
          default => 'Invalid',
        };

        return $type;
    }

    public function shippingType(){
        $type = match ($this->shipping_type) {
          '1' => 'Franco',
          '2' => 'Loco',
          default => 'Invalid',
        };

        return $type;
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
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

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
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
        if($this->document_po !== NULL && Storage::exists($this->document_po)) {
            $document_po = asset(Storage::url($this->document_po));
        } else {
            $document_po = asset('website/empty.png');
        }

        return $document_po;
    }

    public function deleteFile(){
		if(Storage::exists($this->document_po)) {
            Storage::delete($this->document_po);
        }
	}

    public static function generateCode()
    {
        $query = PurchaseOrder::selectRaw('RIGHT(code, 9) as code')
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

        $pre = 'PO-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type','purchase_orders')->where('lookable_id',$this->id)->first();
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

    public function hasBalance(){
        $qty = 0;

        foreach($this->purchaseOrderDetail as $row){
            $qty += $row->getBalanceReceipt();
        }

        if($qty > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getListItem(){
        $html = '<ol>';

        foreach($this->purchaseOrderDetail as $row){
            $html .= '<li>'.($row->item_id ? $row->item->code.' - '.$row->item->name : $row->coa->name).' Qty. '.$row->qty.' '.($row->item_id ? $row->item->buyUnit->code : '-').'</li>';
        }

        $html .= '</ol>';

        return $html;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->purchaseOrderDetail as $row){
            if($row->goodReceiptDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function totalInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseOrderDetail as $row){
            $total += $row->totalInvoice();
        }

        return $total;
    }
}
