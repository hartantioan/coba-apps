<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderPersonInChargeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'work_order_person_in_charge_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [ 
        'work_order_id',
        'user_id',//orang yang mengassign
        'pic_id',//orang yang diassign
        'status'
    ];

    public function workOrder(){
        return $this->belongsTo('App\Models\WorkOrder', 'work_order_id', 'id')->withTrashed();
    }
    public function user(){
        return $this->belongsTo('App\Models\User', 'pic_id', 'id')->withTrashed();
    }

    public static function generateCode()
    {
        $query = WorkOrderPersonInChargeDetail::withTrashed()
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

        $pre = 'WOPIC-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }
}
