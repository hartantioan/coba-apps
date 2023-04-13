<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'qty',
        'note',
        'required_date',
        'place_id',
        'department_id',
        'warehouse_id',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo('App\Models\PurchaseRequest', 'purchase_request_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function qtyBalance(){
        $qty = $this->qty;

        foreach($this->purchaseOrderDetail as $row){
            $qty -= $row->qty;
        }

        return $qty;
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail', 'purchase_request_detail_id', 'id');
    }
}
