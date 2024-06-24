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
        'pallet_no',
        'qty_sell',
        'qty',
    ];

    public function productionFgReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_fg_receive_id', 'id')->withTrashed();
    }
}
