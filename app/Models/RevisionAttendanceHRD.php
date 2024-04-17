<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RevisionAttendanceHRD extends Model
{

    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'revision_attendance_hrd';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'post_date',
        'period_id',
        'note',
        'void_id',
        'void_date',
        'void_note',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
        'status',
    ];


    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function period(){
        return $this->belongsTo('App\Models\AttendancePeriod','period_id','id');
    }

    public function attendance(){
        return $this->hasMany('App\Models\Attendances');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id');
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

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = RevisionAttendanceHRD::selectRaw('RIGHT(code, 8) as code')
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
