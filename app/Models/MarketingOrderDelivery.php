<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDelivery extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_deliveries';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'user_update_id',
        'update_time',
        'company_id',
        'account_id',
        'customer_id',
        'post_date',
        'delivery_date',
        'destination_address',
        'city_id',
        'district_id',
        'transportation_id',
        'note_internal',
        'note_external',
        'status',
        'send_status',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function transportation()
    {
        return $this->belongsTo('App\Models\Transportation', 'transportation_id', 'id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function userUpdate()
    {
        return $this->belongsTo('App\Models\User', 'user_update_id', 'id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\User', 'customer_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function marketingOrderDeliveryDetail()
    {
        return $this->hasMany('App\Models\MarketingOrderDeliveryDetail');
    }

    public function marketingOrderDeliveryProcess()
    {
        return $this->hasOne('App\Models\MarketingOrderDeliveryProcess')->whereIn('status',['1','2','3']);
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

    public function sendStatus(){
        $status = match ($this->send_status) {
          '1' => 'SIAP DIKIRIM',
          default => 'BELUM SIAP DIKIRIM',
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

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = MarketingOrderDelivery::selectRaw('RIGHT(code, 8) as code')
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

        if($this->marketingOrderDeliveryProcess()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }
    
    public function getTotal(){
        $total = 0;
        foreach($this->marketingOrderDeliveryDetail as $row){
            $total += $row->getTotal();
        }
        return $total;
    }

    public function getTax(){
        $tax = 0;
        foreach($this->marketingOrderDeliveryDetail as $row){
            $tax += $row->getTax();
        }
        /* return floor($tax); */
        return $tax;
    }

    public function getRounding(){
        $round = 0;
        foreach($this->marketingOrderDeliveryDetail as $row){
            $totalRow = $row->getTotal();
            $roundRow = $row->marketingOrderDetail->marketingOrder->rounding * ($totalRow / $row->marketingOrderDetail->marketingOrder->total);
            $round += $roundRow;
        }
        return $round;
    }

    public function getGrandtotal(){
        $total = $this->getTotal() + $this->getRounding() + $this->getTax();
        return $total;
    }

    public function updateGrandtotal(){
        MarketingOrderDelivery::find($this->id)->update([
            'grandtotal'    => $this->getGrandtotal(),
        ]);
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
}
