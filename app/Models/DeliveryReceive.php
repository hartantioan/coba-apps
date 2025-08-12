<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryReceive extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_receives'; // Explicitly set the table name

    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'receiver_name',
        'post_date',
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCode($prefix)
    {
        $query = DeliveryReceive::selectRaw('RIGHT(code, 8) as code')
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

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Direvisi',
            '7' => 'Schedule',
            '8' => 'Ditutup Balik',
            default => 'Invalid',
        };
        return $status;
    }


    public function account(){
        return $this->belongsTo('App\Models\Supplier','account_id','id')->withTrashed();
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'void_id');
    }

    public function deliveryReceiveDetail()
    {
        return $this->hasMany('App\Models\DeliveryReceiveDetail');
    }
    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }
}
