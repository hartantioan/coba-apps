<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestSparepartDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'request_sparepart_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'request_sparepart_id',
        'equipment_sparepart_id',
        'item_stock_id',
        'qty_request',
        'qty_usage',
        'qty_return',
        'qty_repair',
    ];

    public function requestSparepart()
    {
        return $this->belongsTo('App\Models\RequestSparepart', 'request_sparepart_id', 'id')->withTrashed();
    }

    public function equipmentSparepart()
    {
        return $this->belongsTo('App\Models\EquipmentSparepart', 'equipment_sparepart_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }
    
}
