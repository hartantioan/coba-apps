<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemCogs extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_cogs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'lookable_type',
        'lookable_id',
        'detailable_type',
        'detailable_id',
        'company_id',
        'place_id',
        'warehouse_id',
        'area_id',
        'item_id',
        'item_shading_id',
        'production_batch_id',
        'qty_in',
        'price_in',
        'total_in',
        'qty_out',
        'price_out',
        'total_out',
        'qty_final',
        'price_final',
        'total_final',
        'date',
        'type',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function detailable(){
        return $this->morphTo();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\Item', 'item_shading_id', 'id')->withTrashed();
    }

    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function productionBatch(){
        return $this->belongsTo('App\Models\ProductionBatch', 'production_batch_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function area(){
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function getRequester(){
        $requester = '-';
        if($this->lookable_type == 'good_receipts' || $this->lookable_type == 'good_issues'){
            if($this->lookable_type == 'good_issues'){
                $requester = $this->detailable->requester ?? '';
            }
            if($this->lookable_type == 'good_receipts'){
                $requester = $this->detailable->purchaseOrderDetail->requester ?? '';
            }
        }
        return $requester;
    }
}
