<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class InventoryIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'inventory_issue_id',
        'item_stock_new_id',
        'qty',
        'price',
        'total',
        'note',
        'store_item_stock_id',
        'qty_store_item',
    ];

    public function itemStockNew()
    {
        return $this->belongsTo('App\Models\ItemStockNew', 'item_stock_new_id', 'id');
    }

    public function storeItemStock()
    {
        return $this->belongsTo('App\Models\ItemStockNew', 'store_item_stock_id', 'id');
    }

    public function inventoryIssue()
    {
        return $this->belongsTo('App\Models\InventoryIssue', 'inventory_issue_id', 'id');
    }
}
