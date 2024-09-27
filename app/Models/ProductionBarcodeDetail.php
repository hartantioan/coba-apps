<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionBarcodeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_barcode_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_barcode_id',
        'item_id',
        'bom_id',
        'item_unit_id',
        'pallet_no',
        'shading',
        'qty_sell',
        'qty',
        'conversion',
        'pallet_id',
        'grade_id',
    ];

    public function bom()
    {
        return $this->belongsTo('App\Models\Bom', 'bom_id', 'id')->withTrashed();
    }

    public function itemUnit()
    {
        return $this->belongsTo('App\Models\ItemUnit', 'item_unit_id', 'id')->withTrashed();
    }

    public function productionBarcode()
    {
        return $this->belongsTo('App\Models\ProductionBarcode', 'production_barcode_id', 'id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\ProductionBarcode', 'production_barcode_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function pallet()
    {
        return $this->belongsTo('App\Models\Pallet', 'pallet_id', 'id')->withTrashed();
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Grade', 'grade_id', 'id')->withTrashed();
    }

    public function productionFgReceiveDetail()
    {
        return $this->hasOne('App\Models\ProductionFgReceiveDetail', 'pallet_no', 'pallet_no')->whereHas('productionFgReceive',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
