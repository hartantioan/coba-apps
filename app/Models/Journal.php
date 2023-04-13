<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'journals';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'account_id',
        'code',
        'place_id',
        'lookable_type',
        'lookable_id',
        'currency_id',
        'currency_rate',
        'post_date',
        'due_date',
        'note',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }
    
    public function currency(){
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function journalDetail()
    {
        return $this->hasMany('App\Models\JournalDetail','journal_id','id');
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type','journals')->where('lookable_id',$this->id)->first();
        if($source && $source->approvalMatrix()->exists()){
            return $source;
        }else{
            return '';
        }
    }

    public function listApproval(){
        $source = $this->approval();
        if($source){
            $html = '';
            foreach($source->approvalMatrix()->whereHas('approvalTable',function($query){ $query->orderBy('level'); })->get() as $row){
                $html .= '<span style="top:-10px;">'.$row->user->name.'</span> '.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br>';
            }

            return $html;
        }else{
            return '';
        }
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

    public static function generateCode()
    {
        $query = Journal::selectRaw('RIGHT(code, 11) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000000001';
        }

        $no = str_pad($code, 11, 0, STR_PAD_LEFT);

        $pre = 'JR-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }
}
