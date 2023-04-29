<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'work_orders';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [    
        'id',
        'code',
        'place_id',
        'equipment_id',
        'activity_id',
        'area_id',
        'user_id',
        'maintenance_type',
        'priority',
        'work_order_type',
        'suggested_completion_date',
        'request_date',
        'estimated_fix_time',
        'detail_issue',
        'expected_result', 
        'status',  
        'void_id', //void user
        'void_note',    
        'void_date'
    ];

    public static function generateCode()
    {
        $query = WorkOrder::withTrashed()
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

        $pre = 'WO-'.date('y').date('m').date('d').'-';

        return $pre.$no;
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

    public function approval(){
        $source = ApprovalSource::where('lookable_type','purchase_orders')->where('lookable_id',$this->id)->first();
        if($source && $source->approvalMatrix()->exists()){
            return $source;
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

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function area(){
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function activity(){
        return $this->belongsTo('App\Models\Activity', 'activity_id', 'id')->withTrashed();
    }

    public function equipment(){
        return $this->belongsTo('App\Models\Equipment','equipment_id','id')->withTrashed();
    }

    public function workOrderPartDetail(){
        return $this->hasMany('App\Models\WorkOrderPartDetail');
    }
    public function requestSparepart(){
        return $this->hasMany('App\Models\RequestSparepart');
    }
    public function workOrderPersonInChargeDetail(){
        return $this->hasMany('App\Models\WorkOrderPersonInChargeDetail');
    }
    public function workOrderAttachmentDetail(){
        return $this->hasMany('App\Models\WorkOrderAttachmentDetail');
    }
}
