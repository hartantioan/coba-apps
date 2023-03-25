<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetailComposition extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_order_detail_compositions';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'pod_id',
        'pr_id',
        'qty'
    ];

    public function purchaseOrderDetail()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'pod_id', 'id')->withTrashed();
    }

    public function purchaseRequest()
    {
        return $this->belongsTo('App\Models\PurchaseRequest', 'pr_id', 'id')->withTrashed();
    }
}
