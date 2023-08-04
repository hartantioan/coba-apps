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
        'type_sales',
        'post_date',
        'valid_date',
        'document',
        'document_no',
        'type_delivery',
        'sender_id',
        'delivery_date',
        'payment_type',
        'top_internal',
        'top_customer',
        'is_guarantee',
        'shipment_address',
        'billing_address',
        'destination_address',
        'province_id',
        'city_id',
        'subdistrict_id',
        'sales_id',
        'currency_id',
        'currency_rate',
        'note',
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

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
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

    public function deliveryType(){
        $type = match ($this->type_delivery) {
            '1' => 'Loco',
            '2' => 'Franco',
          default => 'Invalid',
        };

        return $type;
    }

    public function typeSales(){
        $type = match ($this->type_sales) {
            '1'   => 'Standar SO',
            '2'   => 'Cash / POS',
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

        //logic here

        return $hasRelation;
    }
}
