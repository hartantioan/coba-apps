<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
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

    public function supplier()
    {
        return $this->belongsTo('App\Models\User', 'supplier_id', 'id')->withTrashed();
    }

    public function vendor()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
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

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
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

    public function getPurchaseCode(){
        $arr = [];
        
        foreach($this->landedCostDetail as $row){
            if($row->goodReceiptDetail()){
                $index = CustomHelper::checkArrayRaw($arr,$row->lookable->purchaseOrderDetail->purchaseOrder->code);
                if($index < 0){
                    $arr[] = $row->lookable->purchaseOrderDetail->purchaseOrder->code;
                }
            }
        }

        if(count($arr) > 0){
            return implode(', ',$arr);
        }else{
            return '-';
        }
    }

    public function getGoodReceiptNo(){
        $arr = [];
        
        foreach($this->landedCostDetail as $row){
            if($row->goodReceiptDetail()){
                $index = CustomHelper::checkArrayRaw($arr,$row->lookable->goodReceipt->code);
                if($index < 0){
                    $arr[] = $row->lookable->goodReceipt->code;
                }
            }
        }

        if(count($arr) > 0){
            return implode(', ',$arr);
        }else{
            return '-';
        }
    }

    public function getListDeliveryNo(){
        $arr = [];

        foreach($this->landedCostDetail as $row){
            if($row->goodReceiptDetail()){
                $index = CustomHelper::checkArrayRaw($arr,$row->lookable->goodReceipt->delivery_no);
                if($index < 0){
                    $arr[] = $row->lookable->goodReceipt->delivery_no;
                }
            }
        }

        if(count($arr) > 0){
            return implode(', ',$arr);
        }else{
            return '-';
        }
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
        $query = LandedCost::selectRaw('RIGHT(code, 8) as code')
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

    

    public function getListItem(){
        $html = '<ol>';

        foreach($this->landedCostDetail as $row){
            if($row->goodReceiptDetail()){
                $html .= '<li>'.$row->lookable->item->code.' - '.$row->lookable->item->name.' Qty. '.CustomHelper::formatConditionalQty($row->lookable->qty).' '.$row->lookable->itemUnit->unit->code.'</li>';
            }
        }

        $html .= '</ol>';

        return $html;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->landedCostFeeDetail as $row){
            if($row->purchaseInvoiceDetail()->exists()){
                $hasRelation = true;
            }
        }
        

        return $hasRelation;
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->landedCostFeeDetail as $row){
            foreach($row->purchaseInvoiceDetail as $rowinvoice){
                $total -= $rowinvoice->grandtotal;
            }
        }

        return $total;
    }

    public function hasBalanceInvoice(){
        if($this->balanceInvoice() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getArrayDetail(){
        $arrInfo = [];
        
        foreach($this->landedCostDetail as $row){
            $arrInfo = [
                'place_id'          => $row->place_id,
                'place_name'        => $row->place->code,
                'line_id'           => $row->line_id ? $row->line_id : '',
                'line_name'         => $row->line_id ? $row->line->name : '-',
                'machine_id'        => $row->machine_id ? $row->machine_id : '',
                'machine_name'      => $row->machine_id ? $row->machine->name : '-',
                'department_id'     => $row->department_id ? $row->department_id : '',
                'department_name'   => $row->department_id ? $row->department->name : '-',
                'warehouse_id'      => $row->warehouse_id,
                'warehouse_name'    => $row->warehouse->name,
            ];
        }

        return $arrInfo;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->landedCostFeeDetail as $row){
            foreach($row->purchaseInvoiceDetail as $rowinvoice){
                $total += $rowinvoice->grandtotal;
            }
        }

        return $total;
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function getLandedCostList(){
        $arr = [];

        foreach($this->landedCostDetail as $row){
            foreach($row->landedCostDetailSelf as $rowdetail){
                $arr[] = $rowdetail->landedCost->code;
            }
        }

        $result = array_unique($arr);

        return implode(', ',$result);
    }

    public function getDetailFromInformation(){
        $from_address = '';
        $subdistrict_id = 0;
        foreach($this->landedCostDetail as $row){
            $subdistrict_id = $row->lookable->inventoryTransferOut->placeFrom->subdistrict_id;
            $from_address = $row->lookable->inventoryTransferOut->placeFrom->city->name.' - '.$row->lookable->inventoryTransferOut->placeFrom->subdistrict->name;
        }

        $arr = [
            'from_address'      => $from_address,
            'subdistrict_id'    => $subdistrict_id,
        ];

        return $arr;
    }

    public function getLocalImportCost(){
        $arr = [];

        $totalLocal = $this->landedCostFeeDetail()->whereHas('landedCostFee', function($query){
            $query->where('type','1');
        })->sum('total');

        $totalImport = $this->landedCostFeeDetail()->whereHas('landedCostFee', function($query){
            $query->where('type','2');
        })->sum('total');

        $arr['total_local'] = $totalLocal;
        $arr['coa_local'] = Coa::where('code','200.01.05.01.10')->where('company_id',$this->place->company_id)->first()->id;
        $arr['total_import'] = $totalImport;
        $arr['coa_import'] = Coa::where('code','200.01.05.01.11')->where('company_id',$this->place->company_id)->first()->id;

        return $arr;
    }

    public function getReference(){
        $code = [];
        foreach($this->landedCostDetail as $row){
            if($row->goodReceiptDetail()){
                $code[] = $row->lookable->goodReceipt->code;
            }elseif($row->inventoryTransferOutDetail()){
                $code[] = $row->lookable->inventoryTransferOut->code;
            }elseif($row->landedCostDetail()){
                $code[] = $row->lookable->landedCost->code;
            }
        }
        return implode(', ',$code);
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

    public function getRequesterByItem($item_id){
        $arr = [];
        foreach($this->landedCostDetail()->get() as $row){
            if($row->goodReceiptDetail()){
                if($row->lookable->item_id == $item_id){
                    $arr[] = $row->lookable->purchaseOrderDetail->requester;
                }
            }
        }
        return implode(', ',$arr);
    }
}
