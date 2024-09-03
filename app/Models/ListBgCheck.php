<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ListBgCheck extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'list_bg_checks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'post_date',
        'valid_until_date',
        'pay_date',
        'bank_source_name',
        'bank_source_no',
        'document_no',
        'document',
        'note',
        'nominal',
        'grandtotal',
        'status',
    ];

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = ListBgCheck::selectRaw('RIGHT(code, 8) as code')
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
    
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function incomingPayment()
    {
        return $this->hasOne('App\Models\IncomingPayment','list_bg_check_id','id')->whereIn('status',['2','3']);
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
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
}
