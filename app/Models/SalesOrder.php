<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_orders';

    protected $fillable = [
        'code',
        'user_id',
        'note',
        'type_sales',
        'customer_id',
        'document',
        'post_date',
        'payment_type',
        'subtotal',
        'tax',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    protected $dates = ['post_date', 'void_date', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\User', 'customer_id', 'id')->withTrashed();
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'void_id');
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }

    public function paymentType(){
        $type = match ($this->payment_type) {
            '1' => 'Cash',
            '2' => 'Credit',
            default => 'Invalid',
        };

        return $type;
    }

    public function salesOrderDetail()
    {
        return $this->hasMany('App\Models\SalesOrderDetail');
    }
    public static function generateCode($prefix)
    {
        $query = SalesOrder::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$prefix%'")
            ->withTrashed()
            ->orderByDesc('code')
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

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          '8' => '<span class="pink darken-4 medium-small white-text padding-3">Ditutup Balik</span>',
          '9' => '<span class="pink darken-4 medium-small white-text padding-3">Dilock Procurement</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
