<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderPartDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'work_order_part_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [    
        'id',
        'work_order_id',
        'part_id'
    ];

    public static function generateCode($post_date)
    {
        $query = WorkOrderPartDetail::selectRaw('RIGHT(code, 9) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000000001';
        }

        $no = str_pad($code, 9, 0, STR_PAD_LEFT);

        $pre = 'WOPD-'.date('ymd',strtotime($post_date)).'-';

        return $pre.$no;
    }

    public function workOrder(){
        return $this->belongsTo('App\Models\WorkOrder', 'work_order_id', 'id')->withTrashed();
    }

    public function equipmentPart(){
        return $this->belongsTo('App\Models\EquipmentPart', 'part_id', 'id')->withTrashed();
    }
}
