<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTransfer extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'employee_transfers';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'account_id',
        'plant_id',
        'manager_id',
        'position_id',
        'type',
        'code',
        'note',
        'status',
        'valid_date',
        'post_date',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note', 
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function manager(){
        return $this->belongsTo('App\Models\User','manager_id','id');
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','plant_id','id')->withTrashed();
    }


    public function position(){
        return $this->belongsTo('App\Models\Position','position_id','id')->withTrashed();
    }

    public function lastTransfer($givenId,$givenAccount){
        $lastTransferBeforeGivenId = EmployeeTransfer::where('id', '<', $givenId)
            ->whereIn('status', [2, 3])
            ->where('account_id',$givenAccount)
            ->orderBy('id', 'desc')
            ->latest() 
            ->first();

        return $lastTransferBeforeGivenId;
    }

    public static function generateCode()
    {
        $query = EmployeeTransfer::selectRaw('RIGHT(code, 6) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return 'ET'.$no;
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

    public function typeRaw(){
        $type = match ($this->type) {
            '1' => 'Promotion',
            '2' => 'Demotion',
            '3' => 'Mutation',
            '4' => 'Resign',
            default => 'Invalid',
        };

        return $type;
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
}
