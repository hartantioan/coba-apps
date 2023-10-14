<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'leave_requests';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'account_id',
        'leave_type_id',
        'start_time',
        'end_time',
        'end_date',
        'post_date',
        'start_date',
        'note',
        'document',
        'void_id',
        'void_date',
        'void_note',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id');
    }

    public function leaveRequestShift(){
        return $this->hasMany('App\Models\LeaveRequestShift');
    }

    public function leaveType(){
        return $this->belongsTo('App\Models\LeaveType','leave_type_id','id');
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
    
    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
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

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = LeaveRequest::selectRaw('RIGHT(code, 8) as code')
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

}
