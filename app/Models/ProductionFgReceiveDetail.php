<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class productionFgReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_fg_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_fg_receive_id',
        'item_id',
        'pallet_no',
        'shading',
        'qty_sell',
        'qty',
    ];

    public function productionFgReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public static function getLatestCode($prefix){
        $query = ProductionReceive::selectRaw('RIGHT(code, 5) as code')
            ->whereRaw("code LIKE '$prefix%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00001';
        }

        $no = str_pad($code, 5, 0, STR_PAD_LEFT);

        return $prefix.'.'.$no;
    }
}
