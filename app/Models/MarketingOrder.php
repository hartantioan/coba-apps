<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrder extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_orders';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'type',
        'post_date',
        'valid_date',
        'document',
        'project_id',
        'document_no',
        'type_delivery',
        'sender_id',
        'transportation_id',
        'delivery_date',
        'outlet_id',
        'payment_type',
        'top_internal',
        'top_customer',
        'is_guarantee',
        'user_data_id',
        'billing_address',
        'destination_address',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'sales_id',
        'currency_id',
        'currency_rate',
        'percent_dp',
        'note_internal',
        'note_external',
        'subtotal',
        'discount',
        'total',
        'tax',
        'total_after_tax',
        'rounding',
        'grandtotal',
        'status',
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

    public function transportation()
    {
        return $this->belongsTo('App\Models\Transportation', 'transportation_id', 'id')->withTrashed();
    }

    public function outlet()
    {
        return $this->belongsTo('App\Models\Outlet', 'outlet_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function userData(){
        return $this->belongsTo('App\Models\UserData','user_data_id','id')->withTrashed();
    }

    public function project(){
        return $this->belongsTo('App\Models\Project','project_id','id')->withTrashed();
    }

    public function sender(){
        return $this->belongsTo('App\Models\User','sender_id','id')->withTrashed();
    }

    public function sales(){
        return $this->belongsTo('App\Models\User','sales_id','id')->withTrashed();
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function subdistrict(){
        return $this->belongsTo('App\Models\Region','subdistrict_id','id')->withTrashed();
    }

    public function paymentType(){
        $type = match ($this->payment_type) {
            '1' => 'Cash',
            '2' => 'Credit',
            default => 'Invalid',
        };

        return $type;
    }

    public function isGuarantee(){
        $is = match ($this->is_guarantee) {
            '1' => 'Ya',
            '2' => 'Tidak',
            default => 'Invalid',
        };

        return $is;
    }

    public function type(){
        $type = match ($this->type) {
            '1' => 'Proyek',
            '2' => 'Retail',
            '3' => 'Khusus',
            '4' => 'Sampel',
            default => 'Invalid',
        };

        return $type;
    }

    public function deliveryType(){
        $type = match ($this->type_delivery) {
            '1' => 'Loco',
            '2' => 'Franco',
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

    public function marketingOrderDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderDetail');
    }

    public function marketingOrderDelivery(){
        return $this->hasMany('App\Models\MarketingOrderDelivery','marketing_order_id','id')->whereIn('status',['2','3']);
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
        $query = MarketingOrder::selectRaw('RIGHT(code, 8) as code')
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

        if($this->marketingOrderDelivery()->exists()){
            $hasRelation = true;
        }

        foreach($this->marketingOrderDetail as $row){
            if($row->marketingOrderDeliveryDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function hasBalanceMod(){
        $passed = false;

        foreach($this->marketingOrderDetail as $row){
            if($row->balanceQtyMod() > 0){
                $passed = true;
            }
        }

        return $passed;
    }

    public function totalMod(){
        $total = 0;
        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail as $rowmodd){
                $total += $rowmodd->getGrandtotal();
                /* foreach($rowmodd->marketingOrderReturnDetail as $rowreturn){
                    $total -= $rowreturn->getGrandtotal();
                } */
            }
        }
        return $total;
    }

    public function totalModProcess(){
        $total = 0;
        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail()->whereHas('marketingOrderDelivery',function($query){
                $query->whereHas('marketingOrderDeliveryProcess');
            })->get() as $rowmodd){
                $total += $rowmodd->getGrandtotal();
                /* foreach($rowmodd->marketingOrderReturnDetail as $rowreturn){
                    $total -= $rowreturn->getGrandtotal();
                } */
            }
        }
        return $total;
    }

    public function totalReturn(){
        $total = 0;
        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail()->whereHas('marketingOrderDelivery',function($query){
                $query->whereHas('marketingOrderDeliveryProcess');
            })->get() as $rowmodd){
                foreach($rowmodd->marketingOrderReturnDetail as $rowreturn){
                    $total += $rowreturn->getGrandtotal();
                }
            }
        }
        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail as $rowmodd){
                foreach($rowmodd->marketingOrderInvoiceDetail as $rowinvoice){
                    $total += $rowinvoice->getGrandtotal();
                }
            }
        }

        return $total;
    }

    public function totalMemo(){
        $total = 0;

        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail as $rowmodd){
                foreach($rowmodd->marketingOrderInvoiceDetail as $rowinvoice){
                    foreach($rowinvoice->marketingOrderMemoDetail as $rowmemo){
                        $total += $rowmemo->grandtotal;
                    }
                }
            }
        }

        return $total;
    }

    public function totalPayment(){
        $total = 0;

        foreach($this->marketingOrderDetail as $row){
            foreach($row->marketingOrderDeliveryDetail as $rowmodd){
                foreach($rowmodd->marketingOrderInvoiceDetail as $rowinvoice){
                    $total += $rowinvoice->getDownPayment();
                    $total += $rowinvoice->getPayment();
                }
            }
        }

        return $total;
    }
}
