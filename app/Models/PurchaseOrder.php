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
        'note_external',
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
        'delete_id',
        'delete_note',
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

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
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
          '1' => 'Persediaan Barang',
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

    public function details()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
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

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = PurchaseOrder::selectRaw('RIGHT(code, 8) as code')
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

    
    public function hasBalance(){
        $qty = 0;

        foreach($this->purchaseOrderDetail()->whereNull('status')->get() as $row){
            $qty += $row->getBalanceReceipt();
        }

        if($qty > 0){
            return true;
        }else{
            return false;
        }
    }

    public function hasBalanceInvoice(){
        $total = $this->grandtotal;

        foreach($this->purchaseOrderDetail()->whereHas('purchaseInvoiceDetail')->get() as $row){
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

    public function percentBalance(){
        $qtyBalance = 0;
        $totalQty = 0;

        foreach($this->purchaseOrderDetail()->whereNull('status')->get() as $row){
            $totalQty += $row->qty;
            $qtyBalance += $row->getBalanceReceipt();
        }

        $percent = round(($qtyBalance / $totalQty * 100),2);

        return $percent;
    }

    public function getListItem(){
        $html = '<ol>';

        foreach($this->purchaseOrderDetail as $row){
            $html .= '<li>'.($row->item_id ? $row->item->code.' - '.$row->item->name : $row->coa->name).' Qty. '.$row->qty.' '.($row->item_id ? $row->itemUnit->unit->code : '-').'</li>';
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
        $total = 0;

        foreach($this->purchaseOrderDetail as $row){
            $total += $row->totalInvoice();
        }

        return $total;
    }

    public function updateRootDocumentStatusProcess(){
        if($this->inventory_type == '1'){
            foreach($this->purchaseOrderDetail()->whereHas('purchaseRequestDetail')->get() as $row){
                $row->purchaseRequestDetail->purchaseRequest->update([
                    'status'	=> '2'
                ]);
            }
        }
    }

    public function updateRootDocumentStatusDone(){
        if($this->inventory_type == '1'){
            foreach($this->purchaseOrderDetail()->whereHas('purchaseRequestDetail')->get() as $row){
                if(!$row->purchaseRequestDetail->purchaseRequest->hasBalance()){
                    $row->purchaseRequestDetail->purchaseRequest->update([
                        'status'	=> '3'
                    ]);
                }
            }
        }
    }
}
