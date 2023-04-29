<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestSparepart extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'request_spareparts';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'code',
        'work_order_id',
        'user_id',
        'request_date',
        'summary_issue',
        'status',
    ];
    public function approval(){
        $source = ApprovalSource::where('lookable_type','purchase_orders')->where('lookable_id',$this->id)->first();
        if($source && $source->approvalMatrix()->exists()){
            return $source;
        }else{
            return '';
        }
    }

    public static function generateCode()
    {
        $query = RequestSparepart::withTrashed()
            ->selectRaw('RIGHT(code, 9) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000000001';
        }

        $no = str_pad($code, 9, 0, STR_PAD_LEFT);

        $pre = 'RSp-'.date('y').date('m').date('d').'-';

        return $pre.$no;
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
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function workOrder()
    {
        return $this->belongsTo('App\Models\WorkOrder', 'work_order_id', 'id')->withTrashed();
    }

    public function requestSparePartDetail()
    {
        return $this->hasMany('App\Models\RequestSparepartDetail');
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

}
