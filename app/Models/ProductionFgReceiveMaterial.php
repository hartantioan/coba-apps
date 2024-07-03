<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionFgReceiveMaterial extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_fg_receive_materials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_fg_receive_detail_id',
        'bom_detail_id',
        'qty',
        'item_stock_id',
        'total'
    ];

    public function productionFgReceiveDetail()
    {
        return $this->belongsTo('App\Models\ProductionFgReceiveDetail', 'production_fg_receive_detail_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id')->withTrashed();
    }

    public function bomDetail()
    {
        return $this->belongsTo('App\Models\BomDetail', 'bom_detail_id', 'id')->withTrashed();
    }
}
