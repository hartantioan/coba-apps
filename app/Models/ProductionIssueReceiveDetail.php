<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionIssueReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_issue_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_issue_receive_id',
        'production_order_detail_id',
        'lookable_type',
        'lookable_id',
        'shading',
        'bom_id',
        'qty',
        'nominal',
        'total',
        'type',
        'from_item_stock_id',
    ];

    public function productionIssueReceive()
    {
        return $this->belongsTo('App\Models\ProductionIssueReceive', 'production_issue_receive_id', 'id')->withTrashed();
    }

    public function productionOrderDetail(){
        return $this->belongsTo('App\Models\ProductionOrderDetail','production_order_detail_id','id')->withTrashed();
    }

    public function itemStock(){
        return $this->belongsTo('App\Models\ItemStock','from_item_stock_id','id')->withTrashed();
    }

    public function bom(){
        return $this->belongsTo('App\Models\Bom','bom_id','id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function type(){
        $type = match ($this->type) {
            '1' => 'Issue',
            '2' => 'Receive',
            default => 'Invalid',
        };

        return $type;
    }
}
